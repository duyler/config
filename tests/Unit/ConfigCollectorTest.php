<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\ConfigCollectorInterface;
use Duyler\Config\FileConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigCollectorTest extends TestCase
{
    #[Test]
    public function it_should_collect_config_values(): void
    {
        $collector = new class implements ConfigCollectorInterface {
            public array $collected = [];

            public function collect(string $key, mixed $value): void
            {
                $this->collected[$key] = $value;
            }
        };

        $config = new FileConfig(
            configDir: 'tests/Unit/fixtures/config',
            rootFile: 'composer.json',
            externalConfigCollector: $collector,
        );

        $config->get('app', 'name');

        $this->assertNotEmpty($collector->collected);
        $this->assertArrayHasKey('name', $collector->collected);
        $this->assertEquals('TestApp', $collector->collected['name']);
    }
}
