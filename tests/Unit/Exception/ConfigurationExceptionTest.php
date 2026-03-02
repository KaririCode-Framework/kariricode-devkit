<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Exception;

use KaririCode\Devkit\Exception\ConfigurationException;
use KaririCode\Devkit\Exception\DevkitException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ConfigurationExceptionTest extends TestCase
{
    #[Test]
    public function invalidOverrideContainsKeyAndReasonInMessage(): void
    {
        $key = 'phpstan_level';
        $reason = 'Expected integer, got string.';

        $ex = ConfigurationException::invalidOverride($key, $reason);

        $this->assertInstanceOf(ConfigurationException::class, $ex);
        $this->assertStringContainsString($key, $ex->getMessage());
        $this->assertStringContainsString($reason, $ex->getMessage());
    }

    #[Test]
    public function fileNotReadableContainsPathInMessage(): void
    {
        $path = '/etc/shadow';
        $ex = ConfigurationException::fileNotReadable($path);

        $this->assertInstanceOf(ConfigurationException::class, $ex);
        $this->assertStringContainsString($path, $ex->getMessage());
    }

    #[Test]
    public function exceptionExtendsDevkitException(): void
    {
        $ex = ConfigurationException::invalidOverride('key', 'reason');

        $this->assertInstanceOf(DevkitException::class, $ex);
    }
}
