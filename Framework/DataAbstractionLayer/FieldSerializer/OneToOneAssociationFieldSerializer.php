<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Cicada\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteCommandExtractor;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('core')]
class OneToOneAssociationFieldSerializer implements FieldSerializerInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly WriteCommandExtractor $writeExtractor
    ) {
    }

    public function normalize(Field $field, array $data, WriteParameterBag $parameters): array
    {
        if (!$field instanceof OneToOneAssociationField) {
            throw DataAbstractionLayerException::invalidSerializerField(OneToOneAssociationField::class, $field);
        }

        $key = $field->getPropertyName();
        $value = $data[$key] ?? null;
        if ($value === null) {
            return $data;
        }

        if (!\is_array($value)) {
            throw DataAbstractionLayerException::expectedArray(
                \sprintf('%s/%s', $parameters->getPath(), $key)
            );
        }

        /** @var Field $keyField */
        $keyField = $parameters->getDefinition()->getFields()->getByStorageName($field->getStorageName());
        $reference = $field->getReferenceDefinition();

        if ($keyField instanceof FkField) {
            $referenceField = $field->getReferenceField();
            $pkField = $reference->getFields()->getByStorageName($referenceField);
            if ($pkField === null) {
                throw DataAbstractionLayerException::definitionFieldDoesNotExist($reference::class, $referenceField);
            }

            // id provided? otherwise set new one to return it and yield the id into the FkField
            if (isset($value[$pkField->getPropertyName()])) {
                $id = $value[$pkField->getPropertyName()];
            } else {
                $id = Uuid::randomHex();
                $value[$pkField->getPropertyName()] = $id;
            }

            $data[$keyField->getPropertyName()] = $id;
        } else {
            $id = $parameters->getContext()->get($parameters->getDefinition()->getEntityName(), $field->getStorageName());
            /** @var Field $keyField */
            $keyField = $reference->getFields()->getByStorageName($field->getReferenceField());

            $value[$keyField->getPropertyName()] = $id;
        }

        $clonedParams = $parameters->cloneForSubresource(
            $field->getReferenceDefinition(),
            $parameters->getPath() . '/' . $key
        );

        $value = $this->writeExtractor->normalizeSingle($field->getReferenceDefinition(), $value, $clonedParams);

        $data[$key] = $value;

        return $data;
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof OneToOneAssociationField) {
            throw DataAbstractionLayerException::invalidSerializerField(OneToOneAssociationField::class, $field);
        }

        if (!\is_array($data->getValue())) {
            throw DataAbstractionLayerException::expectedArray(
                \sprintf('%s/%s', $parameters->getPath(), $data->getKey())
            );
        }

        $reference = $field->getReferenceDefinition();
        $value = $data->getValue();

        $this->writeExtractor->extract(
            $value,
            $parameters->cloneForSubresource(
                $reference,
                $parameters->getPath() . '/' . $data->getKey()
            )
        );

        yield from [];
    }

    public function decode(Field $field, mixed $value): never
    {
        throw DataAbstractionLayerException::decodeHandledByHydrator($field);
    }
}
