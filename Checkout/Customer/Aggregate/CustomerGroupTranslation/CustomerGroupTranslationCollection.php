<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomerGroupTranslationEntity>
 */
#[Package('checkout')]
class CustomerGroupTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getCustomerGroupIds(): array
    {
        return $this->fmap(fn (CustomerGroupTranslationEntity $customerGroupTranslation) => $customerGroupTranslation->getCustomerGroupId());
    }

    public function filterByCustomerGroupId(string $id): self
    {
        return $this->filter(fn (CustomerGroupTranslationEntity $customerGroupTranslation) => $customerGroupTranslation->getCustomerGroupId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (CustomerGroupTranslationEntity $customerGroupTranslation) => $customerGroupTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (CustomerGroupTranslationEntity $customerGroupTranslation) => $customerGroupTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'customer_group_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerGroupTranslationEntity::class;
    }
}
