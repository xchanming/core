<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\Route;

use Cicada\Core\Framework\Api\ApiException;
use Cicada\Core\Framework\Api\Controller\ApiController;
use Cicada\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\PlatformRequest;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

#[Package('core')]
class ApiRouteLoader extends Loader
{
    /**
     * The dynamic API allows traverse associations over the route path. This key for an option
     * holds the root path for each entity definition without further associations in the path.
     */
    public const DYNAMIC_RESOURCE_ROOT_PATH = 'resourceRootPath';

    private bool $isLoaded = false;

    /**
     * @internal
     */
    public function __construct(private readonly DefinitionInstanceRegistry $definitionRegistry)
    {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw ApiException::apiRoutesAreAlreadyLoaded();
        }

        $routes = new RouteCollection();

        $this->loadAdminRoutes($routes);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $type === 'api';
    }

    private function loadAdminRoutes(RouteCollection $routes): void
    {
        $class = ApiController::class;

        // uuid followed by any number of '/{entity-name}/{uuid}' | '/extensions/{entity-name}/{uuid}' pairs followed by an optional slash
        $detailSuffix = '[0-9a-f]{32}(\/(extensions\/)?[0-9a-zA-Z-]+\/[0-9a-f]{32})*\/?$';

        // '/{uuid}/{entity-name}' | '/{uuid}/extensions/{entity-name}' pairs followed by an optional slash
        $listSuffix = '(\/[0-9a-f]{32}\/(extensions\/)?[0-9a-zA-Z-]+)*\/?$';

        $elements = $this->definitionRegistry->getDefinitions();
        usort($elements, fn (EntityDefinition $a, EntityDefinition $b) => $a->getEntityName() <=> $b->getEntityName());

        foreach ($elements as $definition) {
            $entityName = $definition->getEntityName();
            $resourceName = str_replace('_', '-', $definition->getEntityName());

            // detail routes
            $route = new Route('/api/' . $resourceName . '/{path}');
            $route->setMethods(['GET']);
            $route->setDefault('_controller', $class . '::detail');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $detailSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/' . $resourceName . '/{id}');
            $routes->add('api.' . $entityName . '.detail', $route);

            $route = new Route('/api/' . $resourceName . '/{path}');
            $route->setMethods(['PATCH']);
            $route->setDefault('_controller', $class . '::update');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $detailSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/' . $resourceName . '/{id}');
            $routes->add('api.' . $entityName . '.update', $route);

            $route = new Route('/api/' . $resourceName . '/{path}');
            $route->setMethods(['DELETE']);
            $route->setDefault('_controller', $class . '::delete');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $detailSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/' . $resourceName . '/{id}');
            $routes->add('api.' . $entityName . '.delete', $route);

            // list routes
            $route = new Route('/api/' . $resourceName . '{path}');
            $route->setMethods(['GET']);
            $route->setDefault('_controller', $class . '::list');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $listSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/' . $resourceName);
            $routes->add('api.' . $entityName . '.list', $route);

            $route = new Route('/api/search/' . $resourceName . '{path}');
            $route->setMethods(['POST']);
            $route->setDefault('_controller', $class . '::search');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $listSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/search/' . $resourceName);
            $routes->add('api.' . $entityName . '.search', $route);

            $route = new Route('/api/search-ids/' . $resourceName . '{path}');
            $route->setMethods(['POST']);
            $route->setDefault('_controller', $class . '::searchIds');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $listSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/search-ids/' . $resourceName);
            $routes->add('api.' . $entityName . '.search-ids', $route);

            $route = new Route('/api/aggregate/' . $resourceName . '{path}');
            $route->setMethods(['POST']);
            $route->setDefault('_controller', $class . '::aggregate');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $listSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/aggregate/' . $resourceName);
            $routes->add('api.' . $entityName . '.aggregate', $route);

            $route = new Route('/api/' . $resourceName . '{path}');
            $route->setMethods(['POST']);
            $route->setDefault('_controller', $class . '::create');
            $route->setDefault('entityName', $resourceName);
            $route->setDefault(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, ['api']);
            $route->addRequirements(['path' => $listSuffix, 'version' => '\d+']);
            $route->setOption(self::DYNAMIC_RESOURCE_ROOT_PATH, '/api/' . $resourceName);
            $routes->add('api.' . $entityName . '.create', $route);
        }
    }
}
