<?php declare(strict_types=1);

namespace Cicada\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Field;

use Cicada\Core\Content\ImportExport\ImportExportException;
use Cicada\Core\Content\ImportExport\Struct\Config;
use Cicada\Core\Defaults;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\System\Language\LanguageCollection;

#[Package('core')]
class TranslationsSerializer extends FieldSerializer
{
    /**
     * @param EntityRepository<LanguageCollection> $languageRepository
     *
     * @internal
     */
    public function __construct(private readonly EntityRepository $languageRepository)
    {
    }

    public function serialize(Config $config, Field $associationField, $translations): iterable
    {
        if ($translations === null) {
            return;
        }

        if (!$associationField instanceof TranslationsAssociationField) {
            throw ImportExportException::invalidInstanceType('associationField', TranslationsAssociationField::class);
        }

        if ($translations instanceof EntityCollection) {
            $translations = $translations->jsonSerialize();
        }

        $codedTranslations = [];

        $referenceDefinition = $associationField->getReferenceDefinition();
        $entitySerializer = $this->serializerRegistry->getEntity($referenceDefinition->getEntityName());

        /** @var TranslationEntity $translation */
        foreach ($translations as $languageId => $translation) {
            if ($translation instanceof TranslationEntity) {
                $languageId = $translation->getLanguageId();
            }

            $translationCode = $this->mapToTranslationCode($languageId);
            $result = iterator_to_array($entitySerializer->serialize($config, $referenceDefinition, $translation));

            $codedTranslations[$translationCode] = $result;
            if ($languageId === Defaults::LANGUAGE_SYSTEM) {
                $codedTranslations['DEFAULT'] = $codedTranslations[$translationCode];
            }
        }

        yield $associationField->getPropertyName() => $codedTranslations;
    }

    public function deserialize(Config $config, Field $associationField, $translations): mixed
    {
        if (!$associationField instanceof TranslationsAssociationField) {
            throw ImportExportException::invalidInstanceType('associationField', '*ToOneField');
        }

        $translations = \is_array($translations) ? $translations : iterator_to_array($translations);
        if (isset($translations['DEFAULT'])) {
            $translations[Defaults::LANGUAGE_SYSTEM] = $translations['DEFAULT'];
            unset($translations['DEFAULT']);
        }

        $referenceDefinition = $associationField->getReferenceDefinition();
        $entitySerializer = $this->serializerRegistry->getEntity($referenceDefinition->getEntityName());

        foreach ($translations as $languageId => $translation) {
            $deserialized = $entitySerializer->deserialize($config, $referenceDefinition, $translation);
            if (!\is_array($deserialized) && is_iterable($deserialized)) {
                $deserialized = iterator_to_array($deserialized);
            }

            if (empty($deserialized)) {
                unset($translations[$languageId]);
            } else {
                $translations[$languageId] = $deserialized;
            }
        }

        if (empty($translations)) {
            return null;
        }

        return $translations;
    }

    public function supports(Field $field): bool
    {
        return $field instanceof TranslationsAssociationField;
    }

    private function mapToTranslationCode(string $languageId): string
    {
        if (!Uuid::isValid($languageId)) {
            return $languageId;
        }

        $criteria = (new Criteria([$languageId]))->addAssociation('translationCode');

        $language = $this->languageRepository
            ->search($criteria, Context::createDefaultContext())->getEntities()
            ->first();

        return $language && $language->getTranslationCode() ? $language->getTranslationCode()->getCode() : $languageId;
    }
}
