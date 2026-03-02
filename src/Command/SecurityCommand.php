<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Runs `composer audit` for known vulnerability scanning.
 *
 * @since 1.0.0
 */
final class SecurityCommand extends AbstractCommand
{
    public function name(): string
    {
        return 'security';
    }

    public function description(): string
    {
        return 'Run composer audit for security vulnerabilities';
    }

    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Security Audit');

        $result = $devkit->run('composer-audit', $arguments);

        $this->line($result->output());
        $this->line();

        if ($result->success) {
            $this->info(\sprintf('No known vulnerabilities (%.2fs)', $result->elapsedSeconds));
        } else {
            $this->error(\sprintf('Vulnerabilities found (%.2fs)', $result->elapsedSeconds));
        }

        return $result->exitCode;
    }
}
