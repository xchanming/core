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
class Migration1599463278AddCustomerIdIntoSalesChannelContext extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1599463278;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `sales_channel_api_context` ADD `customer_id` BINARY(16) NULL UNIQUE DEFAULT NULL AFTER `payload`;');

        $connection->executeStatement('
            ALTER TABLE `sales_channel_api_context`
            ADD CONSTRAINT `fk.sales_channel_api_context.customer_id`
            FOREIGN KEY (`customer_id`)
            REFERENCES `customer` (`id`) ON DELETE CASCADE;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
