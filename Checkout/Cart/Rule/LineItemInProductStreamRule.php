<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Rule;

use Cicada\Core\Checkout\Cart\CartException;
use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Content\ProductStream\ProductStreamDefinition;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use Cicada\Core\Framework\Rule\Rule;
use Cicada\Core\Framework\Rule\RuleComparison;
use Cicada\Core\Framework\Rule\RuleConfig;
use Cicada\Core\Framework\Rule\RuleConstraints;
use Cicada\Core\Framework\Rule\RuleScope;

#[Package('services-settings')]
class LineItemInProductStreamRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemInProductStream';

    /**
     * @internal
     *
     * @param list<string> $streamIds
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected array $streamIds = []
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->matchesOneOfProductStream($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->matchesOneOfProductStream($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::uuidOperators(),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['streamIds'] = RuleConstraints::uuids();

        return $constraints;
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, true, true)
            ->entitySelectField('streamIds', ProductStreamDefinition::ENTITY_NAME, true);
    }

    /**
     * @throws UnsupportedOperatorException
     * @throws CartException
     */
    private function matchesOneOfProductStream(LineItem $lineItem): bool
    {
        return RuleComparison::uuids($lineItem->getPayloadValue('streamIds'), $this->streamIds, $this->operator);
    }
}
