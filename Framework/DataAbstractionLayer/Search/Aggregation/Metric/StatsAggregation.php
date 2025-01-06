<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Aggregation;
use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('core')]
class StatsAggregation extends Aggregation
{
    public function __construct(
        string $name,
        string $field,
        protected readonly bool $max = true,
        protected readonly bool $min = true,
        protected readonly bool $sum = true,
        protected readonly bool $avg = true
    ) {
        parent::__construct($name, $field);
    }

    public function fetchMax(): bool
    {
        return $this->max;
    }

    public function fetchMin(): bool
    {
        return $this->min;
    }

    public function fetchSum(): bool
    {
        return $this->sum;
    }

    public function fetchAvg(): bool
    {
        return $this->avg;
    }
}
