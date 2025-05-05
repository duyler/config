<?php

declare(strict_types=1);

namespace Duyler\Config;

interface ConfigInterface
{
    public function get(string $configFile, string $configName, mixed $default = null): mixed;
    public function env(string $key, mixed $default = null, bool $raw = false): mixed;
    public function path(string $dir = ''): string;
}
