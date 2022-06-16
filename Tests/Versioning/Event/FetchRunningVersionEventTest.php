<?php

namespace Draw\Component\Application\Tests\Versioning\Event;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent
 */
class FetchRunningVersionEventTest extends TestCase
{
    private FetchRunningVersionEvent $event;

    protected function setUp(): void
    {
        $this->event = new FetchRunningVersionEvent();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testRunningVersionMutator(): void
    {
        static::assertFalse($this->event->isPropagationStopped());

        static::assertNull($this->event->getRunningVersion());

        static::assertSame(
            $this->event,
            $this->event->setRunningVersion($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->event->getRunningVersion()
        );

        static::assertTrue($this->event->isPropagationStopped());
    }
}
