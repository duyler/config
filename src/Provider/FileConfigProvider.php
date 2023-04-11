<?php

declare(strict_types=1);

namespace Duyler\Config\Provider;

use Duyler\Config\ConfigProviderInterface;

class FileConfigProvider implements ConfigProviderInterface
{
    private string $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function getAll(string $configName): array
    {
        $configPath = $this->configDir . '/' . str_replace('.', '/', $configName) . '.php';

        if (is_file($configPath)) {
            return include $configPath;
        }

        return [];
    }
}
