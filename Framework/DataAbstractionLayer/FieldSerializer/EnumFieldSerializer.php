<?php
declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Cicada\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Cicada\Core\Framework\DataAbstractionLayer\Field\EnumField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[Package('core')]
class EnumFieldSerializer extends AbstractFieldSerializer
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $field = $this->checkFieldTypeOrThrowInvalidFieldException($field);

        // First check for type. Prevents `tryFrom()` to throw an exception
        $this->validateIfNeeded($field, $existence, $data, $parameters);

        $enumCase = null;
        if ($data->getValue() !== null) {
            $enumCase = $field->getEnum()::tryFrom($data->getValue());
        }
        $data->setValue($enumCase?->value);
        $this->validateIfNeeded($field, $existence, $data, $parameters);

        yield $field->getStorageName() => $enumCase?->value;
    }

    public function decode(Field $field, mixed $value): ?\BackedEnum
    {
        $field = $this->checkFieldTypeOrThrowInvalidFieldException($field);

        return ($value !== null) ? $field->getEnum()::tryFrom($value) : null;
    }

    /**
     * @param StringField $field
     *
     * @return list<Constraint>
     */
    protected function getConstraints(Field $field): array
    {
        $field = $this->checkFieldTypeOrThrowInvalidFieldException($field);
        $constraints = [
            new AtLeastOneOf([
                new Type($field->getType()),
                new IsNull(),
            ]),
        ];

        if ($field->is(Required::class)) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    private function checkFieldTypeOrThrowInvalidFieldException(Field $field): EnumField
    {
        if (!$field instanceof EnumField) {
            throw DataAbstractionLayerException::invalidSerializerField(EnumField::class, $field);
        }

        return $field;
    }
}
