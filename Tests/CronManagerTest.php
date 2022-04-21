<?php

namespace Draw\Component\Application\Tests;

use Draw\Component\Application\Cron\Job;
use Draw\Component\Application\CronManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\CronManager
 */
class CronManagerTest extends TestCase
{
    private CronManager $service;

    public function setUp(): void
    {
        $this->service = new CronManager();
    }

    public function testDumpJobs(): void
    {
        $job = new Job('Job name', 'echo "test"');
        $this->service->addJob($job);
        $cronTab = <<<CRONTAB
#Description: 
* * * * * echo "test" >/dev/null 2>&1

CRONTAB;

        $this->assertSame(
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
        $cronTab = <<<CRONTAB
#Description: 
* * * * * echo "test" >/dev/null 2>&1

#Description: Job every 5 minutes
*/5 * * * * echo "test" >/dev/null 2>&1

CRONTAB;

        $this->assertSame(
            $cronTab,
            $this->service->dumpJobs()
        );
    }

    public function testDumpJobsDisabled(): void
    {
        $job = new Job('Job 2', 'echo "test"');
        $job->setEnabled(false);
        $this->service->addJob($job);

        $this->assertSame(
            PHP_EOL,
            $this->service->dumpJobs()
        );
    }
}
