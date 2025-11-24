<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\EnvironmentResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EnvironmentResolverTest extends TestCase
{
    private EnvironmentResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new EnvironmentResolver(__DIR__ . '/../..');
    }

    #[Test]
    public function it_should_get_environment_variable(): void
    {
        $_ENV['TEST_VAR'] = 'test_value';

        $value = $this->resolver->get('TEST_VAR');

        $this->assertEquals('test_value', $value);
    }

    #[Test]
    public function it_should_return_default_when_variable_not_found(): void
    {
        $value = $this->resolver->get('NON_EXISTENT_VAR', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    #[Test]
    public function it_should_cast_boolean_true(): void
    {
        $_ENV['TEST_BOOL_TRUE'] = 'true';

        $value = $this->resolver->get('TEST_BOOL_TRUE');

        $this->assertIsBool($value);
        $this->assertTrue($value);
    }

    #[Test]
    public function it_should_cast_boolean_false(): void
    {
        $_ENV['TEST_BOOL_FALSE'] = 'false';

        $value = $this->resolver->get('TEST_BOOL_FALSE');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }

    #[Test]
    public function it_should_cast_null(): void
    {
        $_ENV['TEST_NULL'] = 'null';

        $value = $this->resolver->get('TEST_NULL');

        $this->assertNull($value);
    }

    #[Test]
    public function it_should_cast_integer(): void
    {
        $_ENV['TEST_INT'] = '123';

        $value = $this->resolver->get('TEST_INT');

        $this->assertIsInt($value);
        $this->assertEquals(123, $value);
    }

    #[Test]
    public function it_should_cast_float(): void
    {
        $_ENV['TEST_FLOAT'] = '123.45';

        $value = $this->resolver->get('TEST_FLOAT');

        $this->assertIsFloat($value);
        $this->assertEquals(123.45, $value);
    }

    #[Test]
    public function it_should_return_raw_value_when_requested(): void
    {
        $_ENV['TEST_RAW'] = 'true';

        $value = $this->resolver->get('TEST_RAW', null, true);

        $this->assertEquals('true', $value);
        $this->assertIsString($value);
    }

    #[Test]
    public function it_should_return_default_for_empty_string(): void
    {
        $_ENV['TEST_EMPTY'] = '';

        $value = $this->resolver->get('TEST_EMPTY', 'default');

        $this->assertEquals('default', $value);
    }
}
