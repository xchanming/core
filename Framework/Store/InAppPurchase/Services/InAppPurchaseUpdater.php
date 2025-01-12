<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\InAppPurchase\Services;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Cicada\Core\Framework\Store\InAppPurchase;
use Cicada\Core\Framework\Store\InAppPurchase\Event\InAppPurchaseChangedEvent;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchaseUpdater
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $systemConfigService,
        private readonly string $fetchEndpoint,
        private readonly AbstractStoreRequestOptionsProvider $storeRequestOptionsProvider,
        private readonly InAppPurchase $inAppPurchase,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function update(Context $context): void
    {
        $this->fetchFromStore($context);
        $this->inAppPurchase->reset();
        $this->dispatchEvents($context);
    }

    private function fetchFromStore(Context $context): void
    {
        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                $this->fetchEndpoint,
                [
                    'query' => $this->storeRequestOptionsProvider->getDefaultQueryParameters($context),
                    'headers' => $this->storeRequestOptionsProvider->getAuthenticationHeader($context),
                ],
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Error fetching in-app purchases from store', ['error' => 'Invalid response code: ' . $response->getStatusCode()]);
            } else {
                $body = $response->getBody()->getContents();

                if ($this->validateData($body)) {
                    $this->systemConfigService->set(InAppPurchaseProvider::CONFIG_STORE_IAP_KEY, $body);
                } else {
                    $this->logger->error('Error fetching in-app purchases from store', ['error' => 'Invalid response format']);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error fetching in-app purchases from store', ['error' => $e->getMessage()]);
        }
    }

    private function dispatchEvents(Context $context): void
    {
        $extensionNames = \array_keys($this->inAppPurchase->all());

        $apps = $this->fetchAppList();

        foreach ($extensionNames as $extensionName) {
            $purchaseToken = \json_encode($this->inAppPurchase->getByExtension($extensionName), \JSON_THROW_ON_ERROR);

            if (!\array_key_exists($extensionName, $apps)) {
                continue;
            }

            $event = new InAppPurchaseChangedEvent($extensionName, $purchaseToken, $apps[$extensionName], $context);
            $this->eventDispatcher->dispatch($event);
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    private function fetchAppList(): array
    {
        return $this->connection->fetchAllKeyValue('
            SELECT `name`, LOWER(HEX(`id`)) AS `id`
            FROM `app`
            WHERE `active` = 1');
    }

    private function validateData(string $array): bool
    {
        $array = json_decode($array, true, 512, \JSON_THROW_ON_ERROR);
        foreach ($array as $key => $value) {
            if (!\is_string($key) || !\is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
