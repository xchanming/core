<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Aggregate\ProductCrossSelling;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductCrossSellingEntity>
 */
#[Package('inventory')]
class ProductCrossSellingCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_cross_selling_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductCrossSellingEntity::class;
    }
}
