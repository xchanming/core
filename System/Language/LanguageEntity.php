<?php declare(strict_types=1);

namespace Cicada\Core\System\Language;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation\CustomerGroupTranslationCollection;
use Cicada\Core\Checkout\Customer\CustomerCollection;
use Cicada\Core\Checkout\Order\OrderCollection;
use Cicada\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationCollection;
use Cicada\Core\Checkout\Promotion\Aggregate\PromotionTranslation\PromotionTranslationCollection;
use Cicada\Core\Checkout\Shipping\Aggregate\ShippingMethodTranslation\ShippingMethodTranslationCollection;
use Cicada\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationCollection;
use Cicada\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationEntity;
use Cicada\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationEntity;
use Cicada\Core\Content\ImportExport\ImportExportProfileTranslationCollection;
use Cicada\Core\Content\LandingPage\Aggregate\LandingPageTranslation\LandingPageTranslationCollection;
use Cicada\Core\Content\MailTemplate\Aggregate\MailHeaderFooter\MailHeaderFooterCollection;
use Cicada\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeDefinition;
use Cicada\Core\Content\MailTemplate\MailTemplateCollection;
use Cicada\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationCollection;
use Cicada\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientCollection;
use Cicada\Core\Content\Product\Aggregate\ProductCrossSellingTranslation\ProductCrossSellingTranslationCollection;
use Cicada\Core\Content\Product\Aggregate\ProductFeatureSetTranslation\ProductFeatureSetTranslationCollection;
use Cicada\Core\Content\Product\Aggregate\ProductKeywordDictionary\ProductKeywordDictionaryCollection;
use Cicada\Core\Content\Product\Aggregate\ProductManufacturerTranslation\ProductManufacturerTranslationCollection;
use Cicada\Core\Content\Product\Aggregate\ProductReview\ProductReviewCollection;
use Cicada\Core\Content\Product\Aggregate\ProductSearchConfig\ProductSearchConfigEntity;
use Cicada\Core\Content\Product\Aggregate\ProductSearchKeyword\ProductSearchKeywordCollection;
use Cicada\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationCollection;
use Cicada\Core\Content\Product\SalesChannel\Sorting\ProductSortingTranslationCollection;
use Cicada\Core\Content\ProductStream\Aggregate\ProductStreamTranslation\ProductStreamTranslationCollection;
use Cicada\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation\PropertyGroupOptionTranslationCollection;
use Cicada\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationCollection;
use Cicada\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use Cicada\Core\Framework\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationCollection;
use Cicada\Core\Framework\App\Aggregate\AppScriptConditionTranslation\AppScriptConditionTranslationCollection;
use Cicada\Core\Framework\App\Aggregate\AppTranslation\AppTranslationCollection;
use Cicada\Core\Framework\App\Aggregate\CmsBlockTranslation\AppCmsBlockTranslationCollection;
use Cicada\Core\Framework\App\Aggregate\FlowActionTranslation\AppFlowActionTranslationCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Aggregate\PluginTranslation\PluginTranslationCollection;
use Cicada\Core\Framework\Struct\Collection;
use Cicada\Core\System\Country\Aggregate\CountryStateTranslation\CountryStateTranslationCollection;
use Cicada\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationCollection;
use Cicada\Core\System\Currency\Aggregate\CurrencyTranslation\CurrencyTranslationCollection;
use Cicada\Core\System\DeliveryTime\DeliveryTimeCollection;
use Cicada\Core\System\Locale\Aggregate\LocaleTranslation\LocaleTranslationCollection;
use Cicada\Core\System\Locale\LocaleEntity;
use Cicada\Core\System\NumberRange\Aggregate\NumberRangeTranslation\NumberRangeTranslationCollection;
use Cicada\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation\NumberRangeTypeTranslationCollection;
use Cicada\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Cicada\Core\System\SalesChannel\Aggregate\SalesChannelTranslation\SalesChannelTranslationCollection;
use Cicada\Core\System\SalesChannel\Aggregate\SalesChannelTypeTranslation\SalesChannelTypeTranslationCollection;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;
use Cicada\Core\System\Salutation\Aggregate\SalutationTranslation\SalutationTranslationCollection;
use Cicada\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateTranslationCollection;
use Cicada\Core\System\StateMachine\StateMachineTranslationCollection;
use Cicada\Core\System\Tax\Aggregate\TaxRuleTypeTranslation\TaxRuleTypeTranslationCollection;
use Cicada\Core\System\TaxProvider\Aggregate\TaxProviderTranslation\TaxProviderTranslationCollection;
use Cicada\Core\System\Unit\Aggregate\UnitTranslation\UnitTranslationCollection;

#[Package('buyers-experience')]
class LanguageEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $parentId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $localeId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translationCodeId;

    /**
     * @var LocaleEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translationCode;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var LocaleEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $locale;

    /**
     * @var LanguageEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $parent;

    /**
     * @var LanguageCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $children;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannels;

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
     * @var CategoryTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $categoryTranslations;

    /**
     * @var CountryStateTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $countryStateTranslations;

    /**
     * @var CountryTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $countryTranslations;

    /**
     * @var CurrencyTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $currencyTranslations;

    /**
     * @var CustomerGroupTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customerGroupTranslations;

    /**
     * @var LocaleTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $localeTranslations;

    /**
     * @var MediaTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mediaTranslations;

    /**
     * @var PaymentMethodTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $paymentMethodTranslations;

    /**
     * @var ProductManufacturerTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productManufacturerTranslations;

    /**
     * @var ProductTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productTranslations;

    /**
     * @var ShippingMethodTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodTranslations;

    /**
     * @var UnitTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $unitTranslations;

    /**
     * @var PropertyGroupTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $propertyGroupTranslations;

    /**
     * @var PropertyGroupOptionTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $propertyGroupOptionTranslations;

    /**
     * @var SalesChannelTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelTranslations;

    /**
     * @var SalesChannelTypeTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelTypeTranslations;

    /**
     * @var SalutationTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salutationTranslations;

    /**
     * @var SalesChannelDomainCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $salesChannelDomains;

    /**
     * @var PluginTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $pluginTranslations;

    /**
     * @var ProductStreamTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productStreamTranslations;

    /**
     * @var StateMachineTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $stateMachineTranslations;

    /**
     * @var StateMachineStateTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $stateMachineStateTranslations;

    /**
     * @var EntityCollection<CmsPageTranslationEntity>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsPageTranslations;

    /**
     * @var EntityCollection<CmsSlotTranslationEntity>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsSlotTranslations;

    /**
     * @var MailTemplateCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mailTemplateTranslations;

    /**
     * @var MailHeaderFooterCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mailHeaderFooterTranslations;

    /**
     * @var DeliveryTimeCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $deliveryTimeTranslations;

    /**
     * @var NewsletterRecipientCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $newsletterRecipients;

    /**
     * @var OrderCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orders;

    /**
     * @var NumberRangeTypeTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $numberRangeTypeTranslations;

    /**
     * @var ProductSearchKeywordCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSearchKeywords;

    /**
     * @var ProductKeywordDictionaryCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productKeywordDictionaries;

    /**
     * @var MailTemplateTypeDefinition|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mailTemplateTypeTranslations;

    /**
     * @var PromotionTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $promotionTranslations;

    /**
     * @var NumberRangeTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $numberRangeTranslations;

    /**
     * @var ProductReviewCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productReviews;

    /**
     * @var SeoUrlCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $seoUrlTranslations;

    /**
     * @var TaxRuleTypeTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxRuleTypeTranslations;

    /**
     * @var ProductCrossSellingTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productCrossSellingTranslations;

    /**
     * @var ImportExportProfileTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $importExportProfileTranslations;

    /**
     * @var ProductFeatureSetTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productFeatureSetTranslations;

    /**
     * @var AppTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appTranslations;

    /**
     * @var ActionButtonTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $actionButtonTranslations;

    /**
     * @var ProductSortingTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSortingTranslations;

    /**
     * @var ProductSearchConfigEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSearchConfig;

    /**
     * @var LandingPageTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $landingPageTranslations;

    /**
     * @var AppCmsBlockTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appCmsBlockTranslations;

    /**
     * @var AppScriptConditionTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appScriptConditionTranslations;

    /**
     * @var AppFlowActionTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appFlowActionTranslations;

    protected ?TaxProviderTranslationCollection $taxProviderTranslations = null;

    public function getMailHeaderFooterTranslations(): ?MailHeaderFooterCollection
    {
        return $this->mailHeaderFooterTranslations;
    }

    public function setMailHeaderFooterTranslations(MailHeaderFooterCollection $mailHeaderFooterTranslations): void
    {
        $this->mailHeaderFooterTranslations = $mailHeaderFooterTranslations;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getLocaleId(): string
    {
        return $this->localeId;
    }

    public function setLocaleId(string $localeId): void
    {
        $this->localeId = $localeId;
    }

    public function getTranslationCodeId(): ?string
    {
        return $this->translationCodeId;
    }

    public function setTranslationCodeId(?string $translationCodeId): void
    {
        $this->translationCodeId = $translationCodeId;
    }

    public function getTranslationCode(): ?LocaleEntity
    {
        return $this->translationCode;
    }

    public function setTranslationCode(?LocaleEntity $translationCode): void
    {
        $this->translationCode = $translationCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLocale(): ?LocaleEntity
    {
        return $this->locale;
    }

    public function setLocale(LocaleEntity $locale): void
    {
        $this->locale = $locale;
    }

    public function getParent(): ?LanguageEntity
    {
        return $this->parent;
    }

    public function setParent(LanguageEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): ?LanguageCollection
    {
        return $this->children;
    }

    public function setChildren(LanguageCollection $children): void
    {
        $this->children = $children;
    }

    public function getCategoryTranslations(): ?CategoryTranslationCollection
    {
        return $this->categoryTranslations;
    }

    public function setCategoryTranslations(CategoryTranslationCollection $categoryTranslations): void
    {
        $this->categoryTranslations = $categoryTranslations;
    }

    public function getCountryStateTranslations(): ?CountryStateTranslationCollection
    {
        return $this->countryStateTranslations;
    }

    public function setCountryStateTranslations(CountryStateTranslationCollection $countryStateTranslations): void
    {
        $this->countryStateTranslations = $countryStateTranslations;
    }

    public function getCountryTranslations(): ?CountryTranslationCollection
    {
        return $this->countryTranslations;
    }

    public function setCountryTranslations(CountryTranslationCollection $countryTranslations): void
    {
        $this->countryTranslations = $countryTranslations;
    }

    public function getCurrencyTranslations(): ?CurrencyTranslationCollection
    {
        return $this->currencyTranslations;
    }

    public function setCurrencyTranslations(CurrencyTranslationCollection $currencyTranslations): void
    {
        $this->currencyTranslations = $currencyTranslations;
    }

    public function getCustomerGroupTranslations(): ?CustomerGroupTranslationCollection
    {
        return $this->customerGroupTranslations;
    }

    public function setCustomerGroupTranslations(CustomerGroupTranslationCollection $customerGroupTranslations): void
    {
        $this->customerGroupTranslations = $customerGroupTranslations;
    }

    public function getLocaleTranslations(): ?LocaleTranslationCollection
    {
        return $this->localeTranslations;
    }

    public function setLocaleTranslations(LocaleTranslationCollection $localeTranslations): void
    {
        $this->localeTranslations = $localeTranslations;
    }

    public function getMediaTranslations(): ?MediaTranslationCollection
    {
        return $this->mediaTranslations;
    }

    public function setMediaTranslations(MediaTranslationCollection $mediaTranslations): void
    {
        $this->mediaTranslations = $mediaTranslations;
    }

    public function getPaymentMethodTranslations(): ?PaymentMethodTranslationCollection
    {
        return $this->paymentMethodTranslations;
    }

    public function setPaymentMethodTranslations(PaymentMethodTranslationCollection $paymentMethodTranslations): void
    {
        $this->paymentMethodTranslations = $paymentMethodTranslations;
    }

    public function getProductManufacturerTranslations(): ?ProductManufacturerTranslationCollection
    {
        return $this->productManufacturerTranslations;
    }

    public function setProductManufacturerTranslations(ProductManufacturerTranslationCollection $productManufacturerTranslations): void
    {
        $this->productManufacturerTranslations = $productManufacturerTranslations;
    }

    public function getProductTranslations(): ?ProductTranslationCollection
    {
        return $this->productTranslations;
    }

    public function setProductTranslations(ProductTranslationCollection $productTranslations): void
    {
        $this->productTranslations = $productTranslations;
    }

    public function getShippingMethodTranslations(): ?ShippingMethodTranslationCollection
    {
        return $this->shippingMethodTranslations;
    }

    public function setShippingMethodTranslations(ShippingMethodTranslationCollection $shippingMethodTranslations): void
    {
        $this->shippingMethodTranslations = $shippingMethodTranslations;
    }

    public function getUnitTranslations(): ?UnitTranslationCollection
    {
        return $this->unitTranslations;
    }

    public function setUnitTranslations(UnitTranslationCollection $unitTranslations): void
    {
        $this->unitTranslations = $unitTranslations;
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

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function setSalesChannelDefaultAssignments(SalesChannelCollection $salesChannelDefaultAssignments): void
    {
        $this->salesChannelDefaultAssignments = $salesChannelDefaultAssignments;
    }

    public function getSalutationTranslations(): ?SalutationTranslationCollection
    {
        return $this->salutationTranslations;
    }

    public function setSalutationTranslations(SalutationTranslationCollection $salutationTranslations): void
    {
        $this->salutationTranslations = $salutationTranslations;
    }

    public function getPropertyGroupTranslations(): ?PropertyGroupTranslationCollection
    {
        return $this->propertyGroupTranslations;
    }

    public function setPropertyGroupTranslations(PropertyGroupTranslationCollection $propertyGroupTranslations): void
    {
        $this->propertyGroupTranslations = $propertyGroupTranslations;
    }

    public function getPropertyGroupOptionTranslations(): ?PropertyGroupOptionTranslationCollection
    {
        return $this->propertyGroupOptionTranslations;
    }

    public function setPropertyGroupOptionTranslations(PropertyGroupOptionTranslationCollection $propertyGroupOptionTranslationCollection): void
    {
        $this->propertyGroupOptionTranslations = $propertyGroupOptionTranslationCollection;
    }

    public function getSalesChannelTranslations(): ?SalesChannelTranslationCollection
    {
        return $this->salesChannelTranslations;
    }

    public function setSalesChannelTranslations(SalesChannelTranslationCollection $salesChannelTranslations): void
    {
        $this->salesChannelTranslations = $salesChannelTranslations;
    }

    public function getSalesChannelTypeTranslations(): ?SalesChannelTypeTranslationCollection
    {
        return $this->salesChannelTypeTranslations;
    }

    public function setSalesChannelTypeTranslations(SalesChannelTypeTranslationCollection $salesChannelTypeTranslations): void
    {
        $this->salesChannelTypeTranslations = $salesChannelTypeTranslations;
    }

    public function getSalesChannelDomains(): ?SalesChannelDomainCollection
    {
        return $this->salesChannelDomains;
    }

    public function setSalesChannelDomains(SalesChannelDomainCollection $salesChannelDomains): void
    {
        $this->salesChannelDomains = $salesChannelDomains;
    }

    public function getPluginTranslations(): ?PluginTranslationCollection
    {
        return $this->pluginTranslations;
    }

    public function setPluginTranslations(PluginTranslationCollection $pluginTranslations): void
    {
        $this->pluginTranslations = $pluginTranslations;
    }

    public function getProductStreamTranslations(): ?ProductStreamTranslationCollection
    {
        return $this->productStreamTranslations;
    }

    public function setProductStreamTranslations(ProductStreamTranslationCollection $productStreamTranslations): void
    {
        $this->productStreamTranslations = $productStreamTranslations;
    }

    /**
     * @return StateMachineTranslationCollection|null
     */
    public function getStateMachineTranslations(): ?Collection
    {
        return $this->stateMachineTranslations;
    }

    /**
     * @param StateMachineTranslationCollection $stateMachineTranslations
     */
    public function setStateMachineTranslations(Collection $stateMachineTranslations): void
    {
        $this->stateMachineTranslations = $stateMachineTranslations;
    }

    /**
     * @return StateMachineStateTranslationCollection|null
     */
    public function getStateMachineStateTranslations(): ?Collection
    {
        return $this->stateMachineStateTranslations;
    }

    /**
     * @param StateMachineStateTranslationCollection $stateMachineStateTranslations
     */
    public function setStateMachineStateTranslations(Collection $stateMachineStateTranslations): void
    {
        $this->stateMachineStateTranslations = $stateMachineStateTranslations;
    }

    /**
     * @return EntityCollection<CmsPageTranslationEntity>|null
     */
    public function getCmsPageTranslations(): ?Collection
    {
        return $this->cmsPageTranslations;
    }

    /**
     * @param EntityCollection<CmsPageTranslationEntity> $cmsPageTranslations
     */
    public function setCmsPageTranslations(Collection $cmsPageTranslations): void
    {
        $this->cmsPageTranslations = $cmsPageTranslations;
    }

    /**
     * @return EntityCollection<CmsSlotTranslationEntity>|null
     */
    public function getCmsSlotTranslations(): ?Collection
    {
        return $this->cmsSlotTranslations;
    }

    /**
     * @param EntityCollection<CmsSlotTranslationEntity> $cmsSlotTranslations
     */
    public function setCmsSlotTranslations(Collection $cmsSlotTranslations): void
    {
        $this->cmsSlotTranslations = $cmsSlotTranslations;
    }

    public function getMailTemplateTranslations(): ?MailTemplateCollection
    {
        return $this->mailTemplateTranslations;
    }

    public function setMailTemplateTranslations(MailTemplateCollection $mailTemplateTranslations): void
    {
        $this->mailTemplateTranslations = $mailTemplateTranslations;
    }

    public function getDeliveryTimeTranslations(): ?DeliveryTimeCollection
    {
        return $this->deliveryTimeTranslations;
    }

    public function setDeliveryTimeTranslations(DeliveryTimeCollection $deliveryTimeTranslations): void
    {
        $this->deliveryTimeTranslations = $deliveryTimeTranslations;
    }

    public function getNewsletterRecipients(): ?NewsletterRecipientCollection
    {
        return $this->newsletterRecipients;
    }

    public function setNewsletterRecipients(NewsletterRecipientCollection $newsletterRecipients): void
    {
        $this->newsletterRecipients = $newsletterRecipients;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getNumberRangeTypeTranslations(): ?NumberRangeTypeTranslationCollection
    {
        return $this->numberRangeTypeTranslations;
    }

    public function setNumberRangeTypeTranslations(NumberRangeTypeTranslationCollection $numberRangeTypeTranslations): void
    {
        $this->numberRangeTypeTranslations = $numberRangeTypeTranslations;
    }

    public function getMailTemplateTypeTranslations(): ?MailTemplateTypeDefinition
    {
        return $this->mailTemplateTypeTranslations;
    }

    public function setMailTemplateTypeTranslations(MailTemplateTypeDefinition $mailTemplateTypeTranslations): void
    {
        $this->mailTemplateTypeTranslations = $mailTemplateTypeTranslations;
    }

    public function getProductSearchKeywords(): ?ProductSearchKeywordCollection
    {
        return $this->productSearchKeywords;
    }

    public function setProductSearchKeywords(ProductSearchKeywordCollection $productSearchKeywords): void
    {
        $this->productSearchKeywords = $productSearchKeywords;
    }

    public function getProductKeywordDictionaries(): ?ProductKeywordDictionaryCollection
    {
        return $this->productKeywordDictionaries;
    }

    public function setProductKeywordDictionaries(ProductKeywordDictionaryCollection $productKeywordDictionaries): void
    {
        $this->productKeywordDictionaries = $productKeywordDictionaries;
    }

    public function getPromotionTranslations(): ?PromotionTranslationCollection
    {
        return $this->promotionTranslations;
    }

    public function setPromotionTranslations(PromotionTranslationCollection $promotionTranslations): void
    {
        $this->promotionTranslations = $promotionTranslations;
    }

    public function getNumberRangeTranslations(): ?NumberRangeTranslationCollection
    {
        return $this->numberRangeTranslations;
    }

    public function setNumberRangeTranslations(NumberRangeTranslationCollection $numberRangeTranslations): void
    {
        $this->numberRangeTranslations = $numberRangeTranslations;
    }

    public function getProductReviews(): ?ProductReviewCollection
    {
        return $this->productReviews;
    }

    public function setProductReviews(ProductReviewCollection $productReviews): void
    {
        $this->productReviews = $productReviews;
    }

    public function getSeoUrlTranslations(): ?SeoUrlCollection
    {
        return $this->seoUrlTranslations;
    }

    public function setSeoUrlTranslations(SeoUrlCollection $seoUrlTranslations): void
    {
        $this->seoUrlTranslations = $seoUrlTranslations;
    }

    public function getTaxRuleTypeTranslations(): ?TaxRuleTypeTranslationCollection
    {
        return $this->taxRuleTypeTranslations;
    }

    public function setTaxRuleTypeTranslations(TaxRuleTypeTranslationCollection $taxRuleTypeTranslations): void
    {
        $this->taxRuleTypeTranslations = $taxRuleTypeTranslations;
    }

    public function getProductCrossSellingTranslations(): ?ProductCrossSellingTranslationCollection
    {
        return $this->productCrossSellingTranslations;
    }

    public function setProductCrossSellingTranslations(ProductCrossSellingTranslationCollection $productCrossSellingTranslations): void
    {
        $this->productCrossSellingTranslations = $productCrossSellingTranslations;
    }

    public function getImportExportProfileTranslations(): ?ImportExportProfileTranslationCollection
    {
        return $this->importExportProfileTranslations;
    }

    public function setImportExportProfileTranslations(ImportExportProfileTranslationCollection $importExportProfileTranslations): void
    {
        $this->importExportProfileTranslations = $importExportProfileTranslations;
    }

    public function getProductFeatureSetTranslations(): ?ProductFeatureSetTranslationCollection
    {
        return $this->productFeatureSetTranslations;
    }

    public function setProductFeatureSetTranslations(ProductFeatureSetTranslationCollection $productFeatureSetTranslations): void
    {
        $this->productFeatureSetTranslations = $productFeatureSetTranslations;
    }

    public function getAppTranslations(): ?AppTranslationCollection
    {
        return $this->appTranslations;
    }

    public function setAppTranslations(AppTranslationCollection $appTranslations): void
    {
        $this->appTranslations = $appTranslations;
    }

    public function getActionButtonTranslations(): ?ActionButtonTranslationCollection
    {
        return $this->actionButtonTranslations;
    }

    public function setActionButtonTranslations(ActionButtonTranslationCollection $actionButtonTranslations): void
    {
        $this->actionButtonTranslations = $actionButtonTranslations;
    }

    public function getProductSortingTranslations(): ?ProductSortingTranslationCollection
    {
        return $this->productSortingTranslations;
    }

    public function setProductSortingTranslations(ProductSortingTranslationCollection $productSortingTranslations): void
    {
        $this->productSortingTranslations = $productSortingTranslations;
    }

    public function getProductSearchConfig(): ?ProductSearchConfigEntity
    {
        return $this->productSearchConfig;
    }

    public function setProductSearchConfig(ProductSearchConfigEntity $productSearchConfig): void
    {
        $this->productSearchConfig = $productSearchConfig;
    }

    public function getLandingPageTranslations(): ?LandingPageTranslationCollection
    {
        return $this->landingPageTranslations;
    }

    public function setLandingPageTranslations(LandingPageTranslationCollection $landingPageTranslations): void
    {
        $this->landingPageTranslations = $landingPageTranslations;
    }

    public function getAppCmsBlockTranslations(): ?AppCmsBlockTranslationCollection
    {
        return $this->appCmsBlockTranslations;
    }

    public function setAppCmsBlockTranslations(AppCmsBlockTranslationCollection $appCmsBlockTranslations): void
    {
        $this->appCmsBlockTranslations = $appCmsBlockTranslations;
    }

    public function getAppScriptConditionTranslations(): ?AppScriptConditionTranslationCollection
    {
        return $this->appScriptConditionTranslations;
    }

    public function setAppScriptConditionTranslations(AppScriptConditionTranslationCollection $appScriptConditionTranslations): void
    {
        $this->appScriptConditionTranslations = $appScriptConditionTranslations;
    }

    public function getAppFlowActionTranslations(): ?AppFlowActionTranslationCollection
    {
        return $this->appFlowActionTranslations;
    }

    public function setAppFlowActionTranslations(AppFlowActionTranslationCollection $appFlowActionTranslations): void
    {
        $this->appFlowActionTranslations = $appFlowActionTranslations;
    }

    public function getApiAlias(): string
    {
        return 'language';
    }

    public function getTaxProviderTranslations(): ?TaxProviderTranslationCollection
    {
        return $this->taxProviderTranslations;
    }

    public function setTaxProviderTranslations(TaxProviderTranslationCollection $taxProviderTranslations): void
    {
        $this->taxProviderTranslations = $taxProviderTranslations;
    }
}
