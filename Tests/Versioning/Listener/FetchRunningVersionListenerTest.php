<?php

namespace Draw\Component\Application\Tests\Versioning\Listener;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use Draw\Component\Application\Versioning\Listener\FetchRunningVersionListener;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Draw\Component\Application\Versioning\Listener\FetchRunningVersionListener
 */
class FetchRunningVersionListenerTest extends TestCase
{
    private FetchRunningVersionListener $service;

    private string $projectDirectory;

    public function setUp(): void
    {
        $this->service = new FetchRunningVersionListener(
            $this->projectDirectory = realpath(__DIR__.'/../../../../..')
        );

        $this->tearDown();
    }

    public function tearDown(): void
    {
        @unlink($this->projectDirectory.'/public/version.txt');
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                FetchRunningVersionEvent::class => [
                    ['fetchFromFilesystemPublicVersion', 255],
                    ['fetchFromGit', -10],
                ],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testFetchFromFilesystemPublicVersionFileDoNotExists(): void
    {
        $this->service->fetchFromFilesystemPublicVersion($event = new FetchRunningVersionEvent());

        $this->assertNull($event->getRunningVersion());
    }

    public function testFetchFromFilesystemPublicVersion(): void
    {
        file_put_contents($this->projectDirectory.'/public/version.txt', $version = uniqid('version-'));

        $this->service->fetchFromFilesystemPublicVersion($event = new FetchRunningVersionEvent());

        $this->assertSame(
            $version,
            $event->getRunningVersion()
        );
    }

    public function testFetchFromGitNotExists(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->service,
            'projectDirectory',
            __DIR__
        );

        $this->service->fetchFromGit($event = new FetchRunningVersionEvent());

        $this->assertNull($event->getRunningVersion());
    }

    public function testFetchFromGit(): void
    {
        $this->service->fetchFromGit($event = new FetchRunningVersionEvent());

        $this->assertNotNull($event->getRunningVersion());
    }
}
