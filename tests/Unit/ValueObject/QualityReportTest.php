<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\ValueObject;

use KaririCode\Devkit\ValueObject\QualityReport;
use KaririCode\Devkit\ValueObject\ToolResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(QualityReport::class)]
final class QualityReportTest extends TestCase
{
    private function makeResult(string $name, int $exitCode, float $elapsed = 0.1): ToolResult
    {
        return new ToolResult(
            toolName: $name,
            exitCode: $exitCode,
            stdout: '',
            stderr: '',
            elapsedSeconds: $elapsed,
        );
    }

    #[Test]
    public function passedIsTrueWhenAllToolsSucceed(): void
    {
        $report = new QualityReport([
            $this->makeResult('phpunit', 0, 1.0),
            $this->makeResult('phpstan', 0, 0.5),
        ]);

        $this->assertTrue($report->passed);
        $this->assertSame(0, $report->failureCount);
    }

    #[Test]
    public function passedIsFalseWhenAtLeastOneToolFails(): void
    {
        $report = new QualityReport([
            $this->makeResult('phpunit', 0, 1.0),
            $this->makeResult('phpstan', 1, 0.5),
        ]);

        $this->assertFalse($report->passed);
        $this->assertSame(1, $report->failureCount);
    }

    #[Test]
    public function totalSecondsIsTheSumOfAllElapsedTimes(): void
    {
        $report = new QualityReport([
            $this->makeResult('phpunit', 0, 1.5),
            $this->makeResult('phpstan', 0, 0.7),
            $this->makeResult('psalm', 0, 0.3),
        ]);

        $this->assertEqualsWithDelta(2.5, $report->totalSeconds, 0.001);
    }

    #[Test]
    public function failuresReturnsOnlyFailedResults(): void
    {
        $passing = $this->makeResult('phpunit', 0);
        $failing1 = $this->makeResult('phpstan', 1);
        $failing2 = $this->makeResult('psalm', 2);

        $report = new QualityReport([$passing, $failing1, $failing2]);

        $failures = $report->failures();

        $this->assertCount(2, $failures);
        $this->assertSame($failing1, $failures[0]);
        $this->assertSame($failing2, $failures[1]);
    }

    #[Test]
    public function emptyReportPassesWithZeroTotals(): void
    {
        $report = new QualityReport([]);

        $this->assertTrue($report->passed);
        $this->assertSame(0, $report->failureCount);
        $this->assertSame(0.0, $report->totalSeconds);
        $this->assertEmpty($report->failures());
    }
}
