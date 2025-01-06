<?php declare(strict_types=1);

namespace SwagManualMigrationTestPlugin\Migration;

use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class Migration4 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 4;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
