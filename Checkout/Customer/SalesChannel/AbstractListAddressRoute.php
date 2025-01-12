<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\SalesChannel;

use Cicada\Core\Checkout\Customer\CustomerEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

/**
 * This route can be used to list all addresses of an customer
 */
#[Package('checkout')]
abstract class AbstractListAddressRoute
{
    abstract public function load(Criteria $criteria, SalesChannelContext $context, CustomerEntity $customer): ListAddressRouteResponse;

    abstract public function getDecorated(): AbstractListAddressRoute;
}
