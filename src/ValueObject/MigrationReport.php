<?php

declare(strict_types=1);

namespace KaririCode\Devkit\ValueObject;

/**
 * Immutable snapshot of redundant items detected in a project
 * that the devkit replaces.
 *
 * Pure Value Object — no I/O side-effects. To apply the migration,
 * pass this report to {@see \KaririCode\Devkit\Core\MigrationService}.
 *
 * @since 1.0.0
 */
final readonly class MigrationReport
{
    public bool $hasRedundancies;
    public int $totalItems;

    /**
     * @param array<string, string> $redundantPackages    Package name → version constraint
     * @param list<string>          $redundantConfigFiles Filenames relative to project root
     * @param list<string>          $redundantCachePaths  Cache paths relative to project root
     */
    public function __construct(
        public string $projectRoot,
        public array $redundantPackages,
        public array $redundantConfigFiles,
        public array $redundantCachePaths,
    ) {
        $this->totalItems = \count($redundantPackages)
            + \count($redundantConfigFiles)
            + \count($redundantCachePaths);
        $this->hasRedundancies = $this->totalItems > 0;
    }

    public function hasPackages(): bool
    {
        return [] !== $this->redundantPackages;
    }

    public function hasConfigFiles(): bool
    {
        return [] !== $this->redundantConfigFiles;
    }

    public function hasCachePaths(): bool
    {
        return [] !== $this->redundantCachePaths;
    }
}
