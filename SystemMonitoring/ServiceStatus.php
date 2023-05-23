<?php

namespace Draw\Component\Application\SystemMonitoring;

class ServiceStatus
{
    /**
     * @var array<string>
     */
    private array $errors = [];

    /**
     * @param array<string|\Throwable> $errors
     */
    public function __construct(
        private string $name,
        private Status $status,
        array $errors = [],
    ) {
        foreach ($errors as $index => $error) {
            if (\is_string($error)) {
                $this->errors[$index] = $error;
                continue;
            }

            if ($error instanceof \Throwable) {
                $this->errors[$index] = sprintf(
                    '%s(code: %s): %s',
                    \get_class($error),
                    $error->getCode(),
                    $error->getMessage()
                );
            }
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
