<?php

namespace Draw\Component\Application\Tests\DependencyInjection;

use Draw\Component\Application\DependencyInjection\VersioningIntegration;
use Draw\Component\Application\Versioning\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\Versioning\EventListener\FetchRunningVersionListener;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Contracts\Application\VersionVerificationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * @property VersioningIntegration $integration
 */
#[CoversClass(VersioningIntegration::class)]
class VersioningIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new VersioningIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'versioning';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.versioning.command.update_deployed_version_command',
                    [
                        UpdateDeployedVersionCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.versioning.version_manager',
                    [
                        VersionManager::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.versioning.event_listener.fetch_running_version_listener',
                    [
                        FetchRunningVersionListener::class,
                    ],
                    function (Definition $definition): void {
                        $parameter = $definition->getArgument('$projectDirectory');

                        static::assertInstanceOf(Parameter::class, $parameter);
                        static::assertSame('kernel.project_dir', (string) $parameter);
                    }
                ),
            ],
            [
                VersionManager::class => [
                    VersionVerificationInterface::class,
                ],
            ],
        ];
    }
}
