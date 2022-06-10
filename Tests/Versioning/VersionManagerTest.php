<?php

namespace Draw\Component\Application\Tests\Versioning;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Draw\Contracts\Application\VersionVerificationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Draw\Component\Application\Versioning\VersionManager
 */
class VersionManagerTest extends TestCase
{
    private VersionManager $service;

    private ConfigurationRegistryInterface $configurationRegistry;

    private EventDispatcherInterface $eventDispatcher;

    public function setUp(): void
    {
        $this->service = new VersionManager(
            $this->configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testConstant(): void
    {
        $this->assertSame(
            'draw-application-deployed-version',
            $this->service::CONFIG
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            VersionVerificationInterface::class,
            $this->service
        );
    }

    public function testGetRunningVersionNotFound(): void
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FetchRunningVersionEvent::class))
            ->willReturnArgument(0);

        $this->assertNull($this->service->getRunningVersion());

        // Multiple call will not trigger multiple event
        $this->assertNull($this->service->getRunningVersion());
    }

    public function testGetRunningVersion(): void
    {
        $version = uniqid('version-');

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (FetchRunningVersionEvent $event) use ($version) {
                    $event->setRunningVersion($version);

                    return true;
                })
            )
            ->willReturnArgument(0);

        $this->assertSame(
            $version,
            $this->service->getRunningVersion()
        );
    }

    public function testUpdateDeployedVersion(): void
    {
        $version = uniqid('version-');

        ReflectionAccessor::setPropertyValue(
            $this->service,
            'runningVersion',
            $version
        );

        $this->configurationRegistry
            ->expects($this->once())
            ->method('set')
            ->with($this->service::CONFIG, $version);

        $this->service->updateDeployedVersion();
    }

    public function testGetDeployedVersion(): void
    {
        $this->configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($this->service::CONFIG)
            ->willReturn($version = uniqid('version-'));

        $this->assertSame(
            $version,
            $this->service->getDeployedVersion()
        );
    }

    public function testIsUpToDate(): void
    {
        $this->configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($this->service::CONFIG)
            ->willReturn($version = uniqid('version-'));

        ReflectionAccessor::setPropertyValue(
            $this->service,
            'runningVersion',
            $version
        );

        $this->assertTrue($this->service->isUpToDate());
    }

    public function testIsUpToDateFalse(): void
    {
        $this->configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($this->service::CONFIG)
            ->willReturn(uniqid('version-'));

        ReflectionAccessor::setPropertyValue(
            $this->service,
            'runningVersion',
            uniqid('version-')
        );

        $this->assertFalse($this->service->isUpToDate());
    }
}
