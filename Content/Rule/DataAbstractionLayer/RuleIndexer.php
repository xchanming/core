<?php declare(strict_types=1);

namespace Cicada\Core\Content\Rule\DataAbstractionLayer;

use Cicada\Core\Content\Rule\Event\RuleIndexerEvent;
use Cicada\Core\Content\Rule\RuleDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Cicada\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @final
 */
#[Package('services-settings')]
class RuleIndexer extends EntityIndexer
{
    final public const PAYLOAD_UPDATER = 'rule.payload';

    final public const AREA_UPDATER = 'rule.area';

    /**
     * @internal
     */
    public function __construct(
        private readonly IteratorFactory $iteratorFactory,
        private readonly EntityRepository $repository,
        private readonly RulePayloadUpdater $payloadUpdater,
        private readonly RuleAreaUpdater $areaUpdater,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getName(): string
    {
        return 'rule.indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new RuleIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $updates = $event->getPrimaryKeys(RuleDefinition::ENTITY_NAME);

        if (empty($updates)) {
            return null;
        }

        $this->handle(new RuleIndexingMessage(array_values($updates), null, $event->getContext()));

        return null;
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

        if ($message->allow(self::PAYLOAD_UPDATER)) {
            $this->payloadUpdater->update($ids);
        }

        if ($message->allow(self::AREA_UPDATER)) {
            $this->areaUpdater->update($ids);
        }

        $this->eventDispatcher->dispatch(new RuleIndexerEvent($ids, $message->getContext(), $message->getSkip()));
    }

    public function getTotal(): int
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition())->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }
}
