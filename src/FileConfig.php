<?php

declare(strict_types=1);

namespace Duyler\Config;

use Override;
use RuntimeException;

final class FileConfig implements ConfigInterface
{
    private string $projectRootDir;
    private string $configPath;
    private ProjectRootFinder $rootFinder;
    private ConfigFileLoader $fileLoader;
    private EnvironmentResolver $envResolver;

    /** @var array<string, array<string, mixed>> */
    private array $vars = [];

    /** @var array<string, bool> */
    private array $loading = [];

    /**
     * @param string $configDir
     * @param string $rootFile
     * @param ConfigCollectorInterface|null $externalConfigCollector
     * @param string|null $cacheDir
     * @param bool $useCache
     */
    public function __construct(
        private string $configDir,
        private readonly string $rootFile,
        private ?ConfigCollectorInterface $externalConfigCollector = null,
        private ?string $cacheDir = null,
        private bool $useCache = false,
    ) {
        $this->rootFinder = new ProjectRootFinder();
        $this->fileLoader = new ConfigFileLoader();

        $this->projectRootDir = $this->rootFinder->find($this->rootFile, dirname(__DIR__));
        $this->configPath = $this->projectRootDir . $configDir;

        $this->envResolver = new EnvironmentResolver($this->projectRootDir);

        if ($this->useCache && $this->cacheDir !== null) {
            $cache = new ConfigCache($this->cacheDir);

            if ($cache->has()) {
                $this->vars = $cache->load();
                return;
            }
        }

        $this->loadAllConfigs();
    }

    private function loadAllConfigs(): void
    {
        $configs = $this->fileLoader->loadAll($this->configPath, $this);

        foreach ($configs as $configName => $config) {
            if (!array_key_exists($configName, $this->vars)) {
                $this->vars[$configName] = $config;

                foreach ($config as $key => $value) {
                    $this->externalConfigCollector?->collect($key, $value);
                }
            }
        }
    }

    /**
     * @throws RuntimeException
     */
    #[Override]
    public function get(string $configFile, string $configName, mixed $default = null): mixed
    {
        if (array_key_exists($configFile, $this->vars) && array_key_exists($configName, $this->vars[$configFile])) {
            return $this->vars[$configFile][$configName];
        }

        if (!array_key_exists($configFile, $this->vars)) {
            $this->loadConfigFile($configFile);
        }

        return $this->vars[$configFile][$configName] ?? $default;
    }

    private function loadConfigFile(string $configFile): void
    {
        if (array_key_exists($configFile, $this->vars)) {
            return;
        }

        if (isset($this->loading[$configFile])) {
            $this->vars[$configFile] = [];
            return;
        }

        $this->loading[$configFile] = true;
        $this->vars[$configFile] = [];

        try {
            $configArray = $this->fileLoader->load($configFile, $this->configPath, $this);

            $this->vars[$configFile] = $configArray;

            foreach ($configArray as $key => $value) {
                $this->externalConfigCollector?->collect($key, $value);
            }
        } finally {
            unset($this->loading[$configFile]);
        }
    }

    #[Override]
    public function has(string $configFile, string $configName): bool
    {
        if (!array_key_exists($configFile, $this->vars)) {
            $this->loadConfigFile($configFile);
        }

        return array_key_exists($configName, $this->vars[$configFile] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function all(string $configFile): array
    {
        if (!array_key_exists($configFile, $this->vars)) {
            $this->loadConfigFile($configFile);
        }

        return $this->vars[$configFile] ?? [];
    }

    #[Override]
    public function getInt(string $configFile, string $configName, int $default = 0): int
    {
        $value = $this->get($configFile, $configName, $default);

        return is_int($value) ? $value : (int) $value;
    }

    #[Override]
    public function getBool(string $configFile, string $configName, bool $default = false): bool
    {
        $value = $this->get($configFile, $configName, $default);

        return is_bool($value) ? $value : (bool) $value;
    }

    #[Override]
    public function getString(string $configFile, string $configName, string $default = ''): string
    {
        $value = $this->get($configFile, $configName, $default);

        return is_string($value) ? $value : (string) $value;
    }

    /**
     * @param array<mixed> $default
     * @return array<mixed>
     */
    #[Override]
    public function getArray(string $configFile, string $configName, array $default = []): array
    {
        $value = $this->get($configFile, $configName, $default);

        return is_array($value) ? $value : $default;
    }

    #[Override]
    public function env(string $key, mixed $default = null, bool $raw = false): mixed
    {
        return $this->envResolver->get($key, $default, $raw);
    }

    #[Override]
    public function path(string $dir = ''): string
    {
        return rtrim($this->projectRootDir, '/') . '/' . trim($dir, '/');
    }

    public function configDir(): string
    {
        return $this->configDir;
    }

    public function rootFile(): string
    {
        return $this->rootFile;
    }

    public function clearCache(): bool
    {
        if ($this->cacheDir === null) {
            return false;
        }

        $cache = new ConfigCache($this->cacheDir);
        return $cache->clear();
    }

    public function warmup(): void
    {
        if ($this->useCache && $this->cacheDir !== null) {
            $cache = new ConfigCache($this->cacheDir);

            if ($cache->has()) {
                return;
            }
        }

        $this->loadAllConfigs();

        if ($this->useCache && $this->cacheDir !== null) {
            $cache = new ConfigCache($this->cacheDir);
            $cache->save($this->vars);
        }
    }
}
