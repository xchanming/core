<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Write\Command;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('core')]
class ChangeSet extends Struct
{
    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $state = [];

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $after = [];

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $isDelete;

    public function __construct(
        array $state,
        array $payload,
        bool $isDelete
    ) {
        $this->state = $state;

        // calculate changes
        $changes = array_intersect_key($payload, $state);

        // validate data types
        foreach ($changes as $property => $after) {
            $before = (string) $state[$property];
            $string = (string) $after;
            if ($string === $before) {
                continue;
            }
            $this->after[$property] = $after;
        }
        $this->isDelete = $isDelete;
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBefore(?string $property)
    {
        if ($property) {
            return $this->state[$property] ?? null;
        }

        return $this->state;
    }

    /**
     * @return array|mixed|string|null
     */
    public function getAfter(?string $property)
    {
        if ($property) {
            return $this->after[$property] ?? null;
        }

        return $this->after;
    }

    public function hasChanged(string $property): bool
    {
        return \array_key_exists($property, $this->after) || $this->isDelete;
    }

    public function merge(ChangeSet $changeSet): void
    {
        $this->after = array_merge($this->after, $changeSet->after);
        $this->state = array_merge($this->state, $changeSet->state);
        $this->isDelete = $this->isDelete || $changeSet->isDelete;
    }

    public function getApiAlias(): string
    {
        return 'dal_change_set';
    }
}
