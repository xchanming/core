<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Address\Error;

use Cicada\Core\Checkout\Cart\Error\Error;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
abstract class SalutationMissingError extends Error
{
    protected const KEY = 'salutation-missing';

    /**
     * @var array<string, string>
     */
    protected array $parameters;

    abstract public function getId(): string;

    public function getMessageKey(): string
    {
        return $this->getId();
    }

    public function getLevel(): int
    {
        return Error::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
