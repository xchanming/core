<?php declare(strict_types=1);

namespace Cicada\Core\System\Country\Aggregate\CountryState;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Country\Aggregate\CountryStateTranslation\CountryStateTranslationCollection;
use Cicada\Core\System\Country\CountryEntity;

#[Package('fundamentals@discovery')]
class CountryStateEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $countryId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shortCode;

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
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @var CountryEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $country;

    /**
     * @var CountryStateTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var CustomerAddressCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customerAddresses;

    /**
     * @var OrderAddressCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderAddresses;

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): void
    {
        $this->shortCode = $shortCode;
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

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): void
    {
        $this->country = $country;
    }

    public function getTranslations(): ?CountryStateTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CountryStateTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getCustomerAddresses(): ?CustomerAddressCollection
    {
        return $this->customerAddresses;
    }

    public function setCustomerAddresses(CustomerAddressCollection $customerAddresses): void
    {
        $this->customerAddresses = $customerAddresses;
    }

    public function getOrderAddresses(): ?OrderAddressCollection
    {
        return $this->orderAddresses;
    }

    public function setOrderAddresses(OrderAddressCollection $orderAddresses): void
    {
        $this->orderAddresses = $orderAddresses;
    }
}
