<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Payment\SalesChannel;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load all payment methods of the authenticated sales channel.
 * It is possible to use the query parameter 'onlyAvailable' to load only the available payment methods.
 * When sending this parameter, the payment methods are validated against the active rules.
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('checkout')]
abstract class AbstractPaymentMethodRoute
{
    abstract public function getDecorated(): AbstractPaymentMethodRoute;

    abstract public function load(Request $request, SalesChannelContext $context, Criteria $criteria): PaymentMethodRouteResponse;
}
