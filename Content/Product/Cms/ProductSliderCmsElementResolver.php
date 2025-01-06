<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Cms;

use Cicada\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Cicada\Core\Content\Cms\DataResolver\CriteriaCollection;
use Cicada\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Cicada\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Cicada\Core\Content\Cms\DataResolver\FieldConfig;
use Cicada\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Cicada\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Cicada\Core\Content\Product\ProductCollection;
use Cicada\Core\Content\Product\ProductDefinition;
use Cicada\Core\Content\Product\ProductEntity;
use Cicada\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Cicada\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\SystemConfig\SystemConfigService;

#[Package('buyers-experience')]
class ProductSliderCmsElementResolver extends AbstractCmsElementResolver
{
    private const PRODUCT_SLIDER_ENTITY_FALLBACK = 'product-slider-entity-fallback';
    private const STATIC_SEARCH_KEY = 'product-slider';
    private const FALLBACK_LIMIT = 50;
    /**
     * @deprecated tag:v6.7.0 - will be removed, as the associations will not be loaded in the collect method anymore
     */
    private const PRODUCT_ASSOCIATIONS = [
        'options.group',
        'manufacturer',
    ];

    /**
     * @internal
     *
     * @param SalesChannelRepository<SalesChannelProductCollection> $productRepository
     */
    public function __construct(
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly SystemConfigService $systemConfigService,
        private readonly SalesChannelRepository $productRepository,
    ) {
    }

    public function getType(): string
    {
        return 'product-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $collection = new CriteriaCollection();

        $products = $config->get('products');
        if ($products === null) {
            return null;
        }

        if ($products->isStatic() && $products->getValue()) {
            $criteria = new Criteria($products->getArrayValue());

            if (!Feature::isActive('v6.7.0.0')) {
                $criteria->addAssociations(self::PRODUCT_ASSOCIATIONS);
            }

            $collection->add(self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);
        }

        if ($products->isMapped() && $products->getValue() && $resolverContext instanceof EntityResolverContext) {
            $criteria = $this->collectByEntity($resolverContext, $products);
            if ($criteria !== null) {
                $collection->add(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);
            }
        }

        if ($products->isProductStream() && $products->getValue()) {
            $criteria = $this->collectByProductStream($resolverContext, $products, $config);
            $collection->add(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);
        }

        return $collection->all() ? $collection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $slider = new ProductSliderStruct();
        $slot->setData($slider);

        $productConfig = $config->get('products');
        if ($productConfig === null) {
            return;
        }

        if ($productConfig->isStatic()) {
            $this->enrichFromSearch($slider, $result, self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier(), $resolverContext->getSalesChannelContext());
        }

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $products = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
            if ($products === null) {
                $this->enrichFromSearch($slider, $result, self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), $resolverContext->getSalesChannelContext());
            } else {
                $slider->setProducts($products);
            }
        }

        if ($productConfig->isProductStream() && $productConfig->getValue()) {
            $entitySearchResult = $result->get(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier());
            if ($entitySearchResult === null) {
                return;
            }

            $streamResult = $entitySearchResult->getEntities();
            if (!$streamResult instanceof ProductCollection) {
                return;
            }

            $slider->setProducts($this->handleProductStream($streamResult, $resolverContext->getSalesChannelContext(), $entitySearchResult->getCriteria()));
            $slider->setStreamId($productConfig->getStringValue());
        }
    }

    private function enrichFromSearch(ProductSliderStruct $slider, ElementDataCollection $result, string $searchKey, SalesChannelContext $saleschannelContext): void
    {
        $products = $result->get($searchKey)?->getEntities();
        if (!$products instanceof ProductCollection) {
            return;
        }

        if ($this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $saleschannelContext->getSalesChannelId())) {
            $products = $this->filterOutOutOfStockHiddenCloseoutProducts($products);
        }

        $slider->setProducts($products);
    }

    private function filterOutOutOfStockHiddenCloseoutProducts(ProductCollection $products): ProductCollection
    {
        return $products->filter(function (ProductEntity $product) {
            if ($product->getIsCloseout() && $product->getStock() <= 0) {
                return false;
            }

            return true;
        });
    }

    private function collectByEntity(EntityResolverContext $resolverContext, FieldConfig $config): ?Criteria
    {
        $entityProducts = $this->resolveEntityValue($resolverContext->getEntity(), $config->getStringValue());
        if ($entityProducts !== null) {
            return null;
        }

        $criteria = $this->resolveCriteriaForLazyLoadedRelations($resolverContext, $config);

        if (!Feature::isActive('v6.7.0.0')) {
            $criteria?->addAssociations(self::PRODUCT_ASSOCIATIONS);
        }

        return $criteria;
    }

    private function collectByProductStream(ResolverContext $resolverContext, FieldConfig $config, FieldConfigCollection $elementConfig): Criteria
    {
        $filters = $this->productStreamBuilder->buildFilters(
            $config->getStringValue(),
            $resolverContext->getSalesChannelContext()->getContext()
        );

        $criteria = new Criteria();
        $criteria->addFilter(...$filters);
        $criteria->setLimit($elementConfig->get('productStreamLimit')?->getIntValue() ?? self::FALLBACK_LIMIT);

        if (!Feature::isActive('v6.7.0.0')) {
            $criteria->addAssociations(self::PRODUCT_ASSOCIATIONS);
        }

        $criteria->addGroupField(new FieldGrouping('displayGroup'));
        $criteria->addFilter(
            new NotFilter(
                NotFilter::CONNECTION_AND,
                [new EqualsFilter('displayGroup', null)]
            )
        );

        $sorting = $elementConfig->get('productStreamSorting')?->getStringValue() ?? 'name:' . FieldSorting::ASCENDING;
        if ($sorting === 'random') {
            $this->addRandomSort($criteria);
        } else {
            $sorting = explode(':', $sorting);
            $field = $sorting[0];
            $direction = $sorting[1];

            $criteria->addSorting(new FieldSorting($field, $direction));
        }

        return $criteria;
    }

    private function addRandomSort(Criteria $criteria): void
    {
        $fields = [
            'id',
            'stock',
            'releaseDate',
            'manufacturer.id',
            'unit.id',
            'tax.id',
            'cover.id',
        ];
        shuffle($fields);
        $fields = \array_slice($fields, 0, 2);
        $direction = [FieldSorting::ASCENDING, FieldSorting::DESCENDING];
        $direction = $direction[random_int(0, 1)];
        foreach ($fields as $field) {
            $criteria->addSorting(new FieldSorting($field, $direction));
        }
    }

    private function handleProductStream(ProductCollection $streamResult, SalesChannelContext $context, Criteria $originCriteria): ProductCollection
    {
        $finalProductIds = $this->collectFinalProductIds($streamResult);
        if (\count($finalProductIds) === 0) {
            return new ProductCollection();
        }

        $criteria = $originCriteria->cloneForRead($finalProductIds);
        $products = $this->productRepository->search($criteria, $context)->getEntities();
        $products->sortByIdArray($finalProductIds);

        return $products;
    }

    /**
     * @return string[] List of product ids
     */
    private function collectFinalProductIds(ProductCollection $streamResult): array
    {
        $finalProductIds = [];
        foreach ($streamResult as $product) {
            $variantConfig = $product->getVariantListingConfig();

            if ($variantConfig === null) {
                $finalProductIds[] = $product->getId();
                continue;
            }

            $finalProductIds[] = ($variantConfig->getDisplayParent() ? $product->getParentId() : $variantConfig->getMainVariantId()) ?? $product->getId();
        }

        return array_unique($finalProductIds);
    }
}
