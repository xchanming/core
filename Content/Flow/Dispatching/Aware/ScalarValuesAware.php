<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Dispatching\Aware;

use Cicada\Core\Framework\Event\IsFlowEventAware;
use Cicada\Core\Framework\Log\Package;

#[Package('after-sales')]
#[IsFlowEventAware]
interface ScalarValuesAware
{
    public const STORE_VALUES = 'store_values';

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array;
}
