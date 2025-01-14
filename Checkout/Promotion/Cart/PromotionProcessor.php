<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Cart;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Cart\CartBehavior;
use Cicada\Core\Checkout\Cart\CartException;
use Cicada\Core\Checkout\Cart\CartProcessorInterface;
use Cicada\Core\Checkout\Cart\LineItem\CartDataCollection;
use Cicada\Core\Checkout\Cart\LineItem\Group\LineItemGroupBuilder;
use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Checkout\Cart\LineItem\LineItemCollection;
use Cicada\Core\Checkout\Promotion\Cart\Error\AutoPromotionNotFoundError;
use Cicada\Core\Checkout\Promotion\Cart\Error\PromotionsOnCartPriceZeroError;
use Cicada\Core\Checkout\Promotion\Exception\InvalidPriceDefinitionException;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Profiling\Profiler;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class PromotionProcessor implements CartProcessorInterface
{
    final public const DATA_KEY = 'promotions';
    final public const LINE_ITEM_TYPE = 'promotion';

    final public const SKIP_PROMOTION = 'skipPromotion';

    /**
     * @internal
     */
    public function __construct(
        private readonly PromotionCalculator $promotionCalculator,
        private readonly LineItemGroupBuilder $groupBuilder
    ) {
    }

    /**
     * @throws CartException
     * @throws InvalidPriceDefinitionException
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::promotion::process', function () use ($data, $original, $toCalculate, $context, $behavior): void {
            // always make sure we have
            // the line item group builder for our
            // line item group rule inside the cart data
            $toCalculate->getData()->set(LineItemGroupBuilder::class, $this->groupBuilder);

            // if we are in recalculation,
            // we must not re-add any promotions. just leave it as it is.
            if ($behavior->hasPermission(self::SKIP_PROMOTION)) {
                $items = $original->getLineItems()->filterType(self::LINE_ITEM_TYPE);
                foreach ($items as $item) {
                    $toCalculate->add($item);
                }

                return;
            }

            // if there is no collected promotion we may return - nothing to calculate!
            if (!$data->has(self::DATA_KEY)) {
                $lineItemPromotions = $original->getLineItems()->filterType(self::LINE_ITEM_TYPE);
                foreach ($lineItemPromotions as $lineItemPromotion) {
                    if (empty($lineItemPromotion->getReferencedId())) {
                        $toCalculate->addErrors(
                            new AutoPromotionNotFoundError($lineItemPromotion->getLabel() ?? $lineItemPromotion->getId())
                        );
                    }
                }

                return;
            }

            /** @var LineItemCollection $discountLineItems */
            $discountLineItems = $data->get(self::DATA_KEY);

            if ($toCalculate->getPrice()->getTotalPrice() === 0.0) {
                // We'll only display the `PromotionsOnCartPriceZeroError` if a promotion code is input and the cart price is zero. Auto-promotions are not considered in this case.
                $discountPromotionsWithCode = $discountLineItems->filter(fn (LineItem $lineItem) => !$lineItem->hasPayloadValue('promotionCodeType') || $lineItem->getPayloadValue('promotionCodeType') !== PromotionItemBuilder::PROMOTION_TYPE_GLOBAL);
                if ($discountPromotionsWithCode->count() === 0) {
                    return;
                }

                $toCalculate->addErrors(
                    new PromotionsOnCartPriceZeroError($discountPromotionsWithCode->fmap(fn (LineItem $lineItem) => $lineItem->getLabel()))
                );

                return;
            }

            // calculate the whole cart with the
            // new list of created promotion discount line items
            $items = new LineItemCollection();
            foreach ($discountLineItems as $lineItem) {
                $lineItem->setShippingCostAware(true);
                $items->add($lineItem);
            }

            $this->promotionCalculator->calculate($items, $original, $toCalculate, $context, $behavior);
        }, 'cart');
    }
}
