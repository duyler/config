<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\FileConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FileConfigTest extends TestCase
{
    private string $testConfigDir;
    private FileConfig $config;

    protected function setUp(): void
    {
        $this->testConfigDir = 'tests/Unit/fixtures/config';
        $this->config = new FileConfig(
            configDir: $this->testConfigDir,
            rootFile: 'composer.json',
        );
    }

    #[Test]
    public function it_should_get_config_value(): void
    {
        $value = $this->config->get('app', 'name');
        $this->assertEquals('TestApp', $value);
    }

    #[Test]
    public function it_should_return_default_value_when_config_not_found(): void
    {
        $value = $this->config->get('non_existent', 'key', 'default');
        $this->assertEquals('default', $value);
    }

    #[Test]
    public function it_should_get_environment_variable(): void
    {
        $_ENV['TEST_ENV'] = 'test_value';
        $value = $this->config->env('TEST_ENV');
        $this->assertEquals('test_value', $value);
    }

    #[Test]
    public function it_should_return_default_value_when_env_not_found(): void
    {
        $value = $this->config->env('NON_EXISTENT_ENV', 'default');
        $this->assertEquals('default', $value);
    }

    #[Test]
    public function it_should_convert_env_values_to_proper_types(): void
    {
        $_ENV['TEST_BOOL'] = 'true';
        $_ENV['TEST_INT'] = '42';
        $_ENV['TEST_NULL'] = 'null';

        $this->assertTrue($this->config->env('TEST_BOOL'));
        $this->assertEquals(42, $this->config->env('TEST_INT'));
        $this->assertNull($this->config->env('TEST_NULL'));
    }

    #[Test]
    public function it_should_get_raw_env_value(): void
    {
        $_ENV['TEST_RAW'] = 'true';
        $value = $this->config->env('TEST_RAW', null, true);
        $this->assertEquals('true', $value);
    }

    #[Test]
    public function it_should_get_project_path(): void
    {
        $path = $this->config->path();
        $this->assertIsString($path);
        $this->assertNotEmpty($path);
    }

    #[Test]
    public function it_should_get_subdirectory_path(): void
    {
        $subDir = 'test';
        $path = $this->config->path($subDir);
        $this->assertStringEndsWith($subDir, $path);
    }

    #[Test]
    public function it_should_cache_config_values(): void
    {
        // First call should load from file
        $value1 = $this->config->get('app', 'name');

        // Second call should use cached value
        $value2 = $this->config->get('app', 'name');

        $this->assertEquals($value1, $value2);
    }

    #[Test]
    public function it_should_check_if_config_key_exists(): void
    {
        $this->assertTrue($this->config->has('app', 'name'));
        $this->assertFalse($this->config->has('app', 'non_existent'));
        $this->assertFalse($this->config->has('non_existent', 'key'));
    }

    #[Test]
    public function it_should_get_all_config_values(): void
    {
        $all = $this->config->all('app');

        $this->assertIsArray($all);
        $this->assertArrayHasKey('name', $all);
        $this->assertArrayHasKey('version', $all);
        $this->assertEquals('TestApp', $all['name']);
    }

    #[Test]
    public function it_should_get_empty_array_for_non_existent_config_file(): void
    {
        $all = $this->config->all('non_existent');

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    #[Test]
    public function it_should_get_integer_value(): void
    {
        $value = $this->config->getInt('app', 'debug', 0);

        $this->assertIsInt($value);
        $this->assertEquals(1, $value);
    }

    #[Test]
    public function it_should_get_integer_default_value(): void
    {
        $value = $this->config->getInt('app', 'non_existent', 42);

        $this->assertEquals(42, $value);
    }

    #[Test]
    public function it_should_get_boolean_value(): void
    {
        $value = $this->config->getBool('app', 'debug', false);

        $this->assertIsBool($value);
        $this->assertTrue($value);
    }

    #[Test]
    public function it_should_get_boolean_default_value(): void
    {
        $value = $this->config->getBool('app', 'non_existent', false);

        $this->assertFalse($value);
    }

    #[Test]
    public function it_should_get_string_value(): void
    {
        $value = $this->config->getString('app', 'name', 'default');

        $this->assertIsString($value);
        $this->assertEquals('TestApp', $value);
    }

    #[Test]
    public function it_should_get_string_default_value(): void
    {
        $value = $this->config->getString('app', 'non_existent', 'default_string');

        $this->assertEquals('default_string', $value);
    }

    #[Test]
    public function it_should_get_array_value(): void
    {
        $config = new FileConfig(
            configDir: $this->testConfigDir,
            rootFile: 'composer.json',
        );

        $all = $config->all('app');

        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    #[Test]
    public function it_should_get_array_default_value(): void
    {
        $value = $this->config->getArray('app', 'non_existent', ['default']);

        $this->assertEquals(['default'], $value);
    }

    #[Test]
    public function it_should_handle_circular_dependencies(): void
    {
        $valueA = $this->config->get('config_a', 'value');
        $valueB = $this->config->get('config_b', 'value');

        $this->assertEquals('from_a', $valueA);
        $this->assertEquals('from_b', $valueB);

        $referenceFromA = $this->config->get('config_a', 'reference');
        $referenceFromB = $this->config->get('config_b', 'reference');

        $this->assertEquals('from_b', $referenceFromA);
        $this->assertEquals('default_from_a', $referenceFromB);
    }
}
