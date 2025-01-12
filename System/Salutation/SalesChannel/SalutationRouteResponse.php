<?php declare(strict_types=1);

namespace Cicada\Core\System\Salutation\SalesChannel;

use Cicada\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\StoreApiResponse;
use Cicada\Core\System\Salutation\SalutationCollection;

#[Package('checkout')]
class SalutationRouteResponse extends StoreApiResponse
{
    /**
     * @var EntitySearchResult<SalutationCollection>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $object;

    /**
     * @param EntitySearchResult<SalutationCollection> $object
     */
    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    public function getSalutations(): SalutationCollection
    {
        return $this->object->getEntities();
    }
}
