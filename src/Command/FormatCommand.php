<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Applies all formatting: CS-Fixer fix + Rector apply.
 *
 * @since 1.0.0
 */
final class FormatCommand extends AbstractCommand
{
    #[\Override]
    public function name(): string
    {
        return 'format';
    }

    #[\Override]
    public function description(): string
    {
        return 'Apply all formatting (cs:fix + rector --fix)';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Format');

        $exitCode = 0;

        // Step 1: CS-Fixer fix
        if ($devkit->isToolAvailable('cs-fixer')) {
            $this->line("\033[1m▸ Running php-cs-fixer fix…\033[0m");
            $result = $devkit->run('cs-fixer', $arguments);
            $this->line($result->output());

            if ($result->success) {
                $this->info(\sprintf('CS-Fixer done (%.2fs)', $result->elapsedSeconds));
            } else {
                $this->error(\sprintf('CS-Fixer failed (%.2fs)', $result->elapsedSeconds));
                $exitCode = $result->exitCode;
            }

            $this->line();
        }

        // Step 2: Rector apply (--no-dry-run overrides runner default)
        if ($devkit->isToolAvailable('rector')) {
            $this->line("\033[1m▸ Running rector process…\033[0m");
            $result = $devkit->run('rector', ['--no-dry-run', ...$arguments]);
            $this->line($result->output());

            if ($result->success) {
                $this->info(\sprintf('Rector done (%.2fs)', $result->elapsedSeconds));
            } else {
                $this->error(\sprintf('Rector failed (%.2fs)', $result->elapsedSeconds));
                $exitCode = max($exitCode, $result->exitCode);
            }
        }

        return $exitCode;
    }
}
