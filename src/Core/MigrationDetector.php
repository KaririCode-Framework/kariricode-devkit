<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;

use KaririCode\Devkit\ValueObject\MigrationReport;

/**
 * Detects redundant dev dependencies and root-level config files
 * that become unnecessary after adopting kcode.
 *
 * Scans composer.json require-dev for tools the devkit bundles,
 * and the project root for config files that `.kcode/` replaces.
 *
 * @since 1.0.0
 */
final class MigrationDetector
{
    /** Composer packages that devkit replaces via PHAR or its own require-dev. */
    private const array REPLACED_PACKAGES = [
        'phpunit/phpunit',
        'phpstan/phpstan',
        'phpstan/extension-installer',
        'phpstan/phpstan-deprecation-rules',
        'phpstan/phpstan-strict-rules',
        'friendsofphp/php-cs-fixer',
        'rector/rector',
        'vimeo/psalm',
    ];

    /** Root-level config files that `.kcode/` generated configs replace. */
    private const array REPLACED_CONFIG_FILES = [
        'phpunit.xml',
        'phpunit.xml.dist',
        'phpstan.neon',
        'phpstan.neon.dist',
        '.php-cs-fixer.php',
        '.php-cs-fixer.dist.php',
        'rector.php',
        'psalm.xml',
        'psalm.xml.dist',
    ];

    /** Root-level cache artifacts that `.kcode/build/` consolidates. */
    private const array REPLACED_CACHE_PATHS = [
        '.phpunit.cache',
        '.phpunit.result.cache',
        '.phpstan',
        '.php-cs-fixer.cache',
        '.psalm',
    ];

    public function detect(string $projectRoot): MigrationReport
    {
        $composerPath = $projectRoot . DIRECTORY_SEPARATOR . 'composer.json';

        $redundantPackages = [];
        $redundantConfigFiles = [];
        $redundantCachePaths = [];

        // Scan composer.json require-dev
        if (is_file($composerPath)) {
            $raw = file_get_contents($composerPath);

            if (false !== $raw) {
                /** @var array<string, mixed> $composer */
                $composer = json_decode(
                    $raw,
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );

                /** @var array<string, string> $requireDev */
                $requireDev = $composer['require-dev'] ?? [];

                foreach (self::REPLACED_PACKAGES as $package) {
                    if (\array_key_exists($package, $requireDev)) {
                        $redundantPackages[$package] = $requireDev[$package];
                    }
                }
            }
        }

        // Scan root-level config files
        foreach (self::REPLACED_CONFIG_FILES as $file) {
            $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $file;
            if (is_file($fullPath)) {
                $redundantConfigFiles[] = $file;
            }
        }

        // Scan root-level cache paths
        foreach (self::REPLACED_CACHE_PATHS as $cachePath) {
            $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $cachePath;
            if (file_exists($fullPath)) {
                $redundantCachePaths[] = $cachePath;
            }
        }

        return new MigrationReport(
            projectRoot: $projectRoot,
            redundantPackages: $redundantPackages,
            redundantConfigFiles: $redundantConfigFiles,
            redundantCachePaths: $redundantCachePaths,
        );
    }
}
