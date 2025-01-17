<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Gateway\Template;

use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('checkout')]
class ActiveDateRange extends MultiFilter
{
    /**
     * Creates a new date range filter that only matches
     * for promotions that have a valid date range right now.
     * This means either no date ranges set at all, either no starting
     * or ending date, or a valid and active date range.
     */
    public function __construct()
    {
        $today = new \DateTime();
        $today = $today->setTimezone(new \DateTimeZone('Asia/Shanghai'));

        $todayStart = $today->format('Y-m-d H:i:s');
        $todayEnd = $today->format('Y-m-d H:i:s');

        $filterNoDateRange = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('validFrom', null),
                new EqualsFilter('validUntil', null),
            ]
        );

        $filterStartedNoEndDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('validFrom', ['lte' => $todayStart]),
                new EqualsFilter('validUntil', null),
            ]
        );

        $filterActiveNoStartDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('validFrom', null),
                new RangeFilter('validUntil', ['gt' => $todayEnd]),
            ]
        );

        $activeDateRangeFilter = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('validFrom', ['lte' => $todayStart]),
                new RangeFilter('validUntil', ['gt' => $todayEnd]),
            ]
        );

        parent::__construct(
            MultiFilter::CONNECTION_OR,
            [
                $filterNoDateRange,
                $filterActiveNoStartDate,
                $filterStartedNoEndDate,
                $activeDateRangeFilter,
            ]
        );
    }
}
