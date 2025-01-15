<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Rule\Container;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Rule\Rule;

#[Package('services-settings')]
abstract class FilterRule extends Rule implements ContainerInterface
{
    /**
     * @var Container|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $filter;

    public function addRule(Rule $rule): void
    {
        if ($this->filter === null) {
            $this->filter = new AndRule();
        }

        $this->filter->addRule($rule);
    }

    /**
     * @param list<Rule> $rules
     */
    public function setRules(array $rules): void
    {
        $this->filter = new AndRule($rules);
    }

    /**
     * @return list<Rule>
     */
    public function getRules(): array
    {
        return $this->filter ? $this->filter->getRules() : [];
    }
}
