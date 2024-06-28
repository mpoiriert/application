<?php

namespace Draw\Component\Application\Feature;

interface SelfInitializeFeatureInterface extends FeatureInterface
{
    public function initialize(array $configuration): void;
}
