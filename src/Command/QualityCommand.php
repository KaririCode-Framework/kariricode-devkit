<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;
use KaririCode\Devkit\ValueObject\ToolResult;

/**
 * Full quality pipeline: cs-check → phpstan → psalm → phpunit.
 *
 * @since 1.0.0
 */
final class QualityCommand extends AbstractCommand
{
    #[\Override]
    public function name(): string
    {
        return 'quality';
    }

    #[\Override]
    public function description(): string
    {
        return 'Full pipeline: cs:check → analyse → test';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Quality Pipeline');

        $report = $devkit->quality();

        foreach ($report->results as $result) {
            $this->renderToolResult($result);
        }

        // Summary
        $this->line();

        if ($report->passed) {
            $this->info(\sprintf(
                'All %d tool(s) passed (%.2fs total)',
                \count($report->results),
                $report->totalSeconds,
            ));

            return 0;
        }

        $this->error(\sprintf(
            '%d of %d tool(s) failed (%.2fs total)',
            $report->failureCount,
            \count($report->results),
            $report->totalSeconds,
        ));

        foreach ($report->failures() as $failure) {
            $this->error("  └─ {$failure->toolName} (exit {$failure->exitCode})");
        }

        return 1;
    }

    private function renderToolResult(ToolResult $result): void
    {
        if ($result->success) {
            $this->info(\sprintf(
                '%s passed (%.2fs)',
                $result->toolName,
                $result->elapsedSeconds,
            ));
        } else {
            $this->error(\sprintf(
                '%s failed — exit code %d (%.2fs)',
                $result->toolName,
                $result->exitCode,
                $result->elapsedSeconds,
            ));
            $this->line($result->output());
        }

        $this->line();
    }
}
