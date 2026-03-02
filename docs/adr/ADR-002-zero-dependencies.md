# ADR-002: Zero External Dependencies in Core

**Status:** Accepted
**Date:** 2025-02-28
**Deciders:** Walmir Silva
**Context:** KaririCode Framework Devkit v1.0.0

## Context

The devkit's `src/` layer (Contract, Core, Command, Runner, Configuration, Exception, ValueObject) needs a CLI framework, process execution, and config generation. Standard PHP ecosystem choices include Symfony Console (~50+ classes), League CLImate, or Laravel Artisan.

KaririCode Framework follows a **zero external dependencies** principle (ARFA 1.3, §2.1) for all framework components.

## Decision

The devkit core (`src/`) has **zero external `require` dependencies**. The only PHP requirement is `>=8.4`.

All infrastructure is implemented in-house:

| Capability | Implementation | Lines |
|---|---|---|
| CLI router | `Command\Application` | 118 |
| Argument parsing | `Command\AbstractCommand` | 113 |
| Process execution | `Core\ProcessExecutor` | 109 |
| Config file loading | `Core\DevkitConfig` | 112 |
| ANSI output formatting | Inline escape sequences | ~20 |

The five quality tools (PHPUnit, PHPStan, etc.) are `require-dev` dependencies — they are not imported at the PHP level. The devkit spawns them as subprocesses via `proc_open()`.

## Rationale

### Why Not Symfony Console

Symfony Console is excellent but introduces:

- **~50 classes** and their autoloading overhead into the PHAR.
- **Transitive dependencies** (symfony/string, symfony/deprecation-contracts, etc.).
- **PHAR namespace conflicts** with projects that use different Symfony versions.
- **Conceptual overhead** — the devkit has exactly 9 commands with simple flag parsing. A full command framework is over-engineered.

### What 231 Lines Buys

`Application` (118) + `AbstractCommand` (113) = 231 lines that provide:

- Named command dispatch with `--help` and `--version`.
- ANSI-colored output (info, warning, error, banner).
- Flag detection (`--coverage`, `--check`).
- Key-value option extraction (`--suite=Unit`).
- Passthrough argument filtering.

This covers 100% of the devkit's CLI needs with no runtime dependencies and sub-millisecond boot time.

### Process Execution via proc_open

Tools are spawned as child processes, not loaded as PHP libraries. This provides:

- **Isolation** — Tool crashes don't bring down the devkit process.
- **Output capture** — stdout and stderr are captured separately into `ToolResult`.
- **Timing** — `hrtime(true)` captures nanosecond-precision execution time.
- **Exit code semantics** — Standard Unix exit codes propagate through `ToolResult::$exitCode`.

## Consequences

### Positive

- PHAR boot time < 1ms (no autoloader for framework classes beyond the devkit itself).
- Zero risk of dependency conflicts with host projects.
- Full control over CLI behavior — no upstream breaking changes.
- Entire CLI layer is readable in ~15 minutes.

### Negative

- No built-in features like table rendering, progress bars, or interactive prompts.
- Argument parsing is less sophisticated than Symfony Console's InputDefinition.
- Maintenance burden for CLI infrastructure falls on the project.

### Acceptable Trade-offs

- Table rendering is unnecessary — the devkit outputs tool stdout directly.
- Progress bars are unnecessary — tool progress is handled by the tools themselves.
- Interactive prompts are unnecessary — the devkit is designed for CI and scripted use.

## References

- ARFA 1.3 Specification, §2.1: Zero External Dependencies Principle
- KaririCode Specification V4.0, §3.2: Package Independence
