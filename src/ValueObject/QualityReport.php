<?php

declare(strict_types=1);

namespace KaririCode\Devkit\ValueObject;

/**
 * Aggregated result of a full quality pipeline.
 *
 * @since 1.0.0
 */
final readonly class QualityReport
{
    public bool $passed;
    public float $totalSeconds;
    public int $failureCount;

    /** @param list<ToolResult> $results */
    public function __construct(
        public array $results,
    ) {
        $this->passed = \array_all($results, static fn (ToolResult $r): bool => $r->success);
        $this->totalSeconds = \array_sum(\array_map(
            static fn (ToolResult $r): float => $r->elapsedSeconds,
            $results,
        ));
        $this->failureCount = \count(\array_filter(
            $results,
            static fn (ToolResult $r): bool => !$r->success,
        ));
    }

    /** @return list<ToolResult> */
    public function failures(): array
    {
        return \array_values(\array_filter(
            $this->results,
            static fn (ToolResult $r): bool => !$r->success,
        ));
    }
}
