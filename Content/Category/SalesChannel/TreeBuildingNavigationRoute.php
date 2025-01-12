<?php declare(strict_types=1);

namespace Cicada\Core\Content\Category\SalesChannel;

use Cicada\Core\Content\Category\CategoryCollection;
use Cicada\Core\Content\Category\CategoryEntity;
use Cicada\Core\Content\Category\CategoryException;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('discovery')]
class TreeBuildingNavigationRoute extends AbstractNavigationRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractNavigationRoute $decorated)
    {
    }

    public function getDecorated(): AbstractNavigationRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/navigation/{activeId}/{rootId}', name: 'store-api.navigation', methods: ['GET', 'POST'], defaults: ['_entity' => 'category'])]
    public function load(string $activeId, string $rootId, Request $request, SalesChannelContext $context, Criteria $criteria): NavigationRouteResponse
    {
        try {
            $activeId = $this->resolveAliasId($activeId, $context->getSalesChannel());
        } catch (CategoryException $e) {
            if (!$e->is(CategoryException::FOOTER_CATEGORY_NOT_FOUND, CategoryException::SERVICE_CATEGORY_NOT_FOUND)) {
                throw $e;
            }

            $response = new NavigationRouteResponse(new CategoryCollection());
            $response->setStatusCode(Response::HTTP_NO_CONTENT);

            return $response;
        }

        $rootId = $this->resolveAliasId($rootId, $context->getSalesChannel());

        $response = $this->getDecorated()->load($activeId, $rootId, $request, $context, $criteria);

        $buildTree = $request->query->getBoolean('buildTree', $request->request->getBoolean('buildTree', true));

        if (!$buildTree) {
            return $response;
        }

        $categories = $this->buildTree($rootId, $response->getCategories()->getElements());

        return new NavigationRouteResponse($categories);
    }

    /**
     * @param CategoryEntity[] $categories
     */
    private function buildTree(?string $parentId, array $categories): CategoryCollection
    {
        $children = new CategoryCollection();
        foreach ($categories as $key => $category) {
            if ($category->getParentId() !== $parentId) {
                continue;
            }

            unset($categories[$key]);

            $children->add($category);
        }

        $children->sortByPosition();

        $items = new CategoryCollection();
        foreach ($children as $child) {
            if (!$child->getActive() || !$child->getVisible()) {
                continue;
            }

            $child->setChildren($this->buildTree($child->getId(), $categories));

            $items->add($child);
        }

        return $items;
    }

    private function resolveAliasId(string $id, SalesChannelEntity $salesChannelEntity): string
    {
        $name = $salesChannelEntity->getTranslation('name') ?? '';
        \assert(\is_string($name));

        switch ($id) {
            case 'main-navigation':
                return $salesChannelEntity->getNavigationCategoryId();
            case 'service-navigation':
                if ($salesChannelEntity->getServiceCategoryId() === null) {
                    throw CategoryException::serviceCategoryNotFoundForSalesChannel($name);
                }

                return $salesChannelEntity->getServiceCategoryId();
            case 'footer-navigation':
                if ($salesChannelEntity->getFooterCategoryId() === null) {
                    throw CategoryException::footerCategoryNotFoundForSalesChannel($name);
                }

                return $salesChannelEntity->getFooterCategoryId();
            default:
                return $id;
        }
    }
}
