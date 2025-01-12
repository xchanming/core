<?php declare(strict_types=1);

namespace Cicada\Core\Framework\App\ActionButton\Response;

use Cicada\Core\Framework\App\ActionButton\AppAction;
use Cicada\Core\Framework\App\AppException;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('core')]
class ActionButtonResponseFactory
{
    /**
     * @param ActionButtonResponseFactoryInterface[] $factories
     */
    public function __construct(private readonly iterable $factories)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createFromResponse(AppAction $action, string $actionType, array $payload, Context $context): ActionButtonResponse
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($actionType)) {
                return $factory->create($action, $payload, $context);
            }
        }

        throw AppException::actionButtonProcessException($action->getActionId(), \sprintf('No factory found for action type "%s"', $actionType));
    }
}
