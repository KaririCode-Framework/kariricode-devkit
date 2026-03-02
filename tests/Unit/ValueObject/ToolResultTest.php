<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\ValueObject;

use KaririCode\Devkit\ValueObject\ToolResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolResult::class)]
final class ToolResultTest extends TestCase
{
    #[Test]
    public function successIsTrueWhenExitCodeIsZero(): void
    {
        $result = new ToolResult(
            toolName: 'phpunit',
            exitCode: 0,
            stdout: 'OK (42 tests)',
            stderr: '',
            elapsedSeconds: 1.234,
        );

        $this->assertTrue($result->success);
        $this->assertSame('phpunit', $result->toolName);
        $this->assertSame(0, $result->exitCode);
        $this->assertSame(1.234, $result->elapsedSeconds);
    }

    #[Test]
    public function successIsFalseWhenExitCodeIsNonZero(): void
    {
        $result = new ToolResult(
            toolName: 'phpstan',
            exitCode: 1,
            stdout: '',
            stderr: 'Found 3 errors',
            elapsedSeconds: 0.5,
        );

        $this->assertFalse($result->success);
    }

    #[Test]
    public function outputCombinesStdoutAndStderr(): void
    {
        $result = new ToolResult(
            toolName: 'phpstan',
            exitCode: 1,
            stdout: 'Line 1',
            stderr: 'Error here',
            elapsedSeconds: 0.1,
        );

        $output = $result->output();
        $this->assertStringContainsString('Line 1', $output);
        $this->assertStringContainsString('Error here', $output);
    }

    #[Test]
    public function outputReturnsPlaceholderWhenBothStreamsAreEmpty(): void
    {
        $result = new ToolResult(
            toolName: 'rector',
            exitCode: 0,
            stdout: '',
            stderr: '',
            elapsedSeconds: 0.0,
        );

        $this->assertSame('(no output)', $result->output());
    }

    #[Test]
    public function outputTrimsWhitespace(): void
    {
        $result = new ToolResult(
            toolName: 'psalm',
            exitCode: 0,
            stdout: "  passed  \n",
            stderr: '   ',
            elapsedSeconds: 0.2,
        );

        $this->assertSame('passed', $result->output());
    }
}
