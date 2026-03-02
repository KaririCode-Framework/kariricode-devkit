# Changelog

All notable changes to **KaririCode Devkit** are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- **`bin/build-phar.php`:** Native PHP PHAR builder — compiles `kcode.phar` without `humbug/box`. Added as a workaround for a Box 4.x / PHP 8.4 incompatibility (`chdir(): Not a directory` during `endBuffering()`).
- **Test suite — `tests/Unit/Exception`:** `DevkitExceptionTest`, `ConfigurationExceptionTest`, `ToolExceptionTest`.
- **Test suite — `tests/Unit/ValueObject`:** `ToolResultTest`, `QualityReportTest`, `MigrationReportTest`.
- **Test suite — `tests/Unit/Core`:** `ProjectContextTest`, `DevkitConfigTest`.
- **Full unit test suite:** 41 tests, 81 assertions — all passing on PHP 8.4 / PHPUnit 12.4.5.

### Changed

- **`composer.json`:** `phpunit/phpunit` constraint updated from `^11.0` to `^12.0`. Composer scripts renamed to `kcode:*` prefix to avoid collision with built-in Composer commands. `infection/extension-installer: false` added to `allow-plugins` to suppress spurious warnings. The `build` script now calls `bin/build-phar.php` instead of `box compile`.
- **`Makefile`:** `build` target no longer requires `humbug/box` — uses `PHAR_BUILDER := bin/build-phar.php`. `VERSION` simplified to `git describe --tags` only (no `box.json` fallback). `check-env` shows PHAR builder status instead of Box version. `_require-box` guard replaced by `_require-phar-builder`. `install`/`install-prod` add `--no-scripts`. New `security` target. New `_require-kcode` guard on all quality targets. `distclean` no longer removes `composer.lock`. `check-env` correctly detects `vendor/bin/kcode`.
- **`.github/workflows/ci.yml`:** Added `develop` branch to push/PR triggers.
- **`.github/workflows/code-quality.yml`:** Replaced 378-line legacy workflow (PHPMD + runtime `composer require`) with a lean 160-line workflow aligned to the kcode CLI. Jobs: `dependencies → security → phpstan → cs-fixer → quality-summary`.
- **`.gitignore`:** Expanded from 8 to 42 patterns (legacy scaffold files, IDE artefacts, OS artefacts, environment files, tool caches).
- **`docs/BUILDING.md`:** Fully rewritten for `bin/build-phar.php` — removed all `humbug/box` references.
- **`phpunit.xml`:** `memory_limit` reduced from 1G to 256M. Attributes reformatted one-per-line.

### Fixed

- **`bin/kcode`:** Fatal `TypeError` when invoked via Composer scripts — `$argv` was inaccessible inside the static IIFE. Fixed by passing it as an explicit parameter (`$argv ?? []`).
- **`src/Configuration/PhpStanConfigGenerator`:** Removed `checkMissingIterableValueType` and `checkGenericClassInNonGenericObjectType` — both were removed from PHPStan 2.x core and caused `Invalid configuration` errors.
- **`src/Configuration/CsFixerConfigGenerator`:** Added `(string)` cast on `preg_replace()` return values in `exportRules()` to satisfy PHPStan level-9 type constraints.
- **`src/Core/Devkit`:** Added `false` guard on `file_get_contents()` in `appendGitignore()`. Added `@var \SplFileInfo` annotation in `removeRecursive()`.
- **`src/Core/DevkitConfig`:** Added `@var array<string, mixed>` before assigning loaded overrides. `toolVersions()` now correctly returns `array<string, string>` via `array_filter`.
- **`src/Core/MigrationDetector`:** Added `false !== $raw` guard on `file_get_contents()`. Added `@var` annotations on `json_decode()` result.
- **`src/Core/ProjectDetector`:** Added `false !== $raw` guard on `file_get_contents()`. Extracted `$psr4Source`, `$psr4Test`, `$projectName` with proper `is_array()` guards. Fixed `detectNamespace()` and `detectPhpVersion()` with nested `is_array()` / `is_string()` checks.
- **`src/Runner/*`:** Added `#[\Override]` attribute to `toolName()`, `vendorBin()`, and `defaultArguments()` across all six runners.
- **`src/ValueObject/MigrationReport`:** Added `false` guard after `file_get_contents()` and `json_encode()`. Extracted `$requireDev` with `@var` and `is_array()` guard. Fixed `removePackagesFromComposer()` to write back the updated `$requireDev` slice into `$composer` before `json_encode()`. Added `@var \SplFileInfo` in `removeRecursive()`.
- **Security:** `phpunit/phpunit` updated from 12.4.2 → 12.4.5 (fixes CVE-2026-24765, HIGH).

### Removed

- **`humbug/box`:** Removed as a build dependency. `bin/build-phar.php` replaces it.
- **Legacy scaffold files (42):** `.config/`, `.docs/`, `.make/`, `devkit/`, `docker-compose.yml`, `.env.example`, `.env.xdebug`, root-level tool configs, `build/`, `coverage/`.
- **Prototype test files (4):** Targeting removed classes `UserProfile`, `Email`, `UserId`, `UserRole`.

**Net result:** PHPStan level 9 — **0 errors** (down from 35). PHPUnit — **41 tests, 81 assertions passing**.

---

## [1.0.0] — 2025-12-01

### Added

- **Core:** `Devkit` orchestrator, `ProjectDetector`, `ProjectContext`, `DevkitConfig`, `ProcessExecutor`.
- **Contracts:** `ConfigGenerator` and `ToolRunner` interfaces.
- **Configuration generators:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm.
- **Tool runners:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm, Composer Audit.
- **CLI commands:** `init`, `migrate`, `test`, `analyse`, `cs:fix`, `rector`, `security`, `quality`, `format`, `clean`.
- **Migration detector:** Scans `composer.json` require-dev and project root for redundant files and cache paths. Interactive cleanup with `--dry-run` and `--no-interaction`.
- **CLI framework:** Zero-dependency `Application` router with ANSI output, argument parsing, and passthrough support.
- **Value objects:** `ToolResult`, `QualityReport`, `MigrationReport` (immutable, readonly).
- **Exception hierarchy:** `DevkitException`, `ConfigurationException`, `ToolException`.
- **Build automation:** `Makefile` with targets: `install`, `build`, `verify`, `self-test`, `quality`, `security`, `clean`, `release`.
- **CI/CD:** GitHub Actions workflows for quality checks (`ci.yml`) and automated PHAR releases (`release.yml`).
- **Binary resolution:** Three-tier strategy — PHAR → vendor → global PATH.
- **Override system:** `devkit.php` at project root with type-safe merging. Scaffold with `kcode init --config`.
- **Documentation:** README, 6 ADRs, 3 specifications, docs index.

---

[Unreleased]: https://github.com/kariricode/devkit/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/kariricode/devkit/releases/tag/v1.0.0
