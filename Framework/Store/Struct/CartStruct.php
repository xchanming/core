<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class CartStruct extends Struct
{
    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $netPrice;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxValue;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $taxRate;

    /**
     * @var float
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $grossPrice;

    /**
     * @var CartPositionCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $positions;

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $shop;

    public static function fromArray(array $data): CartStruct
    {
        $data['positions'] = new CartPositionCollection($data['positions']);

        return (new self())->assign($data);
    }

    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    public function setNetPrice(float $netPrice): void
    {
        $this->netPrice = $netPrice;
    }

    public function getTaxValue(): float
    {
        return $this->taxValue;
    }

    public function setTaxValue(float $taxValue): void
    {
        $this->taxValue = $taxValue;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): void
    {
        $this->taxRate = $taxRate;
    }

    public function getGrossPrice(): float
    {
        return $this->grossPrice;
    }

    public function setGrossPrice(float $grossPrice): void
    {
        $this->grossPrice = $grossPrice;
    }

    public function getPositions(): CartPositionCollection
    {
        return $this->positions;
    }

    public function setPositions(CartPositionCollection $positions): void
    {
        $this->positions = $positions;
    }

    public function getShop(): array
    {
        return $this->shop;
    }

    public function setShop(array $shop): void
    {
        $this->shop = $shop;
    }

    public function getShopId(): int
    {
        return $this->getShop()['id'];
    }

    public function getShopDomain(): string
    {
        return $this->getShop()['domain'];
    }

    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        unset($vars['extensions']);

        return $vars;
    }
}
