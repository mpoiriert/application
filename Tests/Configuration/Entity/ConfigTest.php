<?php

namespace Draw\Component\Application\Tests\Configuration\Entity;

use DateTimeImmutable;
use Draw\Component\Application\Configuration\Entity\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\Configuration\Entity\Config
 */
class ConfigTest extends TestCase
{
    private Config $entity;

    public function setUp(): void
    {
        $this->entity = new Config();
    }

    public function testIdMutator(): void
    {
        static::assertNull($this->entity->getId());

        static::assertSame(
            $this->entity,
            $this->entity->setId($value = uniqid())
        );

        static::assertSame($value, $this->entity->getId());
    }

    public function testDataMutator(): void
    {
        static::assertSame(
            ['value' => null],
            $this->entity->getData()
        );

        static::assertSame(
            $this->entity,
            $this->entity->setData($value = ['value' => uniqid()])
        );

        static::assertSame($value, $this->entity->getData());
    }

    public function testValueMutator(): void
    {
        static::assertSame(
            null,
            $this->entity->getValue()
        );

        static::assertSame(
            $this->entity,
            $this->entity->setValue($value = uniqid())
        );

        static::assertSame($value, $this->entity->getValue());

        static::assertSame(
            ['value' => $value],
            $this->entity->getData()
        );
    }

    public function testCreatedAtMutator(): void
    {
        static::assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getCreatedAt(),
            1
        );

        static::assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new DateTimeImmutable('+ 5 minutes'))
        );

        static::assertSame(
            $value->getTimestamp(),
            $this->entity->getCreatedAt()->getTimestamp()
        );
    }

    public function testUpdatedAtMutator(): void
    {
        static::assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getUpdatedAt(),
            1
        );

        static::assertSame(
            $this->entity->getCreatedAt()->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );

        static::assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($value = new DateTimeImmutable('+ 5 minutes'))
        );

        static::assertSame(
            $value->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );
    }

    public function testUpdateTimestamps(): void
    {
        $this->entity->setCreatedAt($value = new DateTimeImmutable('- 5 seconds'));
        $this->entity->setUpdatedAt($value);

        $this->entity->updateTimestamps();

        static::assertSame(
            $value->getTimestamp(),
            $this->entity->getCreatedAt()->getTimestamp()
        );

        static::assertNotSame(
            $value->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );
    }

    public function testToString()
    {
        static::assertSame(
            '',
            (string) $this->entity,
        );

        $this->entity->setId($value = uniqid());

        static::assertSame(
            $value,
            (string) $this->entity,
        );
    }
}
