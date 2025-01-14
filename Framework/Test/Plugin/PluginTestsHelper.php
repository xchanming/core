<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\Plugin;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\Plugin;
use Cicada\Core\Framework\Plugin\KernelPluginCollection;
use Cicada\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use Cicada\Core\Framework\Plugin\PluginService;
use Cicada\Core\Framework\Plugin\Util\PluginFinder;
use Cicada\Core\Framework\Plugin\Util\VersionSanitizer;
use SwagTestPlugin\SwagTestPlugin;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PluginTestsHelper
{
    protected function createPluginService(
        string $pluginDir,
        string $projectDir,
        EntityRepository $pluginRepo,
        EntityRepository $languageRepo,
        PluginFinder $pluginFinder
    ): PluginService {
        return new PluginService(
            $pluginDir,
            $projectDir,
            $pluginRepo,
            $languageRepo,
            $pluginFinder,
            new VersionSanitizer()
        );
    }

    protected function createPlugin(
        EntityRepository $pluginRepo,
        Context $context,
        string $version = SwagTestPlugin::PLUGIN_VERSION,
        ?string $installedAt = null
    ): void {
        $pluginRepo->create(
            [
                [
                    'baseClass' => SwagTestPlugin::class,
                    'name' => 'SwagTestPlugin',
                    'version' => $version,
                    'label' => SwagTestPlugin::PLUGIN_LABEL,
                    'installedAt' => $installedAt,
                    'active' => false,
                    'autoload' => [],
                ],
            ],
            $context
        );
    }

    abstract protected static function getContainer(): ContainerInterface;

    private function addTestPluginToKernel(string $testPluginBaseDir, string $pluginName, bool $active = false): void
    {
        require_once $testPluginBaseDir . '/src/' . $pluginName . '.php';

        /** @var KernelPluginCollection $pluginCollection */
        $pluginCollection = static::getContainer()->get(KernelPluginCollection::class);
        /** @var class-string<Plugin> $class */
        $class = '\\' . $pluginName . '\\' . $pluginName;
        $plugin = new $class($active, $testPluginBaseDir);
        $pluginCollection->add($plugin);

        static::getContainer()->get(KernelPluginLoader::class)->getPluginInstances()->add($plugin);
    }
}
