<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\Migration;

use Cicada\Core\Framework\Migration\MigrationCollection;
use Cicada\Core\Framework\Migration\MigrationCollectionLoader;
use Cicada\Core\Framework\Migration\MigrationSource;
use Cicada\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait MigrationTestBehaviour
{
    #[Before]
    public function addMigrationSources(): void
    {
        $loader = static::getContainer()->get(MigrationCollectionLoader::class);

        $loader->addSource(
            new MigrationSource(
                '_test_migrations_invalid_namespace',
                [__DIR__ . '/_test_migrations_invalid_namespace' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_invalid_namespace']
            )
        );

        $loader->addSource(
            new MigrationSource(
                '_test_migrations_valid',
                [__DIR__ . '/_test_migrations_valid' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid']
            )
        );

        $loader->addSource(
            new MigrationSource(
                '_test_migrations_valid_run_time',
                [__DIR__ . '/_test_migrations_valid_run_time' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid_run_time']
            )
        );

        $loader->addSource(
            new MigrationSource(
                '_test_migrations_valid_run_time_exceptions',
                [__DIR__ . '/_test_migrations_valid_run_time_exceptions' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid_run_time_exceptions']
            )
        );

        $loader->addSource(
            new MigrationSource(
                '_test_trigger_with_trigger_',
                [__DIR__ . '/_test_trigger_with_trigger_' => 'Cicada\Core\Framework\Test\Migration\_test_trigger_with_trigger_']
            )
        );

        static::getContainer()->get(MigrationCollectionLoader::class)->addSource(
            new MigrationSource(
                self::INTEGRATION_IDENTIFIER(),
                [__DIR__ . '/_test_migrations_valid' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid']
            )
        );

        static::getContainer()->get(MigrationCollectionLoader::class)->addSource(
            new MigrationSource(
                self::INTEGRATION_WITH_EXCEPTION_IDENTIFIER(),
                [
                    __DIR__ . '/_test_migrations_valid' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid',
                    __DIR__ . '/_test_migrations_valid_run_time_exceptions' => 'Cicada\Core\Framework\Test\Migration\_test_migrations_valid_run_time_exceptions',
                ]
            )
        );
    }

    #[After]
    public function removeMigrationSources(): void
    {
        $loader = static::getContainer()->get(MigrationCollectionLoader::class);
        $prop = ReflectionHelper::getProperty(MigrationCollectionLoader::class, 'migrationSources');
        $migrationSources = $prop->getValue($loader);
        unset($migrationSources['_test_migrations_invalid_namespace']);
        unset($migrationSources['_test_migrations_valid']);
        unset($migrationSources['_test_migrations_valid_run_time']);
        unset($migrationSources['_test_migrations_valid_run_time_exceptions']);
        unset($migrationSources['_test_trigger_with_trigger_']);
        unset($migrationSources[self::INTEGRATION_IDENTIFIER()]);
        unset($migrationSources[self::INTEGRATION_WITH_EXCEPTION_IDENTIFIER()]);
        $prop->setValue($loader, $migrationSources);
    }

    protected static function INTEGRATION_IDENTIFIER(): string
    {
        return 'integration';
    }

    protected static function INTEGRATION_WITH_EXCEPTION_IDENTIFIER(): string
    {
        return 'integration_with_exception';
    }

    protected function getMigrationCollection(string $name): MigrationCollection
    {
        return static::getContainer()->get(MigrationCollectionLoader::class)->collect($name);
    }

    protected function assertMigrationState(MigrationCollection $migrationCollection, int $expectedCount, ?int $updateUntil = null, ?int $destructiveUntil = null): void
    {
        $connection = static::getContainer()->get(Connection::class);

        /** @var MigrationSource $migrationSource */
        $migrationSource = ReflectionHelper::getPropertyValue($migrationCollection, 'migrationSource');

        $dbMigrations = $connection
            ->fetchAllAssociative(
                'SELECT * FROM `migration` WHERE `class` REGEXP :pattern ORDER BY `creation_timestamp`',
                ['pattern' => $migrationSource->getNamespacePattern()]
            );

        TestCase::assertCount($expectedCount, $dbMigrations);

        $assertState = static function (array $dbMigrations, $until, $key): void {
            foreach ($dbMigrations as $migration) {
                if ($migration['creation_timestamp'] <= $until && $migration[$key] === null) {
                    TestCase::fail('Too few migrations have "' . $key . '"' . print_r($dbMigrations, true));
                }

                if ($migration['creation_timestamp'] > $until && $migration[$key] !== null) {
                    TestCase::fail('Too many migrations have "' . $key . '"' . print_r($dbMigrations, true));
                }
            }
        };

        $assertState($dbMigrations, $updateUntil, 'update');
        $assertState($dbMigrations, $destructiveUntil, 'update_destructive');
    }

    abstract protected static function getContainer(): ContainerInterface;
}
