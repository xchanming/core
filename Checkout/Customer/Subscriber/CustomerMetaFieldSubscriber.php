<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Subscriber;

use Cicada\Core\Checkout\Order\OrderDefinition;
use Cicada\Core\Checkout\Order\OrderStates;
use Cicada\Core\Defaults;
use Cicada\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerMetaFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'fillCustomerMetaDataFields',
            PreWriteValidationEvent::class => 'deleteOrder',
        ];
    }

    public function fillCustomerMetaDataFields(StateMachineTransitionEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        if ($event->getEntityName() !== 'order') {
            return;
        }

        if ($event->getToPlace()->getTechnicalName() !== OrderStates::STATE_COMPLETED && $event->getFromPlace()->getTechnicalName() !== OrderStates::STATE_COMPLETED) {
            return;
        }

        $this->updateCustomer([$event->getEntityId()]);
    }

    public function deleteOrder(PreWriteValidationEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        $orderIds = [];
        foreach ($event->getCommands() as $command) {
            if ($command->getEntityName() === OrderDefinition::ENTITY_NAME
                && $command instanceof DeleteCommand
            ) {
                $orderIds[] = Uuid::fromBytesToHex($command->getPrimaryKey()['id']);
            }
        }

        $this->updateCustomer($orderIds, true);
    }

    /**
     * @param array<string> $orderIds
     */
    private function updateCustomer(array $orderIds, bool $isDelete = false): void
    {
        if (empty($orderIds)) {
            return;
        }

        $customerIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(customer_id)) FROM `order_customer` WHERE order_id IN (:ids) AND order_version_id = :version AND customer_id IS NOT NULL',
            ['ids' => Uuid::fromHexToBytesList($orderIds), 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['ids' => ArrayParameterType::BINARY]
        );

        if (empty($customerIds)) {
            return;
        }

        $parameters = [
            'customerIds' => Uuid::fromHexToBytesList($customerIds),
            'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            'state' => OrderStates::STATE_COMPLETED,
        ];
        $types = [
            'customerIds' => ArrayParameterType::BINARY,
        ];

        $whereOrder = '';
        if ($isDelete) {
            $whereOrder = 'AND `order`.id NOT IN (:exceptOrderIds)';
            $parameters['exceptOrderIds'] = Uuid::fromHexToBytesList($orderIds);
            $types['exceptOrderIds'] = ArrayParameterType::BINARY;
        }

        $select = '
            SELECT `order_customer`.customer_id as id,
                   COUNT(`order`.id) as order_count,
                   ROUND(SUM(`order`.amount_total / `order`.currency_factor), 2) as order_total_amount,
                   MAX(`order`.order_date_time) as last_order_date

            FROM `order_customer`

            INNER JOIN `order`
                ON `order`.id = `order_customer`.order_id
                AND `order`.version_id = `order_customer`.order_version_id
                AND `order`.version_id = :version
                ' . $whereOrder . '

            INNER JOIN `state_machine_state`
                ON `state_machine_state`.id = `order`.state_id
                AND `state_machine_state`.technical_name = :state

            WHERE `order_customer`.customer_id IN (:customerIds)
            GROUP BY `order_customer`.customer_id
        ';

        $data = $this->connection->fetchAllAssociative($select, $parameters, $types);

        if (empty($data)) {
            foreach ($customerIds as $customerId) {
                $data[] = [
                    'id' => Uuid::fromHexToBytes($customerId),
                    'order_count' => 0,
                    'order_total_amount' => 0,
                    'last_order_date' => null,
                ];
            }
        }

        $update = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('UPDATE `customer` SET order_count = :order_count, order_total_amount = :order_total_amount, last_order_date = :last_order_date WHERE id = :id')
        );

        foreach ($data as $record) {
            $update->execute($record);
        }
    }
}
