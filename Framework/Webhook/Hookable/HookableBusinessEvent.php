<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Webhook\Hookable;

use Cicada\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Cicada\Core\Framework\App\AppEntity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\Event\EventData\ArrayType;
use Cicada\Core\Framework\Event\EventData\EntityCollectionType;
use Cicada\Core\Framework\Event\EventData\EntityType;
use Cicada\Core\Framework\Event\EventData\ObjectType;
use Cicada\Core\Framework\Event\FlowEventAware;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Webhook\AclPrivilegeCollection;
use Cicada\Core\Framework\Webhook\BusinessEventEncoder;
use Cicada\Core\Framework\Webhook\Hookable;

/**
 * @internal
 */
#[Package('core')]
class HookableBusinessEvent implements Hookable
{
    private function __construct(
        private readonly FlowEventAware $flowEventAware,
        private readonly BusinessEventEncoder $businessEventEncoder
    ) {
    }

    public static function fromBusinessEvent(
        FlowEventAware $flowEventAware,
        BusinessEventEncoder $businessEventEncoder
    ): self {
        return new self($flowEventAware, $businessEventEncoder);
    }

    public function getName(): string
    {
        return $this->flowEventAware->getName();
    }

    public function getWebhookPayload(?AppEntity $app = null): array
    {
        return $this->businessEventEncoder->encode($this->flowEventAware);
    }

    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        foreach ($this->flowEventAware->getAvailableData()->toArray() as $dataType) {
            if (!$this->checkPermissionsForDataType($dataType, $permissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $dataType
     */
    private function checkPermissionsForDataType(array $dataType, AclPrivilegeCollection $permissions): bool
    {
        if ($dataType['type'] === ObjectType::TYPE && \is_array($dataType['data']) && !empty($dataType['data'])) {
            foreach ($dataType['data'] as $nested) {
                if (!$this->checkPermissionsForDataType($nested, $permissions)) {
                    return false;
                }
            }
        }

        if ($dataType['type'] === ArrayType::TYPE && $dataType['of']) {
            if (!$this->checkPermissionsForDataType($dataType['of'], $permissions)) {
                return false;
            }
        }

        if ($dataType['type'] === EntityType::TYPE || $dataType['type'] === EntityCollectionType::TYPE) {
            /** @var EntityDefinition $definition */
            $definition = new $dataType['entityClass']();
            if (!$permissions->isAllowed($definition->getEntityName(), AclRoleDefinition::PRIVILEGE_READ)) {
                return false;
            }
        }

        return true;
    }
}
