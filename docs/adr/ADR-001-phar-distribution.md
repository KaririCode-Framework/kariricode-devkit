# ADR-001: PHAR-Based Distribution Strategy

**Status:** Accepted
**Date:** 2025-02-28
**Deciders:** Walmir Silva
**Context:** KaririCode Framework Devkit v1.0.0

## Context

The KaririCode Framework comprises 35+ components, each requiring identical development tooling: PHPUnit, PHPStan, PHP-CS-Fixer, Rector, and Psalm. The conventional Composer `require-dev` approach results in:

- **175+ redundant dependency entries** across the ecosystem (5 tools × 35+ components).
- **~120 MB per component** in `vendor/` from dev dependencies alone.
- **Inconsistent tool versions** when components are updated at different cadences.
- **5+ config files per project root** (phpunit.xml.dist, phpstan.neon, etc.) with near-identical content.

## Decision

Distribute the devkit as a **PHAR archive** compiled via [humbug/box](https://github.com/box-project/box), bundling all five quality tools and their transitive dependencies into a single `kcode.phar` file (~15-20 MB).

Additionally, support **Composer library mode** (`composer require --dev kariricode/devkit`) for environments where PHAR is impractical (e.g., CI caches, Dependabot).

## Rationale

### PHAR Advantages

1. **Single artifact** — One file replaces 5 `require-dev` entries and all their transitive dependencies.
2. **Version pinning** — The PHAR freezes exact tool versions. Every component uses the same PHPStan 2.x, same Rector 2.x, etc.
3. **Zero-conflict installation** — PHAR runs in an isolated class-loading context. No dependency conflicts between the project's production code and the analysis tools.
4. **Portable** — A CI pipeline can `wget` the PHAR and run it without `composer install` for dev dependencies.

### Composer Library Fallback

The `bin/kcode` entry point resolves autoloaders in priority order:

```
1. PHAR-internal vendor/autoload.php
2. Project-local vendor/autoload.php  (Composer require-dev)
3. Global Composer vendor/autoload.php
```

This makes the package usable in both distribution modes without code changes.

### Alternatives Considered

| Alternative | Rejected Because |
|---|---|
| Composer plugin | Couples to Composer lifecycle; does not solve version consistency |
| Docker image | Adds container overhead; poor IDE integration |
| Makefile + global tools | No version pinning; system-dependent |
| Mono-repo shared config | Doesn't scale to independently versioned components |

## Consequences

### Positive

- Component `composer.json` shrinks from 5+ dev dependencies to 1 (or 0 with PHAR).
- Tool version upgrades happen once in devkit, propagate to all components on next PHAR release.
- CI pipelines download one artifact instead of resolving 5 dependency trees.

### Negative

- PHAR compilation requires `humbug/box` and a release pipeline.
- PHAR file size (~15-20 MB) must be managed; GZ compression mitigates this.
- Developers must update the PHAR when tool versions change (mitigated by Composer fallback).

### Risks

- **Tool compatibility** — A bundled tool version may conflict with a component's minimum PHP version. Mitigated by targeting PHP 8.4+ across the ecosystem.
- **PHAR readonly** — PHP `phar.readonly=1` (default) prevents runtime modification. This is acceptable since the PHAR is read-only by design.

## References

- [humbug/box documentation](https://github.com/box-project/box/blob/main/doc/configuration.md)
- [PHP PHAR extension](https://www.php.net/manual/en/book.phar.php)
- [Composer bin-dir specification](https://getcomposer.org/doc/articles/vendor-binaries.md)
