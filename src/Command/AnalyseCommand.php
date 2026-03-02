<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Runs static analysis: PHPStan + Psalm (both optional).
 *
 * @since 1.0.0
 */
final class AnalyseCommand extends AbstractCommand
{
    public function name(): string
    {
        return 'analyse';
    }

    public function description(): string
    {
        return 'Run static analysis (PHPStan + Psalm)';
    }

    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Analyse');

        $exitCode = 0;

        foreach (['phpstan', 'psalm'] as $tool) {
            if (!$devkit->isToolAvailable($tool)) {
                $this->warning("{$tool} not available — skipping");

                continue;
            }

            $this->line("\033[1m▸ Running {$tool}…\033[0m");
            $result = $devkit->run($tool, $arguments);
            $this->line($result->output());

            if ($result->success) {
                $this->info(\sprintf('%s passed (%.2fs)', $tool, $result->elapsedSeconds));
            } else {
                $this->error(\sprintf('%s failed — exit code %d (%.2fs)', $tool, $result->exitCode, $result->elapsedSeconds));
                $exitCode = \max($exitCode, $result->exitCode);
            }

            $this->line();
        }

        return $exitCode;
    }
}
