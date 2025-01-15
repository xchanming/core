<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Search\Term;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Field;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Cicada\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Cicada\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\StringField;
use Cicada\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Cicada\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Query\ScoreQuery;
use Cicada\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('core')]
class EntityScoreQueryBuilder
{
    /**
     * @return ScoreQuery[]
     */
    public function buildScoreQueries(
        SearchPattern $term,
        EntityDefinition $definition,
        string $root,
        Context $context,
        float $multiplier = 1.0
    ): array {
        static $counter = 0;
        ++$counter;

        $fields = $this->getQueryFields($definition, $context);

        $queries = [];
        foreach ($fields as $field) {
            $flag = $field->getFlag(SearchRanking::class);

            $ranking = $multiplier;
            if ($flag) {
                $ranking = $flag->getRanking() * $multiplier;
            }

            if ($field instanceof DateTimeField) {
                if (!$this->validateDateFormat(Defaults::STORAGE_DATE_FORMAT, $term->getOriginal()->getTerm())) {
                    continue;
                }
            }

            $select = $root . '.' . $field->getPropertyName();

            if ($field instanceof ManyToManyAssociationField) {
                $queries = array_merge(
                    $queries,
                    $this->buildScoreQueries($term, $field->getToManyReferenceDefinition(), $select, $context, $ranking)
                );

                continue;
            }

            if ($field instanceof AssociationField) {
                $queries = array_merge(
                    $queries,
                    $this->buildScoreQueries($term, $field->getReferenceDefinition(), $select, $context, $ranking)
                );

                continue;
            }

            $queries[] = new ScoreQuery(
                new EqualsFilter($select, $term->getOriginal()->getTerm()),
                $ranking * $term->getOriginal()->getScore()
            );

            $queries[] = new ScoreQuery(
                new ContainsFilter($select, $term->getOriginal()->getTerm()),
                $ranking * $term->getOriginal()->getScore() * 0.5
            );

            if ($flag && !$flag->tokenize()) {
                continue;
            }

            foreach ($term->getTerms() as $part) {
                $queries[] = new ScoreQuery(
                    new EqualsFilter($select, $part->getTerm()),
                    $ranking * $part->getScore()
                );

                $queries[] = new ScoreQuery(
                    new ContainsFilter($select, $part->getTerm()),
                    $ranking * $part->getScore() * 0.5
                );
            }
        }

        return $queries;
    }

    private function getQueryFields(EntityDefinition $definition, Context $context): FieldCollection
    {
        $fields = $definition->getFields()->filterByFlag(SearchRanking::class);

        // exclude read protected fields which are not allowed for the current scope
        $fields = $fields->filter(function (Field $field) use ($context) {
            $flag = $field->getFlag(ApiAware::class);
            if ($flag === null) {
                return false;
            }

            return $flag->isSourceAllowed($context->getSource()::class);
        });

        if ($fields->count() > 0) {
            return $fields;
        }

        $fields = $definition->getFields()->filterInstance(TranslatedField::class);
        if ($fields->count() > 0) {
            return $fields;
        }

        return $definition->getFields()->filterInstance(StringField::class);
    }

    private function validateDateFormat(string $format, string $date): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }
}
