<?php declare(strict_types=1);

namespace Cicada\Core\Content\Cms\Aggregate\CmsSlot;

use Cicada\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Cicada\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationEntity;
use Cicada\Core\Content\Cms\CmsException;
use Cicada\Core\Content\Cms\DataResolver\FieldConfig;
use Cicada\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('discovery')]
class CmsSlotEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $type;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $slot;

    /**
     * @var CmsBlockEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $block;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $blockId;

    /**
     * @var array<mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $config;

    /**
     * @var FieldConfigCollection|null
     *
     * @internal
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $fieldConfig;

    /**
     * @var EntityCollection<CmsSlotTranslationEntity>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var Struct|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $data;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $locked;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsBlockVersionId;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSlot(): string
    {
        return $this->slot;
    }

    public function setSlot(string $slot): void
    {
        $this->slot = $slot;
    }

    public function getBlock(): ?CmsBlockEntity
    {
        return $this->block;
    }

    public function setBlock(CmsBlockEntity $block): void
    {
        $this->block = $block;
    }

    public function getBlockId(): string
    {
        return $this->blockId;
    }

    public function setBlockId(string $blockId): void
    {
        $this->blockId = $blockId;
    }

    /**
     * @return array<mixed>|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @param array<mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->fieldConfig = null;
    }

    /**
     * @return EntityCollection<CmsSlotTranslationEntity>|null
     */
    public function getTranslations(): ?EntityCollection
    {
        return $this->translations;
    }

    /**
     * @param EntityCollection<CmsSlotTranslationEntity> $translations
     */
    public function setTranslations(EntityCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getData(): ?Struct
    {
        return $this->data;
    }

    public function setData(Struct $data): void
    {
        $this->data = $data;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getFieldConfig(): FieldConfigCollection
    {
        if ($this->fieldConfig) {
            return $this->fieldConfig;
        }

        $collection = new FieldConfigCollection();
        $config = $this->getTranslation('config') ?? [];

        foreach ($config as $key => $data) {
            $source = $data['source'] ?? null;
            $value = $data['value'] ?? null;

            if (!\is_string($source)) {
                throw CmsException::invalidFieldConfigSource($key);
            }

            $collection->add(
                new FieldConfig($key, $source, $value)
            );
        }

        return $this->fieldConfig = $collection;
    }

    public function setFieldConfig(FieldConfigCollection $fieldConfig): void
    {
        $this->fieldConfig = $fieldConfig;
    }

    public function getCmsBlockVersionId(): ?string
    {
        return $this->cmsBlockVersionId;
    }

    public function setCmsBlockVersionId(?string $cmsBlockVersionId): void
    {
        $this->cmsBlockVersionId = $cmsBlockVersionId;
    }
}
