<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Cart\Discount\Filter;

use Cicada\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
interface FilterPickerInterface
{
    public function getKey(): string;

    public function pickItems(DiscountPackageCollection $units): DiscountPackageCollection;
}
