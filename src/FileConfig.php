<?php

declare(strict_types=1);

namespace Duyler\Config;

use Dotenv\Dotenv;
use FilesystemIterator;
use LogicException;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class FileConfig implements ConfigInterface
{
    private array $mainLog;
    private array $repeatedLog;
    private string $projectRootDir;
    private string $configDir;
    private array $env;
    private array $vars = [];

    public function __construct(
        string $configDir,
        private readonly string $rootFile,
        private ?ConfigCollectorInterface $externalConfigCollector = null,
    ) {
        $this->projectRootDir = $this->getRootDir();
        $this->configDir = $this->projectRootDir . $configDir;

        $env = Dotenv::createImmutable($this->projectRootDir);
        $this->env = $env->safeLoad() + $_ENV;

        $this->repeatedLog = ['named' => [], 'index' => []];
        $this->mainLog = ['named' => [], 'index' => []];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->configDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD,
        );

        $configCollector = new class {
            public function collect(string $path, FileConfig $config): array
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
                        $this->vars[$configName] = $config;
                        $this->externalConfigCollector?->collect($key, $value);
                    }
                }
            }
        }

        foreach ($this->repeatedLog['named'] as $configFile => $configName) {
            $config = $this->fakeReadFile($configFile);
            foreach ($config as $key => $value) {
                $this->vars[$configFile] = $config;
                $this->externalConfigCollector?->collect($key, $value);
            }
        }

        $this->repeatedLog = ['named' => [], 'index' => []];
        $this->mainLog = ['named' => [], 'index' => []];
    }

    private function getRootDir(): string
    {
        $dir = dirname(__DIR__);

        while (!is_file($dir . '/' . $this->rootFile)) {

            $dir = dirname($dir);

            if (!is_dir($dir)) {
                throw new LogicException('Cannot auto-detect project dir');
            }
        }

        return $dir . '/';
    }

    #[Override]
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

    private function readFile(string $configFile): array
    {
        $configPath = $this->configDir . '/' . str_replace('.', '/', $configFile) . '.php';

        if (is_file($configFile) === false) {
            return [];
        }

        $configCollector = new class {
            public function collect(string $configPath, FileConfig $config): array
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
            public function __construct(private FileConfig $config, private array $repeatedLog, private array $vars) {}
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

        $configCollector = new class {
            public function collect(string $configPath, mixed $config): array
            {
                return require $configPath;
            }
        };

        return $configCollector->collect($configPath, $fakeConfig);
    }

    #[Override]
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
            default => $default,
        };
    }

    #[Override]
    public function writeFile(string $filePath, array $data): FileConfig
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
            
            use Duyler\Config\FileConfig;
            
            /**
             * @var FileConfig \$config
             * @var string \$path
             */
            return {$data};

            EOF;

        $file = $dirPath . '/' . $fileName . '.php';

        if (is_file($file)) {
            throw new RuntimeException('File already exists: ' . $file);
        }

        file_put_contents(
            $file,
            $fileContent,
        );

        return $this;
    }

    #[Override]
    public function path(string $dir = ''): string
    {
        return rtrim($this->projectRootDir, '/') . '/' . trim($dir, '/');
    }
}
