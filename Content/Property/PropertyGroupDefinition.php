<?php declare(strict_types=1);

namespace Cicada\Core\Content\Property;

use Cicada\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Cicada\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class PropertyGroupDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'property_group';

    final public const DISPLAY_TYPE_TEXT = 'text';

    final public const DISPLAY_TYPE_IMAGE = 'image';

    final public const DISPLAY_TYPE_MEDIA = 'media';

    final public const DISPLAY_TYPE_COLOR = 'color';

    final public const SORTING_TYPE_ALPHANUMERIC = 'alphanumeric';

    final public const SORTING_TYPE_POSITION = 'position';

    final public const FILTERABLE = true;

    final public const VISIBLE_ON_PRODUCT_DETAIL_PAGE = true;

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PropertyGroupCollection::class;
    }

    public function getEntityClass(): string
    {
        return PropertyGroupEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'displayType' => self::DISPLAY_TYPE_TEXT,
            'sortingType' => self::SORTING_TYPE_ALPHANUMERIC,
            'filterable' => self::FILTERABLE,
            'visibleOnProductDetailPage' => self::VISIBLE_ON_PRODUCT_DETAIL_PAGE,
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    public function getHydratorClass(): string
    {
        return PropertyGroupHydrator::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new StringField('display_type', 'displayType'))->addFlags(new ApiAware(), new Required()),
            (new StringField('sorting_type', 'sortingType'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('filterable', 'filterable'))->addFlags(new ApiAware()),
            (new BoolField('visible_on_product_detail_page', 'visibleOnProductDetailPage'))->addFlags(new ApiAware()),
            (new TranslatedField('position'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('options', PropertyGroupOptionDefinition::class, 'property_group_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new TranslationsAssociationField(PropertyGroupTranslationDefinition::class, 'property_group_id'))->addFlags(new Required(), new CascadeDelete()),
        ]);
    }
}
