<?php

declare(strict_types=1);

namespace Duyler\Config;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class Config
{
    public const PROJECT_ROOT = 'PROJECT_ROOT';

    private array $objects = [];

    public function __construct(
        private string $configDir,
        private readonly array $env = [],
        private array $vars = [],
    ) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->configDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        $configCollector = new class () {
            public function collect(string $path, Config $config): array
            {
                return require_once $path;
            }
        };

        /**
         * @var iterable $iterator
         * @var string $path
         * @var SplFileInfo $dir
         * */
        foreach ($iterator as $path => $dir) {
            if ($dir->isFile()) {
                if ('php' === strtolower($dir->getExtension())) {
                    $configName = str_replace('/', '.', str_replace([$this->configDir . '/', '.php'], ['', ''], $path));

                    if (array_key_exists($configName, $this->vars)) {
                        continue;
                    }

                    $config = $configCollector->collect($path, $this);

                    foreach ($config as $key => $value) {
                        if (class_exists($key)) {
                            $this->objects[$key] = new $key(...$value);
                        } else {
                            $this->vars[$configName] = $config;
                        }
                    }
                }
            }
        }
    }

    public function get(
        string $configFile,
        string $configName,
        mixed $default = null,
    ): mixed {
        if (array_key_exists($configFile, $this->vars)) {
            return $this->vars[$configFile][$configName] ?? null;
        }

        $configArray = $this->readFile($configFile);

        if (array_key_exists($configName, $configArray)) {
            $this->vars[$configFile] = $configArray;
            return $configArray[$configName];
        }

        return $default;
    }

    public function env(string $key, mixed $default = null, bool $raw = false): mixed
    {
        if ($raw) {
            return $this->env[$key] ?? $default;
        }

        $value = $this->env[$key];
        return match (true) {
            'null' === $value => null,
            'true' === $value => true,
            'false' === $value => false,
            is_numeric($value) => intval($value),
            is_string($value) => $value,
            default => $default
        };
    }

    public function readFile(string $configFile): array
    {
        var_dump($configFile);
        $configPath = $this->configDir . '/' . str_replace('.', '/', $configFile) . '.php';

        $configCollector = new class () {
            public function collect(string $configPath, Config $config): array
            {
                return require $configPath;
            }
        };

        return $configCollector->collect($configPath, $this);
    }

    public function getObjects(): array
    {
        return $this->objects;
    }

    public function writeFile(string $filePath, array $data): Config
    {
        $path = explode('.', $filePath);
        $fileName = array_pop($path);

        $dirPath = $this->configDir . '/' . implode('/', $path);

        if (!empty($dirPath) && !is_dir($dirPath)) {
            mkdir($dirPath, 0o755, true);
        }

        $data = var_export($data, true);

        $data = str_replace(['array (', ')'], ['[', ']'], $data);

        $fileContent = <<<EOF
            <?php

            declare(strict_types=1);
            
            use Duyler\Config\Config;
            
            /** @var Config \$config */
            return {$data};

            EOF;

        $file = $dirPath . $fileName . '.php';

        if (is_file($file)) {
            throw new RuntimeException('File already exists: ' . $file);
        }

        file_put_contents(
            $dirPath . '/' . $fileName . '.php',
            $fileContent,
        );

        return $this;
    }
}
