<?php

namespace Draw\Component\Application\Tests\Cron;

use Draw\Component\Application\Cron\CronManager;
use Draw\Component\Application\Cron\Job;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\Cron\CronManager
 */
class CronManagerTest extends TestCase
{
    private CronManager $service;

    protected function setUp(): void
    {
        $this->service = new CronManager();
    }

    public function testDumpJobs(): void
    {
        $job = new Job('Job name', 'echo "test"');
        $this->service->addJob($job);
        $cronTab = "#Description: \n* * * * * echo \"test\" >/dev/null 2>&1\n";

        static::assertSame(
            $cronTab,
            $this->service->dumpJobs()
        );
    }

    public function testDumpJobsTwoJobs(): void
    {
        $job = new Job('Job name', 'echo "test"');
        $this->service->addJob($job);

        $job = new Job('Job 2', 'echo "test"', '*/5 * * * *', true, 'Job every 5 minutes');
        $this->service->addJob($job);
        $cronTab = "#Description: \n* * * * * echo \"test\" >/dev/null 2>&1\n\n";
        $cronTab .= "#Description: Job every 5 minutes\n*/5 * * * * echo \"test\" >/dev/null 2>&1\n";

        static::assertSame(
            $cronTab,
            $this->service->dumpJobs()
        );
    }

    public function testDumpJobsDisabled(): void
    {
        $job = new Job('Job 2', 'echo "test"');
        $job->setEnabled(false);
        $this->service->addJob($job);

        static::assertSame(
            \PHP_EOL,
            $this->service->dumpJobs()
        );
    }
}
