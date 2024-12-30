<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Promotion\DataAbstractionLayer;

use Cicada\Core\Checkout\Promotion\Aggregate\PromotionIndividualCode\PromotionIndividualCodeDefinition;
use Cicada\Core\Checkout\Promotion\Event\PromotionIndexerEvent;
use Cicada\Core\Checkout\Promotion\PromotionDefinition;
use Cicada\Core\Framework\Api\Context\AdminApiSource;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Cicada\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('buyers-experience')]
class PromotionIndexer extends EntityIndexer
{
    final public const EXCLUSION_UPDATER = 'promotion.exclusion';
    final public const REDEMPTION_UPDATER = 'promotion.redemption';

    /**
     * @internal
     */
    public function __construct(
        private readonly IteratorFactory $iteratorFactory,
        private readonly EntityRepository $repository,
        private readonly PromotionExclusionUpdater $exclusionUpdater,
        private readonly PromotionRedemptionUpdater $redemptionUpdater,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getName(): string
    {
        return 'promotion.indexer';
    }

    /**
     * @param array{offset: int|null}|null $offset
     */
    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new PromotionIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $updates = $event->getPrimaryKeys(PromotionDefinition::ENTITY_NAME);

        if (empty($updates)) {
            return null;
        }

        if ($this->isGeneratingIndividualCode($event)) {
            return null;
        }

        return new PromotionIndexingMessage(array_values($updates), null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        if (!\is_array($ids)) {
            return;
        }

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        if ($message->allow(self::EXCLUSION_UPDATER)) {
            $this->exclusionUpdater->update($ids);
        }

        if ($message->allow(self::REDEMPTION_UPDATER)) {
            $this->redemptionUpdater->update($ids, $message->getContext());
        }

        $this->eventDispatcher->dispatch(new PromotionIndexerEvent($ids, $message->getContext(), $message->getSkip()));
    }

    public function getOptions(): array
    {
        return [
            self::EXCLUSION_UPDATER,
            self::REDEMPTION_UPDATER,
        ];
    }

    public function getTotal(): int
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition())->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }

    private function isGeneratingIndividualCode(EntityWrittenContainerEvent $event): bool
    {
        $events = $event->getEvents();

        if (!$event->getContext()->getSource() instanceof AdminApiSource || $events === null || $events->count() !== 2) {
            return false;
        }

        $promotionIndividualWrittenEvent = $event->getEventByEntityName(PromotionIndividualCodeDefinition::ENTITY_NAME);

        if ($promotionIndividualWrittenEvent === null || $promotionIndividualWrittenEvent->getName() !== 'promotion_individual_code.written') {
            return false;
        }

        $promotionWrittenEvent = $event->getEventByEntityName(PromotionDefinition::ENTITY_NAME);

        if ($promotionWrittenEvent === null || $promotionWrittenEvent->getName() !== 'promotion.written' || !empty($promotionWrittenEvent->getPayloads()[0])) {
            return false;
        }

        return true;
    }
}
