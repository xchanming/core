<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\Sync;

use Cicada\Core\Framework\Api\Exception\InvalidSyncOperationException;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Doctrine\DBAL\ConnectionException;

#[Package('core')]
interface SyncServiceInterface
{
    /**
     * @param list<SyncOperation> $operations
     *
     * @throws ConnectionException
     * @throws InvalidSyncOperationException
     */
    public function sync(array $operations, Context $context, SyncBehavior $behavior): SyncResult;
}
