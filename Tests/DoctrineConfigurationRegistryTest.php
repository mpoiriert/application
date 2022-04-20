<?php

namespace Draw\Component\Application\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Application\DoctrineConfigurationRegistry;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Application\DoctrineConfigurationRegistry
 */
class DoctrineConfigurationRegistryTest extends TestCase
{
    private static EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            [dirname((new \ReflectionClass(Config::class))->getFileName())],
            true,
            null,
            null,
            false
        );

        static::$entityManager = EntityManager::create(
            [
                'driver' => 'pdo_mysql',
                'url' => getenv('DATABASE_URL'),
            ],
            $config,
        );

        $helperSet = ConsoleRunner::createHelperSet(static::$entityManager);

        $console = ConsoleRunner::createApplication($helperSet);
        $console->setAutoExit(false);
        $console->setCatchExceptions(false);

        $result = $console->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'command' => 'orm:schema-tool:update',
                '--force' => null,
            ]),
            $output = new \Symfony\Component\Console\Output\BufferedOutput()
        );

        if (0 !== $result) {
            var_dump($output->fetch());
            exit($result);
        }

        static::$entityManager
            ->createQueryBuilder()
            ->delete(Config::class, 'config')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(['name' => 'value']);
    }

    public function setUp(): void
    {
        $this->service = new DoctrineConfigurationRegistry(static::$entityManager);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ConfigurationRegistryInterface::class, $this->service);
    }

    public function testHasNotSet(): void
    {
        $this->assertFalse($this->service->has('value'));
    }

    /**
     * @depends testHasNotSet
     */
    public function testGetDefault(): void
    {
        $this->assertNull($this->service->get('value'));

        $this->assertTrue($this->service->get('value', true));
    }

    public function testSet(): void
    {
        $this->service->set('value', 'the-value');

        $this->addToAssertionCount(1);
    }

    /**
     * @depends testSet
     */
    public function testHasSet(): void
    {
        $this->assertTrue($this->service->has('value'));
    }

    /**
     * @depends testHasSet
     */
    public function testGetSet(): void
    {
        $this->assertSame('the-value', $this->service->get('value'));
    }

    /**
     * @depends testHasSet
     */
    public function testDelete(): void
    {
        $this->service->delete('value');

        $this->addToAssertionCount(1);
    }

    /**
     * @depends testDelete
     */
    public function testHasAfterDelete(): void
    {
        $this->assertFalse($this->service->has('value'));
    }

    /**
     * @depends testGetSet
     */
    public function testGetValueChangeFromOtherScope(): void
    {
        $this->service->set('value', 'the-value');
        $this->assertSame('the-value', $this->service->get('value'));

        static::$entityManager
            ->createQueryBuilder()
            ->update(Config::class, 'config')
            ->set('config.data', ':data')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(
                ['name' => 'value', 'data' => json_encode(['value' => 'new-value'])]
            );

        $this->assertSame('new-value', $this->service->get('value'));
    }

    /**
     * @depends testGetSet
     */
    public function testGetValueInvalidState(): void
    {
        $this->service->set('value', 'the-value');
        $this->assertSame('the-value', $this->service->get('value'));

        static::$entityManager
            ->createQueryBuilder()
            ->update(Config::class, 'config')
            ->set('config.data', ':data')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(
                ['name' => 'value', 'data' => json_encode(['value' => 'new-value'])]
            );

        static::$entityManager->clear();

        $this->assertSame('new-value', $this->service->get('value'));
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
    public function testSetGetKeepType($value): void
    {
        $this->service->set('value', $value);
        $this->assertSame($value, $this->service->get('value'));
    }
}
