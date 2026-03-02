<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Exception;

/**
 * Base exception for all devkit errors.
 *
 * @since 1.0.0
 */
class DevkitException extends \RuntimeException
{
    public static function projectNotDetected(string $path): self
    {
        return new self(\sprintf(
            'No composer.json found in "%s". Run from a PHP project root.',
            $path,
        ));
    }

    public static function directoryNotWritable(string $path): self
    {
        return new self(\sprintf(
            'Cannot write to "%s". Check filesystem permissions.',
            $path,
        ));
    }
}
