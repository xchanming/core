<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Plugin;

use Cicada\Core\Framework\App\ActiveAppsLoader;
use Cicada\Core\Framework\Bundle;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin;
use Cicada\Core\Kernel;
use Cicada\Storefront\Theme\StorefrontPluginRegistry;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @phpstan-import-type BundleConfig from BundleConfigGeneratorInterface
 */
#[Package('core')]
class BundleConfigGenerator implements BundleConfigGeneratorInterface
{
    private readonly string $projectDir;

    /**
     * @internal
     */
    public function __construct(
        private readonly Kernel $kernel,
        private readonly ActiveAppsLoader $activeAppsLoader
    ) {
        $projectDir = $this->kernel->getContainer()->getParameter('kernel.project_dir');
        if (!\is_string($projectDir)) {
            throw PluginException::projectDirNotInContainer();
        }
        $this->projectDir = $projectDir;
    }

    /**
     * @return array<string, BundleConfig>
     */
    public function getConfig(): array
    {
        return array_merge($this->generatePluginConfigs(), $this->generateAppConfigs());
    }

    /**
     * @return array<string, BundleConfig>
     */
    private function generatePluginConfigs(): array
    {
        $activePlugins = $this->getActivePlugins();

        $kernelBundles = $this->kernel->getBundles();

        $bundles = [];
        foreach ($kernelBundles as $bundle) {
            // only include cicada bundles
            if (!$bundle instanceof Bundle) {
                continue;
            }

            // dont include deactivated plugins
            if ($bundle instanceof Plugin && !\in_array($bundle->getName(), $activePlugins, true)) {
                continue;
            }

            $path = $bundle->getPath();
            if (mb_strpos($bundle->getPath(), $this->projectDir) === 0) {
                // make relative
                $path = \ltrim(\mb_substr($path, \mb_strlen($this->projectDir)), '/');
            }

            $bundles[$bundle->getName()] = [
                'basePath' => $path . '/',
                'views' => ['Resources/views'],
                'technicalName' => \str_replace('_', '-', $bundle->getContainerPrefix()),
                'isTheme' => $this->isTheme($path),
                'administration' => [
                    'path' => 'Resources/app/administration/src',
                    'entryFilePath' => $this->getEntryFile($bundle->getPath(), 'Resources/app/administration/src'),
                    'webpack' => $this->getWebpackConfig($bundle->getPath(), 'Resources/app/administration'),
                ],
                'storefront' => [
                    'path' => 'Resources/app/storefront/src',
                    'entryFilePath' => $this->getEntryFile($bundle->getPath(), 'Resources/app/storefront/src'),
                    'webpack' => $this->getWebpackConfig($bundle->getPath(), 'Resources/app/storefront'),
                    'styleFiles' => $this->getStyleFiles($bundle->getName(), $this->stripProjectDir($bundle->getPath())),
                ],
            ];
        }

        return $bundles;
    }

    /**
     * @return array<string, BundleConfig>
     */
    private function generateAppConfigs(): array
    {
        $configs = [];
        foreach ($this->activeAppsLoader->getActiveApps() as $app) {
            $absolutePath = $this->projectDir . '/' . $app['path'];

            $configs[$app['name']] = [
                'basePath' => $app['path'] . '/',
                'views' => ['Resources/views'],
                'technicalName' => str_replace('_', '-', $this->asSnakeCase($app['name'])),
                'isTheme' => $this->isTheme($absolutePath),
                'storefront' => [
                    'path' => 'Resources/app/storefront/src',
                    'entryFilePath' => $this->getEntryFile($absolutePath, 'Resources/app/storefront/src'),
                    'webpack' => $this->getWebpackConfig($absolutePath, 'Resources/app/storefront'),
                    'styleFiles' => $this->getStyleFiles($app['name'], $app['path']),
                ],
            ];
        }

        return $configs;
    }

    private function isTheme(string $path): bool
    {
        return file_exists($path . '/Resources/theme.json');
    }

    private function getEntryFile(string $rootPath, string $componentPath): ?string
    {
        $path = trim($componentPath, '/');
        $absolutePath = $rootPath . '/' . $path;

        return file_exists($absolutePath . '/main.ts') ? $path . '/main.ts'
            : (file_exists($absolutePath . '/main.js') ? $path . '/main.js'
                : null);
    }

    private function getWebpackConfig(string $rootPath, string $componentPath): ?string
    {
        $path = trim($componentPath, '/');
        $absolutePath = $rootPath . '/' . $path;

        $configFileName = match (true) {
            file_exists($absolutePath . '/build/webpack.config.ts') => 'webpack.config.ts',
            file_exists($absolutePath . '/build/webpack.config.cts') => 'webpack.config.cts',
            file_exists($absolutePath . '/build/webpack.config.js') => 'webpack.config.js',
            file_exists($absolutePath . '/build/webpack.config.cjs') => 'webpack.config.cjs',
            default => null,
        };

        if ($configFileName === null) {
            return null;
        }

        if (mb_strpos($path, $this->projectDir) === 0) {
            // make relative
            $path = ltrim(mb_substr($path, mb_strlen($this->projectDir)), '/');
        }

        return $path . '/build/' . $configFileName;
    }

    /**
     * @return array<string>
     */
    private function getStyleFiles(string $technicalName, string $basePath): array
    {
        if (!$this->kernel->getContainer()->has(StorefrontPluginRegistry::class)) {
            return [];
        }

        $registry = $this->kernel->getContainer()->get(StorefrontPluginRegistry::class);
        $config = $registry->getConfigurations()->getByTechnicalName($technicalName);

        if (!$config) {
            return [];
        }

        return array_map(
            fn (string $path) => Path::join($basePath, 'Resources', $path),
            $config->getStyleFiles()->getFilepaths()
        );
    }

    private function asSnakeCase(string $string): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($string);
    }

    /**
     * @return array<string>
     */
    private function getActivePlugins(): array
    {
        $activePlugins = $this->kernel->getPluginLoader()->getPluginInstances()->getActives();

        return array_map(static fn (Plugin $plugin) => $plugin->getName(), $activePlugins);
    }

    private function stripProjectDir(string $path): string
    {
        if (str_starts_with($path, $this->projectDir)) {
            return substr($path, \strlen($this->projectDir) + 1);
        }

        return $path;
    }
}
