<?php

namespace Draw\Component\Application\Cron;

class CronManager
{
    private const JOB_STRING_PATTERN = "#Description: {description}\n{expression} {command} {output}\n";

    /**
     * @var array|Job[]
     */
    private array $jobs = [];

    public function addJob(Job $job): void
    {
        $this->jobs[] = $job;
    }

    /**
     * Dump all the jobs to a crontab compatible string.
     */
    public function dumpJobs(): string
    {
        $result = [];
        foreach ($this->jobs as $job) {
            if (!$job->getEnabled()) {
                continue;
            }
            $jobData = $job->toArray();
            $mapping = [];
            foreach ($jobData as $key => $value) {
                $mapping['{'.$key.'}'] = $value;
            }

            $cronLine = str_replace(
                array_keys($mapping),
                array_values($mapping),
                self::JOB_STRING_PATTERN
            );

            $result[] = $cronLine;
        }

        return trim(implode(\PHP_EOL, $result)).\PHP_EOL;
    }
}
