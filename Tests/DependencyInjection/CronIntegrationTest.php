<?php

namespace Draw\Component\Application\Tests\DependencyInjection;

use Draw\Component\Application\Cron\Command\CronDumpToFileCommand;
use Draw\Component\Application\Cron\CronManager;
use Draw\Component\Application\DependencyInjection\CronIntegration;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @property CronIntegration $integration
 */
#[CoversClass(CronIntegration::class)]
class CronIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new CronIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'cron';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'jobs' => [],
        ];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [
                [
                    'jobs' => [
                        'acme_cron' => [
                            'description' => 'Execute acme:command every 5 minutes',
                            'command' => 'acme:command',
                            'expression' => '*/5 * * * *',
                            'enabled' => false,
                        ],
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.cron.command.cron_dump_to_file_command',
                    [
                        CronDumpToFileCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.cron.cron_manager',
                    [
                        CronManager::class,
                    ],
                    function (Definition $definition): void {
                        $methodCalls = $definition->getMethodCalls();

                        static::assertCount(1, $methodCalls);

                        static::assertSame(
                            'addJob',
                            $methodCalls[0][0]
                        );

                        static::assertCount(
                            1,
                            $methodCalls[0][1]
                        );

                        $jobDefinition = $methodCalls[0][1][0];

                        static::assertInstanceOf(Definition::class, $jobDefinition);

                        static::assertSame(
                            [
                                'acme_cron',
                                'acme:command',
                                '*/5 * * * *',
                                false,
                                'Execute acme:command every 5 minutes',
                            ],
                            $jobDefinition->getArguments()
                        );

                        static::assertSame(
                            [
                                [
                                    'setOutput',
                                    [
                                        '>/dev/null 2>&1',
                                    ],
                                ],
                            ],
                            $jobDefinition->getMethodCalls()
                        );
                    }
                ),
            ],
        ];
    }
}
