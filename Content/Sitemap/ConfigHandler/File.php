<?php declare(strict_types=1);

namespace Cicada\Core\Content\Sitemap\ConfigHandler;

use Cicada\Core\Content\Sitemap\Service\ConfigHandler;
use Cicada\Core\Framework\Log\Package;

#[Package('services-settings')]
class File implements ConfigHandlerInterface
{
    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    private $excludedUrls;

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    private $customUrls;

    /**
     * @internal
     */
    public function __construct($sitemapConfig)
    {
        $this->customUrls = $sitemapConfig[ConfigHandler::CUSTOM_URLS_KEY];
        $this->excludedUrls = $sitemapConfig[ConfigHandler::EXCLUDED_URLS_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function getSitemapConfig(): array
    {
        return [
            ConfigHandler::CUSTOM_URLS_KEY => $this->getSitemapCustomUrls($this->customUrls),
            ConfigHandler::EXCLUDED_URLS_KEY => $this->excludedUrls,
        ];
    }

    private function getSitemapCustomUrls(array $customUrls): array
    {
        array_walk($customUrls, static function (array &$customUrl): void {
            $customUrl['lastMod'] = \DateTime::createFromFormat('Y-m-d H:i:s', $customUrl['lastMod']);
        });

        return $customUrls;
    }
}
