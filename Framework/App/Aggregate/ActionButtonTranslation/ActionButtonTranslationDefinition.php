<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Aggregate\ActionButtonTranslation;

use Cicada\Core\Framework\App\Aggregate\ActionButton\ActionButtonDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class ActionButtonTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'app_action_button_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ActionButtonTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ActionButtonTranslationCollection::class;
    }

    public function since(): ?string
    {
        return '6.3.1.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ActionButtonDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required()),
        ]);
    }
}
