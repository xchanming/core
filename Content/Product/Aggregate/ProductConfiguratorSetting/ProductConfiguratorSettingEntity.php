<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Aggregate\ProductConfiguratorSetting;

use Cicada\Core\Content\Media\MediaEntity;
use Cicada\Core\Content\Product\ProductEntity;
use Cicada\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductConfiguratorSettingEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

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
    protected $optionId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mediaId;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $position;

    /**
     * @var array<Price>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $price;

    /**
     * @var PropertyGroupOptionEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $option;

    /**
     * @var MediaEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $media;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $selected = false;

    /**
     * @var ProductEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $product;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getOptionId(): string
    {
        return $this->optionId;
    }

    public function setOptionId(string $optionId): void
    {
        $this->optionId = $optionId;
    }

    /**
     * @return array<Price>|null
     */
    public function getPrice(): ?array
    {
        return $this->price;
    }

    /**
     * @param array<Price> $price
     */
    public function setPrice(array $price): void
    {
        $this->price = $price;
    }

    public function getOption(): ?PropertyGroupOptionEntity
    {
        return $this->option;
    }

    public function setOption(PropertyGroupOptionEntity $option): void
    {
        $this->option = $option;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    public function setSelected(bool $selected): void
    {
        $this->selected = $selected;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }
}
