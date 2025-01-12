<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DependencyInjection\CompilerPass;

use Cicada\Core\Framework\Adapter\Asset\AssetPackageService;
use Cicada\Core\Framework\Bundle;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

#[Package('core')]
class AssetBundleRegistrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array<class-string<Bundle>> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        $assetService = $container->getDefinition('assets.packages');
        $assetService->setFactory([AssetPackageService::class, 'create']);

        $bundleMap = [];

        foreach ($bundles as $bundleClass) {
            $reflection = new \ReflectionClass($bundleClass);
            $bundle = $reflection->newInstanceWithoutConstructor();

            if ($bundle instanceof Bundle) {
                $bundleMap[$bundle->getName()] = $bundle->getPath();
            }
        }

        $arguments = $assetService->getArguments();
        array_unshift($arguments, new Reference('cicada.asset.asset.version_strategy'));
        array_unshift($arguments, new Reference('cicada.asset.asset_without_versioning'));
        array_unshift($arguments, $bundleMap);

        $assetService->setArguments($arguments);
    }
}
