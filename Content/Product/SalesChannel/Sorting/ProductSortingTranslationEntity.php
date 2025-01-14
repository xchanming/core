<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Sorting;

use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSortingTranslationEntity extends TranslationEntity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSortingId;

    /**
     * @var ProductSortingEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSorting;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $label;

    public function getProductSortingId(): string
    {
        return $this->productSortingId;
    }

    public function setProductSortingId(string $productSortingId): void
    {
        $this->productSortingId = $productSortingId;
    }

    public function getProductSorting(): ?ProductSortingEntity
    {
        return $this->productSorting;
    }

    public function setProductSorting(?ProductSortingEntity $productSorting): void
    {
        $this->productSorting = $productSorting;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getApiAlias(): string
    {
        return 'product_sorting_translation';
    }
}
