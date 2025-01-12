<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\Datadog\Subscriber;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\PHPUnit\Extension\Common\TimeKeeper;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\DatadogExtension;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\DatadogPayload;
use Cicada\Core\Test\PHPUnit\Extension\Datadog\DatadogPayloadCollection;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
 * @internal
 */
#[Package('core')]
class TestFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(
        private readonly TimeKeeper $timeKeeper,
        private readonly DatadogPayloadCollection $slowTests
    ) {
    }

    public function notify(Finished $event): void
    {
        $time = $event->telemetryInfo()->time();

        $duration = $this->timeKeeper->stop(
            $event->test()->id(),
            HRTime::fromSecondsAndNanoseconds(
                $time->seconds(),
                $time->nanoseconds(),
            ),
        );

        $maximumDuration = Duration::fromSecondsAndNanoseconds(DatadogExtension::THRESHOLD_IN_SECONDS, 0);

        if (!$duration->isGreaterThan($maximumDuration)) {
            return;
        }

        $payload = new DatadogPayload(
            'phpunit',
            'phpunit,test:slow',
            'Slow test: ' . $event->asString(),
            'PHPUnit',
            $event->test()->id(),
            $duration->asFloat()
        );

        $this->slowTests->set($event->test()->id(), $payload);
    }
}
