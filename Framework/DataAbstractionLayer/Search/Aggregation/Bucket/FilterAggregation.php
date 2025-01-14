<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Aggregation;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('core')]
class FilterAggregation extends BucketAggregation
{
    /**
     * @param array<Filter> $filter
     */
    public function __construct(
        string $name,
        Aggregation $aggregation,
        protected array $filter
    ) {
        parent::__construct($name, '', $aggregation);
    }

    /**
     * @return array<Filter>
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    public function getField(): string
    {
        return $this->aggregation?->getField() ?? '';
    }

    public function getFields(): array
    {
        $fields = $this->aggregation?->getFields() ?? [];

        foreach ($this->filter as $filter) {
            foreach ($filter->getFields() as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param array<Filter> $filters
     */
    public function addFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $this->filter[] = $filter;
        }
    }
}
