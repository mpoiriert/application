<?php

namespace Draw\Component\Application\Tests\Event;

use Draw\Component\Application\Event\FetchRunningVersionEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Application\Event\FetchRunningVersionEvent
 */
class FetchRunningVersionEventTest extends TestCase
{
    private FetchRunningVersionEvent $event;

    public function setUp(): void
    {
        $this->event = new FetchRunningVersionEvent();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testRunningVersionMutator(): void
    {
        $this->assertFalse($this->event->isPropagationStopped());

        $this->assertNull($this->event->getRunningVersion());

        $this->assertSame(
            $this->event,
            $this->event->setRunningVersion($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->event->getRunningVersion()
        );

        $this->assertTrue($this->event->isPropagationStopped());
    }
}
