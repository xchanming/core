<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Customer\Service;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

#[Package('checkout')]
/**
 * @final
 */
class ProductReviewCountService
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @param list<string> $reviewIds
     */
    public function updateReviewCount(array $reviewIds): void
    {
        /** @var list<string> $affectedCustomers */
        $affectedCustomers = array_filter($this->connection->fetchFirstColumn(
            'SELECT DISTINCT(`customer_id`) FROM product_review WHERE id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($reviewIds)],
            ['ids' => ArrayParameterType::BINARY]
        ));

        foreach ($affectedCustomers as $customerId) {
            $this->updateReviewCountForCustomer($customerId);
        }
    }

    public function updateReviewCountForCustomer(string $customerId): void
    {
        $this->connection->executeStatement(
            'UPDATE `customer` SET review_count = (
                  SELECT COUNT(*) FROM `product_review` WHERE `customer_id` = :id AND `status` = 1
            ) WHERE id = :id',
            ['id' => $customerId]
        );
    }
}
