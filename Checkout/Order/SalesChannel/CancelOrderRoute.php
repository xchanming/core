<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Order\SalesChannel;

use Cicada\Core\Checkout\Order\OrderException;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Routing\RoutingException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class CancelOrderRoute extends AbstractCancelOrderRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly OrderService $orderService,
        private readonly EntityRepository $orderRepository
    ) {
    }

    public function getDecorated(): AbstractCancelOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/order/state/cancel', name: 'store-api.order.state.cancel', methods: ['POST'], defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true])]
    public function cancel(Request $request, SalesChannelContext $context): CancelOrderRouteResponse
    {
        $orderId = $request->get('orderId', null);

        if ($orderId === null) {
            throw RoutingException::invalidRequestParameter('orderId');
        }

        $this->verify($orderId, $context);

        $newState = $this->orderService->orderStateTransition(
            $orderId,
            'cancel',
            new ParameterBag(),
            $context->getContext()
        );

        return new CancelOrderRouteResponse($newState);
    }

    private function verify(string $orderId, SalesChannelContext $context): void
    {
        if (!$context->getCustomer()) {
            throw OrderException::customerNotLoggedIn();
        }

        $criteria = new Criteria([$orderId]);
        $criteria->addFilter(new EqualsFilter('orderCustomer.customerId', $context->getCustomerId()));

        if ($this->orderRepository->searchIds($criteria, $context->getContext())->firstId() === null) {
            throw OrderException::orderNotFound($orderId);
        }
    }
}
