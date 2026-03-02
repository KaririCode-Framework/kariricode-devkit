# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- **bin/kcode:** Fatal `TypeError` when invoked via Composer scripts — `$argv` was inaccessible inside the static IIFE. Fix passes it as an explicit parameter (`$argv ?? []`).
- **composer.json:** `phpunit/phpunit` constraint updated from `^11.0` to `^12.0` to match the installed version (12.4.2). Composer scripts renamed to `kcode:*` prefix to avoid collision with built-in Composer commands (e.g. `init`). `allow-plugins.infection/extension-installer: false` added to suppress spurious warnings.
- **Makefile:** `VERSION` now resolved from `git describe --tags` (falls back to `box.json` metadata). `install`/`install-prod` add `--no-scripts` to prevent broken `kcode init` invocation during dependency installation. New `security` target (`kcode security`). New `_require-kcode` guard on all quality targets. `distclean` no longer removes `composer.lock` (library — not tracked). `check-env` now correctly detects `vendor/bin/kcode`.
- **phpunit.xml:** `memory_limit` reduced from 1G to 256M (actual usage ≈ 22 MB). Opening tag attributes reformatted one-per-line.

### Changed

- **.github/workflows/ci.yml:** Added `develop` branch to push/PR triggers.
- **.github/workflows/code-quality.yml:** Replaced 378-line legacy workflow (PHPMD + runtime `composer require` anti-pattern) with 160-line workflow aligned to the kcode CLI. Jobs: `dependencies → security → phpstan → cs-fixer → quality-summary`.
- **.gitignore:** Expanded from 8 to 42 patterns, covering legacy scaffold files, tool configs outside the toolchain, IDE artefacts, environment files, and OS artefacts.

### Removed (cleanup)

- 42 legacy scaffold files: `.config/`, `.docs/`, `.make/`, `devkit/`, `docker-compose.yml`, `.env.example`, `.env.xdebug`, `.php-cs-fixer.php`, `phpcs.xml`, `phpstan.neon`, `infection.json`, `phpbench.json`, `.editorconfig`, `.gitattributes`, `.vscode/` (read-only root-owned), `build/`, `coverage/`.
- 4 prototype test files targeting removed classes (`UserProfile`, `Email`, `UserId`, `UserRole`).

### Added

- **tests/Unit/Exception:** `DevkitExceptionTest`, `ConfigurationExceptionTest`, `ToolExceptionTest`.
- **tests/Unit/ValueObject:** `ToolResultTest`, `QualityReportTest`, `MigrationReportTest`.
- **tests/Unit/Core:** `ProjectContextTest`, `DevkitConfigTest`.
- Full unit test suite: **41 tests, 81 assertions** — all passing on PHP 8.4.14 / PHPUnit 12.4.2.

---

## [1.0.0] — 2025-12-01

### Added

- **Core:** `Devkit` orchestrator, `ProjectDetector`, `ProjectContext`, `DevkitConfig`, `ProcessExecutor`.
- **Contracts:** `ConfigGenerator` and `ToolRunner` interfaces.
- **Configuration generators:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm.
- **Tool runners:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm, Composer Audit.
- **CLI commands:** `init`, `migrate`, `test`, `analyse`, `cs:fix`, `rector`, `security`, `quality`, `format`, `clean`.
- **Migration detector:** Scans `composer.json` require-dev and project root for redundant dependencies, config files, and cache paths. Interactive cleanup with `--dry-run` and `--no-interaction` modes.
- **CLI framework:** Zero-dependency `Application` router with ANSI output, argument parsing, and passthrough.
- **Value objects:** `ToolResult` and `QualityReport` (immutable, readonly).
- **Exception hierarchy:** `DevkitException`, `ConfigurationException`, `ToolException`.
- **PHAR support:** `box.json` configuration for humbug/box compilation.
- **Build automation:** `Makefile` with targets: `install`, `build`, `verify`, `self-test`, `quality`, `security`, `clean`, `release`.
- **CI/CD:** GitHub Actions workflows for quality checks (`ci.yml`) and automated PHAR releases (`release.yml`).
- **Build documentation:** Complete PHAR build guide with prerequisites, troubleshooting, and verification steps.
- **Binary resolution:** Three-tier strategy (PHAR → vendor → global PATH).
- **Project detection:** Automatic namespace, PHP version, source/test directory discovery from `composer.json`.
- **Override system:** `devkit.php` at project root for per-project customization with type-safe merging. Scaffold with `kcode init --config`.
- **Documentation:** README, 6 ADRs, 3 specifications, docs index.
