<?php

namespace Draw\Component\Application\SystemMonitoring\Bridge\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\ServiceStatusProviderInterface;
use Draw\Component\Application\SystemMonitoring\Status;

class DoctrineConnectionServiceStatusProvider implements ServiceStatusProviderInterface
{
    /**
     * @param iterable<ConnectionStatusProviderInterface> $connectionTesters
     */
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private iterable $connectionTesters
    ) {
    }

    public function getServiceStatuses(): iterable
    {
        foreach ($this->managerRegistry->getConnections() as $key => $connection) {
            foreach ($this->connectionTesters as $connectionTester) {
                if ($connectionTester->supports($connection)) {
                    foreach ($connectionTester->getConnectionServiceStatuses($connection) as $serviceStatus) {
                        yield new ServiceStatus(
                            sprintf('Doctrine Connection [%s] %s', $key, $serviceStatus->getName()),
                            $serviceStatus->getStatus(),
                            $serviceStatus->getErrors()
                        );
                    }

                    continue 2;
                }
            }

            yield new ServiceStatus(
                sprintf('Doctrine Connection [%s]', $key),
                Status::UNKNOWN,
            );
        }
    }
}
