<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

/**
 * Runs PHPStan with the generated `.kcode/phpstan.neon`.
 *
 * @since 1.0.0
 */
final class PhpStanRunner extends AbstractToolRunner
{
    #[\Override]
    public function toolName(): string
    {
        return 'phpstan';
    }

    #[\Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/phpstan';
    }

    #[\Override]
    protected function defaultArguments(): array
    {
        return [
            'analyse',
            '--configuration',
            $this->context->configPath('phpstan.neon'),
            '--no-progress',
            '--memory-limit=1G',
        ];
    }
}
