<?php declare(strict_types=1);

namespace Cicada\Core\System\Language;

use Cicada\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Cicada\Core\Framework\Log\Package;
use Doctrine\DBAL\Connection;

/**
 * @phpstan-import-type LanguageData from LanguageLoaderInterface
 */
#[Package('core')]
class LanguageLoader implements LanguageLoaderInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return LanguageData
     */
    public function loadLanguages(): array
    {
        $data = $this->connection->createQueryBuilder()
            ->select('LOWER(HEX(language.id)) AS array_key, LOWER(HEX(language.id)) AS id, locale.code, parentLocale.code AS parentCode, LOWER(HEX(language.parent_id)) parentId')
            ->from('language')
            ->leftJoin('language', 'locale', 'locale', 'language.translation_code_id = locale.id')
            ->leftJoin('language', 'language', 'parentLanguage', 'language.parent_id = parentLanguage.id')
            ->leftJoin('parentLanguage', 'locale', 'parentLocale', 'parentLanguage.translation_code_id = parentLocale.id')
            ->executeQuery()
            ->fetchAllAssociative();

        /** @var LanguageData $grouped */
        $grouped = FetchModeHelper::groupUnique($data);

        return $grouped;
    }
}
