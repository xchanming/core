<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\MediaType;

use Cicada\Core\Framework\Log\Package;

#[Package('discovery')]
class ImageType extends MediaType
{
    final public const ANIMATED = 'animated';
    final public const TRANSPARENT = 'transparent';
    final public const VECTOR_GRAPHIC = 'vectorGraphic';
    final public const ICON = 'image/x-icon';

    /**
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name = 'IMAGE';
}
