<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Subscriber;

use Cicada\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('checkout')]
readonly class CustomerRemoteAddressSubscriber implements EventSubscriberInterface
{
    private const STORE_PLAIN_IP_ADDRESS = 'core.loginRegistration.customerIpAddressesNotAnonymously';

    /**
     * @internal
     */
    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack,
        private SystemConfigService $configService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerLoginEvent::class => 'updateRemoteAddressByLogin',
        ];
    }

    public function updateRemoteAddressByLogin(CustomerLoginEvent $event): void
    {
        $request = $this->requestStack
            ->getMainRequest();

        if (!$request) {
            return;
        }

        $clientIp = $request->getClientIp();

        if ($clientIp === null) {
            return;
        }

        if (!$this->configService->getBool(self::STORE_PLAIN_IP_ADDRESS)) {
            $clientIp = IpUtils::anonymize($clientIp);
        }

        $this->connection->update('customer', [
            'remote_address' => $clientIp,
        ], [
            'id' => Uuid::fromHexToBytes($event->getCustomer()->getId()),
        ]);
    }
}
