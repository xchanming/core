<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Payment\Cart\Recurring;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

/**
 * This is an experimental payment struct to make generic subscription informations available without relying as an payment handler to a specific subscription extensions
 */
#[Package('checkout')]
class RecurringDataStruct extends Struct
{
    /**
     * @internal
     */
    public function __construct(
        protected string $subscriptionId,
        protected \DateTimeInterface $nextSchedule,
    ) {
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function getNextSchedule(): \DateTimeInterface
    {
        return $this->nextSchedule;
    }
}
