<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\InAppPurchases\Payload;

use Cicada\Core\Framework\App\Payload\Source;
use Cicada\Core\Framework\App\Payload\SourcedPayloadInterface;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\JsonSerializableTrait;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class InAppPurchasesPayload implements SourcedPayloadInterface
{
    use JsonSerializableTrait;

    public Source $source;

    /**
     * @param array<int, string> $purchases
     */
    public function __construct(public readonly array $purchases)
    {
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }
}
