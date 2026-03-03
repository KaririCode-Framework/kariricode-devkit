<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Runner;

use KaririCode\Devkit\Contract\ToolRunner;
use KaririCode\Devkit\Core\ProcessExecutor;
use KaririCode\Devkit\Core\ProjectContext;
use KaririCode\Devkit\ValueObject\ToolResult;
use Override;

/**
 * Base runner: resolves binary via three-tier strategy and delegates
 * execution to ProcessExecutor. Subclasses define tool identity and
 * default arguments.
 *
 * @since 1.0.0
 */
abstract class AbstractToolRunner implements ToolRunner
{
    private ?string $resolvedBinary = null;

    public function __construct(
        protected readonly ProcessExecutor $executor,
        protected readonly ProjectContext $context,
    ) {
    }

    /** Vendor-relative binary path (e.g. "vendor/bin/phpunit"). */
    abstract protected function vendorBin(): string;

    /**
     * Default arguments prepended before user-supplied arguments.
     *
     * @return list<string>
     */
    abstract protected function defaultArguments(): array;

    #[Override]
    public function isAvailable(): bool
    {
        return null !== $this->binary();
    }

    /** @param list<string> $arguments */
    #[Override]
    public function run(array $arguments = []): ToolResult
    {
        $binary = $this->binary();

        if (null === $binary) {
            return new ToolResult(
                toolName: $this->toolName(),
                exitCode: 127,
                stdout: '',
                stderr: \sprintf('Binary not found for "%s".', $this->toolName()),
                elapsedSeconds: 0.0,
            );
        }

        $command = [$binary, ...$this->defaultArguments(), ...$arguments];

        return $this->executor->execute($this->toolName(), $command);
    }

    protected function binary(): ?string
    {
        return $this->resolvedBinary ??= $this->executor->resolveBinary($this->vendorBin());
    }
}
