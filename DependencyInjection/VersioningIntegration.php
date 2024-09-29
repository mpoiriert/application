<?php

namespace Draw\Component\Application\DependencyInjection;

use Draw\Component\Application\Versioning\EventListener\FetchRunningVersionListener;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;

class VersioningIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'versioning';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\Component\Application\Versioning\\',
            \dirname((new \ReflectionClass(VersionManager::class))->getFileName())
        );

        $container
            ->getDefinition(FetchRunningVersionListener::class)
            ->setArgument('$projectDirectory', new Parameter('kernel.project_dir'))
        ;

        $container->setAlias(VersionVerificationInterface::class, VersionManager::class);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.versioning.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
