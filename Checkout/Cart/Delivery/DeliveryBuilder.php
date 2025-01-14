<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Delivery;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Cart\CartBehavior;
use Cicada\Core\Checkout\Cart\CartException;
use Cicada\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryDate;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryPosition;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryPositionCollection;
use Cicada\Core\Checkout\Cart\Delivery\Struct\DeliveryTime;
use Cicada\Core\Checkout\Cart\LineItem\CartDataCollection;
use Cicada\Core\Checkout\Cart\LineItem\LineItemCollection;
use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Cicada\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Cicada\Core\Checkout\Shipping\ShippingMethodEntity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class DeliveryBuilder
{
    public function build(Cart $cart, CartDataCollection $data, SalesChannelContext $context, CartBehavior $cartBehavior): DeliveryCollection
    {
        $key = DeliveryProcessor::buildKey($context->getShippingMethod()->getId());

        if (!$data->has($key)) {
            throw CartException::shippingMethodNotFound($context->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        return $this->buildByUsingShippingMethod($cart, $shippingMethod, $context);
    }

    public function buildByUsingShippingMethod(Cart $cart, ShippingMethodEntity $shippingMethod, SalesChannelContext $context): DeliveryCollection
    {
        $delivery = $this->buildSingleDelivery($shippingMethod, $cart->getLineItems(), $context);

        if (!$delivery) {
            return new DeliveryCollection();
        }

        return new DeliveryCollection([$delivery]);
    }

    private function buildSingleDelivery(
        ShippingMethodEntity $shippingMethod,
        LineItemCollection $collection,
        SalesChannelContext $context
    ): ?Delivery {
        $positions = new DeliveryPositionCollection();
        $deliveryTime = null;
        // use shipping method delivery time as default
        if ($shippingMethod->getDeliveryTime() !== null) {
            $deliveryTime = DeliveryTime::createFromEntity($shippingMethod->getDeliveryTime());
        }

        $this->buildPositions($collection, $positions, $deliveryTime);

        if (!($positions->first() instanceof DeliveryPosition)) {
            return null;
        }

        $maxDeliveryDate = $positions->first()->getDeliveryDate();

        return new Delivery(
            $positions,
            $this->getDeliveryDateByPositions($positions, $maxDeliveryDate),
            $shippingMethod,
            $context->getShippingLocation(),
            new CalculatedPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection())
        );
    }

    private function getDeliveryDateByPositions(DeliveryPositionCollection $positions, DeliveryDate $max): DeliveryDate
    {
        foreach ($positions as $position) {
            $date = $position->getDeliveryDate();

            // detect the latest delivery date
            $earliest = $max->getEarliest() > $date->getEarliest() ? $max->getEarliest() : $date->getEarliest();

            $latest = $max->getLatest() > $date->getLatest() ? $max->getLatest() : $date->getLatest();

            // if earliest and latest is same date, add one day buffer
            if ($earliest->format('Y-m-d') === $latest->format('Y-m-d')) {
                $latest = $latest->add(new \DateInterval('P1D'));
            }

            $max = new DeliveryDate($earliest, $latest);
        }

        return $max;
    }

    private function buildPositions(
        LineItemCollection $items,
        DeliveryPositionCollection $positions,
        ?DeliveryTime $default
    ): void {
        foreach ($items as $item) {
            if (!$item->isShippingCostAware()) {
                continue;
            }

            if ($item->getDeliveryInformation() === null) {
                if ($item->getChildren()->count() > 0) {
                    $this->buildPositions($item->getChildren(), $positions, $default);

                    continue;
                }

                if ($default && $item->getPrice()) {
                    $positions->add(new DeliveryPosition($item->getId(), clone $item, $item->getQuantity(), clone $item->getPrice(), DeliveryDate::createFromDeliveryTime($default)));
                }

                continue;
            }

            // each line item can override the delivery time
            $deliveryTime = $default;
            if ($item->getDeliveryInformation()->getDeliveryTime()) {
                $deliveryTime = $item->getDeliveryInformation()->getDeliveryTime();
            }

            if ($deliveryTime === null) {
                continue;
            }

            // create the estimated delivery date by detected delivery time
            $deliveryDate = DeliveryDate::createFromDeliveryTime($deliveryTime);

            // create a restock date based on the detected delivery time
            $restockDate = DeliveryDate::createFromDeliveryTime($deliveryTime);

            $restockTime = $item->getDeliveryInformation()->getRestockTime();

            // if the line item has a restock time, add this days to the restock date
            if ($restockTime) {
                $restockDate = $restockDate->add(new \DateInterval('P' . $restockTime . 'D'));
            }

            if ($item->getPrice() === null) {
                continue;
            }

            // if the item is completely in stock, use the delivery date
            if ($item->getDeliveryInformation()->getStock() >= $item->getQuantity()) {
                $position = new DeliveryPosition($item->getId(), clone $item, $item->getQuantity(), clone $item->getPrice(), $deliveryDate);
            } else {
                // otherwise use the restock date as delivery date
                $position = new DeliveryPosition($item->getId(), clone $item, $item->getQuantity(), clone $item->getPrice(), $restockDate);
            }

            $positions->add($position);
        }
    }
}
