<?php declare(strict_types=1);

namespace Cicada\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MailTemplateTranslationEntity>
 */
#[Package('after-sales')]
class MailTemplateTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getMailTemplateIds(): array
    {
        return $this->fmap(fn (MailTemplateTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getMailTemplateId());
    }

    public function filterByMailTemplateId(string $id): self
    {
        return $this->filter(fn (MailTemplateTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getMailTemplateId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (MailTemplateTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (MailTemplateTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'mail_template_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return MailTemplateTranslationEntity::class;
    }
}
