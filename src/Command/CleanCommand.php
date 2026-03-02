<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;

/**
 * Removes `.kcode/build/` directory (caches, coverage, reports).
 *
 * @since 1.0.0
 */
final class CleanCommand extends AbstractCommand
{
    public function name(): string
    {
        return 'clean';
    }

    public function description(): string
    {
        return 'Remove .kcode/build/ (caches, coverage, reports)';
    }

    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Clean');

        $devkit->clean();

        $this->info('Build directory cleaned: .kcode/build/');

        return 0;
    }
}
