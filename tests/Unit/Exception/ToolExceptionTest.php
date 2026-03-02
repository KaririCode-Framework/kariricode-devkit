<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Exception;

use KaririCode\Devkit\Exception\DevkitException;
use KaririCode\Devkit\Exception\ToolException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ToolExceptionTest extends TestCase
{
    #[Test]
    public function binaryNotFoundContainsToolNameInMessage(): void
    {
        $ex = ToolException::binaryNotFound('phpunit');

        $this->assertInstanceOf(ToolException::class, $ex);
        $this->assertStringContainsString('phpunit', $ex->getMessage());
    }

    #[Test]
    public function executionFailedContainsToolNameExitCodeAndOutput(): void
    {
        $ex = ToolException::executionFailed('phpstan', 1, 'Analysis failed');

        $this->assertInstanceOf(ToolException::class, $ex);
        $this->assertStringContainsString('phpstan', $ex->getMessage());
        $this->assertStringContainsString('1', $ex->getMessage());
        $this->assertStringContainsString('Analysis failed', $ex->getMessage());
        $this->assertSame(1, $ex->getCode());
    }

    #[Test]
    public function executionFailedWithEmptyOutputUsesNoOutputPlaceholder(): void
    {
        $ex = ToolException::executionFailed('rector', 2, '');

        $this->assertStringContainsString('(no output)', $ex->getMessage());
        $this->assertSame(2, $ex->getCode());
    }

    #[Test]
    public function exceptionExtendsDevkitException(): void
    {
        $ex = ToolException::binaryNotFound('phpunit');

        $this->assertInstanceOf(DevkitException::class, $ex);
    }
}
