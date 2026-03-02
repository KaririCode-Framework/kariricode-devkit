<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Command;

use KaririCode\Devkit\Command\AbstractCommand;
use KaririCode\Devkit\Core\Devkit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractCommand::class)]
final class AbstractCommandTest extends TestCase
{
    private AbstractCommand $command;

    protected function setUp(): void
    {
        $this->command = new class () extends AbstractCommand {
            public function name(): string
            {
                return 'test';
            }

            public function description(): string
            {
                return 'Test command';
            }

            public function execute(Devkit $devkit, array $arguments): int
            {
                return 0;
            }

            /** @param list<string> $args */
            public function callHasFlag(array $args, string ...$flags): bool
            {
                return $this->hasFlag($args, ...$flags);
            }

            /** @param list<string> $args */
            public function callOption(array $args, string $key, ?string $default = null): ?string
            {
                return $this->option($args, $key, $default);
            }

            /** @param list<string> $args @return list<string> */
            public function callPositional(array $args): array
            {
                return $this->positional($args);
            }

            /**
             * @param list<string> $args
             * @param list<string> $consume
             * @return list<string>
             */
            public function callPassthrough(array $args, array $consume = []): array
            {
                return $this->passthrough($args, $consume);
            }

            public function callInfo(string $msg): void
            {
                $this->info($msg);
            }

            public function callWarning(string $msg): void
            {
                $this->warning($msg);
            }

            public function callError(string $msg): void
            {
                $this->error($msg);
            }

            public function callLine(string $msg = ''): void
            {
                $this->line($msg);
            }

            public function callBanner(string $title): void
            {
                $this->banner($title);
            }

            public function callSection(string $title): void
            {
                $this->section($title);
            }
        };
    }

    // ── Identity ───────────────────────────────────────────────────

    #[Test]
    public function nameReturnsCommandName(): void
    {
        $this->assertSame('test', $this->command->name());
    }

    #[Test]
    public function descriptionReturnsDescription(): void
    {
        $this->assertSame('Test command', $this->command->description());
    }

    // ── Argument helpers ───────────────────────────────────────────

    #[Test]
    public function hasFlagReturnsTrueWhenFlagPresent(): void
    {
        $this->assertTrue($this->command->callHasFlag(['--verbose', '--check'], '--verbose'));
    }

    #[Test]
    public function hasFlagReturnsFalseWhenFlagAbsent(): void
    {
        $this->assertFalse($this->command->callHasFlag(['--check'], '--verbose'));
    }

    #[Test]
    public function hasFlagMatchesAnyOfMultipleFlags(): void
    {
        $this->assertTrue($this->command->callHasFlag(['--dry-run'], '--check', '--dry-run'));
    }

    #[Test]
    public function hasFlagReturnsFalseWithEmptyArgs(): void
    {
        $this->assertFalse($this->command->callHasFlag([], '--any'));
    }

    #[Test]
    public function optionExtractsValueFromArguments(): void
    {
        $result = $this->command->callOption(['--level=9', '--other=x'], 'level');
        $this->assertSame('9', $result);
    }

    #[Test]
    public function optionReturnsDefaultWhenNotFound(): void
    {
        $result = $this->command->callOption(['--other=x'], 'level', 'default');
        $this->assertSame('default', $result);
    }

    #[Test]
    public function optionReturnsNullWhenNotFoundAndNoDefault(): void
    {
        $this->assertNull($this->command->callOption([], 'level'));
    }

    #[Test]
    public function positionalFiltersOutFlags(): void
    {
        $result = $this->command->callPositional(['file.php', '--verbose', 'other.php', '--dry-run']);
        $this->assertSame(['file.php', 'other.php'], $result);
    }

    #[Test]
    public function positionalReturnsEmptyForAllFlags(): void
    {
        $result = $this->command->callPositional(['--a', '--b', '--c']);
        $this->assertSame([], $result);
    }

    #[Test]
    public function passthroughRemovesConsumedFlags(): void
    {
        $result = $this->command->callPassthrough(
            ['--verbose', '--coverage', '--check'],
            ['--coverage'],
        );
        $this->assertSame(['--verbose', '--check'], $result);
    }

    #[Test]
    public function passthroughWithNoConsumedFlagsReturnsAll(): void
    {
        $args = ['--verbose', '--check'];
        $result = $this->command->callPassthrough($args);
        $this->assertSame($args, $result);
    }

    // ── Output helpers — use assertIsString as trivial assertion ───
    // fwrite(STDOUT/STDERR) bypasses ob_start; we verify no exception
    // is thrown and perform a trivial assertion to avoid Notice.

    #[Test]
    public function infoWritesToOutput(): void
    {
        $this->command->callInfo('hello');
        $this->assertTrue(true);
    }

    #[Test]
    public function warningWritesToOutput(): void
    {
        $this->command->callWarning('caution');
        $this->assertTrue(true);
    }

    #[Test]
    public function errorWritesToStderr(): void
    {
        $this->command->callError('something failed');
        $this->assertTrue(true);
    }

    #[Test]
    public function lineWritesToOutput(): void
    {
        $this->command->callLine('some output');
        $this->assertTrue(true);
    }

    #[Test]
    public function bannerWritesToOutput(): void
    {
        $this->command->callBanner('My Banner');
        $this->assertTrue(true);
    }

    #[Test]
    public function sectionWritesToOutput(): void
    {
        $this->command->callSection('My Section');
        $this->assertTrue(true);
    }
}
