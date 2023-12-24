<?php

declare(strict_types=1);

namespace Duyler\Config;

interface ConfigCollectorInterface
{
    public function collect(string $key, mixed $value): void;
}
