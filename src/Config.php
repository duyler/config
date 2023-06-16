<?php

declare(strict_types=1);

namespace Duyler\Config;

readonly class Config
{
    public const PROJECT_ROOT = 'PROJECT_ROOT';

    public function __construct(private ConfigProviderInterface $configProvider, private array $env = [])
    {
    }

    public function get(
        string $configFile,
        string $configName,
        string|int|float|bool|null|array $default = null
    ): string|int|float|bool|null|array {
        $configArray = $this->configProvider->getAll($configFile);

        if (array_key_exists($configName, $configArray)) {
            return $configArray[$configName];
        }

        return $this->env(strtoupper($configName), $default);
    }

    public function env(string $key, string|bool|int|float|null $default = null): string|bool|int|float|null|array
    {
        return $this->env[$key] ?? $default;
    }
}
