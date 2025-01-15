<?php declare(strict_types=1);

namespace Cicada\Core\Installer\Controller;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Installer\Database\BlueGreenDeploymentService;
use Cicada\Core\Maintenance\System\Exception\DatabaseSetupException;
use Cicada\Core\Maintenance\System\Service\DatabaseConnectionFactory;
use Cicada\Core\Maintenance\System\Service\SetupDatabaseAdapter;
use Cicada\Core\Maintenance\System\Struct\DatabaseConnectionInformation;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[Package('core')]
class DatabaseConfigurationController extends InstallerController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly BlueGreenDeploymentService $blueGreenDeploymentService,
        private readonly SetupDatabaseAdapter $setupDatabaseAdapter,
        private readonly DatabaseConnectionFactory $connectionFactory
    ) {
    }

    #[Route(path: '/installer/database-configuration', name: 'installer.database-configuration', methods: ['POST', 'GET'])]
    public function databaseConfiguration(Request $request): Response
    {
        $session = $request->getSession();
        $connectionInfo = $session->get(DatabaseConnectionInformation::class) ?? new DatabaseConnectionInformation();

        if ($request->isMethod('GET')) {
            return $this->renderInstaller('@Installer/installer/database-configuration.html.twig', [
                'connectionInfo' => $connectionInfo,
                'error' => null,
            ]);
        }

        $connectionInfo = (new DatabaseConnectionInformation())->assign($request->request->all());

        try {
            try {
                // check connection
                $connection = $this->connectionFactory->getConnection($connectionInfo);
            } catch (DriverException $e) {
                // Unknown database https://dev.mysql.com/doc/refman/8.0/en/server-error-reference.html#error_er_bad_db_error
                if ($e->getCode() !== 1049) {
                    throw $e;
                }

                $connection = $this->connectionFactory->getConnection($connectionInfo, true);

                $this->setupDatabaseAdapter->createDatabase($connection, $connectionInfo->getDatabaseName());

                $connection = $this->connectionFactory->getConnection($connectionInfo);
            }

            $session->set(DatabaseConnectionInformation::class, $connectionInfo);

            $this->blueGreenDeploymentService->setEnvironmentVariable($connection, $session);

            if ($this->setupDatabaseAdapter->getTableCount($connection, $connectionInfo->getDatabaseName())) {
                $connectionInfo->setDatabaseName('');

                return $this->renderInstaller('@Installer/installer/database-configuration.html.twig', [
                    'connectionInfo' => $connectionInfo,
                    'error' => $this->translator->trans('cicada.installer.database-configuration_non_empty_database'),
                ]);
            }
        } catch (DatabaseSetupException) {
            return $this->renderInstaller('@Installer/installer/database-configuration.html.twig', [
                'connectionInfo' => $connectionInfo,
                'error' => $this->translator->trans('cicada.installer.database-configuration_invalid_requirements'),
            ]);
        } catch (\Throwable $e) {
            return $this->renderInstaller('@Installer/installer/database-configuration.html.twig', [
                'connectionInfo' => $connectionInfo,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->redirectToRoute('installer.database-import');
    }

    #[Route(path: '/installer/database-information', name: 'installer.database-information', methods: ['POST'])]
    public function databaseInformation(Request $request): JsonResponse
    {
        $connectionInfo = (new DatabaseConnectionInformation())->assign($request->request->all());

        try {
            $connection = $this->connectionFactory->getConnection($connectionInfo, true);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => match (true) {
                    $e instanceof DatabaseSetupException => $this->translator->trans('cicada.installer.database-configuration_invalid_requirements'),
                    preg_match('/SQLSTATE\[HY000] \[1045]/', $e->getMessage()) === 1 => $this->translator->trans('cicada.installer.database-configuration_access_denied'),
                    preg_match('/SQLSTATE\[HY000] \[2002]/', $e->getMessage()) === 1 => $this->translator->trans('cicada.installer.database-configuration_host_not_found'),
                    default => $e->getMessage(),
                },
            ], Response::HTTP_BAD_REQUEST);
        }

        // No need for listing the following schemas
        $ignoredSchemas = ['information_schema', 'performance_schema', 'sys', 'mysql'];
        $databaseNames = $this->setupDatabaseAdapter->getExistingDatabases($connection, $ignoredSchemas);

        $result = [];
        foreach ($databaseNames as $databaseName) {
            $result[$databaseName] = $this->setupDatabaseAdapter->getTableCount($connection, $databaseName) > 0;
        }

        return new JsonResponse($result);
    }
}
