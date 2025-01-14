<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\LineItem;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Cart\CartValidatorInterface;
use Cicada\Core\Checkout\Cart\Error\ErrorCollection;
use Cicada\Core\Checkout\Cart\Error\IncompleteLineItemError;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class LineItemValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            if ($lineItem->getLabel() === null && $lineItem->getType() !== LineItem::CONTAINER_LINE_ITEM) {
                $errors->add(new IncompleteLineItemError($lineItem->getId(), 'label'));
                $cart->getLineItems()->removeElement($lineItem);
            }

            if ($lineItem->getPrice() === null) {
                $errors->add(new IncompleteLineItemError($lineItem->getId(), 'price'));
                $cart->getLineItems()->removeElement($lineItem);
            }
        }
    }
}
