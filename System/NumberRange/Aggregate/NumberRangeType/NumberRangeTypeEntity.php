<?php declare(strict_types=1);

namespace Cicada\Core\System\NumberRange\Aggregate\NumberRangeType;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\NumberRange\Aggregate\NumberRangeSalesChannel\NumberRangeSalesChannelEntity;
use Cicada\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation\NumberRangeTypeTranslationCollection;
use Cicada\Core\System\NumberRange\NumberRangeCollection;

#[Package('checkout')]
class NumberRangeTypeEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $typeName;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $technicalName;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $global;

    /**
     * @var NumberRangeCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $numberRanges;

    /**
     * @var NumberRangeSalesChannelEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $numberRangeSalesChannels;

    /**
     * @var NumberRangeTypeTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): void
    {
        $this->typeName = $typeName;
    }

    public function getGlobal(): bool
    {
        return $this->global;
    }

    public function setGlobal(bool $global): void
    {
        $this->global = $global;
    }

    public function getNumberRanges(): ?NumberRangeCollection
    {
        return $this->numberRanges;
    }

    public function setNumberRanges(NumberRangeCollection $numberRanges): void
    {
        $this->numberRanges = $numberRanges;
    }

    public function getTranslations(): ?NumberRangeTypeTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(NumberRangeTypeTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getNumberRangeSalesChannels(): ?NumberRangeSalesChannelEntity
    {
        return $this->numberRangeSalesChannels;
    }

    public function setNumberRangeSalesChannels(NumberRangeSalesChannelEntity $numberRangeSalesChannels): void
    {
        $this->numberRangeSalesChannels = $numberRangeSalesChannels;
    }
}
