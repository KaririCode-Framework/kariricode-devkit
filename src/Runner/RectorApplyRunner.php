<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

use Override;

/**
 * Runs Rector without --dry-run, applying all changes.
 *
 * Used by FormatCommand and RectorCommand (with --fix) to actually write
 * the changes to disk, as opposed to RectorRunner which only previews.
 *
 * @since 1.0.0
 */
final class RectorApplyRunner extends AbstractToolRunner
{
    #[Override]
    public function toolName(): string
    {
        return 'rector-apply';
    }

    #[Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/rector';
    }

    #[Override]
    protected function defaultArguments(): array
    {
        return [
            'process',
            '--config',
            $this->context->configPath('rector.php'),
            '--ansi',
        ];
    }
}
