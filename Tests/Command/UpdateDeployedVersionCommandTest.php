<?php

namespace Draw\Component\Application\Tests\Command;

use Draw\Component\Application\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\VersionManager;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @covers \Draw\Component\Application\Command\UpdateDeployedVersionCommand
 */
class UpdateDeployedVersionCommandTest extends TestCase
{
    use CommandTestTrait;

    private VersionManager $versionManager;

    public function createCommand(): Command
    {
        return new UpdateDeployedVersionCommand(
            $this->versionManager = $this->createMock(VersionManager::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:application:update-deployed-version';
    }

    public function getCommandDescription(): string
    {
        return 'You should run this after every successful application deployment.';
    }

    public function provideTestArgument(): iterable
    {
        return [];
    }

    public function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $this->versionManager
            ->expects($this->once())
            ->method('updateDeployedVersion');

        $this->versionManager
            ->expects($this->once())
            ->method('getRunningVersion')
            ->willReturn($deployedVersion = uniqid('version-'));

        $this->execute([])
            ->test(
                CommandDataTester::create(
                    0,
                    ['Deployed Version set to: '.$deployedVersion]
                )
            );
    }
}
