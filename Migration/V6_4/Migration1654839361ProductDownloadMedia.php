<?php declare(strict_types=1);

namespace Cicada\Core\Migration\V6_4;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationStep;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('core')]
class Migration1654839361ProductDownloadMedia extends MigrationStep
{
    private const FOLDER_NAME = 'Product downloads';

    public function getCreationTimestamp(): int
    {
        return 1654839361;
    }

    public function update(Connection $connection): void
    {
        $configurationId = Uuid::randomBytes();

        // media default folder
        $defaultFolderId = $connection->fetchOne('SELECT id FROM media_default_folder WHERE entity = :entity', ['entity' => 'product_download']);
        if (!$defaultFolderId) {
            $defaultFolderId = Uuid::randomBytes();

            $connection->insert('media_default_folder', [
                'id' => $defaultFolderId,
                'association_fields' => '["productDownloads", "orderLineItemDownloads"]',
                'entity' => 'product_download',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        // media folder
        $folderId = $connection->fetchOne('SELECT id FROM media_folder WHERE default_folder_id = :id', ['id' => $defaultFolderId]);
        if (!$folderId) {
            $connection->executeStatement('
                INSERT INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
                VALUES (:id, 80, 0, 1, :createdAt)
            ', [
                'id' => $configurationId,
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);

            $connection->executeStatement('
                INSERT into `media_folder` (`id`, `name`, `default_folder_id`, `media_folder_configuration_id`, `use_parent_configuration`, `child_count`, `created_at`)
                VALUES (:folderId, :folderName, :defaultFolderId, :configurationId, 0, 0, :createdAt)
            ', [
                'folderId' => $folderId,
                'folderName' => self::FOLDER_NAME,
                'defaultFolderId' => $defaultFolderId,
                'configurationId' => $configurationId,
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
