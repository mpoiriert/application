<?php

namespace Draw\Component\Application\Tests\Versioning\Command;

use Draw\Component\Application\Versioning\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

#[CoversClass(UpdateDeployedVersionCommand::class)]
class UpdateDeployedVersionCommandTest extends TestCase
{
    use CommandTestTrait;

    private VersionManager&MockObject $versionManager;

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

    public static function provideTestArgument(): iterable
    {
        return [];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $this->versionManager
            ->expects(static::once())
            ->method('updateDeployedVersion');

        $this->versionManager
            ->expects(static::once())
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
