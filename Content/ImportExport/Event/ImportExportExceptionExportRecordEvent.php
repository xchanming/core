<?php declare(strict_types=1);

namespace Cicada\Core\Content\ImportExport\Event;

use Cicada\Core\Content\ImportExport\Struct\Config;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('services-settings')]
class ImportExportExceptionExportRecordEvent extends Event
{
    /**
     * @param array<int|string, \Throwable> $exceptions
     * @param array<int|string, mixed> $record
     */
    public function __construct(
        private array $exceptions,
        private readonly array $record,
        private readonly Config $config,
        private readonly Context $context
    ) {
    }

    /**
     * @return array<int|string, \Throwable>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param array<int|string, \Throwable> $exceptions
     */
    public function setExceptions(array $exceptions): void
    {
        $this->exceptions = $exceptions;
    }

    public function clearExceptions(): void
    {
        $this->exceptions = [];
    }

    public function hasExceptions(): bool
    {
        return \count($this->exceptions) > 0;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getRecord(): array
    {
        return $this->record;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
