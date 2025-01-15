<?php declare(strict_types=1);

namespace Cicada\Core\System\Country\SalesChannel;

use Cicada\Core\Framework\Adapter\Cache\AbstractCacheTracer;
use Cicada\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Cicada\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Hasher;
use Cicada\Core\System\Country\Event\CountryStateRouteCacheKeyEvent;
use Cicada\Core\System\Country\Event\CountryStateRouteCacheTagsEvent;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\SalesChannel\StoreApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @deprecated tag:v6.7.0 - reason:decoration-will-be-removed - Will be removed
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('fundamentals@discovery')]
class CachedCountryStateRoute extends AbstractCountryStateRoute
{
    final public const ALL_TAG = 'country-state-route';

    /**
     * @internal
     *
     * @param AbstractCacheTracer<CountryStateRouteResponse> $tracer
     * @param array<string> $states
     */
    public function __construct(
        private readonly AbstractCountryStateRoute $decorated,
        private readonly CacheInterface $cache,
        private readonly EntityCacheKeyGenerator $generator,
        private readonly AbstractCacheTracer $tracer,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly array $states
    ) {
    }

    public static function buildName(string $id): string
    {
        return CountryStateRoute::buildName($id);
    }

    #[Route(path: '/store-api/country-state/{countryId}', name: 'store-api.country.state', methods: ['GET', 'POST'], defaults: ['_entity' => 'country'])]
    public function load(string $countryId, Request $request, Criteria $criteria, SalesChannelContext $context): CountryStateRouteResponse
    {
        if (Feature::isActive('cache_rework')) {
            return $this->getDecorated()->load($countryId, $request, $criteria, $context);
        }
        if ($context->hasState(...$this->states)) {
            return $this->getDecorated()->load($countryId, $request, $criteria, $context);
        }

        $key = $this->generateKey($countryId, $request, $context, $criteria);

        if ($key === null) {
            return $this->getDecorated()->load($countryId, $request, $criteria, $context);
        }

        $value = $this->cache->get($key, function (ItemInterface $item) use ($countryId, $request, $criteria, $context) {
            $name = self::buildName($countryId);
            $response = $this->tracer->trace($name, fn () => $this->getDecorated()->load($countryId, $request, $criteria, $context));

            $item->tag($this->generateTags($countryId, $request, $response, $context, $criteria));

            return CacheValueCompressor::compress($response);
        });

        return CacheValueCompressor::uncompress($value);
    }

    protected function getDecorated(): AbstractCountryStateRoute
    {
        return $this->decorated;
    }

    private function generateKey(string $countryId, Request $request, SalesChannelContext $context, Criteria $criteria): ?string
    {
        $parts = [
            $countryId,
            $this->generator->getCriteriaHash($criteria),
            $context->getLanguageId(),
        ];

        $event = new CountryStateRouteCacheKeyEvent($parts, $request, $context, $criteria);
        $this->dispatcher->dispatch($event);

        if (!$event->shouldCache()) {
            return null;
        }

        return self::buildName($countryId) . '-' . Hasher::hash($event->getParts());
    }

    /**
     * @return array<string>
     */
    private function generateTags(string $countryId, Request $request, StoreApiResponse $response, SalesChannelContext $context, Criteria $criteria): array
    {
        $tags = array_merge(
            $this->tracer->get(self::buildName($countryId)),
            [self::buildName($countryId), self::ALL_TAG]
        );

        $event = new CountryStateRouteCacheTagsEvent($tags, $request, $response, $context, $criteria);
        $this->dispatcher->dispatch($event);

        return array_unique(array_filter($event->getTags()));
    }
}
