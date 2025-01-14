<?php declare(strict_types=1);

namespace Cicada\Core\System\Currency\Aggregate\CurrencyCountryRounding;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Country\CountryEntity;
use Cicada\Core\System\Currency\CurrencyEntity;

#[Package('buyers-experience')]
class CurrencyCountryRoundingEntity extends Entity
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
    protected $countryId;

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
     * @var CurrencyEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $currency;

    /**
     * @var CountryEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $country;

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
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

    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): void
    {
        $this->country = $country;
    }
}
