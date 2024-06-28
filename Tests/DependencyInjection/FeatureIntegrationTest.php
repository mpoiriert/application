<?php

namespace Draw\Component\Application\Tests\DependencyInjection;

use Draw\Component\Application\DependencyInjection\ConfigurationIntegration;
use Draw\Component\Application\DependencyInjection\FeatureIntegration;
use Draw\Component\Application\Feature\FeatureInitializer;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property ConfigurationIntegration $integration
 */
#[CoversClass(FeatureIntegration::class)]
class FeatureIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new FeatureIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'feature';
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
                    'draw.feature.feature_initializer',
                    [
                        FeatureInitializer::class,
                    ]
                ),
            ],
            [
            ],
            [],
        ];
    }
}
