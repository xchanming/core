<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Cart\Discount\Filter;

use Cicada\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Cicada\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class AdvancedPackagePicker
{
    /**
     * @internal
     */
    public function __construct(private readonly FilterServiceRegistry $registry)
    {
    }

    public function pickItems(DiscountLineItem $discount, DiscountPackageCollection $scopePackages): DiscountPackageCollection
    {
        $pickerKey = $discount->getFilterPickerKey();

        // we start by modifying our packages
        // with the currently set picker, if available
        // this restructures our packages
        if (!empty($pickerKey)) {
            $picker = $this->registry->getPicker($pickerKey);

            // get the new list of packages to consider
            $scopePackages = $picker->pickItems($scopePackages);
        }

        return $scopePackages;
    }
}
