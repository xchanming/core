<?php declare(strict_types=1);

namespace Cicada\Core\Content\MailTemplate\Aggregate\MailHeaderFooterTranslation;

use Cicada\Core\Content\MailTemplate\Aggregate\MailHeaderFooter\MailHeaderFooterDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
class MailHeaderFooterTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'mail_header_footer_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return MailHeaderFooterTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return MailHeaderFooterTranslationCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return MailHeaderFooterDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('description', 'description'))->addFlags(new ApiAware()),
            (new LongTextField('header_html', 'headerHtml'))->addFlags(new ApiAware(), new AllowHtml(false)),
            // plain header / footer should still allow usage of html,
            // for example to replace a twig variable with html '<br>' tags via twig functions
            // otherwise something like this `{{ config('core.basicInformation.address')|striptags('<br>')|replace({"<br>":"\n"}) }}`
            // gets replaced to this (which is problematic) `{{ config('core.basicInformation.address')|striptags('')|replace({"":"\n"}) }}`
            (new LongTextField('header_plain', 'headerPlain'))->addFlags(new ApiAware(), new AllowHtml(false)),
            (new LongTextField('footer_html', 'footerHtml'))->addFlags(new ApiAware(), new AllowHtml(false)),
            (new LongTextField('footer_plain', 'footerPlain'))->addFlags(new ApiAware(), new AllowHtml(false)),
        ]);
    }
}
