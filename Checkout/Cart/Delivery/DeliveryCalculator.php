<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Delivery;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Cart\CartBehavior;
use Cicada\Core\Checkout\Cart\CartException;
use Cicada\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Cicada\Core\Checkout\Cart\LineItem\CartDataCollection;
use Cicada\Core\Checkout\Cart\LineItem\LineItemCollection;
use Cicada\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Cart\Price\Struct\CartPrice;
use Cicada\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Cicada\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceEntity;
use Cicada\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Cicada\Core\Checkout\Shipping\ShippingMethodEntity;
use Cicada\Core\Defaults;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\FloatComparator;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class DeliveryCalculator
{
    final public const CALCULATION_BY_LINE_ITEM_COUNT = 1;

    final public const CALCULATION_BY_PRICE = 2;

    final public const CALCULATION_BY_WEIGHT = 3;

    final public const CALCULATION_BY_VOLUME = 4;

    /**
     * @internal
     */
    public function __construct(
        private readonly QuantityPriceCalculator $priceCalculator,
        private readonly PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
    }

    public function calculate(CartDataCollection $data, Cart $cart, DeliveryCollection $deliveries, SalesChannelContext $context): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($data, $cart, $delivery, $context);
        }
    }

    private function calculateDelivery(CartDataCollection $data, Cart $cart, Delivery $delivery, SalesChannelContext $context): void
    {
        $costs = null;
        $manualShippingCost = $cart->getExtension(DeliveryProcessor::MANUAL_SHIPPING_COSTS);
        $manualShippingCost = $manualShippingCost instanceof CalculatedPrice ? $manualShippingCost : null;
        if ($delivery->getShippingCosts()->getUnitPrice() > 0 || $manualShippingCost) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([
                    new Price(
                        Defaults::CURRENCY,
                        $delivery->getShippingCosts()->getTotalPrice(),
                        $delivery->getShippingCosts()->getTotalPrice(),
                        false
                    ),
                ]),
                $delivery->getPositions()->getLineItems(),
                $context,
                $manualShippingCost
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if (
            $this->hasDeliveryPriceRecalculationSkipWithZeroUnitPrice($cart->getBehavior(), $delivery->getShippingCosts()->getUnitPrice())
            || $this->hasDeliveryWithOnlyShippingFreeItems($delivery)
        ) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([new Price(Defaults::CURRENCY, 0, 0, false)]),
                $delivery->getPositions()->getLineItems(),
                $context
            );
            $delivery->setShippingCosts($costs);

            return;
        }

        $key = DeliveryProcessor::buildKey($delivery->getShippingMethod()->getId());

        if (!$data->has($key)) {
            throw CartException::shippingMethodNotFound($delivery->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        foreach ($context->getRuleIds() as $ruleId) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', $ruleId);

            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
            if ($costs !== null) {
                break;
            }
        }

        // Fetch default price if no rule matched
        if ($costs === null) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', null);
            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
        }

        if (!$costs) {
            $cart->addErrors(
                new ShippingMethodBlockedError((string) $shippingMethod->getTranslation('name'))
            );

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function hasDeliveryWithOnlyShippingFreeItems(Delivery $delivery): bool
    {
        foreach ($delivery->getPositions()->getLineItems()->getIterator() as $lineItem) {
            if ($lineItem->getDeliveryInformation() && !$lineItem->getDeliveryInformation()->getFreeDelivery()) {
                return false;
            }
        }

        return true;
    }

    private function matches(Delivery $delivery, ShippingMethodPriceEntity $shippingMethodPrice, SalesChannelContext $context): bool
    {
        if ($shippingMethodPrice->getCalculationRuleId()) {
            return \in_array($shippingMethodPrice->getCalculationRuleId(), $context->getRuleIds(), true);
        }

        $start = $shippingMethodPrice->getQuantityStart();
        $end = $shippingMethodPrice->getQuantityEnd();

        $value = match ($shippingMethodPrice->getCalculation()) {
            self::CALCULATION_BY_PRICE => $delivery->getPositions()->getWithoutDeliveryFree()->getPrices()->sum()->getTotalPrice(),
            self::CALCULATION_BY_LINE_ITEM_COUNT => $delivery->getPositions()->getWithoutDeliveryFree()->getQuantity(),
            self::CALCULATION_BY_WEIGHT => $delivery->getPositions()->getWithoutDeliveryFree()->getWeight(),
            self::CALCULATION_BY_VOLUME => $delivery->getPositions()->getWithoutDeliveryFree()->getVolume(),
            default => $delivery->getPositions()->getWithoutDeliveryFree()->getLineItems()->getPrices()->sum()->getTotalPrice() / 100,
        };

        // $end (optional) exclusive
        return (!$start || FloatComparator::greaterThanOrEquals($value, $start)) && (!$end || FloatComparator::lessThanOrEquals($value, $end));
    }

    private function calculateShippingCosts(ShippingMethodEntity $shippingMethod, PriceCollection $priceCollection, LineItemCollection $calculatedLineItems, SalesChannelContext $context, ?CalculatedPrice $manualShippingCost = null): CalculatedPrice
    {
        switch ($shippingMethod->getTaxType()) {
            case ShippingMethodEntity::TAX_TYPE_HIGHEST:
                $rules = $calculatedLineItems->getPrices()->getHighestTaxRule();

                break;

            case ShippingMethodEntity::TAX_TYPE_FIXED:
                $taxId = $shippingMethod->getTaxId();

                if ($taxId !== null) {
                    $rules = $context->buildTaxRules($taxId);

                    break;
                }

                // no break
            default:
                $rules = $this->percentageTaxRuleBuilder->buildRules(
                    $calculatedLineItems->getPrices()->sum()
                );
        }

        if ($manualShippingCost !== null) {
            $price = $manualShippingCost->getTotalPrice();
        } else {
            $price = $this->getCurrencyPrice($priceCollection, $context);
        }

        $definition = new QuantityPriceDefinition($price, $rules, 1);

        return $this->priceCalculator->calculate($definition, $context);
    }

    private function getCurrencyPrice(PriceCollection $priceCollection, SalesChannelContext $context): float
    {
        /** @var Price $price */
        $price = $priceCollection->getCurrencyPrice($context->getCurrency()->getId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    private function getMatchingPriceOfRule(Delivery $delivery, SalesChannelContext $context, ShippingMethodPriceCollection $shippingPrices): ?CalculatedPrice
    {
        $shippingPrices->sort(
            function (ShippingMethodPriceEntity $priceEntityA, ShippingMethodPriceEntity $priceEntityB) use ($context) {
                /** @var PriceCollection $priceCollectionA */
                $priceCollectionA = $priceEntityA->getCurrencyPrice();
                $priceA = $this->getCurrencyPrice($priceCollectionA, $context);

                /** @var PriceCollection $priceCollectionB */
                $priceCollectionB = $priceEntityB->getCurrencyPrice();
                $priceB = $this->getCurrencyPrice($priceCollectionB, $context);

                return $priceA <=> $priceB;
            }
        );

        $costs = null;
        foreach ($shippingPrices as $shippingPrice) {
            if (!$this->matches($delivery, $shippingPrice, $context)) {
                continue;
            }
            $price = $shippingPrice->getCurrencyPrice();
            if (!$price) {
                continue;
            }
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                $price,
                $delivery->getPositions()->getLineItems(),
                $context
            );

            break;
        }

        return $costs;
    }

    private function hasDeliveryPriceRecalculationSkipWithZeroUnitPrice(?CartBehavior $behavior, float $unitPrice): bool
    {
        return $behavior
            && $behavior->hasPermission(DeliveryProcessor::SKIP_DELIVERY_PRICE_RECALCULATION)
            && $unitPrice === 0.0;
    }
}
