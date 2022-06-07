<?php

namespace Draw\Component\Application\Tests\Command;

use Draw\Component\Application\Command\CronDumpToFileCommand;
use Draw\Component\Application\CronManager;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @covers \Draw\Component\Application\Command\CronDumpToFileCommand
 */
class CronDumpToFileCommandTest extends TestCase
{
    use CommandTestTrait;

    private CronManager $cronManager;

    public function createCommand(): Command
    {
        return new CronDumpToFileCommand(
            $this->cronManager = $this->createMock(CronManager::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:cron:dump-to-file';
    }

    public function provideTestArgument(): iterable
    {
        yield ['filePath', InputArgument::REQUIRED];
    }

    public function provideTestOption(): iterable
    {
        yield ['override', null, InputOption::VALUE_NONE];
    }

    public function testExecuteNewFile(): void
    {
        $this->cronManager
            ->expects($this->once())
            ->method('dumpJobs')
            ->willReturn('Output');

        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        register_shutdown_function('unlink', $filePath);
        $this
            ->execute(['filePath' => $filePath])
            ->test(CommandDataTester::create());

        $this->assertSame('Output', file_get_contents($filePath));
    }

    public function testExecuteNewFileOverride(): void
    {
        $this->cronManager
            ->expects($this->once())
            ->method('dumpJobs')
            ->willReturn('Output');

        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($filePath, 'Before');
        register_shutdown_function('unlink', $filePath);
        $this
            ->execute(['filePath' => $filePath, '--override' => '1'])
            ->test(CommandDataTester::create());

        $this->assertSame('Output', file_get_contents($filePath));
    }

    public function testExecuteNewFileNoOverrideException(): void
    {
        $this->cronManager
            ->expects($this->never())
            ->method('dumpJobs');

        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        touch($filePath);
        register_shutdown_function('unlink', $filePath);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The file [%s] already exists. Remove the file or use option --override.',
            $filePath
        ));

        $this->execute(['filePath' => $filePath]);
    }
}
