<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\ConfigFileLoader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConfigFileLoaderTest extends TestCase
{
    private ConfigFileLoader $loader;
    private string $testConfigPath;

    protected function setUp(): void
    {
        $this->loader = new ConfigFileLoader();
        $this->testConfigPath = __DIR__ . '/fixtures/config';
    }

    #[Test]
    public function it_should_load_all_configs(): void
    {
        $configs = $this->loader->loadAll($this->testConfigPath, null);

        $this->assertIsArray($configs);
        $this->assertArrayHasKey('app', $configs);
        $this->assertIsArray($configs['app']);
        $this->assertEquals('TestApp', $configs['app']['name']);
    }

    #[Test]
    public function it_should_load_single_config(): void
    {
        $config = $this->loader->load('app', $this->testConfigPath);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('name', $config);
        $this->assertEquals('TestApp', $config['name']);
    }

    #[Test]
    public function it_should_return_empty_array_for_non_existent_file(): void
    {
        $config = $this->loader->load('non_existent', $this->testConfigPath);

        $this->assertIsArray($config);
        $this->assertEmpty($config);
    }

    #[Test]
    public function it_should_throw_exception_for_non_existent_directory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config directory');

        $this->loader->loadAll('/non/existent/path');
    }
}
