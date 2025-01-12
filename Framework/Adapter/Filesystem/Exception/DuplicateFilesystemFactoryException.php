<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Filesystem\Exception;

use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class DuplicateFilesystemFactoryException extends CicadaHttpException
{
    public function __construct(string $type)
    {
        parent::__construct('The type of factory "{{ type }}" must be unique.', ['type' => $type]);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__DUPLICATE_FILESYSTEM_FACTORY';
    }
}
