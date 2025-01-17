<?php declare(strict_types=1);

namespace Cicada\Core\Migration\V6_5;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1678969082DropVariantListingFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1678969082;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        $this->dropColumnIfExists($connection, 'product', 'display_parent');

        $this->dropColumnIfExists($connection, 'product', 'configurator_group_config');

        if ($this->columnExists($connection, 'product', 'main_variant_id')) {
            // Maybe FK still exists, so we need to drop it first
            $this->dropForeignKeyIfExists($connection, 'product', 'fk.product.main_variant_id');

            $this->dropColumnIfExists($connection, 'product', 'main_variant_id');
        }
    }
}
