<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\CrossSelling;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route will be used to load all cross-selling lists of the provided product id
 */
#[Package('inventory')]
abstract class AbstractProductCrossSellingRoute
{
    abstract public function getDecorated(): AbstractProductCrossSellingRoute;

    abstract public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductCrossSellingRouteResponse;
}
