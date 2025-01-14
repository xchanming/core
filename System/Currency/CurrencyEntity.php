<?php declare(strict_types=1);

namespace Cicada\Core\System\Currency;

use Cicada\Core\Checkout\Order\OrderCollection;
use Cicada\Core\Checkout\Promotion\Aggregate\PromotionDiscountPrice\PromotionDiscountPriceCollection;
use Cicada\Core\Content\ProductExport\ProductExportCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;
use Cicada\Core\System\Currency\Aggregate\CurrencyTranslation\CurrencyTranslationCollection;
use Cicada\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;

#[Package('buyers-experience')]
class CurrencyEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $isoCode;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $factor;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $symbol;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shortName;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $position;

    /**
     * @var CurrencyTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var OrderCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orders;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannels;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelDefaultAssignments;

    /**
     * @var SalesChannelDomainCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelDomains;

    /**
     * @var PromotionDiscountPriceCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $promotionDiscountPrices;

    /**
     * @var bool|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $isSystemDefault;

    /**
     * @var ProductExportCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productExports;

    /**
     * @var CurrencyCountryRoundingCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $countryRoundings;

    /**
     * @var CashRoundingConfig
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $itemRounding;

    /**
     * @var CashRoundingConfig
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $totalRounding;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxFreeFrom;

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setIsoCode(string $isoCode): void
    {
        $this->isoCode = $isoCode;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function setFactor(float $factor): void
    {
        $this->factor = $factor;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTranslations(): ?CurrencyTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CurrencyTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    public function setSalesChannels(SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    public function getSalesChannelDefaultAssignments(): ?SalesChannelCollection
    {
        return $this->salesChannelDefaultAssignments;
    }

    public function setSalesChannelDefaultAssignments(SalesChannelCollection $salesChannelDefaultAssignments): void
    {
        $this->salesChannelDefaultAssignments = $salesChannelDefaultAssignments;
    }

    public function getSalesChannelDomains(): ?SalesChannelDomainCollection
    {
        return $this->salesChannelDomains;
    }

    public function setSalesChannelDomains(SalesChannelDomainCollection $salesChannelDomains): void
    {
        $this->salesChannelDomains = $salesChannelDomains;
    }

    public function getIsSystemDefault(): ?bool
    {
        return $this->isSystemDefault;
    }

    public function setIsSystemDefault(bool $isSystemDefault): void
    {
        $this->isSystemDefault = $isSystemDefault;
    }

    public function getPromotionDiscountPrices(): ?PromotionDiscountPriceCollection
    {
        return $this->promotionDiscountPrices;
    }

    public function setPromotionDiscountPrices(PromotionDiscountPriceCollection $promotionDiscountPrices): void
    {
        $this->promotionDiscountPrices = $promotionDiscountPrices;
    }

    public function getProductExports(): ?ProductExportCollection
    {
        return $this->productExports;
    }

    public function setProductExports(ProductExportCollection $productExports): void
    {
        $this->productExports = $productExports;
    }

    public function getCountryRoundings(): ?CurrencyCountryRoundingCollection
    {
        return $this->countryRoundings;
    }

    public function setCountryRoundings(CurrencyCountryRoundingCollection $countryRoundings): void
    {
        $this->countryRoundings = $countryRoundings;
    }

    public function getItemRounding(): CashRoundingConfig
    {
        return $this->itemRounding;
    }

    public function setItemRounding(CashRoundingConfig $itemRounding): void
    {
        $this->itemRounding = $itemRounding;
    }

    public function getTotalRounding(): CashRoundingConfig
    {
        return $this->totalRounding;
    }

    public function setTotalRounding(CashRoundingConfig $totalRounding): void
    {
        $this->totalRounding = $totalRounding;
    }

    public function getTaxFreeFrom(): ?float
    {
        return $this->taxFreeFrom;
    }

    public function setTaxFreeFrom(?float $taxFreeFrom): void
    {
        $this->taxFreeFrom = $taxFreeFrom;
    }
}
