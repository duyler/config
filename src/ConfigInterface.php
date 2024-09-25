<?php

declare(strict_types=1);

namespace Duyler\Config;

interface ConfigInterface
{
    public const string PROJECT_ROOT = 'PROJECT_ROOT';
    public function get(string $configFile, string $configName, mixed $default = null): mixed;
    public function env(string $key, mixed $default = null, bool $raw = false): mixed;
    public function writeFile(string $filePath, array $data): FileConfig;
    public function path(string $dir = ''): string;
}
