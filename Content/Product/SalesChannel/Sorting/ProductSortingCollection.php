<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Sorting;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductSortingEntity>
 */
#[Package('inventory')]
class ProductSortingCollection extends EntityCollection
{
    /**
     * @param string[] $keys
     */
    public function sortByKeyArray(array $keys): void
    {
        $sorted = [];

        foreach ($keys as $key) {
            $sorting = $this->getByKey($key);
            if ($sorting !== null) {
                $sorted[$sorting->getId()] = $this->elements[$sorting->getId()];
            }
        }

        $this->elements = $sorted;
    }

    public function getByKey(string $key): ?ProductSortingEntity
    {
        return $this->filterByProperty('key', $key)->first();
    }

    public function removeByKey(string $key): void
    {
        foreach ($this->elements as $element) {
            if ($element->getKey() === $key) {
                $this->remove($element->getId());
            }
        }
    }

    public function getApiAlias(): string
    {
        return 'product_sorting_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductSortingEntity::class;
    }
}
