<?php declare(strict_types=1);

namespace Cicada\Core\Migration\V6_3;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('core')]
class Migration1605609309AddCreatedByIdAndUpdatedByIdToOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1605609309;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `order`
            ADD COLUMN `created_by_id` BINARY(16) NULL AFTER `rule_ids`,
            ADD COLUMN `updated_by_id` BINARY(16) NULL AFTER `created_by_id`;
        ');

        $connection->executeStatement('ALTER TABLE `order` ADD CONSTRAINT `fk.order.created_by_id` FOREIGN KEY (`created_by_id`)
              REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE');

        $connection->executeStatement('ALTER TABLE `order` ADD CONSTRAINT `fk.order.updated_by_id` FOREIGN KEY (`updated_by_id`)
              REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
