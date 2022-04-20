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
        $this->assertNull($this->entity->getId());

        $this->assertSame(
            $this->entity,
            $this->entity->setId($value = uniqid())
        );

        $this->assertSame($value, $this->entity->getId());
    }

    public function testDataMutator(): void
    {
        $this->assertSame(
            ['value' => null],
            $this->entity->getData()
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setData($value = ['value' => uniqid()])
        );

        $this->assertSame($value, $this->entity->getData());
    }

    public function testValueMutator(): void
    {
        $this->assertSame(
            null,
            $this->entity->getValue()
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setValue($value = uniqid())
        );

        $this->assertSame($value, $this->entity->getValue());

        $this->assertSame(
            ['value' => $value],
            $this->entity->getData()
        );
    }

    public function testCreatedAtMutator(): void
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getCreatedAt(),
            1
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new DateTimeImmutable('+ 5 minutes'))
        );

        $this->assertSame(
            $value->getTimestamp(),
            $this->entity->getCreatedAt()->getTimestamp()
        );
    }

    public function testUpdatedAtMutator(): void
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getUpdatedAt(),
            1
        );

        $this->assertSame(
            $this->entity->getCreatedAt()->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($value = new DateTimeImmutable('+ 5 minutes'))
        );

        $this->assertSame(
            $value->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );
    }

    public function testUpdateTimestamps(): void
    {
        $this->entity->setCreatedAt($value = new DateTimeImmutable('- 5 seconds'));
        $this->entity->setUpdatedAt($value);

        $this->entity->updateTimestamps();

        $this->assertSame(
            $value->getTimestamp(),
            $this->entity->getCreatedAt()->getTimestamp()
        );

        $this->assertNotSame(
            $value->getTimestamp(),
            $this->entity->getUpdatedAt()->getTimestamp()
        );
    }

    public function testToString()
    {
        $this->assertSame(
            '',
            (string) $this->entity,
        );

        $this->entity->setId($value = uniqid());

        $this->assertSame(
            $value,
            (string) $this->entity,
        );
    }
}
