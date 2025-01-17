<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Order;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\CicadaSalesChannelEvent;
use Cicada\Core\Framework\Event\NestedEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class CartConvertedEvent extends NestedEvent implements CicadaSalesChannelEvent
{
    /**
     * @var array<mixed>
     */
    private array $convertedCart;

    /**
     * @param array<mixed> $originalConvertedCart
     */
    public function __construct(
        private readonly Cart $cart,
        private readonly array $originalConvertedCart,
        private readonly SalesChannelContext $salesChannelContext,
        private readonly OrderConversionContext $conversionContext
    ) {
        $this->convertedCart = $originalConvertedCart;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return mixed[]
     */
    public function getOriginalConvertedCart(): array
    {
        return $this->originalConvertedCart;
    }

    /**
     * @return mixed[]
     */
    public function getConvertedCart(): array
    {
        return $this->convertedCart;
    }

    /**
     * @param mixed[] $convertedCart
     */
    public function setConvertedCart(array $convertedCart): void
    {
        $this->convertedCart = $convertedCart;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getConversionContext(): OrderConversionContext
    {
        return $this->conversionContext;
    }
}
