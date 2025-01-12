<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\Subscriber;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\PHPUnit\Extension\FeatureFlag\SavedConfig;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;

/**
 * @internal
 */
#[Package('core')]
class TestSkippedSubscriber implements SkippedSubscriber
{
    public function __construct(private readonly SavedConfig $savedConfig)
    {
    }

    public function notify(Skipped $event): void
    {
        if ($this->savedConfig->savedFeatureConfig === null) {
            return;
        }

        $_SERVER = $this->savedConfig->savedServerVars;

        Feature::resetRegisteredFeatures();
        Feature::registerFeatures($this->savedConfig->savedFeatureConfig);

        $this->savedConfig->savedFeatureConfig = null;
    }
}
