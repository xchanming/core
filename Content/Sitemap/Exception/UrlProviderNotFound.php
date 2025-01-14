<?php declare(strict_types=1);

namespace Cicada\Core\Content\Sitemap\Exception;

use Cicada\Core\Framework\CicadaHttpException;
use Cicada\Core\Framework\Log\Package;

#[Package('discovery')]
class UrlProviderNotFound extends CicadaHttpException
{
    public function __construct(string $provider)
    {
        parent::__construct('provider "{{ provider }}" not found.', ['provider' => $provider]);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__SITEMAP_PROVIDER_NOT_FOUND';
    }
}
