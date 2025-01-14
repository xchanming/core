<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Rule;

use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Rule;
use Cicada\Core\Framework\Rule\RuleComparison;
use Cicada\Core\Framework\Rule\RuleConstraints;
use Cicada\Core\Framework\Rule\RuleScope;

#[Package('services-settings')]
class LineItemPropertyRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemProperty';

    /**
     * @param list<string> $identifiers
     *
     * @internal
     */
    public function __construct(
        protected array $identifiers = [],
        protected string $operator = self::OPERATOR_EQ
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->lineItemMatch($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->lineItemMatch($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'identifiers' => RuleConstraints::uuids(),
            'operator' => RuleConstraints::uuidOperators(false),
        ];
    }

    private function lineItemMatch(LineItem $lineItem): bool
    {
        $properties = $lineItem->getPayloadValue('propertyIds') ?? [];
        $options = $lineItem->getPayloadValue('optionIds') ?? [];

        /** @var list<string> $ids */
        $ids = array_merge($properties, $options);

        return RuleComparison::uuids($ids, $this->identifiers, $this->operator);
    }
}
