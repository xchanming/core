<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Template;

use Cicada\Core\Framework\App\AppDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\AllowEmptyString;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class TemplateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'app_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateCollection::class;
    }

    public function since(): ?string
    {
        return '6.3.1.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new LongTextField('template', 'template'))->addFlags(new Required(), new AllowHtml(false), new AllowEmptyString()),
            (new StringField('path', 'path', 1024))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            (new FkField('app_id', 'appId', AppDefinition::class))->addFlags(new Required()),
            new StringField('hash', 'hash', 32),
            new ManyToOneAssociationField('app', 'app_id', AppDefinition::class),
        ]);
    }
}
