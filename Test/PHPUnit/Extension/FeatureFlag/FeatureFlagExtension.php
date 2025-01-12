<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\FeatureFlag;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\Subscriber\TestFinishedSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\Subscriber\TestPreparationStartedSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\Subscriber\TestSkippedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * This extension guarantees a clean feature environment for pure unit tests
 *
 * @internal
 */
#[Package('core')]
class FeatureFlagExtension implements Extension
{
    private const DEFAULT_TEST_NAMESPACE_PREFIX = 'Cicada\\Tests\\Unit\\';

    /**
     * @var string[]
     */
    private static array $testNamespaces = [
        self::DEFAULT_TEST_NAMESPACE_PREFIX, // Default test namespace: must always be present in the array
    ];

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $savedConfig = new SavedConfig();

        $facade->registerSubscribers(
            new TestPreparationStartedSubscriber($savedConfig),
            new TestFinishedSubscriber($savedConfig),
            new TestSkippedSubscriber($savedConfig)
        );
    }

    public static function addTestNamespace(string $namespace): void
    {
        if (preg_match('/^[_a-zA-Z][_a-zA-Z0-9]*(\\\\[_a-zA-Z][_a-zA-Z0-9]*)*\\\\$/', $namespace) === 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Namespace must be a valid PHP namespace ending with a backslash like this "%s", "%s" given.',
                    self::DEFAULT_TEST_NAMESPACE_PREFIX,
                    $namespace
                )
            );
        }

        if (\in_array($namespace, self::$testNamespaces, true)) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Namespace "%s" was already added to test namespaces.',
                    $namespace
                )
            );
        }

        self::$testNamespaces[] = $namespace;
    }

    /**
     * @return string[]
     */
    public static function getTestNamespaces(): array
    {
        return self::$testNamespaces;
    }
}
