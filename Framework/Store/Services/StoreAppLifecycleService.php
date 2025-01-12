<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Services;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\AppStateService;
use Cicada\Core\Framework\App\Delta\AppConfirmationDeltaProvider;
use Cicada\Core\Framework\App\Lifecycle\AbstractAppLifecycle;
use Cicada\Core\Framework\App\Lifecycle\AppLoader;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Cicada\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Store\Exception\ExtensionNotFoundException;
use Cicada\Core\Framework\Store\StoreException;

/**
 * @internal - only for use by the app-system
 */
#[Package('checkout')]
class StoreAppLifecycleService extends AbstractStoreAppLifecycleService
{
    public function __construct(
        private readonly StoreClient $storeClient,
        private readonly AppLoader $appLoader,
        private readonly AbstractAppLifecycle $appLifecycle,
        private readonly EntityRepository $appRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly ?EntityRepository $themeRepository,
        private readonly AppStateService $appStateService,
        private readonly AppConfirmationDeltaProvider $appDeltaService
    ) {
    }

    public function installExtension(string $technicalName, Context $context): void
    {
        $manifests = $this->appLoader->load();

        if (!isset($manifests[$technicalName])) {
            throw StoreException::extensionInstallException(\sprintf('Cannot find app by name %s', $technicalName));
        }

        $this->appLifecycle->install($manifests[$technicalName], false, $context);
    }

    public function uninstallExtension(string $technicalName, Context $context, bool $keepUserData = false): void
    {
        try {
            $app = $this->getAppByName($technicalName, $context);
        } catch (ExtensionNotFoundException) {
            // extension is not installed, so we can skip the uninstall process
            return;
        }

        $this->validateExtensionCanBeRemoved($technicalName, $app->getId(), $context);
        $this->appLifecycle->delete($technicalName, ['id' => $app->getId(), 'roleId' => $app->getAclRoleId()], $context, $keepUserData);
    }

    public function removeExtensionAndCancelSubscription(int $licenseId, string $technicalName, string $id, bool $keepUserData, Context $context): void
    {
        $this->validateExtensionCanBeRemoved($technicalName, $id, $context);
        $app = $this->getAppById($id, $context);
        $this->storeClient->cancelSubscription($licenseId, $context);
        $this->appLifecycle->delete($technicalName, ['id' => $id, 'roleId' => $app->getAclRoleId()], $context);
        $this->deleteExtension($technicalName, $keepUserData, $context);
    }

    public function deleteExtension(string $technicalName, bool $keepUserData, Context $context): void
    {
        $this->uninstallExtension($technicalName, $context, $keepUserData);
        $this->appLoader->deleteApp($technicalName);
    }

    public function activateExtension(string $technicalName, Context $context): void
    {
        $id = $this->getAppByName($technicalName, $context)->getId();
        $this->appStateService->activateApp($id, $context);
    }

    public function deactivateExtension(string $technicalName, Context $context): void
    {
        $id = $this->getAppByName($technicalName, $context)->getId();
        $this->appStateService->deactivateApp($id, $context);
    }

    public function updateExtension(string $technicalName, bool $allowNewPermissions, Context $context): void
    {
        $manifests = $this->appLoader->load();

        if (!\array_key_exists($technicalName, $manifests)) {
            throw StoreException::extensionInstallException('Cannot find extension');
        }

        $app = $this->getAppByName($technicalName, $context);
        $requiresRenewedConsent = $this->appDeltaService->requiresRenewedConsent(
            $manifests[$technicalName],
            $app
        );

        if (!$allowNewPermissions && $requiresRenewedConsent) {
            $deltas = $this->appDeltaService->getReports(
                $manifests[$technicalName],
                $app
            );

            throw StoreException::extensionUpdateRequiresConsentAffirmationException($technicalName, $deltas);
        }

        $this->appLifecycle->update(
            $manifests[$technicalName],
            [
                'id' => $app->getId(),
                'version' => $app->getVersion(),
                'roleId' => $app->getAclRoleId(),
            ],
            $context
        );
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getDecorated(): AbstractStoreAppLifecycleService
    {
        throw new DecorationPatternException(self::class);
    }

    private function getAppByName(string $technicalName, Context $context): AppEntity
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', $technicalName));
        $app = $this->appRepository->search($criteria, $context)->first();

        if (!$app instanceof AppEntity) {
            throw StoreException::extensionNotFoundFromTechnicalName($technicalName);
        }

        return $app;
    }

    private function getThemeIdByTechnicalName(string $technicalName, Context $context): ?string
    {
        if (!$this->themeRepository instanceof EntityRepository) {
            return null;
        }

        return $this->themeRepository->searchIds(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', $technicalName)),
            $context
        )->firstId();
    }

    private function validateExtensionCanBeRemoved(string $technicalName, string $id, Context $context): void
    {
        $themeId = $this->getThemeIdByTechnicalName($technicalName, $context);

        if ($themeId === null) {
            // extension is not a theme
            return;
        }

        $criteria = new Criteria();
        $criteria->addAggregation(
            new FilterAggregation(
                'assigned_theme_filter',
                new TermsAggregation('assigned_theme', 'themes.id'),
                [new EqualsFilter('themes.id', $themeId)]
            )
        );
        $criteria->addAggregation(
            new FilterAggregation(
                'assigned_children_filter',
                new TermsAggregation('assigned_children', 'themes.parentThemeId'),
                [new EqualsFilter('themes.parentThemeId', $themeId)]
            )
        );

        $aggregates = $this->salesChannelRepository->aggregate($criteria, $context);

        /** @var TermsResult $directlyAssigned */
        $directlyAssigned = $aggregates->get('assigned_theme');

        /** @var TermsResult $assignedChildren */
        $assignedChildren = $aggregates->get('assigned_children');

        if (!empty($directlyAssigned->getKeys()) || !empty($assignedChildren->getKeys())) {
            throw StoreException::extensionThemeStillInUse($id);
        }
    }

    private function getAppById(string $id, Context $context): AppEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $id));

        /** @var AppEntity $app */
        $app = $this->appRepository->search($criteria, $context)->first();

        return $app;
    }
}
