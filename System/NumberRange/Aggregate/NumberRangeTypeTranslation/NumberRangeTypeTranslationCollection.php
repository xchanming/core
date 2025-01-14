<?php declare(strict_types=1);

namespace Cicada\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NumberRangeTypeTranslationEntity>
 */
#[Package('checkout')]
class NumberRangeTypeTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getNumberRangeTypeIds(): array
    {
        return $this->fmap(fn (NumberRangeTypeTranslationEntity $numberRangeTypeTranslation) => $numberRangeTypeTranslation->getNumberRangeTypeId());
    }

    public function filterByNumberRangeTypeId(string $id): self
    {
        return $this->filter(fn (NumberRangeTypeTranslationEntity $numberRangeTypeTranslation) => $numberRangeTypeTranslation->getNumberRangeTypeId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (NumberRangeTypeTranslationEntity $numberRangeTypeTranslation) => $numberRangeTypeTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (NumberRangeTypeTranslationEntity $numberRangeTypeTranslation) => $numberRangeTypeTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'number_range_type_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return NumberRangeTypeTranslationEntity::class;
    }
}
