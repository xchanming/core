<?php declare(strict_types=1);

namespace Cicada\Core\Content\ImportExport\Event;

use Cicada\Core\Content\ImportExport\Message\ImportExportMessage;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('services-settings')]
class ImportExportExceptionImportExportHandlerEvent extends Event
{
    public function __construct(
        private ?\Throwable $exception,
        private readonly ImportExportMessage $message
    ) {
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    public function setException(?\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function clearException(): void
    {
        $this->exception = null;
    }

    public function hasException(): bool
    {
        return $this->exception instanceof \Throwable;
    }

    public function getMessage(): ImportExportMessage
    {
        return $this->message;
    }
}
