<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Routing;

use Cicada\Core\Framework\Feature;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Cicada\Core\Framework\Util\Random;
use Cicada\Core\PlatformRequest;
use Cicada\Core\SalesChannelRequest;
use Cicada\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Cicada\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

#[Package('core')]
class SalesChannelRequestContextResolver implements RequestContextResolverInterface
{
    use RouteScopeCheckTrait;

    /**
     * @internal
     */
    public function __construct(
        private readonly RequestContextResolverInterface $decorated,
        private readonly SalesChannelContextServiceInterface $contextService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouteScopeRegistry $routeScopeRegistry
    ) {
    }

    public function resolve(Request $request): void
    {
        if (!$request->attributes->has(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID)) {
            $this->decorated->resolve($request);

            return;
        }

        if (!$this->isRequestScoped($request, SalesChannelContextRouteScopeDependant::class)) {
            return;
        }

        if (!$request->headers->has(PlatformRequest::HEADER_CONTEXT_TOKEN)) {
            if ($this->contextTokenRequired($request)) {
                throw RoutingException::missingRequestParameter(PlatformRequest::HEADER_CONTEXT_TOKEN);
            }

            $request->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, Random::getAlphanumericString(32));
        }

        $session = $request->hasSession() ? $request->getSession() : null;

        // Retrieve context for current request
        $usedContextToken = (string) $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);
        $contextServiceParameters = new SalesChannelContextServiceParameters(
            (string) $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID),
            $usedContextToken,
            $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID),
            $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_CURRENCY_ID),
            $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID),
            $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT),
            null,
            $session?->get(PlatformRequest::ATTRIBUTE_IMITATING_USER_ID)
        );
        $context = $this->contextService->get($contextServiceParameters);

        // Remove imitating user id from session, if there is no customer
        if ($session && $context->getImitatingUserId() && !$context->getCustomerId()) {
            $session->remove(PlatformRequest::ATTRIBUTE_IMITATING_USER_ID);
            $context->setImitatingUserId(null);
        }

        // Validate if a customer login is required for the current request
        $this->validateLogin($request, $context);

        // Update attributes and headers of the current request
        $request->attributes->set(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT, $context->getContext());
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $context);
        $request->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $context->getToken());

        $this->eventDispatcher->dispatch(
            new SalesChannelContextResolvedEvent($context, $usedContextToken)
        );
    }

    /**
     * @deprecated tag:v6.7.0 - Not used anymore, will be removed without replacement
     */
    public function handleSalesChannelContext(Request $request, string $salesChannelId, string $contextToken): void
    {
        Feature::triggerDeprecationOrThrow(
            'v6.7.0.0',
            'SalesChannelRequestContextResolver::handleSalesChannelContext does not need to be called anymore. Will be removed with no replacement',
        );

        $language = $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID);
        $currencyId = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_CURRENCY_ID);

        $context = $this->contextService
            ->get(new SalesChannelContextServiceParameters($salesChannelId, $contextToken, $language, $currencyId));

        $request->attributes->set(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT, $context->getContext());
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $context);
    }

    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }

    private function contextTokenRequired(Request $request): bool
    {
        return (bool) $request->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_TOKEN_REQUIRED, false);
    }

    private function validateLogin(Request $request, SalesChannelContext $context): void
    {
        if (!$request->attributes->get(PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED)) {
            return;
        }

        if ($context->getCustomer() === null) {
            throw RoutingException::customerNotLoggedIn();
        }

        if ($request->attributes->get(PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED_ALLOW_GUEST, false) === false && $context->getCustomer()->getGuest()) {
            throw RoutingException::customerNotLoggedIn();
        }
    }
}
