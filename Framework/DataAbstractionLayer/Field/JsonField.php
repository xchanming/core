<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Field;

use Cicada\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder\JsonFieldAccessorBuilder;
use Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class JsonField extends Field implements StorageAware
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $storageName;

    /**
     * @var list<Field>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $propertyMapping;

    /**
     * @var array<mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $default;

    /**
     * @param list<Field> $propertyMapping
     * @param array<mixed>|null $default
     */
    public function __construct(
        string $storageName,
        string $propertyName,
        array $propertyMapping = [],
        ?array $default = null
    ) {
        $this->storageName = $storageName;
        $this->propertyMapping = $propertyMapping;
        $this->default = $default;
        parent::__construct($propertyName);
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    /**
     * @return list<Field>
     */
    public function getPropertyMapping(): array
    {
        return $this->propertyMapping;
    }

    /**
     * @return array<mixed>|null
     */
    public function getDefault(): ?array
    {
        return $this->default;
    }

    protected function getSerializerClass(): string
    {
        return JsonFieldSerializer::class;
    }

    protected function getAccessorBuilderClass(): ?string
    {
        return JsonFieldAccessorBuilder::class;
    }
}
