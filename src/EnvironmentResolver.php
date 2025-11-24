<?php

declare(strict_types=1);

namespace Duyler\Config;

use Dotenv\Dotenv;

final class EnvironmentResolver
{
    /** @var array<string, mixed> */
    private array $env;

    public function __construct(string $projectRootDir)
    {
        $dotenv = Dotenv::createImmutable($projectRootDir);
        $this->env = $dotenv->safeLoad();
    }

    public function get(string $key, mixed $default = null, bool $raw = false): mixed
    {
        $this->env = $this->env + $_ENV;

        if (!array_key_exists($key, $this->env) || $this->env[$key] === null || $this->env[$key] === '') {
            return $default;
        }

        $value = $this->env[$key];

        if ($raw || !is_string($value)) {
            return $value;
        }

        return match ($value) {
            'null' => null,
            'true' => true,
            'false' => false,
            default => $this->castNumericValue($value),
        };
    }

    private function castNumericValue(string $value): string|int|float
    {
        if (!is_numeric($value)) {
            return $value;
        }

        if (str_contains($value, '.')) {
            return (float) $value;
        }

        return (int) $value;
    }
}
