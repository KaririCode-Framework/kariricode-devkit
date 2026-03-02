# ADR-006: Immutable Value Objects for Tool Results

**Status:** Accepted
**Date:** 2025-02-28
**Deciders:** Walmir Silva
**Context:** KaririCode Framework Devkit v1.0.0

## Context

Tool execution produces structured output: exit code, stdout, stderr, elapsed time, and a pass/fail determination. This data flows from `ProcessExecutor` → `ToolRunner` → `Command` → user output, and is also aggregated in quality pipeline reports.

The data must be reliable at every consumption point — no mutation between capture and display.

## Decision

Tool execution results are modeled as `final readonly class` value objects (PHP 8.2+):

```php
final readonly class ToolResult
{
    public bool $success;

    public function __construct(
        public string $toolName,
        public int    $exitCode,
        public string $stdout,
        public string $stderr,
        public float  $elapsedSeconds,
    ) {
        $this->success = 0 === $exitCode;
    }
}
```

```php
final readonly class QualityReport
{
    public bool  $passed;
    public float $totalSeconds;
    public int   $failureCount;

    public function __construct(
        public array $results, // list<ToolResult>
    ) {
        $this->passed = array_all($results, fn (ToolResult $r) => $r->success);
        // ... aggregations computed in constructor
    }
}
```

`ProjectContext` follows the same pattern — `final readonly class` with all state computed at construction time.

## Rationale

### ARFA 1.3 Principle P1: Immutable State

ARFA 1.3 mandates immutable state for data flowing through processing pipelines. Value objects satisfy this by construction:

- `readonly` prevents property reassignment after construction.
- `final` prevents subclassing that could introduce mutation.
- Derived properties (`$success`, `$passed`, `$totalSeconds`) are computed once in the constructor.

### Why Not DTOs with Getters

KaririCode V4.0 Specification explicitly forbids the getter/setter anti-pattern. Public readonly properties are the canonical access pattern:

```php
// ✅ Direct property access
$result->exitCode
$result->success

// ❌ Getter anti-pattern (V4.0 violation)
$result->getExitCode()
$result->isSuccess()
```

### Constructor-Computed Derived State

`QualityReport::$passed` and `$failureCount` are derived from `$results`. Computing them in the constructor guarantees consistency — there is no window where the report exists but aggregations haven't been calculated.

This follows the **complete construction** principle: an object is fully valid immediately after `new`.

## Consequences

### Positive

- Thread-safe by construction (relevant for future async/parallel tool execution).
- No defensive copying needed when passing results between layers.
- IDE autocompletion works directly on public properties.
- Memory-efficient — no getter overhead, no backing field duplication.

### Negative

- Cannot extend or decorate results without creating a new class.
- `array_all()` requires PHP 8.4+ (acceptable given the framework's minimum version).

## References

- ARFA 1.3 Specification, §2.3: Immutable State Principle (P1)
- KaririCode Specification V4.0, §4.1: No Getter/Setter Anti-pattern
- Martin Fowler, *Value Object* pattern (https://martinfowler.com/bliki/ValueObject.html)
- PHP RFC: Readonly classes (PHP 8.2)
