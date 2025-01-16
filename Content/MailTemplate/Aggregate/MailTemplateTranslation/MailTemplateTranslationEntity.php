<?php declare(strict_types=1);

namespace Cicada\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation;

use Cicada\Core\Content\MailTemplate\MailTemplateEntity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
class MailTemplateTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mailTemplateId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $senderName;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $description;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $subject;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $contentHtml;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $contentPlain;

    /**
     * @var MailTemplateEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mailTemplate;

    public function getMailTemplateId(): string
    {
        return $this->mailTemplateId;
    }

    public function setMailTemplateId(string $mailTemplateId): void
    {
        $this->mailTemplateId = $mailTemplateId;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): void
    {
        $this->senderName = $senderName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): void
    {
        $this->contentHtml = $contentHtml;
    }

    public function getContentPlain(): ?string
    {
        return $this->contentPlain;
    }

    public function setContentPlain(?string $contentPlain): void
    {
        $this->contentPlain = $contentPlain;
    }

    public function getMailTemplate(): ?MailTemplateEntity
    {
        return $this->mailTemplate;
    }

    public function setMailTemplate(MailTemplateEntity $mailTemplate): void
    {
        $this->mailTemplate = $mailTemplate;
    }
}
