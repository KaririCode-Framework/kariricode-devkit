# KaririCode Devkit

<div align="center">

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-22c55e.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-4F46E5)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/Tests-41%20passing-22c55e)](https://kariricode.org)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-orange)](https://kariricode.org)

**Unified quality toolchain for the KaririCode Framework ecosystem.**

One dependency. One CLI. Zero config drift across 35+ components.

[Installation](#installation) · [Quick Start](#quick-start) · [CLI Reference](#cli-reference) · [Configuration](#configuration) · [CI Integration](#ci-integration)

</div>

---

## The Problem

Every KaririCode component independently maintains five dev tools — leading to hundreds of near-identical, manually-drifting configurations:

```
composer.json          →  5 require-dev entries per component
phpunit.xml.dist       →  ~60 lines per component
phpstan.neon           →  ~25 lines per component
.php-cs-fixer.dist.php →  ~50 lines per component
rector.php             →  ~30 lines per component
psalm.xml              →  ~20 lines per component
```

Across **35+ components**, that's **175+ redundant dependency entries** and **175+ near-identical config files**. Updating a single CS-Fixer rule means 35 pull requests.

## The Solution

```bash
composer require --dev kariricode/devkit
vendor/bin/kcode init
```

Devkit generates `.kcode/` — a gitignored directory of configs derived directly from your `composer.json`. Your five dev dependencies become one, with one canonical source of truth:

```
your-component/
├── devkit.php               ← Optional overrides (committed to git)
├── .kcode/                  ← Generated (gitignored — run `kcode init` to recreate)
│   ├── phpunit.xml.dist
│   ├── phpstan.neon
│   ├── php-cs-fixer.php
│   ├── rector.php
│   ├── psalm.xml
│   └── build/              ← Coverage reports, caches, JUnit XML
├── composer.json
├── src/
└── tests/
```

```diff
  "require-dev": {
-     "phpunit/phpunit": "^12.0",
-     "phpstan/phpstan": "^2.0",
-     "friendsofphp/php-cs-fixer": "^3.64",
-     "rector/rector": "^2.0",
-     "vimeo/psalm": "^6.0"
+     "kariricode/devkit": "^1.0"
  }
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.4 or higher |
| Composer | 2.x |

---

## Installation

### As a Composer dependency *(recommended)*

```bash
composer require --dev kariricode/devkit
```

### As a standalone PHAR

```bash
wget https://github.com/kariricode/devkit/releases/latest/download/kcode.phar
chmod +x kcode.phar
sudo mv kcode.phar /usr/local/bin/kcode
```

---

## Quick Start

```bash
# Step 1 — Generate .kcode/ configs from your composer.json
vendor/bin/kcode init

# Step 2 — Remove old deps/configs (interactive prompt)
vendor/bin/kcode migrate

# Step 3 — Run the full quality pipeline
vendor/bin/kcode quality
```

---

## CLI Reference

### `kcode init`

Generates or regenerates all configs inside `.kcode/` from your `composer.json`. Safe to run at any time — existing files are cleanly overwritten.

```bash
kcode init           # Generate configs
kcode init --config  # Also scaffold devkit.php with all available keys
```

```
✓ Project: kariricode/parser
✓ Namespace: KaririCode\Parser
✓ PHP: 8.4
✓ Generated 5 config file(s) in .kcode/
✓ .kcode/ added to .gitignore
⚠ Found 8 redundant item(s) that kcode replaces.
  Run kcode migrate to review and clean up.
```

---

### `kcode migrate`

Scans for redundant dev dependencies, root-level config files, and cache paths that Devkit now manages. Presents a full report before making any change.

```bash
kcode migrate                   # Interactive (default)
kcode migrate --dry-run         # Preview only — no changes applied
kcode migrate --no-interaction  # Auto-remove (for CI)
```

**Detected items:**

| Category | Items |
|---|---|
| `require-dev` packages | `phpunit/phpunit`, `phpstan/phpstan`, `phpstan` extensions, `php-cs-fixer`, `rector/rector`, `vimeo/psalm` |
| Root config files | `phpunit.xml(.dist)`, `phpstan.neon(.dist)`, `.php-cs-fixer(.dist).php`, `rector.php`, `psalm.xml(.dist)` |
| Root cache paths | `.phpunit.cache`, `.phpunit.result.cache`, `.phpstan`, `.php-cs-fixer.cache`, `.psalm` |

---

### `kcode test`

Runs PHPUnit with the `.kcode/phpunit.xml.dist` configuration.

```bash
kcode test                           # Run all test suites
kcode test --suite=Unit              # Run a single suite
kcode test --coverage                # Generate HTML coverage report
kcode test --filter=testMyMethod     # Pass any PHPUnit argument through
```

---

### `kcode analyse`

Runs PHPStan and Psalm in sequence.

```bash
kcode analyse
```

---

### `kcode cs:fix`

Applies PHP-CS-Fixer with the KaririCode code standard.

```bash
kcode cs:fix          # Fix all violations
kcode cs:fix --check  # Dry-run — only report (no modifications)
```

---

### `kcode rector`

Runs Rector. Defaults to read-only preview mode.

```bash
kcode rector        # Preview changes (no files modified)
kcode rector --fix  # Apply changes
```

---

### `kcode quality`

Full sequential pipeline in optimal order:

```
cs:check → phpstan → psalm → phpunit
```

```bash
kcode quality
```

```
✓ cs-fixer   passed (1.23s)
✓ phpstan     passed (4.56s)
✓ psalm       passed (3.21s)
✓ phpunit     passed (2.10s)

✓ All 4 tool(s) passed (11.10s total)
```

---

### `kcode format`

Applies all auto-formatting in sequence: CS-Fixer fix + Rector apply.

```bash
kcode format
```

---

### `kcode security`

Runs `composer audit` to check for known security vulnerabilities.

```bash
kcode security
```

---

### `kcode clean`

Removes `.kcode/build/` — caches, coverage reports, JUnit XML.

```bash
kcode clean
```

---

## Configuration

### Auto-detection

Devkit reads your `composer.json` to derive all defaults automatically:

| Detected Field | Source |
|---|---|
| Project name | `name` |
| Root namespace | `autoload.psr-4` (first key) |
| PHP version | `require.php` |
| Source directories | `autoload.psr-4` values |
| Test directories | `autoload-dev.psr-4` values |
| Test suites | Standard subdirs: `Unit/`, `Integration/`, `Conformance/`, `Functional/` |

### Project overrides via `devkit.php`

Create `devkit.php` in your project root to override any default. Scaffold a fully-annotated file with:

```bash
kcode init --config
```

Example:

```php
<?php

declare(strict_types=1);

return [
    'phpstan_level'    => 8,                      // 0–9 (default: 9)
    'psalm_level'      => 4,                      // 1–9 (default: 3)
    'exclude_dirs'     => ['src/Contract'],       // Excluded from analysis
    'test_suites'      => [
        'Unit'         => 'tests/Unit',
        'Integration'  => 'tests/Integration',
    ],
    'coverage_exclude' => ['src/Exception'],
    'cs_fixer_rules'   => [                       // Merged with KaririCode defaults
        'yoda_style'   => false,
    ],
    'rector_sets'      => [                       // Replaces defaults entirely
        'LevelSetList::UP_TO_PHP_84',
        'SetList::CODE_QUALITY',
    ],
];
```

After editing, run `kcode init` to regenerate `.kcode/`.

### Override reference

| Key | Type | Default | Merge strategy |
|---|---|---|---|
| `project_name` | `string` | From `composer.json` | Replace |
| `namespace` | `string` | From PSR-4 autoload | Replace |
| `php_version` | `string` | From `require.php` | Replace |
| `phpstan_level` | `int` | `9` | Replace |
| `psalm_level` | `int` | `3` | Replace |
| `source_dirs` | `list<string>` | From PSR-4 autoload | Replace |
| `test_dirs` | `list<string>` | From PSR-4 autoload-dev | Replace |
| `exclude_dirs` | `list<string>` | `['src/Contract']` | Replace |
| `test_suites` | `array<string, string>` | Auto-detected | Replace |
| `coverage_exclude` | `list<string>` | `['src/Exception']` | Replace |
| `cs_fixer_rules` | `array<string, mixed>` | KaririCode standard | **Merge** (your rules win) |
| `rector_sets` | `list<string>` | KaririCode standard | Replace |
| `tools` | `array<string, string>` | — | Informational |

### File ownership

| File | Location | Committed | Managed by |
|---|---|---|---|
| `devkit.php` | Project root | ✅ Yes | Developer |
| `.kcode/` | Generated dir | ❌ No (gitignored) | `kcode init` |

### KaririCode coding standard

The default CS-Fixer ruleset includes:

- **PSR-12** baseline with **PHP 8.4 migration** rules
- Strict types enforcement (`declare_strict_types`)
- Compiler-optimized native function invocations
- Alphabetically ordered imports
- Trailing commas in multiline arrays, arguments, and parameters

See [SPEC-001](docs/spec/SPEC-001-project-detection.md) §7 for the complete rule set.

---

## CI Integration

### GitHub Actions — unified pipeline

```yaml
name: Quality
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: pcov
      - run: composer install --no-progress --no-scripts
      - run: vendor/bin/kcode init
      - run: vendor/bin/kcode migrate --no-interaction
      - run: vendor/bin/kcode quality
```

### GitHub Actions — parallel jobs

```yaml
jobs:
  cs-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-progress --no-scripts
      - run: vendor/bin/kcode init && vendor/bin/kcode cs:fix --check

  analyse:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-progress --no-scripts
      - run: vendor/bin/kcode init && vendor/bin/kcode analyse

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4', coverage: pcov }
      - run: composer install --no-progress --no-scripts
      - run: vendor/bin/kcode init && vendor/bin/kcode test --coverage
```

---

## Migrating from Root-Level Configs

```bash
# 1. Add devkit as a dev dependency
composer require --dev kariricode/devkit

# 2. Generate .kcode/ configs
vendor/bin/kcode init

# 3. Review and remove redundant files (interactive)
vendor/bin/kcode migrate

# 4. Apply composer.json changes
composer update

# 5. Verify the pipeline
vendor/bin/kcode quality
```

Use `--dry-run` to inspect what would be removed before committing.

---

## Architecture

### Component layout

```
src/
├── Contract/        Interfaces: ConfigGenerator, ToolRunner
├── Core/            Devkit façade · ProjectDetector · ProcessExecutor
├── Configuration/   Config generators (5 tools)
├── Runner/          Tool runners (6 runners + AbstractToolRunner)
├── Command/         CLI commands (10 commands + Application + AbstractCommand)
├── Exception/       Exception hierarchy
└── ValueObject/     Immutable results: ToolResult · QualityReport · MigrationReport
```

### Dependency flow

```
Command → Core (Devkit) → Contract ← Runner / Configuration
```

Strict unidirectional flow. No circular dependencies. Commands call the `Devkit` façade. Runners and generators implement contracts. Core orchestrates.

### Key design decisions

| Decision | Rationale | ADR |
|---|---|---|
| Native PHAR builder | Avoids Box 4.x / PHP 8.4 incompatibility | — |
| Zero external runtime dependencies | Sub-millisecond boot, no version conflicts | [ADR-002](docs/adr/ADR-002-zero-dependencies.md) |
| Config generation over bundling | Eliminates drift across 35+ components | [ADR-003](docs/adr/ADR-003-config-generation.md) |
| Three-tier binary resolution | PHAR → vendor → global fallback | [ADR-004](docs/adr/ADR-004-binary-resolution.md) |
| `.kcode/` directory | Clean project root, single gitignore entry | [ADR-005](docs/adr/ADR-005-kariricode-directory.md) |
| Immutable value objects | Thread-safe, ARFA 1.3 compliant | [ADR-006](docs/adr/ADR-006-immutable-value-objects.md) |

### Specifications

| Spec | Covers |
|---|---|
| [SPEC-001](docs/spec/SPEC-001-project-detection.md) | Project detection, config merging, defaults |
| [SPEC-002](docs/spec/SPEC-002-cli-interface.md) | CLI interface, argument parsing, output format |
| [SPEC-003](docs/spec/SPEC-003-tool-runner.md) | Runner contract, process execution, result handling |

---

## Project Stats

| Metric | Value |
|---|---|
| PHP source files | 38 |
| Total source lines | ~2,900 |
| External runtime dependencies | 0 |
| Quality tools supported | 6 (PHPUnit, PHPStan, PHP-CS-Fixer, Rector, Psalm, Composer Audit) |
| CLI commands | 10 |
| PHPStan level | 9 (0 errors) |
| Test suite | 41 tests · 81 assertions |
| PHP version | 8.4+ |
| ARFA compliance | 1.3 |

---

## Building `kcode.phar`

```bash
# Requirements: PHP 8.4+ · Composer 2.x · phar.readonly=0

# Full release pipeline (recommended)
make release

# Manual
composer install
php -d phar.readonly=0 bin/build-phar.php
php build/kcode.phar --version    # → KaririCode Devkit 1.0.0

# Install globally
sudo mv build/kcode.phar /usr/local/bin/kcode
```

See [docs/BUILDING.md](docs/BUILDING.md) for full build documentation, troubleshooting, and release automation details.

---

## Contributing

```bash
git clone https://github.com/kariricode/devkit.git
cd devkit
composer install
vendor/bin/kcode init
vendor/bin/kcode quality        # Must pass before opening a PR
```

CI enforces code quality and a PHAR smoke test on every push and PR.

---

## License

[MIT License](LICENSE) © [Walmir Silva](mailto:walmir.silva@kariricode.org)

---

<div align="center">

Part of the **[KaririCode Framework](https://kariricode.org)** ecosystem.

[kariricode.org](https://kariricode.org) · [GitHub](https://github.com/kariricode) · [Packagist](https://packagist.org/packages/kariricode/)

</div>
