<?php

namespace Draw\Component\Application\SystemMonitoring\Bridge\Doctrine;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\Status;

class DBALPrimaryReadReplicaConnectionStatusProvider implements ConnectionStatusProviderInterface
{
    public static function getDefaultPriority(): int
    {
        return 0;
    }

    public function supports(object $connection): bool
    {
        return $connection instanceof PrimaryReadReplicaConnection;
    }

    public function getConnectionServiceStatuses(object $connection): iterable
    {
        \assert($connection instanceof PrimaryReadReplicaConnection);

        $previousConnectionToPrimary = $connection->isConnectedToPrimary();

        $dummySql = $connection->getDatabasePlatform()->getDummySelectSQL();

        try {
            $connection->ensureConnectedToPrimary();
            $connection->executeQuery($dummySql);

            yield new ServiceStatus(
                'Primary Connection',
                Status::OK
            );
        } catch (\Throwable $error) {
            yield new ServiceStatus(
                'Primary Connection',
                Status::ERROR,
                [$error]
            );
        }

        try {
            $connection->ensureConnectedToReplica();
            $connection->executeQuery($dummySql);

            yield new ServiceStatus(
                'Primary Connection',
                Status::OK
            );
        } catch (\Throwable $error) {
            yield new ServiceStatus(
                'Replica Connection',
                Status::ERROR,
                [$error]
            );
        }

        $previousConnectionToPrimary
            ? $connection->ensureConnectedToPrimary()
            : $connection->ensureConnectedToReplica();
    }
}
