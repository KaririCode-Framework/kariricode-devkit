<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use FilesystemIterator;
use KaririCode\Devkit\Core\MigrationDetector;
use KaririCode\Devkit\ValueObject\MigrationReport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

#[CoversClass(MigrationDetector::class)]
#[UsesClass(MigrationReport::class)]
final class MigrationDetectorTest extends TestCase
{
    private string $tmpDir;
    private MigrationDetector $detector;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/devkit_migration_test_' . uniqid();
        mkdir($this->tmpDir, 0o777, true);
        $this->detector = new MigrationDetector();
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    #[Test]
    public function detectReturnsMigrationReport(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode([
            'name' => 'test/project',
            'require-dev' => [],
        ]));

        $report = $this->detector->detect($this->tmpDir);
        $this->assertInstanceOf(MigrationReport::class, $report);
    }

    #[Test]
    public function detectFindsRedundantPackagesInRequireDev(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode([
            'name' => 'test/project',
            'require-dev' => [
                'phpunit/phpunit' => '^12.0',
                'phpstan/phpstan' => '^1.0',
            ],
        ]));

        $report = $this->detector->detect($this->tmpDir);
        $this->assertTrue($report->hasRedundancies);
        $this->assertArrayHasKey('phpunit/phpunit', $report->redundantPackages);
        $this->assertArrayHasKey('phpstan/phpstan', $report->redundantPackages);
    }

    #[Test]
    public function detectFindsRedundantConfigFiles(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode(['name' => 'test/project']));
        file_put_contents($this->tmpDir . '/phpunit.xml', '<phpunit/>');
        file_put_contents($this->tmpDir . '/phpstan.neon', 'parameters:');

        $report = $this->detector->detect($this->tmpDir);
        $this->assertContains('phpunit.xml', $report->redundantConfigFiles);
        $this->assertContains('phpstan.neon', $report->redundantConfigFiles);
    }

    #[Test]
    public function detectFindsRedundantCachePaths(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode(['name' => 'test/project']));
        mkdir($this->tmpDir . '/.phpunit.cache', 0o777, true);
        file_put_contents($this->tmpDir . '/.php-cs-fixer.cache', '');

        $report = $this->detector->detect($this->tmpDir);
        $this->assertContains('.phpunit.cache', $report->redundantCachePaths);
        $this->assertContains('.php-cs-fixer.cache', $report->redundantCachePaths);
    }

    #[Test]
    public function detectReturnsEmptyReportWhenNothingFound(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode([
            'name' => 'test/project',
            'require-dev' => ['my/custom-package' => '^1.0'],
        ]));

        $report = $this->detector->detect($this->tmpDir);
        $this->assertFalse($report->hasRedundancies);
        $this->assertEmpty($report->redundantPackages);
        $this->assertEmpty($report->redundantConfigFiles);
        $this->assertEmpty($report->redundantCachePaths);
    }

    #[Test]
    public function detectWorksWithoutComposerJson(): void
    {
        $report = $this->detector->detect($this->tmpDir);
        $this->assertFalse($report->hasRedundancies);
        $this->assertEmpty($report->redundantPackages);
    }

    #[Test]
    public function detectHandlesComposerJsonWithoutRequireDev(): void
    {
        file_put_contents($this->tmpDir . '/composer.json', json_encode([
            'name' => 'test/project',
        ]));

        $report = $this->detector->detect($this->tmpDir);
        $this->assertEmpty($report->redundantPackages);
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var SplFileInfo $item */
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
