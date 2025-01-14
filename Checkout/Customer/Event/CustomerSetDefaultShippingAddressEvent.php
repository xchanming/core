<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Event;

use Cicada\Core\Checkout\Customer\CustomerDefinition;
use Cicada\Core\Checkout\Customer\CustomerEntity;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\CicadaSalesChannelEvent;
use Cicada\Core\Framework\Event\EventData\EntityType;
use Cicada\Core\Framework\Event\EventData\EventDataCollection;
use Cicada\Core\Framework\Event\EventData\ScalarValueType;
use Cicada\Core\Framework\Event\FlowEventAware;
use Cicada\Core\Framework\Event\SalesChannelAware;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CustomerSetDefaultShippingAddressEvent extends Event implements SalesChannelAware, CicadaSalesChannelEvent, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.customer.default.shipping.address.event';

    public function __construct(
        private readonly SalesChannelContext $salesChannelContext,
        private readonly CustomerEntity $customer,
        private string $addressId
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelContext->getSalesChannelId();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('customer', new EntityType(CustomerDefinition::class))
            ->add('contextToken', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getAddressId(): string
    {
        return $this->addressId;
    }

    public function setAddressId(string $addressId): void
    {
        $this->addressId = $addressId;
    }
}
