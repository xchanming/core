<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Indexing\MessageQueue;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('core')]
class IterateEntityIndexerMessage implements AsyncMessageInterface
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $indexer;

    /**
     * @internal
     *
     * @param array{offset: int|null}|null $offset
     * @param array<string> $skip
     */
    public function __construct(
        string $indexer,
        protected ?array $offset,
        protected array $skip = []
    ) {
        $this->indexer = $indexer;
    }

    public function getIndexer(): string
    {
        return $this->indexer;
    }

    /**
     * @return array{offset: int|null}|null
     */
    public function getOffset(): ?array
    {
        return $this->offset;
    }

    /**
     * @param array{offset: int|null}|null $offset
     */
    public function setOffset(?array $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return array<string>
     */
    public function getSkip(): array
    {
        return $this->skip;
    }
}
