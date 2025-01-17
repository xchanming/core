<?php declare(strict_types=1);

namespace Cicada\Core\System\Country\Aggregate\CountryState;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use Cicada\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IntField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Country\Aggregate\CountryStateTranslation\CountryStateTranslationDefinition;
use Cicada\Core\System\Country\CountryDefinition;

#[Package('fundamentals@discovery')]
class CountryStateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'country_state';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CountryStateCollection::class;
    }

    public function getEntityClass(): string
    {
        return CountryStateEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return CountryDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new ParentFkField(self::class))->addFlags(new ApiAware()),
            (new FkField('country_id', 'countryId', CountryDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('short_code', 'shortCode'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('country', 'country_id', CountryDefinition::class, 'id', false),
            (new TranslationsAssociationField(CountryStateTranslationDefinition::class, 'country_state_id'))->addFlags(new Required()),
            // Reverse Associations, not available in sales-channel-api
            (new OneToManyAssociationField('customerAddresses', CustomerAddressDefinition::class, 'country_state_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('orderAddresses', OrderAddressDefinition::class, 'country_state_id', 'id'))->addFlags(new SetNullOnDelete()),
            new ManyToOneAssociationField('parent', CountryStateDefinition::class, CountryStateDefinition::class, 'id', false),
            new OneToManyAssociationField('children', CountryStateDefinition::class, 'parent_id'),
        ]);
    }
}
