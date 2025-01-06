<?php declare(strict_types=1);

namespace Cicada\Core\System\SystemConfig\Event;

use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('services-settings')]
class SystemConfigMultipleChangedEvent extends Event
{
    /**
     * @param array<string, array<mixed>|bool|float|int|string|null> $config
     */
    public function __construct(
        private readonly array $config,
        private readonly ?string $salesChannelId
    ) {
    }

    /**
     * @return array<string, array<mixed>|bool|float|int|string|null>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }
}
