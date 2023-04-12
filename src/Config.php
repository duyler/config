<?php

declare(strict_types=1);

namespace Duyler\Config;

readonly class Config
{
    public function __construct(private ConfigProviderInterface $provider)
    {
    }

    public function get(
        string $configFile,
        string $configName,
        string|int|float|bool|null $default = null
    ): string|int|float|bool|null {
        $configArray = $this->provider->getAll($configFile);

        if (array_key_exists($configName, $configArray)) {
            return $configArray[$configName];
        }

        return $default;
    }
}
