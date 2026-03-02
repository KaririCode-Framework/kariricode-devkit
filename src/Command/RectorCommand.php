<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Runs Rector. Default: dry-run preview. Pass `--fix` to apply changes.
 *
 * @since 1.0.0
 */
final class RectorCommand extends AbstractCommand
{
    #[\Override]
    public function name(): string
    {
        return 'rector';
    }

    #[\Override]
    public function description(): string
    {
        return 'Run Rector (--fix to apply changes)';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $apply = $this->hasFlag($arguments, '--fix', '--apply');
        $mode = $apply ? 'applying' : 'previewing';

        $this->banner("KaririCode Devkit — Rector ({$mode})");

        $passthrough = $this->passthrough($arguments, ['--fix', '--apply']);

        // RectorRunner defaults to --dry-run for safety.
        // When applying, --no-dry-run overrides it (Rector: last flag wins).
        $result = $apply
            ? $devkit->run('rector', ['--no-dry-run', ...$passthrough])
            : $devkit->run('rector', $passthrough);

        $this->line($result->output());
        $this->line();

        if ($result->success) {
            $this->info(\sprintf('Rector %s (%.2fs)', $apply ? 'applied' : 'clean', $result->elapsedSeconds));
        } else {
            $this->error(\sprintf('Rector found issues (%.2fs)', $result->elapsedSeconds));
        }

        return $result->exitCode;
    }
}
