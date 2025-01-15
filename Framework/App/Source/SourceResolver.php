<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Source;

use Cicada\Core\Framework\App\AppCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\AppException;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Filesystem;
use Cicada\Core\Framework\Util\Hasher;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[Package('core')]
class SourceResolver implements ResetInterface
{
    /**
     * @var array<class-string<Source>, array<Filesystem>>
     */
    private array $sourceFilesystemCache = [];

    /**
     * @var array<string, Filesystem>
     */
    private array $appFilesystemCache = [];

    /**
     * @param iterable<Source> $sources
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly iterable $sources,
        private EntityRepository $appRepository,
        private NoDatabaseSourceResolver $noDbSourceResolver
    ) {
    }

    public function resolveSourceType(Manifest $manifest): string
    {
        foreach ($this->sources as $source) {
            if ($source->supports($manifest)) {
                return $source->name();
            }
        }

        throw AppException::noSourceSupports();
    }

    public function filesystemForManifest(Manifest $manifest): Filesystem
    {
        return $this->filesystem($manifest);
    }

    public function filesystemForApp(AppEntity $app): Filesystem
    {
        return $this->filesystem($app);
    }

    /**
     * For where we don't have an app instance
     */
    public function filesystemForAppName(string $appName): Filesystem
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $appName));

        try {
            $app = $this->appRepository->search($criteria, Context::createDefaultContext())->getEntities()->first();

            if ($app === null) {
                throw AppException::notFoundByField($appName, 'name');
            }

            return $this->filesystemForApp($app);
        } catch (DBALException $e) {
            // if we don't have a db, try to load it from the local filesystem
            return $this->noDbSourceResolver->filesystem($appName);
        }
    }

    public function reset(): void
    {
        foreach ($this->sourceFilesystemCache as $sourceClass => $filesystems) {
            $this->getSourceByClassName($sourceClass)->reset($filesystems);
        }

        $this->sourceFilesystemCache = [];
        $this->appFilesystemCache = [];
    }

    private function filesystem(AppEntity|Manifest $app): Filesystem
    {
        $cacheKey = $this->cacheKey($app);

        if (isset($this->appFilesystemCache[$cacheKey])) {
            return $this->appFilesystemCache[$cacheKey];
        }

        foreach ($this->sources as $source) {
            if ($source->supports($app)) {
                $filesystem = $source->filesystem($app);
                $this->cacheResolvedFileSystem($app, $filesystem, $source::class);

                return $filesystem;
            }
        }

        throw AppException::noSourceSupports();
    }

    /**
     * @param class-string<Source> $sourceClass
     */
    private function cacheResolvedFileSystem(AppEntity|Manifest $app, Filesystem $filesystem, string $sourceClass): void
    {
        $this->appFilesystemCache[$this->cacheKey($app)] = $filesystem;

        if (!isset($this->sourceFilesystemCache[$sourceClass])) {
            $this->sourceFilesystemCache[$sourceClass] = [];
        }

        $this->sourceFilesystemCache[$sourceClass][] = $filesystem;
    }

    private function getSourceByClassName(string $className): Source
    {
        foreach ($this->sources as $source) {
            if ($source instanceof $className) {
                return $source;
            }
        }

        throw AppException::sourceDoesNotExist($className);
    }

    private function cacheKey(AppEntity|Manifest $app): string
    {
        return Hasher::hash(match (true) {
            $app instanceof AppEntity => $app->getName() . '-' . $app->getVersion(),
            $app instanceof Manifest => $app->getMetadata()->getName() . '-' . $app->getMetadata()->getVersion(),
        });
    }
}
