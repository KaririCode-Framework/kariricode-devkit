<?php

declare(strict_types=1);

namespace KaririCode\Devkit\ValueObject;

/**
 * Immutable snapshot of redundant items detected in a project
 * that the devkit replaces.
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

    /** Remove redundant config files and cache paths from disk. */
    public function removeFiles(): int
    {
        $removed = 0;

        foreach ([...$this->redundantConfigFiles, ...$this->redundantCachePaths] as $relative) {
            $fullPath = $this->projectRoot . \DIRECTORY_SEPARATOR . $relative;

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
    public function removePackagesFromComposer(): array
    {
        $composerPath = $this->projectRoot . \DIRECTORY_SEPARATOR . 'composer.json';

        if (! is_file($composerPath)) {
            return [];
        }

        $raw = file_get_contents($composerPath);

        if (false === $raw) {
            return [];
        }

        /** @var array<string, mixed> $composer */
        $composer = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);

        $removed = [];

        /** @var array<string, string> $requireDev */
        $requireDev = \is_array($composer['require-dev'] ?? null) ? $composer['require-dev'] : [];

        foreach (array_keys($this->redundantPackages) as $package) {
            if (isset($requireDev[$package])) {
                unset($requireDev[$package]);
                $removed[] = $package;
            }
        }

        if ([] === $removed) {
            return [];
        }

        // Write back the updated require-dev (or remove the key if empty)
        if ([] === $requireDev) {
            unset($composer['require-dev']);
        } else {
            $composer['require-dev'] = $requireDev;
        }

        // Detect indentation: 4-space (default) or tab
        $jsonFlags = \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
        $encoded = json_encode($composer, $jsonFlags);

        if (false === $encoded) {
            return [];
        }

        // Re-apply tab indentation if original used tabs
        if (str_contains($raw, "\t")) {
            $encoded = str_replace('    ', "\t", $encoded);
        }

        file_put_contents(
            $composerPath,
            $encoded . \PHP_EOL,
        );

        return $removed;
    }

    private function removeRecursive(string $dir): void
    {
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
