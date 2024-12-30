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
class Migration1592837424AddProductTypeToCategory extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1592837424;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
ALTER TABLE `category`
ADD COLUMN `product_assignment_type` VARCHAR(32) NOT NULL DEFAULT 'product' AFTER `cms_page_id`,
ADD COLUMN `product_stream_id` BINARY(16) NULL AFTER `cms_page_id`,
ADD CONSTRAINT `fk.category.product_stream_id` FOREIGN KEY (`product_stream_id`)
REFERENCES `product_stream` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
