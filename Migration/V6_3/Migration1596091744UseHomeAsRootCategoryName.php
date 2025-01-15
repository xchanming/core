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
class Migration1596091744UseHomeAsRootCategoryName extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1596091744;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('UPDATE category_translation SET `name` = "Home" WHERE `name` IN ("Catalogue #1", "Katalog #1") AND updated_at IS NULL');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
