<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Lifecycle\Persister;

use Cicada\Core\Framework\App\AppCollection;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\App\Template\AbstractTemplateLoader;
use Cicada\Core\Framework\App\Template\TemplateCollection;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class TemplatePersister
{
    /**
     * @param EntityRepository<TemplateCollection> $templateRepository
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly AbstractTemplateLoader $templateLoader,
        private readonly EntityRepository $templateRepository,
        private readonly EntityRepository $appRepository
    ) {
    }

    public function updateTemplates(Manifest $manifest, string $appId, Context $context): void
    {
        $app = $this->getAppWithExistingTemplates($appId, $context);
        $existingTemplates = $app->getTemplates();
        \assert($existingTemplates !== null);
        $templatePaths = $this->templateLoader->getTemplatePathsForApp($manifest);
        $upserts = [];
        foreach ($templatePaths as $templatePath) {
            $payload = [
                'template' => $this->templateLoader->getTemplateContent($templatePath, $manifest),
            ];

            $existing = $existingTemplates->filterByProperty('path', $templatePath)->first();
            if ($existing) {
                $payload['id'] = $existing->getId();
                $existingTemplates->remove($existing->getId());
            } else {
                $payload['appId'] = $appId;
                $payload['active'] = $app->isActive();
                $payload['path'] = $templatePath;
            }

            $upserts[] = $payload;
        }

        if (!empty($upserts)) {
            $this->templateRepository->upsert($upserts, $context);
        }

        $this->deleteOldTemplates($existingTemplates, $context);
    }

    private function deleteOldTemplates(TemplateCollection $toBeRemoved, Context $context): void
    {
        $ids = $toBeRemoved->getIds();

        if (!empty($ids)) {
            $ids = array_map(static fn (string $id): array => ['id' => $id], array_values($ids));

            $this->templateRepository->delete($ids, $context);
        }
    }

    private function getAppWithExistingTemplates(string $appId, Context $context): AppEntity
    {
        $criteria = new Criteria([$appId]);
        $criteria->addAssociation('templates');

        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();
        \assert($app !== null);

        return $app;
    }
}
