<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Rule\Container;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\RuleScope;

/**
 * XorRule returns true, if exactly one child rule is true
 */
#[Package('services-settings')]
class XorRule extends Container
{
    final public const RULE_NAME = 'xorContainer';

    public function match(RuleScope $scope): bool
    {
        $matches = 0;

        foreach ($this->rules as $rule) {
            $match = $rule->match($scope);
            if (!$match) {
                continue;
            }
            ++$matches;
        }

        return $matches === 1;
    }
}
