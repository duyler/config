<?php

declare(strict_types=1);

namespace Duyler\Config;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

final class ConfigFileLoader
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws RuntimeException
     */
    public function loadAll(string $configPath, mixed $configContext = null): array
    {
        if (!is_dir($configPath)) {
            throw new RuntimeException(sprintf('Config directory "%s" does not exist', $configPath));
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($configPath, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD,
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Cannot read config directory "%s": %s', $configPath, $e->getMessage()),
                0,
                $e,
            );
        }

        $configs = [];

        /**
         * @var iterable $iterator
         * @var string $path
         * @var SplFileInfo $file
         */
        foreach ($iterator as $path => $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $configName = $this->getConfigName($path, $configPath);

            if (array_key_exists($configName, $configs)) {
                continue;
            }

            try {
                $configs[$configName] = $this->loadFile($path, $configContext);
            } catch (Throwable $e) {
                if ($configContext === null) {
                    continue;
                }

                throw new RuntimeException(
                    sprintf('Error loading config file "%s": %s', $path, $e->getMessage()),
                    0,
                    $e,
                );
            }
        }

        return $configs;
    }

    /**
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function load(string $configFile, string $configPath, mixed $configContext = null): array
    {
        $filePath = $configPath . '/' . str_replace('.', '/', $configFile) . '.php';

        if (!is_file($filePath)) {
            return [];
        }

        if (!is_readable($filePath)) {
            throw new RuntimeException(sprintf('Config file "%s" is not readable', $filePath));
        }

        try {
            return $this->loadFile($filePath, $configContext);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Error loading config file "%s": %s', $filePath, $e->getMessage()),
                0,
                $e,
            );
        }
    }

    /**
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    private function loadFile(string $path, mixed $config = null): array
    {
        /**
         * @return mixed
         */
        $loader = static function (string $filePath, mixed $config): mixed {
            /** @var mixed $result */
            $result = require $filePath;
            return $result;
        };
        
        $result = $loader($path, $config);

        if (!is_array($result)) {
            throw new RuntimeException(
                sprintf('Config file "%s" must return an array, %s returned', $path, get_debug_type($result)),
            );
        }

        /** @var array<string, mixed> $validatedResult */
        $validatedResult = $result;
        
        return $validatedResult;
    }

    private function getConfigName(string $path, string $configPath): string
    {
        return str_replace(
            '/',
            '.',
            str_replace([$configPath . '/', '.php'], ['', ''], $path),
        );
    }
}
