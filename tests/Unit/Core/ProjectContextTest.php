<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectContext::class)]
final class ProjectContextTest extends TestCase
{
    private ProjectContext $context;

    private string $root;

    protected function setUp(): void
    {
        $this->root = '/var/www/my-project';
        $this->context = new ProjectContext(
            projectRoot: $this->root,
            projectName: 'kariricode/parser',
            namespace: 'KaririCode\\Parser',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: [$this->root . '/src'],
            testDirs: [$this->root . '/tests'],
            excludeDirs: ['src/Contract'],
            testSuites: ['Unit' => 'tests/Unit'],
            coverageExclude: ['src/Exception'],
            csFixerRules: ['@PSR12' => true],
            rectorSets: ['LevelSetList::UP_TO_PHP_84'],
            toolVersions: [],
        );
    }

    #[Test]
    public function devkitDirIsComposedCorrectly(): void
    {
        $this->assertSame($this->root . DIRECTORY_SEPARATOR . '.kcode', $this->context->devkitDir);
    }

    #[Test]
    public function buildDirIsInsideDevkitDir(): void
    {
        $expected = $this->root . DIRECTORY_SEPARATOR . '.kcode' . DIRECTORY_SEPARATOR . 'build';
        $this->assertSame($expected, $this->context->buildDir);
    }

    #[Test]
    public function configPathReturnsAbsolutePathInsideDevkitDir(): void
    {
        $path = $this->context->configPath('phpunit.xml');
        $this->assertSame($this->root . '/.kcode/phpunit.xml', $path);
    }

    #[Test]
    public function buildPathWithFilenameReturnsFullPath(): void
    {
        $path = $this->context->buildPath('kcode.phar');
        $this->assertStringEndsWith('kcode.phar', $path);
        $this->assertStringContainsString('.kcode/build', $path);
    }

    #[Test]
    public function buildPathWithoutFilenameReturnsBuildDir(): void
    {
        $path = $this->context->buildPath();
        $this->assertSame($this->context->buildDir, $path);
    }

    #[Test]
    public function relativeSourceDirsReturnProjectRelativePaths(): void
    {
        $relative = $this->context->relativeSourceDirs();
        $this->assertSame(['src'], $relative);
    }

    #[Test]
    public function relativeTestDirsReturnProjectRelativePaths(): void
    {
        $relative = $this->context->relativeTestDirs();
        $this->assertSame(['tests'], $relative);
    }

    #[Test]
    public function relativizeStripsProjectRootPrefix(): void
    {
        $absolute = $this->root . '/src/Core/Devkit.php';
        $relative = $this->context->relativize($absolute);
        $this->assertSame('src/Core/Devkit.php', $relative);
    }

    #[Test]
    public function relativizeReturnsPathUnchangedWhenNotUnderRoot(): void
    {
        $external = '/some/other/path/file.php';
        $this->assertSame($external, $this->context->relativize($external));
    }
}
