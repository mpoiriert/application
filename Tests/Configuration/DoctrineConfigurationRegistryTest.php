<?php

namespace Draw\Component\Application\Tests\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\Application\Configuration\DoctrineConfigurationRegistry;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Tester\DoctrineOrmTrait;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\Configuration\DoctrineConfigurationRegistry
 */
class DoctrineConfigurationRegistryTest extends TestCase
{
    use DoctrineOrmTrait;

    private DoctrineConfigurationRegistry $object;

    private static EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = static::setUpMySqlWithAttributeDriver(
            [\dirname((new \ReflectionClass(Config::class))->getFileName())]
        );

        self::$entityManager
            ->createQueryBuilder()
            ->delete(Config::class, 'config')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(['name' => 'value']);
    }

    protected function setUp(): void
    {
        $this->object = new DoctrineConfigurationRegistry(self::$entityManager);
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(ConfigurationRegistryInterface::class, $this->object);
    }

    public function testHasNotSet(): void
    {
        static::assertFalse($this->object->has('value'));
    }

    /**
     * @depends testHasNotSet
     */
    public function testGetDefault(): void
    {
        static::assertNull($this->object->get('value'));

        static::assertTrue($this->object->get('value', true));
    }

    public function testSet(): void
    {
        $this->object->set('value', 'the-value');

        $this->addToAssertionCount(1);
    }

    /**
     * @depends testSet
     */
    public function testHasSet(): void
    {
        static::assertTrue($this->object->has('value'));
    }

    /**
     * @depends testHasSet
     */
    public function testGetSet(): void
    {
        static::assertSame('the-value', $this->object->get('value'));
    }

    /**
     * @depends testHasSet
     */
    public function testDelete(): void
    {
        $this->object->delete('value');

        $this->addToAssertionCount(1);
    }

    /**
     * @depends testDelete
     */
    public function testHasAfterDelete(): void
    {
        static::assertFalse($this->object->has('value'));
    }

    /**
     * @depends testGetSet
     */
    public function testGetValueChangeFromOtherScope(): void
    {
        $this->object->set('value', 'the-value');
        static::assertSame('the-value', $this->object->get('value'));

        self::$entityManager
            ->createQueryBuilder()
            ->update(Config::class, 'config')
            ->set('config.data', ':data')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(
                ['name' => 'value', 'data' => json_encode(['value' => 'new-value'])]
            );

        static::assertSame('new-value', $this->object->get('value'));
    }

    /**
     * @depends testGetSet
     */
    public function testGetValueInvalidState(): void
    {
        $this->object->set('value', 'the-value');
        static::assertSame('the-value', $this->object->get('value'));

        self::$entityManager
            ->createQueryBuilder()
            ->update(Config::class, 'config')
            ->set('config.data', ':data')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(
                ['name' => 'value', 'data' => json_encode(['value' => 'new-value'])]
            );

        self::$entityManager->clear();

        static::assertSame('new-value', $this->object->get('value'));
    }

    public function provideTestSetGetKeepType(): iterable
    {
        yield 'string' => [
            'value',
        ];

        yield 'boolean' => [
            true,
        ];

        yield 'array' => [
            ['key' => 'value'],
        ];

        yield 'integer' => [
            1,
        ];

        yield 'float' => [
            1.5,
        ];
    }

    /**
     * @dataProvider provideTestSetGetKeepType
     */
    public function testSetGetKeepType(mixed $value): void
    {
        $this->object->set('value', $value);
        static::assertSame($value, $this->object->get('value'));
    }
}
