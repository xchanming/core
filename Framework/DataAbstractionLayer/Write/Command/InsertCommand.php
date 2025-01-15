<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Write\Command;

use Cicada\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('core')]
class InsertCommand extends WriteCommand
{
    /**
     * @deprecated tag:v6.7.0 - Property will be removed
     */
    protected EntityDefinition $definition;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $primaryKey
     */
    public function __construct(
        EntityDefinition $definition,
        array $payload,
        array $primaryKey,
        EntityExistence $existence,
        string $path,
    ) {
        $this->definition = $definition;

        parent::__construct($definition, $payload, $primaryKey, $existence, $path);
    }

    public function getPrivilege(): string
    {
        return AclRoleDefinition::PRIVILEGE_CREATE;
    }

    /**
     * @deprecated tag:v6.7.0 - Method will be removed
     */
    public function getDefinition(): EntityDefinition
    {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            Feature::deprecatedMethodMessage(self::class, 'getDefinition', 'v6.7.0.0')
        );

        return $this->definition;
    }
}
