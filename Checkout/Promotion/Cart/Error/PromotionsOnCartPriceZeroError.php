<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\Cart\Error;

use Cicada\Core\Checkout\Cart\Error\Error;
use Cicada\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class PromotionsOnCartPriceZeroError extends Error
{
    private const KEY = 'promotions-on-cart-price-zero-error';

    /**
     * @param string[] $promotions
     */
    public function __construct(protected array $promotions)
    {
        $this->message = \sprintf(
            'Promotions %s were excluded for cart because the price of the cart is zero.',
            $this->getParameters()['promotions']
        );

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function getId(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    /**
     * @return string[]
     */
    public function getPromotions(): array
    {
        return $this->promotions;
    }

    public function blockOrder(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return [
            'promotions' => implode(', ', $this->promotions),
        ];
    }
}
