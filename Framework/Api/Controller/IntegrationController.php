<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\Controller;

use Cicada\Core\Framework\Api\Context\AdminApiSource;
use Cicada\Core\Framework\Api\Controller\Exception\PermissionDeniedException;
use Cicada\Core\Framework\Api\Response\ResponseFactoryInterface;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Integration\IntegrationCollection;
use Cicada\Core\System\Integration\IntegrationDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('services-settings')]
class IntegrationController extends AbstractController
{
    /**
     * @internal
     *
     * @param EntityRepository<IntegrationCollection> $integrationRepository
     */
    public function __construct(private readonly EntityRepository $integrationRepository)
    {
    }

    #[Route(path: '/api/integration', name: 'api.integration.create', methods: ['POST'], defaults: ['_acl' => ['integration:create']])]
    public function upsertIntegration(?string $integrationId, Request $request, Context $context, ResponseFactoryInterface $factory): Response
    {
        /** @var AdminApiSource $source */
        $source = $context->getSource();

        $data = $request->request->all();

        // only an admin is allowed to set the admin field
        if (
            !$source->isAdmin()
            && isset($data['admin'])
        ) {
            throw new PermissionDeniedException();
        }

        if (!isset($data['id'])) {
            $data['id'] = null;
        }
        $data['id'] = $integrationId ?: $data['id'];

        $events = $context->scope(Context::SYSTEM_SCOPE, fn (Context $context): EntityWrittenContainerEvent => $this->integrationRepository->upsert([$data], $context));

        $event = $events->getEventByEntityName(IntegrationDefinition::ENTITY_NAME);
        \assert($event !== null);

        $eventIds = $event->getIds();
        $entityId = array_pop($eventIds);

        return $factory->createRedirectResponse($this->integrationRepository->getDefinition(), $entityId, $request, $context);
    }

    #[Route(path: '/api/integration/{integrationId}', name: 'api.integration.update', methods: ['PATCH'], defaults: ['_acl' => ['integration:update']])]
    public function updateIntegration(?string $integrationId, Request $request, Context $context, ResponseFactoryInterface $factory): Response
    {
        return $this->upsertIntegration($integrationId, $request, $context, $factory);
    }
}
