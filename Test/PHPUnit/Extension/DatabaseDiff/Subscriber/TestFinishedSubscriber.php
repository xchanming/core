<?php declare(strict_types=1);

namespace Cicada\Core\Test\PHPUnit\Extension\DatabaseDiff\Subscriber;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Test\PHPUnit\Extension\DatabaseDiff\DbState;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
 * @internal
 */
#[Package('core')]
class TestFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(private readonly DbState $dbState)
    {
    }

    public function notify(Finished $event): void
    {
        $diff = $this->dbState->getDiff();

        if (!empty($diff)) {
            echo \PHP_EOL . $event->asString() . \PHP_EOL;

            print_r($diff);
        }
    }
}
