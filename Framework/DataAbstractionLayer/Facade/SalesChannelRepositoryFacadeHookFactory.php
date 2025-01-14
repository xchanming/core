<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Facade;

use Cicada\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Cicada\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use Cicada\Core\Framework\Script\Execution\Awareness\SalesChannelContextAware;
use Cicada\Core\Framework\Script\Execution\Hook;
use Cicada\Core\Framework\Script\Execution\Script;
use Cicada\Core\System\SalesChannel\Entity\SalesChannelDefinitionInstanceRegistry;

/**
 * @internal
 */
#[Package('core')]
class SalesChannelRepositoryFacadeHookFactory extends HookServiceFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SalesChannelDefinitionInstanceRegistry $registry,
        private readonly RequestCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function factory(Hook $hook, Script $script): SalesChannelRepositoryFacade
    {
        if (!$hook instanceof SalesChannelContextAware) {
            throw DataAbstractionLayerException::hookInjectionException($hook, self::class, SalesChannelContextAware::class);
        }

        return new SalesChannelRepositoryFacade(
            $this->registry,
            $this->criteriaBuilder,
            $hook->getSalesChannelContext()
        );
    }

    public function getName(): string
    {
        return 'store';
    }
}
