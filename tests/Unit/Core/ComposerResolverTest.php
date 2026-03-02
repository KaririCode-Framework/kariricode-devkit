<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Core\ComposerResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposerResolver::class)]
final class ComposerResolverTest extends TestCase
{
    #[Test]
    public function resolveReturnsNonEmptyString(): void
    {
        $resolver = new ComposerResolver();
        $result = $resolver->resolve();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function resolveDoesNotReturnShellFragment(): void
    {
        $resolver = new ComposerResolver();
        $result = $resolver->resolve();

        // Must be a single executable path — no shell fragments like "php /path/to/file"
        // (a shell fragment would break proc_open's array invocation)
        $this->assertStringNotContainsString(' ', ltrim($result, '/'));
    }

    #[Test]
    public function resolveRespectsComposerBinaryEnvironmentVariable(): void
    {
        $originalEnv = getenv('COMPOSER_BINARY');

        // Set env to a known executable
        putenv('COMPOSER_BINARY=/usr/bin/env');

        $resolver = new ComposerResolver();
        $result = $resolver->resolve();

        $this->assertSame('/usr/bin/env', $result);

        // Restore
        if (false === $originalEnv) {
            putenv('COMPOSER_BINARY');
        } else {
            putenv('COMPOSER_BINARY=' . $originalEnv);
        }
    }

    #[Test]
    public function resolveIgnoresNonExecutableComposerBinaryEnvVar(): void
    {
        $originalEnv = getenv('COMPOSER_BINARY');

        putenv('COMPOSER_BINARY=/non/existent/path/composer');

        $resolver = new ComposerResolver();
        $result = $resolver->resolve();

        // Should fall through to PATH or fallback — not return the non-executable path
        $this->assertNotSame('/non/existent/path/composer', $result);

        if (false === $originalEnv) {
            putenv('COMPOSER_BINARY');
        } else {
            putenv('COMPOSER_BINARY=' . $originalEnv);
        }
    }
}
