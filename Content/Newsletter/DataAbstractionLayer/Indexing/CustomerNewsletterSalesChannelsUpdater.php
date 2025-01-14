<?php declare(strict_types=1);

namespace Cicada\Core\Content\Newsletter\DataAbstractionLayer\Indexing;

use Cicada\Core\Content\Newsletter\SalesChannel\NewsletterSubscribeRoute;
use Cicada\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

#[Package('buyers-experience')]
class CustomerNewsletterSalesChannelsUpdater
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @param array<string> $ids
     */
    public function update(array $ids, bool $reverseUpdate = false): void
    {
        if (empty($ids)) {
            return;
        }

        $ids = array_unique($ids);

        $tableTemplate = <<<'SQL'
UPDATE `customer`, `newsletter_recipient` SET `customer`.`newsletter_sales_channel_ids` = (
    SELECT CONCAT(
        '{',
        GROUP_CONCAT(
            CONCAT(
                JSON_QUOTE(LOWER(HEX(`newsletter_recipient`.`id`))),
                ':',
                JSON_QUOTE(LOWER(HEX(`newsletter_recipient`.`sales_channel_id`)))
            )
        ),
        '}'
    )
    FROM `newsletter_recipient`
    WHERE `newsletter_recipient`.`email` = `customer`.`email`
    AND `newsletter_recipient`.`status` IN (:states)
)
WHERE `newsletter_recipient`.`email` = `customer`.`email`
AND #table#.`id` IN (:ids)
SQL;

        $resetTemplate = <<<'SQL'
UPDATE `customer`
LEFT JOIN `newsletter_recipient` ON `newsletter_recipient`.`email` = `customer`.`email`
SET `customer`.`newsletter_sales_channel_ids` = NULL
WHERE #table#.`id` IN (:ids)
SQL;

        $parameters = [
            'ids' => Uuid::fromHexToBytesList($ids),
            'states' => [NewsletterSubscribeRoute::STATUS_DIRECT, NewsletterSubscribeRoute::STATUS_OPT_IN],
        ];

        $replacement = [
            '#table#' => $reverseUpdate ? '`customer`' : '`newsletter_recipient`',
        ];

        $sql = str_replace(
            array_keys($replacement),
            array_values($replacement),
            $tableTemplate
        );

        $resetSql = str_replace(
            array_keys($replacement),
            array_values($replacement),
            $resetTemplate
        );

        RetryableQuery::retryable($this->connection, function () use ($resetSql, $parameters): void {
            $this->connection->executeStatement(
                $resetSql,
                $parameters,
                ['ids' => ArrayParameterType::BINARY]
            );
        });

        RetryableQuery::retryable($this->connection, function () use ($sql, $parameters): void {
            $this->connection->executeStatement(
                $sql,
                $parameters,
                ['ids' => ArrayParameterType::BINARY, 'states' => ArrayParameterType::STRING]
            );
        });
    }

    /**
     * @param array<string> $ids
     */
    public function delete(array $ids): void
    {
        $sqlTemplate = <<<'SQL'
SELECT `customer`.`id`
FROM `customer`
WHERE #expressions#
SQL;

        $expressions = [];
        foreach ($ids as $id) {
            $expressions[] = 'JSON_EXTRACT(`customer`.`newsletter_sales_channel_ids`, \'$."' . $id . '"\') IS NOT NULL';
        }

        $replacement = [
            '#expressions#' => implode(' OR ', $expressions),
        ];

        $sql = str_replace(
            array_keys($replacement),
            array_values($replacement),
            $sqlTemplate
        );

        $customerIds = RetryableQuery::retryable($this->connection, fn (): array => $this->connection->fetchFirstColumn($sql));

        if (empty($customerIds)) {
            return;
        }

        $this->update(Uuid::fromBytesToHexList($customerIds), true);
    }

    /**
     * @param array<string> $ids
     */
    public function updateCustomersRecipient(array $ids): void
    {
        $ids = array_unique($ids);

        $customers = $this->connection->fetchAllAssociative(
            'SELECT newsletter_sales_channel_ids, email, name FROM customer WHERE id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $parameters = [];

        foreach ($customers as $customer) {
            if (!$customer['newsletter_sales_channel_ids']) {
                continue;
            }

            $newsletterIds = array_keys(json_decode((string) $customer['newsletter_sales_channel_ids'], true, 512, \JSON_THROW_ON_ERROR));
            $newsletterIds = array_map('\strval', $newsletterIds);

            $parameters[] = [
                'newsletter_ids' => $newsletterIds,
                'email' => $customer['email'],
                'name' => $customer['name'],
            ];
        }

        if (empty($parameters)) {
            return;
        }

        foreach ($parameters as $parameter) {
            RetryableQuery::retryable($this->connection, function () use ($parameter): void {
                $this->connection->executeStatement(
                    'UPDATE newsletter_recipient SET email = (:email), name = (:name) WHERE id IN (:ids)',
                    [
                        'ids' => Uuid::fromHexToBytesList($parameter['newsletter_ids']),
                        'email' => $parameter['email'],
                        'name' => $parameter['name'],
                    ],
                    ['ids' => ArrayParameterType::BINARY],
                );
            });
        }
    }
}
