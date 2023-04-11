<?php

declare(strict_types=1);

namespace Duyler\Config;

class Config
{
    private ConfigProviderInterface $provider;

    public function __construct(ConfigProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function get(string $configFile, string $configName): string | int | float | bool | null
    {
        $configArray = $this->provider->getAll($configFile);

        if (array_key_exists($configName, $configArray)) {
            return $configArray[$configName];
        }

        return null;
    }
}
