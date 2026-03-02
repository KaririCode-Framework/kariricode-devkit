# KaririCode Devkit — Documentation

## Architecture Decision Records (ADR)

| ADR | Title | Status |
|---|---|---|
| [ADR-001](adr/ADR-001-phar-distribution.md) | PHAR-Based Distribution Strategy | Accepted |
| [ADR-002](adr/ADR-002-zero-dependencies.md) | Zero External Dependencies in Core | Accepted |
| [ADR-003](adr/ADR-003-config-generation.md) | Configuration Generation Over Manual Configuration | Accepted |
| [ADR-004](adr/ADR-004-binary-resolution.md) | Three-Tier Binary Resolution Strategy | Accepted |
| [ADR-005](adr/ADR-005-kariricode-directory.md) | Centralized .kcode/ Directory Convention | Accepted |
| [ADR-006](adr/ADR-006-immutable-value-objects.md) | Immutable Value Objects for Tool Results | Accepted |

## Specifications

| Spec | Title | Version |
|---|---|---|
| [SPEC-001](spec/SPEC-001-project-detection.md) | Project Detection and Configuration Merging | 1.0.0 |
| [SPEC-002](spec/SPEC-002-cli-interface.md) | CLI Command Interface and Execution Pipeline | 1.0.0 |
| [SPEC-003](spec/SPEC-003-tool-runner.md) | Tool Runner Abstraction and Process Execution | 1.0.0 |

## Quick Navigation

- [README](../README.md) — Installation, usage, CLI reference
- [BUILDING](BUILDING.md) — PHAR compilation, box.json, troubleshooting
- [CHANGELOG](../CHANGELOG.md) — Release history
- [composer.json](../composer.json) — Package definition
- [box.json](../box.json) — PHAR compilation configuration
- [Makefile](../Makefile) — Build automation targets
- [CI workflow](../.github/workflows/ci.yml) — Quality checks on push/PR
- [Release workflow](../.github/workflows/release.yml) — PHAR build on tag push
