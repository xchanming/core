<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\MediaType;

use Cicada\Core\Framework\Log\Package;

/**
 * @experimental stableVersion:v6.7.0 feature:SPATIAL_BASES
 */
#[Package('buyers-experience')]
class SpatialObjectType extends MediaType
{
    /**
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name = 'SPATIAL_OBJECT';
}
