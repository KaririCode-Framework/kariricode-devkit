<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

/**
 * Runs PHP-CS-Fixer with the generated `.kcode/php-cs-fixer.php`.
 *
 * Default mode is `fix`. Pass `--dry-run` for check-only.
 *
 * @since 1.0.0
 */
final class CsFixerRunner extends AbstractToolRunner
{
    public function toolName(): string
    {
        return 'cs-fixer';
    }

    protected function vendorBin(): string
    {
        return 'vendor/bin/php-cs-fixer';
    }

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
