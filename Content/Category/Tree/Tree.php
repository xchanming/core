<?php declare(strict_types=1);

namespace Cicada\Core\Content\Category\Tree;

use Cicada\Core\Content\Category\CategoryEntity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('discovery')]
class Tree extends Struct
{
    /**
     * @var TreeItem[]
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $tree;

    /**
     * @var CategoryEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @param TreeItem[] $tree
     */
    public function __construct(
        ?CategoryEntity $active,
        array $tree
    ) {
        $this->tree = $tree;
        $this->active = $active;
    }

    public function isSelected(CategoryEntity $category): bool
    {
        if ($this->active === null) {
            return false;
        }

        if ($category->getId() === $this->active->getId()) {
            return true;
        }

        if (!$this->active->getPath()) {
            return false;
        }

        $ids = explode('|', $this->active->getPath());

        return \in_array($category->getId(), $ids, true);
    }

    /**
     * @return TreeItem[]
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * @param TreeItem[] $tree
     */
    public function setTree(array $tree): void
    {
        $this->tree = $tree;
    }

    public function getActive(): ?CategoryEntity
    {
        return $this->active;
    }

    public function setActive(?CategoryEntity $active): void
    {
        $this->active = $active;
    }

    public function getChildren(string $categoryId): ?Tree
    {
        $match = $this->find($categoryId, $this->tree);

        if ($match) {
            return new Tree($match->getCategory(), $match->getChildren());
        }

        // active id is not part of $this->tree? active id is root or used as first level
        if ($this->active && $this->active->getId() === $categoryId) {
            return $this;
        }

        return null;
    }

    public function getApiAlias(): string
    {
        return 'category_tree';
    }

    /**
     * @param TreeItem[] $tree
     */
    private function find(string $categoryId, array $tree): ?TreeItem
    {
        if (isset($tree[$categoryId])) {
            return $tree[$categoryId];
        }

        foreach ($tree as $item) {
            $nested = $this->find($categoryId, $item->getChildren());

            if ($nested) {
                return $nested;
            }
        }

        return null;
    }
}
