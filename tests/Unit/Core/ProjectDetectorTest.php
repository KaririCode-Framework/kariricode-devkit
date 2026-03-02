<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Core\DevkitConfig;
use KaririCode\Devkit\Core\ProjectContext;
use KaririCode\Devkit\Core\ProjectDetector;
use KaririCode\Devkit\Exception\DevkitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectDetector::class)]
#[UsesClass(ProjectContext::class)]
#[UsesClass(DevkitConfig::class)]
#[UsesClass(DevkitException::class)]
final class ProjectDetectorTest extends TestCase
{
    private string $tmpDir;
    private ProjectDetector $detector;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/devkit_detector_test_' . uniqid();
        mkdir($this->tmpDir, 0o777, true);
        $this->detector = new ProjectDetector();
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    #[Test]
    public function detectThrowsWhenComposerJsonMissing(): void
    {
        $this->expectException(DevkitException::class);
        $this->detector->detect($this->tmpDir);
    }

    #[Test]
    public function detectReturnsProjectContext(): void
    {
        $this->writeComposer([
            'name' => 'vendor/project',
            'autoload' => ['psr-4' => ['Vendor\\Project\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['Vendor\\Project\\Tests\\' => 'tests/']],
        ]);
        mkdir($this->tmpDir . '/src', 0o777, true);

        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertInstanceOf(ProjectContext::class, $ctx);
    }

    #[Test]
    public function detectParsesProjectName(): void
    {
        $this->writeComposer(['name' => 'acme/my-lib']);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertSame('acme/my-lib', $ctx->projectName);
    }

    #[Test]
    public function detectFallsBackToDirectoryNameWhenNoComposerName(): void
    {
        $this->writeComposer([]);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertSame(basename($this->tmpDir), $ctx->projectName);
    }

    #[Test]
    public function detectParsesNamespaceFromPsr4(): void
    {
        $this->writeComposer([
            'name' => 'vendor/project',
            'autoload' => ['psr-4' => ['Acme\\Lib\\' => 'src/']],
        ]);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertStringContainsString('Acme', $ctx->namespace);
    }

    #[Test]
    public function detectUsesDefaultPhpVersionWhenMissing(): void
    {
        $this->writeComposer(['name' => 'test/pkg']);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertNotEmpty($ctx->phpVersion);
    }

    #[Test]
    public function detectParsesPhpVersionFromRequire(): void
    {
        $this->writeComposer([
            'name' => 'test/pkg',
            'require' => ['php' => '>=8.3'],
        ]);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertStringContainsString('8.3', $ctx->phpVersion);
    }

    #[Test]
    public function detectSetsDefaultSourceDirWhenSrcNotInPsr4(): void
    {
        mkdir($this->tmpDir . '/src', 0o777, true);
        $this->writeComposer(['name' => 'test/pkg']);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertNotEmpty($ctx->sourceDirs);
    }

    #[Test]
    public function detectSetsDefaultTestDirWhenTestsNotInPsr4(): void
    {
        mkdir($this->tmpDir . '/tests', 0o777, true);
        $this->writeComposer(['name' => 'test/pkg']);
        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertNotEmpty($ctx->testDirs);
    }

    #[Test]
    public function detectLoadsDevkitConfigOverrides(): void
    {
        $this->writeComposer(['name' => 'test/pkg']);
        file_put_contents($this->tmpDir . '/devkit.php', "<?php return ['phpstan_level' => 7];");

        $ctx = $this->detector->detect($this->tmpDir);
        $this->assertSame(7, $ctx->phpstanLevel);
    }

    /** @param array<mixed> $data */
    private function writeComposer(array $data): void
    {
        file_put_contents(
            $this->tmpDir . '/composer.json',
            json_encode($data, JSON_PRETTY_PRINT),
        );
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
