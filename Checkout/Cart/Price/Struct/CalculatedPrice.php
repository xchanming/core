<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Price\Struct;

use Cicada\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Cicada\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;
use Cicada\Core\Framework\Util\FloatComparator;

#[Package('checkout')]
class CalculatedPrice extends Struct
{
    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $unitPrice;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $quantity;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $totalPrice;

    /**
     * @var CalculatedTaxCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $calculatedTaxes;

    /**
     * @var TaxRuleCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxRules;

    /**
     * @var ReferencePrice|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $referencePrice;

    /**
     * @var ListPrice|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $listPrice;

    /**
     * @var RegulationPrice|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $regulationPrice;

    public function __construct(
        float $unitPrice,
        float $totalPrice,
        CalculatedTaxCollection $calculatedTaxes,
        TaxRuleCollection $taxRules,
        int $quantity = 1,
        ?ReferencePrice $referencePrice = null,
        ?ListPrice $listPrice = null,
        ?RegulationPrice $regulationPrice = null
    ) {
        $this->unitPrice = FloatComparator::cast($unitPrice);
        $this->totalPrice = FloatComparator::cast($totalPrice);
        $this->calculatedTaxes = $calculatedTaxes;
        $this->taxRules = $taxRules;
        $this->quantity = $quantity;
        $this->referencePrice = $referencePrice;
        $this->listPrice = $listPrice;
        $this->regulationPrice = $regulationPrice;
    }

    public function getTotalPrice(): float
    {
        return FloatComparator::cast($this->totalPrice);
    }

    public function getCalculatedTaxes(): CalculatedTaxCollection
    {
        return $this->calculatedTaxes;
    }

    public function setCalculatedTaxes(CalculatedTaxCollection $calculatedTaxes): void
    {
        $this->calculatedTaxes = $calculatedTaxes;
    }

    public function getTaxRules(): TaxRuleCollection
    {
        return $this->taxRules;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReferencePrice(): ?ReferencePrice
    {
        return $this->referencePrice;
    }

    public function getListPrice(): ?ListPrice
    {
        return $this->listPrice;
    }

    public function getRegulationPrice(): ?RegulationPrice
    {
        return $this->regulationPrice;
    }

    public function getApiAlias(): string
    {
        return 'calculated_price';
    }

    /**
     * Changing a price should always be a full change, otherwise you have
     * mismatching information regarding the unit, total and tax values.
     */
    public function overwrite(float $unitPrice, float $totalPrice, CalculatedTaxCollection $taxes): void
    {
        $this->unitPrice = $unitPrice;
        $this->totalPrice = $totalPrice;
        $this->calculatedTaxes = $taxes;
    }
}
