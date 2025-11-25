<?php

declare(strict_types=1);

namespace Duyler\Config;

interface ConfigInterface
{
    public function get(string $configFile, string $configName, mixed $default = null): mixed;

    public function has(string $configFile, string $configName): bool;

    /**
     * @return array<string, mixed>
     */
    public function all(string $configFile): array;

    public function getInt(string $configFile, string $configName, int $default = 0): int;

    public function getBool(string $configFile, string $configName, bool $default = false): bool;

    public function getString(string $configFile, string $configName, string $default = ''): string;

    /**
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function getArray(string $configFile, string $configName, array $default = []): array;

    public function env(string $key, mixed $default = null, bool $raw = false): mixed;

    public function path(string $dir = ''): string;
}
