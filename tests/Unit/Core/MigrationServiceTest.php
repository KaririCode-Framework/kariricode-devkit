<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use FilesystemIterator;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

use KaririCode\Devkit\Core\MigrationService;
use KaririCode\Devkit\ValueObject\MigrationReport;

use const PHP_EOL;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

#[CoversClass(MigrationService::class)]
#[UsesClass(MigrationReport::class)]
final class MigrationServiceTest extends TestCase
{
    private MigrationService $service;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->service = new MigrationService();
        $this->tmpDir = sys_get_temp_dir() . '/migration_service_test_' . uniqid();
        mkdir($this->tmpDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            $this->removeRecursive($this->tmpDir);
        }
    }

    #[Test]
    public function removeFilesDeletesExistingFile(): void
    {
        $configFile = $this->tmpDir . '/phpstan.neon';
        file_put_contents($configFile, 'parameters:');

        $report = new MigrationReport(
            projectRoot: $this->tmpDir,
            redundantPackages: [],
            redundantConfigFiles: ['phpstan.neon'],
            redundantCachePaths: [],
        );

        $removed = $this->service->removeFiles($report);

        $this->assertSame(1, $removed);
        $this->assertFileDoesNotExist($configFile);
    }

    #[Test]
    public function removeFilesDeletesDirectory(): void
    {
        $cacheDir = $this->tmpDir . '/.phpunit.cache';
        mkdir($cacheDir, 0o777, true);
        file_put_contents($cacheDir . '/results', 'cached');

        $report = new MigrationReport(
            projectRoot: $this->tmpDir,
            redundantPackages: [],
            redundantConfigFiles: [],
            redundantCachePaths: ['.phpunit.cache'],
        );

        $removed = $this->service->removeFiles($report);

        $this->assertSame(1, $removed);
        $this->assertDirectoryDoesNotExist($cacheDir);
    }

    #[Test]
    public function removeFilesSkipsNonExistentPaths(): void
    {
        $report = new MigrationReport(
            projectRoot: $this->tmpDir,
            redundantPackages: [],
            redundantConfigFiles: ['non-existent.neon'],
            redundantCachePaths: [],
        );

        $removed = $this->service->removeFiles($report);

        $this->assertSame(0, $removed);
    }

    #[Test]
    public function removePackagesFromComposerRemovesPackage(): void
    {
        $composerContent = json_encode([
            'name' => 'test/project',
            'require-dev' => [
                'phpunit/phpunit' => '^11.0',
                'phpstan/phpstan' => '^2.0',
            ],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($this->tmpDir . '/composer.json', $composerContent . PHP_EOL);

        $report = new MigrationReport(
            projectRoot: $this->tmpDir,
            redundantPackages: ['phpstan/phpstan' => '^2.0'],
            redundantConfigFiles: [],
            redundantCachePaths: [],
        );

        $removed = $this->service->removePackagesFromComposer($report);

        $this->assertSame(['phpstan/phpstan'], $removed);

        /** @var array<string, mixed> $updated */
        $updated = json_decode(
            (string) file_get_contents($this->tmpDir . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertArrayHasKey('phpunit/phpunit', $updated['require-dev']);
        $this->assertArrayNotHasKey('phpstan/phpstan', $updated['require-dev']);
    }

    #[Test]
    public function removePackagesFromComposerReturnsEmptyIfNoComposerJson(): void
    {
        $report = new MigrationReport(
            projectRoot: $this->tmpDir . '/non-existent',
            redundantPackages: ['some/package' => '^1.0'],
            redundantConfigFiles: [],
            redundantCachePaths: [],
        );

        $removed = $this->service->removePackagesFromComposer($report);

        $this->assertSame([], $removed);
    }

    private function removeRecursive(string $directoryPath): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var SplFileInfo $item */
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($directoryPath);
    }
}
