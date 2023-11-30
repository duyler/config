<?php

declare(strict_types=1);

namespace Duyler\tests\unit;

use Duyler\Config\Config;
use Duyler\Config\Provider\FileConfigProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TestConfig extends TestCase
{
    private FileConfigProvider $fileConfigProvider;
    private Config $config;

    protected function setUp(): void
    {
        $this->fileConfigProvider = $this->createMock(FileConfigProvider::class);
        $this->config = new Config($this->fileConfigProvider);
        parent::setUp();
    }

    #[Test]
    public function with_file_provider(): void
    {
        $this->fileConfigProvider->method('getAll')->willReturn(['foo' => 'bar']);
        $value = $this->config->get('foo', 'foo');
        self::assertEquals('bar', $value);
    }
}
