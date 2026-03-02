# ADR-004: Three-Tier Binary Resolution Strategy

**Status:** Accepted
**Date:** 2025-02-28
**Deciders:** Walmir Silva
**Context:** KaririCode Framework Devkit v1.0.0

## Context

The devkit must locate tool binaries (phpunit, phpstan, php-cs-fixer, rector, psalm, composer) at runtime. The installation context varies:

1. **PHAR distribution** — Tools are bundled inside the archive.
2. **Composer dependency** — Tools are in the project's `vendor/bin/`.
3. **Global installation** — Tools are installed system-wide via `composer global require` or package managers.

A single resolution strategy cannot serve all contexts.

## Decision

`ProcessExecutor::resolveBinary()` implements a three-tier cascade:

```
Tier 1: PHAR-internal  →  Phar::running(true) . '/' . $vendorBin
Tier 2: Project-local   →  $workingDirectory . '/' . $vendorBin
Tier 3: Global PATH     →  shell_exec('command -v $basename')
```

Resolution stops at the first match. If no tier resolves, `null` is returned and `AbstractToolRunner::run()` produces a `ToolResult` with exit code 127.

### Exception: ComposerAuditRunner

Composer is typically installed globally, not in `vendor/bin/`. `ComposerAuditRunner` overrides `binary()` to check global PATH first (Tier 3 before Tier 2).

## Rationale

### Tier Priority

| Tier | Context | Priority Justification |
|---|---|---|
| 1. PHAR-internal | Self-contained distribution | Guarantees exact version match |
| 2. Project vendor | Composer require-dev | Respects project-specific version constraints |
| 3. Global PATH | System-wide tools | Fallback for minimal setups |

### Why Not Rely on PATH Alone

PATH resolution provides no version guarantees. A globally installed PHPStan 1.x would be used even if the devkit expects 2.x. PHAR-internal binaries eliminate this risk entirely.

### Binary Caching

`AbstractToolRunner` caches the resolved binary path in `$resolvedBinary` (null-coalescing assignment). Resolution happens once per runner instance per process. Since the CLI is short-lived (single command execution), this is sufficient without cache invalidation.

### Security

Tier 3 uses `escapeshellarg()` around the binary basename to prevent shell injection:

```php
shell_exec('command -v ' . \escapeshellarg($basename) . ' 2>/dev/null')
```

## Consequences

### Positive

- PHAR users get deterministic tool versions with zero configuration.
- Composer users can override tool versions via their own `require-dev`.
- Global fallback enables `kcode` usage in minimal CI environments.

### Negative

- Tier precedence may surprise developers who expect their global tool to be used over the PHAR-bundled version.
- `command -v` is POSIX-specific; Windows support would require `where.exe` (out of scope for v1.0).

## References

- POSIX `command -v` specification: IEEE Std 1003.1-2017
- PHP `Phar::running()` documentation
- Composer `vendor/bin` binary proxy specification
