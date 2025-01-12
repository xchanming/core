<?php declare(strict_types=1);

namespace Cicada\Core\Content\Product\SearchKeyword;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\QueryBuilder;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Connection;

#[Package('buyers-experience')]
class KeywordLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param array<array{normal: list<string>, reversed: list<string>}> $tokenSlops
     *
     * @return list<list<string>>
     */
    public function fetch(array $tokenSlops, Context $context): array
    {
        $query = new QueryBuilder($this->connection);
        $query->select('keyword');
        $query->from('product_keyword_dictionary');

        $query->setTitle('search::detect-keywords');

        $counter = 0;
        $wheres = [];
        $index = 0;

        foreach ($tokenSlops as $slops) {
            $slopsWheres = [];
            foreach ($slops['normal'] as $slop) {
                ++$counter;
                $slopsWheres[] = 'keyword LIKE :reg' . $counter;
                $query->setParameter('reg' . $counter, $slop);
            }
            foreach ($slops['reversed'] as $slop) {
                ++$counter;
                $slopsWheres[] = 'reversed LIKE :reg' . $counter;
                $query->setParameter('reg' . $counter, $slop);
            }
            $query->addSelect('IF (' . implode(' OR ', $slopsWheres) . ', 1, 0) as \'' . $index++ . '\'');
            $wheres = array_merge($wheres, $slopsWheres);
        }

        $query->andWhere('language_id = :language');
        $query->andWhere('(' . implode(' OR ', $wheres) . ')');
        $query->addOrderBy('keyword', 'ASC');

        $query->setParameter('language', Uuid::fromHexToBytes($context->getLanguageId()));

        return $query->executeQuery()->fetchAllNumeric();
    }
}
