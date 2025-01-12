<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Indexing\Subscriber;

use Cicada\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Cicada\Core\Framework\DataAbstractionLayer\Indexing\SynchronousPostUpdateIndexer;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\IndexerQueuer;
use Cicada\Core\Framework\Store\Event\FirstRunWizardFinishedEvent;
use Cicada\Core\Framework\Update\Event\UpdatePostFinishEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('core')]
class RegisteredIndexerSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly IndexerQueuer $indexerQueuer,
        private readonly EntityIndexerRegistry $indexerRegistry
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UpdatePostFinishEvent::class => 'runRegisteredIndexers',
            FirstRunWizardFinishedEvent::class => 'runRegisteredIndexers',
        ];
    }

    /**
     * @internal
     */
    public function runRegisteredIndexers(): void
    {
        $queuedIndexers = $this->indexerQueuer->getIndexers();

        if (empty($queuedIndexers)) {
            return;
        }

        $this->indexerQueuer->finishIndexer(array_keys($queuedIndexers));

        foreach ($queuedIndexers as $indexerName => $options) {
            $indexer = $this->indexerRegistry->getIndexer($indexerName);

            if ($indexer === null) {
                continue;
            }

            $skipList = [];
            if ($options !== []) {
                $skipList = array_values(array_diff($indexer->getOptions(), $options));
            }

            if ($indexer instanceof SynchronousPostUpdateIndexer) {
                $this->indexerRegistry->index(false, $skipList, [$indexerName], true);

                continue;
            }

            $this->indexerRegistry->sendIndexingMessage([$indexerName], $skipList, true);
        }
    }
}
