<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

/**
 * Immutable snapshot of a project's structure and devkit configuration.
 *
 * Built once by ProjectDetector and consumed by all generators and
 * runners. All directory paths stored here are absolute.
 *
 * @since 1.0.0
 */
final readonly class ProjectContext
{
    public string $devkitDir;

    public string $buildDir;

    /**
     * @param list<string>          $sourceDirs
     * @param list<string>          $testDirs
     * @param list<string>          $excludeDirs     Relative to project root
     * @param array<string, string> $testSuites      Suite name → relative dir
     * @param list<string>          $coverageExclude Relative to project root
     * @param array<string, mixed>  $csFixerRules    Merged with KaririCode defaults
     * @param list<string>          $rectorSets
     * @param array<string, string> $toolVersions    Tool name → version constraint
     */
    public function __construct(
        public string $projectRoot,
        public string $projectName,
        public string $namespace,
        public string $phpVersion,
        public int $phpstanLevel,
        public int $psalmLevel,
        public array $sourceDirs,
        public array $testDirs,
        public array $excludeDirs,
        public array $testSuites,
        public array $coverageExclude,
        public array $csFixerRules,
        public array $rectorSets,
        public array $toolVersions,
    ) {
        $this->devkitDir = $projectRoot . \DIRECTORY_SEPARATOR . '.kcode';
        $this->buildDir = $this->devkitDir . \DIRECTORY_SEPARATOR . 'build';
    }

    /** Absolute path to a file inside `.kcode/`. */
    public function configPath(string $filename): string
    {
        return $this->devkitDir . \DIRECTORY_SEPARATOR . $filename;
    }

    /** Absolute path inside `.kcode/build/`. */
    public function buildPath(string $filename = ''): string
    {
        return $this->buildDir . ('' !== $filename ? \DIRECTORY_SEPARATOR . $filename : '');
    }

    /** @return list<string> Convert absolute source dirs to project-relative paths. */
    public function relativeSourceDirs(): array
    {
        return array_map(fn (string $dir): string => $this->relativize($dir), $this->sourceDirs);
    }

    /** @return list<string> Convert absolute test dirs to project-relative paths. */
    public function relativeTestDirs(): array
    {
        return array_map(fn (string $dir): string => $this->relativize($dir), $this->testDirs);
    }

    public function relativize(string $absolutePath): string
    {
        $prefix = $this->projectRoot . \DIRECTORY_SEPARATOR;

        return str_starts_with($absolutePath, $prefix)
            ? substr($absolutePath, \strlen($prefix))
            : $absolutePath;
    }
}
