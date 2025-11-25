<?php

declare(strict_types=1);

namespace Duyler\Config\Test\Unit;

use Duyler\Config\ProjectRootFinder;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProjectRootFinderTest extends TestCase
{
    private ProjectRootFinder $finder;

    protected function setUp(): void
    {
        $this->finder = new ProjectRootFinder();
    }

    #[Test]
    public function it_should_find_project_root_by_composer_json(): void
    {
        $rootDir = $this->finder->find('composer.json', __DIR__);

        $this->assertIsString($rootDir);
        $this->assertStringEndsWith('/', $rootDir);
        $this->assertFileExists($rootDir . 'composer.json');
    }

    #[Test]
    public function it_should_throw_exception_when_root_file_not_found(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot auto-detect project dir using root file');

        $this->finder->find('non-existent-file.txt', __DIR__);
    }
}
