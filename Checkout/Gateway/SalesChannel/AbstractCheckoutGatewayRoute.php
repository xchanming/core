<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Gateway\SalesChannel;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to get an evaluated result of the checkout gateway.
 * The checkout gateway can be used to validate payment and shipping methods based on the current cart.
 * It allows context based decisions (e.g. filter out a payment method, when the cart total is too high).
 */
#[Package('checkout')]
abstract class AbstractCheckoutGatewayRoute
{
    abstract public function getDecorated(): AbstractCheckoutGatewayRoute;

    abstract public function load(Request $request, Cart $cart, SalesChannelContext $context): CheckoutGatewayRouteResponse;
}
