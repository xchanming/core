<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Rule;

use Cicada\Core\Framework\Log\Package;

#[Package('services-settings')]
class SimpleRule extends Rule
{
    final public const RULE_NAME = 'simple';

    /**
     * @internal
     */
    public function __construct(protected bool $match = true)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        return $this->match;
    }

    public function getConstraints(): array
    {
        return [
            'match' => RuleConstraints::bool(true),
        ];
    }
}
