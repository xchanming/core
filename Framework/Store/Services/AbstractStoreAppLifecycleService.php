<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Services;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
abstract class AbstractStoreAppLifecycleService
{
    abstract public function installExtension(string $technicalName, Context $context): void;

    abstract public function uninstallExtension(string $technicalName, Context $context, bool $keepUserData = false): void;

    abstract public function removeExtensionAndCancelSubscription(int $licenseId, string $technicalName, string $id, bool $keepUserData, Context $context): void;

    abstract public function deleteExtension(string $technicalName, bool $keepUserData, Context $context): void;

    abstract public function activateExtension(string $technicalName, Context $context): void;

    abstract public function deactivateExtension(string $technicalName, Context $context): void;

    abstract public function updateExtension(string $technicalName, bool $allowNewPermissions, Context $context): void;

    abstract protected function getDecorated(): AbstractStoreAppLifecycleService;
}
