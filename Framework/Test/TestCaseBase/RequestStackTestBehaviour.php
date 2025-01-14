<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\TestCaseBase;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

trait RequestStackTestBehaviour
{
    /**
     * @return array<Request>
     */
    #[Before]
    #[After]
    public function clearRequestStack(): array
    {
        $stack = static::getContainer()
            ->get(RequestStack::class);

        $requests = [];

        while ($stack->getMainRequest()) {
            if ($request = $stack->pop()) {
                $requests[] = $request;
            }
        }

        return $requests;
    }

    #[After]
    public function resetRequestContext(): void
    {
        $router = static::getContainer()
            ->get('router');

        $context = $router->getContext();

        $router->setContext($context->fromRequest(Request::create((string) EnvironmentHelper::getVariable('APP_URL'))));
    }

    abstract protected static function getContainer(): ContainerInterface;
}
