<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

/**
 * Runs PHPUnit with the generated `.kcode/phpunit.xml.dist`.
 *
 * @since 1.0.0
 */
final class PhpUnitRunner extends AbstractToolRunner
{
    #[\Override]
    public function toolName(): string
    {
        return 'phpunit';
    }

    #[\Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/phpunit';
    }

    #[\Override]
    protected function defaultArguments(): array
    {
        return [
            '--configuration',
            $this->context->configPath('phpunit.xml.dist'),
            '--testdox',
            '--colors=always',
        ];
    }
}
