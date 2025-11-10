<div align="center">

# Quality Assurance

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-11.x-3776AB?style=for-the-badge&logo=php)](https://phpunit.de)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4F5B93?style=for-the-badge)](https://phpstan.org)
[![Psalm](https://img.shields.io/badge/Psalm-Type%20Safety-8A2BE2?style=for-the-badge)](https://psalm.dev)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Testing](#testing)
3. [Code Linting](#code-linting)
4. [Static Analysis](#static-analysis)
5. [Code Style & Formatting](#code-style--formatting)
6. [Code Coverage](#code-coverage)
7. [Mutation Testing](#mutation-testing)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

---

## Overview

### Scope

The QA module (`Makefile.qa.mk`) provides comprehensive quality assurance tools:
- **Testing**: PHPUnit test execution (unit, integration, functional)
- **Linting**: PHP syntax validation
- **Static Analysis**: PHPStan and Psalm
- **Code Style**: PHPCS and PHP-CS-Fixer
- **Coverage**: Code coverage reporting
- **Mutation**: Infection mutation testing

### Quality Gates
```
┌─────────────────────────────────────────────────────────┐
│                   Quality Pipeline                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  1. LINT           → Syntax errors?         → FAIL     │
│  2. CS-CHECK       → Style violations?      → FAIL     │
│  3. PHPSTAN        → Type errors?           → FAIL     │
│  4. PSALM          → Logic errors?          → FAIL     │
│  5. TEST           → Tests failing?         → FAIL     │
│  6. COVERAGE       → Coverage < 80%?        → WARN     │
│  7. MUTATION       → MSI < 80%?             → WARN     │
│                                                         │
│  ✓ ALL PASSED      → Ready for production            │
└─────────────────────────────────────────────────────────┘
```

### Tools Configuration

| Tool | Config File | Purpose |
|------|-------------|---------|
| PHPUnit | `phpunit.xml` | Test execution |
| PHPStan | `phpstan.neon` | Static analysis (level max) |
| Psalm | `psalm.xml` | Type checking & taint analysis |
| PHPCS | `phpcs.xml` | PSR-12 style checking |
| PHP-CS-Fixer | `.php-cs-fixer.php` | Modern PHP style fixing |
| Infection | `infection.json` | Mutation testing |
| PHPBench | `phpbench.json` | Performance benchmarking |

---

## Testing

### Test Execution

#### Run All Tests
```bash
make test

# Executes:
# vendor/bin/phpunit --colors=always --testdox

# Output:
# PHPUnit 11.5.2 by Sebastian Bergmann and contributors.
# 
# Parser\Lexer (KaririCode\Parser\Tests\Unit\Lexer)
#  ✔ Tokenizes simple string
#  ✔ Handles whitespace correctly
#  ✔ Recognizes keywords
# 
# Time: 00:01.234, Memory: 12.00 MB
# 
# OK (150 tests, 420 assertions)
```

**Options:**
- `--colors=always`: Colored output
- `--testdox`: Human-readable test names

#### Test Suites
```bash
# Unit tests only (fast, isolated)
make test-unit

# Integration tests (with dependencies)
make test-integration

# Functional tests (end-to-end)
make test-functional
```

**Test Suite Configuration (phpunit.xml):**
```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Integration">
        <directory>tests/Integration</directory>
    </testsuite>
    <testsuite name="Functional">
        <directory>tests/Functional</directory>
    </testsuite>
</testsuites>
```

### Test Organization

#### Directory Structure
```
tests/
├── Unit/                    # Pure unit tests
│   ├── Lexer/
│   │   ├── LexerTest.php
│   │   └── TokenTest.php
│   ├── Parser/
│   │   └── ParserTest.php
│   └── ...
│
├── Integration/             # Component integration
│   ├── LexerParserTest.php
│   └── ...
│
├── Functional/              # End-to-end scenarios
│   ├── CompleteParsingTest.php
│   └── ...
│
├── Fixtures/                # Test data
│   ├── valid_code.php
│   └── invalid_code.php
│
└── bootstrap.php            # Test initialization
```

#### Test Naming Conventions
```php
// ✅ Good: Descriptive, follows conventions
class LexerTest extends TestCase
{
    public function testTokenizesSimpleString(): void
    {
        // Arrange
        $lexer = new Lexer();
        
        // Act
        $tokens = $lexer->tokenize('<?php echo "Hello";');
        
        // Assert
        $this->assertCount(5, $tokens);
    }
}

// ❌ Bad: Vague test name
class Test1 extends TestCase
{
    public function test(): void
    {
        $this->assertTrue(true);
    }
}
```

### Test Execution Modes

#### Standard Mode
```bash
# Default: All tests with testdox output
make test
```

#### Verbose Mode
```bash
# Detailed output for debugging
vendor/bin/phpunit --testdox --verbose
```

#### Stop on Failure
```bash
# Stop at first failure
vendor/bin/phpunit --stop-on-failure
```

#### Filter Specific Tests
```bash
# By test method name
vendor/bin/phpunit --filter testTokenizesSimpleString

# By class name
vendor/bin/phpunit --filter LexerTest

# By namespace pattern
vendor/bin/phpunit --filter "Parser\\Lexer"
```

---

## Code Linting

### Syntax Validation
```bash
make lint

# Executes:
# find src tests -name "*.php" -print0 | xargs -0 -n1 php -l > /dev/null

# Checks:
# - PHP syntax errors
# - Parse errors
# - Fatal syntax violations

# Output:
# → Linting PHP files...
# ✓ All PHP files are valid
```

### What Lint Catches

**Valid Code:**
```php
<?php
class Example
{
    public function method(): void
    {
        echo "Valid";
    }
}
```

**Syntax Errors Caught:**
```php
<?php
class Example
{
    public function method(): void
    {
        echo "Missing semicolon"  // ← Parse error
    }
    
    public function broken(  // ← Unexpected end of file
}
```

### Performance
```bash
# Parallel execution with xargs
find src tests -name "*.php" -print0 | xargs -0 -P4 -n1 php -l

# Flags:
# -P4     : Run 4 processes in parallel
# -n1     : One file per process
# -print0 : Handle filenames with spaces
```

---

## Static Analysis

### PHPStan

#### Basic Analysis
```bash
make phpstan

# Executes:
# vendor/bin/phpstan analyse src --level=max --memory-limit=512M

# Configuration (phpstan.neon):
parameters:
    level: max                    # Strictest level (0-9)
    paths:
        - src
    tmpDir: var/cache/phpstan     # Cache directory
    excludePaths:
        - vendor/
```

#### Output Example
```bash
$ make phpstan

→ Running PHPStan...

 ------ -------------------------------------------------------------------- 
  Line   src/Parser/Node/Statement/ClassNode.php                            
 ------ -------------------------------------------------------------------- 
  25     Method KaririCode\Parser\Node\Statement\ClassNode::getChildren()  
         has no return type specified.                                      
  42     Method KaririCode\Parser\Node\Statement\ClassNode::getFullText()  
         has no return type specified.                                      
 ------ -------------------------------------------------------------------- 

 [ERROR] Found 767 errors                                                   

✗ PHPStan analysis failed
```

#### Levels Explained

| Level | Checks |
|-------|--------|
| 0 | Basic checks: unknown classes, functions, methods |
| 1 | Unknown properties, magic methods |
| 2 | Unknown methods on all expressions |
| 3 | Return types, types assigned to properties |
| 4 | Basic dead code checking |
| 5 | Checking types of arguments passed to methods |
| 6 | Missing type hints |
| 7 | Stricter mixed type checking |
| 8 | Stricter array and callable checking |
| **max** | **All checks enabled (recommended)** |

#### Generate Baseline
```bash
make phpstan-baseline

# Creates phpstan-baseline.neon
# Ignores existing issues
# Future runs only report NEW issues

# Usage in phpstan.neon:
includes:
    - phpstan-baseline.neon
```

**Baseline Strategy:**
```bash
# 1. Generate baseline for legacy code
make phpstan-baseline

# 2. Fix issues incrementally
# (edit source files)

# 3. Regenerate baseline to track progress
make phpstan-baseline

# 4. Enforce zero new issues in CI
make phpstan  # Fails only on new issues
```

#### Common PHPStan Issues

**Issue: Missing Return Type**
```php
// ❌ PHPStan error
public function getChildren()
{
    return $this->children;
}

// ✅ Fixed
public function getChildren(): array
{
    return $this->children;
}
```

**Issue: Undefined Property**
```php
// ❌ PHPStan error
class Node
{
    public function __construct()
    {
        $this->name = 'default';  // Property not declared
    }
}

// ✅ Fixed
class Node
{
    private string $name;
    
    public function __construct()
    {
        $this->name = 'default';
    }
}
```

**Issue: Possibly Null Reference**
```php
// ❌ PHPStan error
public function process(?Node $node): string
{
    return $node->getName();  // $node might be null
}

// ✅ Fixed (Option 1: Guard)
public function process(?Node $node): string
{
    if ($node === null) {
        throw new \InvalidArgumentException('Node cannot be null');
    }
    return $node->getName();
}

// ✅ Fixed (Option 2: Null coalescing)
public function process(?Node $node): string
{
    return $node?->getName() ?? 'default';
}
```

### Psalm

#### Basic Analysis
```bash
make psalm

# Executes:
# vendor/bin/psalm --show-info=true --stats --no-cache

# Configuration (psalm.xml):
<psalm
    errorLevel="3"
    resolveFromConfigFile="true"
    findUnusedCode="true"
    findUnusedVariables="true"
>
    <projectFiles>
        <directory name="src" />
    </projectFiles>
</psalm>
```

#### Output Example
```bash
$ make psalm

→ Running Psalm...

Scanning files...
Analyzing files...

████████████████████████████████████████████ 100%

------------------------------
 ISSUES FOUND
------------------------------

ERROR: UnimplementedAbstractMethod - src/Parser/Node/GreenTree/Expression.php:15
    Class KaririCode\Parser\Node\GreenTree\Expression\BinaryExpression does not 
    implement abstract method getWidthWithoutTrivia

INFO: MixedReturnStatement - src/Parser/Lexer.php:89
    Could not infer return type from $tokens

------------------------------

81 errors found
99.34% type coverage
Psalm can help you fix these errors

✓ Psalm analysis complete (with errors)
```

#### Error Levels

| Level | Strictness | Description |
|-------|------------|-------------|
| 1 | Maximum | Catches almost everything |
| 2 | High | Practical maximum for most projects |
| **3** | **Medium** | **Recommended default** |
| 4 | Low | Loose type checking |
| 5+ | Very low | Minimal checks |

#### Generate Baseline
```bash
make psalm-baseline

# Creates psalm-baseline.xml
# Similar to PHPStan baseline
```

#### Taint Analysis (Security)
```bash
make psalm-taint

# Detects security vulnerabilities:
# - SQL injection
# - XSS vulnerabilities
# - Command injection
# - Path traversal

# Requires taint sources/sinks annotation
```

**Taint Analysis Example:**
```php
/**
 * @psalm-taint-source input
 */
public function getUserInput(): string
{
    return $_GET['name'];
}

/**
 * @psalm-taint-sink html
 */
public function renderHTML(string $html): void
{
    echo $html;  // Psalm warns: untrusted data in HTML context
}

// Usage:
$input = $this->getUserInput();
$this->renderHTML($input);  // ⚠️ Taint detected!
```

#### Common Psalm Issues

**Issue: Mixed Type**
```php
// ❌ Psalm error: MixedReturnStatement
public function getData()
{
    return $this->data;  // Type unknown
}

// ✅ Fixed
/** @return array<string, mixed> */
public function getData(): array
{
    return $this->data;
}
```

**Issue: Unimplemented Abstract Method**
```php
// ❌ Psalm error
abstract class Node
{
    abstract public function getChildren(): array;
}

class Leaf extends Node
{
    // Missing implementation
}

// ✅ Fixed
class Leaf extends Node
{
    public function getChildren(): array
    {
        return [];
    }
}
```

### Combined Analysis
```bash
make analyse

# Runs all static analysis tools:
# 1. PHPStan
# 2. Psalm
# 3. cs-check

# Stops on first failure
# Use in pre-commit hooks
```

---

## Code Style & Formatting

### Check Code Style

#### PHPCS (PHP_CodeSniffer)
```bash
make cs-check

# Executes:
# vendor/bin/phpcs --standard=phpcs.xml --colors src tests

# Checks:
# - PSR-12 compliance
# - Naming conventions
# - Indentation
# - Line length
# - Spacing

# Output:
FILE: /path/to/File.php
----------------------------------------------------------------------
FOUND 5 ERRORS AFFECTING 3 LINES
----------------------------------------------------------------------
 12 | ERROR | Line exceeds 120 characters
 15 | ERROR | Expected 1 space after comma; 0 found
 20 | ERROR | Missing doc comment for function
----------------------------------------------------------------------
```

#### Configuration (phpcs.xml)
```xml
<?xml version="1.0"?>
<ruleset name="KaririCode">
    <description>KaririCode Coding Standard</description>

    <!-- Base standard -->
    <rule ref="PSR12"/>

    <!-- Custom rules -->
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="200"/>
        </properties>
    </rule>

    <!-- Include paths -->
    <file>src</file>
    <file>tests</file>

    <!-- Exclude patterns -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/cache/*</exclude-pattern>
</ruleset>
```

### Auto-fix Code Style

#### Two-Stage Fixing
```bash
make format

# Stage 1: PHP-CS-Fixer (modern PHP features)
# Stage 2: PHPCBF (PSR-12 compliance)

# Output:
# → Formatting code...
# 
# Loaded config default.
# Using cache file ".php-cs-fixer.cache".
# 
# Fixed 12 files in 3.456 seconds, 18.00 MB memory used
# 
# PHPCBF RESULT SUMMARY
# ----------------------------------------------------------------------
# A TOTAL OF 5 FILES WERE FIXED
# ----------------------------------------------------------------------
# 
# ✓ Code formatted
```

#### PHP-CS-Fixer Configuration
```php
// .php-cs-fixer.php
<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('vendor')
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP84Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'native_function_invocation' => [
            'include' => ['@all'],
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
        ],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
```

#### Preview Changes Without Applying
```bash
make format-dry

# Shows diff without modifying files
# Review before applying changes
```

#### What Gets Fixed

**Before Formatting:**
```php
<?php
class Example {
    public function method($param1,$param2) {
        $array = array(1,2,3);
        return $array;
    }
}
```

**After Formatting:**
```php
<?php

declare(strict_types=1);

class Example
{
    public function method($param1, $param2): array
    {
        $array = [1, 2, 3];
        
        return $array;
    }
}
```

### Fix Coding Standards (PHPCBF)
```bash
make cbf-fix

# Executes PHPCBF only (not PHP-CS-Fixer)
# Fixes PSR-12 violations automatically
```

---

## Code Coverage

### Generate Coverage Report
```bash
make coverage

# Executes:
# XDEBUG_MODE=coverage vendor/bin/phpunit \
#     --coverage-html coverage/html \
#     --coverage-clover coverage/clover.xml \
#     --coverage-text=coverage/coverage.txt

# Output formats:
# - HTML: coverage/html/index.html (browse in browser)
# - Clover: coverage/clover.xml (CI integration)
# - Text: coverage/coverage.txt (terminal viewing)
```

#### Coverage Report Example
```
Code Coverage Report:     
  2025-01-15 10:30:00     
                          
 Summary:                 
  Classes: 85.71% (18/21)
  Methods: 82.35% (84/102)
  Lines:   79.24% (1234/1557)

 KaririCode\Parser\Lexer
  Methods: 100.00% (8/8)
  Lines:   95.83% (46/48)

 KaririCode\Parser\Parser
  Methods:  87.50% (14/16)
  Lines:    82.14% (230/280)
```

### Terminal Coverage
```bash
make coverage-text

# Quick coverage overview in terminal
# No HTML generation
# Faster execution
```

### Coverage Requirements
```xml
<!-- phpunit.xml -->
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">src</directory>
    </include>
    <report>
        <html outputDirectory="coverage/html"/>
        <clover outputFile="coverage/clover.xml"/>
        <text outputFile="coverage/coverage.txt"/>
    </report>
</coverage>
```

### Coverage Thresholds
```xml
<!-- Enforce minimum coverage -->
<coverage>
    <report>
        <thresholds>
            <file minCoverage="70"/>
            <class minCoverage="80"/>
        </thresholds>
    </report>
</coverage>
```

---

## Mutation Testing

### Run Mutation Tests
```bash
make mutation

# Executes:
# XDEBUG_MODE=coverage vendor/bin/infection \
#     --threads=4 \
#     --min-msi=80 \
#     --min-covered-msi=90 \
#     --show-mutations

# Requirements:
# - MSI (Mutation Score Indicator): ≥80%
# - Covered MSI: ≥90%

# Output:
You are running Infection with xdebug enabled.
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/

Running initial test suite...

PHPUnit version: 11.5.2

   24 [============================] < 1 sec

Generate mutants...

Processing source code files: 21/21
Creating mutated files and processes: 245/245
.S.SSSS.S.S.S.S.S.S.S.S....S.S..S.S.S....S.

245 mutations were generated:
     187 mutants were killed
      42 mutants were not covered by tests
      16 covered mutants were not detected

Metrics:
         Mutation Score Indicator (MSI): 82%
         Mutation Code Coverage: 83%
         Covered Code MSI: 92%
```

### Generate Detailed Report
```bash
make mutation-report

# Generates infection.html
# Detailed mutation analysis
# Shows which mutations survived
```

### Mutation Types

| Mutator | Description | Example |
|---------|-------------|---------|
| **Binary** | Changes operators | `+` → `-`, `&&` → `\|\|` |
| **Comparison** | Alters comparisons | `>` → `>=`, `==` → `!=` |
| **Increment** | Modifies increments | `++` → `--` |
| **Return Value** | Changes returns | `return true` → `return false` |
| **Array** | Mutates arrays | `[]` → `[null]` |
| **Function Call** | Removes calls | `trim($x)` → `$x` |

### Mutation Testing Example

**Original Code:**
```php
public function isPositive(int $number): bool
{
    return $number > 0;
}
```

**Test:**
```php
public function testIsPositive(): void
{
    $this->assertTrue($this->calculator->isPositive(5));
}
```

**Mutation (survived):**
```php
// Mutator: GreaterThan → GreaterThanOrEqual
public function isPositive(int $number): bool
{
    return $number >= 0;  // Mutation: > changed to >=
}
```

**Why it survived:** Test doesn't check boundary (0)

**Better Test:**
```php
public function testIsPositive(): void
{
    $this->assertTrue($this->calculator->isPositive(5));
    $this->assertTrue($this->calculator->isPositive(1));
    $this->assertFalse($this->calculator->isPositive(0));   // ✅ Kills mutation
    $this->assertFalse($this->calculator->isPositive(-5));
}
```

---

## Troubleshooting

### Issue 1: Tests Not Executing

**Symptoms:**
```bash
$ make test

There was 1 PHPUnit test runner warning:
1) XDEBUG_MODE=coverage has to be set
No tests executed!
```

**Cause:** Xdebug warning mistaken for error

**Solutions:**

**Option A: Disable Xdebug**
```bash
export XDEBUG_MODE=off
make test
```

**Option B: Use PHP INI**
```bash
php -d xdebug.mode=off vendor/bin/phpunit
```

**Option C: Docker (recommended)**
```bash
make docker-test
# Docker container has proper configuration
```

### Issue 2: PHPStan Memory Limit

**Symptoms:**
```bash
$ make phpstan

Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solutions:**

**Option A: Increase in Makefile (already done)**
```makefile
# Makefile.qa.mk already sets --memory-limit=512M
phpstan:
    @$(PHPSTAN) analyse src --level=max --memory-limit=512M
```

**Option B: Increase further if needed**
```bash
vendor/bin/phpstan analyse src --level=max --memory-limit=1G
```

**Option C: Use PHP memory limit**
```bash
php -d memory_limit=1G vendor/bin/phpstan analyse src --level=max
```

### Issue 3: Psalm Cache Issues

**Symptoms:**
```bash
$ make psalm

InvalidArgumentException: Cache directory does not exist
```

**Solutions:**
```bash
# Clear Psalm cache
rm -rf var/cache/psalm

# Run with --no-cache
vendor/bin/psalm --no-cache

# Or use Makefile target (already includes --no-cache)
make psalm
```

### Issue 4: PHP-CS-Fixer Permission Denied

**Symptoms:**
```bash
$ make format

Permission denied: .php-cs-fixer.cache
```

**Solutions:**
```bash
# Remove cache file
rm .php-cs-fixer.cache

# Fix permissions
chmod 644 .php-cs-fixer.cache

# Or run without cache
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --no-cache
```

### Issue 5: Coverage Generation Fails

**Symptoms:**
```bash
$ make coverage

PHP Fatal error: Xdebug is required for code coverage
```

**Solutions:**

**Check Xdebug Installation:**
```bash
php -m | grep xdebug

# If missing, install:
# Ubuntu/Debian
sudo apt install php8.4-xdebug

# macOS (Homebrew)
brew install php@8.4
pecl install xdebug
```

**Verify Configuration:**
```bash
php -i | grep xdebug.mode
# Should show: xdebug.mode => coverage => coverage
```

**Use Docker (recommended):**
```bash
make docker-coverage
# Pre-configured with Xdebug
```

---

## Best Practices

### 1. Pre-Commit Quality Checks
```bash
# Install git hook
make git-hooks-setup

# Or run manually before commit
make pre-commit

# Runs:
# 1. format    → Auto-fix style
# 2. lint      → Syntax check
# 3. analyse   → Static analysis
# 4. test-unit → Fast tests
```

### 2. Incremental Quality Improvement
```bash
# Week 1: Generate baselines
make phpstan-baseline
make psalm-baseline

# Week 2-4: Fix issues incrementally
# (edit files, fix 10-20 issues per day)

# Week 5: Regenerate baselines
make phpstan-baseline
make psalm-baseline

# Track progress
git diff phpstan-baseline.neon
```

### 3. Test-Driven Development
```bash
# 1. Write failing test
vendor/bin/phpunit --filter testNewFeature
# ✗ FAILURES!

# 2. Implement feature
# (edit source)

# 3. Run test again
vendor/bin/phpunit --filter testNewFeature
# ✓ OK

# 4. Run full suite
make test
```

### 4. Coverage-Driven Testing
```bash
# 1. Generate coverage
make coverage

# 2. Open coverage/html/index.html
# Identify uncovered lines (red)

# 3. Write tests for uncovered code

# 4. Verify coverage improved
make coverage-text
```

### 5. Mutation-Driven Test Quality
```bash
# 1. Run mutation testing
make mutation

# 2. Review survived mutants in infection.html

# 3. Write tests to kill mutants

# 4. Re-run until MSI ≥ 80%
make mutation
```

### 6. CI Integration Strategy
```yaml
# .github/workflows/qa.yml
name: Quality Assurance

on: [push, pull_request]

jobs:
  qa:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: xdebug
      
      # Fast checks first
      - name: Install
        run: make install
      
      - name: Lint
        run: make lint
      
      - name: Code Style
        run: make cs-check
      
      - name: Static Analysis
        run: make analyse
      
      - name: Tests
        run: make test
      
      # Slow checks (if fast checks pass)
      - name: Coverage
        if: success()
        run: make coverage
      
      - name: Mutation
        if: success()
        run: make mutation
```

---

## Command Reference

### Testing
```bash
make test               # Run all tests
make test-unit          # Unit tests only
make test-integration   # Integration tests
make test-functional    # Functional tests
```

### Linting & Analysis
```bash
make lint               # PHP syntax validation
make phpstan            # PHPStan static analysis
make phpstan-baseline   # Generate PHPStan baseline
make psalm              # Psalm type checking
make psalm-baseline     # Generate Psalm baseline
make psalm-taint        # Security taint analysis
make analyse            # All static analysis
```

### Code Style
```bash
make cs-check           # Check code style (PHPCS)
make cbf-fix            # Fix code style (PHPCBF)
make format             # Auto-fix all style issues
make format-dry         # Preview formatting changes
```

### Coverage & Mutation
```bash
make coverage           # Generate coverage report
make coverage-text      # Terminal coverage
make mutation           # Mutation testing
make mutation-report    # Detailed mutation report
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║            Quality Assurance Quick Reference              ║
╠═══════════════════════════════════════════════════════════╣
║ LINT         │ make lint                                  ║
║ FORMAT       │ make format                                ║
║ ANALYSE      │ make analyse                               ║
║ TEST         │ make test                                  ║
║──────────────┼────────────────────────────────────────────║
║ UNIT TESTS   │ make test-unit                             ║
║ COVERAGE     │ make coverage                              ║
║ MUTATION     │ make mutation                              ║
║──────────────┼────────────────────────────────────────────║
║ PHPSTAN      │ make phpstan                               ║
║ PSALM        │ make psalm                                 ║
║ CS CHECK     │ make cs-check                              ║
╚═══════════════════════════════════════════════════════════╝

Daily Workflow:
  make format → make lint → make test-unit

Pre-Commit:
  make pre-commit (format + lint + analyse + test-unit)

Full QA:
  make analyse → make test → make coverage → make mutation
```

---

**Version**: 1.0.0  
**Module**: `Makefile.qa.mk`    
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
