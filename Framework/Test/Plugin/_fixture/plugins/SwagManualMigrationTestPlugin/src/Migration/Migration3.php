<?php declare(strict_types=1);

namespace SwagManualMigrationTestPlugin\Migration;

use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class Migration3 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 3;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
