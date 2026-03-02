# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2025-02-28

### Added

- **Core:** `Devkit` orchestrator, `ProjectDetector`, `ProjectContext`, `DevkitConfig`, `ProcessExecutor`.
- **Contracts:** `ConfigGenerator` and `ToolRunner` interfaces.
- **Configuration generators:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm.
- **Tool runners:** PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm, Composer Audit.
- **CLI commands:** `init`, `migrate`, `test`, `analyse`, `cs:fix`, `rector`, `security`, `quality`, `format`, `clean`.
- **Migration detector:** Scans composer.json require-dev and project root for redundant dependencies, config files, and cache paths. Interactive cleanup with `--dry-run` and `--no-interaction` modes.
- **CLI framework:** Zero-dependency `Application` router with ANSI output, argument parsing, and passthrough.
- **Value objects:** `ToolResult` and `QualityReport` (immutable, readonly).
- **Exception hierarchy:** `DevkitException`, `ConfigurationException`, `ToolException`.
- **PHAR support:** `box.json` configuration for humbug/box compilation.
- **Build automation:** `Makefile` with targets: install, build, verify, self-test, quality, clean, release.
- **CI/CD:** GitHub Actions workflows for quality checks (ci.yml) and automated PHAR releases (release.yml).
- **Build documentation:** Complete PHAR build guide with prerequisites, troubleshooting, and verification steps.
- **Binary resolution:** Three-tier strategy (PHAR → vendor → global PATH).
- **Project detection:** Automatic namespace, PHP version, source/test directory discovery from `composer.json`.
- **Override system:** `devkit.php` at project root for per-project customization with type-safe merging. Scaffold with `kcode init --config`.
- **Documentation:** README, 6 ADRs, 3 specifications, docs index.
