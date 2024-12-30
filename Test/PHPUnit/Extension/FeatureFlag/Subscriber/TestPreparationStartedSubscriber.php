<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\Subscriber;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\Annotation\DisabledFeatures;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\FeatureFlagExtension;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\SavedConfig;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;

/**
 * @internal
 */
#[Package('core')]
class TestPreparationStartedSubscriber implements PreparationStartedSubscriber
{
    public function __construct(private readonly SavedConfig $savedConfig)
    {
    }

    public function notify(PreparationStarted $event): void
    {
        $test = $event->test();

        if (!$test->isTestMethod()) {
            return;
        }

        $class = $test->className();
        $method = $test->methodName();

        if (!$this->namespaceIsAllowed($class)) {
            return;
        }

        $reflectedMethod = new \ReflectionMethod($class, $method);

        /** @var \ReflectionAttribute<DisabledFeatures>[] $disabledFeatures */
        $disabledFeatures = array_merge(
            $reflectedMethod->getAttributes(DisabledFeatures::class),
            $reflectedMethod->getDeclaringClass()->getAttributes(DisabledFeatures::class),
        );

        $this->savedConfig->savedFeatureConfig = Feature::getRegisteredFeatures();
        $this->savedConfig->savedServerVars = $_SERVER;

        Feature::resetRegisteredFeatures();
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'v6.') || str_starts_with($key, 'FEATURE_') || str_starts_with($key, 'V6_')) {
                // set to false so that $_ENV is not checked
                $_SERVER[$key] = false;
            }
        }

        $disabledFlags = [];
        foreach ($disabledFeatures as $disabledFeature) {
            /** @var DisabledFeatures $attr */
            $attr = $disabledFeature->newInstance();

            foreach ($attr->features as $featureName) {
                $disabledFlags[Feature::normalizeName($featureName)] = true;
            }
        }

        foreach ($this->savedConfig->savedFeatureConfig as $flag => $config) {
            $flag = Feature::normalizeName($flag);
            $_SERVER[$flag] = !\array_key_exists($flag, $disabledFlags);
        }
    }

    private function namespaceIsAllowed(string $className): bool
    {
        foreach (FeatureFlagExtension::getTestNamespaces() as $namespace) {
            if (str_starts_with($className, $namespace)) {
                return true;
            }
        }

        return false;
    }
}
