<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\PhpUnitConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpUnitConfigGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class PhpUnitConfigGeneratorTest extends TestCase
{
    private PhpUnitConfigGenerator $generator;
    private ProjectContext $context;

    protected function setUp(): void
    {
        $this->generator = new PhpUnitConfigGenerator();
        $this->context = new ProjectContext(
            projectRoot: '/project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: ['/project/src'],
            testDirs: ['/project/tests'],
            excludeDirs: [],
            testSuites: ['Unit' => 'tests/Unit', 'Integration' => 'tests/Integration'],
            coverageExclude: ['src/Exception'],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameReturnsPhpunit(): void
    {
        $this->assertSame('phpunit', $this->generator->toolName());
    }

    #[Test]
    public function outputPathReturnsPhpunitXmlDist(): void
    {
        $this->assertSame('phpunit.xml.dist', $this->generator->outputPath());
    }

    #[Test]
    public function generateContainsXmlDeclaration(): void
    {
        $output = $this->generator->generate($this->context);
        $this->assertStringContainsString('<?xml version="1.0"', $output);
    }

    #[Test]
    public function generateContainsTestSuiteNames(): void
    {
        $output = $this->generator->generate($this->context);
        $this->assertStringContainsString('name="Unit"', $output);
        $this->assertStringContainsString('name="Integration"', $output);
    }

    #[Test]
    public function generateContainsSourceDirectory(): void
    {
        $output = $this->generator->generate($this->context);
        $this->assertStringContainsString('../src', $output);
    }

    #[Test]
    public function generateContainsCoverageExcludes(): void
    {
        $output = $this->generator->generate($this->context);
        $this->assertStringContainsString('../src/Exception', $output);
    }

    #[Test]
    public function generateWithEmptyTestSuitesProducesValidXml(): void
    {
        $context = new ProjectContext(
            projectRoot: '/project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: ['/project/src'],
            testDirs: ['/project/tests'],
            excludeDirs: [],
            testSuites: [],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );

        $output = $this->generator->generate($context);
        $this->assertStringContainsString('<testsuites>', $output);
    }
}
