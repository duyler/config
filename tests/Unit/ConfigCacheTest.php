<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\ConfigCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConfigCacheTest extends TestCase
{
    private ConfigCache $cache;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/duyler_config_test_' . uniqid();
        $this->cache = new ConfigCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();

        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
    }

    #[Test]
    public function it_should_return_false_when_cache_not_exists(): void
    {
        $this->assertFalse($this->cache->has());
    }

    #[Test]
    public function it_should_save_and_load_cache(): void
    {
        $data = [
            'app' => ['name' => 'TestApp', 'version' => '1.0.0'],
            'database' => ['host' => 'localhost', 'port' => 3306],
        ];

        $this->cache->save($data);

        $this->assertTrue($this->cache->has());

        $loaded = $this->cache->load();

        $this->assertEquals($data, $loaded);
    }

    #[Test]
    public function it_should_clear_cache(): void
    {
        $data = ['test' => ['key' => 'value']];

        $this->cache->save($data);
        $this->assertTrue($this->cache->has());

        $result = $this->cache->clear();

        $this->assertTrue($result);
        $this->assertFalse($this->cache->has());
    }

    #[Test]
    public function it_should_throw_exception_when_loading_non_existent_cache(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache file');

        $this->cache->load();
    }

    #[Test]
    public function it_should_return_true_when_clearing_non_existent_cache(): void
    {
        $result = $this->cache->clear();

        $this->assertTrue($result);
    }
}
