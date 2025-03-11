<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Functional;

use Duyler\Config\FileConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestConfig extends TestCase
{
    private FileConfig $config;

    #[Test]
    public function get_with_file(): void
    {
        $this->config = new FileConfig(__DIR__ . '/Support/config', [], [], __DIR__ . '/Support');
        $value = $this->config->get('test', 'foo');

        self::assertEquals('bar', $value);
    }
}
