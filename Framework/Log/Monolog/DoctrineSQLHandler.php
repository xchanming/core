<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Log\Monolog;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

#[Package('core')]
class DoctrineSQLHandler extends AbstractProcessingHandler
{
    /**
     * @var Connection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $connection;

    /**
     * @internal
     */
    public function __construct(
        Connection $connection,
        Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->connection = $connection;
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $envelope = [
            'id' => Uuid::randomBytes(),
            'message' => $record->message,
            'level' => $record->level->value,
            'channel' => $record->channel,
            'context' => json_encode($record->context, \JSON_THROW_ON_ERROR),
            'extra' => json_encode($record->extra, \JSON_THROW_ON_ERROR),
            'updated_at' => null,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        try {
            $this->connection->insert('log_entry', $envelope);
        } catch (\Throwable) {
            $envelope['context'] = json_encode([]);
            $envelope['extra'] = json_encode([]);
            $this->connection->insert('log_entry', $envelope);
        }
    }
}
