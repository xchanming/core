<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Webhook\Service;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Webhook\EventLog\WebhookEventLogDefinition;
use Cicada\Core\System\SystemConfig\SystemConfigService;
use Doctrine\DBAL\Connection;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;

/**
 * @internal
 */
#[Package('core')]
class WebhookCleanup
{
    private const BATCH_SIZE = 500;

    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Connection $connection,
        private readonly ClockInterface $clock = new NativeClock(),
    ) {
    }

    public function removeOldLogs(): void
    {
        $entryLifetimeSeconds = $this->systemConfigService->getInt('core.webhook.entryLifetimeSeconds');

        if ($entryLifetimeSeconds === -1) {
            return;
        }

        $deleteBefore = $this->clock
            ->now()
            ->modify("- $entryLifetimeSeconds seconds")
            ->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        do {
            $deleted = $this->connection->executeStatement(
                'DELETE FROM `webhook_event_log` WHERE `created_at` < :before AND (`delivery_status` = :success OR `delivery_status` = :failed) LIMIT :limit',
                [
                    'before' => $deleteBefore,
                    'success' => WebhookEventLogDefinition::STATUS_SUCCESS,
                    'failed' => WebhookEventLogDefinition::STATUS_FAILED,
                    'limit' => self::BATCH_SIZE,
                ],
                [
                    'limit' => \Doctrine\DBAL\Types\Types::INTEGER,
                ]
            );
        } while ($deleted === self::BATCH_SIZE);
    }
}
