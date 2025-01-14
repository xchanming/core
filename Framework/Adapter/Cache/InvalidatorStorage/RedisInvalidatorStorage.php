<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Cache\InvalidatorStorage;

use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class RedisInvalidatorStorage extends AbstractInvalidatorStorage
{
    private const KEY = 'invalidation';

    /**
     * @internal
     *
     * @param \Redis|\RedisCluster $redis
     */
    public function __construct(
        /** @phpstan-ignore cicada.propertyNativeType (Cannot type natively, as Symfony might change the implementation in the future) */
        private $redis
    ) {
    }

    public function store(array $tags): void
    {
        $this->redis->sAdd(self::KEY, ...$tags);
    }

    public function loadAndDelete(): array
    {
        /** @var array{0: list<string>, 1: mixed} $values */
        $values = $this
            ->redis
            ->multi()
            ->sMembers(self::KEY)
            ->del(self::KEY)
            ->exec();

        return $values[0];
    }
}
