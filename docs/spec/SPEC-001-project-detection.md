# SPEC-001: Project Detection and Configuration Merging

**Version:** 1.0.0
**Status:** Normative
**Date:** 2025-02-28
**Author:** Walmir Silva

## 1. Purpose

This specification defines how the devkit detects project structure, loads configuration overrides, and produces the `ProjectContext` snapshot consumed by all generators and runners.

## 2. Scope

Covers `ProjectDetector`, `DevkitConfig`, and `ProjectContext` classes. Does not cover config file generation (see SPEC-003) or tool execution (see SPEC-002).

## 3. Terminology

| Term | Definition |
|---|---|
| **Project root** | Directory containing `composer.json` |
| **Devkit directory** | `{project_root}/.kcode/` |
| **Override file** | `devkit.php` (project root) — optional PHP file returning an associative array |
| **ProjectContext** | Immutable snapshot containing all resolved configuration values |

## 4. Detection Pipeline

### 4.1 Entry Point

```
ProjectDetector::detect(string $workingDirectory): ProjectContext
```

**Precondition:** `$workingDirectory` must be an absolute path.

**Throws:** `DevkitException::projectNotDetected()` when `composer.json` is absent.

### 4.2 Detection Sequence

```
1. Parse composer.json (JSON decode with JSON_THROW_ON_ERROR)
2. Load `devkit.php` from project root via DevkitConfig
3. For each configuration key:
   a. Check devkit.php override
   b. Fall back to composer.json detection
   c. Fall back to ecosystem default
4. Construct ProjectContext (immutable)
```

### 4.3 Detection Rules

#### 4.3.1 Project Name

| Priority | Source | Example |
|---|---|---|
| 1 | `devkit.php → project_name` | `"kariricode/parser"` |
| 2 | `composer.json → name` | `"kariricode/parser"` |
| 3 | `basename($workingDirectory)` | `"parser"` |

#### 4.3.2 Namespace

| Priority | Source | Example |
|---|---|---|
| 1 | `devkit.php → namespace` | `"KaririCode\\Parser"` |
| 2 | First key in `autoload.psr-4` | `"KaririCode\\Parser\\"` → `"KaririCode\\Parser"` |
| 3 | Literal `"App"` | — |

The trailing backslash from PSR-4 keys is stripped via `rtrim($ns, '\\')`.

#### 4.3.3 PHP Version

| Priority | Source | Example |
|---|---|---|
| 1 | `devkit.php → php_version` | `"8.4"` |
| 2 | First `\d+\.\d+` match in `require.php` | `">=8.4"` → `"8.4"` |
| 3 | Literal `"8.4"` | — |

#### 4.3.4 Source Directories

| Priority | Source | Result |
|---|---|---|
| 1 | `devkit.php → source_dirs` | Absolute paths from override |
| 2 | `autoload.psr-4` values | Absolute paths for existing directories |
| 3 | Fallback: `['src']` if directory exists | Single-element list |

#### 4.3.5 Test Directories

| Priority | Source | Result |
|---|---|---|
| 1 | `devkit.php → test_dirs` | Absolute paths from override |
| 2 | `autoload-dev.psr-4` values | Absolute paths for existing directories |
| 3 | Fallback: `['tests']` if directory exists | Single-element list |

**Important:** Source and test fallbacks use distinct default directories (`src` vs `tests`) to prevent misidentification.

#### 4.3.6 Test Suites

| Priority | Source |
|---|---|
| 1 | `devkit.php → test_suites` |
| 2 | Auto-detected from test directory subdirectories |

Auto-detection scans for standard suite names in order: `Unit`, `Integration`, `Conformance`, `Functional`. Each existing subdirectory becomes a named suite.

If no standard subdirectories exist, a single `Default` suite is registered pointing to the first test directory.

#### 4.3.7 PHPStan Level

| Priority | Source | Default |
|---|---|---|
| 1 | `devkit.php → phpstan_level` | — |
| 2 | Ecosystem default | `9` (maximum) |

#### 4.3.8 Psalm Level

| Priority | Source | Default |
|---|---|---|
| 1 | `devkit.php → psalm_level` | — |
| 2 | Ecosystem default | `3` |

#### 4.3.9 CS-Fixer Rules

Override rules are **merged** with ecosystem defaults via `array_merge()`. This means override keys replace defaults with the same key, and new keys are added.

#### 4.3.10 Rector Sets

Override sets **replace** ecosystem defaults entirely (not merged). This is because set order matters and partial merging could produce invalid configurations.

## 5. DevkitConfig

### 5.1 File Location

The `devkit.php` file lives at the **project root** (not inside `.kcode/`). This separation ensures:

- `devkit.php` is committed to git (user-owned configuration).
- `.kcode/` is gitignored (generated, deterministic output).

Scaffold with `kcode init --config`.

### 5.2 File Format

```php
<?php return [
    'key' => 'value',
    // ...
];
```

The file must return an associative array. Non-array returns throw `ConfigurationException::invalidOverride()`.

### 5.3 Type Safety

`DevkitConfig::get()` enforces type consistency between the override value and the default:

```php
$config->get('phpstan_level', 9);     // OK: int override for int default
$config->get('phpstan_level', '9');    // Throws: string override for int default
$config->get('source_dirs', null);     // OK: null default bypasses type check
```

**Rationale for null bypass:** `null` defaults indicate "detect from composer.json if not overridden." The override type is validated implicitly by the consuming code.

### 5.4 Unknown Keys

Unknown keys in `devkit.php` are silently ignored. This provides forward-compatibility — a newer devkit version can introduce keys without breaking older config files.

## 6. ProjectContext

### 6.1 Invariants

- All directory paths in `$sourceDirs` and `$testDirs` are absolute.
- `$devkitDir` and `$buildDir` are derived deterministically from `$projectRoot`.
- The object is `final readonly` — no mutation after construction.

### 6.2 Path Utilities

```php
$ctx->configPath('phpstan.neon')      // → /project/.kcode/phpstan.neon
$ctx->buildPath('coverage')           // → /project/.kcode/build/coverage
$ctx->relativeSourceDirs()            // → ['src']
$ctx->relativeTestDirs()              // → ['tests']
$ctx->relativize('/project/src')      // → 'src'
```

`relativize()` strips the `$projectRoot` prefix. Paths not under the project root are returned unchanged.

## 7. Ecosystem Defaults

### 7.1 CS-Fixer Rules

```
@PSR12, @PHP84Migration, array_syntax (short), ordered_imports (alpha),
no_unused_imports, trailing_comma_in_multiline, phpdoc_scalar,
unary_operator_spaces, binary_operator_spaces, blank_line_before_statement,
class_attributes_separation, method_argument_space,
single_trait_insert_per_statement, declare_strict_types,
native_function_invocation (@compiler_optimized, namespaced),
not_operator_with_successor_space
```

### 7.2 Rector Sets

```
LevelSetList::UP_TO_PHP_84, SetList::CODE_QUALITY, SetList::DEAD_CODE,
SetList::EARLY_RETURN, SetList::TYPE_DECLARATION
```

### 7.3 Default Exclusions

- Analysis excludes: `src/Contract` (interfaces are analyzed via implementors)
- Coverage excludes: `src/Exception` (exception classes are trivial)
