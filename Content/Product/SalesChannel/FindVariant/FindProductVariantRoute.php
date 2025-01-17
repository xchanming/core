<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\FindVariant;

use Cicada\Core\Content\Product\Exception\VariantNotFoundException;
use Cicada\Core\Content\Product\ProductException;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('inventory')]
class FindProductVariantRoute extends AbstractFindProductVariantRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly SalesChannelRepository $productRepository)
    {
    }

    public function getDecorated(): AbstractFindProductVariantRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/{productId}/find-variant', name: 'store-api.product.find-variant', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $productId, Request $request, SalesChannelContext $context): FindProductVariantRouteResponse
    {
        /** @var string|null $switchedGroup */
        $switchedGroup = $request->get('switchedGroup');

        $options = $request->get('options') ? $request->get('options', []) : [];

        foreach ($options as $optionId) {
            if (!\is_string($optionId)) {
                throw ProductException::invalidOptionsParameter();
            }
        }

        $variantId = $this->searchForOptions($productId, $context, $options);

        if ($variantId !== null) {
            return new FindProductVariantRouteResponse(new FoundCombination($variantId, $options));
        }

        while (\count($options) > 1) {
            foreach ($options as $groupId => $_optionId) {
                if ($groupId !== $switchedGroup) {
                    unset($options[$groupId]);

                    break;
                }
            }

            $variantId = $this->searchForOptions($productId, $context, $options);

            if ($variantId) {
                return new FindProductVariantRouteResponse(new FoundCombination($variantId, $options));
            }
        }

        throw new VariantNotFoundException($productId, $options);
    }

    /**
     * @param array<string> $options
     */
    private function searchForOptions(
        string $productId,
        SalesChannelContext $salesChannelContext,
        array $options
    ): ?string {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->setLimit(1);

        foreach ($options as $optionId) {
            $criteria->addFilter(new EqualsFilter('product.optionIds', $optionId));
        }

        return $this->productRepository->searchIds($criteria, $salesChannelContext)->firstId();
    }
}
