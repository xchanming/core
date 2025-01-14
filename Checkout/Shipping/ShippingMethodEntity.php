<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Shipping;

use Cicada\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodTranslation\ShippingMethodTranslationCollection;
use Cicada\Core\Content\Media\MediaEntity;
use Cicada\Core\Content\Rule\RuleEntity;
use Cicada\Core\Framework\App\Aggregate\AppShippingMethod\AppShippingMethodEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Contract\IdAware;
use Cicada\Core\Framework\DataAbstractionLayer\Contract\RuleIdAware;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\DeliveryTime\DeliveryTimeEntity;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;
use Cicada\Core\System\Tag\TagCollection;
use Cicada\Core\System\Tax\TaxEntity;

#[Package('checkout')]
class ShippingMethodEntity extends Entity implements IdAware, RuleIdAware
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    final public const TAX_TYPE_AUTO = 'auto';
    final public const TAX_TYPE_FIXED = 'fixed';
    final public const TAX_TYPE_HIGHEST = 'highest';
    final public const POSITION_DEFAULT = 1;
    final public const ACTIVE_DEFAULT = false;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $position;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $description;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $trackingUrl;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $deliveryTimeId;

    /**
     * @var DeliveryTimeEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $deliveryTime;

    /**
     * @var ShippingMethodTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var OrderDeliveryCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderDeliveries;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelDefaultAssignments;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannels;

    /**
     * @var RuleEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $availabilityRule;

    protected ?string $availabilityRuleId = null;

    /**
     * @var ShippingMethodPriceCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $prices;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mediaId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxId;

    /**
     * @var MediaEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $media;

    /**
     * @var TagCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $tags;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxType;

    /**
     * @deprecated tag:v6.7.0 - will not be nullable
     */
    protected ?string $technicalName = null;

    /**
     * @var TaxEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $tax;

    protected ?AppShippingMethodEntity $appShippingMethod = null;

    public function __construct()
    {
        $this->prices = new ShippingMethodPriceCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    public function setTrackingUrl(?string $trackingUrl): void
    {
        $this->trackingUrl = $trackingUrl;
    }

    public function getDeliveryTimeId(): string
    {
        return $this->deliveryTimeId;
    }

    public function setDeliveryTimeId(string $deliveryTimeId): void
    {
        $this->deliveryTimeId = $deliveryTimeId;
    }

    public function getDeliveryTime(): ?DeliveryTimeEntity
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(DeliveryTimeEntity $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime;
    }

    public function getTranslations(): ?ShippingMethodTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ShippingMethodTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getOrderDeliveries(): ?OrderDeliveryCollection
    {
        return $this->orderDeliveries;
    }

    public function setOrderDeliveries(OrderDeliveryCollection $orderDeliveries): void
    {
        $this->orderDeliveries = $orderDeliveries;
    }

    public function getSalesChannelDefaultAssignments(): ?SalesChannelCollection
    {
        return $this->salesChannelDefaultAssignments;
    }

    public function setSalesChannelDefaultAssignments(SalesChannelCollection $salesChannelDefaultAssignments): void
    {
        $this->salesChannelDefaultAssignments = $salesChannelDefaultAssignments;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    public function setSalesChannels(SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    public function getPrices(): ShippingMethodPriceCollection
    {
        return $this->prices;
    }

    public function setPrices(ShippingMethodPriceCollection $prices): void
    {
        $this->prices = $prices;
    }

    public function getAvailabilityRule(): ?RuleEntity
    {
        return $this->availabilityRule;
    }

    public function setAvailabilityRule(?RuleEntity $availabilityRule): void
    {
        $this->availabilityRule = $availabilityRule;
    }

    public function getAvailabilityRuleId(): ?string
    {
        return $this->availabilityRuleId;
    }

    public function setAvailabilityRuleId(?string $availabilityRuleId): void
    {
        $this->availabilityRuleId = $availabilityRuleId;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getTaxType(): string
    {
        return $this->taxType;
    }

    public function setTaxType(string $taxType): void
    {
        $this->taxType = $taxType;
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - return type will not be nullable
     */
    public function getTechnicalName(): ?string
    {
        if (!$this->technicalName) {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Parameter `technical_name` will be required');
        }

        return $this->technicalName;
    }

    /**
     * @deprecated tag:v6.7.0 - reason:parameter-type-change - parameter type will not be nullable
     */
    public function setTechnicalName(?string $technicalName): void
    {
        if (!$technicalName) {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Parameter `technical_name` will be required');
        }

        $this->technicalName = $technicalName;
    }

    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    public function setTax(TaxEntity $tax): void
    {
        $this->tax = $tax;
    }

    public function getAppShippingMethod(): ?AppShippingMethodEntity
    {
        return $this->appShippingMethod;
    }

    public function setAppShippingMethod(?AppShippingMethodEntity $appShippingMethod): void
    {
        $this->appShippingMethod = $appShippingMethod;
    }
}
