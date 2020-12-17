<?php

declare(strict_types=1);

namespace Jine\Config;

class Config
{
    private string $rootPath;

    private string $configDir;

    private array $configures = [];

    public function __construct(string $rootPath, string $configDir)
    {
        $this->rootPath = $rootPath;
        $this->configDir = $rootPath . '/' . $configDir;
    }

    public function getAll(string $configName) : array
    {
        if (array_key_exists($configName, $this->configures)) {
            return $this->configures[$configName];
        }

        $configPath = $this->configDir . '/' . str_replace('.', '/', $configName) . '.php';

        if (is_file($configPath)) {
            $this->configures[$configName] = include $configPath;
            return $this->configures[$configName];
        }
        return [];
    }

    public function get(string $configFile, string $configName)
    {
        $configArray = $this->getAll($configFile);

        if (array_key_exists($configName, $configArray)) {
            return $configArray[$configName];
        }
    }
}
