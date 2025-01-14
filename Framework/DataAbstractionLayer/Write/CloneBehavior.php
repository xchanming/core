<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Write;

use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class CloneBehavior
{
    public function __construct(
        private readonly array $overwrites = [],
        private readonly bool $cloneChildren = true
    ) {
    }

    public function getOverwrites(): array
    {
        return $this->overwrites;
    }

    public function cloneChildren(): bool
    {
        return $this->cloneChildren;
    }
}
