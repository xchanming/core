<?php declare(strict_types=1);

namespace Cicada\Core\Content\Sitemap\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('discovery')]
class SitemapGenerationResult extends Struct
{
    public function __construct(
        private readonly bool $finish,
        private readonly ?string $provider,
        private readonly ?int $offset,
        private readonly ?string $lastSalesChannelId,
        private readonly string $lastLanguageId
    ) {
    }

    public function isFinish(): bool
    {
        return $this->finish;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLastSalesChannelId(): ?string
    {
        return $this->lastSalesChannelId;
    }

    public function getLastLanguageId(): string
    {
        return $this->lastLanguageId;
    }

    public function getApiAlias(): string
    {
        return 'sitemap_generation_result';
    }
}
