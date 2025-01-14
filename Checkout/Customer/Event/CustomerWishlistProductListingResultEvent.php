<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Event;

use Cicada\Core\Content\Product\ProductCollection;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Cicada\Core\Framework\Event\CicadaSalesChannelEvent;
use Cicada\Core\Framework\Event\NestedEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class CustomerWishlistProductListingResultEvent extends NestedEvent implements CicadaSalesChannelEvent
{
    final public const EVENT_NAME = 'checkout.customer.wishlist_listing_product_result';

    /**
     * @var Request
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $request;

    /**
     * @var EntitySearchResult<ProductCollection>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $result;

    /**
     * @param EntitySearchResult<ProductCollection> $wishlistProductListingResult
     */
    public function __construct(
        Request $request,
        EntitySearchResult $wishlistProductListingResult,
        private SalesChannelContext $context
    ) {
        $this->request = $request;
        $this->result = $wishlistProductListingResult;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return EntitySearchResult<ProductCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->result;
    }

    /**
     * @param EntitySearchResult<ProductCollection> $result
     */
    public function setResult(EntitySearchResult $result): void
    {
        $this->result = $result;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function setSalesChannelContext(SalesChannelContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}
