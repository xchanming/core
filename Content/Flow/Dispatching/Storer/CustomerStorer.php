<?php declare(strict_types=1);

namespace Cicada\Core\Content\Flow\Dispatching\Storer;

use Cicada\Core\Checkout\Customer\CustomerDefinition;
use Cicada\Core\Checkout\Customer\CustomerEntity;
use Cicada\Core\Content\Flow\Dispatching\StorableFlow;
use Cicada\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use Cicada\Core\Content\Flow\Exception\CustomerDeletedException;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\CustomerAware;
use Cicada\Core\Framework\Event\FlowEventAware;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('services-settings')]
class CustomerStorer extends FlowStorer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
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
        if (!$event instanceof CustomerAware || isset($stored[CustomerAware::CUSTOMER_ID])) {
            return $stored;
        }

        try {
            $stored[CustomerAware::CUSTOMER_ID] = $event->getCustomerId();
        } catch (CustomerDeletedException) {
        }

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(CustomerAware::CUSTOMER_ID)) {
            return;
        }

        $storable->setData(CustomerAware::CUSTOMER_ID, $storable->getStore(CustomerAware::CUSTOMER_ID));

        $storable->lazy(
            CustomerAware::CUSTOMER,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?CustomerEntity
    {
        $id = $storableFlow->getStore(CustomerAware::CUSTOMER_ID);
        if ($id === null) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadCustomer($criteria, $storableFlow->getContext(), $id);
    }

    private function loadCustomer(Criteria $criteria, Context $context, string $id): ?CustomerEntity
    {
        $criteria->addAssociation('salutation');
        $criteria->addAssociation('defaultBillingAddress.country');
        $criteria->addAssociation('defaultBillingAddress.countryState');
        $criteria->addAssociation('defaultBillingAddress.salutation');
        $criteria->addAssociation('defaultShippingAddress.country');
        $criteria->addAssociation('defaultShippingAddress.countryState');
        $criteria->addAssociation('defaultShippingAddress.salutation');

        $event = new BeforeLoadStorableFlowDataEvent(
            CustomerDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        $customer = $this->customerRepository->search($criteria, $context)->get($id);

        if ($customer) {
            /** @var CustomerEntity $customer */
            return $customer;
        }

        return null;
    }
}
