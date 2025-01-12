<?php declare(strict_types=1);

namespace Cicada\Core\System\SalesChannel\Aggregate\SalesChannelTranslation;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SalesChannelTranslationEntity>
 */
#[Package('discovery')]
class SalesChannelTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getSalesChannelIds(): array
    {
        return $this->fmap(fn (SalesChannelTranslationEntity $salesChannelTranslation) => $salesChannelTranslation->getSalesChannelId());
    }

    public function filterBySalesChannelId(string $id): self
    {
        return $this->filter(fn (SalesChannelTranslationEntity $salesChannelTranslation) => $salesChannelTranslation->getSalesChannelId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (SalesChannelTranslationEntity $salesChannelTranslation) => $salesChannelTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (SalesChannelTranslationEntity $salesChannelTranslation) => $salesChannelTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'sales_channel_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return SalesChannelTranslationEntity::class;
    }
}
