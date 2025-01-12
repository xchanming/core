<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SalesChannel\Sorting;

use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSortingEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $key;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $priority;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $active;

    /**
     * @var array<array{field: string, priority: int, order: ?string, naturalSorting: bool|int|null}>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $fields;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $label;

    /**
     * @var ProductSortingTranslationCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $translations;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $locked;

    /**
     * @return array<FieldSorting>
     */
    public function createDalSorting(): array
    {
        $sorting = [];

        $fields = $this->fields;

        if (!\is_array($fields)) {
            $fields = [];
        }

        usort($fields, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        foreach ($fields as $field) {
            $direction = mb_strtoupper((string) $field['order']) === FieldSorting::ASCENDING
                ? FieldSorting::ASCENDING
                : FieldSorting::DESCENDING;

            $sorting[] = new FieldSorting(
                $field['field'],
                $direction,
                (bool) ($field['naturalSorting'] ?? false)
            );
        }

        $flat = array_column($fields, 'field');

        if (\in_array('id', $flat, true)) {
            return $sorting;
        }
        if (\in_array('product.id', $flat, true)) {
            return $sorting;
        }

        $sorting[] = new FieldSorting('id', FieldSorting::ASCENDING);

        return $sorting;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return array<array{field: string, priority: int, order: ?string, naturalSorting: bool|int|null}>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array<array{field: string, priority: int, order: ?string, naturalSorting: bool|int|null}> $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getTranslations(): ?ProductSortingTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ProductSortingTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getApiAlias(): string
    {
        return 'product_sorting';
    }
}
