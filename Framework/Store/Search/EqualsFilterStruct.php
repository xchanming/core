<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Search;

use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class EqualsFilterStruct extends FilterStruct
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $field;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $value;

    public static function fromArray(array $data): FilterStruct
    {
        $filter = new EqualsFilterStruct();
        $filter->assign($data);

        return $filter;
    }

    /**
     * @return array<string, string>
     */
    public function getQueryParameter(): array
    {
        return [$this->field => $this->value];
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
