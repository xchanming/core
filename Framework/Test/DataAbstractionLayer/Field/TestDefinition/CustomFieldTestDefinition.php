<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class CustomFieldTestDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'attribute_test';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function isInheritanceAware(): bool
    {
        return true;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey()),
            (new IdField('parent_id', 'parentId'))->addFlags(new ApiAware(), new PrimaryKey()),
            (new ParentFkField(self::class))->addFlags(new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new Inherited()),
            (new TranslatedField('customTranslated'))->addFlags(new Inherited()),
            (new CustomFields('custom', 'custom'))->addFlags(new Inherited()),
            (new TranslationsAssociationField(CustomFieldTestTranslationDefinition::class, 'attribute_test_id'))->addFlags(new ApiAware()),
            // parent - child inheritance
            (new ParentAssociationField(self::class, 'id'))->addFlags(new ApiAware()),
            (new ChildrenAssociationField(self::class))->addFlags(new ApiAware()),
        ]);
    }
}
