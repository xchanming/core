<?php declare(strict_types=1);

namespace Cicada\Core\System\SystemConfig\Service;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\Bundle;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SystemConfig\Exception\BundleConfigNotFoundException;
use Cicada\Core\System\SystemConfig\Exception\ConfigurationNotFoundException;
use Cicada\Core\System\SystemConfig\SystemConfigException;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Cicada\Core\System\SystemConfig\Util\ConfigReader;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

#[Package('services-settings')]
class ConfigurationService
{
    /**
     * @internal
     *
     * @param BundleInterface[] $bundles
     */
    public function __construct(
        private readonly iterable $bundles,
        private readonly ConfigReader $configReader,
        private readonly AppConfigReader $appConfigReader,
        private readonly EntityRepository $appRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    /**
     * @throws ConfigurationNotFoundException
     * @throws \InvalidArgumentException
     * @throws BundleConfigNotFoundException
     *
     * @return array<mixed>
     */
    public function getConfiguration(string $domain, Context $context): array
    {
        $validDomain = preg_match('/^([\w-]+)\.?([\w-]*)$/', $domain, $match);

        if (!$validDomain) {
            throw SystemConfigException::invalidDomain();
        }

        $scope = $match[1];
        $configName = $match[2] !== '' ? $match[2] : null;

        $config = $this->fetchConfiguration($scope === 'core' ? 'System' : $scope, $configName, $context);
        if (!$config) {
            throw SystemConfigException::configurationNotFound($scope);
        }

        $domain = rtrim($domain, '.') . '.';

        foreach ($config as $i => $card) {
            if (\array_key_exists('flag', $card) && !Feature::isActive($card['flag'])) {
                unset($config[$i]);

                continue;
            }

            foreach ($card['elements'] ?? [] as $j => $field) {
                $newField = ['name' => $domain . $field['name']];

                if (\array_key_exists('flag', $field) && !Feature::isActive($field['flag'])) {
                    unset($card['elements'][$j]);

                    continue;
                }

                if (\array_key_exists('type', $field)) {
                    $newField['type'] = $field['type'];
                }

                unset($field['type'], $field['name']);
                $newField['config'] = $field;
                $card['elements'][$j] = $newField;
            }

            if (isset($card['elements']) && \is_array($card['elements'])) {
                $card['elements'] = array_values($card['elements']);
            }

            $config[$i] = $card;
        }

        return array_values($config);
    }

    /**
     * @return array<mixed>
     */
    public function getResolvedConfiguration(string $domain, Context $context, ?string $salesChannelId = null): array
    {
        $config = [];
        if ($this->checkConfiguration($domain, $context)) {
            $config = array_merge(
                $config,
                $this->enrichValues(
                    $this->getConfiguration($domain, $context),
                    $salesChannelId
                )
            );
        }

        return $config;
    }

    public function checkConfiguration(string $domain, Context $context): bool
    {
        try {
            $this->getConfiguration($domain, $context);

            return true;
        } catch (\InvalidArgumentException|SystemConfigException|BundleConfigNotFoundException) {
            return false;
        }
    }

    /**
     * @return array<mixed>|null
     */
    private function fetchConfiguration(string $scope, ?string $configName, Context $context): ?array
    {
        $technicalName = \array_slice(explode('\\', $scope), -1)[0];

        foreach ($this->bundles as $bundle) {
            if ($bundle->getName() === $technicalName && $bundle instanceof Bundle) {
                return $this->configReader->getConfigFromBundle($bundle, $configName);
            }
        }

        $app = $this->getAppByName($technicalName, $context);

        return $app ? $this->appConfigReader->read($app) : null;
    }

    private function getAppByName(string $name, Context $context): ?AppEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        /** @var AppEntity|null $result */
        $result = $this->appRepository->search($criteria, $context)->first();

        return $result;
    }

    /**
     * @param array<mixed> $config
     *
     * @return array<mixed>
     */
    private function enrichValues(array $config, ?string $salesChannelId): array
    {
        foreach ($config as &$card) {
            if (!\is_array($card['elements'] ?? false)) {
                continue;
            }

            foreach ($card['elements'] as &$element) {
                $element['value'] = $this->systemConfigService->get(
                    $element['name'],
                    $salesChannelId
                ) ?? $element['config']['defaultValue'] ?? '';
            }
        }

        return $config;
    }
}
