<?php declare(strict_types=1);

namespace Cicada\Core\Migration\V6_4;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('core')]
class Migration1618989442AddProductConfigurationSettingsUniqKey extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1618989442;
    }

    public function update(Connection $connection): void
    {
        $index = $connection->fetchOne('
            SHOW INDEXES IN `product_configurator_setting`
            WHERE `Key_name` = \'uniq.product_configurator_setting.prod_id.vers_id.prop_group_id\'
        ');

        if (!$index) {
            // remove existing duplicates
            $connection->executeStatement('
                DELETE config1 FROM product_configurator_setting AS config1
                INNER JOIN product_configurator_setting AS config2
                WHERE config1.id < config2.id
                    AND config1.product_id = config2.product_id
                    AND config1.product_version_id = config2.product_version_id
                    AND config1.property_group_option_id = config2.property_group_option_id;
            ');

            // add unique index
            $connection->executeStatement('
                ALTER TABLE `product_configurator_setting`
                ADD CONSTRAINT `uniq.product_configurator_setting.prod_id.vers_id.prop_group_id`
                UNIQUE (product_id, version_id, property_group_option_id)
            ');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
