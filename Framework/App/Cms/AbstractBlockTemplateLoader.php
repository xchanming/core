<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Cms;

use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('buyers-experience')]
abstract class AbstractBlockTemplateLoader
{
    abstract public function getTemplateForBlock(CmsExtensions $cmsExtensions, string $blockName): string;

    abstract public function getStylesForBlock(CmsExtensions $cmsExtensions, string $blockName): string;
}
