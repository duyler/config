![build](https://github.com/duyler/config/workflows/build/badge.svg)
# Duyler Config

### Description
Duyler Config is a powerful and flexible configuration management library for PHP applications. It provides a simple and intuitive way to handle configuration files and environment variables in your projects.

### Features
- 🔄 Recursive configuration file loading
- 🌍 Environment variables support with .env files
- 💾 Configuration caching for better performance
- 🔒 Type-safe configuration values
- 🛠 Extensible through interfaces
- 🚀 PHP 8.3+ support

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

// Get environment variable
$env = $config->env('APP_ENV', 'production');

```

### Requirements
- PHP 8.3 or higher
- Composer

### License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
