<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Payload;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\CloneTrait;
use Cicada\Core\Framework\Struct\JsonSerializableTrait;

/**
 * @internal only for use by the app-system
 *
 * @method array{url: string, shopId: string, appVersion: string} jsonSerialize()
 */
#[Package('core')]
class Source implements \JsonSerializable
{
    use CloneTrait;
    use JsonSerializableTrait;

    public function __construct(
        protected string $url,
        protected string $shopId,
        protected string $appVersion,
        protected ?string $inAppPurchases = null,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getAppVersion(): string
    {
        return $this->appVersion;
    }

    public function getInAppPurchases(): ?string
    {
        return $this->inAppPurchases;
    }
}
