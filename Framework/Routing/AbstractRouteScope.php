<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Routing;

use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;

#[Package('core')]
abstract class AbstractRouteScope
{
    /**
     * @var array<string>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $allowedPaths = [];

    public function isAllowedPath(string $path): bool
    {
        $basePath = explode('/', $path);

        return empty($this->allowedPaths) || \in_array($basePath[1], $this->allowedPaths, true);
    }

    abstract public function isAllowed(Request $request): bool;

    abstract public function getId(): string;

    public function getRoutePrefixes(): array
    {
        return $this->allowedPaths;
    }
}
