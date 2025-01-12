<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Lifecycle\Persister;

use Cicada\Core\Framework\App\AppCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Lifecycle\ScriptFileReader;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Script\ScriptCollection;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class ScriptPersister
{
    /**
     * @param EntityRepository<ScriptCollection> $scriptRepository
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly ScriptFileReader $scriptReader,
        private readonly EntityRepository $scriptRepository,
        private readonly EntityRepository $appRepository
    ) {
    }

    public function updateScripts(string $appId, Context $context): void
    {
        $app = $this->getAppWithExistingScripts($appId, $context);
        $existingScripts = $app->getScripts();
        \assert($existingScripts !== null);

        $scriptPaths = $this->scriptReader->getScriptPathsForApp($app);

        $upserts = [];
        foreach ($scriptPaths as $scriptPath) {
            $payload = [
                'script' => $this->scriptReader->getScriptContent($app, $scriptPath),
            ];

            $existing = $existingScripts->filterByProperty('name', $scriptPath)->first();
            if ($existing) {
                $existingScripts->remove($existing->getId());

                if ($existing->getScript() === $payload['script']) {
                    // Don't update DB when content is identical
                    continue;
                }
                $payload['id'] = $existing->getId();
            } else {
                $payload['appId'] = $appId;
                $payload['active'] = $app->isActive();
                $payload['name'] = $scriptPath;
                $payload['hook'] = explode('/', $scriptPath)[0];
            }

            $upserts[] = $payload;
        }

        if (!empty($upserts)) {
            $this->scriptRepository->upsert($upserts, $context);
        }

        $this->deleteOldScripts($existingScripts, $context);
    }

    public function activateAppScripts(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->setTitle('app-scripts::activate');
        $criteria->addFilter(new EqualsFilter('appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', false));

        /** @var array<string> $scriptIds */
        $scriptIds = $this->scriptRepository->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => true], $scriptIds);

        $this->scriptRepository->update($updateSet, $context);
    }

    public function deactivateAppScripts(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->setTitle('app-scripts::deactivate');
        $criteria->addFilter(new EqualsFilter('appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var array<string> $scriptIds */
        $scriptIds = $this->scriptRepository->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => false], $scriptIds);

        $this->scriptRepository->update($updateSet, $context);
    }

    /**
     * Refresh is only called on dev to update scripts independently of the app lifecycle for better dev experience
     */
    public function refresh(): void
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->setTitle('app-scripts::refresh');
        $criteria->addFilter(new EqualsFilter('active', true));

        // We don't automatically update service scripts, as that would do a request to the service on every request to cicada
        $criteria->addFilter(new EqualsFilter('selfManaged', false));

        /** @var array<string> $appIds */
        $appIds = $this->appRepository->searchIds($criteria, $context)->getIds();

        foreach ($appIds as $appId) {
            $this->updateScripts($appId, $context);
        }
    }

    private function deleteOldScripts(ScriptCollection $toBeRemoved, Context $context): void
    {
        $ids = $toBeRemoved->getIds();

        if (!empty($ids)) {
            $ids = array_map(static fn (string $id): array => ['id' => $id], array_values($ids));

            $this->scriptRepository->delete($ids, $context);
        }
    }

    private function getAppWithExistingScripts(string $appId, Context $context): AppEntity
    {
        $criteria = new Criteria([$appId]);
        $criteria->addAssociation('scripts');

        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();
        \assert($app !== null);

        return $app;
    }
}
