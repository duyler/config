<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Functional;

use Duyler\Config\FileConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestConfig extends TestCase
{
    #[Test]
    public function get_with_file(): void
    {
        $config = new FileConfig('config', 'rootfile');
        $value = $config->get('test', 'foo');

        $this->assertEquals('bar', $value);
    }
}
