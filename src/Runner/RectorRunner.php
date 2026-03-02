<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

/**
 * Runs Rector with the generated `.kcode/rector.php`.
 *
 * Default mode is `--dry-run`. Pass `--no-dry-run` via arguments to apply.
 *
 * @since 1.0.0
 */
final class RectorRunner extends AbstractToolRunner
{
    #[\Override]
    public function toolName(): string
    {
        return 'rector';
    }

    #[\Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/rector';
    }

    #[\Override]
    protected function defaultArguments(): array
    {
        return [
            'process',
            '--config',
            $this->context->configPath('rector.php'),
            '--dry-run',
            '--ansi',
        ];
    }
}
