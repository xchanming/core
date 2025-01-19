<?php declare(strict_types=1);

namespace Cicada\Core\System\CustomField\Aggregate\CustomFieldSet;

use Cicada\Core\Content\Product\ProductCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationCollection;
use Cicada\Core\System\CustomField\CustomFieldCollection;

#[Package('services-settings')]
class CustomFieldSetEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var array|null
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
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $global;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $position;

    /**
     * @var CustomFieldCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customFields;

    /**
     * @var CustomFieldSetRelationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $relations;

    /**
     * @var ProductCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $products;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $appId;

    /**
     * @var AppEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $app;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getCustomFields(): ?CustomFieldCollection
    {
        return $this->customFields;
    }

    public function setCustomFields(CustomFieldCollection $customFields): void
    {
        $this->customFields = $customFields;
    }

    public function getRelations(): ?CustomFieldSetRelationCollection
    {
        return $this->relations;
    }

    public function setRelations(CustomFieldSetRelationCollection $relations): void
    {
        $this->relations = $relations;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function setGlobal(bool $global): void
    {
        $this->global = $global;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(?string $appId): void
    {
        $this->appId = $appId;
    }

    public function getApp(): ?AppEntity
    {
        return $this->app;
    }

    public function setApp(?AppEntity $app): void
    {
        $this->app = $app;
    }
}
