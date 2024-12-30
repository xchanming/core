<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Gateway;

use Cicada\Core\Checkout\Cart\Error\ErrorCollection;
use Cicada\Core\Checkout\Payment\PaymentMethodCollection;
use Cicada\Core\Checkout\Shipping\ShippingMethodCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('checkout')]
final class CheckoutGatewayResponse extends Struct
{
    /**
     * @internal
     */
    public function __construct(
        protected PaymentMethodCollection $availablePaymentMethods,
        protected ShippingMethodCollection $availableShippingMethods,
        protected ErrorCollection $cartErrors,
    ) {
    }

    public function getAvailablePaymentMethods(): PaymentMethodCollection
    {
        return $this->availablePaymentMethods;
    }

    public function setAvailablePaymentMethods(PaymentMethodCollection $availablePaymentMethods): void
    {
        $this->availablePaymentMethods = $availablePaymentMethods;
    }

    public function getAvailableShippingMethods(): ShippingMethodCollection
    {
        return $this->availableShippingMethods;
    }

    public function setAvailableShippingMethods(ShippingMethodCollection $availableShippingMethods): void
    {
        $this->availableShippingMethods = $availableShippingMethods;
    }

    public function getCartErrors(): ErrorCollection
    {
        return $this->cartErrors;
    }

    public function setCartErrors(ErrorCollection $cartErrors): void
    {
        $this->cartErrors = $cartErrors;
    }
}
