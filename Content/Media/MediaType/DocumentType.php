<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\MediaType;

use Cicada\Core\Framework\Log\Package;

#[Package('discovery')]
class DocumentType extends MediaType
{
    /**
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name = 'DOCUMENT';
}
