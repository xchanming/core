<?php declare(strict_types=1);

namespace Cicada\Core\System\Locale;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Language\LanguageCollection;
use Cicada\Core\System\Locale\Aggregate\LocaleTranslation\LocaleTranslationCollection;
use Cicada\Core\System\User\UserCollection;

#[Package('discovery')]
class LocaleEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $code;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $territory;

    /**
     * @var LocaleTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var UserCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $users;

    /**
     * @var LanguageCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $languages;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTerritory(): ?string
    {
        return $this->territory;
    }

    public function setTerritory(?string $territory): void
    {
        $this->territory = $territory;
    }

    public function getTranslations(): ?LocaleTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(LocaleTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getUsers(): ?UserCollection
    {
        return $this->users;
    }

    public function setUsers(UserCollection $users): void
    {
        $this->users = $users;
    }

    public function getLanguages(): ?LanguageCollection
    {
        return $this->languages;
    }

    public function setLanguages(LanguageCollection $languages): void
    {
        $this->languages = $languages;
    }
}
