<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Delta;

use Cicada\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\App\Manifest\Manifest;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Store\Struct\PermissionCollection;
use Cicada\Core\Framework\Store\Struct\PermissionStruct;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class PermissionsDeltaProvider extends AbstractAppDeltaProvider
{
    final public const DELTA_NAME = 'permissions';

    public function getDeltaName(): string
    {
        return self::DELTA_NAME;
    }

    /**
     * @return array<string, PermissionCollection>
     */
    public function getReport(Manifest $manifest, AppEntity $app): array
    {
        $permissions = $manifest->getPermissions();

        if (!$permissions) {
            return [];
        }

        return $this->makeCategorizedPermissions($permissions->asParsedPrivileges());
    }

    public function hasDelta(Manifest $manifest, AppEntity $app): bool
    {
        $permissions = $manifest->getPermissions();

        if (!$permissions) {
            return false;
        }

        $aclRole = $app->getAclRole();

        if (!$aclRole) {
            return true;
        }

        $newPrivileges = $permissions->asParsedPrivileges();
        $currentPrivileges = $aclRole->getPrivileges();

        $privilegesDelta = array_diff($newPrivileges, $currentPrivileges);

        return \count($privilegesDelta) > 0;
    }

    /**
     * @param array<string> $appPrivileges
     *
     * @return list<array<'entity'|'operation', string>>
     */
    private function makePermissions(array $appPrivileges): array
    {
        $permissions = [];

        foreach ($appPrivileges as $privilege) {
            if ($this->isCrudPrivilege($privilege)) {
                $entityAndOperation = explode(':', $privilege);
                if (\array_key_exists($entityAndOperation[1], AclRoleDefinition::PRIVILEGE_DEPENDENCE)) {
                    $permissions[] = array_combine(['entity', 'operation'], $entityAndOperation);

                    continue;
                }
            }

            $permissions[] = ['entity' => 'additional_privileges', 'operation' => $privilege];
        }

        return $permissions;
    }

    private function isCrudPrivilege(string $privilege): bool
    {
        return substr_count($privilege, ':') === 1;
    }

    /**
     * @param array<string> $privilegesDelta
     *
     * @return array<string, PermissionCollection>
     */
    private function makeCategorizedPermissions(array $privilegesDelta): array
    {
        $permissions = $this->makePermissions($privilegesDelta);

        $permissionCollection = new PermissionCollection();

        foreach ($permissions as $permission) {
            $permissionCollection->add(PermissionStruct::fromArray([
                'entity' => $permission['entity'],
                'operation' => $permission['operation'],
            ]));
        }

        return $permissionCollection->getCategorizedPermissions();
    }
}
