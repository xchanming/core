<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\Datadog;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\PHPUnit\Extension\Common\TimeKeeper;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Gateway\DatadogGateway;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber\TestErroredSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber\TestFailedSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber\TestFinishedSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber\TestPreparedSubscriber;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber\TestRunnerExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @internal
 */
#[Package('core')]
class DatadogExtension implements Extension
{
    public const THRESHOLD_IN_SECONDS = 2;

    public const GATEWAY_URL = 'https://http-intake.logs.datadoghq.eu/v1/input';

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $timeKeeper = new TimeKeeper();
        $failedTests = new DatadogPayloadCollection();
        $slowTests = new DatadogPayloadCollection();
        $erroredTests = new DatadogPayloadCollection();

        $facade->registerSubscribers(
            new TestPreparedSubscriber($timeKeeper),
            new TestFailedSubscriber($timeKeeper, $failedTests),
            new TestFinishedSubscriber($timeKeeper, $slowTests),
            new TestErroredSubscriber($timeKeeper, $erroredTests),
            new TestRunnerExecutionFinishedSubscriber(
                $failedTests,
                $slowTests,
                $erroredTests,
                new DatadogGateway(self::GATEWAY_URL)
            ),
        );
    }

    private function isEnabled(): bool
    {
        return EnvironmentHelper::hasVariable('DATADOG_API_KEY')
            && (EnvironmentHelper::getVariable('CI_COMMIT_REF_NAME') === 'trunk'
                || EnvironmentHelper::getVariable('CI_MERGE_REQUEST_EVENT_TYPE') === 'merge_train');
    }
}
