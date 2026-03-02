<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\ValueObject;

use KaririCode\Devkit\ValueObject\MigrationReport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationReport::class)]
final class MigrationReportTest extends TestCase
{
    #[Test]
    public function hasRedundanciesIsFalseWhenAllEmpty(): void
    {
        $report = new MigrationReport(
            projectRoot: '/app',
            redundantPackages: [],
            redundantConfigFiles: [],
            redundantCachePaths: [],
        );

        $this->assertFalse($report->hasRedundancies);
        $this->assertSame(0, $report->totalItems);
        $this->assertFalse($report->hasPackages());
        $this->assertFalse($report->hasConfigFiles());
        $this->assertFalse($report->hasCachePaths());
    }

    #[Test]
    public function hasRedundanciesIsTrueWhenThereAreRedundantPackages(): void
    {
        $report = new MigrationReport(
            projectRoot: '/app',
            redundantPackages: ['phpunit/phpunit' => '^11.0'],
            redundantConfigFiles: [],
            redundantCachePaths: [],
        );

        $this->assertTrue($report->hasRedundancies);
        $this->assertSame(1, $report->totalItems);
        $this->assertTrue($report->hasPackages());
        $this->assertFalse($report->hasConfigFiles());
    }

    #[Test]
    public function totalItemsCountsAllCategories(): void
    {
        $report = new MigrationReport(
            projectRoot: '/app',
            redundantPackages: ['phpstan/phpstan' => '^2.0', 'vimeo/psalm' => '^6.0'],
            redundantConfigFiles: ['phpstan.neon', 'phpcs.xml'],
            redundantCachePaths: ['.phpunit.cache'],
        );

        $this->assertSame(5, $report->totalItems);
        $this->assertTrue($report->hasRedundancies);
        $this->assertTrue($report->hasPackages());
        $this->assertTrue($report->hasConfigFiles());
        $this->assertTrue($report->hasCachePaths());
    }

    #[Test]
    public function removeFilesDeletesExistingFiles(): void
    {
        $tmpDir = sys_get_temp_dir() . '/devkit_test_' . uniqid();
        mkdir($tmpDir, 0o777, true);

        $fileToRemove = $tmpDir . '/phpstan.neon';
        file_put_contents($fileToRemove, 'parameters:');

        $report = new MigrationReport(
            projectRoot: $tmpDir,
            redundantPackages: [],
            redundantConfigFiles: ['phpstan.neon'],
            redundantCachePaths: [],
        );

        $removed = $report->removeFiles();

        $this->assertSame(1, $removed);
        $this->assertFileDoesNotExist($fileToRemove);

        rmdir($tmpDir);
    }
}
