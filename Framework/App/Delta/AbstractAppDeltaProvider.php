<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Delta;

use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
abstract class AbstractAppDeltaProvider
{
    abstract public function getDeltaName(): string;

    /**
     * @return array<array-key, mixed>
     */
    abstract public function getReport(Manifest $manifest, AppEntity $app): array;

    abstract public function hasDelta(Manifest $manifest, AppEntity $app): bool;
}
