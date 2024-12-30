<?php declare(strict_types=1);

namespace Cicada\Core\Content\ProductExport\Event;

use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('inventory')]
class ProductExportContentTypeEvent extends Event
{
    public function __construct(
        private readonly string $fileFormat,
        private string $contentType
    ) {
    }

    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }
}
