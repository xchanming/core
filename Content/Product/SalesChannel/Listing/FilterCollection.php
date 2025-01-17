<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Listing;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\Filter as DALFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Collection;

/**
 * @extends Collection<Filter>
 */
#[Package('inventory')]
class FilterCollection extends Collection
{
    /**
     * @param string|int $key
     * @param Filter|null $element
     */
    public function set($key, $element): void
    {
        if ($element === null) {
            return;
        }

        parent::set($key, $element);
    }

    /**
     * @param Filter $element
     */
    public function add($element): void
    {
        $this->validateType($element);

        $this->elements[$element->getName()] = $element;
    }

    public function blacklist(string $exclude): FilterCollection
    {
        $filtered = new self();
        foreach ($this->elements as $key => $value) {
            if ($exclude === $key) {
                continue;
            }
            $filtered->set($key, $value);
        }

        return $filtered;
    }

    public function filtered(): FilterCollection
    {
        return $this->filter(fn (Filter $filter) => $filter->isFiltered() ? $filter : null);
    }

    /**
     * @return array<DALFilter>
     */
    public function getFilters(): array
    {
        return $this->fmap(fn (Filter $filter) => $filter->getFilter());
    }

    protected function getExpectedClass(): ?string
    {
        return Filter::class;
    }
}
