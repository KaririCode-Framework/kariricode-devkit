<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Runs PHPUnit via the devkit runner.
 *
 * Supports `--coverage`, `--suite=Name`, and passthrough arguments.
 *
 * @since 1.0.0
 */
final class TestCommand extends AbstractCommand
{
    public function name(): string
    {
        return 'test';
    }

    public function description(): string
    {
        return 'Run PHPUnit tests';
    }

    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Test');

        $extraArgs = [];

        if ($this->hasFlag($arguments, '--coverage')) {
            $extraArgs[] = '--coverage-html';
            $extraArgs[] = $devkit->context()->buildPath('coverage');
        }

        $suite = $this->option($arguments, 'suite');
        if (null !== $suite) {
            $extraArgs[] = '--testsuite';
            $extraArgs[] = $suite;
        }

        $passthrough = $this->passthrough($arguments, ['--coverage']);

        // Strip consumed --suite=X option (prefix match, not exact)
        $passthrough = \array_values(\array_filter(
            $passthrough,
            static fn (string $arg): bool => !\str_starts_with($arg, '--suite='),
        ));

        $allArgs = [...$extraArgs, ...$passthrough];

        $result = $devkit->run('phpunit', $allArgs);

        $this->line($result->output());
        $this->line();

        if ($result->success) {
            $this->info(\sprintf('Tests passed (%.2fs)', $result->elapsedSeconds));
        } else {
            $this->error(\sprintf('Tests failed — exit code %d (%.2fs)', $result->exitCode, $result->elapsedSeconds));
        }

        return $result->exitCode;
    }
}
