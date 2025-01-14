<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Search\Filter;

use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('core')]
class ContainsFilter extends SingleFieldFilter
{
    public function __construct(
        protected readonly string $field,
        protected mixed $value
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getFields(): array
    {
        return [$this->field];
    }
}
