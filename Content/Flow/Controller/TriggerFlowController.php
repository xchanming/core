<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Controller;

use Cicada\Core\Content\Flow\Exception\CustomTriggerByNameNotFoundException;
use Cicada\Core\Framework\App\Event\CustomAppEvent;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class TriggerFlowController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $appFlowEventRepository,
    ) {
    }

    #[Route(path: '/api/_action/trigger-event/{eventName}', name: 'api.action.trigger_event', methods: ['POST'])]
    public function trigger(string $eventName, Request $request, Context $context): JsonResponse
    {
        $data = $request->request->all();

        $this->checkAppEventIsExist($eventName, $context);

        $this->eventDispatcher->dispatch(new CustomAppEvent($eventName, $data, $context), $eventName);

        return new JsonResponse([
            'message' => \sprintf('The trigger `%s`successfully dispatched!', $eventName),
        ], Response::HTTP_OK);
    }

    private function checkAppEventIsExist(string $eventName, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('name', $eventName));
        $criteria->addFilter(new EqualsFilter('app.active', 1));

        $this->appFlowEventRepository->search($criteria, $context)->first() ?? throw new CustomTriggerByNameNotFoundException($eventName);
    }
}
