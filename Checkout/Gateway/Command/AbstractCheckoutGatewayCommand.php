<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Gateway\Command;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('checkout')]
abstract class AbstractCheckoutGatewayCommand extends Struct
{
    abstract public static function getDefaultKeyName(): string;

    /**
     * @param array<array-key, mixed> $payload
     *
     * @throws \Error
     */
    public static function createFromPayload(array $payload): static
    {
        /** @phpstan-ignore-next-line  */
        return new static(...$payload);
    }
}
