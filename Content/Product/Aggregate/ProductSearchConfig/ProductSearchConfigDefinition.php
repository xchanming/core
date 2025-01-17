<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Aggregate\ProductSearchConfig;

use Cicada\Core\Content\Product\Aggregate\ProductSearchConfigField\ProductSearchConfigFieldDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IntField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ListField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Language\LanguageDefinition;

#[Package('inventory')]
class ProductSearchConfigDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_search_config';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductSearchConfigEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductSearchConfigCollection::class;
    }

    public function since(): ?string
    {
        return '6.3.5.0';
    }

    public function getDefaults(): array
    {
        return [
            'andLogic' => true,
            'minSearchLength' => 2,
            'excludedTerms' => [],
        ];
    }

    public function getHydratorClass(): string
    {
        return ProductSearchConfigHydrator::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new Required()),
            (new BoolField('and_logic', 'andLogic'))->addFlags(new Required()),
            (new IntField('min_search_length', 'minSearchLength'))->addFlags(new Required()),
            new ListField('excluded_terms', 'excludedTerms', StringField::class),
            new OneToOneAssociationField('language', 'language_id', 'id', LanguageDefinition::class, false),
            (new OneToManyAssociationField('configFields', ProductSearchConfigFieldDefinition::class, 'product_search_config_id', 'id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
