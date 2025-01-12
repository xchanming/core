<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\EventListener\Acl;

use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Cicada\Core\Framework\Api\Acl\Event\CommandAclValidationEvent;
use Cicada\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('core')]
class CreditOrderLineItemListener implements EventSubscriberInterface
{
    final public const ACL_ORDER_CREATE_DISCOUNT_PRIVILEGE = 'order:create:discount';

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [CommandAclValidationEvent::class => 'validate'];
    }

    public function validate(CommandAclValidationEvent $event): void
    {
        $command = $event->getCommand();
        $resource = $command->getEntityName();
        $privilege = $command->getPrivilege();

        if ($privilege !== AclRoleDefinition::PRIVILEGE_CREATE || $resource !== OrderLineItemDefinition::ENTITY_NAME) {
            return;
        }

        $payload = $command->getPayload();
        $type = $payload['type'] ?? null;

        if ($type !== LineItem::CREDIT_LINE_ITEM_TYPE) {
            return;
        }

        if (!$event->getSource()->isAllowed(self::ACL_ORDER_CREATE_DISCOUNT_PRIVILEGE)) {
            $event->addMissingPrivilege(self::ACL_ORDER_CREATE_DISCOUNT_PRIVILEGE);
        }
    }
}
