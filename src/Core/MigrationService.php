<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use const DIRECTORY_SEPARATOR;

use FilesystemIterator;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

use KaririCode\Devkit\ValueObject\MigrationReport;

use const PHP_EOL;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Executes the I/O side-effects of a migration: removes files,
 * cache paths, and redundant packages from composer.json.
 *
 * Extracted from MigrationReport to preserve the Value Object's
 * immutability (ARFA 1.3 S7) and enforce Single Responsibility (S1).
 *
 * @since 1.0.0
 */
final class MigrationService
{
    public function removeFiles(MigrationReport $report): int
    {
        $removed = 0;

        foreach ([...$report->redundantConfigFiles, ...$report->redundantCachePaths] as $relative) {
            $fullPath = $report->projectRoot . DIRECTORY_SEPARATOR . $relative;

            if (is_dir($fullPath)) {
                $this->removeRecursive($fullPath);
                ++$removed;
            } elseif (is_file($fullPath)) {
                unlink($fullPath);
                ++$removed;
            }
        }

        return $removed;
    }

    /**
     * Remove redundant packages from composer.json require-dev.
     *
     * Rewrites composer.json in place preserving JSON formatting.
     *
     * @return list<string> Package names actually removed.
     */
    public function removePackagesFromComposer(MigrationReport $report): array
    {
        $composerManifestPath = $report->projectRoot . DIRECTORY_SEPARATOR . 'composer.json';

        if (! is_file($composerManifestPath)) {
            return [];
        }

        $raw = file_get_contents($composerManifestPath);

        if (false === $raw) {
            return [];
        }

        /** @var array<string, mixed> $composer */
        $composer = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $removed = [];

        /** @var array<string, string> $requireDev */
        $requireDev = \is_array($composer['require-dev'] ?? null) ? $composer['require-dev'] : [];

        foreach (array_keys($report->redundantPackages) as $package) {
            if (isset($requireDev[$package])) {
                unset($requireDev[$package]);
                $removed[] = $package;
            }
        }

        if ([] === $removed) {
            return [];
        }

        if ([] === $requireDev) {
            unset($composer['require-dev']);
        } else {
            $composer['require-dev'] = $requireDev;
        }

        $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $encoded = json_encode($composer, $jsonFlags);

        if (false === $encoded) {
            return [];
        }

        // Re-apply tab indentation if the original used tabs
        if (str_contains($raw, "\t")) {
            $encoded = str_replace('    ', "\t", $encoded);
        }

        file_put_contents(
            $composerManifestPath,
            $encoded . PHP_EOL,
        );

        return $removed;
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
