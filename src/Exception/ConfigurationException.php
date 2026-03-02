<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Exception;

/**
 * Raised when devkit.php overrides or generated configs are invalid.
 *
 * @since 1.0.0
 */
final class ConfigurationException extends DevkitException
{
    public static function invalidOverride(string $key, string $reason): self
    {
        return new self(\sprintf('Invalid devkit.php key "%s": %s', $key, $reason));
    }

    public static function fileNotReadable(string $path): self
    {
        return new self(\sprintf('Configuration file not readable: %s', $path));
    }
}
