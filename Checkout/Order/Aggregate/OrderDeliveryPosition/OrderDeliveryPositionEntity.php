<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Order\Aggregate\OrderDeliveryPosition;

use Cicada\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Cicada\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Cicada\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Cicada\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderDeliveryPositionEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderDeliveryId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderLineItemId;

    /**
     * @var CalculatedPrice|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $price;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $unitPrice;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $totalPrice;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $quantity;

    /**
     * @var OrderLineItemEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderLineItem;

    /**
     * @var OrderDeliveryEntity|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderDelivery;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderDeliveryVersionId;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $orderLineItemVersionId;

    public function getOrderDeliveryId(): string
    {
        return $this->orderDeliveryId;
    }

    public function setOrderDeliveryId(string $orderDeliveryId): void
    {
        $this->orderDeliveryId = $orderDeliveryId;
    }

    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }

    public function setOrderLineItemId(string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    public function getPrice(): ?CalculatedPrice
    {
        return $this->price;
    }

    public function setPrice(?CalculatedPrice $price): void
    {
        $this->price = $price;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getOrderLineItem(): ?OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    public function setOrderLineItem(OrderLineItemEntity $orderLineItem): void
    {
        $this->orderLineItem = $orderLineItem;
    }

    public function getOrderDelivery(): ?OrderDeliveryEntity
    {
        return $this->orderDelivery;
    }

    public function setOrderDelivery(OrderDeliveryEntity $orderDelivery): void
    {
        $this->orderDelivery = $orderDelivery;
    }

    public function getOrderDeliveryVersionId(): string
    {
        return $this->orderDeliveryVersionId;
    }

    public function setOrderDeliveryVersionId(string $orderDeliveryVersionId): void
    {
        $this->orderDeliveryVersionId = $orderDeliveryVersionId;
    }

    public function getOrderLineItemVersionId(): string
    {
        return $this->orderLineItemVersionId;
    }

    public function setOrderLineItemVersionId(string $orderLineItemVersionId): void
    {
        $this->orderLineItemVersionId = $orderLineItemVersionId;
    }
}
