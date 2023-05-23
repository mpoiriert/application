<?php

namespace Draw\Component\Application\SystemMonitoring;

class MonitoredService
{
    /**
     * @param array<string> $contexts
     */
    public function __construct(
        private string $name,
        private ServiceStatusProviderInterface $serviceStatusProvider,
        private array $contexts,
        private bool $anyContexts,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function supports(?string $context = null): bool
    {
        if ($this->anyContexts) {
            return true;
        }

        if (null === $context) {
            return true;
        }

        return \in_array($context, $this->contexts);
    }

    public function getServiceStatuses(?string $context = null): iterable
    {
        if (!$this->supports($context)) {
            throw new \RuntimeException(sprintf('Context "%s" is not supported by "%s".', $context, $this->name));
        }

        yield from $this->serviceStatusProvider->getServiceStatuses();
    }
}
