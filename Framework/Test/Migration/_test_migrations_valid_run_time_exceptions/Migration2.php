<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\Migration\_test_migrations_valid_run_time_exceptions;

use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class Migration2 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 22;
    }

    public function update(Connection $connection): void
    {
        throw new \RuntimeException('update');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
