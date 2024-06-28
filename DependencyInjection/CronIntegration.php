<?php

namespace Draw\Component\Application\DependencyInjection;

use Draw\Component\Application\Cron\CronManager;
use Draw\Component\Application\Cron\Job;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class CronIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'cron';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Application\\Cron\\',
            $directory = \dirname((new \ReflectionClass(CronManager::class))->getFileName()),
            [
                $directory.'/Job.php',
            ]
        );

        $cronManagerDefinition = $container->getDefinition(CronManager::class);
        foreach ($config['jobs'] as $jobData) {
            $jobDefinition = new Definition(
                Job::class,
                [
                    $jobData['name'],
                    $jobData['command'],
                    $jobData['expression'],
                    $jobData['enabled'],
                    $jobData['description'],
                ]
            );

            $jobDefinition->addMethodCall('setOutput', [$jobData['output']]);

            $cronManagerDefinition->addMethodCall('addJob', [$jobDefinition]);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.cron.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('jobs')
                    ->defaultValue([])
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
                        ->children()
                            ->scalarNode('name')
                                ->validate()
                                    ->ifTrue(fn ($value) => \is_int($value))
                                    ->thenInvalid('You must specify a name for the job. Can be via the attribute or the key.')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('description')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('expression')
                                ->isRequired()
                            ->end()
                            ->scalarNode('output')
                                ->defaultValue('>/dev/null 2>&1')
                            ->end()
                            ->scalarNode('command')
                                ->isRequired()
                            ->end()
                            ->booleanNode('enabled')
                                ->defaultValue(true)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
