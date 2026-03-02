<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

/**
 * Runs `composer audit` for known vulnerability scanning.
 *
 * Unlike other runners, this invokes the `composer` binary directly
 * and does not require a generated config file.
 *
 * @since 1.0.0
 */
final class ComposerAuditRunner extends AbstractToolRunner
{
    #[\Override]
    public function toolName(): string
    {
        return 'composer-audit';
    }

    #[\Override]
    protected function vendorBin(): string
    {
        return 'vendor/bin/composer';
    }

    #[\Override]
    protected function defaultArguments(): array
    {
        return ['audit', '--format=plain', '--ansi'];
    }

    /**
     * Composer is typically global — override binary resolution
     * to prefer global `composer` before vendor path.
     */
    #[\Override]
    protected function binary(): ?string
    {
        $global = trim((string) shell_exec('command -v ' . escapeshellarg('composer') . ' 2>/dev/null'));

        if ('' !== $global && is_executable($global)) {
            return $global;
        }

        return parent::binary();
    }
}
