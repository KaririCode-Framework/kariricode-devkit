<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use KaririCode\Devkit\ValueObject\ToolResult;

/**
 * Generic process executor used by all tool runners via composition.
 *
 * Handles process spawning, stdout/stderr capture, timing, and
 * exit-code collection. Binary resolution follows a three-tier
 * strategy tailored for PHAR distribution:
 *
 *   1. PHAR-internal vendor/bin/ (self-contained distribution)
 *   2. Project-local vendor/bin/ (composer-installed devkit)
 *   3. Global PATH (system-wide tool installations)
 *
 * @since 1.0.0
 */
final readonly class ProcessExecutor
{
    public function __construct(
        private string $workingDirectory,
    ) {
    }

    /**
     * Execute a command and capture the result.
     *
     * @param list<string> $command Full command with arguments.
     */
    public function execute(string $toolName, array $command): ToolResult
    {
        $start = hrtime(true);

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $this->workingDirectory,
        );

        if (! \is_resource($process)) {
            return new ToolResult(
                toolName: $toolName,
                exitCode: 127,
                stdout: '',
                stderr: 'Failed to spawn process: ' . implode(' ', $command),
                elapsedSeconds: 0.0,
            );
        }

        fclose($pipes[0]);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        $elapsed = (hrtime(true) - $start) / 1_000_000_000;

        return new ToolResult(
            toolName: $toolName,
            exitCode: $exitCode,
            stdout: $stdout,
            stderr: $stderr,
            elapsedSeconds: round($elapsed, 3),
        );
    }

    /**
     * Resolve a tool binary path using the three-tier strategy.
     *
     * @param string $vendorBin Relative path like "vendor/bin/phpunit"
     */
    public function resolveBinary(string $vendorBin): ?string
    {
        $basename = basename($vendorBin);

        // Tier 1: .kcode/vendor/bin/ — tools installed by `kcode init`
        // This is the primary resolution path for the kcode toolchain.
        $kcodeBin = $this->workingDirectory . '/.kcode/vendor/bin/' . $basename;
        if (is_file($kcodeBin) && is_executable($kcodeBin)) {
            return $kcodeBin;
        }

        // Tier 2: Project-local vendor binary (e.g. package has devkit as require-dev)
        $localBin = $this->workingDirectory . '/' . $vendorBin;
        if (is_file($localBin) && is_executable($localBin)) {
            return $localBin;
        }

        // Tier 3: Global PATH
        /** @psalm-suppress ForbiddenCode — shell_exec is intentional for binary resolution; input is escaped */
        $globalBin = trim((string) shell_exec('command -v ' . escapeshellarg($basename) . ' 2>/dev/null'));
        if ('' !== $globalBin && is_executable($globalBin)) {
            return $globalBin;
        }

        return null;
    }

}
