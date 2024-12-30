<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Cart\Discount;

use Cicada\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Cicada\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Cicada\Core\Checkout\Promotion\Exception\PriceNotFoundException;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Collection;

/**
 * @extends Collection<DiscountPackage>
 */
#[Package('buyers-experience')]
class DiscountPackageCollection extends Collection
{
    /**
     * Gets a list of all prices within all
     * existing packages of this collection.
     *
     * @throws PriceNotFoundException
     */
    public function getAffectedPrices(): PriceCollection
    {
        $affectedPrices = new PriceCollection();

        /** @var DiscountPackage $package */
        foreach ($this->elements as $package) {
            foreach ($package->getAffectedPrices() as $price) {
                $affectedPrices->add($price);
            }
        }

        return $affectedPrices;
    }

    /**
     * Gets a list of all line item entries
     * that existing within all packages.
     */
    public function getAllLineMetaItems(): LineItemQuantityCollection
    {
        $items = new LineItemQuantityCollection();

        /** @var DiscountPackage $package */
        foreach ($this->elements as $package) {
            foreach ($package->getMetaData() as $item) {
                $items->add($item);
            }
        }

        $items->compress();

        return $items;
    }

    /**
     * This function splits all line items within
     * all existing packages into separate packages.
     * If you have 1 package with 10 items, then you will
     * get 10 packages with each 1 item.
     */
    public function splitPackages(): DiscountPackageCollection
    {
        $tmpPackages = [];

        /** @var DiscountPackage $package */
        foreach ($this->elements as $package) {
            foreach ($package->getMetaData() as $meta) {
                $tmpPackages[] = new DiscountPackage(new LineItemQuantityCollection([$meta]));
            }
        }

        return new self($tmpPackages);
    }

    public function getApiAlias(): string
    {
        return 'promotion_cart_discount_package_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return DiscountPackage::class;
    }
}
