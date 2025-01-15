<?php declare(strict_types=1);

namespace Cicada\Core\Installer\Database;

use Cicada\Core\Framework\Log\Package;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @internal
 */
#[Package('core')]
class BlueGreenDeploymentService
{
    final public const ENV_NAME = 'BLUE_GREEN_DEPLOYMENT';

    public function setEnvironmentVariable(Connection $connection, SessionInterface $session): void
    {
        $value = $this->checkIfMayCreateTrigger($connection);

        $_SERVER[self::ENV_NAME] = $_ENV[self::ENV_NAME] = $value;
        $session->set(self::ENV_NAME, $value);
    }

    private function checkIfMayCreateTrigger(Connection $connection): bool
    {
        try {
            $connection->executeQuery($this->getCreateTableQuery());
            $connection->executeQuery($this->getTriggerQuery());
        } catch (Exception) {
            return false;
        } finally {
            $connection->executeQuery('DROP TABLE IF EXISTS example');
        }

        return true;
    }

    private function getCreateTableQuery(): string
    {
        return <<<'SQL'
            CREATE TABLE IF NOT EXISTS `example` (
              `id` int NOT NULL
            );
SQL;
    }

    private function getTriggerQuery(): string
    {
        return <<<'SQL'
            CREATE TRIGGER example_trigger BEFORE UPDATE ON `example`
                FOR EACH ROW
                BEGIN
                END;
SQL;
    }
}
