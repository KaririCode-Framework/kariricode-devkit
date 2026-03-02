# SPEC-002: CLI Command Interface and Execution Pipeline

**Version:** 1.0.0
**Status:** Normative
**Date:** 2025-02-28
**Author:** Walmir Silva

## 1. Purpose

This specification defines the CLI interface, command dispatch, argument parsing, and output formatting for the `kcode` binary.

## 2. Binary Invocation

```
kcode <command> [options] [arguments]
kcode --help | -h | help
kcode --version | -V
```

Exit codes follow Unix conventions: `0` = success, `1` = tool failure, `127` = binary not found.

## 3. Command Registry

### 3.1 Available Commands

| Command | Description | Tools Invoked |
|---|---|---|
| `init` | Generate `.kcode/` config directory | None (filesystem only) |
| `migrate` | Detect and remove redundant deps/configs | None (filesystem + composer.json) |
| `test` | Run PHPUnit tests | phpunit |
| `analyse` | Run static analysis | phpstan, psalm |
| `cs:fix` | Fix code style | php-cs-fixer |
| `rector` | Run Rector refactoring | rector |
| `security` | Vulnerability scanning | composer audit |
| `quality` | Full pipeline | cs-fixer, phpstan, psalm, phpunit |
| `format` | Apply formatting | cs-fixer, rector |
| `clean` | Remove build artifacts | None (filesystem only) |

### 3.2 Command-Specific Options

#### init

| Option | Effect |
|---|---|
| `--config` | Scaffold a `devkit.php` override file in the project root |

#### test

| Option | Effect |
|---|---|
| `--coverage` | Enable HTML coverage report in `.kcode/build/coverage/` |
| `--suite=Name` | Run only the named test suite |
| All other `--*` flags | Passed through to PHPUnit |

#### migrate

| Option | Effect |
|---|---|
| `--dry-run` or `--check` | Show findings without making changes |
| `-n` or `--no-interaction` | Apply all removals without prompting |

#### cs:fix

| Option | Effect |
|---|---|
| `--check` or `--dry-run` | Check-only mode (no modifications) |
| All other `--*` flags | Passed through to PHP-CS-Fixer |

#### rector

| Option | Effect |
|---|---|
| `--fix` or `--apply` | Apply changes (default is dry-run preview) |
| All other `--*` flags | Passed through to Rector |

#### quality

No command-specific options. Runs the pipeline: `cs-fixer --dry-run → phpstan → psalm → phpunit`. Unavailable tools are skipped automatically.

## 4. Dispatch Architecture

### 4.1 Application Router

`Command\Application` maps command names to `AbstractCommand` instances:

```
argv → strip script name → match command → execute(Devkit, arguments) → exit code
```

Unknown commands produce exit code 1 with a help suggestion.

### 4.2 Argument Parsing

`AbstractCommand` provides parsing utilities consumed by subclasses:

| Method | Purpose | Example |
|---|---|---|
| `hasFlag($args, ...$flags)` | Boolean flag detection | `hasFlag($args, '--coverage')` |
| `option($args, $key, $default)` | Key-value extraction | `option($args, 'suite')` → `'Unit'` |
| `positional($args)` | Non-flag arguments | Filters out all `--*` prefixed args |
| `passthrough($args, $consume)` | Forward remaining args | Strips consumed flags, passes rest |

### 4.3 Passthrough Pattern

Commands consume their own flags and forward everything else to the underlying tool:

```php
// CsFixCommand
$dryRun = $this->hasFlag($arguments, '--check', '--dry-run');  // consume
$passthrough = $this->passthrough($arguments, ['--check', '--dry-run']);  // strip consumed
$result = $devkit->run('cs-fixer', [...$extraArgs, ...$passthrough]);  // forward rest
```

This allows users to pass tool-native flags without the devkit needing to enumerate all possibilities:

```bash
kcode test --filter=testSpecificMethod --verbose
kcode cs:fix --check --using-cache=no
kcode analyse --level=7
```

## 5. Output Formatting

### 5.1 Output Streams

| Stream | Content |
|---|---|
| STDOUT | Info messages, tool output, banners |
| STDERR | Error messages, exception messages |

### 5.2 ANSI Formatting

| Method | Prefix | Color |
|---|---|---|
| `info()` | `✓` | Green (32) |
| `warning()` | `⚠` | Yellow (33) |
| `error()` | `✗` | Red (31) |
| `banner()` | Ruler + bold title | Cyan (36) + Bold (1) |
| `line()` | None | Default |

### 5.3 Banner Format

```
────────────────────────────────────────────────────────────   (cyan)
  KaririCode Devkit — Command Name                            (bold)
────────────────────────────────────────────────────────────   (cyan)
```

60-character ruler width. Title indented by 2 spaces.

## 6. Error Handling

### 6.1 Command-Level

`Application::run()` wraps each command execution in `try/catch`. Unhandled exceptions produce:

```
✗ Exception message here
```

Exit code: 1.

### 6.2 Tool-Level

When a tool binary is not found, `AbstractToolRunner::run()` returns a `ToolResult` with:

- `exitCode: 127`
- `stderr: 'Binary not found for "toolName".'`
- `success: false`

Commands that iterate over multiple tools (analyse, quality, format) skip unavailable tools with a warning and continue.

## 7. Quality Pipeline Execution Order

The `quality` command delegates to `Devkit::quality()`, which executes tools in this fixed order:

```
1. cs-fixer   (--dry-run --diff)
2. phpstan     (default args)
3. psalm       (default args)
4. phpunit     (default args)
```

**Rationale for order:** Style issues are cheapest to detect. Static analysis catches type errors before tests run. Tests are the most expensive operation and run last.

Results are aggregated into a `QualityReport` and the pipeline always completes all available tools (no fail-fast).
