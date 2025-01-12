<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\Stock;

use Cicada\Core\Content\Product\Events\ProductNoLongerAvailableEvent;
use Cicada\Core\Content\Product\Events\ProductStockAlteredEvent;
use Cicada\Core\Defaults;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('core')]
class StockStorage extends AbstractStockStorage
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function getDecorated(): AbstractStockStorage
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(StockLoadRequest $stockRequest, SalesChannelContext $context): StockDataCollection
    {
        return new StockDataCollection([]);
    }

    /**
     * @param list<StockAlteration> $changes
     */
    public function alter(array $changes, Context $context): void
    {
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        if (\count($changes) === 0) {
            return;
        }

        $sql = <<<'SQL'
            UPDATE product
            SET stock = stock + :quantity, sales = sales - :quantity, available_stock = stock
            WHERE id = :id AND version_id = :version
        SQL;

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare($sql)
        );

        foreach ($changes as $alteration) {
            $query->execute([
                'quantity' => $alteration->quantityDelta(),
                'id' => Uuid::fromHexToBytes($alteration->productId),
                'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            ]);
        }

        $this->updateAvailableFlag(array_column($changes, 'productId'), $context);

        $this->dispatcher->dispatch(new ProductStockAlteredEvent(array_column($changes, 'productId'), $context));
    }

    /**
     * @param list<string> $productIds
     */
    public function index(array $productIds, Context $context): void
    {
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        $this->updateAvailableFlag($productIds, $context);
    }

    /**
     * @param list<string> $ids
     */
    private function updateAvailableFlag(array $ids, Context $context): void
    {
        $ids = array_filter(array_unique($ids));

        if (empty($ids)) {
            return;
        }

        $bytes = Uuid::fromHexToBytesList($ids);

        $sql = '
            UPDATE product
            LEFT JOIN product parent
                ON parent.id = product.parent_id
                AND parent.version_id = product.version_id

            SET product.available = IFNULL((
                COALESCE(product.is_closeout, parent.is_closeout, 0) * product.stock
                >=
                COALESCE(product.is_closeout, parent.is_closeout, 0) * IFNULL(product.min_purchase, parent.min_purchase)
            ), 0)
            WHERE product.id IN (:ids)
            AND product.version_id = :version
        ';

        $before = $this->connection->fetchAllKeyValue(
            'SELECT LOWER(HEX(id)), available FROM product WHERE id IN (:ids) AND product.version_id = :version',
            ['ids' => $bytes, 'version' => Uuid::fromHexToBytes($context->getVersionId())],
            ['ids' => ArrayParameterType::BINARY]
        );

        RetryableQuery::retryable($this->connection, function () use ($sql, $context, $bytes): void {
            $this->connection->executeStatement(
                $sql,
                ['ids' => $bytes, 'version' => Uuid::fromHexToBytes($context->getVersionId())],
                ['ids' => ArrayParameterType::BINARY]
            );
        });

        $after = $this->connection->fetchAllKeyValue(
            'SELECT LOWER(HEX(id)), available FROM product WHERE id IN (:ids) AND product.version_id = :version',
            ['ids' => $bytes, 'version' => Uuid::fromHexToBytes($context->getVersionId())],
            ['ids' => ArrayParameterType::BINARY]
        );

        $updated = [];
        foreach ($before as $id => $available) {
            if ($available !== $after[$id]) {
                $updated[] = (string) $id;
            }
        }

        if (!empty($updated)) {
            $this->dispatcher->dispatch(new ProductNoLongerAvailableEvent($updated, $context));
        }
    }
}
