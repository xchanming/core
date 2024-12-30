<?php declare(strict_types=1);

namespace Cicada\Core\Content\Media\Event;

use Cicada\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('buyers-experience')]
class MediaThumbnailDeletedEvent extends Event
{
    final public const EVENT_NAME = 'media_thumbnail.after_delete';

    public function __construct(
        private readonly MediaThumbnailCollection $thumbnails,
        private readonly Context $context
    ) {
    }

    public function getThumbnails(): MediaThumbnailCollection
    {
        return $this->thumbnails;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
