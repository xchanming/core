<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Dispatching\Storer;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryDefinition;
use Cicada\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryEntity;
use Cicada\Core\Content\Flow\Dispatching\Aware\CustomerRecoveryAware;
use Cicada\Core\Content\Flow\Dispatching\StorableFlow;
use Cicada\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\FlowEventAware;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('after-sales')]
class CustomerRecoveryStorer extends FlowStorer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $customerRecoveryRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof CustomerRecoveryAware || isset($stored[CustomerRecoveryAware::CUSTOMER_RECOVERY_ID])) {
            return $stored;
        }

        $stored[CustomerRecoveryAware::CUSTOMER_RECOVERY_ID] = $event->getCustomerRecoveryId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(CustomerRecoveryAware::CUSTOMER_RECOVERY_ID)) {
            return;
        }

        $storable->lazy(
            CustomerRecoveryAware::CUSTOMER_RECOVERY,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?CustomerRecoveryEntity
    {
        $id = $storableFlow->getStore(CustomerRecoveryAware::CUSTOMER_RECOVERY_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadCustomerRecovery($criteria, $storableFlow->getContext(), $id);
    }

    private function loadCustomerRecovery(Criteria $criteria, Context $context, string $id): ?CustomerRecoveryEntity
    {
        $criteria->addAssociation('customer.salutation');

        $event = new BeforeLoadStorableFlowDataEvent(
            CustomerRecoveryDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $customerRecovery = $this->customerRecoveryRepository->search($criteria, $context)->get($id);

        if ($customerRecovery) {
            /** @var CustomerRecoveryEntity $customerRecovery */
            return $customerRecovery;
        }

        return null;
    }
}
