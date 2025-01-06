<?php declare(strict_types=1);

namespace Cicada\Core\System\Language;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Locale\LocaleCollection;

/**
 * @extends EntityCollection<LanguageEntity>
 */
#[Package('core')]
class LanguageCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getParentIds(): array
    {
        return $this->fmap(fn (LanguageEntity $language) => $language->getParentId());
    }

    public function filterByParentId(string $id): LanguageCollection
    {
        return $this->filter(fn (LanguageEntity $language) => $language->getParentId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLocaleIds(): array
    {
        return $this->fmap(fn (LanguageEntity $language) => $language->getLocaleId());
    }

    public function filterByLocaleId(string $id): LanguageCollection
    {
        return $this->filter(fn (LanguageEntity $language) => $language->getLocaleId() === $id);
    }

    public function getLocales(): LocaleCollection
    {
        return new LocaleCollection(
            $this->fmap(fn (LanguageEntity $language) => $language->getLocale())
        );
    }

    public function getApiAlias(): string
    {
        return 'language_collection';
    }

    protected function getExpectedClass(): string
    {
        return LanguageEntity::class;
    }
}
