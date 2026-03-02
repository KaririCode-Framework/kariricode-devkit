<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

/**
 * Resolves the Composer binary path from the environment.
 *
 * Searches in order:
 *   1. `COMPOSER_BINARY` environment variable (explicit override)
 *   2. Global PATH via `command -v composer`
 *   3. Common installation paths (`/usr/local/bin/composer`, `/usr/bin/composer`)
 *   4. Composer PHAR in `HOME/.composer/composer`
 *   5. Fallback literal `composer` (assumes it is on the PATH at runtime)
 *
 * Extracted from `Devkit` as a standalone service to honour SRP.
 *
 * @since 1.0.0
 */
final readonly class ComposerResolver
{
    private const array GLOBAL_PATHS = [
        '/usr/local/bin/composer',
        '/usr/bin/composer',
    ];

    /**
     * Return the absolute path to a usable Composer binary.
     *
     * Returns a single path string (never a shell fragment) so callers
     * can safely pass it as the first element of a `proc_open` command array.
     */
    public function resolve(): string
    {
        // 1. Explicit environment override
        $environmentPath = getenv('COMPOSER_BINARY');

        if (\is_string($environmentPath) && '' !== $environmentPath && is_executable($environmentPath)) {
            return $environmentPath;
        }

        // 2. PATH lookup
        /** @psalm-suppress ForbiddenCode — shell_exec is intentional here; no user-controlled input */
        $pathBinary = trim((string) shell_exec('command -v composer 2>/dev/null'));

        if ('' !== $pathBinary && is_executable($pathBinary)) {
            return $pathBinary;
        }

        // 3. Known global install locations
        foreach (self::GLOBAL_PATHS as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        // 4. User-level Composer PHAR
        $home = getenv('HOME');

        if (\is_string($home) && '' !== $home) {
            $userPhar = $home . '/.composer/composer';

            if (is_executable($userPhar)) {
                return $userPhar;
            }
        }

        // 5. Assume PATH fallback — let the OS fail with a clear error
        return 'composer';
    }
}
