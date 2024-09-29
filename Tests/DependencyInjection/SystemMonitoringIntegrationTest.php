<?php

namespace Draw\Component\Application\Tests\DependencyInjection;

use Draw\Component\Application\DependencyInjection\SystemMonitoringIntegration;
use Draw\Component\Application\SystemMonitoring\Action\PingAction;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DBALConnectionStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DBALPrimaryReadReplicaConnectionStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DoctrineConnectionServiceStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Symfony\Messenger\MessengerStatusProvider;
use Draw\Component\Application\SystemMonitoring\Command\SystemStatusesCommand;
use Draw\Component\Application\SystemMonitoring\System;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @property SystemMonitoringIntegration $integration
 *
 * @internal
 */
#[CoversClass(SystemMonitoringIntegration::class)]
class SystemMonitoringIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new SystemMonitoringIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'system_monitoring';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'enabled' => true,
            'service_status_providers' => [
            ],
        ];
    }

    public function testPrepend(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->registerExtension($this->mockExtension('draw_framework_extra'));

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'draw_framework_extra' => [
                    [
                        'system_monitoring' => [
                            'service_status_providers' => [
                                'doctrine_connection' => [
                                    'service' => DoctrineConnectionServiceStatusProvider::class,
                                    'enabled' => true,
                                    'any_contexts' => true,
                                ],
                                'messenger' => [
                                    'service' => MessengerStatusProvider::class,
                                    'enabled' => true,
                                    'any_contexts' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [
                [
                    'service_status_providers' => [
                        'doctrine_connection' => [
                            'service' => DoctrineConnectionServiceStatusProvider::class,
                            'enabled' => true,
                            'any_contexts' => true,
                        ],
                        'messenger' => [
                            'service' => MessengerStatusProvider::class,
                            'enabled' => true,
                            'any_contexts' => true,
                        ],
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.system_monitoring.monitored_service.doctrine_connection',
                    [
                    ]
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.monitored_service.messenger',
                    [
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.action.ping_action',
                    [
                        PingAction::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.system',
                    [
                        System::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.command.system_statuses_command',
                    [
                        SystemStatusesCommand::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.bridge.symfony.messenger.messenger_status_provider',
                    [
                        MessengerStatusProvider::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.bridge.doctrine.doctrine_connection_service_status_provider',
                    [
                        DoctrineConnectionServiceStatusProvider::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.bridge.doctrine.dbal_connection_status_provider',
                    [
                        DBALConnectionStatusProvider::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.system_monitoring.bridge.doctrine.dbal_primary_read_replica_connection_status_provider',
                    [
                        DBALPrimaryReadReplicaConnectionStatusProvider::class,
                    ],
                ),
            ],
        ];
    }
}
