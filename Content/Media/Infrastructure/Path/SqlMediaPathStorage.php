<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\Infrastructure\Path;

use Cicada\Core\Content\Media\Core\Application\MediaPathStorage;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;

/**
 * @codeCoverageIgnore (see \Cicada\Tests\Integration\Core\Content\Media\Infrastructure\Path\MediaPathStorageTest)
 */
#[Package('core')]
class SqlMediaPathStorage implements MediaPathStorage
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @param array<string, string> $paths
     */
    public function media(array $paths): void
    {
        $update = $this->connection->prepare('UPDATE media SET path = :path WHERE id = :id');

        foreach ($paths as $id => $path) {
            $update->executeStatement(['path' => $path, 'id' => Uuid::fromHexToBytes($id)]);
        }
    }

    /**
     * @param array<string, string> $paths
     */
    public function thumbnails(array $paths): void
    {
        $update = $this->connection->prepare('UPDATE media_thumbnail SET path = :path WHERE id = :id');

        foreach ($paths as $id => $path) {
            $update->executeStatement([':path' => $path, ':id' => Uuid::fromHexToBytes($id)]);
        }
    }
}
