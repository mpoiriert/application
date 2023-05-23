<?php

namespace Draw\Component\Application\SystemMonitoring;

class System
{
    /**
     * @param iterable<MonitoredService> $monitoredServices
     */
    public function __construct(
        private iterable $monitoredServices,
    ) {
    }

    public function getServiceStatuses(string $context): MonitoringResult
    {
        $result = [];
        foreach ($this->monitoredServices as $monitoredService) {
            if (!$monitoredService->supports($context)) {
                continue;
            }

            $result[$monitoredService->getName()] = [];

            foreach ($monitoredService->getServiceStatuses($context) as $serviceStatus) {
                $result[$monitoredService->getName()][] = $serviceStatus;
            }
        }

        return new MonitoringResult($result, $context);
    }

    public function getAllServiceStatuses(): MonitoringResult
    {
        $result = [];
        foreach ($this->monitoredServices as $monitoredService) {
            $result[$monitoredService->getName()] = [];

            foreach ($monitoredService->getServiceStatuses() as $serviceStatus) {
                $result[$monitoredService->getName()][] = $serviceStatus;
            }
        }

        return new MonitoringResult($result);
    }
}
