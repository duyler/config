<?php

declare(strict_types=1);

namespace Duyler\Config;

use RuntimeException;

final class ConfigCache
{
    private string $cacheFile;

    public function __construct(string $cacheDir)
    {
        $this->cacheFile = rtrim($cacheDir, '/') . '/config.cache.php';
    }

    public function has(): bool
    {
        return is_file($this->cacheFile) && is_readable($this->cacheFile);
    }

    /**
     * @return array<string, array<string, mixed>>
     * @throws RuntimeException
     */
    public function load(): array
    {
        if (!$this->has()) {
            throw new RuntimeException(sprintf('Cache file "%s" does not exist or is not readable', $this->cacheFile));
        }

        /** @var mixed $data */
        $data = require $this->cacheFile;

        if (!is_array($data)) {
            throw new RuntimeException(sprintf('Invalid cache file format in "%s"', $this->cacheFile));
        }

        /** @var array<string, array<string, mixed>> $validatedData */
        $validatedData = $data;
        
        return $validatedData;
    }

    /**
     * @param array<string, array<string, mixed>> $data
     * @throws RuntimeException
     */
    public function save(array $data): void
    {
        $cacheDir = dirname($this->cacheFile);

        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
            throw new RuntimeException(sprintf('Failed to create cache directory "%s"', $cacheDir));
        }

        $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";

        if (file_put_contents($this->cacheFile, $content) === false) {
            throw new RuntimeException(sprintf('Failed to write cache file "%s"', $this->cacheFile));
        }
    }

    public function clear(): bool
    {
        if ($this->has()) {
            return unlink($this->cacheFile);
        }

        return true;
    }
}
