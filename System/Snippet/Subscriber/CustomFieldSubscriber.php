<?php declare(strict_types=1);

namespace Cicada\Core\System\Snippet\Subscriber;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Cicada\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Cicada\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('discovery')]
class CustomFieldSubscriber implements EventSubscriberInterface
{
    private const CUSTOM_FIELD_ID_FIELD = 'custom_field_id';

    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'custom_field.written' => 'customFieldIsWritten',
            'custom_field.deleted' => 'customFieldIsDeleted',
        ];
    }

    public function customFieldIsWritten(EntityWrittenEvent $event): void
    {
        $snippets = [];
        $snippetSets = null;
        foreach ($event->getWriteResults() as $writeResult) {
            if (!isset($writeResult->getPayload()['config']['label']) || empty($writeResult->getPayload()['config']['label'])) {
                continue;
            }

            if ($writeResult->getOperation() === EntityWriteResult::OPERATION_INSERT) {
                if ($snippetSets === null) {
                    $snippetSets = $this->connection->fetchAllAssociative('SELECT id, iso FROM snippet_set');
                }

                if (empty($snippetSets)) {
                    return;
                }

                $this->setInsertSnippets($writeResult, $snippetSets, $snippets);
            }
        }

        if (empty($snippets)) {
            return;
        }

        $queue = new MultiInsertQueryQueue($this->connection, 500, false, false);
        $queue->addUpdateFieldOnDuplicateKey('snippet', 'value');

        foreach ($snippets as $snippet) {
            $queue->addInsert('snippet', $snippet);
        }

        $queue->execute();
    }

    public function customFieldIsDeleted(EntityDeletedEvent $event): void
    {
        $this->connection->executeStatement(
            'DELETE FROM `snippet`
            WHERE JSON_EXTRACT(`custom_fields`, "$.custom_field_id") IN (:customFieldIds)',
            ['customFieldIds' => $event->getIds()],
            ['customFieldIds' => ArrayParameterType::STRING]
        );
    }

    /**
     * @param array<array<string, string>> $snippetSets
     * @param list<array<string, mixed>> $snippets
     */
    private function setInsertSnippets(EntityWriteResult $writeResult, array $snippetSets, array &$snippets): void
    {
        $name = $writeResult->getPayload()['name'];
        $labels = $writeResult->getPayload()['config']['label'];

        foreach ($snippetSets as $snippetSet) {
            $label = $name;
            $iso = $snippetSet['iso'];

            if (isset($labels[$iso])) {
                $label = $labels[$iso];
            }

            $snippets[] = [
                'id' => Uuid::randomBytes(),
                'snippet_set_id' => $snippetSet['id'],
                'translation_key' => 'customFields.' . $name,
                'value' => $label,
                'author' => 'System',
                'custom_fields' => json_encode([
                    self::CUSTOM_FIELD_ID_FIELD => $writeResult->getPrimaryKey(),
                ], \JSON_THROW_ON_ERROR),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];
        }
    }
}
