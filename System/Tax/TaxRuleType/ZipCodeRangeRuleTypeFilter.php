<?php declare(strict_types=1);

namespace Cicada\Core\System\Tax\TaxRuleType;

use Cicada\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Cicada\Core\Checkout\Customer\CustomerEntity;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Tax\Aggregate\TaxRule\TaxRuleEntity;

#[Package('checkout')]
class ZipCodeRangeRuleTypeFilter extends AbstractTaxRuleTypeFilter
{
    final public const TECHNICAL_NAME = 'zip_code_range';

    public function match(TaxRuleEntity $taxRuleEntity, ?CustomerEntity $customer, ShippingLocation $shippingLocation): bool
    {
        if ($taxRuleEntity->getType()->getTechnicalName() !== self::TECHNICAL_NAME
            || !$this->metPreconditions($taxRuleEntity, $shippingLocation)
        ) {
            return false;
        }

        $zipCode = $this->getZipCode($shippingLocation);

        $toZipCode = $taxRuleEntity->getData()['toZipCode'] ?? null;
        $fromZipCode = $taxRuleEntity->getData()['fromZipCode'] ?? null;

        if ($fromZipCode === null || $toZipCode === null || $zipCode < $fromZipCode || $zipCode > $toZipCode) {
            return false;
        }

        if ($taxRuleEntity->getActiveFrom() !== null) {
            return $this->isTaxActive($taxRuleEntity);
        }

        return true;
    }

    private function metPreconditions(TaxRuleEntity $taxRuleEntity, ShippingLocation $shippingLocation): bool
    {
        if ($this->getZipCode($shippingLocation) === null) {
            return false;
        }

        return $shippingLocation->getCountry()->getId() === $taxRuleEntity->getCountryId();
    }

    private function getZipCode(ShippingLocation $shippingLocation): ?string
    {
        return $shippingLocation->getAddress()?->getZipcode();
    }
}
