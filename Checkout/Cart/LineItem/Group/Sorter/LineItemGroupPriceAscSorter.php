<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\LineItem\Group\Sorter;

use Cicada\Core\Checkout\Cart\LineItem\Group\LineItemGroupSorterInterface;
use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class LineItemGroupPriceAscSorter implements LineItemGroupSorterInterface
{
    public function getKey(): string
    {
        return 'PRICE_ASC';
    }

    public function sort(LineItemFlatCollection $items): LineItemFlatCollection
    {
        $sorted = $items->getElements();

        usort($sorted, static function (LineItem $a, LineItem $b) {
            if ($a->getPrice() === null) {
                return 0;
            }

            if ($b->getPrice() === null) {
                return 1;
            }

            return $a->getPrice()->getUnitPrice() <=> $b->getPrice()->getUnitPrice();
        });

        return new LineItemFlatCollection($sorted);
    }
}
