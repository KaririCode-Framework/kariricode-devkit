<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\CsFixerConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsFixerConfigGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class CsFixerConfigGeneratorTest extends TestCase
{
    private CsFixerConfigGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new CsFixerConfigGenerator();
    }

    private function makeContext(array $rules = ['@PSR12' => true]): ProjectContext
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
            csFixerRules: $rules,
            rectorSets: [],
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameReturnsCsFixer(): void
    {
        $this->assertSame('cs-fixer', $this->generator->toolName());
    }

    #[Test]
    public function outputPathReturnsPhpCsFixerPhp(): void
    {
        $this->assertSame('php-cs-fixer.php', $this->generator->outputPath());
    }

    #[Test]
    public function generateContainsPhpDeclaration(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('<?php', $output);
    }

    #[Test]
    public function generateContainsSourceDir(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString("->in(__DIR__ . '/../src')", $output);
    }

    #[Test]
    public function generateContainsTestDir(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString("->in(__DIR__ . '/../tests')", $output);
    }

    #[Test]
    public function generateContainsRulesFromContext(): void
    {
        $output = $this->generator->generate($this->makeContext(['@PSR12' => true, 'array_syntax' => ['syntax' => 'short']]));
        $this->assertStringContainsString('@PSR12', $output);
        $this->assertStringContainsString('array_syntax', $output);
    }

    #[Test]
    public function generateContainsCacheFile(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('.php-cs-fixer.cache', $output);
    }

    #[Test]
    public function generateContainsRiskyAllowed(): void
    {
        $output = $this->generator->generate($this->makeContext());
        $this->assertStringContainsString('setRiskyAllowed(true)', $output);
    }
}
