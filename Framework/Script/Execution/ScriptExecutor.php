<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Script\Execution;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Adapter\Twig\Extension\PcreExtension;
use Cicada\Core\Framework\Adapter\Twig\Extension\PhpSyntaxExtension;
use Cicada\Core\Framework\Adapter\Twig\Filter\ReplaceRecursiveFilter;
use Cicada\Core\Framework\Adapter\Twig\SecurityExtension;
use Cicada\Core\Framework\Adapter\Twig\TwigEnvironment;
use Cicada\Core\Framework\App\Event\Hooks\AppLifecycleHook;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Script\Debugging\Debug;
use Cicada\Core\Framework\Script\Debugging\ScriptTraces;
use Cicada\Core\Framework\Script\Execution\Awareness\AppSpecificHook;
use Cicada\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use Cicada\Core\Framework\Script\Execution\Awareness\StoppableHook;
use Cicada\Core\Framework\Script\ScriptException;
use Cicada\Core\Framework\Script\ServiceStubs;
use Cicada\Core\Framework\Struct\ArrayStruct;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Twig\Environment;
use Twig\Extension\DebugExtension;

#[Package('core')]
class ScriptExecutor
{
    public static bool $isInScriptExecutionContext = false;

    /**
     * @internal
     */
    public function __construct(
        private readonly ScriptLoader $loader,
        private readonly ScriptTraces $traces,
        private readonly ContainerInterface $container,
        private readonly TranslationExtension $translationExtension,
        private readonly string $cicadaVersion,
    ) {
    }

    public function execute(Hook $hook): void
    {
        // @deprecated tag:v6.7.0 - remove if condition
        if (EnvironmentHelper::getVariable('DISABLE_EXTENSIONS', false)) {
            return;
        }

        if ($hook instanceof InterfaceHook) {
            throw ScriptException::interfaceHookExecutionNotAllowed($hook::class);
        }

        $scripts = $this->loader->get($hook->getName());
        $this->traces->initHook($hook);

        foreach ($scripts as $script) {
            $scriptAppInfo = $script->getScriptAppInformation();
            if ($scriptAppInfo && $hook instanceof AppSpecificHook && $hook->getAppId() !== $scriptAppInfo->getAppId()) {
                // only execute scripts from the app the hook specifies
                continue;
            }

            if (!$hook instanceof AppLifecycleHook && !$script->isActive()) {
                continue;
            }

            try {
                static::$isInScriptExecutionContext = true;
                $this->render($hook, $script);
            } catch (\Throwable $e) {
                throw ScriptException::scriptExecutionFailed($hook->getName(), $script->getName(), $e);
            } finally {
                static::$isInScriptExecutionContext = false;
            }

            if ($hook instanceof StoppableHook && $hook->isPropagationStopped()) {
                break;
            }
        }
    }

    private function render(Hook $hook, Script $script): void
    {
        $twig = $this->initEnv($script);

        $services = $this->initServices($hook, $script);

        $twig->addGlobal('services', $services);

        $this->traces->trace($hook, $script, function (Debug $debug) use ($twig, $script, $hook): void {
            $twig->addGlobal('debug', $debug);

            if ($hook instanceof DeprecatedHook) {
                ScriptTraces::addDeprecationNotice($hook->getDeprecationNotice());
            }

            $template = $twig->load($script->getName());

            if (!$hook instanceof FunctionHook) {
                $template->render(['hook' => $hook]);

                return;
            }

            $blockName = $hook->getFunctionName();
            if ($template->hasBlock($blockName)) {
                $template->renderBlock($blockName, ['hook' => $hook]);

                return;
            }

            if (!$hook instanceof OptionalFunctionHook) {
                throw ScriptException::requiredFunctionMissingInInterfaceHook($hook->getFunctionName(), $script->getName());
            }

            $requiredFromVersion = $hook->willBeRequiredInVersion();
            if ($requiredFromVersion) {
                ScriptTraces::addDeprecationNotice(\sprintf(
                    'Function "%s" will be required from %s onward, but is not implemented in script "%s", please make sure you add the block in your script.',
                    $hook->getFunctionName(),
                    $requiredFromVersion,
                    $script->getName()
                ));
            }
        });

        $this->callAfter($services, $hook, $script);
    }

    private function initEnv(Script $script): Environment
    {
        $twig = new TwigEnvironment(
            new ScriptTwigLoader($script),
            $script->getTwigOptions()
        );

        $twig->addExtension(new PhpSyntaxExtension());
        $twig->addExtension($this->translationExtension);
        $twig->addExtension(new SecurityExtension([]));
        $twig->addExtension(new PcreExtension());
        $twig->addExtension(new ReplaceRecursiveFilter());

        if ($script->getTwigOptions()['debug'] ?? false) {
            $twig->addExtension(new DebugExtension());
        }

        $twig->addGlobal('cicada', new ArrayStruct([
            'version' => $this->cicadaVersion,
        ]));

        return $twig;
    }

    private function initServices(Hook $hook, Script $script): ServiceStubs
    {
        $services = new ServiceStubs($hook->getName());
        $deprecatedServices = $hook->getDeprecatedServices();
        foreach ($hook->getServiceIds() as $serviceId) {
            if (!$this->container->has($serviceId)) {
                throw new ServiceNotFoundException($serviceId, 'Hook: ' . $hook->getName());
            }

            $service = $this->container->get($serviceId);
            if (!$service instanceof HookServiceFactory) {
                throw ScriptException::noHookServiceFactory($serviceId);
            }

            $services->add($service->getName(), $service->factory($hook, $script), $deprecatedServices[$serviceId] ?? null);
        }

        return $services;
    }

    private function callAfter(ServiceStubs $services, Hook $hook, Script $script): void
    {
        foreach ($hook->getServiceIds() as $serviceId) {
            if (!$this->container->has($serviceId)) {
                throw new ServiceNotFoundException($serviceId, 'Hook: ' . $hook->getName());
            }

            $factory = $this->container->get($serviceId);
            if (!$factory instanceof HookServiceFactory) {
                throw ScriptException::noHookServiceFactory($serviceId);
            }

            $service = $services->get($factory->getName());

            $factory->after($service, $hook, $script);
        }
    }
}
