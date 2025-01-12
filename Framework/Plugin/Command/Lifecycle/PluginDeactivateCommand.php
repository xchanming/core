<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Plugin\Command\Lifecycle;

use Cicada\Core\Framework\Adapter\Console\CicadaStyle;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\PluginNotActivatedException;
use Cicada\Core\Framework\Plugin\Exception\PluginNotInstalledException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'plugin:deactivate',
    description: 'Deactivates a plugin',
)]
#[Package('core')]
class PluginDeactivateCommand extends AbstractPluginLifecycleCommand
{
    private const LIFECYCLE_METHOD = 'deactivate';

    protected function configure(): void
    {
        $this->configureCommand(self::LIFECYCLE_METHOD);
    }

    /**
     * {@inheritdoc}
     *
     * @throws PluginNotInstalledException
     * @throws PluginNotActivatedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CicadaStyle($input, $output);
        $context = Context::createCLIContext();
        $plugins = $this->prepareExecution(self::LIFECYCLE_METHOD, $io, $input, $context);

        if ($plugins === null) {
            return self::SUCCESS;
        }

        $deactivatedPluginCount = 0;
        foreach ($plugins as $plugin) {
            if ($plugin->getInstalledAt() === null) {
                $io->note(\sprintf('Plugin "%s" must be installed. Skipping.', $plugin->getName()));

                continue;
            }

            if ($plugin->getActive() === false) {
                $io->note(\sprintf('Plugin "%s" must be activated. Skipping.', $plugin->getName()));

                continue;
            }

            $this->pluginLifecycleService->deactivatePlugin($plugin, $context);
            ++$deactivatedPluginCount;

            $io->text(\sprintf('Plugin "%s" has been deactivated successfully.', $plugin->getName()));
        }

        if ($deactivatedPluginCount !== 0) {
            $io->success(\sprintf('Deactivated %d plugin(s).', $deactivatedPluginCount));
        }

        $this->handleClearCacheOption($input, $io, 'deactivating');

        return self::SUCCESS;
    }
}
