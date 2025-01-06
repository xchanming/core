<?php declare(strict_types=1);

namespace Cicada\Core\Content\Category;

use Cicada\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationCollection;
use Cicada\Core\Content\Cms\CmsPageEntity;
use Cicada\Core\Content\Media\MediaEntity;
use Cicada\Core\Content\Product\ProductCollection;
use Cicada\Core\Content\ProductStream\ProductStreamEntity;
use Cicada\Core\Content\Seo\MainCategory\MainCategoryCollection;
use Cicada\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;
use Cicada\Core\System\Tag\TagCollection;

#[Package('inventory')]
class CategoryEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $afterCategoryId = null;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $parentId;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $autoIncrement;

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
    protected $name;

    /**
     * @var array<mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $breadcrumb;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $path;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $level;

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
    protected $childCount;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $visibleChildCount = 0;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $displayNestedProducts;

    /**
     * @var CategoryEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $parent;

    /**
     * @var CategoryCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $children;

    /**
     * @var CategoryTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var MediaEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $media;

    /**
     * @var ProductCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $products;

    /**
     * @var ProductCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $nestedProducts;

    /**
     * @var TagCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $tags;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsPageId;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsPageIdSwitched = false;

    /**
     * @var CmsPageEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cmsPage;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productStreamId;

    /**
     * @var ProductStreamEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $productStream;

    /**
     * @var array<mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $slotConfig;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $navigationSalesChannels;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $footerSalesChannels;

    /**
     * @var SalesChannelCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $serviceSalesChannels;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $linkType;

    /**
     * @var bool|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $linkNewTab;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $internalLink;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $externalLink;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $visible;

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
    protected $productAssignmentType;

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
    protected $metaTitle;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $metaDescription;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $keywords;

    /**
     * @var MainCategoryCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $mainCategories;

    /**
     * @var SeoUrlCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $seoUrls;

    /**
     * @var ?string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $customEntityTypeId;

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getChildCount(): int
    {
        return $this->childCount;
    }

    public function setChildCount(int $childCount): void
    {
        $this->childCount = $childCount;
    }

    public function getVisibleChildCount(): int
    {
        return $this->visibleChildCount;
    }

    public function setVisibleChildCount(int $visibleChildCount): void
    {
        $this->visibleChildCount = $visibleChildCount;
    }

    public function getParent(): ?CategoryEntity
    {
        return $this->parent;
    }

    public function setParent(CategoryEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getChildren(): ?CategoryCollection
    {
        return $this->children;
    }

    public function setChildren(CategoryCollection $children): void
    {
        $this->children = $children;
    }

    public function getTranslations(): ?CategoryTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CategoryTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getNestedProducts(): ?ProductCollection
    {
        return $this->nestedProducts;
    }

    public function setNestedProducts(ProductCollection $nestedProducts): void
    {
        $this->nestedProducts = $nestedProducts;
    }

    public function getDisplayNestedProducts(): bool
    {
        return $this->displayNestedProducts;
    }

    public function setDisplayNestedProducts(bool $displayNestedProducts): void
    {
        $this->displayNestedProducts = $displayNestedProducts;
    }

    public function getAfterCategoryId(): ?string
    {
        return $this->afterCategoryId;
    }

    public function setAfterCategoryId(string $afterCategoryId): void
    {
        $this->afterCategoryId = $afterCategoryId;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId;
    }

    public function setCmsPageId(string $cmsPageId): void
    {
        $this->cmsPageId = $cmsPageId;
    }

    public function getCmsPageIdSwitched(): bool
    {
        return $this->cmsPageIdSwitched;
    }

    public function setCmsPageIdSwitched(bool $switched): void
    {
        $this->cmsPageIdSwitched = $switched;
    }

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSlotConfig(): ?array
    {
        return $this->slotConfig;
    }

    /**
     * @param array<mixed> $slotConfig
     */
    public function setSlotConfig(array $slotConfig): void
    {
        $this->slotConfig = $slotConfig;
    }

    public function getNavigationSalesChannels(): ?SalesChannelCollection
    {
        return $this->navigationSalesChannels;
    }

    public function setNavigationSalesChannels(SalesChannelCollection $navigationSalesChannels): void
    {
        $this->navigationSalesChannels = $navigationSalesChannels;
    }

    public function getFooterSalesChannels(): ?SalesChannelCollection
    {
        return $this->footerSalesChannels;
    }

    public function setFooterSalesChannels(SalesChannelCollection $footerSalesChannels): void
    {
        $this->footerSalesChannels = $footerSalesChannels;
    }

    public function getServiceSalesChannels(): ?SalesChannelCollection
    {
        return $this->serviceSalesChannels;
    }

    public function setServiceSalesChannels(SalesChannelCollection $serviceSalesChannels): void
    {
        $this->serviceSalesChannels = $serviceSalesChannels;
    }

    public function getLinkType(): ?string
    {
        return $this->linkType;
    }

    public function setLinkType(?string $linkType): void
    {
        $this->linkType = $linkType;
    }

    public function getLinkNewTab(): ?bool
    {
        return $this->linkNewTab;
    }

    public function setLinkNewTab(?bool $linkNewTab): void
    {
        $this->linkNewTab = $linkNewTab;
    }

    public function getInternalLink(): ?string
    {
        return $this->internalLink;
    }

    public function setInternalLink(?string $internalLink): void
    {
        $this->internalLink = $internalLink;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array<mixed>
     */
    public function getBreadcrumb(): array
    {
        return array_values($this->getPlainBreadcrumb());
    }

    /**
     * @return array<mixed>
     */
    public function getPlainBreadcrumb(): array
    {
        $breadcrumb = $this->getTranslation('breadcrumb');
        if ($breadcrumb === null) {
            return [];
        }
        if ($this->path === null) {
            return $breadcrumb;
        }

        $parts = \array_slice(explode('|', $this->path), 1, -1);

        $filtered = [];
        foreach ($parts as $id) {
            if (isset($breadcrumb[$id])) {
                $filtered[$id] = $breadcrumb[$id];
            }
        }

        $filtered[$this->getId()] = $breadcrumb[$this->getId()];

        return $filtered;
    }

    /**
     * @param array<mixed>|null $breadcrumb
     */
    public function setBreadcrumb(?array $breadcrumb): void
    {
        $this->breadcrumb = $breadcrumb;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        // Make sure that the sorted breadcrumb gets serialized
        $data = parent::jsonSerialize();
        $data['translated']['breadcrumb'] = $data['breadcrumb'] = $this->getBreadcrumb();

        return $data;
    }

    public function getMainCategories(): ?MainCategoryCollection
    {
        return $this->mainCategories;
    }

    public function setMainCategories(MainCategoryCollection $mainCategories): void
    {
        $this->mainCategories = $mainCategories;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getSeoUrls(): ?SeoUrlCollection
    {
        return $this->seoUrls;
    }

    public function setSeoUrls(SeoUrlCollection $seoUrls): void
    {
        $this->seoUrls = $seoUrls;
    }

    public function getProductAssignmentType(): string
    {
        return $this->productAssignmentType;
    }

    public function setProductAssignmentType(string $productAssignmentType): void
    {
        $this->productAssignmentType = $productAssignmentType;
    }

    public function getCustomEntityTypeId(): ?string
    {
        return $this->customEntityTypeId;
    }

    public function setCustomEntityTypeId(?string $customEntityTypeId): void
    {
        $this->customEntityTypeId = $customEntityTypeId;
    }
}
