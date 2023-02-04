<?php

namespace Draw\Component\Application\Versioning;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VersionManager implements VersionVerificationInterface
{
    final public const CONFIG = 'draw-application-deployed-version';

    private ?string $runningVersion = null;

    public function __construct(
        private ConfigurationRegistryInterface $configurationRegistry,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getRunningVersion(): ?string
    {
        if (null === $this->runningVersion) {
            $this->runningVersion = '';

            $this->eventDispatcher->dispatch($event = new FetchRunningVersionEvent());

            $this->runningVersion = $event->getRunningVersion() ?: '';
        }

        return $this->runningVersion ?: null;
    }

    public function updateDeployedVersion(): void
    {
        $this->configurationRegistry->set(static::CONFIG, $this->getRunningVersion());
    }

    public function getDeployedVersion(): ?string
    {
        return $this->configurationRegistry->get(static::CONFIG);
    }

    public function isUpToDate(): bool
    {
        return $this->getDeployedVersion() === $this->getRunningVersion();
    }
}
