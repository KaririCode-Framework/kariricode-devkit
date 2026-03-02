<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Runs PHP-CS-Fixer. Default: fix. Pass `--check` for dry-run.
 *
 * @since 1.0.0
 */
final class CsFixCommand extends AbstractCommand
{
    #[\Override]
    public function name(): string
    {
        return 'cs:fix';
    }

    #[\Override]
    public function description(): string
    {
        return 'Fix code style (--check for dry-run)';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $dryRun = $this->hasFlag($arguments, '--check', '--dry-run');
        $mode = $dryRun ? 'checking' : 'fixing';

        $this->banner("KaririCode Devkit — CS {$mode}");

        $extraArgs = $dryRun ? ['--dry-run'] : [];
        $passthrough = $this->passthrough($arguments, ['--check', '--dry-run']);

        $result = $devkit->run('cs-fixer', [...$extraArgs, ...$passthrough]);

        $this->line($result->output());
        $this->line();

        if ($result->success) {
            $this->info(\sprintf('Code style %s (%.2fs)', $dryRun ? 'OK' : 'fixed', $result->elapsedSeconds));
        } else {
            $this->error(\sprintf('Code style issues found (%.2fs)', $result->elapsedSeconds));
        }

        return $result->exitCode;
    }
}
