<?php

namespace Draw\Component\Application\Versioning\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FetchRunningVersionEvent extends Event
{
    private ?string $runningVersion = null;

    public function getRunningVersion(): ?string
    {
        return $this->runningVersion;
    }

    public function setRunningVersion(string $value): self
    {
        $this->runningVersion = $value;
        $this->stopPropagation();

        return $this;
    }
}
