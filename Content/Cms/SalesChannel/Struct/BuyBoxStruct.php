<?php declare(strict_types=1);

namespace Cicada\Core\Content\Cms\SalesChannel\Struct;

use Cicada\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Cicada\Core\Content\Property\PropertyGroupCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('buyers-experience')]
class BuyBoxStruct extends Struct
{
    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productId;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $totalReviews;

    /**
     * @var SalesChannelProductEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $product;

    /**
     * @var PropertyGroupCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $configuratorSettings;

    public function getProduct(): ?SalesChannelProductEntity
    {
        return $this->product;
    }

    public function getConfiguratorSettings(): ?PropertyGroupCollection
    {
        return $this->configuratorSettings;
    }

    public function setConfiguratorSettings(?PropertyGroupCollection $configuratorSettings): void
    {
        $this->configuratorSettings = $configuratorSettings;
    }

    public function setProduct(SalesChannelProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getTotalReviews(): ?int
    {
        return $this->totalReviews;
    }

    public function setTotalReviews(int $totalReviews): void
    {
        $this->totalReviews = $totalReviews;
    }

    public function getApiAlias(): string
    {
        return 'cms_buy_box';
    }
}
