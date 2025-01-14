<?php
declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteCommandExtractor;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('core')]
class ManyToManyAssociationFieldSerializer implements FieldSerializerInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly WriteCommandExtractor $writeExtrator
    ) {
    }

    public function normalize(Field $field, array $data, WriteParameterBag $parameters): array
    {
        if (!$field instanceof ManyToManyAssociationField) {
            throw DataAbstractionLayerException::invalidSerializerField(ManyToManyAssociationField::class, $field);
        }

        $key = $field->getPropertyName();
        $value = $data[$key] ?? null;

        if ($value === null) {
            return $data;
        }

        $referencedDefinition = $field->getMappingDefinition();

        if (!\is_array($value)) {
            throw DataAbstractionLayerException::expectedArray($parameters->getPath() . '/' . $key);
        }

        $mappingAssociation = $this->getMappingAssociation($referencedDefinition, $field);

        foreach ($value as $keyValue => $subResources) {
            $mapped = $subResources;

            if (!\is_array($mapped)) {
                throw DataAbstractionLayerException::expectedArray($parameters->getPath() . '/' . $key . '/' . $keyValue);
            }

            if ($mappingAssociation) {
                $mapped = $this->map($referencedDefinition, $mappingAssociation, $subResources);
            }

            $clonedParams = $parameters->cloneForSubresource(
                $referencedDefinition,
                $parameters->getPath() . '/' . $key . '/' . $keyValue
            );

            $done = [];

            foreach ($mapped as $property => $_) {
                if (\array_key_exists($property, $done)) {
                    continue;
                }
                $f = $referencedDefinition->getFields()->get($property);
                if ($f === null) {
                    continue;
                }
                $mapped = $f->getSerializer()->normalize($f, $mapped, $clonedParams);
                $done[$property] = true;
            }

            foreach ($referencedDefinition->getPrimaryKeys() as $pkField) {
                if (\array_key_exists($pkField->getPropertyName(), $done)) {
                    continue;
                }
                $mapped = $pkField->getSerializer()->normalize($pkField, $mapped, $clonedParams);
                $done[$pkField->getPropertyName()] = true;
            }

            $value[$keyValue] = $mapped;
        }

        $data[$key] = $value;

        return $data;
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof ManyToManyAssociationField) {
            throw DataAbstractionLayerException::invalidSerializerField(ManyToManyAssociationField::class, $field);
        }
        $key = $data->getKey();
        $value = $data->getValue();
        $referencedDefinition = $field->getMappingDefinition();

        if ($value === null) {
            yield from [];

            return;
        }

        if (!\is_array($value)) {
            throw DataAbstractionLayerException::expectedArray($parameters->getPath() . '/' . $key);
        }

        foreach ($value as $keyValue => $subResources) {
            if (!\is_array($subResources)) {
                throw DataAbstractionLayerException::expectedArray($parameters->getPath() . '/' . $key . '/' . $keyValue);
            }

            $this->writeExtrator->extract(
                $subResources,
                $parameters->cloneForSubresource(
                    $referencedDefinition,
                    $parameters->getPath() . '/' . $key . '/' . $keyValue
                )
            );
        }

        yield from [];
    }

    public function decode(Field $field, mixed $value): never
    {
        throw DataAbstractionLayerException::decodeHandledByHydrator($field);
    }

    private function getMappingAssociation(
        EntityDefinition $referencedDefinition,
        ManyToManyAssociationField $field
    ): ?ManyToOneAssociationField {
        $associations = $referencedDefinition->getFields()->filterInstance(ManyToOneAssociationField::class);

        foreach ($associations as $association) {
            \assert($association instanceof ManyToOneAssociationField);
            if ($association->getStorageName() === $field->getMappingReferenceColumn()) {
                return $association;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, array<string, mixed>>
     */
    private function map(EntityDefinition $referencedDefinition, ManyToOneAssociationField $association, array $data): array
    {
        // not only foreign key provided? data is provided as insert or update command
        if (\count($data) > 1) {
            $data['id'] ??= Uuid::randomHex();
            $data['versionId'] = Defaults::LIVE_VERSION;

            return [$association->getPropertyName() => $data];
        }

        // no id provided? data is provided as insert command (like create category in same request with the product)
        if (!isset($data[$association->getReferenceField()])) {
            $data['id'] ??= Uuid::randomHex();
            $data['versionId'] = Defaults::LIVE_VERSION;

            return [$association->getPropertyName() => $data];
        }

        /* only foreign key provided? entity should only be linked. e.g:
            [
                categories => [
                    ['id' => {id}],
                    ['id' => {id}],
                ]
            ]
        */
        $fk = $referencedDefinition->getFields()->getByStorageName(
            $association->getStorageName()
        );

        if (!$fk) {
            if (!Feature::isActive('v6.7.0.0')) {
                Feature::triggerDeprecationOrThrow(
                    'v6.7.0.0',
                    \sprintf(
                        'Foreign key for association "%s" not found. Please add one to "%s"',
                        $association->getPropertyName(),
                        $referencedDefinition::class
                    )
                );

                $data['versionId'] = Defaults::LIVE_VERSION;

                return [$association->getPropertyName() => $data];
            }

            throw DataAbstractionLayerException::foreignKeyNotFoundInDefinition($association->getPropertyName(), $referencedDefinition::class);
        }

        return [
            $fk->getPropertyName() => $data[$association->getReferenceField()],

            // break versioning at many-to-many relations
            $referencedDefinition->getEntityName() . '_version_id' => Defaults::LIVE_VERSION,
        ];
    }
}
