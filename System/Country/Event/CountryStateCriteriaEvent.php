<?php declare(strict_types=1);

namespace Cicada\Core\System\Country\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\CicadaSalesChannelEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@discovery')]
class CountryStateCriteriaEvent extends Event implements CicadaSalesChannelEvent
{
    public function __construct(
        private readonly string $countryId,
        private readonly Request $request,
        private readonly Criteria $criteria,
        private readonly SalesChannelContext $salesChannelContext
    ) {
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
