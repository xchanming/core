<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Routing;

use Cicada\Core\Framework\Adapter\Twig\TemplateScopeDetector;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\PlatformRequest;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[Package('core')]
class RouteParamsCleanupListener
{
    private const CLEANUP_PARAMETERS = [
        PlatformRequest::ATTRIBUTE_ROUTE_SCOPE,
        PlatformRequest::ATTRIBUTE_CAPTCHA,
        PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED,
        PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED_ALLOW_GUEST,
        PlatformRequest::ATTRIBUTE_ACL,
        TemplateScopeDetector::SCOPES_ATTRIBUTE,
    ];

    public function __invoke(RequestEvent $event): void
    {
        $routeParams = $event->getRequest()->attributes->get('_route_params', []);

        if ($routeParams) {
            foreach (self::CLEANUP_PARAMETERS as $param) {
                if (isset($routeParams[$param])) {
                    unset($routeParams[$param]);
                }
            }
        }

        $event->getRequest()->attributes->set('_route_params', $routeParams);
    }
}
