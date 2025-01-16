<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Log;

/**
 * @internal
 *
 * @phpstan-type PackageString 'buyers-experience'|'services-settings'|'inventory'|'checkout'|'after-sales'|'framework'|'storefront'|'core'|'administration'|'data-services'|'innovation'|'discovery'|'b2b'|'fundamentals@framework'|'fundamentals@discovery'|'fundamentals@checkout'|'fundamentals@after-sales'
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[Package('core')]
final class Package
{
    public const PACKAGE_TRACE_ATTRIBUTE_KEY = 'pTrace';

    /**
     * @param PackageString $package
     */
    public function __construct(public string $package)
    {
    }

    public static function getPackageName(string $class, bool $tryParentClass = false): ?string
    {
        if (!class_exists($class)) {
            return null;
        }

        $package = self::evaluateAttributes($class);
        if ($package || !$tryParentClass) {
            return $package;
        }

        $parentClass = get_parent_class($class);
        if ($parentClass && $package = self::evaluateAttributes($parentClass)) {
            return $package;
        }

        return null;
    }

    /**
     * @param class-string $class
     */
    private static function evaluateAttributes(string $class): ?string
    {
        $reflection = new \ReflectionClass($class);

        $attrs = $reflection->getAttributes(Package::class);

        if (!empty($attrs)) {
            return $attrs[0]->getArguments()[0] ?? null;
        }

        return null;
    }
}
