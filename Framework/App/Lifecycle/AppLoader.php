<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Lifecycle;

use Cicada\Core\Framework\App\AppException;
use Cicada\Core\Framework\App\Exception\AppXmlParsingException;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SystemConfig\Exception\XmlParsingException;
use Composer\InstalledVersions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
#[Package('core')]
class AppLoader
{
    final public const COMPOSER_TYPE = 'cicada-app';

    public function __construct(
        private readonly string $appDir,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return array<string, Manifest>
     */
    public function load(): array
    {
        return [...$this->loadFromAppDir(), ...$this->loadFromComposer()];
    }

    public function deleteApp(string $technicalName): void
    {
        $apps = $this->load();

        if (!isset($apps[$technicalName])) {
            return;
        }

        $manifest = $apps[$technicalName];

        if ($manifest->isManagedByComposer()) {
            throw AppException::cannotDeleteManaged($technicalName);
        }

        (new Filesystem())->remove($manifest->getPath());
    }

    /**
     * @return array<string, Manifest>
     */
    private function loadFromAppDir(): array
    {
        if (!file_exists($this->appDir)) {
            return [];
        }

        $finder = new Finder();
        $finder->in($this->appDir)
            ->depth('<= 1') // only use manifest files in-app root folders
            ->followLinks()
            ->name('manifest.xml');

        $manifests = [];
        foreach ($finder->files() as $xml) {
            try {
                $manifest = Manifest::createFromXmlFile($xml->getPathname());

                $manifests[$manifest->getMetadata()->getName()] = $manifest;
            } catch (AppXmlParsingException|XmlParsingException $exception) {
                $this->logger->error('Manifest XML parsing error. Reason: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            }
        }

        // Overriding with local manifests
        $finder = new Finder();

        $finder->in($this->appDir)
            ->depth('<= 1') // only use manifest files in-app root folders
            ->followLinks()
            ->name('manifest.local.xml');

        foreach ($finder->files() as $xml) {
            try {
                $manifest = Manifest::createFromXmlFile($xml->getPathname());

                $manifests[$manifest->getMetadata()->getName()] = $manifest;
            } catch (AppXmlParsingException|XmlParsingException $exception) {
                $this->logger->error('Local manifest XML parsing error. Reason: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
            }
        }

        return $manifests;
    }

    /**
     * @return array<string, Manifest>
     */
    private function loadFromComposer(): array
    {
        $manifests = [];

        foreach (InstalledVersions::getInstalledPackagesByType(self::COMPOSER_TYPE) as $packageName) {
            $path = InstalledVersions::getInstallPath($packageName);

            if ($path !== null) {
                try {
                    $manifest = Manifest::createFromXmlFile($path . '/manifest.xml');
                    $manifest->setManagedByComposer(true);

                    $manifests[$manifest->getMetadata()->getName()] = $manifest;
                } catch (AppXmlParsingException|XmlParsingException $exception) {
                    $this->logger->error('Manifest XML parsing error. Reason: ' . $exception->getMessage(), ['trace' => $exception->getTrace()]);
                }
            }
        }

        return $manifests;
    }
}
