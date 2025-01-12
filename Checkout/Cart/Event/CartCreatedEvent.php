<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Event;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CartCreatedEvent extends Event implements CartEvent
{
    /**
     * @var Cart
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}
