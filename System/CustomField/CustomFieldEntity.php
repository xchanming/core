<?php declare(strict_types=1);

namespace Cicada\Core\System\CustomField;

use Cicada\Core\Content\Product\Aggregate\ProductSearchConfigField\ProductSearchConfigFieldCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;

#[Package('services-settings')]
class CustomFieldEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $type;

    /**
     * @var array<string, mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $config;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customFieldSetId;

    /**
     * @var CustomFieldSetEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customFieldSet;

    /**
     * @var ProductSearchConfigFieldCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productSearchConfigFields;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $allowCustomerWrite = false;

    protected bool $allowCartExpose = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCustomFieldSetId(): ?string
    {
        return $this->customFieldSetId;
    }

    public function setCustomFieldSetId(?string $attributeSetId): void
    {
        $this->customFieldSetId = $attributeSetId;
    }

    public function getCustomFieldSet(): ?CustomFieldSetEntity
    {
        return $this->customFieldSet;
    }

    public function setCustomFieldSet(?CustomFieldSetEntity $attributeSet): void
    {
        $this->customFieldSet = $attributeSet;
    }

    public function getProductSearchConfigFields(): ?ProductSearchConfigFieldCollection
    {
        return $this->productSearchConfigFields;
    }

    public function setProductSearchConfigFields(ProductSearchConfigFieldCollection $productSearchConfigFields): void
    {
        $this->productSearchConfigFields = $productSearchConfigFields;
    }

    public function isAllowCustomerWrite(): bool
    {
        return $this->allowCustomerWrite;
    }

    public function setAllowCustomerWrite(bool $allowCustomerWrite): void
    {
        $this->allowCustomerWrite = $allowCustomerWrite;
    }

    public function isAllowCartExpose(): bool
    {
        return $this->allowCartExpose;
    }

    public function setAllowCartExpose(bool $allowCartExpose): void
    {
        $this->allowCartExpose = $allowCartExpose;
    }
}
