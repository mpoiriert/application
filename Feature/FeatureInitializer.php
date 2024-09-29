<?php

namespace Draw\Component\Application\Feature;

use Draw\Component\Application\Feature\Attribute\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class FeatureInitializer
{
    public function __construct(private ConfigurationRegistryInterface $configurationRegistry)
    {
    }

    public function initialize(FeatureInterface $feature): void
    {
        $configuration = $this->configurationRegistry
            ->get($feature->getName(), [])
        ;

        $properties = (new \ReflectionObject($feature))->getProperties();

        foreach ($properties as $property) {
            if (0 === \count($property->getAttributes(Config::class, ArgumentMetadata::IS_INSTANCEOF))) {
                continue;
            }

            if (!\array_key_exists($property->getName(), $configuration)) {
                return;
            }

            $property->setValue($feature, $configuration[$property->getName()]);
        }

        if ($feature instanceof SelfInitializeFeatureInterface) {
            $feature->initialize($configuration);
        }
    }
}
