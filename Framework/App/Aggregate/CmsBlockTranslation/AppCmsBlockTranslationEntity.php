<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Aggregate\CmsBlockTranslation;

use Cicada\Core\Framework\App\Aggregate\CmsBlock\AppCmsBlockEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Language\LanguageEntity;

/**
 * @internal
 */
#[Package('buyers-experience')]
class AppCmsBlockTranslationEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $label;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appCmsBlockId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $languageId;

    /**
     * @var AppCmsBlockEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appCmsBlock;

    /**
     * @var LanguageEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $language;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getAppCmsBlockId(): string
    {
        return $this->appCmsBlockId;
    }

    public function setAppCmsBlockId(string $appCmsBlockId): void
    {
        $this->appCmsBlockId = $appCmsBlockId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getAppCmsBlock(): ?AppCmsBlockEntity
    {
        return $this->appCmsBlock;
    }

    public function setAppCmsBlock(?AppCmsBlockEntity $appCmsBlock): void
    {
        $this->appCmsBlock = $appCmsBlock;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
