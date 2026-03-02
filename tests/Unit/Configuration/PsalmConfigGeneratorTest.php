<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\PsalmConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PsalmConfigGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class PsalmConfigGeneratorTest extends TestCase
{
    private PsalmConfigGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new PsalmConfigGenerator();
    }

    private function makeContext(int $psalmLevel = 3): ProjectContext
    {
        return new ProjectContext(
            projectRoot: '/project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: $psalmLevel,
            sourceDirs: ['/project/src'],
            testDirs: ['/project/tests'],
            excludeDirs: [],
            testSuites: [],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameReturnsPsalm(): void
    {
        $this->assertSame('psalm', $this->generator->toolName());
    }

    #[Test]
    public function outputPathReturnsPsalmXml(): void
    {
        $this->assertSame('psalm.xml', $this->generator->outputPath());
    }

    #[Test]
    public function generateContainsXmlDeclaration(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('<?xml', $output);
    }

    #[Test]
    public function generateContainsErrorLevel(): void
    {
        $output = $this->generator->generate($this->makeContext(psalmLevel: 5));
        $this->assertStringContainsString('errorLevel="5"', $output);
    }

    #[Test]
    public function generateContainsSourceDirectory(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('directory name="../src"', $output);
    }

    #[Test]
    public function generateIgnoresVendorDirectory(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('directory name="../vendor"', $output);
    }

    #[Test]
    public function generateContainsCacheDirectory(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('cacheDirectory="build/.psalm"', $output);
    }
}
