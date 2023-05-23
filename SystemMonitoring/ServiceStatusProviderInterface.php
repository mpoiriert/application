<?php

namespace Draw\Component\Application\SystemMonitoring;

interface ServiceStatusProviderInterface
{
    /**
     * @return iterable<ServiceStatus>
     */
    public function getServiceStatuses(): iterable;
}
