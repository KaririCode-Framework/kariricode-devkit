# KaririCode Devkit

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-blue)](https://kariricode.org)

**Unified quality toolchain for the KaririCode Framework ecosystem.**

Devkit encapsulates PHPUnit, PHPStan, PHP-CS-Fixer, Rector, and Psalm configurations into a single dependency. One `require-dev`, one CLI, zero config drift across 35+ components.

## The Problem

Every KaririCode component independently maintains:

```
composer.json          →  5 require-dev entries
phpunit.xml.dist       →  ~60 lines
phpstan.neon           →  ~25 lines
.php-cs-fixer.dist.php →  ~50 lines
rector.php             →  ~30 lines
psalm.xml              →  ~20 lines
```

Across 35+ components, that's **175+ redundant dependency entries** and **175+ near-identical config files**. Updating a single CS-Fixer rule means 35 PRs.

## The Solution

```bash
composer require --dev kariricode/devkit
vendor/bin/kcode init
```

This generates a `.kcode/` directory (automatically added to `.gitignore`) with all configs derived from your `composer.json`:

```
your-component/
├── devkit.php                  ← Override config (committed to git)
├── .kcode/                     ← Gitignored, regenerate with `kcode init`
│   ├── phpunit.xml.dist        ← Generated
│   ├── phpstan.neon            ← Generated
│   ├── php-cs-fixer.php        ← Generated
│   ├── rector.php              ← Generated
│   ├── psalm.xml               ← Generated
│   └── build/                  ← Caches & reports
├── composer.json
├── src/
└── tests/
```

Your `composer.json` goes from:

```json
{
    "require-dev": {
        "phpunit/phpunit": "^12.0",
        "phpstan/phpstan": "^2.0",
        "friendsofphp/php-cs-fixer": "^3.64",
        "rector/rector": "^2.0",
        "vimeo/psalm": "^6.0"
    }
}
```

To:

```json
{
    "require-dev": {
        "kariricode/devkit": "^1.0"
    }
}
```

## Requirements

- PHP 8.4 or higher
- Composer 2.x

## Installation

### As a Composer dependency (recommended)

```bash
composer require --dev kariricode/devkit
```

### As a PHAR (standalone)

```bash
wget https://github.com/kariricode/devkit/releases/latest/download/kcode.phar
chmod +x kcode.phar
sudo mv kcode.phar /usr/local/bin/kcode
```

## Quick Start

```bash
# 1. Initialize configs
vendor/bin/kcode init

# 2. Migrate: remove old deps and configs
vendor/bin/kcode migrate

# 3. Run tests
vendor/bin/kcode test

# 4. Check code style
vendor/bin/kcode cs:fix --check

# 5. Run full quality pipeline
vendor/bin/kcode quality
```

## CLI Reference

### `kcode init`

Generates all config files inside `.kcode/`. Safe to run repeatedly — files are overwritten with fresh configs.

If redundant dev dependencies or root-level config files are detected, suggests running `kcode migrate`.

```bash
kcode init
```

Output:
```
✓ Project: kariricode/parser
✓ Namespace: KaririCode\Parser
✓ PHP: 8.4
✓ Generated 5 config file(s) in .kcode/
✓ .kcode/ added to .gitignore (regenerate with kcode init)
⚠ Found 8 redundant item(s) that kcode replaces.
  Run kcode migrate to review and clean up.
```

### `kcode migrate`

Detects redundant dev dependencies, root-level config files, and cache paths that the devkit replaces. Shows a detailed report and asks for confirmation before removing anything.

```bash
kcode migrate                         # Interactive (default)
kcode migrate --dry-run               # Show findings without changes
kcode migrate --no-interaction        # Auto-remove all without prompting
```

Output:
```
  Found 8 redundant item(s) that kcode replaces:

  composer.json require-dev

    ✗ phpunit/phpunit: ^11.0
    ✗ phpstan/phpstan: ^2.0
    ✗ friendsofphp/php-cs-fixer: ^3.64
    ✗ rector/rector: ^2.0
    ✗ vimeo/psalm: ^6.0

  Root-level config files

    ✗ phpunit.xml.dist
    ✗ phpstan.neon
    ✗ .php-cs-fixer.dist.php

? Remove these config files and cache paths? [y/N] y
✓ Removed 3 file(s)/directory(ies).
? Remove these packages from composer.json require-dev? [y/N] y
✓ Removed 5 package(s) from composer.json: phpunit/phpunit, phpstan/phpstan, ...

  Summary

✓ 8 item(s) cleaned up.
⚠ Run composer update to apply dependency changes.
```

### `kcode test`

Runs PHPUnit with the generated configuration.

```bash
kcode test                          # All tests
kcode test --suite=Unit             # Specific suite
kcode test --coverage               # With HTML coverage report
kcode test --filter=testMyMethod    # PHPUnit passthrough
```

### `kcode analyse`

Runs PHPStan and Psalm in sequence. Skips unavailable tools.

```bash
kcode analyse
```

### `kcode cs:fix`

Fixes code style violations. Use `--check` for dry-run (CI mode).

```bash
kcode cs:fix                        # Fix files
kcode cs:fix --check                # Check only (no modifications)
```

### `kcode rector`

Runs Rector refactoring in dry-run mode by default. Use `--fix` to apply changes.

```bash
kcode rector                        # Preview changes
kcode rector --fix                  # Apply changes
```

### `kcode quality`

Full quality pipeline in optimal order:

```
cs-fixer (check) → phpstan → psalm → phpunit
```

```bash
kcode quality
```

Output:
```
✓ cs-fixer passed (1.23s)
✓ phpstan passed (4.56s)
✓ psalm passed (3.21s)
✓ phpunit passed (2.10s)

✓ All 4 tool(s) passed (11.10s total)
```

### `kcode format`

Applies all auto-formatting: CS-Fixer fix + Rector apply.

```bash
kcode format
```

### `kcode security`

Runs `composer audit` for known vulnerability scanning.

```bash
kcode security
```

### `kcode clean`

Removes `.kcode/build/` directory (caches, coverage reports, JUnit XML).

```bash
kcode clean
```

## Configuration

### Automatic Detection

Devkit reads your `composer.json` to detect:

- **Project name** from `name`
- **Namespace** from `autoload.psr-4`
- **PHP version** from `require.php`
- **Source directories** from `autoload.psr-4` values
- **Test directories** from `autoload-dev.psr-4` values
- **Test suites** from standard subdirectories (`Unit/`, `Integration/`, `Conformance/`, `Functional/`)

### Project Overrides

Create `devkit.php` in the project root to customize behavior:

```bash
# Scaffold a fully-documented devkit.php with all available keys
kcode init --config
```

Or create it manually:

```php
<?php

declare(strict_types=1);

return [
    'phpstan_level'    => 8,                    // 0–9 (default: 9)
    'psalm_level'      => 4,                    // 1–9 (default: 3)
    'exclude_dirs'     => ['src/Contract'],     // excluded from analysis
    'test_suites'      => [
        'Unit'        => 'tests/Unit',
        'Integration' => 'tests/Integration',
        'Conformance' => 'tests/Conformance',
    ],
    'coverage_exclude' => ['src/Exception'],
    'cs_fixer_rules'   => [                     // MERGED with defaults
        'yoda_style' => false,
        'concat_space' => ['spacing' => 'one'],
    ],
    'rector_sets'      => [                     // REPLACES defaults
        'LevelSetList::UP_TO_PHP_84',
        'SetList::CODE_QUALITY',
    ],
];
```

After editing, regenerate configs:

```bash
kcode init
```

### File Ownership

| File | Location | Git Status | Owner |
|---|---|---|---|
| `devkit.php` | Project root | **Committed** | Developer |
| `.kcode/` | Generated dir | **Gitignored** | `kcode init` |

The `devkit.php` lives at the project root because `.kcode/` is gitignored. This ensures overrides survive `git clone` and are visible in code review.

Only specify keys that differ from defaults — unset keys are auto-detected from `composer.json`.

### Override Merge Strategy

| Key | Strategy | Behavior |
|---|---|---|
| Scalar values | Replace | `phpstan_level: 8` overrides default `9` |
| List values | Replace | `source_dirs: ['src', 'lib']` replaces default |
| `cs_fixer_rules` | **Merge** | Your rules are merged with KaririCode defaults (your rules win on conflict) |
| `rector_sets` | Replace | Your sets replace defaults entirely |

### Available Override Keys

| Key | Type | Default | Description |
|---|---|---|---|
| `project_name` | `string` | From `composer.json` | Project display name |
| `namespace` | `string` | From PSR-4 autoload | Root namespace |
| `php_version` | `string` | From `require.php` | Minimum PHP for analysis |
| `phpstan_level` | `int` | `9` | PHPStan strictness (0–9) |
| `psalm_level` | `int` | `3` | Psalm error level (1–9) |
| `source_dirs` | `list<string>` | From PSR-4 autoload | Source directories |
| `test_dirs` | `list<string>` | From PSR-4 autoload-dev | Test directories |
| `exclude_dirs` | `list<string>` | `['src/Contract']` | Excluded from analysis |
| `test_suites` | `array<string, string>` | Auto-detected | Suite name → relative dir |
| `coverage_exclude` | `list<string>` | `['src/Exception']` | Excluded from coverage |
| `cs_fixer_rules` | `array<string, mixed>` | KaririCode standard | CS-Fixer rules (merged) |
| `rector_sets` | `list<string>` | KaririCode standard | Rector set constants |
| `tools` | `array<string, string>` | — | Version constraints (informational) |

### Default Coding Standards

The KaririCode coding standard includes:

- **PSR-12** baseline
- **PHP 8.4 migration** rules
- **Strict types** enforcement
- **Compiler-optimized** native function invocations
- **Ordered imports** (alphabetical)
- **Trailing commas** in multiline arrays, arguments, and parameters

See [SPEC-001](docs/spec/SPEC-001-project-detection.md) §7 for the complete rule set.

## CI Integration

### GitHub Actions

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

### Separate CI Jobs

```yaml
jobs:
  cs-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-progress
      - run: vendor/bin/kcode cs:fix --check

  analyse:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-progress
      - run: vendor/bin/kcode analyse

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4', coverage: pcov }
      - run: composer install --no-progress
      - run: vendor/bin/kcode test --coverage
```

## Migration from Root-Level Configs

```bash
# 1. Install devkit
composer require --dev kariricode/devkit

# 2. Generate new configs
vendor/bin/kcode init

# 3. Interactive migration — detects and removes redundant deps/configs
vendor/bin/kcode migrate

# 4. Apply composer changes
composer update

# 5. Verify everything works
vendor/bin/kcode quality
```

The `migrate` command detects and offers to remove:

| Category | Items Detected |
|---|---|
| **composer.json require-dev** | phpunit/phpunit, phpstan/phpstan, phpstan extensions, friendsofphp/php-cs-fixer, rector/rector, vimeo/psalm |
| **Root-level config files** | phpunit.xml(.dist), phpstan.neon(.dist), .php-cs-fixer(.dist).php, rector.php, psalm.xml(.dist) |
| **Root-level cache paths** | .phpunit.cache, .phpunit.result.cache, .phpstan, .php-cs-fixer.cache, .psalm |

Use `--dry-run` to preview changes without applying them. Use `--no-interaction` for CI environments.

## Architecture

### Component Overview

```
src/
├── Contract/           Interfaces (ConfigGenerator, ToolRunner)
├── Core/               Orchestration (Devkit, ProjectDetector, ProcessExecutor)
├── Configuration/      Config generators (5 tools)
├── Runner/             Tool runners (6 runners + abstract base)
├── Command/            CLI commands (9 commands + app + base)
├── Exception/          Exception hierarchy
└── ValueObject/        Immutable results (ToolResult, QualityReport)
```

### Dependency Flow

```
Command → Core → Contract/ValueObject ← Runner/Configuration
```

Unidirectional. No cycles. Commands depend on the Devkit facade. Runners and generators depend on contracts and value objects. Core orchestrates everything.

### Key Design Decisions

| Decision | Rationale | ADR |
|---|---|---|
| PHAR distribution | Single artifact, version-pinned tools | [ADR-001](docs/adr/ADR-001-phar-distribution.md) |
| Zero external dependencies | Sub-millisecond boot, no conflicts | [ADR-002](docs/adr/ADR-002-zero-dependencies.md) |
| Config generation | Eliminates drift across 35+ components | [ADR-003](docs/adr/ADR-003-config-generation.md) |
| Three-tier binary resolution | PHAR → vendor → global fallback | [ADR-004](docs/adr/ADR-004-binary-resolution.md) |
| `.kcode/` directory | Clean project root, single gitignore | [ADR-005](docs/adr/ADR-005-kariricode-directory.md) |
| Immutable value objects | Thread-safe, ARFA 1.3 compliant | [ADR-006](docs/adr/ADR-006-immutable-value-objects.md) |

### Specifications

| Spec | Covers |
|---|---|
| [SPEC-001](docs/spec/SPEC-001-project-detection.md) | Project detection, config merging, defaults |
| [SPEC-002](docs/spec/SPEC-002-cli-interface.md) | CLI commands, argument parsing, output |
| [SPEC-003](docs/spec/SPEC-003-tool-runner.md) | Runner contract, process execution, results |

## Project Stats

| Metric | Value |
|---|---|
| PHP source files | 38 |
| Total PHP lines | ~2,900 |
| External runtime dependencies | 0 |
| Supported tools | 6 (PHPUnit, PHPStan, CS-Fixer, Rector, Psalm, Composer Audit) |
| CLI commands | 10 |
| PHP version | 8.4+ |
| ARFA compliance | 1.3 |

## Building kcode.phar

### Requirements

- PHP 8.4+ with `phar.readonly=0`
- Composer 2.x

### Quick Build

```bash
# Full release pipeline: quality → build → verify
make release
```

### Step-by-Step

```bash
# 1. Install dependencies
composer install

# 2. Compile PHAR
php -d phar.readonly=0 bin/build-phar.php

# 3. Verify
php build/kcode.phar --version
php build/kcode.phar --help

# 4. Install globally
chmod +x build/kcode.phar
sudo mv build/kcode.phar /usr/local/bin/kcode
```

### Automated Releases

Push a tag to trigger the GitHub Actions release workflow:

```bash
git tag v1.0.0
git push --tags
```

The CI compiles `kcode.phar`, verifies it, and attaches it to the GitHub Release automatically.

See [docs/BUILDING.md](docs/BUILDING.md) for full build documentation and troubleshooting.

## Contributing

1. Clone the repository
2. Run `composer install`
3. Run `vendor/bin/kcode init && vendor/bin/kcode quality`
4. Submit a PR

CI runs quality checks and a PHAR smoke test on every push and PR.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

**Walmir Silva** — [walmir.silva@kariricode.org](mailto:walmir.silva@kariricode.org)

Part of the [KaririCode Framework](https://kariricode.org) ecosystem.
