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
class Migration1599806584AddEventActionRule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1599806584;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `event_action_rule` (
              `event_action_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              PRIMARY KEY (`event_action_id`,`rule_id`),
              KEY `rule_id` (`rule_id`),
              CONSTRAINT `fk.event_action_rule.event_action_id` FOREIGN KEY (`event_action_id`) REFERENCES `event_action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.event_action_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
