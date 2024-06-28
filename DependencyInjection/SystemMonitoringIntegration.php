<?php

namespace Draw\Component\Application\DependencyInjection;

use Doctrine\Persistence\ConnectionRegistry;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\ConnectionStatusProviderInterface;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DBALConnectionStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DBALPrimaryReadReplicaConnectionStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Doctrine\DoctrineConnectionServiceStatusProvider;
use Draw\Component\Application\SystemMonitoring\Bridge\Symfony\Messenger\MessengerStatusProvider;
use Draw\Component\Application\SystemMonitoring\MonitoredService;
use Draw\Component\Application\SystemMonitoring\System;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\DependencyInjection\Integration\PrependIntegrationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;

class SystemMonitoringIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'system_monitoring';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Application\\SystemMonitoring\\',
            $directory = \dirname((new \ReflectionClass(System::class))->getFileName()),
            [
                $directory.'/Error.php',
                $directory.'/MonitoredService.php',
                $directory.'/MonitoringResult.php',
                $directory.'/ServiceStatus.php',
                $directory.'/Status.php',
            ]
        );

        $monitoredServices = [];
        foreach ($config['service_status_providers'] as $serviceStatusProvider) {
            if (!$this->isConfigEnabled($container, $serviceStatusProvider)) {
                continue;
            }

            $serviceId = 'draw.system_monitoring.monitored_service.'.$serviceStatusProvider['name'];

            $container->setDefinition(
                $serviceId,
                (new Definition(MonitoredService::class))
                    ->setArgument('$name', $serviceStatusProvider['name'])
                    ->setArgument('$serviceStatusProvider', new Reference($serviceStatusProvider['service']))
                    ->setArgument('$contexts', $serviceStatusProvider['contexts'])
                    ->setArgument('$anyContexts', $serviceStatusProvider['any_contexts'])
                    ->setArgument('$options', $serviceStatusProvider['options'])
            );

            $monitoredServices[] = new Reference($serviceId);
        }

        $container
            ->getDefinition(System::class)
            ->setArgument('$monitoredServices', new IteratorArgument($monitoredServices));

        $container
            ->registerForAutoconfiguration(ConnectionStatusProviderInterface::class)
            ->addTag(ConnectionStatusProviderInterface::class);

        $container
            ->getDefinition(DoctrineConnectionServiceStatusProvider::class)
            ->setArgument('$connectionTesters', new TaggedIteratorArgument(ConnectionStatusProviderInterface::class));

        $this->renameDefinitions(
            $container,
            DBALConnectionStatusProvider::class,
            'draw.system_monitoring.bridge.doctrine.dbal_connection_status_provider'
        );

        $this->renameDefinitions(
            $container,
            DBALPrimaryReadReplicaConnectionStatusProvider::class,
            'draw.system_monitoring.bridge.doctrine.dbal_primary_read_replica_connection_status_provider'
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.system_monitoring.'
        );

        $container
            ->getDefinition('draw.system_monitoring.action.ping_action')
            ->addTag('controller.service_arguments');
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        $defaultProviders = [];

        if (interface_exists(ConnectionRegistry::class)) {
            $defaultProviders['doctrine_connection'] = [
                'service' => DoctrineConnectionServiceStatusProvider::class,
                'enabled' => true,
                'any_contexts' => true,
            ];
        }

        if (interface_exists(MessageBusInterface::class)) {
            $defaultProviders['messenger'] = [
                'service' => MessengerStatusProvider::class,
                'enabled' => true,
                'any_contexts' => true,
            ];
        }

        $container->prependExtensionConfig(
            'draw_framework_extra',
            [
                $this->getConfigSectionName() => [
                    'service_status_providers' => $defaultProviders,
                ],
            ]
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->canBeEnabled()
            ->children()
                ->arrayNode('service_status_providers')
                ->beforeNormalization()
                    ->always(function ($config) {
                        foreach ($config as $name => $configuration) {
                            if (!isset($configuration['name'])) {
                                $config[$name]['name'] = $name;
                            }
                        }

                        return $config;
                    })
                ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('name')
                                ->validate()
                                    ->ifTrue(fn ($value) => \is_int($value))
                                    ->thenInvalid('You must specify a name for the service status provider. Can be via the attribute or the key.')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('service')->end()
                            ->arrayNode('options')
                                ->variablePrototype()->end()
                                ->defaultValue([])
                            ->end()
                            ->booleanNode('any_contexts')->defaultFalse()->end()
                            ->arrayNode('contexts')
                                ->validate()
                                    ->ifTrue(function ($values) {
                                        foreach ($values as $value) {
                                            if (str_starts_with($value, '_')) {
                                                return true;
                                            }
                                        }

                                        return false;
                                    })
                                    ->thenInvalid('Contexts cannot start with "_", its reserved for internal usage.')
                                ->end()
                                ->scalarPrototype()->defaultValue([])->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
