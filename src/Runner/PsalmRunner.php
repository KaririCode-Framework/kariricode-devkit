<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

use Override;

/**
 * Runs Psalm with the generated `.kcode/psalm.xml`.
 *
 * @since 1.0.0
 */
final class PsalmRunner extends AbstractToolRunner
{
    #[Override]
    public function toolName(): string
    {
        return 'psalm';
    }

    #[Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/psalm';
    }

    #[Override]
    protected function defaultArguments(): array
    {
        return [
            '--config',
            $this->context->configPath('psalm.xml'),
            '--no-progress',
            '--show-info=false',
        ];
    }
}
