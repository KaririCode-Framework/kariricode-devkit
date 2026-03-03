<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

use const PHP_EOL;
use const STDERR;
use const STDOUT;

use Throwable;

/**
 * Zero-dependency CLI application router.
 *
 * Parses `argv`, resolves command by name, and dispatches execution.
 * Handles `--help`, `--version`, and unknown-command fallback.
 *
 * @since 1.0.0
 */
final class Application
{
    /** @var array<string, AbstractCommand> */
    private array $commands = [];

    public function __construct(
        private readonly Devkit $devkit,
    ) {
    }

    public function register(AbstractCommand $command): void
    {
        $this->commands[$command->name()] = $command;
    }

    /** @param list<string> $argv Raw $argv from CLI. */
    public function run(array $argv): int
    {
        // Strip script name
        array_shift($argv);

        if ([] === $argv || $this->isHelp($argv)) {
            $this->printUsage();

            return 0;
        }

        if ($this->isVersion($argv)) {
            $this->printVersion();

            return 0;
        }

        $commandName = array_shift($argv);
        $command = $this->commands[$commandName] ?? null;

        if (null === $command) {
            fwrite(STDERR, "\033[31m✗\033[0m Unknown command: {$commandName}" . PHP_EOL);
            fwrite(STDERR, "  Run \033[1mkcode --help\033[0m for available commands." . PHP_EOL);

            return 1;
        }

        try {
            return $command->execute($this->devkit, $argv);
        } catch (Throwable $exception) {
            fwrite(STDERR, "\033[31m✗\033[0m {$exception->getMessage()}" . PHP_EOL);

            return 1;
        }
    }

    // ── Internals ─────────────────────────────────────────────────

    /** @param list<string> $argv */
    private function isHelp(array $argv): bool
    {
        return \in_array($argv[0] ?? '', ['--help', '-h', 'help'], true);
    }

    /** @param list<string> $argv */
    private function isVersion(array $argv): bool
    {
        return \in_array($argv[0] ?? '', ['--version', '-V'], true);
    }

    private function printVersion(): void
    {
        fwrite(STDOUT, \sprintf(
            "\033[1mKaririCode Devkit\033[0m %s" . PHP_EOL,
            Devkit::version(),
        ));
    }

    private function printUsage(): void
    {
        $this->printVersion();
        fwrite(STDOUT, PHP_EOL);
        fwrite(STDOUT, "\033[33mUsage:\033[0m" . PHP_EOL);
        fwrite(STDOUT, '  kcode <command> [options] [arguments]' . PHP_EOL . PHP_EOL);
        fwrite(STDOUT, "\033[33mAvailable commands:\033[0m" . PHP_EOL);

        $maxLen = 0;

        foreach ($this->commands as $name => $command) {
            $maxLen = max($maxLen, \strlen($name));
        }

        foreach ($this->commands as $name => $command) {
            fwrite(STDOUT, \sprintf(
                "  \033[32m%-{$maxLen}s\033[0m  %s" . PHP_EOL,
                $name,
                $command->description(),
            ));
        }

        fwrite(STDOUT, PHP_EOL);
        fwrite(STDOUT, "\033[33mOptions:\033[0m" . PHP_EOL);
        fwrite(STDOUT, "  \033[32m-h, --help\033[0m     Show this help" . PHP_EOL);
        fwrite(STDOUT, "  \033[32m-V, --version\033[0m  Show version" . PHP_EOL);
    }
}
