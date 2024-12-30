<?php declare(strict_types=1);

namespace Cicada\Core\Content\Cms\Extension;

use Cicada\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Cicada\Core\Content\Cms\DataResolver\CriteriaCollection;
use Cicada\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Cicada\Core\Framework\Extensions\Extension;
use Cicada\Core\Framework\Log\Package;

/**
 * @public This class is used as type-hint for all event listeners, so the class string is "public consumable" API
 *
 * @title Determination of the criteria list which is used to load CMS elements in the CMS page resolve process
 *
 * @description This event allows interception of the collection process, where a criteria list is populated using the respective CMS resolver.
 * The resulting criteria list is then used to load CMS elements during the CMS page resolution process.
 *
 * @experimental stableVersion:v6.7.0 feature:EXTENSION_SYSTEM
 *
 * @codeCoverageIgnore
 *
 * @extends Extension<array<string, CriteriaCollection>>
 */
#[Package('buyers-experience')]
final class CmsSlotsDataCollectExtension extends Extension
{
    public const NAME = 'cms-slots-data.collect';

    /**
     * @internal Cicada owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The slot collection which is used to determine the correct resolver for each CMS slot by id and type
         */
        public readonly CmsSlotCollection $slots,
        /**
         * @public
         *
         * @description Allows you to access to the current resolver-context
         */
        public readonly ResolverContext $resolverContext,
    ) {
    }
}
