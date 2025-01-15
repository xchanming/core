<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Delta;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class AppConfirmationDeltaProvider
{
    /**
     * @param AbstractAppDeltaProvider[] $deltaProviders
     */
    public function __construct(private readonly iterable $deltaProviders)
    {
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public function getReports(Manifest $manifest, AppEntity $app): array
    {
        $deltas = [];

        foreach ($this->deltaProviders as $provider) {
            $deltas[$provider->getDeltaName()] = $provider->getReport($manifest, $app);
        }

        return $deltas;
    }

    public function requiresRenewedConsent(Manifest $manifest, AppEntity $app): bool
    {
        foreach ($this->deltaProviders as $provider) {
            if ($provider->hasDelta($manifest, $app)) {
                return true;
            }
        }

        return false;
    }
}
