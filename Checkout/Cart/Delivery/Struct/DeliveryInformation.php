<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Delivery\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('checkout')]
class DeliveryInformation extends Struct
{
    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $stock;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $weight;

    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $freeDelivery;

    /**
     * @var int|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $restockTime;

    /**
     * @var DeliveryTime|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $deliveryTime;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $height;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $width;

    /**
     * @var float|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $length;

    public function __construct(
        int $stock,
        ?float $weight,
        bool $freeDelivery,
        ?int $restockTime = null,
        ?DeliveryTime $deliveryTime = null,
        ?float $height = null,
        ?float $width = null,
        ?float $length = null
    ) {
        $this->stock = $stock;
        $this->weight = $weight;
        $this->freeDelivery = $freeDelivery;
        $this->restockTime = $restockTime;
        $this->deliveryTime = $deliveryTime;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getFreeDelivery(): bool
    {
        return $this->freeDelivery;
    }

    public function setFreeDelivery(bool $freeDelivery): void
    {
        $this->freeDelivery = $freeDelivery;
    }

    public function getRestockTime(): ?int
    {
        return $this->restockTime;
    }

    public function setRestockTime(?int $restockTime): void
    {
        $this->restockTime = $restockTime;
    }

    public function getDeliveryTime(): ?DeliveryTime
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(?DeliveryTime $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): void
    {
        $this->length = $length;
    }

    public function getVolume(): float
    {
        if ($this->getLength() === null || $this->getLength() <= 0.0) {
            return 0;
        }

        if ($this->getWidth() === null || $this->getWidth() <= 0.0) {
            return 0;
        }

        if ($this->getHeight() === null || $this->getHeight() <= 0.0) {
            return 0;
        }

        return $this->getLength() * $this->getWidth() * $this->getHeight();
    }

    public function getApiAlias(): string
    {
        return 'cart_delivery_information';
    }
}
