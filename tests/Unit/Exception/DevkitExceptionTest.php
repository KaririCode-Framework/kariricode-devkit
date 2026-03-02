<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Exception;

use KaririCode\Devkit\Exception\DevkitException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class DevkitExceptionTest extends TestCase
{
    #[Test]
    public function projectNotDetectedContainsPathInMessage(): void
    {
        $path = '/some/project/path';
        $ex = DevkitException::projectNotDetected($path);

        $this->assertInstanceOf(DevkitException::class, $ex);
        $this->assertStringContainsString($path, $ex->getMessage());
        $this->assertStringContainsString('composer.json', $ex->getMessage());
    }

    #[Test]
    public function directoryNotWritableContainsPathInMessage(): void
    {
        $path = '/some/readonly/dir';
        $ex = DevkitException::directoryNotWritable($path);

        $this->assertInstanceOf(DevkitException::class, $ex);
        $this->assertStringContainsString($path, $ex->getMessage());
    }

    #[Test]
    public function exceptionExtendsRuntimeException(): void
    {
        $ex = DevkitException::projectNotDetected('/foo');

        $this->assertInstanceOf(\RuntimeException::class, $ex);
    }
}
