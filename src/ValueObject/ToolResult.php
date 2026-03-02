<?php

declare(strict_types=1);

namespace KaririCode\Devkit\ValueObject;

/**
 * Immutable result of a single tool execution.
 *
 * @since 1.0.0
 */
final readonly class ToolResult
{
    public bool $success;

    public function __construct(
        public string $toolName,
        public int $exitCode,
        public string $stdout,
        public string $stderr,
        public float $elapsedSeconds,
    ) {
        $this->success = 0 === $exitCode;
    }

    public function output(): string
    {
        $combined = \trim($this->stdout . "\n" . $this->stderr);

        return '' !== $combined ? $combined : '(no output)';
    }
}
