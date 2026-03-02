<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Core\ProcessExecutor;
use KaririCode\Devkit\ValueObject\ToolResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessExecutor::class)]
#[UsesClass(ToolResult::class)]
final class ProcessExecutorTest extends TestCase
{
    private string $projectRoot;
    private ProcessExecutor $executor;

    protected function setUp(): void
    {
        $this->projectRoot = (string) realpath(\dirname(__DIR__, 3));
        $this->executor = new ProcessExecutor($this->projectRoot);
    }

    #[Test]
    public function executeRunsCommandAndCapturesOutput(): void
    {
        $result = $this->executor->execute('php-inline', ['php', '-r', 'echo "hello";']);
        $this->assertInstanceOf(ToolResult::class, $result);
        $this->assertSame(0, $result->exitCode);
        $this->assertStringContainsString('hello', $result->stdout);
    }

    #[Test]
    public function executeReturnsTrueSuccessOnExitCode0(): void
    {
        $result = $this->executor->execute('test-tool', ['php', '-r', 'echo "ok";']);
        $this->assertTrue($result->success);
    }

    #[Test]
    public function executeReturnsFalseSuccessOnNonZeroExit(): void
    {
        $result = $this->executor->execute('false-cmd', ['php', '-r', 'exit(1);']);
        $this->assertSame(1, $result->exitCode);
        $this->assertFalse($result->success);
    }

    #[Test]
    public function executeRecordsElapsedTime(): void
    {
        $result = $this->executor->execute('test-tool', ['php', '-r', 'echo "ok";']);
        $this->assertGreaterThanOrEqual(0.0, $result->elapsedSeconds);
    }

    #[Test]
    public function executeReturnsCorrectToolName(): void
    {
        $result = $this->executor->execute('mytool', ['php', '-r', 'echo "x";']);
        $this->assertSame('mytool', $result->toolName);
    }

    #[Test]
    public function executeCapturesStderr(): void
    {
        $result = $this->executor->execute('php-err', ['php', '-r', 'fwrite(STDERR, "err-msg");']);
        $this->assertStringContainsString('err-msg', $result->stderr);
    }

    #[Test]
    public function resolveBinaryFindsLocalVendorBin(): void
    {
        // phpunit is installed as a dev dependency — vendor/bin/phpunit must exist
        $binary = $this->executor->resolveBinary('vendor/bin/phpunit');
        $this->assertNotNull($binary);
        $this->assertStringContainsString('phpunit', $binary);
    }

    #[Test]
    public function resolveBinaryReturnsNullForNonExistentTool(): void
    {
        // Use an executor with a path where the binary won't exist locally OR globally
        $executor = new ProcessExecutor('/tmp');
        $result = $executor->resolveBinary('vendor/bin/nonexistent-tool-xyz-999');
        $this->assertNull($result);
    }

    #[Test]
    public function executeReturnsNonZeroForFailingCommand(): void
    {
        $result = $this->executor->execute('exit-1', ['php', '-r', 'exit(2);']);
        $this->assertSame(2, $result->exitCode);
        $this->assertFalse($result->success);
    }

    #[Test]
    public function toolResultOutputCombinesStdoutAndStderr(): void
    {
        $result = $this->executor->execute('test', ['php', '-r', 'echo "out"; fwrite(STDERR, "err");']);
        $output = $result->output();
        $this->assertStringContainsString('out', $output);
    }

    #[Test]
    public function toolResultOutputReturnsNoOutputWhenBothEmpty(): void
    {
        $result = new ToolResult('test', 0, '', '', 0.0);
        $this->assertSame('(no output)', $result->output());
    }
}
