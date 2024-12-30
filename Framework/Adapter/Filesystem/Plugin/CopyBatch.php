<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Filesystem\Plugin;

use Cicada\Core\Framework\Log\Package;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;

#[Package('core')]
class CopyBatch
{
    public static function copy(FilesystemOperator $filesystem, CopyBatchInput ...$files): void
    {
        $adapter = self::getAdapter($filesystem);
        if ($adapter instanceof WriteBatchInterface) {
            $adapter->writeBatch(...$files);

            return;
        }

        foreach ($files as $batchInput) {
            $handle = $batchInput->getSourceFile();
            if (\is_string($handle)) {
                $handle = fopen($handle, 'r');
            }

            foreach ($batchInput->getTargetFiles() as $targetFile) {
                $filesystem->writeStream($targetFile, $handle);
            }

            if (\is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public static function getAdapter(FilesystemOperator $filesystem): ?FilesystemAdapter
    {
        if (!$filesystem instanceof Filesystem) {
            return null;
        }

        $func = \Closure::bind(fn () => $filesystem->adapter, $filesystem, $filesystem::class);

        return $func();
    }
}
