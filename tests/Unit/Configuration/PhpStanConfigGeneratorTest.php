<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\PhpStanConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpStanConfigGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class PhpStanConfigGeneratorTest extends TestCase
{
    private PhpStanConfigGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new PhpStanConfigGenerator();
    }

    private function makeContext(int $level = 9, array $excludeDirs = []): ProjectContext
    {
        return new ProjectContext(
            projectRoot: '/project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: $level,
            psalmLevel: 3,
            sourceDirs: ['/project/src'],
            testDirs: ['/project/tests'],
            excludeDirs: $excludeDirs,
            testSuites: [],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameReturnsPhpstan(): void
    {
        $this->assertSame('phpstan', $this->generator->toolName());
    }

    #[Test]
    public function outputPathReturnsPhpstanNeon(): void
    {
        $this->assertSame('phpstan.neon', $this->generator->outputPath());
    }

    #[Test]
    public function generateContainsLevel(): void
    {
        $output = $this->generator->generate($this->makeContext(level: 8));
        $this->assertStringContainsString('level: 8', $output);
    }

    #[Test]
    public function generateContainsSourcePath(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('- ../src', $output);
    }

    #[Test]
    public function generateContainsExcludePathsWhenSet(): void
    {
        $output = $this->generator->generate($this->makeContext(excludeDirs: ['src/Contract']));
        $this->assertStringContainsString('excludePaths:', $output);
        $this->assertStringContainsString('- ../src/Contract', $output);
    }

    #[Test]
    public function generateOmitsExcludePathsWhenEmpty(): void
    {
        $output = $this->generator->generate($this->makeContext(excludeDirs: []));
        $this->assertStringNotContainsString('excludePaths:', $output);
    }

    #[Test]
    public function generateContainsTmpDir(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('tmpDir: build/.phpstan', $output);
    }
}
