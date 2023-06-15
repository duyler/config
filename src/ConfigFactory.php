<?php

declare(strict_types=1);

namespace Duyler\Config;

use Duyler\Config\Provider\FileConfigProvider;

class ConfigFactory
{
    public function create(string $configDir, array $env = []): Config
    {
        return $this->createCustom(new FileConfigProvider($configDir), $env);
    }

    public function createCustom(ConfigProviderInterface $provider, array $env = []): Config
    {
        return new Config($provider, $env);
    }
}
