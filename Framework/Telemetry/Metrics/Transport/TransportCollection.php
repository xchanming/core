<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Telemetry\Metrics\Transport;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Telemetry\Metrics\Config\TransportConfigProvider;
use Cicada\Core\Framework\Telemetry\Metrics\Factory\MetricTransportFactoryInterface;
use Cicada\Core\Framework\Telemetry\Metrics\MetricTransportInterface;
use IteratorAggregate;

/**
 * @template MetricTransport of MetricTransportInterface
 *
 * @implements IteratorAggregate<int, MetricTransport>
 *
 * @internal
 */
#[Package('core')]
class TransportCollection implements \IteratorAggregate
{
    /**
     * @param array<MetricTransport> $transports
     */
    private function __construct(private readonly array $transports)
    {
    }

    /**
     * @param \Traversable<MetricTransportFactoryInterface> $transportFactories
     *
     * @return TransportCollection<MetricTransportInterface>
     */
    public static function create(\Traversable $transportFactories, TransportConfigProvider $configProvider): TransportCollection
    {
        $config = $configProvider->getTransportConfig();
        $transports = array_map(
            static fn (MetricTransportFactoryInterface $factory): MetricTransportInterface => $factory->create($config),
            iterator_to_array($transportFactories)
        );

        return new self($transports);
    }

    /**
     * @return \Traversable<int, MetricTransport>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->transports);
    }
}
