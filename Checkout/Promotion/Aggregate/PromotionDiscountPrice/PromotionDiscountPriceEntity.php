<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Aggregate\PromotionDiscountPrice;

use Cicada\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Currency\CurrencyEntity;

#[Package('checkout')]
class PromotionDiscountPriceEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $currencyId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $discountId;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $price;

    /**
     * @var PromotionDiscountEntity
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $promotionDiscount;

    /**
     * @var CurrencyEntity
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $currency;

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getDiscountId(): string
    {
        return $this->discountId;
    }

    public function setDiscountId(string $discountId): void
    {
        $this->discountId = $discountId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getCurrency(): CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getPromotionDiscount(): PromotionDiscountEntity
    {
        return $this->promotionDiscount;
    }

    public function setPromotionDiscount(PromotionDiscountEntity $promotionDiscount): void
    {
        $this->promotionDiscount = $promotionDiscount;
    }
}
