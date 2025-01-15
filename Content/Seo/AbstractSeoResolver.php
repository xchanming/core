<?php declare(strict_types=1);

namespace Cicada\Core\Content\Seo;

use Cicada\Core\Framework\Log\Package;

/**
 * @phpstan-type ResolvedSeoUrl = array{id?: string, pathInfo: string, isCanonical: bool|string, canonicalPathInfo?: string}
 */
#[Package('buyers-experience')]
abstract class AbstractSeoResolver
{
    abstract public function getDecorated(): AbstractSeoResolver;

    /**
     * @return ResolvedSeoUrl
     */
    abstract public function resolve(string $languageId, string $salesChannelId, string $pathInfo): array;
}
