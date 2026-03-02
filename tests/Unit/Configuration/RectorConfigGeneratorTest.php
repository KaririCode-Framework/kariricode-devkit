<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\RectorConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RectorConfigGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class RectorConfigGeneratorTest extends TestCase
{
    private RectorConfigGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new RectorConfigGenerator();
    }

    private function makeContext(array $rectorSets = ['LevelSetList::UP_TO_PHP_84']): ProjectContext
    {
        return new ProjectContext(
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
            rectorSets: $rectorSets,
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameReturnsRector(): void
    {
        $this->assertSame('rector', $this->generator->toolName());
    }

    #[Test]
    public function outputPathReturnsRectorPhp(): void
    {
        $this->assertSame('rector.php', $this->generator->outputPath());
    }

    #[Test]
    public function generateContainsPhpDeclaration(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('<?php', $output);
    }

    #[Test]
    public function generateContainsSourceAndTestPaths(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString("__DIR__ . '/../src'", $output);
        $this->assertStringContainsString("__DIR__ . '/../tests'", $output);
    }

    #[Test]
    public function generateContainsRectorSets(): void
    {
        $output = $this->generator->generate($this->makeContext(['LevelSetList::UP_TO_PHP_84', 'SetList::CODE_QUALITY']));
        $this->assertStringContainsString('LevelSetList::UP_TO_PHP_84', $output);
        $this->assertStringContainsString('SetList::CODE_QUALITY', $output);
    }

    #[Test]
    public function generateContainsWithPhpSets(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('withPhpSets(php84: true)', $output);
    }

    #[Test]
    public function generateContainsWithImportNames(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('withImportNames(', $output);
    }
}
