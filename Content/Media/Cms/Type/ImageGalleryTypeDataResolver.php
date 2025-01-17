<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\Cms\Type;

use Cicada\Core\Framework\Log\Package;

#[Package('discovery')]
class ImageGalleryTypeDataResolver extends ImageSliderTypeDataResolver
{
    public function getType(): string
    {
        return 'image-gallery';
    }
}
