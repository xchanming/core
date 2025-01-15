<?php declare(strict_types=1);

namespace Cicada\Core\Content\ContactForm\SalesChannel;

use Cicada\Core\Checkout\Customer\Service\EmailIdnConverter;
use Cicada\Core\Content\Category\CategoryEntity;
use Cicada\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Cicada\Core\Content\ContactForm\Event\ContactFormEvent;
use Cicada\Core\Content\LandingPage\LandingPageDefinition;
use Cicada\Core\Content\LandingPage\LandingPageEntity;
use Cicada\Core\Content\Product\ProductDefinition;
use Cicada\Core\Content\Product\ProductEntity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\EventData\MailRecipientStruct;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\RateLimiter\RateLimiter;
use Cicada\Core\Framework\Validation\DataBag\DataBag;
use Cicada\Core\Framework\Validation\DataBag\RequestDataBag;
use Cicada\Core\Framework\Validation\DataValidationFactoryInterface;
use Cicada\Core\Framework\Validation\DataValidator;
use Cicada\Core\Framework\Validation\Exception\ConstraintViolationException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('buyers-experience')]
class ContactFormRoute extends AbstractContactFormRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly DataValidationFactoryInterface $contactFormValidationFactory,
        private readonly DataValidator $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $cmsSlotRepository,
        private readonly EntityRepository $salutationRepository,
        private readonly EntityRepository $categoryRepository,
        private readonly EntityRepository $landingPageRepository,
        private readonly EntityRepository $productRepository,
        private readonly RequestStack $requestStack,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    public function getDecorated(): AbstractContactFormRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/contact-form', name: 'store-api.contact.form', methods: ['POST'])]
    public function load(RequestDataBag $data, SalesChannelContext $context): ContactFormRouteResponse
    {
        EmailIdnConverter::encodeDataBag($data);

        $this->validateContactForm($data, $context);

        if (($request = $this->requestStack->getMainRequest()) !== null && $request->getClientIp() !== null) {
            $this->rateLimiter->ensureAccepted(RateLimiter::CONTACT_FORM, $request->getClientIp());
        }

        $mailConfigs = $this->getMailConfigs($context, $data->get('slotId'), $data->get('navigationId'), $data->get('entityName'));

        $salutationCriteria = new Criteria([$data->get('salutationId')]);
        $salutationSearchResult = $this->salutationRepository->search($salutationCriteria, $context->getContext());

        if ($salutationSearchResult->count() !== 0) {
            $data->set('salutation', $salutationSearchResult->first());
        }

        if (empty($mailConfigs['receivers'])) {
            $mailConfigs['receivers'][] = $this->systemConfigService->get('core.basicInformation.email', $context->getSalesChannelId());
        }

        $recipientStructs = [];
        foreach ($mailConfigs['receivers'] as $mail) {
            $recipientStructs[$mail] = $mail;
        }

        /** @var array<string, mixed> $recipientStructs */
        $event = new ContactFormEvent(
            $context->getContext(),
            $context->getSalesChannelId(),
            new MailRecipientStruct($recipientStructs),
            $data
        );

        $this->eventDispatcher->dispatch(
            $event,
            ContactFormEvent::EVENT_NAME
        );

        $result = new ContactFormRouteResponseStruct();
        $result->assign([
            'individualSuccessMessage' => $mailConfigs['message'] ?? '',
        ]);

        return new ContactFormRouteResponse($result);
    }

    private function validateContactForm(DataBag $data, SalesChannelContext $context): void
    {
        $definition = $this->contactFormValidationFactory->create($context);
        $violations = $this->validator->getViolations($data->all(), $definition);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $data->all());
        }
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function getSlotConfig(string $slotId, string $navigationId, SalesChannelContext $context, ?string $entityName = null): array
    {
        $mailConfigs = [];
        $mailConfigs['receivers'] = [];
        $mailConfigs['message'] = '';

        $criteria = new Criteria([$navigationId]);

        /** @var CategoryEntity|ProductEntity|LandingPageEntity $entity */
        $entity = match ($entityName) {
            ProductDefinition::ENTITY_NAME => $this->productRepository->search($criteria, $context->getContext())->first(),
            LandingPageDefinition::ENTITY_NAME => $this->landingPageRepository->search($criteria, $context->getContext())->first(),
            default => $this->categoryRepository->search($criteria, $context->getContext())->first(),
        };

        if (!$entity) {
            return $mailConfigs;
        }

        if (empty($entity->getSlotConfig()[$slotId])) {
            return $mailConfigs;
        }

        $mailConfigs['receivers'] = $entity->getSlotConfig()[$slotId]['mailReceiver']['value'];
        $mailConfigs['message'] = $entity->getSlotConfig()[$slotId]['confirmationText']['value'];

        return $mailConfigs;
    }

    /**
     * @return array<string, array<string, array<int, mixed>|bool|float|int|string|null>|string|mixed>
     */
    private function getMailConfigs(SalesChannelContext $context, ?string $slotId = null, ?string $navigationId = null, ?string $entityName = null): array
    {
        $mailConfigs = [];
        $mailConfigs['receivers'] = [];
        $mailConfigs['message'] = '';

        if (!$slotId) {
            return $mailConfigs;
        }

        if ($navigationId) {
            $mailConfigs = $this->getSlotConfig($slotId, $navigationId, $context, $entityName);
            if (!empty($mailConfigs['receivers'])) {
                return $mailConfigs;
            }
        }

        $criteria = new Criteria([$slotId]);

        /** @var CmsSlotEntity|null $slot */
        $slot = $this->cmsSlotRepository->search($criteria, $context->getContext())->getEntities()->first();

        if (!$slot) {
            return $mailConfigs;
        }

        $mailConfigs['receivers'] = $slot->getTranslated()['config']['mailReceiver']['value'];
        $mailConfigs['message'] = $slot->getTranslated()['config']['confirmationText']['value'];

        return $mailConfigs;
    }
}
