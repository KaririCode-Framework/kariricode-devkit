<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use KaririCode\Devkit\Exception\DevkitException;

/**
 * Builds a ProjectContext by scanning filesystem and merging overrides.
 *
 * Detection strategy:
 *   1. Parse composer.json for namespace, name, PHP version, PSR-4 paths
 *   2. Detect test suite directories from standard layout
 *   3. Load `devkit.php` overrides from project root via DevkitConfig
 *   4. Merge overrides onto detected defaults
 *
 * @since 1.0.0
 */
final class ProjectDetector
{
    public function detect(string $workingDirectory): ProjectContext
    {
        $composerPath = $workingDirectory . \DIRECTORY_SEPARATOR . 'composer.json';

        if (! is_file($composerPath)) {
            throw DevkitException::projectNotDetected($workingDirectory);
        }

        $raw = file_get_contents($composerPath);

        if (false === $raw) {
            throw DevkitException::projectNotDetected($workingDirectory);
        }

        /** @var array<string, mixed> $composer */
        $composer = json_decode(
            $raw,
            true,
            512,
            \JSON_THROW_ON_ERROR,
        );

        // Load overrides from project root devkit.php (not from .kcode/)
        $config = new DevkitConfig($workingDirectory);

        /** @var array<string, array<string, string|list<string>>> $autoload */
        $autoload = \is_array($composer['autoload'] ?? null) ? $composer['autoload'] : [];
        /** @var array<string, array<string, string|list<string>>> $autoloadDev */
        $autoloadDev = \is_array($composer['autoload-dev'] ?? null) ? $composer['autoload-dev'] : [];

        /** @var array<string, string|list<string>> $psr4Source */
        $psr4Source = \is_array($autoload['psr-4'] ?? null) ? $autoload['psr-4'] : [];
        /** @var array<string, string|list<string>> $psr4Test */
        $psr4Test = \is_array($autoloadDev['psr-4'] ?? null) ? $autoloadDev['psr-4'] : [];

        $sourceDirs = $config->get('source_dirs', null)
            ?? $this->detectPsr4Dirs($workingDirectory, $psr4Source, ['src']);

        $testDirs = $config->get('test_dirs', null)
            ?? $this->detectPsr4Dirs($workingDirectory, $psr4Test, ['tests']);

        $projectName = isset($composer['name']) && \is_string($composer['name'])
            ? $composer['name']
            : basename($workingDirectory);

        return new ProjectContext(
            projectRoot: $workingDirectory,
            projectName: $config->get('project_name', $projectName),
            namespace: $config->get('namespace', $this->detectNamespace($composer)),
            phpVersion: $config->get('php_version', $this->detectPhpVersion($composer)),
            phpstanLevel: $config->get('phpstan_level', 9),
            psalmLevel: $config->get('psalm_level', 3),
            sourceDirs: $sourceDirs,
            testDirs: $testDirs,
            excludeDirs: $config->get('exclude_dirs', ['src/Contract']),
            testSuites: $config->get('test_suites', $this->detectTestSuites($workingDirectory, $testDirs)),
            coverageExclude: $config->get('coverage_exclude', ['src/Exception']),
            csFixerRules: array_merge(self::DEFAULT_CS_RULES, $config->get('cs_fixer_rules', [])),
            rectorSets: $config->get('rector_sets', self::DEFAULT_RECTOR_SETS),
            toolVersions: $config->toolVersions(),
        );
    }

    // ── Detection helpers ─────────────────────────────────────────

    /** @param array<string, mixed> $composer */
    private function detectNamespace(array $composer): string
    {
        $autoload = \is_array($composer['autoload'] ?? null) ? $composer['autoload'] : [];

        /** @var array<string, string|list<string>> $psr4 */
        $psr4 = \is_array($autoload['psr-4'] ?? null) ? $autoload['psr-4'] : [];

        foreach ($psr4 as $ns => $path) {
            return rtrim((string) $ns, '\\');
        }

        return 'App';
    }

    /** @param array<string, mixed> $composer */
    private function detectPhpVersion(array $composer): string
    {
        $require = \is_array($composer['require'] ?? null) ? $composer['require'] : [];
        $constraint = \is_string($require['php'] ?? null) ? $require['php'] : '^8.4';

        return preg_match('/(\d+\.\d+)/', $constraint, $m) ? $m[1] : '8.4';
    }

    /**
     * @param array<string, string|list<string>> $psr4Map
     * @param list<string> $fallbackDirs Context-aware fallback directories.
     * @return list<string> Absolute paths
     */
    private function detectPsr4Dirs(string $root, array $psr4Map, array $fallbackDirs): array
    {
        $dirs = [];

        foreach ($psr4Map as $paths) {
            foreach ((array) $paths as $path) {
                $absolute = $root . \DIRECTORY_SEPARATOR . rtrim((string) $path, '/');
                if (is_dir($absolute)) {
                    $dirs[] = $absolute;
                }
            }
        }

        // Fallback: use context-aware defaults (source → 'src', test → 'tests')
        if ([] === $dirs) {
            foreach ($fallbackDirs as $fallback) {
                $candidate = $root . \DIRECTORY_SEPARATOR . $fallback;
                if (is_dir($candidate)) {
                    $dirs[] = $candidate;

                    break;
                }
            }
        }

        return $dirs;
    }

    /**
     * @param list<string> $testDirs
     * @return array<string, string> Suite name → relative path
     */
    private function detectTestSuites(string $root, array $testDirs): array
    {
        $suites = [];
        $standard = ['Unit', 'Integration', 'Conformance', 'Functional'];

        foreach ($testDirs as $testDir) {
            foreach ($standard as $suite) {
                $candidate = $testDir . \DIRECTORY_SEPARATOR . $suite;
                if (is_dir($candidate)) {
                    $relative = str_replace($root . \DIRECTORY_SEPARATOR, '', $candidate);
                    $suites[$suite] = $relative;
                }
            }
        }

        // If nothing detected, register full test dir
        if ([] === $suites && [] !== $testDirs) {
            $relative = str_replace($root . \DIRECTORY_SEPARATOR, '', $testDirs[0]);
            $suites['Default'] = $relative;
        }

        return $suites;
    }

    // ── KaririCode Defaults ───────────────────────────────────────

    private const array DEFAULT_CS_RULES = [
        '@PSR12' => true,
        '@PHP84Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'class_attributes_separation' => [
            'elements' => ['method' => 'one', 'property' => 'one'],
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
        'single_trait_insert_per_statement' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],
        'not_operator_with_successor_space' => true,
    ];

    private const array DEFAULT_RECTOR_SETS = [
        'LevelSetList::UP_TO_PHP_84',
        'SetList::CODE_QUALITY',
        'SetList::DEAD_CODE',
        'SetList::EARLY_RETURN',
        'SetList::TYPE_DECLARATION',
    ];
}
