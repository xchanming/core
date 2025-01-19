<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\DataAbstractionLayer\FieldSerializer;

use Cicada\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use Cicada\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Json;
use Cicada\Core\Framework\Validation\Constraint\Uuid;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[Package('after-sales')]
class FlowTemplateConfigFieldSerializer extends JsonFieldSerializer
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof StorageAware) {
            throw DataAbstractionLayerException::invalidSerializerField(self::class, $field);
        }

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        $value = $data->getValue();

        if (!\is_array($value)) {
            yield $field->getStorageName() => null;

            return;
        }

        $value = array_merge([
            'description' => null,
            'sequences' => [],
        ], $value);

        $sequences = $value['sequences'];

        $value['sequences'] = array_map(fn ($item) => array_merge([
            'parentId' => null,
            'ruleId' => null,
            'position' => 1,
            'displayGroup' => 1,
            'trueCase' => 0,
        ], $item), $sequences);

        yield $field->getStorageName() => Json::encode($value);
    }

    protected function getConstraints(Field $field): array
    {
        return [
            new Collection([
                'allowExtraFields' => true,
                'allowMissingFields' => false,
                'fields' => [
                    'eventName' => [new NotBlank(), new Type('string')],
                    'description' => [new Type('string')],
                    'sequences' => [
                        [
                            new Optional(
                                new Collection([
                                    'allowExtraFields' => true,
                                    'allowMissingFields' => false,
                                    'fields' => [
                                        'id' => [new NotBlank(), new Uuid()],
                                        'actionName' => [new NotBlank(), new Type('string')],
                                        'parentId' => [new Uuid()],
                                        'ruleId' => [new Uuid()],
                                        'position' => [new Type('numeric')],
                                        'trueCase' => [new Type('boolean')],
                                        'displayGroup' => [new Type('numeric')],
                                        'config' => [new Type('array')],
                                    ],
                                ])
                            ),
                        ],
                    ],
                ],
            ]),
        ];
    }
}
