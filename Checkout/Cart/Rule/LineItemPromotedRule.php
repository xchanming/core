<?php declare(strict_types=1);

namespace Cicada\Core\Checkout\Cart\Rule;

use Cicada\Core\Checkout\Cart\LineItem\LineItem;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Rule;
use Cicada\Core\Framework\Rule\RuleConfig;
use Cicada\Core\Framework\Rule\RuleConstraints;
use Cicada\Core\Framework\Rule\RuleScope;

#[Package('services-settings')]
class LineItemPromotedRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemPromoted';

    /**
     * @internal
     */
    public function __construct(protected bool $isPromoted = false)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->isItemMatching($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->isItemMatching($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'isPromoted' => RuleConstraints::bool(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('isPromoted');
    }

    private function isItemMatching(LineItem $lineItem): bool
    {
        return (bool) $lineItem->getPayloadValue('markAsTopseller') === $this->isPromoted;
    }
}
