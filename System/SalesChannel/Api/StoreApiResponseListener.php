<?php declare(strict_types=1);

namespace Cicada\Core\System\SalesChannel\Api;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\StoreApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('core')]
class StoreApiResponseListener implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly StructEncoder $encoder,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['encodeResponse', 10000],
        ];
    }

    public function encodeResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (!$response instanceof StoreApiResponse) {
            return;
        }

        $this->dispatch($event);

        $includes = $event->getRequest()->get('includes', []);

        if (!\is_array($includes)) {
            $includes = explode(',', $includes);
        }

        $fields = new ResponseFields($includes);

        $encoded = $this->encoder->encode($response->getObject(), $fields);

        $event->setResponse(new JsonResponse($encoded, $response->getStatusCode(), $response->headers->all()));
    }

    /**
     * Equivalent to `\Shopware\Core\Framework\Routing\RouteEventSubscriber::render`
     */
    private function dispatch(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_route')) {
            return;
        }

        $name = $request->attributes->get('_route') . '.encode';
        $this->dispatcher->dispatch($event, $name);
    }
}
