<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer;

use Cicada\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelDefinitionInstanceRegistry;

/**
 * @internal
 *
 * @deprecated tag:v6.7.0 - unused with 6.7, will be now handled by \Shopware\Core\System\DependencyInjection\CompilerPass\SalesChannelEntityCompilerPass - reason:remove-entity
 * Contains all registered entity extensions in the system and attaches them to the corresponding entity definitions
 */
#[Package('core')]
class ExtensionRegistry
{
    private bool $is67;

    /**
     * @internal
     *
     * @param iterable<EntityExtension> $extensions
     * @param iterable<BulkEntityExtension> $bulks
     */
    public function __construct(
        private readonly iterable $extensions,
        private readonly iterable $bulks
    ) {
        $this->is67 = Feature::isActive('v6.7.0.0');
    }

    public function configureExtensions(DefinitionInstanceRegistry $registry, SalesChannelDefinitionInstanceRegistry $salesChannelRegistry): void
    {
        foreach ($this->extensions as $extension) {
            $this->addExtension($registry, $salesChannelRegistry, $extension);
        }

        foreach ($this->bulks as $bulk) {
            foreach ($bulk->collect() as $entity => $fields) {
                $extension = $this->buildBulkExtension($registry, $entity, $fields);

                if ($extension !== null) {
                    $this->addExtension(
                        $registry,
                        $salesChannelRegistry,
                        $extension
                    );
                }
            }
        }
    }

    private function addExtension(
        DefinitionInstanceRegistry $definitionRegistry,
        SalesChannelDefinitionInstanceRegistry $salesChannelRegistry,
        EntityExtension $extension
    ): void {
        try {
            $definition = $this->getInstance($definitionRegistry, $extension);
        } catch (DefinitionNotFoundException) {
            return;
        }

        $definition->addExtension($extension);

        try {
            $salesChannelDefinition = $this->getInstance($salesChannelRegistry, $extension);
        } catch (DefinitionNotFoundException) {
            return;
        }

        // same definition? do not added extension
        if ($salesChannelDefinition !== $definition) {
            $salesChannelDefinition->addExtension($extension);
        }
    }

    /**
     * @param list<Field> $fields
     */
    private function buildBulkExtension(DefinitionInstanceRegistry $registry, string $entity, array $fields): ?EntityExtension
    {
        try {
            // @deprecated tag:v6.7.0 - can be removed, as it is not used anymore. The entity name is the only requirement for EntityExtensions within v6.7.0
            $definition = $registry->getByEntityName($entity);
        } catch (DefinitionNotFoundException) {
            return null;
        }

        return new class($fields, $definition->getClass(), $entity) extends EntityExtension {
            /**
             * @param list<Field> $fields
             */
            public function __construct(private readonly array $fields, private readonly string $class, private readonly string $entity)
            {
            }

            public function extendFields(FieldCollection $collection): void
            {
                foreach ($this->fields as $field) {
                    $collection->add($field);
                }
            }

            public function getDefinitionClass(): string
            {
                return $this->class;
            }

            public function getEntityName(): string
            {
                return $this->entity;
            }
        };
    }

    private function getInstance(DefinitionInstanceRegistry $registry, EntityExtension $extension): EntityDefinition
    {
        if ($this->is67) {
            $entity = $extension->getEntityName();

            return $registry->getByEntityName($entity);
        }

        if (!empty($extension->getEntityName())) {
            $entity = $extension->getEntityName();

            return $registry->getByEntityName($entity);
        }

        $class = $extension->getDefinitionClass();

        return $registry->get($class);
    }
}
