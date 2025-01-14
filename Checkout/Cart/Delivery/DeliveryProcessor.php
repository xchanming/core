<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Delivery;

use Cicada\Core\Checkout\Cart\Cart;
use Cicada\Core\Checkout\Cart\CartBehavior;
use Cicada\Core\Checkout\Cart\CartDataCollectorInterface;
use Cicada\Core\Checkout\Cart\CartProcessorInterface;
use Cicada\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Cicada\Core\Checkout\Cart\LineItem\CartDataCollection;
use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Profiling\Profiler;
use Cicada\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class DeliveryProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    final public const MANUAL_SHIPPING_COSTS = 'manualShippingCosts';

    final public const SKIP_DELIVERY_PRICE_RECALCULATION = 'skipDeliveryPriceRecalculation';

    final public const SKIP_DELIVERY_TAX_RECALCULATION = 'skipDeliveryTaxRecalculation';

    /**
     * @var DeliveryBuilder
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $builder;

    /**
     * @var DeliveryCalculator
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $deliveryCalculator;

    /**
     * @var EntityRepository
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodRepository;

    /**
     * @internal
     */
    public function __construct(
        DeliveryBuilder $builder,
        DeliveryCalculator $deliveryCalculator,
        EntityRepository $shippingMethodRepository
    ) {
        $this->builder = $builder;
        $this->deliveryCalculator = $deliveryCalculator;
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    public static function buildKey(string $shippingMethodId): string
    {
        return 'shipping-method-' . $shippingMethodId;
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::delivery::collect', function () use ($data, $original, $context): void {
            $default = $context->getShippingMethod()->getId();

            if (!$data->has(self::buildKey($default))) {
                $ids = [$default];
            }

            foreach ($original->getDeliveries() as $delivery) {
                $id = $delivery->getShippingMethod()->getId();

                if (!$data->has(self::buildKey($id))) {
                    $ids[] = $id;
                }
            }

            if (empty($ids)) {
                return;
            }

            $criteria = new Criteria($ids);
            $criteria->addAssociation('prices');
            $criteria->addAssociation('deliveryTime');
            $criteria->addAssociation('tax');
            $criteria->setTitle('cart::shipping-methods');

            $shippingMethods = $this->shippingMethodRepository->search($criteria, $context->getContext());

            foreach ($ids as $id) {
                $key = self::buildKey($id);

                if (!$shippingMethods->has($id)) {
                    continue;
                }

                $data->set($key, $shippingMethods->get($id));
            }
        }, 'cart');
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::delivery::process', function () use ($data, $original, $toCalculate, $context, $behavior): void {
            if ($behavior->hasPermission(self::SKIP_DELIVERY_PRICE_RECALCULATION)) {
                $deliveries = $original->getDeliveries()->filter(function (Delivery $delivery) {
                    return $delivery->getShippingCosts()->getTotalPrice() >= 0;
                });

                $firstDelivery = $deliveries->first();
                if ($firstDelivery === null) {
                    return;
                }

                // Stored original edit shipping cost
                $manualShippingCosts = $toCalculate->getExtension(self::MANUAL_SHIPPING_COSTS) ?? $firstDelivery->getShippingCosts();

                $toCalculate->addExtension(self::MANUAL_SHIPPING_COSTS, $manualShippingCosts);

                if ($manualShippingCosts instanceof CalculatedPrice) {
                    $firstDelivery->setShippingCosts($manualShippingCosts);
                }

                $this->deliveryCalculator->calculate($data, $toCalculate, $deliveries, $context);

                $toCalculate->setDeliveries($deliveries);

                return;
            }

            $deliveries = $this->builder->build($toCalculate, $data, $context, $behavior);
            $manualShippingCosts = $original->getExtension(self::MANUAL_SHIPPING_COSTS);

            if ($manualShippingCosts instanceof CalculatedPrice) {
                $deliveries->first()?->setShippingCosts($manualShippingCosts);
            }

            $this->deliveryCalculator->calculate($data, $toCalculate, $deliveries, $context);

            $toCalculate->setDeliveries($deliveries);
        }, 'cart');
    }
}
