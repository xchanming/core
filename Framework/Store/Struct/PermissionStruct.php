<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Struct;

use Cicada\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class PermissionStruct extends StoreStruct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $entity;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $operation;

    /**
     * @return PermissionStruct
     */
    public static function fromArray(array $data): StoreStruct
    {
        return (new self())->assign($data);
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }
}
