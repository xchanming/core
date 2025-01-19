<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Cms;

use Cicada\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Cicada\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Cicada\Core\Content\Cms\SalesChannel\Struct\ProductDescriptionReviewsStruct;
use Cicada\Core\Content\Product\SalesChannel\Review\AbstractProductReviewLoader;
use Cicada\Core\Content\Product\SalesChannel\Review\ProductReviewsWidgetLoadedHook;
use Cicada\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Script\Execution\ScriptExecutor;

#[Package('discovery')]
class ProductDescriptionReviewsCmsElementResolver extends AbstractProductDetailCmsElementResolver
{
    final public const TYPE = 'product-description-reviews';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductReviewLoader $productReviewLoader,
        private readonly ScriptExecutor $scriptExecutor
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = new ProductDescriptionReviewsStruct();
        $slot->setData($data);

        $productConfig = $slot->getFieldConfig()->get('product');
        if ($productConfig === null) {
            return;
        }

        $request = $resolverContext->getRequest();
        $ratingSuccess = (bool) $request->get('success', false);
        $data->setRatingSuccess($ratingSuccess);

        $product = null;

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $product = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
        }

        if ($productConfig->isStatic()) {
            $product = $this->getSlotProduct($slot, $result, $productConfig->getStringValue());
        }

        if ($product instanceof SalesChannelProductEntity) {
            $reviews = $this->productReviewLoader->load($request, $resolverContext->getSalesChannelContext(), $product->getId(), $product->getParentId());

            $this->scriptExecutor->execute(new ProductReviewsWidgetLoadedHook($reviews, $resolverContext->getSalesChannelContext()));

            $data->setProduct($product);
            $data->setReviews($reviews);
        }
    }
}
