<?php

declare(strict_types=1);

namespace Duyler\Config;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class Config implements ConfigInterface
{
    public const PROJECT_ROOT = 'PROJECT_ROOT';

    private array $objects = [];
    private array $mainLog;
    private array $repeatedLog;

    public function __construct(
        private string $configDir,
        private readonly array $env = [],
        private array $vars = [],
    ) {

        $this->repeatedLog = ['named' => [], 'index' => []];
        $this->mainLog = ['named' => [], 'index' => []];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->configDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        $configCollector = new class () {
            public function collect(string $path, Config $config): array
            {
                return require $path;
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

        foreach ($this->repeatedLog['named'] as $configFile => $configName) {
            $config = $this->fakeReadFile($configFile);
            foreach ($config as $key => $value) {
                if (class_exists($key)) {
                    $this->objects[$key] = new $key(...$value);
                } else {
                    $this->vars[$configFile] = $config;
                }
            }
        }

        $this->repeatedLog = ['named' => [], 'index' => []];
        $this->mainLog = ['named' => [], 'index' => []];
    }

    public function get(string $configFile, string $configName, mixed $default = null): mixed
    {
        if (array_key_exists($configFile, $this->vars)) {
            return $this->vars[$configFile][$configName] ?? null;
        }

        if (in_array($configName, $this->mainLog['named']) || in_array($configName, $this->mainLog['index'])) {
            $this->repeatedLog['named'][$configFile] = $configName;
            $this->repeatedLog['index'][] = $configFile . '.' . $configName;
        } else {
            $this->mainLog['named'][$configFile] = $configName;
            $this->mainLog['index'][] = $configFile . '.' . $configName;
        }

        if (count($this->repeatedLog['named']) === count($this->mainLog['named'])
            || count($this->repeatedLog['index']) === count($this->mainLog['index'])
        ) {
            return $default;
        } else {
            $configArray = $this->readFile($configFile);
        }

        if (array_key_exists($configName, $configArray)) {
            $this->vars[$configFile] = $configArray;
            return $configArray[$configName];
        }

        return $default;
    }

    public function readFile(string $configFile): array
    {
        $configPath = $this->configDir . '/' . str_replace('.', '/', $configFile) . '.php';

        $configCollector = new class () {
            public function collect(string $configPath, Config $config): array
            {
                return require $configPath;
            }
        };

        return $configCollector->collect($configPath, $this);
    }

    private function fakeReadFile(string $configFile): array
    {
        $configPath = $this->configDir . '/' . str_replace('.', '/', $configFile) . '.php';

        $fakeConfig = new class ($this, $this->repeatedLog['named'], $this->vars) {
            public function __construct(private Config $config, private array $repeatedLog, private array $vars) {}
            public function get(string $configFile, string $configName, mixed $default = null): mixed
            {
                if (in_array($configName, $this->repeatedLog)) {
                    return $this->vars[$configFile][$configName] ?? $default;
                }

                return $this->config->get($configFile, $configName, $default);
            }

            public function env(string $key, mixed $default = null, bool $raw = false): mixed
            {
                return $this->config->env($key, $default, $raw);
            }
        };

        $configCollector = new class () {
            public function collect(string $configPath, mixed $config): array
            {
                return require $configPath;
            }
        };

        return $configCollector->collect($configPath, $fakeConfig);
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
