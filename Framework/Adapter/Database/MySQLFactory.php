<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Adapter\Database;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Log\Package;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\DriverManager;

/**
 * @internal
 */
#[Package('core')]
class MySQLFactory
{
    /**
     * @param array<Middleware> $middlewares
     */
    public static function create(array $middlewares = []): Connection
    {
        $config = (new Configuration())
            ->setMiddlewares($middlewares);

        $url = (string) EnvironmentHelper::getVariable('DATABASE_URL', getenv('DATABASE_URL'));
        if ($url === '') {
            $url = 'mysql://root:cicada@127.0.0.1:3306/cicada';
        }

        $replicaUrl = (string) EnvironmentHelper::getVariable('DATABASE_REPLICA_0_URL');

        $parameters = [
            'url' => $url,
            'charset' => 'utf8mb4',
            'driverOptions' => [
                \PDO::ATTR_STRINGIFY_FETCHES => true,
                \PDO::ATTR_TIMEOUT => 5, // 5s connection timeout
            ],
        ];

        if ($sslCa = EnvironmentHelper::getVariable('DATABASE_SSL_CA')) {
            $parameters['driverOptions'][\PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        }

        if ($sslCert = EnvironmentHelper::getVariable('DATABASE_SSL_CERT')) {
            $parameters['driverOptions'][\PDO::MYSQL_ATTR_SSL_CERT] = $sslCert;
        }

        if ($sslCertKey = EnvironmentHelper::getVariable('DATABASE_SSL_KEY')) {
            $parameters['driverOptions'][\PDO::MYSQL_ATTR_SSL_KEY] = $sslCertKey;
        }

        if (EnvironmentHelper::getVariable('DATABASE_SSL_DONT_VERIFY_SERVER_CERT')) {
            $parameters['driverOptions'][\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        if (EnvironmentHelper::getVariable('DATABASE_PERSISTENT_CONNECTION')) {
            $parameters['driverOptions'][\PDO::ATTR_PERSISTENT] = true;
        }

        if (EnvironmentHelper::getVariable('DATABASE_PROTOCOL_COMPRESSION')) {
            $parameters['driverOptions'][\PDO::MYSQL_ATTR_COMPRESS] = true;
        }

        if ($replicaUrl) {
            $parameters['wrapperClass'] = PrimaryReadReplicaConnection::class;
            $parameters['primary'] = ['url' => $url, 'driverOptions' => $parameters['driverOptions']];
            $parameters['replica'] = [];

            for ($i = 0; $replicaUrl = (string) EnvironmentHelper::getVariable('DATABASE_REPLICA_' . $i . '_URL'); ++$i) {
                $parameters['replica'][] = ['url' => $replicaUrl, 'charset' => $parameters['charset'], 'driverOptions' => $parameters['driverOptions']];
            }
        }

        return DriverManager::getConnection($parameters, $config);
    }
}
