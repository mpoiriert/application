<?php

namespace Draw\Component\Application\Tests\Cron;

use Draw\Component\Application\Cron\Job;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\Cron\Job
 */
class JobTest extends TestCase
{
    private const DEFAULT_NAME = 'name';
    private const DEFAULT_COMMAND = 'ls';

    private Job $entity;

    protected function setUp(): void
    {
        $this->entity = new Job(self::DEFAULT_NAME, self::DEFAULT_COMMAND);
    }

    public function testNameMutator(): void
    {
        static::assertSame(self::DEFAULT_NAME, $this->entity->getName());

        static::assertSame(
            $this->entity,
            $this->entity->setName($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getName()
        );
    }

    public function testCommandMutator(): void
    {
        static::assertSame(self::DEFAULT_COMMAND, $this->entity->getCommand());

        static::assertSame(
            $this->entity,
            $this->entity->setCommand($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getCommand()
        );
    }

    public function testDescriptionMutator(): void
    {
        static::assertNull($this->entity->getDescription());

        static::assertSame(
            $this->entity,
            $this->entity->setDescription($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getDescription()
        );
    }

    public function testEnabledMutator(): void
    {
        static::assertTrue(
            $this->entity->getEnabled()
        );

        static::assertSame(
            $this->entity,
            $this->entity->setEnabled(false)
        );

        static::assertFalse(
            $this->entity->getEnabled()
        );
    }

    public function testExpressionMutator(): void
    {
        static::assertSame('* * * * *', $this->entity->getExpression());

        static::assertSame(
            $this->entity,
            $this->entity->setExpression($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getExpression()
        );
    }

    public function testOutputMutator(): void
    {
        static::assertSame('>/dev/null 2>&1', $this->entity->getOutput());

        static::assertSame(
            $this->entity,
            $this->entity->setOutput($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getOutput()
        );
    }

    public function testToArray(): void
    {
        static::assertEquals(
            [
                'name' => 'name',
                'description' => '',
                'expression' => '* * * * *',
                'command' => 'ls',
                'enabled' => true,
                'output' => '>/dev/null 2>&1',
            ],
            $this->entity->toArray()
        );
    }
}
