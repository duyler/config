<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\FileConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CircularDependencyTest extends TestCase
{
    #[Test]
    public function it_should_not_loop_infinitely(): void
    {
        $config = new FileConfig(
            configDir: 'tests/Unit/fixtures/config',
            rootFile: 'composer.json',
        );

        echo "\n=== Getting config_a.value ===\n";
        $valueA = $config->get('config_a', 'value');
        echo "Got: " . $valueA . "\n";

        echo "\n=== Getting config_a.reference ===\n";
        $referenceA = $config->get('config_a', 'reference');
        echo "Got: " . $referenceA . "\n";

        $this->assertEquals('from_a', $valueA);
        $this->assertEquals('from_b', $referenceA);
    }
}
