<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Listing\Filter;

use Cicada\Core\Content\Product\SalesChannel\Listing\Filter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
class ManufacturerListingFilterHandler extends AbstractListingFilterHandler
{
    final public const FILTER_ENABLED_REQUEST_PARAM = 'manufacturer-filter';

    public function getDecorated(): AbstractListingFilterHandler
    {
        throw new DecorationPatternException(self::class);
    }

    public function create(Request $request, SalesChannelContext $context): ?Filter
    {
        if (!$request->request->get(self::FILTER_ENABLED_REQUEST_PARAM, true)) {
            return null;
        }

        $ids = $this->getManufacturerIds($request);

        return new Filter(
            'manufacturer',
            !empty($ids),
            [new EntityAggregation('manufacturer', 'product.manufacturerId', 'product_manufacturer')],
            new EqualsAnyFilter('product.manufacturerId', $ids),
            $ids
        );
    }

    /**
     * @return list<string>
     */
    private function getManufacturerIds(Request $request): array
    {
        $ids = $request->query->get('manufacturer', '');
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get('manufacturer', '');
        }

        if (\is_string($ids)) {
            $ids = explode('|', $ids);
        }

        /** @var list<string> $ids */
        $ids = array_filter((array) $ids);

        return $ids;
    }
}
