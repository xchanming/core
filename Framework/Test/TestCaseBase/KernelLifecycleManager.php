<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\TestCaseBase;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Adapter\Database\MySQLFactory;
use Cicada\Core\Framework\Adapter\Kernel\KernelFactory;
use Cicada\Core\Framework\Plugin\KernelPluginLoader\DbalKernelPluginLoader;
use Cicada\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use Cicada\Core\Framework\Test\Filesystem\Adapter\MemoryAdapterFactory;
use Cicada\Core\Framework\Test\TestCaseHelper\TestBrowser;
use Cicada\Core\Kernel;
use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class KernelLifecycleManager
{
    /**
     * @var class-string<Kernel>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected static $class;

    /**
     * @var Kernel|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected static $kernel;

    /**
     * @var ClassLoader
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected static $classLoader;

    /**
     * @var Connection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected static $connection;

    public static function prepare(ClassLoader $classLoader): void
    {
        self::$classLoader = $classLoader;
    }

    public static function getClassLoader(): ClassLoader
    {
        return self::$classLoader;
    }

    /**
     * Get the currently active kernel
     */
    public static function getKernel(): Kernel
    {
        if (static::$kernel) {
            static::$kernel->boot();

            return static::$kernel;
        }

        return static::bootKernel();
    }

    /**
     * Create a web client with the default kernel and disabled reboots
     */
    public static function createBrowser(KernelInterface $kernel, bool $enableReboot = false): TestBrowser
    {
        /** @var TestBrowser $apiBrowser */
        $apiBrowser = $kernel->getContainer()->get('test.client');

        if ($enableReboot) {
            $apiBrowser->enableReboot();
        } else {
            $apiBrowser->disableReboot();
        }

        return $apiBrowser;
    }

    /**
     * Boots the Kernel for this test.
     */
    public static function bootKernel(bool $reuseConnection = true, string $cacheId = 'h8f3f0ee9c61829627676afd6294bb029'): Kernel
    {
        self::ensureKernelShutdown();

        static::$kernel = static::createKernel(null, $reuseConnection, $cacheId);
        static::$kernel->boot();
        MemoryAdapterFactory::resetInstances();

        return static::$kernel;
    }

    /**
     * @param class-string<Kernel>|null $kernelClass
     */
    public static function createKernel(?string $kernelClass = null, bool $reuseConnection = true, string $cacheId = 'h8f3f0ee9c61829627676afd6294bb029', ?string $projectDir = null): Kernel
    {
        $_SERVER['CICADA_CACHE_ID'] = $cacheId;

        if ($kernelClass === null) {
            if (static::$class === null) {
                static::$class = static::getKernelClass();
            }

            $kernelClass = static::$class;
        }

        $env = (string) EnvironmentHelper::getVariable('APP_ENV', 'test');
        $debug = (bool) EnvironmentHelper::getVariable('APP_DEBUG', true);

        if (self::$classLoader === null) {
            throw new \InvalidArgumentException('No class loader set. Please call KernelLifecycleManager::prepare');
        }

        try {
            $existingConnection = null;
            if ($reuseConnection) {
                $existingConnection = self::getConnection();

                try {
                    $existingConnection->fetchOne('SELECT 1');
                } catch (\Throwable) {
                    // The connection is closed
                    $existingConnection = null;
                }
            }
            if ($existingConnection === null) {
                $existingConnection = self::$connection = $kernelClass::getConnection();
            }

            // force connection to database
            $existingConnection->fetchOne('SELECT 1');

            $pluginLoader = new DbalKernelPluginLoader(self::$classLoader, null, $existingConnection);
        } catch (\Throwable) {
            // if we don't have database yet, we'll boot the kernel without plugins
            $pluginLoader = new StaticKernelPluginLoader(self::$classLoader);
        }

        $kernel = KernelFactory::create(
            environment: $env,
            debug: $debug,
            classLoader: self::$classLoader,
            pluginLoader: $pluginLoader,
            connection: $existingConnection
        );

        \assert($kernel instanceof Kernel);

        return $kernel;
    }

    /**
     * @return class-string<Kernel>
     */
    public static function getKernelClass(): string
    {
        if (!class_exists($class = (string) EnvironmentHelper::getVariable('KERNEL_CLASS', Kernel::class))) {
            throw new \RuntimeException(
                \sprintf(
                    'Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the %s::createKernel() method.',
                    $class,
                    static::class
                )
            );
        }

        if (!is_a($class, Kernel::class, true)) {
            throw new \RuntimeException(
                \sprintf(
                    'Class "%s" must extend "%s". Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the %s::createKernel() method.',
                    $class,
                    Kernel::class,
                    static::class
                )
            );
        }

        return $class;
    }

    /**
     * Shuts the kernel down if it was used in the test.
     */
    public static function ensureKernelShutdown(): void
    {
        if (static::$kernel === null) {
            return;
        }

        $container = static::$kernel->getContainer();
        static::$kernel->shutdown();

        if ($container instanceof ResetInterface) {
            $container->reset();
        }

        static::$kernel = null;
    }

    public static function getConnection(): Connection
    {
        if (!static::$connection) {
            static::$connection = MySQLFactory::create();
        }

        return static::$connection;
    }
}
