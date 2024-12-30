<?php declare(strict_types=1);

namespace Cicada\Core\Content\ImportExport\Struct;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Struct\Struct;

#[Package('services-settings')]
class Progress extends Struct
{
    final public const STATE_PROGRESS = 'progress';
    final public const STATE_MERGING_FILES = 'merging_files';
    final public const STATE_SUCCEEDED = 'succeeded';
    final public const STATE_FAILED = 'failed';
    final public const STATE_ABORTED = 'aborted';

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $logId;

    /**
     * @var string|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $invalidRecordsLogId;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $offset = 0;

    /**
     * @var int|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $total;

    /**
     * @var int
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $processedRecords = 0;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $state;

    public function __construct(
        string $logId,
        string $state,
        int $offset = 0,
        ?int $total = null
    ) {
        $this->logId = $logId;
        $this->state = $state;
        $this->offset = $offset;
        $this->total = $total;
    }

    public function addProcessedRecords(int $processedRecords): void
    {
        $this->processedRecords += $processedRecords;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getProcessedRecords(): ?int
    {
        return $this->processedRecords;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getLogId(): string
    {
        return $this->logId;
    }

    public function getInvalidRecordsLogId(): ?string
    {
        return $this->invalidRecordsLogId;
    }

    public function setInvalidRecordsLogId(?string $invalidRecordsLogId): void
    {
        $this->invalidRecordsLogId = $invalidRecordsLogId;
    }

    public function isFinished(): bool
    {
        return $this->getState() === self::STATE_SUCCEEDED
            || $this->getState() === self::STATE_FAILED
            || $this->getState() === self::STATE_ABORTED;
    }
}
