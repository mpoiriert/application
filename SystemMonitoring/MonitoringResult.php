<?php

namespace Draw\Component\Application\SystemMonitoring;

class MonitoringResult
{
    private ?Status $status = null;

    /**
     * @param array<string, array<ServiceStatus>> $serviceStatuses
     */
    public function __construct(
        private array $serviceStatuses,
        private ?string $context = null,
    ) {
        /** @var ServiceStatus $serviceStatus */
        foreach (array_merge(...array_values($this->serviceStatuses)) as $serviceStatus) {
            switch ($serviceStatus->getStatus()) {
                case Status::OK:
                    $this->status ??= Status::OK;
                    break;
                case Status::UNKNOWN:
                    if (Status::ERROR !== $this->status) {
                        $this->status = Status::UNKNOWN;
                    }
                    break;
                case Status::ERROR:
                    $this->status = Status::ERROR;
                    break 2;
            }
        }

        $this->status ??= Status::UNKNOWN;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @return array<string, array<ServiceStatus>>
     */
    public function getServiceStatuses(): array
    {
        return $this->serviceStatuses;
    }
}
