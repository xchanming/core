<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DependencyInjection;

use Cicada\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

#[Package('core')]
class FrameworkExtension extends Extension
{
    private const ALIAS = 'cicada';

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $this->addCicadaConfig($container, $this->getAlias(), $config);
    }

    private function addCicadaConfig(ContainerBuilder $container, string $alias, array $options): void
    {
        foreach ($options as $key => $option) {
            $key = $alias . '.' . $key;
            $container->setParameter($key, $option);

            /*
             * The route cache in dev mode checks on each request if its fresh. If you use the following expression
             * `defaults={"auth_required"="%cicada.api.api_browser.auth_required%"}` it also checks if the parameter
             * matches the value in the container. The expression always results in a string, but the value in the
             * container is a boolean. So they never match. To workaround this, we add this as an additional string
             * parameter. So in the dynamic use case you have to use `defaults={"auth_required"="%cicada.api.api_browser.auth_required_str%"}`
             */
            if ($key === 'cicada.api.api_browser.auth_required') {
                $container->setParameter('cicada.api.api_browser.auth_required_str', (string) (int) $option);
            }

            if (\is_array($option)) {
                $this->addCicadaConfig($container, $key, $option);
            }
        }
    }
}
