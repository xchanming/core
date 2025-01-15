<?php declare(strict_types=1);

namespace Cicada\Core\Content\Rule;

use Cicada\Core\Checkout\Payment\PaymentMethodCollection;
use Cicada\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountCollection;
use Cicada\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupCollection;
use Cicada\Core\Checkout\Promotion\PromotionCollection;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Cicada\Core\Checkout\Shipping\ShippingMethodCollection;
use Cicada\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceCollection;
use Cicada\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Cicada\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Rule;
use Cicada\Core\System\Tag\TagCollection;
use Cicada\Core\System\TaxProvider\TaxProviderCollection;

#[Package('services-settings')]
class RuleEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $description;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $priority;

    /**
     * @internal
     *
     * @var string|Rule|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $payload;

    /**
     * @var string[]|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $moduleTypes;

    /**
     * @var ProductPriceCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productPrices;

    /**
     * @var ShippingMethodCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethods;

    /**
     * @var PaymentMethodCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $paymentMethods;

    /**
     * @var RuleConditionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $conditions;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $invalid;

    /**
     * @var string[]|null
     */
    protected ?array $areas = null;

    /**
     * @var ShippingMethodPriceCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodPrices;

    /**
     * @var PromotionDiscountCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $promotionDiscounts;

    /**
     * @var PromotionSetGroupCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $promotionSetGroups;

    /**
     * @var ShippingMethodPriceCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodPriceCalculations;

    /**
     * @var PromotionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $personaPromotions;

    /**
     * @var FlowSequenceCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $flowSequences;

    /**
     * @var TagCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $tags;

    /**
     * @var PromotionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderPromotions;

    /**
     * @var PromotionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cartPromotions;

    protected ?TaxProviderCollection $taxProviders = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Rule|string|null
     */
    public function getPayload()
    {
        $this->checkIfPropertyAccessIsAllowed('payload');

        return $this->payload;
    }

    /**
     * @internal
     *
     * @param Rule|string|null $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getProductPrices(): ?ProductPriceCollection
    {
        return $this->productPrices;
    }

    public function setProductPrices(ProductPriceCollection $productPrices): void
    {
        $this->productPrices = $productPrices;
    }

    public function getShippingMethods(): ?ShippingMethodCollection
    {
        return $this->shippingMethods;
    }

    public function setShippingMethods(ShippingMethodCollection $shippingMethods): void
    {
        $this->shippingMethods = $shippingMethods;
    }

    public function getPaymentMethods(): ?PaymentMethodCollection
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(PaymentMethodCollection $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    public function getConditions(): ?RuleConditionCollection
    {
        return $this->conditions;
    }

    public function setConditions(RuleConditionCollection $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function isInvalid(): bool
    {
        return $this->invalid;
    }

    public function setInvalid(bool $invalid): void
    {
        $this->invalid = $invalid;
    }

    /**
     * @return string[]|null
     */
    public function getAreas(): ?array
    {
        return $this->areas;
    }

    /**
     * @param string[] $areas
     */
    public function setAreas(array $areas): void
    {
        $this->areas = $areas;
    }

    /**
     * @return string[]|null
     */
    public function getModuleTypes(): ?array
    {
        return $this->moduleTypes;
    }

    /**
     * @param string[]|null $moduleTypes
     */
    public function setModuleTypes(?array $moduleTypes): void
    {
        $this->moduleTypes = $moduleTypes;
    }

    public function getShippingMethodPrices(): ?ShippingMethodPriceCollection
    {
        return $this->shippingMethodPrices;
    }

    public function setShippingMethodPrices(ShippingMethodPriceCollection $shippingMethodPrices): void
    {
        $this->shippingMethodPrices = $shippingMethodPrices;
    }

    public function getPromotionDiscounts(): ?PromotionDiscountCollection
    {
        return $this->promotionDiscounts;
    }

    public function setPromotionDiscounts(PromotionDiscountCollection $promotionDiscounts): void
    {
        $this->promotionDiscounts = $promotionDiscounts;
    }

    public function getPromotionSetGroups(): ?PromotionSetGroupCollection
    {
        return $this->promotionSetGroups;
    }

    public function setPromotionSetGroups(PromotionSetGroupCollection $promotionSetGroups): void
    {
        $this->promotionSetGroups = $promotionSetGroups;
    }

    public function getShippingMethodPriceCalculations(): ?ShippingMethodPriceCollection
    {
        return $this->shippingMethodPriceCalculations;
    }

    public function setShippingMethodPriceCalculations(ShippingMethodPriceCollection $shippingMethodPriceCalculations): void
    {
        $this->shippingMethodPriceCalculations = $shippingMethodPriceCalculations;
    }

    /**
     * Gets a list of all promotions where this rule
     * is being used within the Persona Conditions
     */
    public function getPersonaPromotions(): ?PromotionCollection
    {
        return $this->personaPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Persona Condition
     */
    public function setPersonaPromotions(PromotionCollection $personaPromotions): void
    {
        $this->personaPromotions = $personaPromotions;
    }

    public function getFlowSequences(): ?FlowSequenceCollection
    {
        return $this->flowSequences;
    }

    public function setFlowSequences(FlowSequenceCollection $flowSequences): void
    {
        $this->flowSequences = $flowSequences;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * Gets a list of all promotions where this rule is
     * being used within the Order Conditions.
     */
    public function getOrderPromotions(): ?PromotionCollection
    {
        return $this->orderPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Order Condition.
     */
    public function setOrderPromotions(PromotionCollection $orderPromotions): void
    {
        $this->orderPromotions = $orderPromotions;
    }

    /**
     * Gets a list of all promotions where this rule is
     * being used within the Cart Conditions.
     */
    public function getCartPromotions(): ?PromotionCollection
    {
        return $this->cartPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Cart Condition.
     */
    public function setCartPromotions(PromotionCollection $cartPromotions): void
    {
        $this->cartPromotions = $cartPromotions;
    }

    public function getTaxProviders(): ?TaxProviderCollection
    {
        return $this->taxProviders;
    }

    public function setTaxProviders(TaxProviderCollection $taxProviders): void
    {
        $this->taxProviders = $taxProviders;
    }
}
