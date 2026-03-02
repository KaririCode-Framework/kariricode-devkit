# KaririCode Devkit — Documentation Index

Documentation for the **kariricode/devkit** package — the unified quality toolchain for the KaririCode Framework ecosystem.

---

## Architecture Decision Records

| ADR | Title | Status |
|---|---|---|
| [ADR-001](adr/ADR-001-phar-distribution.md) | PHAR-Based Distribution Strategy | Accepted |
| [ADR-002](adr/ADR-002-zero-dependencies.md) | Zero External Dependencies in Core | Accepted |
| [ADR-003](adr/ADR-003-config-generation.md) | Configuration Generation Over Manual Configuration | Accepted |
| [ADR-004](adr/ADR-004-binary-resolution.md) | Three-Tier Binary Resolution Strategy | Accepted |
| [ADR-005](adr/ADR-005-kariricode-directory.md) | Centralized `.kcode/` Directory Convention | Accepted |
| [ADR-006](adr/ADR-006-immutable-value-objects.md) | Immutable Value Objects for Tool Results | Accepted |

## Specifications

| Spec | Title | Version |
|---|---|---|
| [SPEC-001](spec/SPEC-001-project-detection.md) | Project Detection and Configuration Merging | 1.0.0 |
| [SPEC-002](spec/SPEC-002-cli-interface.md) | CLI Command Interface and Execution Pipeline | 1.0.0 |
| [SPEC-003](spec/SPEC-003-tool-runner.md) | Tool Runner Abstraction and Process Execution | 1.0.0 |

## Quick Navigation

| Document | Description |
|---|---|
| [README](../README.md) | Installation, usage, CLI reference, architecture |
| [BUILDING.md](BUILDING.md) | PHAR compilation, troubleshooting, release automation |
| [CHANGELOG](../CHANGELOG.md) | Release history and migration notes |
| [composer.json](../composer.json) | Package definition and Composer scripts |
| [Makefile](../Makefile) | Build automation (`make help` for all targets) |
| [ci.yml](../.github/workflows/ci.yml) | Quality pipeline on push/PR |
| [code-quality.yml](../.github/workflows/code-quality.yml) | Security, PHPStan, CS-Fixer via kcode CLI |
| [release.yml](../.github/workflows/release.yml) | Automated PHAR release on tag push |
