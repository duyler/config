<?php

declare(strict_types=1);

namespace Jine\Config;

interface ConfigProviderInterface
{
    public function getAll(string $configName): array;
}
