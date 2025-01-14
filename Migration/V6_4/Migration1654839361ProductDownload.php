<?php declare(strict_types=1);

namespace Cicada\Core\Migration\V6_4;

use Cicada\Core\Content\Product\State;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('core')]
class Migration1654839361ProductDownload extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1654839361;
    }

    public function update(Connection $connection): void
    {
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'product', 'states')) {
            $connection->executeStatement('
                ALTER TABLE `product`
                ADD COLUMN `states` JSON NULL,
                ADD CONSTRAINT `json.product.states` CHECK (JSON_VALID(`states`))
            ');
            $connection->executeStatement('
                UPDATE `product`
                SET `states` = :states
                WHERE `states` IS NULL
            ', ['states' => json_encode([State::IS_PHYSICAL])]);
        }

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_download` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `position` INT(11) NOT NULL DEFAULT 1,
              `product_id` BINARY(16) NOT NULL,
              `product_version_id` BINARY(16) NOT NULL,
              `media_id` BINARY(16) NOT NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              CONSTRAINT `json.product_download.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.product_download.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.product_download.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
