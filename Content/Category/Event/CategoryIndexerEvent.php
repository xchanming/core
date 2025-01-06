<?php declare(strict_types=1);

namespace Cicada\Core\Content\Category\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\NestedEvent;
use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class CategoryIndexerEvent extends NestedEvent
{
    /**
     * @var list<string>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $ids;

    /**
     * @var Context
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $context;

    /**
     * @param list<string> $ids
     * @param array<string> $skip
     */
    public function __construct(
        array $ids,
        Context $context,
        private readonly array $skip = [],
        public bool $isFullIndexing = false
    ) {
        $this->ids = $ids;
        $this->context = $context;
    }

    /**
     * @return list<string>
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<string>
     */
    public function getSkip(): array
    {
        return $this->skip;
    }
}
