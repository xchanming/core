<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Order\Aggregate\OrderDelivery;

use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Cicada\Core\Checkout\Order\Aggregate\OrderDeliveryPosition\OrderDeliveryPositionCollection;
use Cicada\Core\Checkout\Order\OrderEntity;
use Cicada\Core\Checkout\Shipping\ShippingMethodEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

#[Package('checkout')]
class OrderDeliveryEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderVersionId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingOrderAddressId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingOrderAddressVersionId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethodId;

    /**
     * @var array<string>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $trackingCodes;

    /**
     * @var \DateTimeInterface
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingDateEarliest;

    /**
     * @var \DateTimeInterface
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingDateLatest;

    /**
     * @var CalculatedPrice
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingCosts;

    /**
     * @var OrderAddressEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingOrderAddress;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $stateId;

    /**
     * @var StateMachineStateEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $stateMachineState;

    /**
     * @var ShippingMethodEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shippingMethod;

    /**
     * @var OrderEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $order;

    /**
     * @var OrderDeliveryPositionCollection|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $positions;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getShippingOrderAddressId(): string
    {
        return $this->shippingOrderAddressId;
    }

    public function setShippingOrderAddressId(string $shippingOrderAddressId): void
    {
        $this->shippingOrderAddressId = $shippingOrderAddressId;
    }

    public function getShippingMethodId(): string
    {
        return $this->shippingMethodId;
    }

    public function setShippingMethodId(string $shippingMethodId): void
    {
        $this->shippingMethodId = $shippingMethodId;
    }

    /**
     * @return array<string>
     */
    public function getTrackingCodes(): array
    {
        return $this->trackingCodes;
    }

    /**
     * @param array<string> $trackingCodes
     */
    public function setTrackingCodes(array $trackingCodes): void
    {
        $this->trackingCodes = $trackingCodes;
    }

    public function getShippingDateEarliest(): \DateTimeInterface
    {
        return $this->shippingDateEarliest;
    }

    public function setShippingDateEarliest(\DateTimeInterface $shippingDateEarliest): void
    {
        $this->shippingDateEarliest = $shippingDateEarliest;
    }

    public function getShippingDateLatest(): \DateTimeInterface
    {
        return $this->shippingDateLatest;
    }

    public function setShippingDateLatest(\DateTimeInterface $shippingDateLatest): void
    {
        $this->shippingDateLatest = $shippingDateLatest;
    }

    public function getShippingCosts(): CalculatedPrice
    {
        return $this->shippingCosts;
    }

    public function setShippingCosts(CalculatedPrice $shippingCosts): void
    {
        $this->shippingCosts = $shippingCosts;
    }

    public function getShippingOrderAddress(): ?OrderAddressEntity
    {
        return $this->shippingOrderAddress;
    }

    public function setShippingOrderAddress(OrderAddressEntity $shippingOrderAddress): void
    {
        $this->shippingOrderAddress = $shippingOrderAddress;
    }

    public function getShippingMethod(): ?ShippingMethodEntity
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethodEntity $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getPositions(): ?OrderDeliveryPositionCollection
    {
        return $this->positions;
    }

    public function setPositions(OrderDeliveryPositionCollection $positions): void
    {
        $this->positions = $positions;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    public function getStateMachineState(): ?StateMachineStateEntity
    {
        return $this->stateMachineState;
    }

    public function setStateMachineState(StateMachineStateEntity $stateMachineState): void
    {
        $this->stateMachineState = $stateMachineState;
    }

    public function getOrderVersionId(): string
    {
        return $this->orderVersionId;
    }

    public function setOrderVersionId(string $orderVersionId): void
    {
        $this->orderVersionId = $orderVersionId;
    }

    public function getShippingOrderAddressVersionId(): string
    {
        return $this->shippingOrderAddressVersionId;
    }

    public function setShippingOrderAddressVersionId(string $shippingOrderAddressVersionId): void
    {
        $this->shippingOrderAddressVersionId = $shippingOrderAddressVersionId;
    }
}
