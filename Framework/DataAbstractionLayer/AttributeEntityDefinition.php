<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer;

use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class AttributeEntityDefinition extends EntityDefinition
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(private readonly array $meta = [])
    {
        parent::__construct();
    }

    public function since(): ?string
    {
        return $this->meta['since'] ?? null;
    }

    /**
     * @return class-string<Entity>
     */
    public function getEntityClass(): string
    {
        return $this->meta['entity_class'];
    }

    public function getEntityName(): string
    {
        return $this->meta['entity_name'];
    }

    /**
     * @return class-string<EntityCollection<Entity>>
     */
    public function getCollectionClass(): string
    {
        return $this->meta['collection_class'];
    }

    protected function getParentDefinitionClass(): ?string
    {
        return $this->meta['parent'] ?? null;
    }

    protected function defineFields(): FieldCollection
    {
        $fields = [];

        foreach ($this->meta['fields'] as $field) {
            if (!isset($field['class'])) {
                continue;
            }

            if ($field['translated']) {
                $fields[] = new TranslatedField($field['name']);
                continue;
            }

            $instance = new $field['class'](...$field['args']);
            if (!$instance instanceof Field) {
                continue;
            }

            foreach ($field['flags'] ?? [] as $flag) {
                $flagInstance = new $flag['class'](...$flag['args'] ?? []);

                if ($flagInstance instanceof Flag) {
                    $instance->addFlags($flagInstance);
                }
            }

            $fields[] = $instance;
        }

        return new FieldCollection($fields);
    }
}
