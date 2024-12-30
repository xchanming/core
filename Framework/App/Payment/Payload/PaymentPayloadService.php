<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Payment\Payload;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Cicada\Core\Framework\App\Payload\SourcedPayloadInterface;
use Cicada\Core\Framework\App\Payment\Response\AbstractResponse;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use GuzzleHttp\ClientInterface;

/**
 * @internal only for use by the app-systems
 */
#[Package('checkout')]
class PaymentPayloadService
{
    public const PAYMENT_REQUEST_TIMEOUT = 20;

    public function __construct(
        private readonly AppPayloadServiceHelper $helper,
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @template T of AbstractResponse
     *
     * @param class-string<T> $responseClass
     *
     * @return T
     */
    public function request(
        string $url,
        SourcedPayloadInterface $payload,
        AppEntity $app,
        string $responseClass,
        Context $context
    ): AbstractResponse {
        $optionRequest = $this->helper->createRequestOptions(
            $payload,
            $app,
            $context,
            [
                'timeout' => self::PAYMENT_REQUEST_TIMEOUT,
            ],
        );

        $response = $this->client->request('POST', $url, $optionRequest->jsonSerialize());

        $content = $response->getBody()->getContents();

        return $responseClass::create(\json_decode($content, true, 512, \JSON_THROW_ON_ERROR));
    }
}
