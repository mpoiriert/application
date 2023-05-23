<?php

namespace Draw\Component\Application\SystemMonitoring\Bridge\Doctrine;

use Draw\Component\Application\SystemMonitoring\ServiceStatus;

interface ConnectionStatusProviderInterface
{
    public static function getDefaultPriority(): int;

    public function supports(object $connection): bool;

    /**
     * @return iterable<ServiceStatus>
     */
    public function getConnectionServiceStatuses(object $connection): iterable;
}
