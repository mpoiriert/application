<?php

namespace Draw\Component\Application\Cron;

class Job
{
    /**
     * Where the output will be redirect.
     */
    private string $output = '>/dev/null 2>&1';

    public function __construct(
        /**
         * The name of the job for reference.
         */
        private string $name,
        /**
         * The command to execute.
         */
        private string $command,
        /**
         * The cron execution expression configuration.
         */
        private string $expression = '* * * * *',
        /**
         * If the job is enabled or not.
         */
        private bool $enabled = true,
        /**
         * The description of the job.
         */
        private ?string $description = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
