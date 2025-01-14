<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Plugin\Command\Scaffolding\Generator;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Command\Scaffolding\PluginScaffoldConfiguration;
use Cicada\Core\Framework\Plugin\Command\Scaffolding\Stub;
use Cicada\Core\Framework\Plugin\Command\Scaffolding\StubCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[Package('core')]
class PluginClassGenerator implements ScaffoldingGenerator
{
    public function hasCommandOption(): bool
    {
        return false;
    }

    public function getCommandOptionName(): string
    {
        return '';
    }

    public function getCommandOptionDescription(): string
    {
        return '';
    }

    public function addScaffoldConfig(
        PluginScaffoldConfiguration $config,
        InputInterface $input,
        SymfonyStyle $io
    ): void {
    }

    public function generateStubs(
        PluginScaffoldConfiguration $configuration,
        StubCollection $stubCollection
    ): void {
        $stubCollection->add($this->createPluginClass($configuration));
    }

    private function createPluginClass(PluginScaffoldConfiguration $configuration): Stub
    {
        $pluginClassPath = 'src/' . $configuration->name . '.php';

        return Stub::template(
            $pluginClassPath,
            self::STUB_DIRECTORY . '/plugin-class.stub',
            [
                'namespace' => $configuration->namespace,
                'className' => $configuration->name,
            ]
        );
    }
}
