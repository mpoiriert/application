<?php

namespace Draw\Component\Application\Versioning;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Draw\Contracts\Application\Exception\VersionInformationIsNotAccessibleException;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VersionManager implements VersionVerificationInterface
{
    final public const CONFIG = 'draw-application-deployed-version';

    private ?string $runningVersion = null;

    public function __construct(
        private ConfigurationRegistryInterface $configurationRegistry,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getRunningVersion(): ?string
    {
        if (null === $this->runningVersion) {
            $this->runningVersion = '';

            try {
                $this->eventDispatcher->dispatch($event = new FetchRunningVersionEvent());
            } catch (\Throwable $error) {
                throw new VersionInformationIsNotAccessibleException(previous: $error);
            }

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
        try {
            return $this->configurationRegistry->get(static::CONFIG);
        } catch (\Throwable $error) {
            throw new VersionInformationIsNotAccessibleException(previous: $error);
        }
    }

    public function isUpToDate(): bool
    {
        return $this->getDeployedVersion() === $this->getRunningVersion();
    }
}
