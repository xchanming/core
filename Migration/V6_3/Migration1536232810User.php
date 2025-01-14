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
class Migration1536232810User extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232810;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `user` (
              `id`              BINARY(16)                              NOT NULL,
              `username`        VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `password`        VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name`            VARCHAR(255)                            NOT NULL,
              `phone`           VARCHAR(255)                            NULL,
              `email`           VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `active`          TINYINT(1)                              NOT NULL DEFAULT 0,
              `avatar_id`       BINARY(16)                              NULL,
              `locale_id`       BINARY(16)                              NOT NULL,
              `store_token`     VARCHAR(255)                            NULL,
              `custom_fields`   JSON                                    NULL,
              `created_at`      DATETIME(3)                             NOT NULL,
              `updated_at`      DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              KEY `idx.phone` (`phone`),
              CONSTRAINT `uniq.user.email` UNIQUE (`email`),
              CONSTRAINT `uniq.user.username` UNIQUE (`username`),
              CONSTRAINT `json.user.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.user.locale_id` FOREIGN KEY (`locale_id`)
                REFERENCES `locale` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
