<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

use Override;

/**
 * Runs PHP-CS-Fixer with the generated `.kcode/php-cs-fixer.php`.
 *
 * Default mode is `fix`. Pass `--dry-run` for check-only.
 *
 * @since 1.0.0
 */
final class CsFixerRunner extends AbstractToolRunner
{
    #[Override]
    public function toolName(): string
    {
        return 'cs-fixer';
    }

    #[Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/php-cs-fixer';
    }

    #[Override]
    protected function defaultArguments(): array
    {
        return [
            'fix',
            '--config',
            $this->context->configPath('php-cs-fixer.php'),
            '--diff',
            '--ansi',
        ];
    }
}
