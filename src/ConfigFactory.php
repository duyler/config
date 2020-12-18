<?php

declare(strict_types=1);

namespace Jine\Config;

use Jine\Config\Provider\FileConfigProvider;

class ConfigFactory
{
    public function create(string $configDir): Config
    {
        $provider = new FileConfigProvider($configDir);
        return new Config($provider);
    }

    public function createCustom(ConfigProviderInterface $provider): Config
    {
        return new Config($provider);
    }
}
