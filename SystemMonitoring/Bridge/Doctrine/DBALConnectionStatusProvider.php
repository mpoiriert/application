<?php

namespace Draw\Component\Application\SystemMonitoring\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\Status;

class DBALConnectionStatusProvider implements ConnectionStatusProviderInterface
{
    public static function getDefaultPriority(): int
    {
        return -1000;
    }

    public function supports(object $connection): bool
    {
        return $connection instanceof Connection;
    }

    /**
     * @return iterable<ServiceStatus>
     */
    public function getConnectionServiceStatuses(object $connection): iterable
    {
        \assert($connection instanceof Connection);

        $dummySql = $connection->getDatabasePlatform()->getDummySelectSQL();

        try {
            $connection->executeQuery($dummySql);

            yield new ServiceStatus(
                'Connection',
                Status::OK
            );
        } catch (\Throwable $error) {
            yield new ServiceStatus(
                'Connection',
                Status::ERROR,
                [$error]
            );
        }
    }
}
