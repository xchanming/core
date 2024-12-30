<?php declare(strict_types=1);

namespace Cicada\Core\Framework\MessageQueue\Command;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use Cicada\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'scheduled-task:list',
    description: 'List all scheduled tasks',
)]
#[Package('core')]
class ListScheduledTaskCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(private readonly TaskRegistry $taskRegistry)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entities = $this->taskRegistry->getAllTasks(Context::createCLIContext());

        $table = new Table($output);
        $table->setHeaders(['Name', 'Next execution', 'Last execution', 'Run interval', 'Status']);

        /** @var ScheduledTaskEntity $entity */
        foreach ($entities as $entity) {
            $table->addRow([
                $entity->getName(),
                $entity->getNextExecutionTime()->format(\DATE_ATOM),
                $entity->getLastExecutionTime() ? $entity->getLastExecutionTime()->format(\DATE_ATOM) : '-',
                $entity->getRunInterval(),
                $entity->getStatus(),
            ]);
        }
        $table->render();

        return self::SUCCESS;
    }
}
