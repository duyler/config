[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=duyler_config&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=duyler_config)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=duyler_config&metric=coverage)](https://sonarcloud.io/summary/new_code?id=duyler_config)
[![Psalm Type Coverage](https://shepherd.dev/github/duyler/config/coverage.svg)](https://shepherd.dev/github/duyler/config)
![PHP Version](https://img.shields.io/packagist/dependency-v/duyler/config/php?version=dev-main)
# Duyler Config

### Description
Duyler Config is a powerful and flexible configuration management library for PHP applications. It provides a simple and intuitive way to handle configuration files and environment variables in your projects.

### Features
- Recursive configuration file loading
- Environment variables support with .env files
- Configuration caching for production environments
- Type-safe configuration values with typed getters
- Circular dependency detection
- Extensible through interfaces
- Clean architecture with SOLID principles
- PHP 8.4+ support

### Installation
```bash
composer require duyler/config
```

### Basic Usage
```php
use Duyler\Config\FileConfig;

// Initialize config
$config = new FileConfig(
    configDir: 'config',
    rootFile: 'composer.json'
);

// Get configuration value
$value = $config->get('app', 'name', 'default');

// Check if config key exists
if ($config->has('app', 'name')) {
    // ...
}

// Get all values from config file
$allConfig = $config->all('app');

// Type-safe getters
$timeout = $config->getInt('app', 'timeout', 30);
$debug = $config->getBool('app', 'debug', false);
$name = $config->getString('app', 'name', 'MyApp');
$servers = $config->getArray('app', 'servers', []);

// Get environment variable
$env = $config->env('APP_ENV', 'production');

// Get project path
$path = $config->path('storage/logs');
```

### Advanced Usage

#### Configuration Caching

For production environments, you can enable configuration caching:

```php
$config = new FileConfig(
    configDir: 'config',
    rootFile: 'composer.json',
    cacheDir: 'var/cache',
    useCache: true
);

// Warm up cache (optional, useful for deployment)
$config->warmup();

// Clear cache when needed
$config->clearCache();
```

#### Cross-Config References

Configuration files can reference values from other configs:

```php
// config/app.php
return [
    'name' => 'MyApp',
    'database' => $config->get('database', 'default', 'mysql'),
];

// config/database.php
return [
    'default' => 'postgresql',
    'connections' => [
        'mysql' => ['host' => 'localhost'],
        'postgresql' => ['host' => 'localhost'],
    ],
];
```

The library automatically handles circular dependencies.

### Requirements
- PHP 8.4 or higher
- Composer

### Architecture

The library follows clean architecture principles and SOLID design:

- **ProjectRootFinder** - Locates project root directory
- **ConfigFileLoader** - Loads configuration files from filesystem
- **EnvironmentResolver** - Handles environment variables
- **ConfigCache** - Manages configuration caching
- **FileConfig** - Main facade for configuration access

### License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
