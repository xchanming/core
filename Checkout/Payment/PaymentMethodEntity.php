<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Payment;

use Cicada\Core\Checkout\Customer\CustomerCollection;
use Cicada\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Cicada\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationCollection;
use Cicada\Core\Content\Media\MediaEntity;
use Cicada\Core\Content\Rule\RuleEntity;
use Cicada\Core\Framework\App\Aggregate\AppPaymentMethod\AppPaymentMethodEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Contract\IdAware;
use Cicada\Core\Framework\DataAbstractionLayer\Contract\RuleIdAware;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\PluginEntity;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;

#[Package('checkout')]
class PaymentMethodEntity extends Entity implements IdAware, RuleIdAware
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $pluginId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $handlerIdentifier;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $distinguishableName;

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
    protected $position;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $afterOrderEnabled;

    /**
     * @var PluginEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $plugin;

    /**
     * @var PaymentMethodTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var OrderTransactionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderTransactions;

    /**
     * @var CustomerCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customers;

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

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $availabilityRuleId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mediaId;

    /**
     * @var MediaEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $media;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $formattedHandlerIdentifier;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shortName;

    /**
     * @deprecated tag:v6.7.0 - will not be nullable
     */
    protected ?string $technicalName = null;

    /**
     * @var AppPaymentMethodEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appPaymentMethod;

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    protected bool $synchronous = false;

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    protected bool $asynchronous = false;

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    protected bool $prepared = false;

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    protected bool $refundable = false;

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    protected bool $recurring = false;

    public function getPluginId(): ?string
    {
        return $this->pluginId;
    }

    public function setPluginId(?string $pluginId): void
    {
        $this->pluginId = $pluginId;
    }

    public function getHandlerIdentifier(): string
    {
        return $this->handlerIdentifier;
    }

    public function setHandlerIdentifier(string $handlerIdentifier): void
    {
        $this->handlerIdentifier = $handlerIdentifier;
    }

    public function setFormattedHandlerIdentifier(string $formattedHandlerIdentifier): void
    {
        $this->formattedHandlerIdentifier = $formattedHandlerIdentifier;
    }

    public function getFormattedHandlerIdentifier(): string
    {
        return $this->formattedHandlerIdentifier;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDistinguishableName(): ?string
    {
        return $this->distinguishableName;
    }

    public function setDistinguishableName(?string $distinguishableName): void
    {
        $this->distinguishableName = $distinguishableName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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

    public function getPlugin(): ?PluginEntity
    {
        return $this->plugin;
    }

    public function setPlugin(PluginEntity $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getTranslations(): ?PaymentMethodTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(PaymentMethodTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getOrderTransactions(): ?OrderTransactionCollection
    {
        return $this->orderTransactions;
    }

    public function setOrderTransactions(OrderTransactionCollection $orderTransactions): void
    {
        $this->orderTransactions = $orderTransactions;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
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

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getAfterOrderEnabled(): bool
    {
        return $this->afterOrderEnabled;
    }

    public function setAfterOrderEnabled(bool $afterOrderEnabled): void
    {
        $this->afterOrderEnabled = $afterOrderEnabled;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - return type will not be nullable
     */
    public function getTechnicalName(): ?string
    {
        if (!$this->technicalName) {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `technical_name` will be required');
        }

        return $this->technicalName;
    }

    /**
     * @deprecated tag:v6.7.0 - reason:parameter-type-change - property type will not be nullable
     */
    public function setTechnicalName(?string $technicalName): void
    {
        if (!$technicalName) {
            Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `technical_name` will be required');
        }

        $this->technicalName = $technicalName;
    }

    public function getAppPaymentMethod(): ?AppPaymentMethodEntity
    {
        return $this->appPaymentMethod;
    }

    public function setAppPaymentMethod(?AppPaymentMethodEntity $appPaymentMethod): void
    {
        $this->appPaymentMethod = $appPaymentMethod;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function isSynchronous(): bool
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `synchronous` will be removed');

        return $this->synchronous;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function setSynchronous(bool $synchronous): void
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `synchronous` will be removed');
        $this->synchronous = $synchronous;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function isAsynchronous(): bool
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `asynchronous` will be removed');

        return $this->asynchronous;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function setAsynchronous(bool $asynchronous): void
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `asynchronous` will be removed');
        $this->asynchronous = $asynchronous;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function isPrepared(): bool
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `prepared` will be removed');

        return $this->prepared;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function setPrepared(bool $prepared): void
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `prepared` will be removed');
        $this->prepared = $prepared;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function isRefundable(): bool
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `refundable` will be removed');

        return $this->refundable;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function setRefundable(bool $refundable): void
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `refundable` will be removed');
        $this->refundable = $refundable;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function isRecurring(): bool
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `recurring` will be removed');

        return $this->recurring;
    }

    /**
     * @deprecated tag:v6.7.0 - will be removed
     */
    public function setRecurring(bool $recurring): void
    {
        Feature::triggerDeprecationOrThrow('v6.7.0.0', 'Property `recurring` will be removed');
        $this->recurring = $recurring;
    }
}
