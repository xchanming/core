<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice;

use Cicada\Core\Checkout\Shipping\ShippingMethodEntity;
use Cicada\Core\Content\Rule\RuleEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Contract\IdAware;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class ShippingMethodPriceEntity extends Entity implements IdAware
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $ruleId;

    /**
     * @var int|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $calculation;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $quantityStart;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $quantityEnd;

    /**
     * @var ShippingMethodEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethod;

    /**
     * @var RuleEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $rule;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $calculationRuleId;

    /**
     * @var RuleEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $calculationRule;

    /**
     * @var PriceCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $currencyPrice;

    public function getShippingMethodId(): string
    {
        return $this->shippingMethodId;
    }

    public function setShippingMethodId(string $shippingMethodId): void
    {
        $this->shippingMethodId = $shippingMethodId;
    }

    public function getQuantityStart(): ?float
    {
        return $this->quantityStart;
    }

    public function setQuantityStart(float $quantityStart): void
    {
        $this->quantityStart = $quantityStart;
    }

    public function getQuantityEnd(): ?float
    {
        return $this->quantityEnd;
    }

    public function setQuantityEnd(float $quantityEnd): void
    {
        $this->quantityEnd = $quantityEnd;
    }

    public function getCalculation(): ?int
    {
        return $this->calculation;
    }

    public function setCalculation(int $calculation): void
    {
        $this->calculation = $calculation;
    }

    public function getShippingMethod(): ?ShippingMethodEntity
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethodEntity $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getRuleId(): ?string
    {
        return $this->ruleId;
    }

    public function setRuleId(string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getRule(): ?RuleEntity
    {
        return $this->rule;
    }

    public function setRule(?RuleEntity $rule): void
    {
        $this->rule = $rule;
    }

    public function getCalculationRuleId(): ?string
    {
        return $this->calculationRuleId;
    }

    public function setCalculationRuleId(?string $calculationRuleId): void
    {
        $this->calculationRuleId = $calculationRuleId;
    }

    public function getCalculationRule(): ?RuleEntity
    {
        return $this->calculationRule;
    }

    public function setCalculationRule(?RuleEntity $calculationRule): void
    {
        $this->calculationRule = $calculationRule;
    }

    public function getCurrencyPrice(): ?PriceCollection
    {
        return $this->currencyPrice;
    }

    public function setCurrencyPrice(?PriceCollection $price): void
    {
        $this->currencyPrice = $price;
    }
}
