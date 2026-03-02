<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;
use KaririCode\Devkit\Core\MigrationDetector;
use KaririCode\Devkit\ValueObject\MigrationReport;

/**
 * Detects redundant dev dependencies and root-level config files,
 * then offers interactive cleanup.
 *
 * Runs MigrationDetector against the project root, displays all
 * findings grouped by category, and asks for confirmation before
 * removing files or modifying composer.json.
 *
 * Safe to run multiple times — only acts on items that still exist.
 *
 * @since 1.0.0
 */
final class MigrateCommand extends AbstractCommand
{
    public function __construct(
        private readonly MigrationDetector $detector,
    ) {
    }

    #[\Override]
    public function name(): string
    {
        return 'migrate';
    }

    #[\Override]
    public function description(): string
    {
        return 'Detect and remove redundant dev dependencies and root configs';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Migrate');

        $dryRun = $this->hasFlag($arguments, '--dry-run', '--check');
        $noInteraction = $this->hasFlag($arguments, '--no-interaction', '-n');

        $context = $devkit->context();
        $report = $this->detector->detect($context->projectRoot);

        if (! $report->hasRedundancies) {
            $this->info('No redundant dependencies or config files found. Project is clean.');

            return 0;
        }

        $this->renderReport($report);

        if ($dryRun) {
            $this->warning('Dry-run mode — no changes applied.');

            return 0;
        }

        // ── Config files & caches ─────────────────────────────────
        $filesRemoved = 0;

        if ($report->hasConfigFiles() || $report->hasCachePaths()) {
            $shouldRemoveFiles = $noInteraction || $this->confirm(
                'Remove these config files and cache paths?',
            );

            if ($shouldRemoveFiles) {
                $filesRemoved = $report->removeFiles();
                $this->info("Removed {$filesRemoved} file(s)/directory(ies).");
            } else {
                $this->warning('Skipped file removal.');
            }
        }

        // ── Composer.json require-dev ─────────────────────────────
        $packagesRemoved = [];

        if ($report->hasPackages()) {
            $shouldRemovePackages = $noInteraction || $this->confirm(
                'Remove these packages from composer.json require-dev?',
            );

            if ($shouldRemovePackages) {
                $packagesRemoved = $report->removePackagesFromComposer();

                if ([] !== $packagesRemoved) {
                    $this->info(\sprintf(
                        'Removed %d package(s) from composer.json: %s',
                        \count($packagesRemoved),
                        implode(', ', $packagesRemoved),
                    ));
                }
            } else {
                $this->warning('Skipped composer.json modification.');
            }
        }

        // ── Summary ──────────────────────────────────────────────
        $this->section('Summary');

        $totalActioned = $filesRemoved + \count($packagesRemoved);

        if ($totalActioned > 0) {
            $this->info("{$totalActioned} item(s) cleaned up.");

            if ([] !== $packagesRemoved) {
                $this->line();
                $this->warning('Run \033[1mcomposer update\033[0m to apply dependency changes.');
            }
        } else {
            $this->warning('No changes applied.');
        }

        return 0;
    }

    private function renderReport(MigrationReport $report): void
    {
        $this->line(\sprintf(
            '  Found \033[1m%d\033[0m redundant item(s) that kcode replaces:',
            $report->totalItems,
        ));

        if ($report->hasPackages()) {
            $this->section('composer.json require-dev');

            foreach ($report->redundantPackages as $package => $version) {
                $this->line("    \033[31m✗\033[0m {$package}: {$version}");
            }
        }

        if ($report->hasConfigFiles()) {
            $this->section('Root-level config files');

            foreach ($report->redundantConfigFiles as $file) {
                $this->line("    \033[31m✗\033[0m {$file}");
            }
        }

        if ($report->hasCachePaths()) {
            $this->section('Root-level cache paths');

            foreach ($report->redundantCachePaths as $cachePath) {
                $isDir = is_dir($report->projectRoot . \DIRECTORY_SEPARATOR . $cachePath);
                $suffix = $isDir ? '/' : '';
                $this->line("    \033[31m✗\033[0m {$cachePath}{$suffix}");
            }
        }

        $this->line();
        $this->line('  These are replaced by \033[1m.kcode/\033[0m generated configs.');
        $this->line();
    }
}
