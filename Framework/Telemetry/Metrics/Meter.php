<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Telemetry\Metrics;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Telemetry\Metrics\Config\MetricConfigProvider;
use Cicada\Core\Framework\Telemetry\Metrics\Exception\MetricNotSupportedException;
use Cicada\Core\Framework\Telemetry\Metrics\Exception\MissingMetricConfigurationException;
use Cicada\Core\Framework\Telemetry\Metrics\Metric\ConfiguredMetric;
use Cicada\Core\Framework\Telemetry\Metrics\Metric\Metric;
use Cicada\Core\Framework\Telemetry\Metrics\Transport\TransportCollection;
use Psr\Log\LoggerInterface;

/**
 * @experimental feature:TELEMETRY_METRICS stableVersion:v6.7.0
 */
#[Package('core')]
class Meter
{
    /**
     * @internal
     *
     * @param TransportCollection<MetricTransportInterface> $transports
     */
    public function __construct(
        private readonly TransportCollection $transports,
        private readonly MetricConfigProvider $metricConfigProvider,
        private readonly LoggerInterface $logger,
        private readonly string $environment
    ) {
    }

    public function emit(ConfiguredMetric $metric): void
    {
        if (!Feature::isActive('TELEMETRY_METRICS')) {
            return;
        }

        $metric = $this->process($metric);
        if ($metric === null) {
            return;
        }

        foreach ($this->transports as $transport) {
            $this->doEmitVia($metric, $transport);
        }
    }

    private function process(ConfiguredMetric $metric): ?Metric
    {
        try {
            $metricConfig = $this->metricConfigProvider->get($metric->name);
            if (!$metricConfig->enabled) {
                return null;
            }

            return Metric::fromConfigured(configuredMetric: $metric, metricConfig: $metricConfig);
        } catch (MissingMetricConfigurationException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            if ($this->environment === 'dev' || $this->environment === 'test') {
                throw $exception;
            }

            return null;
        }
    }

    private function doEmitVia(Metric $metric, MetricTransportInterface $transport): void
    {
        try {
            $transport->emit($metric);
        } catch (\Throwable $e) {
            $this->logger->warning(
                $e instanceof MetricNotSupportedException ? $e->getMessage() : \sprintf('Failed to emit metric via transport %s', $transport::class),
                ['exception' => $e]
            );

            if ($this->environment === 'dev' || $this->environment === 'test') {
                throw $e;
            }
        }
    }
}
