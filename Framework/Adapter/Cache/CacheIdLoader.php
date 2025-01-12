<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Cache;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;

#[Package('core')]
class CacheIdLoader
{
    private const CONFIG_KEY = 'cache-id';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractKeyValueStorage $keyValueStorage,
        private readonly ?CacheItemPoolInterface $restartSignalCachePool = null
    ) {
    }

    public function load(): string
    {
        $cacheId = EnvironmentHelper::getVariable('CICADA_CACHE_ID');
        if ($cacheId) {
            return (string) $cacheId;
        }

        try {
            $cacheId = $this->keyValueStorage->get(self::CONFIG_KEY);
        } catch (\Exception) {
            $cacheId = null;
        }

        if (\is_string($cacheId)) {
            return $cacheId;
        }

        $cacheId = Uuid::randomHex();

        try {
            $this->write($cacheId);

            return $cacheId;
        } catch (\Exception) {
            return 'live';
        }
    }

    public function write(string $cacheId): void
    {
        $this->keyValueStorage->set(self::CONFIG_KEY, $cacheId);

        if ($this->restartSignalCachePool) {
            $cacheItem = $this->restartSignalCachePool->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY);
            $cacheItem->set(microtime(true));
            $this->restartSignalCachePool->save($cacheItem);
        }
    }
}
