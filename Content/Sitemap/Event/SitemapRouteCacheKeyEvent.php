<?php declare(strict_types=1);

namespace Cicada\Core\Content\Sitemap\Event;

use Cicada\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use Cicada\Core\Framework\Log\Package;

#[Package('discovery')]
class SitemapRouteCacheKeyEvent extends StoreApiRouteCacheKeyEvent
{
}
