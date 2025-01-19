<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Shipping\SalesChannel;

use Cicada\Core\Checkout\Shipping\Hook\ShippingMethodRouteHook;
use Cicada\Core\Checkout\Shipping\ShippingMethodCollection;
use Cicada\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Script\Execution\ScriptExecutor;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class ShippingMethodRoute extends AbstractShippingMethodRoute
{
    final public const ALL_TAG = 'shipping-method-route';

    /**
     * @param SalesChannelRepository<ShippingMethodCollection> $shippingMethodRepository
     *
     * @internal
     */
    public function __construct(
        private readonly SalesChannelRepository $shippingMethodRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ScriptExecutor $scriptExecutor
    ) {
    }

    public function getDecorated(): AbstractShippingMethodRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public static function buildName(string $salesChannelId): string
    {
        return 'shipping-method-route-' . $salesChannelId;
    }

    #[Route(path: '/store-api/shipping-method', name: 'store-api.shipping.method', methods: ['GET', 'POST'], defaults: ['_entity' => 'shipping_method'])]
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ShippingMethodRouteResponse
    {
        $this->dispatcher->dispatch(new AddCacheTagEvent(
            self::buildName($context->getSalesChannelId())
        ));

        $criteria
            ->addFilter(new EqualsFilter('active', true))
            ->addAssociation('media');

        if (empty($criteria->getSorting())) {
            $criteria->addSorting(new FieldSorting('position'), new FieldSorting('name', FieldSorting::ASCENDING));
        }

        $result = $this->shippingMethodRepository->search($criteria, $context);

        $shippingMethods = $result->getEntities();

        if (Feature::isActive('cache_rework')) {
            $shippingMethods->sortShippingMethodsByPreference($context);
        }

        /**
         * @deprecated tag:v6.7.0 - onlyAvailable flag will be removed, use Cicada\Core\Checkout\Gateway\SalesChannel\CheckoutGatewayRoute  instead
         */
        if ($request->query->getBoolean('onlyAvailable') || $request->request->getBoolean('onlyAvailable')) {
            $shippingMethods = $shippingMethods->filterByActiveRules($context);
        }

        $result->assign(['entities' => $shippingMethods, 'elements' => $shippingMethods, 'total' => $shippingMethods->count()]);

        if (Feature::isActive('cache_rework')) {
            $this->scriptExecutor->execute(new ShippingMethodRouteHook(
                $shippingMethods,
                $request->query->getBoolean('onlyAvailable') || $request->request->getBoolean('onlyAvailable'),
                $context
            ));
        }

        return new ShippingMethodRouteResponse($result);
    }
}
