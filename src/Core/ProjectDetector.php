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

        if (!\is_file($composerPath)) {
            throw DevkitException::projectNotDetected($workingDirectory);
        }

        $composer = \json_decode(
            \file_get_contents($composerPath),
            true,
            512,
            \JSON_THROW_ON_ERROR,
        );

        // Load overrides from project root devkit.php (not from .kcode/)
        $config = new DevkitConfig($workingDirectory);

        $sourceDirs = $config->get('source_dirs', null)
            ?? $this->detectPsr4Dirs($workingDirectory, $composer['autoload']['psr-4'] ?? [], ['src']);

        $testDirs = $config->get('test_dirs', null)
            ?? $this->detectPsr4Dirs($workingDirectory, $composer['autoload-dev']['psr-4'] ?? [], ['tests']);

        return new ProjectContext(
            projectRoot: $workingDirectory,
            projectName: $config->get('project_name', $composer['name'] ?? \basename($workingDirectory)),
            namespace: $config->get('namespace', $this->detectNamespace($composer)),
            phpVersion: $config->get('php_version', $this->detectPhpVersion($composer)),
            phpstanLevel: $config->get('phpstan_level', 9),
            psalmLevel: $config->get('psalm_level', 3),
            sourceDirs: $sourceDirs,
            testDirs: $testDirs,
            excludeDirs: $config->get('exclude_dirs', ['src/Contract']),
            testSuites: $config->get('test_suites', $this->detectTestSuites($workingDirectory, $testDirs)),
            coverageExclude: $config->get('coverage_exclude', ['src/Exception']),
            csFixerRules: \array_merge(self::DEFAULT_CS_RULES, $config->get('cs_fixer_rules', [])),
            rectorSets: $config->get('rector_sets', self::DEFAULT_RECTOR_SETS),
            toolVersions: $config->toolVersions(),
        );
    }

    // ── Detection helpers ─────────────────────────────────────────

    private function detectNamespace(array $composer): string
    {
        foreach ($composer['autoload']['psr-4'] ?? [] as $ns => $path) {
            return \rtrim($ns, '\\');
        }

        return 'App';
    }

    private function detectPhpVersion(array $composer): string
    {
        $constraint = $composer['require']['php'] ?? '^8.4';

        return \preg_match('/(\d+\.\d+)/', $constraint, $m) ? $m[1] : '8.4';
    }

    /**
     * @param list<string> $fallbackDirs Context-aware fallback directories.
     * @return list<string> Absolute paths
     */
    private function detectPsr4Dirs(string $root, array $psr4Map, array $fallbackDirs): array
    {
        $dirs = [];

        foreach ($psr4Map as $paths) {
            foreach ((array) $paths as $path) {
                $absolute = $root . \DIRECTORY_SEPARATOR . \rtrim($path, '/');
                if (\is_dir($absolute)) {
                    $dirs[] = $absolute;
                }
            }
        }

        // Fallback: use context-aware defaults (source → 'src', test → 'tests')
        if ([] === $dirs) {
            foreach ($fallbackDirs as $fallback) {
                $candidate = $root . \DIRECTORY_SEPARATOR . $fallback;
                if (\is_dir($candidate)) {
                    $dirs[] = $candidate;

                    break;
                }
            }
        }

        return $dirs;
    }

    /** @return array<string, string> Suite name → relative path */
    private function detectTestSuites(string $root, array $testDirs): array
    {
        $suites = [];
        $standard = ['Unit', 'Integration', 'Conformance', 'Functional'];

        foreach ($testDirs as $testDir) {
            foreach ($standard as $suite) {
                $candidate = $testDir . \DIRECTORY_SEPARATOR . $suite;
                if (\is_dir($candidate)) {
                    $relative = \str_replace($root . \DIRECTORY_SEPARATOR, '', $candidate);
                    $suites[$suite] = $relative;
                }
            }
        }

        // If nothing detected, register full test dir
        if ([] === $suites && [] !== $testDirs) {
            $relative = \str_replace($root . \DIRECTORY_SEPARATOR, '', $testDirs[0]);
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
