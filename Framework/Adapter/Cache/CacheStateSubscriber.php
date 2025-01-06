<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Cache;

use Cicada\Core\Checkout\Cart\Event\CartChangedEvent;
use Cicada\Core\Checkout\Cart\SalesChannel\CartService;
use Cicada\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Routing\KernelListenerPriorities;
use Cicada\Core\PlatformRequest;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('core')]
class CacheStateSubscriber implements EventSubscriberInterface
{
    final public const STATE_LOGGED_IN = 'logged-in';

    final public const STATE_CART_FILLED = 'cart-filled';

    /**
     * @internal
     */
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['setStates', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE_POST],
            ],
            CustomerLoginEvent::class => 'login',
            CartChangedEvent::class => 'cartChanged',
        ];
    }

    public function login(CustomerLoginEvent $event): void
    {
        $event->getSalesChannelContext()->addState(self::STATE_LOGGED_IN);
    }

    public function cartChanged(CartChangedEvent $event): void
    {
        $event->getSalesChannelContext()->removeState(self::STATE_CART_FILLED);

        if ($event->getCart()->getLineItems()->count() > 0) {
            $event->getSalesChannelContext()->addState(self::STATE_CART_FILLED);
        }
    }

    public function setStates(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT)) {
            return;
        }

        /** @var SalesChannelContext $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        $cart = $this->cartService->getCart($context->getToken(), $context);

        $context->removeState(self::STATE_LOGGED_IN);

        $context->removeState(self::STATE_CART_FILLED);

        if ($cart->getLineItems()->count() > 0) {
            $context->addState(self::STATE_CART_FILLED);
        }

        if ($context->getCustomer() !== null) {
            $context->addState(self::STATE_LOGGED_IN);
        }
    }
}
