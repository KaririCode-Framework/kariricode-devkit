<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Base command with output formatting, option parsing, and exit-code handling.
 *
 * @since 1.0.0
 */
abstract class AbstractCommand
{
    abstract public function name(): string;

    abstract public function description(): string;

    /** @param list<string> $arguments Raw CLI arguments after command name. */
    abstract public function execute(Devkit $devkit, array $arguments): int;

    // ── Output Helpers ────────────────────────────────────────────

    protected function info(string $message): void
    {
        fwrite(\STDOUT, "\033[32m✓\033[0m {$message}" . \PHP_EOL);
    }

    protected function warning(string $message): void
    {
        fwrite(\STDOUT, "\033[33m⚠\033[0m {$message}" . \PHP_EOL);
    }

    protected function error(string $message): void
    {
        fwrite(\STDERR, "\033[31m✗\033[0m {$message}" . \PHP_EOL);
    }

    protected function line(string $message = ''): void
    {
        fwrite(\STDOUT, $message . \PHP_EOL);
    }

    protected function banner(string $title): void
    {
        $ruler = str_repeat('─', 60);
        $this->line("\033[36m{$ruler}\033[0m");
        $this->line("\033[1m  {$title}\033[0m");
        $this->line("\033[36m{$ruler}\033[0m");
    }

    protected function section(string $title): void
    {
        $this->line();
        $this->line("\033[33m  {$title}\033[0m");
        $this->line();
    }

    /**
     * Interactive yes/no confirmation via STDIN.
     *
     * @param bool $default Default answer when user presses Enter without input.
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        $hint = $default ? '[Y/n]' : '[y/N]';
        fwrite(\STDOUT, "\033[33m?\033[0m {$question} {$hint} ");

        $input = trim((string) fgets(\STDIN));

        if ('' === $input) {
            return $default;
        }

        return \in_array(strtolower($input), ['y', 'yes', 'sim', 's'], true);
    }

    // ── Argument Helpers ──────────────────────────────────────────

    /** @param list<string> $arguments */
    protected function hasFlag(array $arguments, string ...$flags): bool
    {
        return array_any($flags, fn ($flag): bool => \in_array($flag, $arguments, true));
    }

    /**
     * Extract --key=value from arguments.
     *
     * @param list<string> $arguments
     */
    protected function option(array $arguments, string $key, ?string $default = null): ?string
    {
        $prefix = "--{$key}=";

        foreach ($arguments as $arg) {
            if (str_starts_with($arg, $prefix)) {
                return substr($arg, \strlen($prefix));
            }
        }

        return $default;
    }

    /**
     * Return arguments that are not flags (--xxx).
     *
     * @param list<string> $arguments
     * @return list<string>
     */
    protected function positional(array $arguments): array
    {
        return array_values(array_filter(
            $arguments,
            static fn (string $arg): bool => ! str_starts_with($arg, '--'),
        ));
    }

    /**
     * Filter arguments to pass through to underlying tool.
     *
     * @param list<string> $arguments
     * @param list<string> $consume Flags consumed by the command itself.
     * @return list<string>
     */
    protected function passthrough(array $arguments, array $consume = []): array
    {
        return array_values(array_filter(
            $arguments,
            static fn (string $arg): bool => ! \in_array($arg, $consume, true),
        ));
    }
}
