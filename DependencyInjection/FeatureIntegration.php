<?php

namespace Draw\Component\Application\DependencyInjection;

use Draw\Component\Application\Feature\FeatureInitializer;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class FeatureIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'feature';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Application\\Feature\\',
            \dirname((new \ReflectionClass(FeatureInitializer::class))->getFileName())
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.feature.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
