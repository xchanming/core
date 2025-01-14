<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\LineItem\Group;

use Cicada\Core\Checkout\Cart\CartException;
use Cicada\Core\Framework\Log\Package;

#[Package('checkout')]
class LineItemGroupServiceRegistry
{
    /**
     * @internal
     *
     * @param iterable<LineItemGroupPackagerInterface> $packagers
     * @param iterable<LineItemGroupSorterInterface> $sorters
     */
    public function __construct(
        private readonly iterable $packagers,
        private readonly iterable $sorters
    ) {
    }

    /**
     * Gets a list of all registered packagers.
     */
    public function getPackagers(): \Generator
    {
        foreach ($this->packagers as $packager) {
            yield $packager;
        }
    }

    /**
     * Gets the packager for the provided key, if registered.
     *
     * @throws CartException
     */
    public function getPackager(string $key): LineItemGroupPackagerInterface
    {
        /** @var LineItemGroupPackagerInterface $packager */
        foreach ($this->packagers as $packager) {
            if (mb_strtolower($packager->getKey()) === mb_strtolower($key)) {
                return $packager;
            }
        }

        throw CartException::lineItemGroupPackagerNotFoundException($key);
    }

    /**
     * Gets a list of all registered sorters.
     */
    public function getSorters(): \Generator
    {
        foreach ($this->sorters as $sorter) {
            yield $sorter;
        }
    }

    /**
     * Gets the sorter for the provided key, if registered.
     *
     * @throws CartException
     */
    public function getSorter(string $key): LineItemGroupSorterInterface
    {
        /** @var LineItemGroupSorterInterface $sorter */
        foreach ($this->sorters as $sorter) {
            if (mb_strtolower($sorter->getKey()) === mb_strtolower($key)) {
                return $sorter;
            }
        }

        throw CartException::lineItemGroupSorterNotFoundException($key);
    }
}
