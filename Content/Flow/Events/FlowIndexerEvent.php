<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Events;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\NestedEvent;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
class FlowIndexerEvent extends NestedEvent
{
    public function __construct(
        private readonly array $ids,
        private readonly Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
