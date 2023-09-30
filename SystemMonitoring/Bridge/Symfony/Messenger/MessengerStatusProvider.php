<?php

namespace Draw\Component\Application\SystemMonitoring\Bridge\Symfony\Messenger;

use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\ServiceStatusProviderInterface;
use Draw\Component\Application\SystemMonitoring\Status;
use Draw\Contracts\Messenger\TransportRepositoryInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;

class MessengerStatusProvider implements ServiceStatusProviderInterface
{
    public function __construct(private TransportRepositoryInterface $transportRepository)
    {
    }

    public function getServiceStatuses(array $options = []): iterable
    {
        $transportNames = $options['transportNames'] ?? $this->transportRepository->getTransportNames();

        foreach ($transportNames as $transportName) {
            $transport = $this->transportRepository->get($transportName);
            $serviceName = sprintf('Messenger transport [%s]', $transportName);
            if (
                $transport instanceof SyncTransport
                || $transport instanceof InMemoryTransport
            ) {
                yield new ServiceStatus($serviceName, Status::OK);

                continue;
            }

            if (!$transport instanceof MessageCountAwareInterface) {
                yield new ServiceStatus($serviceName, Status::UNKNOWN);

                continue;
            }

            try {
                $transport->getMessageCount();
                yield new ServiceStatus($serviceName, Status::OK);
            } catch (\Throwable $error) {
                yield new ServiceStatus($serviceName, Status::ERROR, [$error]);
            }
        }
    }
}
