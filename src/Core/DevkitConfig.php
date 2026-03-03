<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use const DIRECTORY_SEPARATOR;

use KaririCode\Devkit\Exception\ConfigurationException;

/**
 * Loads and validates `devkit.php` project overrides from the project root.
 *
 * The config file is optional. When absent, all defaults apply.
 * When present, it must return an associative array. Unknown keys
 * are silently ignored (forward-compatible with future versions).
 *
 * The file lives at the project root (not inside `.kcode/`) because
 * `.kcode/` is gitignored. Overrides are user-owned configuration
 * that must survive `git clone`.
 *
 * ## Supported Keys
 *
 * ```php
 * <?php return [
 *     'project_name'     => 'kariricode/parser',
 *     'namespace'        => 'KaririCode\\Parser',
 *     'php_version'      => '8.4',
 *     'phpstan_level'    => 9,                // 0–9
 *     'psalm_level'      => 3,                // 1–9
 *     'source_dirs'      => ['src'],          // relative to project root
 *     'test_dirs'        => ['tests'],
 *     'exclude_dirs'     => ['src/Contract'], // excluded from analysis
 *     'test_suites'      => ['Unit' => 'tests/Unit', 'Integration' => 'tests/Integration'],
 *     'coverage_exclude'  => ['src/Exception'],
 *     'cs_fixer_rules'   => [],               // MERGED with KaririCode defaults
 *     'rector_sets'      => [],               // REPLACES KaririCode defaults
 *     'tools'            => [                 // version constraints (optional)
 *         'phpunit'       => '^11.0',
 *         'phpstan'       => '^2.0',
 *         'php-cs-fixer'  => '^3.64',
 *         'rector'        => '^2.0',
 *         'psalm'         => '^6.0',
 *     ],
 * ];
 * ```
 *
 * @since 1.0.0
 */
final readonly class DevkitConfig
{
    private const string CONFIG_FILE = 'devkit.php';

    /** @var array<string, mixed> */
    public array $overrides;

    public function __construct(string $projectRoot)
    {
        $configPath = $projectRoot . DIRECTORY_SEPARATOR . self::CONFIG_FILE;

        if (! is_file($configPath)) {
            $this->overrides = [];

            return;
        }

        if (! is_readable($configPath)) {
            throw ConfigurationException::fileNotReadable($configPath);
        }

        $loaded = require $configPath;

        if (! \is_array($loaded)) {
            throw ConfigurationException::invalidOverride(
                self::CONFIG_FILE,
                'Must return an array.',
            );
        }

        /** @var array<string, mixed> $loaded */
        $this->overrides = $loaded;
    }

    /**
     * Get a config value with type-safe fallback.
     *
     * @template T
     * @param T $default
     * @return T
     */
    public function get(string $key, mixed $default): mixed
    {
        if (! \array_key_exists($key, $this->overrides)) {
            return $default;
        }

        $value = $this->overrides[$key];

        // Type consistency check: override must match default's type
        if (null !== $default && \gettype($value) !== \gettype($default)) {
            throw ConfigurationException::invalidOverride(
                $key,
                \sprintf('Expected %s, got %s.', \gettype($default), \gettype($value)),
            );
        }

        return $value;
    }

    /** @return array<string, string> */
    public function toolVersions(): array
    {
        $tools = $this->overrides['tools'] ?? [];

        if (! \is_array($tools)) {
            return [];
        }

        /** @var array<string, string> $typed */
        $typed = array_filter(
            $tools,
            \is_string(...),
        );

        return $typed;
    }

    public function hasOverrides(): bool
    {
        return [] !== $this->overrides;
    }
}
