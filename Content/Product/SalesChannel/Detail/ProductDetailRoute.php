<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Detail;

use Cicada\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Cicada\Core\Content\Cms\CmsPageEntity;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Cicada\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Cicada\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Cicada\Core\Content\Product\Exception\ProductNotFoundException;
use Cicada\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Cicada\Core\Content\Product\SalesChannel\Detail\Event\ResolveVariantIdEvent;
use Cicada\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Cicada\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Cicada\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Cicada\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Cicada\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\Profiling\Profiler;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('inventory')]
class ProductDetailRoute extends AbstractProductDetailRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly SystemConfigService $config,
        private readonly Connection $connection,
        private readonly ProductConfiguratorLoader $configuratorLoader,
        private readonly CategoryBreadcrumbBuilder $breadcrumbBuilder,
        private readonly SalesChannelCmsPageLoaderInterface $cmsPageLoader,
        private readonly SalesChannelProductDefinition $productDefinition,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function buildName(string $parentId): string
    {
        return EntityCacheKeyGenerator::buildProductTag($parentId);
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/{productId}', name: 'store-api.product.detail', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductDetailRouteResponse
    {
        return Profiler::trace('product-detail-route', function () use ($productId, $request, $context, $criteria) {
            $mainVariantId = $this->checkVariantListingConfig($productId, $context);

            $resolveVariantIdEvent = new ResolveVariantIdEvent(
                $productId,
                $mainVariantId,
                $context,
            );

            $this->dispatcher->dispatch($resolveVariantIdEvent);
            $productId = $resolveVariantIdEvent->getResolvedVariantId() ?? $this->findBestVariant($productId, $context);

            $this->addFilters($context, $criteria);

            $criteria->setIds([$productId]);
            $criteria->setTitle('product-detail-route');

            $product = $this->productRepository
                ->search($criteria, $context)
                ->first();

            if (!($product instanceof SalesChannelProductEntity)) {
                throw new ProductNotFoundException($productId);
            }

            $parent = $product->getParentId() ?? $product->getId();

            $this->dispatcher->dispatch(new AddCacheTagEvent(EntityCacheKeyGenerator::buildProductTag($parent)));

            $product->setSeoCategory(
                $this->breadcrumbBuilder->getProductSeoCategory($product, $context)
            );

            $configurator = $this->configuratorLoader->load($product, $context);

            $pageId = $product->getCmsPageId();

            if ($pageId) {
                // clone product to prevent recursion encoding (see NEXT-17603)
                $resolverContext = new EntityResolverContext($context, $request, $this->productDefinition, clone $product);

                $pages = $this->cmsPageLoader->load(
                    $request,
                    $this->createCriteria($pageId, $request),
                    $context,
                    $product->getTranslation('slotConfig'),
                    $resolverContext
                );

                $page = $pages->first();
                if ($page instanceof CmsPageEntity) {
                    $product->setCmsPage($page);
                }
            }

            return new ProductDetailRouteResponse($product, $configurator);
        });
    }

    private function addFilters(SalesChannelContext $context, Criteria $criteria): void
    {
        $criteria->addFilter(
            new ProductAvailableFilter($context->getSalesChannelId(), ProductVisibilityDefinition::VISIBILITY_LINK)
        );

        $salesChannelId = $context->getSalesChannelId();

        $hideCloseoutProductsWhenOutOfStock = $this->config->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId);

        if ($hideCloseoutProductsWhenOutOfStock) {
            $filter = $this->productCloseoutFilterFactory->create($context);
            $filter->addQuery(new EqualsFilter('product.parentId', null));
            $criteria->addFilter($filter);
        }
    }

    private function checkVariantListingConfig(string $productId, SalesChannelContext $context): ?string
    {
        if (!Uuid::isValid($productId)) {
            return null;
        }

        $productData = $this->connection->fetchAssociative(
            '# product-detail-route::check-variant-listing-config
            SELECT
                variant_listing_config as variantListingConfig,
                parent_id as parentId
            FROM product
            WHERE id = :id
            AND version_id = :versionId',
            [
                'id' => Uuid::fromHexToBytes($productId),
                'versionId' => Uuid::fromHexToBytes($context->getContext()->getVersionId()),
            ]
        );

        if (empty($productData) || $productData['variantListingConfig'] === null) {
            return null;
        }

        $variantListingConfig = json_decode((string) $productData['variantListingConfig'], true, 512, \JSON_THROW_ON_ERROR);

        if (isset($variantListingConfig['displayParent']) && $variantListingConfig['displayParent'] === true) {
            return null;
        }

        return $variantListingConfig['mainVariantId'] ?? null;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function findBestVariant(string $productId, SalesChannelContext $context): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->addSorting(new FieldSorting('product.available', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('product.price'))
            ->setLimit(1);

        $criteria->setTitle('product-detail-route::find-best-variant');
        $variantId = $this->productRepository->searchIds($criteria, $context);

        return $variantId->firstId() ?? $productId;
    }

    private function createCriteria(string $pageId, Request $request): Criteria
    {
        $criteria = new Criteria([$pageId]);
        $criteria->setTitle('product::cms-page');

        $slots = $request->get('slots');

        if (\is_string($slots)) {
            $slots = explode('|', $slots);
        }

        if (!empty($slots) && \is_array($slots)) {
            $criteria
                ->getAssociation('sections.blocks')
                ->addFilter(new EqualsAnyFilter('slots.id', $slots));
        }

        return $criteria;
    }
}
