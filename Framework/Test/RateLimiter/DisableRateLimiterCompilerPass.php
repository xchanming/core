<?php
declare(strict_types=1);

namespace Cicada\Core\Framework\Test\RateLimiter;

use Cicada\Core\Framework\RateLimiter\NoLimitRateLimiterFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
class DisableRateLimiterCompilerPass implements CompilerPassInterface
{
    private static bool $enabled = true;

    public static function enableNoLimit(): void
    {
        self::$enabled = true;
    }

    public static function disableNoLimit(): void
    {
        self::$enabled = false;
    }

    public static function isDisabled(): bool
    {
        return self::$enabled;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!self::$enabled) {
            return;
        }

        $rateLimiter = $container->getDefinition('cicada.rate_limiter');

        $methodCalls = $rateLimiter->getMethodCalls();
        foreach ($methodCalls as &$methodCall) {
            if ($methodCall[0] !== 'registerLimiterFactory') {
                continue;
            }

            $definition = $methodCall[1][1];
            $decoratorDefinition = new Definition(NoLimitRateLimiterFactory::class);
            $decoratorDefinition->addArgument($definition);

            $methodCall[1][1] = $decoratorDefinition;
        }

        $rateLimiter->setMethodCalls($methodCalls);
    }
}
