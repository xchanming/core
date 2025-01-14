<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\CrossSelling;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\StoreApiResponse;

#[Package('inventory')]
class ProductCrossSellingRouteResponse extends StoreApiResponse
{
    /**
     * @var CrossSellingElementCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $object;

    public function __construct(CrossSellingElementCollection $object)
    {
        parent::__construct($object);
    }

    public function getResult(): CrossSellingElementCollection
    {
        return $this->object;
    }
}
