<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\Cms;

use Cicada\Core\Framework\App\Exception\AppCmsExtensionException;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
#[Package('buyers-experience')]
class BlockTemplateLoader extends AbstractBlockTemplateLoader
{
    public function getTemplateForBlock(CmsExtensions $cmsExtensions, string $blockName): string
    {
        try {
            $templateFiles = (new Finder())
                ->files()
                ->name('preview.html')
                ->in(\sprintf('%s/cms/blocks/%s', $cmsExtensions->getPath(), $blockName));

            foreach ($templateFiles as $templateFile) {
                return $templateFile->getContents();
            }
        } catch (\Exception) {
        }

        throw new AppCmsExtensionException(\sprintf('Preview file for block "%s" is missing', $blockName));
    }

    public function getStylesForBlock(CmsExtensions $cmsExtensions, string $blockName): string
    {
        try {
            $styleFiles = (new Finder())
                ->files()
                ->name('styles.css')
                ->in(\sprintf('%s/cms/blocks/%s', $cmsExtensions->getPath(), $blockName));

            foreach ($styleFiles as $styleFile) {
                return $styleFile->getContents();
            }
        } catch (\Exception) {
        }

        throw new AppCmsExtensionException(\sprintf('Style file for block "%s" is missing', $blockName));
    }
}
