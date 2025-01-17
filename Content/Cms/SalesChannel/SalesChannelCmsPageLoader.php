<?php declare(strict_types=1);

namespace Cicada\Core\Content\Cms\SalesChannel;

use Cicada\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Cicada\Core\Content\Cms\Aggregate\CmsSection\CmsSectionCollection;
use Cicada\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Cicada\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Cicada\Core\Content\Cms\CmsPageCollection;
use Cicada\Core\Content\Cms\CmsPageEntity;
use Cicada\Core\Content\Cms\DataResolver\CmsSlotsDataResolver;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Cicada\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Cicada\Core\Content\Cms\Events\CmsPageLoaderCriteriaEvent;
use Cicada\Core\Content\Cms\SalesChannel\Struct\ProductBoxStruct;
use Cicada\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Cicada\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
class SalesChannelCmsPageLoader implements SalesChannelCmsPageLoaderInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<CmsPageCollection> $cmsPageRepository
     */
    public function __construct(
        private readonly EntityRepository $cmsPageRepository,
        private readonly CmsSlotsDataResolver $slotDataResolver,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function load(
        Request $request,
        Criteria $criteria,
        SalesChannelContext $context,
        ?array $config = null,
        ?ResolverContext $resolverContext = null
    ): EntitySearchResult {
        $this->dispatcher->dispatch(new CmsPageLoaderCriteriaEvent($request, $criteria, $context));
        $config ??= [];

        // ensure sections, blocks and slots are loaded, slots and blocks can be restricted by caller
        $criteria->addAssociation('sections.backgroundMedia');
        $criteria->addAssociation('sections.blocks.backgroundMedia');
        $criteria->addAssociation('sections.blocks.slots');

        // step 1, load cms pages with blocks and slots
        $result = $this->cmsPageRepository->search($criteria, $context->getContext());
        $pages = $result->getEntities();

        foreach ($pages as $page) {
            $sections = $page->getSections();

            if ($sections === null) {
                continue;
            }

            $sections->sort(fn (CmsSectionEntity $a, CmsSectionEntity $b) => $a->getPosition() <=> $b->getPosition());

            if (!$resolverContext) {
                $resolverContext = new ResolverContext($context, $request);
            }

            // step 2, sort blocks into sectionPositions
            foreach ($sections as $section) {
                $blocks = $section->getBlocks();
                if ($blocks === null) {
                    continue;
                }
                $blocks->sort(fn (CmsBlockEntity $a, CmsBlockEntity $b) => $a->getPosition() <=> $b->getPosition());

                foreach ($blocks as $block) {
                    $slots = $block->getSlots();
                    if ($slots === null) {
                        continue;
                    }
                    $slots->sort(fn (CmsSlotEntity $a, CmsSlotEntity $b) => $a->getSlot() <=> $b->getSlot());
                }
            }

            // step 3, find config overwrite
            $overwrite = $config[$page->getId()] ?? $config;

            // step 4, overwrite slot config
            $this->overwriteSlotConfig($sections, $overwrite);

            // step 5, resolve slot data
            $this->loadSlotData($sections, $resolverContext);
        }

        $this->dispatcher->dispatch(new CmsPageLoadedEvent($request, $pages, $context));

        $this->dispatcher->dispatch(new AddCacheTagEvent(...$this->extractProductIds($pages)));

        return $result;
    }

    private function loadSlotData(CmsSectionCollection $sections, ResolverContext $resolverContext): void
    {
        $blocks = $sections->getBlocks();
        $slots = $this->slotDataResolver->resolve($blocks->getSlots(), $resolverContext);

        $blocks->setSlots($slots);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function overwriteSlotConfig(CmsSectionCollection $sections, array $config): void
    {
        foreach ($sections->getBlocks()->getSlots() as $slot) {
            if ($slot->getConfig() === null && $slot->getTranslation('config') !== null) {
                $slot->setConfig($slot->getTranslation('config'));
            }

            if (empty($config)) {
                continue;
            }

            if (!isset($config[$slot->getId()])) {
                continue;
            }

            $defaultConfig = $slot->getConfig() ?? [];
            $merged = array_replace_recursive(
                $defaultConfig,
                $config[$slot->getId()]
            );

            $slot->setConfig($merged);
            $slot->addTranslated('config', $merged);
        }
    }

    /**
     * @param EntityCollection<CmsPageEntity> $pages
     *
     * @return array<string>
     */
    private function extractProductIds(EntityCollection $pages): array
    {
        $ids = [];
        $streamIds = [];

        /** @var CmsPageEntity $page */
        foreach ($pages as $page) {
            $slots = $page->getElementsOfType('product-slider');

            /** @var CmsSlotEntity $slot */
            foreach ($slots as $slot) {
                $slider = $slot->getData();

                if (!$slider instanceof ProductSliderStruct) {
                    continue;
                }

                if ($slider->getStreamId() !== null) {
                    $streamIds[] = $slider->getStreamId();
                }

                if ($slider->getProducts() === null) {
                    continue;
                }
                foreach ($slider->getProducts() as $product) {
                    $ids[] = $product->getId();
                    $ids[] = $product->getParentId();
                }
            }

            $slots = $page->getElementsOfType('product-box');
            /** @var CmsSlotEntity $slot */
            foreach ($slots as $slot) {
                $box = $slot->getData();

                if (!$box instanceof ProductBoxStruct) {
                    continue;
                }
                if ($box->getProduct() === null) {
                    continue;
                }

                $ids[] = $box->getProduct()->getId();
                $ids[] = $box->getProduct()->getParentId();
            }

            $ids = array_values(array_unique(array_filter($ids)));
        }

        return [
            ...array_map(EntityCacheKeyGenerator::buildProductTag(...), $ids),
            ...array_map(EntityCacheKeyGenerator::buildStreamTag(...), $streamIds),
            ...array_map(EntityCacheKeyGenerator::buildCmsTag(...), $pages->getIds()),
        ];
    }
}
