<?php

namespace Draw\Component\Application\Feature;

interface SelfInitializeFeatureInterface
{
    public function initialize(array $configuration): void;
}
