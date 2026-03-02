<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Exception;

/**
 * Raised when an external tool binary cannot be found or crashes.
 *
 * @since 1.0.0
 */
final class ToolException extends DevkitException
{
    public static function binaryNotFound(string $tool): self
    {
        return new self(\sprintf(
            'Tool "%s" binary not found. Ensure the PHAR is intact or install the tool globally.',
            $tool,
        ));
    }

    public static function executionFailed(string $tool, int $exitCode, string $output): self
    {
        return new self(
            \sprintf('Tool "%s" exited with code %d: %s', $tool, $exitCode, trim($output) ?: '(no output)'),
            $exitCode,
        );
    }
}
