<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Field;

use Cicada\Core\Framework\DataAbstractionLayer\FieldSerializer\IdFieldSerializer;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class IdField extends Field implements StorageAware
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $storageName;

    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        $this->storageName = $storageName;
        parent::__construct($propertyName);
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function getExtractPriority(): int
    {
        return 75;
    }

    protected function getSerializerClass(): string
    {
        return IdFieldSerializer::class;
    }
}
