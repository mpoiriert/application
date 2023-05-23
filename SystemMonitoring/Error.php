<?php

namespace Draw\Component\Application\SystemMonitoring;

class Error
{
    public function __construct(
        private string $message,
        private ?\Throwable $error = null,
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getError(): ?\Throwable
    {
        return $this->error;
    }
}
