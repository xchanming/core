<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer;

use Cicada\Core\Framework\Api\Context\AdminApiSource;
use Cicada\Core\Framework\Api\Context\SalesChannelApiSource;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\AutoIncrement;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\CustomFields as CustomFieldsAttr;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\ForeignKey;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Inherited;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\ManyToMany;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\ManyToOne;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\OnDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\OneToMany;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\OneToOne;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey as PrimaryKeyAttr;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Protection;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\ReferenceVersion;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Required as RequiredAttr;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Serialized;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\State;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Translations;
use Cicada\Core\Framework\DataAbstractionLayer\Attribute\Version;
use Cicada\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Cicada\Core\Framework\DataAbstractionLayer\Field\AutoIncrementField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Cicada\Core\Framework\DataAbstractionLayer\Field\DateField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\DateIntervalField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field as DalField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\AsArray;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IdField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\IntField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\SerializedField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TimeZoneField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @phpstan-type FieldArray array{type?: string, name?: string, class: class-string<DalField>, flags: array<string, array<string, array<bool|string>|string>|null>, translated: bool, args: list<string|false>}
 */
#[Package('core')]
class AttributeEntityCompiler
{
    private const FIELD_ATTRIBUTES = [
        Translations::class,
        AutoIncrement::class,
        Serialized::class,
        ForeignKey::class,
        Version::class,
        Field::class,
        OneToMany::class,
        ManyToMany::class,
        ManyToOne::class,
        OneToOne::class,
        State::class,
        ReferenceVersion::class,
        CustomFieldsAttr::class,
    ];

    private const ASSOCIATIONS = [
        OneToMany::class,
        ManyToMany::class,
        ManyToOne::class,
        OneToOne::class,
    ];

    private CamelCaseToSnakeCaseNameConverter $converter;

    public function __construct()
    {
        $this->converter = new CamelCaseToSnakeCaseNameConverter();
    }

    /**
     * @param class-string<EntityStruct> $class
     *
     * @return list<array{type: 'entity'|'mapping', since?: string|null, parent: string|null, entity_class: class-string<EntityStruct>, entity_name: string, collection_class?: class-string<EntityCollection<EntityStruct>>, fields: list<FieldArray>, source?: string, reference?: string}>
     */
    public function compile(string $class): array
    {
        $reflection = new \ReflectionClass($class);

        $collection = $reflection->getAttributes(Entity::class);

        if (empty($collection)) {
            return [];
        }

        $instance = $collection[0]->newInstance();

        $properties = $reflection->getProperties();

        $fields = [];
        foreach ($properties as $property) {
            $field = $this->parseField($instance->name, $property);

            if ($field === null) {
                continue;
            }

            $fields[] = $field;

            if ($field['type'] === ManyToMany::TYPE) {
                $definitions[] = $this->mapping($instance->name, $property);
            }
        }

        $definitions[] = [
            'type' => 'entity',
            'since' => $instance->since,
            'parent' => $instance->parent,
            'entity_class' => $class,
            'entity_name' => $instance->name,
            'collection_class' => $instance->collectionClass,
            'fields' => $fields,
        ];

        return $definitions;
    }

    /**
     * @template TClassList of object
     *
     * @param class-string<TClassList> ...$list
     *
     * @return \ReflectionAttribute<TClassList>|null
     */
    private function getAttribute(\ReflectionProperty $property, string ...$list): ?\ReflectionAttribute
    {
        foreach ($list as $attribute) {
            $attribute = $property->getAttributes($attribute);
            if (!empty($attribute)) {
                return $attribute[0];
            }
        }

        return null;
    }

    /**
     * @return array{type: string, name: string, class: class-string<DalField>, flags: array<string, array<string, array<bool|string>|string>|null>, translated: bool, args: list<string|false>}|null
     */
    private function parseField(string $entity, \ReflectionProperty $property): ?array
    {
        $attribute = $this->getAttribute($property, ...self::FIELD_ATTRIBUTES);

        if (!$attribute) {
            return null;
        }
        $field = $attribute->newInstance();

        $field->nullable = $property->getType()?->allowsNull() ?? true;

        return [
            'type' => $field->type,
            'name' => $property->getName(),
            'class' => $this->getFieldClass($field),
            'flags' => $this->getFlags($field, $property),
            'translated' => $field->translated,
            'args' => $this->getFieldArgs($entity, $field, $property),
        ];
    }

    /**
     * @return class-string<DalField>
     */
    private function getFieldClass(Field $field): string
    {
        if (is_a($field->type, DalField::class, true)) {
            return $field->type;
        }

        return match ($field->type) {
            FieldType::INT => IntField::class,
            FieldType::TEXT => LongTextField::class,
            FieldType::FLOAT => FloatField::class,
            FieldType::BOOL => BoolField::class,
            FieldType::DATETIME => DateTimeField::class,
            FieldType::UUID => IdField::class,
            AutoIncrement::TYPE => AutoIncrementField::class,
            CustomFieldsAttr::TYPE => CustomFields::class,
            Serialized::TYPE => SerializedField::class,
            FieldType::JSON => JsonField::class,
            FieldType::DATE => DateField::class,
            FieldType::DATE_INTERVAL => DateIntervalField::class,
            FieldType::TIME_ZONE => TimeZoneField::class,
            OneToMany::TYPE => OneToManyAssociationField::class,
            OneToOne::TYPE => OneToOneAssociationField::class,
            ManyToOne::TYPE => ManyToOneAssociationField::class,
            ManyToMany::TYPE => ManyToManyAssociationField::class,
            ForeignKey::TYPE => FkField::class,
            State::TYPE => StateMachineStateField::class,
            Version::TYPE => VersionField::class,
            ReferenceVersion::TYPE => ReferenceVersionField::class,
            Translations::TYPE => TranslationsAssociationField::class,
            default => StringField::class,
        };
    }

    /**
     * @return list<mixed>
     */
    private function getFieldArgs(string $entity, OneToMany|ManyToMany|ManyToOne|OneToOne|Field|Serialized|AutoIncrement $field, \ReflectionProperty $property): array
    {
        if ($field->column) {
            $column = $field->column;
        } else {
            $column = $this->converter->normalize($property->getName());
        }

        $fk = $column . '_id';

        return match (true) {
            $field instanceof State => [$column, $property->getName(), $field->machine, $field->scopes],
            $field instanceof Translations => [$entity . '_translation', $entity . '_id'],
            $field instanceof ForeignKey => [$column, $property->getName(), $field->entity],
            $field instanceof OneToOne => [$property->getName(), $fk, $field->ref, $field->entity, false],
            $field instanceof ManyToOne => [$property->getName(), $fk, $field->entity, $field->ref],
            $field instanceof OneToMany => [$property->getName(), $field->entity, $field->ref, 'id'],
            $field instanceof ManyToMany => [$property->getName(), $field->entity, self::mappingName($entity, $field), $entity . '_id', $field->entity . '_id'],
            $field instanceof AutoIncrement, $field instanceof Version => [],
            $field instanceof ReferenceVersion => [$field->entity, $column],
            $field instanceof Serialized => [$column, $property->getName(), $field->serializer],
            default => [$column, $property->getName()],
        };
    }

    private static function mappingName(string $entity, ManyToMany $field): string
    {
        $items = [$entity, $field->entity];
        sort($items);

        return implode('_', $items);
    }

    /**
     * @return array<string, array{class: string, args?: array<bool|string>}>
     */
    private function getFlags(Field $field, \ReflectionProperty $property): array
    {
        $flags = [];

        if (!$field->nullable) {
            $flags[Required::class] = ['class' => Required::class];
        }

        if ($this->getAttribute($property, RequiredAttr::class)) {
            $flags[Required::class] = ['class' => Required::class];
        }

        if ($this->getAttribute($property, PrimaryKeyAttr::class)) {
            $flags[PrimaryKey::class] = ['class' => PrimaryKey::class];
            $flags[Required::class] = ['class' => Required::class];
        }

        if ($inherited = $this->getAttribute($property, Inherited::class)) {
            $instance = $inherited->newInstance();
            $flags[Inherited::class] = ['class' => Inherited::class, 'args' => ['reversed' => $instance->reversed]];
        }

        if ($field->api !== false) {
            $aware = [];
            if (\is_array($field->api)) {
                if (isset($field->api['admin-api']) && $field->api['admin-api'] === true) {
                    $aware[] = AdminApiSource::class;
                }
                if (isset($field->api['store-api']) && $field->api['store-api'] === true) {
                    $aware[] = SalesChannelApiSource::class;
                }
            }

            $flags[ApiAware::class] = ['class' => ApiAware::class, 'args' => $aware];
        }

        if ($protection = $this->getAttribute($property, Protection::class)) {
            $protection = $protection->newInstance();

            $flags[WriteProtected::class] = ['class' => WriteProtected::class, 'args' => $protection->write];
        }

        if ($this->getAttribute($property, ManyToMany::class, OneToMany::class, Translations::class)) {
            $type = $property->getType();
            if ($type instanceof \ReflectionNamedType && $type->getName() === 'array') {
                $flags[AsArray::class] = ['class' => AsArray::class];
            }
        }

        if ($this->getAttribute($property, ReferenceVersion::class)) {
            $flags[Required::class] = ['class' => Required::class];
        }

        if ($association = $this->getAttribute($property, ...self::ASSOCIATIONS)) {
            $association = $association->newInstance();

            $flags['cascade'] = match ($association->onDelete) {
                OnDelete::CASCADE => ['class' => CascadeDelete::class],
                OnDelete::SET_NULL => ['class' => SetNullOnDelete::class],
                OnDelete::RESTRICT => ['class' => RestrictDelete::class],
                default => null,
            };

            if ($flags['cascade'] === null) {
                unset($flags['cascade']);
            }
        }

        if ($field->type === AutoIncrement::TYPE) {
            unset($flags[Required::class]);
        }
        if ($field->type === CustomFieldsAttr::TYPE) {
            unset($flags[Required::class]);
        }

        return $flags;
    }

    /**
     * @return array{type: 'mapping', parent: null, entity_class: class-string<ArrayEntity>, entity_name: string, fields: list<FieldArray>, source: string, reference: string}
     */
    private function mapping(string $entity, \ReflectionProperty $property): array
    {
        $attribute = $this->getAttribute($property, ManyToMany::class);

        if (!$attribute) {
            throw DataAbstractionLayerException::canNotFindAttribute(ManyToMany::class, $property->getName());
        }
        $field = $attribute->newInstance();

        $srcProperty = $this->converter->denormalize($entity);
        $refProperty = $this->converter->denormalize($field->entity);

        $fields = [
            [
                'class' => FkField::class,
                'translated' => false,
                'args' => [$entity . '_id', $srcProperty . 'Id', $entity],
                'flags' => [
                    PrimaryKey::class => ['class' => PrimaryKey::class],
                    Required::class => ['class' => Required::class],
                ],
            ],
            [
                'class' => FkField::class,
                'translated' => false,
                'args' => [$field->entity . '_id', $refProperty . 'Id', $field->entity],
                'flags' => [
                    PrimaryKey::class => ['class' => PrimaryKey::class],
                    Required::class => ['class' => Required::class],
                ],
            ],
            [
                'class' => ManyToOneAssociationField::class,
                'translated' => false,
                'args' => [$srcProperty, $entity . '_id', $entity, 'id'],
                'flags' => [],
            ],
            [
                'class' => ManyToOneAssociationField::class,
                'translated' => false,
                'args' => [$refProperty, $field->entity . '_id', $field->entity, 'id'],
                'flags' => [],
            ],
        ];

        return [
            'type' => 'mapping',
            'parent' => null,
            'entity_class' => ArrayEntity::class,
            'entity_name' => self::mappingName($entity, $field),
            'fields' => $fields,
            'source' => $entity,
            'reference' => $field->entity,
        ];
    }
}
