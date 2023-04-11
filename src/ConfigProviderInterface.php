<?php

declare(strict_types=1);

namespace Duyler\Config;

interface ConfigProviderInterface
{
    public function getAll(string $configName): array;
}
