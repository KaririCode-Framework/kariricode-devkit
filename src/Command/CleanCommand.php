<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;
use Override;

/**
 * Removes `.kcode/build/` directory (caches, coverage, reports).
 *
 * @since 1.0.0
 */
final class CleanCommand extends AbstractCommand
{
    #[Override]
    public function name(): string
    {
        return 'clean';
    }

    #[Override]
    public function description(): string
    {
        return 'Remove .kcode/build/ (caches, coverage, reports)';
    }

    #[Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Clean');

        $devkit->clean();

        $this->info('Build directory cleaned: .kcode/build/');

        return 0;
    }
}
