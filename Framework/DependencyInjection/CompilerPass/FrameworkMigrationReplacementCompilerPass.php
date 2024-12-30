<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DependencyInjection\CompilerPass;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Migration\MigrationSource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('core')]
class FrameworkMigrationReplacementCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $bundleRoot = \dirname(__DIR__, 3);

        $migrationSourceV3 = $container->getDefinition(MigrationSource::class . '.core.V6_3');
        $migrationSourceV3->addMethodCall('addDirectory', [$bundleRoot . '/Migration/V6_3', 'Cicada\Core\Migration\V6_3']);

        $migrationSourceV4 = $container->getDefinition(MigrationSource::class . '.core.V6_4');
        $migrationSourceV4->addMethodCall('addDirectory', [$bundleRoot . '/Migration/V6_4', 'Cicada\Core\Migration\V6_4']);

        $migrationSourceV5 = $container->getDefinition(MigrationSource::class . '.core.V6_5');
        $migrationSourceV5->addMethodCall('addDirectory', [$bundleRoot . '/Migration/V6_5', 'Cicada\Core\Migration\V6_5']);

        $migrationSourceV6 = $container->getDefinition(MigrationSource::class . '.core.V6_6');
        $migrationSourceV6->addMethodCall('addDirectory', [$bundleRoot . '/Migration/V6_6', 'Cicada\Core\Migration\V6_6']);

        $migrationSourceV6 = $container->getDefinition(MigrationSource::class . '.core.V6_7');
        $migrationSourceV6->addMethodCall('addDirectory', [$bundleRoot . '/Migration/V6_7', 'Cicada\Core\Migration\V6_7']);
    }
}
