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

    public function setUp(): void
    {
        $this->entity = new Job(self::DEFAULT_NAME, self::DEFAULT_COMMAND);
    }

    public function testNameMutator(): void
    {
        $this->assertSame(self::DEFAULT_NAME, $this->entity->getName());

        $this->assertSame(
            $this->entity,
            $this->entity->setName($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getName()
        );
    }

    public function testCommandMutator(): void
    {
        $this->assertSame(self::DEFAULT_COMMAND, $this->entity->getCommand());

        $this->assertSame(
            $this->entity,
            $this->entity->setCommand($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getCommand()
        );
    }

    public function testDescriptionMutator(): void
    {
        $this->assertNull($this->entity->getDescription());

        $this->assertSame(
            $this->entity,
            $this->entity->setDescription($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getDescription()
        );
    }

    public function testEnabledMutator(): void
    {
        $this->assertSame(
            true,
            $this->entity->getEnabled()
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setEnabled(false)
        );

        $this->assertSame(
            false,
            $this->entity->getEnabled()
        );
    }

    public function testExpressionMutator(): void
    {
        $this->assertSame('* * * * *', $this->entity->getExpression());

        $this->assertSame(
            $this->entity,
            $this->entity->setExpression($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getExpression()
        );
    }

    public function testOutputMutator(): void
    {
        $this->assertSame('>/dev/null 2>&1', $this->entity->getOutput());

        $this->assertSame(
            $this->entity,
            $this->entity->setOutput($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getOutput()
        );
    }

    public function testToArray(): void
    {
        $this->assertEquals(
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
