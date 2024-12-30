<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Aggregate\CustomerWishlistProduct;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistEntity;
use Cicada\Core\Content\Product\ProductEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class CustomerWishlistProductEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $wishlistId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productVersionId;

    /**
     * @var CustomerWishlistEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $wishlist;

    /**
     * @var ProductEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $product;

    public function getWishlist(): ?CustomerWishlistEntity
    {
        return $this->wishlist;
    }

    public function setWishlist(CustomerWishlistEntity $wishlist): void
    {
        $this->wishlist = $wishlist;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getWishlistId(): string
    {
        return $this->wishlistId;
    }

    public function setWishlistId(string $wishlistId): void
    {
        $this->wishlistId = $wishlistId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }
}
