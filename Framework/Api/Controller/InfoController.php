<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\Controller;

use Cicada\Core\Content\Flow\Api\FlowActionCollector;
use Cicada\Core\Framework\Api\ApiDefinition\DefinitionService;
use Cicada\Core\Framework\Api\ApiDefinition\Generator\EntitySchemaGenerator;
use Cicada\Core\Framework\Api\ApiDefinition\Generator\OpenApi3Generator;
use Cicada\Core\Framework\Api\ApiException;
use Cicada\Core\Framework\Api\Route\ApiRouteInfoResolver;
use Cicada\Core\Framework\Api\Route\RouteInfo;
use Cicada\Core\Framework\Bundle;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\BusinessEventCollector;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Increment\Exception\IncrementGatewayNotFoundException;
use Cicada\Core\Framework\Increment\IncrementGatewayRegistry;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin;
use Cicada\Core\Framework\Store\InAppPurchase;
use Cicada\Core\Kernel;
use Cicada\Core\Maintenance\Staging\Event\SetupStagingEvent;
use Cicada\Core\Maintenance\System\Service\AppUrlVerifier;
use Cicada\Core\PlatformRequest;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('core')]
class InfoController extends AbstractController
{
    private const API_SCOPE_ADMIN = 'api';

    /**
     * @internal
     */
    public function __construct(
        private readonly DefinitionService $definitionService,
        private readonly ParameterBagInterface $params,
        private readonly Kernel $kernel,
        private readonly Packages $packages,
        private readonly BusinessEventCollector $eventCollector,
        private readonly IncrementGatewayRegistry $incrementGatewayRegistry,
        private readonly Connection $connection,
        private readonly AppUrlVerifier $appUrlVerifier,
        private readonly RouterInterface $router,
        private readonly FlowActionCollector $flowActionCollector,
        private readonly SystemConfigService $systemConfigService,
        private readonly ApiRouteInfoResolver $apiRouteInfoResolver,
        private readonly InAppPurchase $inAppPurchase,
    ) {
    }

    #[Route(
        path: '/api/_info/openapi3.json',
        name: 'api.info.openapi3',
        defaults: ['auth_required' => '%cicada.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function info(Request $request): JsonResponse
    {
        $type = $request->query->getAlpha('type', DefinitionService::TYPE_JSON_API);

        $apiType = $this->definitionService->toApiType($type);
        if ($apiType === null) {
            throw ApiException::invalidApiType($type);
        }

        $data = $this->definitionService->generate(OpenApi3Generator::FORMAT, DefinitionService::API, $apiType);

        return new JsonResponse($data);
    }

    #[Route(path: '/api/_info/queue.json', name: 'api.info.queue', methods: ['GET'])]
    public function queue(): JsonResponse
    {
        try {
            $gateway = $this->incrementGatewayRegistry->get(IncrementGatewayRegistry::MESSAGE_QUEUE_POOL);
        } catch (IncrementGatewayNotFoundException) {
            // In case message_queue pool is disabled
            return new JsonResponse([]);
        }

        // Fetch unlimited message_queue_stats
        $entries = $gateway->list('message_queue_stats', -1);

        return new JsonResponse(array_map(static fn (array $entry) => [
            'name' => $entry['key'],
            'size' => (int) $entry['count'],
        ], array_values($entries)));
    }

    #[Route(
        path: '/api/_info/open-api-schema.json',
        name: 'api.info.open-api-schema',
        defaults: ['auth_required' => '%cicada.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function openApiSchema(): JsonResponse
    {
        $data = $this->definitionService->getSchema(OpenApi3Generator::FORMAT);

        return new JsonResponse($data);
    }

    #[Route(path: '/api/_info/entity-schema.json', name: 'api.info.entity-schema', methods: ['GET'])]
    public function entitySchema(): JsonResponse
    {
        $data = $this->definitionService->getSchema(EntitySchemaGenerator::FORMAT);

        return new JsonResponse($data);
    }

    #[Route(path: '/api/_info/events.json', name: 'api.info.business-events', methods: ['GET'])]
    public function businessEvents(Context $context): JsonResponse
    {
        $events = $this->eventCollector->collect($context);

        return new JsonResponse($events);
    }

    /**
     * @deprecated tag:v6.7.0 - Will be removed in v6.7.0. Use api.info.stoplightio instead
     */
    #[Route(
        path: '/api/_info/swagger.html',
        name: 'api.info.swagger',
        defaults: ['auth_required' => '%cicada.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function infoHtml(Request $request): Response
    {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            'Route "/api/_info/swagger.html" is deprecated. Use "/api/_info/stoplightio.html" instead.'
        );

        $nonce = $request->attributes->get(PlatformRequest::ATTRIBUTE_CSP_NONCE);
        $apiType = $request->query->getAlpha('type', DefinitionService::TYPE_JSON);
        $response = $this->render(
            '@Framework/swagger.html.twig',
            [
                'schemaUrl' => 'api.info.openapi3',
                'cspNonce' => $nonce,
                'apiType' => $apiType,
            ]
        );

        $cspTemplate = trim($this->params->get('cicada.security.csp_templates')['administration'] ?? '');
        if ($cspTemplate !== '') {
            $csp = str_replace(['%nonce%', "\n", "\r"], [$nonce, ' ', ' '], $cspTemplate);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    #[Route(
        path: '/api/_info/stoplightio.html',
        name: 'api.info.stoplightio',
        defaults: ['auth_required' => '%cicada.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function stoplightIoInfoHtml(Request $request): Response
    {
        $nonce = $request->attributes->get(PlatformRequest::ATTRIBUTE_CSP_NONCE);
        $apiType = $request->query->getAlpha('type', DefinitionService::TYPE_JSON);
        $response = $this->render(
            '@Framework/stoplightio.html.twig',
            [
                'schemaUrl' => 'api.info.openapi3',
                'cspNonce' => $nonce,
                'apiType' => $apiType,
            ]
        );

        $cspTemplate = trim($this->params->get('cicada.security.csp_templates')['administration'] ?? '');
        if ($cspTemplate !== '') {
            $csp = str_replace(['%nonce%', "\n", "\r"], [$nonce, ' ', ' '], $cspTemplate);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    #[Route(path: '/api/_info/config', name: 'api.info.config', methods: ['GET'])]
    public function config(Context $context, Request $request): JsonResponse
    {
        return new JsonResponse([
            'version' => $this->getCicadaVersion(),
            'versionRevision' => $this->params->get('kernel.cicada_version_revision'),
            'adminWorker' => [
                'enableAdminWorker' => $this->params->get('cicada.admin_worker.enable_admin_worker'),
                'enableQueueStatsWorker' => $this->params->get('cicada.admin_worker.enable_queue_stats_worker'),
                'enableNotificationWorker' => $this->params->get('cicada.admin_worker.enable_notification_worker'),
                'transports' => $this->params->get('cicada.admin_worker.transports'),
            ],
            'bundles' => $this->getBundles(),
            'settings' => [
                'enableUrlFeature' => $this->params->get('cicada.media.enable_url_upload_feature'),
                'appUrlReachable' => $this->appUrlVerifier->isAppUrlReachable($request),
                'appsRequireAppUrl' => $this->appUrlVerifier->hasAppsThatNeedAppUrl(),
                'private_allowed_extensions' => $this->params->get('cicada.filesystem.private_allowed_extensions'),
                'enableHtmlSanitizer' => $this->params->get('cicada.html_sanitizer.enabled'),
                'enableStagingMode' => $this->params->get('cicada.staging.administration.show_banner') && $this->systemConfigService->getBool(SetupStagingEvent::CONFIG_FLAG),
                'disableExtensionManagement' => !$this->params->get('cicada.deployment.runtime_extension_management'),
            ],
            'inAppPurchases' => $this->inAppPurchase->all(),
        ]);
    }

    #[Route(path: '/api/_info/version', name: 'api.info.cicada.version', methods: ['GET'])]
    #[Route(path: '/api/v1/_info/version', name: 'api.info.cicada.version_old_version', methods: ['GET'])]
    public function infoCicadaVersion(): JsonResponse
    {
        return new JsonResponse([
            'version' => $this->getCicadaVersion(),
        ]);
    }

    #[Route(path: '/api/_info/flow-actions.json', name: 'api.info.actions', methods: ['GET'])]
    public function flowActions(Context $context): JsonResponse
    {
        return new JsonResponse($this->flowActionCollector->collect($context));
    }

    #[Route(
        path: '/api/_info/routes',
        name: 'api.info.routes',
        defaults: ['auth_required' => '%cicada.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function getRoutes(): JsonResponse
    {
        $endpoints = array_map(
            static fn (RouteInfo $endpoint) => ['path' => $endpoint->path, 'methods' => $endpoint->methods],
            $this->apiRouteInfoResolver->getApiRoutes(self::API_SCOPE_ADMIN)
        );

        return new JsonResponse(['endpoints' => $endpoints]);
    }

    /**
     * @return array<string, array{
     *     type: 'plugin',
     *     css: list<string>,
     *     js: list<string>,
     *     baseUrl: ?string
     * }|array{
     *     type: 'app',
     *     name: string,
     *     active: bool,
     *     integrationId: string,
     *     baseUrl: string,
     *     version: string,
     *     permissions: array<string, list<string>>
     * }>
     */
    private function getBundles(): array
    {
        $assets = [];
        $package = $this->packages->getPackage('asset');

        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof Bundle) {
                continue;
            }

            $bundleDirectoryName = preg_replace('/bundle$/', '', mb_strtolower($bundle->getName()));
            if ($bundleDirectoryName === null) {
                throw ApiException::unableGenerateBundle($bundle->getName());
            }

            $styles = array_map(static function (string $filename) use ($package, $bundleDirectoryName) {
                $url = 'bundles/' . $bundleDirectoryName . '/' . $filename;

                return $package->getUrl($url);
            }, $this->getAdministrationStyles($bundle));

            $scripts = array_map(static function (string $filename) use ($package, $bundleDirectoryName) {
                $url = 'bundles/' . $bundleDirectoryName . '/' . $filename;

                return $package->getUrl($url);
            }, $this->getAdministrationScripts($bundle));

            $baseUrl = $this->getBaseUrl($bundle);

            if (empty($styles) && empty($scripts) && $baseUrl === null) {
                continue;
            }

            $assets[$bundle->getName()] = [
                'css' => $styles,
                'js' => $scripts,
                'baseUrl' => $baseUrl,
                'type' => 'plugin',
            ];
        }

        foreach ($this->getActiveApps() as $app) {
            $assets[$app['name']] = [
                'active' => (bool) $app['active'],
                'integrationId' => $app['integrationId'],
                'type' => 'app',
                'baseUrl' => $app['baseUrl'],
                'permissions' => $app['privileges'],
                'version' => $app['version'],
                'name' => $app['name'],
            ];
        }

        return $assets;
    }

    /**
     * @return list<string>
     */
    private function getAdministrationStyles(Bundle $bundle): array
    {
        $path = 'administration/css/' . str_replace('_', '-', $bundle->getContainerPrefix()) . '.css';
        $bundlePath = $bundle->getPath();

        if (!file_exists($bundlePath . '/Resources/public/' . $path) && !file_exists($bundlePath . '/Resources/.administration-css')) {
            return [];
        }

        return [$path];
    }

    /**
     * @return list<string>
     */
    private function getAdministrationScripts(Bundle $bundle): array
    {
        $path = 'administration/js/' . str_replace('_', '-', $bundle->getContainerPrefix()) . '.js';
        $bundlePath = $bundle->getPath();

        if (!file_exists($bundlePath . '/Resources/public/' . $path) && !file_exists($bundlePath . '/Resources/.administration-js')) {
            return [];
        }

        return [$path];
    }

    private function getBaseUrl(Bundle $bundle): ?string
    {
        if (!$bundle instanceof Plugin) {
            return null;
        }

        if ($bundle->getAdminBaseUrl()) {
            return $bundle->getAdminBaseUrl();
        }

        $defaultEntryFile = 'administration/index.html';
        $bundlePath = $bundle->getPath();

        if (!file_exists($bundlePath . '/Resources/public/' . $defaultEntryFile)) {
            return null;
        }

        // exception is possible as the administration is an optional dependency
        try {
            return $this->router->generate(
                'administration.plugin.index',
                [
                    'pluginName' => \mb_strtolower($bundle->getName()),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<array{name: string, active: int, integrationId: string, baseUrl: string, version: string, privileges: array<string, list<string>>}>
     */
    private function getActiveApps(): array
    {
        /** @var list<array{name: string, active: int, integrationId: string, baseUrl: string, version: string, privileges: ?string}> $apps */
        $apps = $this->connection->fetchAllAssociative('SELECT
    app.name,
    app.active,
    LOWER(HEX(app.integration_id)) as integrationId,
    app.base_app_url as baseUrl,
    app.version,
    ar.privileges as privileges
FROM app
LEFT JOIN acl_role ar on app.acl_role_id = ar.id
WHERE app.active = 1 AND app.base_app_url is not null');

        return array_map(static function (array $item) {
            $privileges = $item['privileges'] ? json_decode((string) $item['privileges'], true, 512, \JSON_THROW_ON_ERROR) : [];

            $item['privileges'] = [];

            foreach ($privileges as $privilege) {
                if (substr_count($privilege, ':') !== 1) {
                    $item['privileges']['additional'][] = $privilege;

                    continue;
                }

                [$entity, $key] = \explode(':', $privilege);
                $item['privileges'][$key][] = $entity;
            }

            return $item;
        }, $apps);
    }

    private function getCicadaVersion(): string
    {
        $cicadaVersion = $this->params->get('kernel.cicada_version');
        if ($cicadaVersion === Kernel::CICADA_FALLBACK_VERSION) {
            $cicadaVersion = str_replace('.9999999-dev', '.9999999.9999999-dev', $cicadaVersion);
        }

        return $cicadaVersion;
    }
}
