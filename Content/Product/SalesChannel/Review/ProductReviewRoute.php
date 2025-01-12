<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Review;

use Cicada\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use Cicada\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('inventory')]
class ProductReviewRoute extends AbstractProductReviewRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductReviewCollection> $productReviewRepository
     */
    public function __construct(
        private readonly EntityRepository $productReviewRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function buildName(string $parentId): string
    {
        return EntityCacheKeyGenerator::buildProductTag($parentId);
    }

    public function getDecorated(): AbstractProductReviewRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/{productId}/reviews', name: 'store-api.product-review.list', methods: ['POST'], defaults: ['_entity' => 'product_review'])]
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductReviewRouteResponse
    {
        $this->dispatcher->dispatch(new AddCacheTagEvent(self::buildName($productId)));

        $active = new MultiFilter(MultiFilter::CONNECTION_OR, [new EqualsFilter('status', true)]);
        if ($customer = $context->getCustomer()) {
            $active->addQuery(new EqualsFilter('customerId', $customer->getId()));
        }

        $criteria->setTitle('product-review-route');
        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                $active,
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('product.id', $productId),
                    new EqualsFilter('product.parentId', $productId),
                ]),
            ])
        );

        $result = $this->productReviewRepository->search($criteria, $context->getContext());

        return new ProductReviewRouteResponse($result);
    }
}
