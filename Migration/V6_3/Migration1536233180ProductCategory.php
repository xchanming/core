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
class Migration1536233180ProductCategory extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233180;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_category` (
              `product_id` BINARY(16) NOT NULL,
              `product_version_id` BINARY(16) NOT NULL,
              `category_id` BINARY(16) NOT NULL,
              `category_version_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`product_id`, `product_version_id`, `category_id`, `category_version_id`),
              CONSTRAINT `fk.product_category.category_id` FOREIGN KEY (`category_id`, `category_version_id`)
                REFERENCES `category` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_category.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
