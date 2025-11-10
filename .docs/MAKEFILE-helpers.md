<div align="center">

# Development Helpers

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![Benchmarks](https://img.shields.io/badge/PHPBench-Performance-FF6F00?style=for-the-badge)](https://github.com/phpbench/phpbench)
[![Git](https://img.shields.io/badge/Git-Hooks-F05032?style=for-the-badge&logo=git)](https://git-scm.com)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Benchmarking](#benchmarking)
3. [Git Hooks Management](#git-hooks-management)
4. [Release Management](#release-management)
5. [Project Information](#project-information)
6. [Statistics & Metrics](#statistics--metrics)
7. [Best Practices](#best-practices)

---

## Overview

### Scope

The helpers module (`Makefile.helpers.mk`) provides developer productivity tools:
- **Benchmarking**: Performance measurement with PHPBench
- **Git Hooks**: Automated pre-commit quality checks
- **Release Management**: Version tagging and release preparation
- **Project Info**: Environment and tool verification
- **Statistics**: Code metrics and project analytics

### Module Architecture
```
Helpers Module (.make/local/Makefile.helpers.mk)
│
├── Benchmarking
│   ├── bench              → Unified benchmark command
│   └── bench-help         → Parameter documentation
│
├── Git Hooks
│   ├── git-hooks-setup    → Install pre-commit hook
│   ├── git-hooks-check    → Verify installation
│   └── git-hooks-remove   → Cleanup hooks
│
├── Release Management
│   ├── tag                → Create version tag
│   └── release            → Release preparation
│
├── Information
│   └── info               → Environment details
│
└── Statistics
    ├── stats              → Project statistics
    └── loc                → Lines of code count
```

---

## Benchmarking

### Unified Benchmark Interface

#### Overview

The `make bench` command provides a **single, parameter-driven interface** for all benchmarking scenarios:
- Standard benchmarks
- Performance comparisons
- Result storage with tagging
- Automated baseline management

#### Basic Usage
```bash
# Simple benchmark run
make bench

# If no 'main' baseline exists:
# → Running benchmarks (unified target)…
# ⚠  No 'main' reference found. Running without comparison.
#   Hint: make bench STORE=1 TAG=main ENFORCE_MAIN=1
# ✓ Benchmarks complete
```

### Parameters

| Parameter | Values | Default | Description |
|-----------|--------|---------|-------------|
| **REF** | `auto`, `main`, `<tag>` | `auto` | Compare against stored reference |
| **STORE** | `0`, `1` | `0` | Store results with tag |
| **TAG** | `<string>` | - | Tag name when storing (required if STORE=1) |
| **ENFORCE_MAIN** | `0`, `1` | `0` | Require 'main' branch when TAG=main |
| **REPORT** | `0`, `1` | `0` | Save output to `build/benchmarks/last.txt` |

### Reference Comparison (REF)

#### Auto-detect Reference
```bash
# Try 'main' baseline if available
make bench REF=auto

# If 'main' exists:
# ✓ 'main' reference found. Enabling comparison…
# Shows comparison against main baseline

# If 'main' doesn't exist:
# ⚠  No 'main' reference found. Running without comparison.
```

#### Compare Against Main
```bash
make bench REF=main

# Output:
# → Comparing against reference: main
# 
# Subject       Groups  Iters  Mean      Diff (%)
# benchTokenize        10     127.234μs -12.5%   ← Faster
# benchParse           10     456.789μs +5.2%    ← Slower
```

#### Compare Against Custom Tag
```bash
make bench REF=feature-x

# Compares current code against stored 'feature-x' baseline
```

### Storing Results (STORE + TAG)

#### Store Feature Baseline
```bash
make bench STORE=1 TAG=feature-optimization

# → Storing run with tag 'feature-optimization'…
# Stores current performance metrics
# ✓ Benchmarks complete
```

#### Store Main Baseline (with branch enforcement)
```bash
# Must be on 'main' branch
git checkout main
make bench STORE=1 TAG=main ENFORCE_MAIN=1

# If not on main:
# ✗ This action requires branch 'main' (current: develop)

# If on main:
# → Storing run with tag 'main'…
# ✓ Benchmarks complete
```

### Save Output to File (REPORT)
```bash
# Compare and save detailed report
make bench REF=main REPORT=1

# Output saved to: build/benchmarks/last.txt
# ✓ Output saved to build/benchmarks/last.txt

# View report
cat build/benchmarks/last.txt
```

### Complete Workflow Examples

#### Baseline Creation Workflow
```bash
# 1. Create initial baseline on main branch
git checkout main
git pull origin main

# 2. Store as 'main' reference (with enforcement)
make bench STORE=1 TAG=main ENFORCE_MAIN=1

# Output:
# → Storing run with tag 'main'…
# 
# Subject       Groups  Iters  Mean
# benchTokenize        10     145.234μs
# benchParse           10     432.156μs
# 
# ✓ Benchmarks complete
```

#### Optimization Workflow
```bash
# 1. Create optimization branch
git checkout -b optimize/lexer-performance

# 2. Make optimization changes
# (edit source files)

# 3. Run benchmark with comparison
make bench REF=main

# Output shows performance delta:
# benchTokenize: 127.234μs (-12.4%) ← 12.4% faster! ✓

# 4. Store optimization attempt
make bench STORE=1 TAG=opt-attempt-1

# 5. Make more changes
# (edit source files)

# 6. Compare against previous attempt
make bench REF=opt-attempt-1

# 7. Save final report
make bench REF=main REPORT=1

# 8. Review detailed comparison
cat build/benchmarks/last.txt
```

#### Feature Development Workflow
```bash
# 1. Create feature branch
git checkout -b feature/new-parser

# 2. Develop feature with periodic benchmarks
make bench REF=main

# 3. Store milestone benchmarks
make bench STORE=1 TAG=feature-milestone-1
# (develop more)
make bench STORE=1 TAG=feature-milestone-2

# 4. Compare milestones
make bench REF=feature-milestone-1

# 5. Before merging to main
make bench REF=main REPORT=1
# Ensure no performance regression
```

#### Release Workflow
```bash
# Before release, verify performance
git checkout main
make bench REF=main REPORT=1

# Store release baseline
make bench STORE=1 TAG=v2.0.0

# Archive report
cp build/benchmarks/last.txt docs/performance/v2.0.0-benchmark.txt
git add docs/performance/v2.0.0-benchmark.txt
git commit -m "docs: add v2.0.0 performance baseline"
```

### Benchmark Help
```bash
make bench-help

# Shows comprehensive parameter documentation:
# 
# ╔════════════════════════════════════════════════════════╗
# ║             Benchmark Command Help                     ║
# ╚════════════════════════════════════════════════════════╝
# 
# Single entrypoint: make bench
# 
# Parameters:
#   REF=auto|main|<tag>     Compare against a stored tag
#   STORE=1 TAG=<tag>       Store benchmarks with a tag
#   ENFORCE_MAIN=1          Require 'main' branch for TAG=main
#   REPORT=1                Save output to last.txt
# 
# Examples:
#   make bench                         # Normal run
#   make bench REF=main                # Compare vs main
#   make bench STORE=1 TAG=feat        # Store as 'feat'
#   make bench REF=main REPORT=1       # Compare and save
```

### Benchmark Configuration

#### PHPBench Configuration (phpbench.json)
```json
{
    "runner.bootstrap": "vendor/autoload.php",
    "runner.path": "benchmarks",
    "runner.progress": "dots",
    "runner.iterations": [10],
    "runner.revs": [100],
    "runner.warmup": [1],
    "report.generators": {
        "default": {
            "extends": "aggregate"
        }
    }
}
```

#### Benchmark Example
```php
// benchmarks/LexerBench.php
<?php

declare(strict_types=1);

namespace KaririCode\Benchmark;

use KaririCode\Parser\Lexer;

/**
 * @Groups({"lexer"})
 * @Iterations(10)
 * @Revs(100)
 * @Warmup(1)
 */
class LexerBench
{
    private Lexer $lexer;
    private string $sourceCode;

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->sourceCode = file_get_contents(__DIR__ . '/fixtures/sample.php');
    }

    /**
     * @Subject
     */
    public function benchTokenize(): void
    {
        $this->lexer->tokenize($this->sourceCode);
    }

    /**
     * @Subject
     * @ParamProviders({"provideCodeSamples"})
     */
    public function benchTokenizeVariousSizes(array $params): void
    {
        $this->lexer->tokenize($params['code']);
    }

    public function provideCodeSamples(): \Generator
    {
        yield 'small' => ['code' => '<?php echo "hello";'];
        yield 'medium' => ['code' => file_get_contents(__DIR__ . '/fixtures/medium.php')];
        yield 'large' => ['code' => file_get_contents(__DIR__ . '/fixtures/large.php')];
    }
}
```

### Performance Analysis Tips

#### Interpret Results
```
Subject       Groups  Iters  Mean      Stddev   RPS
benchTokenize lexer   10     127.234μs ±2.34%   7,859.2

Mean:    Average execution time
Stddev:  Standard deviation (consistency indicator)
RPS:     Requests per second (throughput)
```

**Good Performance:**
- Low mean time
- Low standard deviation (< 5%)
- High RPS

#### Optimization Checklist
```bash
# 1. Create baseline
make bench STORE=1 TAG=baseline

# 2. Profile hot paths
make bench REF=baseline
# Identify slowest operations

# 3. Optimize critical sections
# (implement optimizations)

# 4. Verify improvement
make bench REF=baseline
# Must show negative % (faster)

# 5. Check for regressions
make bench REF=main
# Ensure no other operations slowed down
```

---

## Git Hooks Management

### Pre-commit Hook Installation

#### Setup Hook
```bash
make git-hooks-setup

# What it does:
# 1. Creates .git/hooks/pre-commit
# 2. Backs up existing hook (if present) → .git/hooks/pre-commit.bak
# 3. Writes 'make pre-commit' script
# 4. Makes hook executable (chmod +x)

# Output:
# → Setting up git hooks...
# ✓ Git hooks set up
```

#### Hook Script Content
```bash
# .git/hooks/pre-commit
#!/bin/sh
set -e
make pre-commit
```

**Behavior:**
- Runs automatically before each commit
- Executes: `format → lint → analyse → test-unit`
- Blocks commit if any check fails
- Exit code determines commit success

### Verify Installation
```bash
make git-hooks-check

# Checks:
# 1. Hook file exists (.git/hooks/pre-commit)
# 2. Contains 'make pre-commit' command
# 3. Is executable

# Output (if installed):
# → Verifying git hooks...
# ✓ pre-commit hook is installed correctly

# Output (if not installed):
# → Verifying git hooks...
# ✗ pre-commit hook not found
```

### Remove Hook
```bash
make git-hooks-remove

# What it does:
# 1. Checks for backup (.git/hooks/pre-commit.bak)
# 2. If backup exists: Restores it
# 3. If no backup: Removes generated hook
# 4. Cleanup

# Output (with backup):
# → Cleaning up git hooks...
# ↩ Restoring backup pre-commit hook...
# ✓ Git hooks cleaned

# Output (without backup):
# → Cleaning up git hooks...
# ✗ Removing generated pre-commit hook...
# ✓ Git hooks cleaned
```

### Hook Workflow

#### Commit with Hook Active
```bash
$ git add src/Parser.php
$ git commit -m "feat: optimize parser performance"

# Hook executes automatically:
→ Running pre-commit checks...

→ Formatting code...
✓ Code formatted

→ Linting PHP files...
✓ All PHP files are valid

→ Running static analysis...
✓ PHPStan analysis passed
✓ Psalm analysis passed

→ Running unit tests...
OK (95 tests, 280 assertions)
✓ Unit tests passed

✓ Pre-commit checks passed

[feature/optimize abc123] feat: optimize parser performance
 1 file changed, 10 insertions(+), 5 deletions(-)
```

#### Commit Failed by Hook
```bash
$ git commit -m "wip: broken code"

→ Running pre-commit checks...

→ Formatting code...
✓ Code formatted

→ Linting PHP files...
Parse error: syntax error, unexpected 'class'
✗ Linting failed

# Commit blocked - fix issues first
```

#### Bypass Hook (Emergency)
```bash
# Override hook (use sparingly!)
git commit --no-verify -m "emergency: critical hotfix"

# ⚠️ Still run checks after:
make pre-commit
```

### Custom Hook Configuration

#### Conditional Execution
```bash
# Edit: .git/hooks/pre-commit
#!/bin/sh
set -e

# Get staged files
STAGED=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$' || true)

# Only run checks if PHP files changed
if [ -n "$STAGED" ]; then
    echo "PHP files changed, running checks..."
    make pre-commit
else
    echo "No PHP files changed, skipping checks"
fi
```

#### Partial Checks
```bash
# Edit: .git/hooks/pre-commit
#!/bin/sh
set -e

# Check if only documentation changed
CHANGED=$(git diff --cached --name-only)

if echo "$CHANGED" | grep -qv "\.md$"; then
    # Source code changed: full checks
    make pre-commit
else
    # Only docs changed: format and lint only
    make format
    make lint
fi
```

---

## Release Management

### Create Version Tag

#### Basic Usage
```bash
make tag VERSION=1.0.0

# Executes:
# 1. Validates VERSION parameter
# 2. Creates annotated git tag: v1.0.0
# 3. Pushes tag to origin

# Output:
# → Creating tag v1.0.0...
# ✓ Tag v1.0.0 created and pushed
```

#### Tag Format
```bash
# Semantic Versioning (MAJOR.MINOR.PATCH)
make tag VERSION=1.0.0     # v1.0.0
make tag VERSION=2.1.0     # v2.1.0
make tag VERSION=2.1.5     # v2.1.5

# Pre-release tags
make tag VERSION=1.0.0-alpha.1
make tag VERSION=2.0.0-beta.2
make tag VERSION=1.0.0-rc.1
```

#### Tag Annotation
```makefile
# Makefile creates annotated tag with message
git tag -a "v$(VERSION)" -m "Release v$(VERSION)"

# View tag details:
git show v1.0.0
```

### Release Preparation
```bash
make release

# Executes complete release workflow:
# 1. make cd              → Full validation pipeline
# 2. Display checklist    → Manual steps reminder

# Output:
# ╔════════════════════════════════════════════════════════╗
# ║  KaririCode\DevKit CD Pipeline                         ║
# ╚════════════════════════════════════════════════════════╝
# 
# [Runs ci-full + benchmarks...]
# 
# ✓ Release preparation complete
# 
# Next steps:
#   1. Update CHANGELOG.md
#   2. Update version in composer.json
#   3. Commit changes
#   4. Run: make tag VERSION=X.Y.Z
#   5. Push to GitHub
#   6. Create GitHub release
```

### Complete Release Workflow

#### Step-by-Step Release
```bash
# 1. Ensure clean state
git checkout main
git pull origin main
git status  # Should be clean

# 2. Run release preparation
make release

# 3. Update CHANGELOG.md
nano CHANGELOG.md
# Add release notes:
# ## [2.0.0] - 2025-01-15
# ### Added
# - New feature X
# ### Changed
# - Improved Y
# ### Fixed
# - Bug Z

# 4. Update version in composer.json
nano composer.json
# Change: "version": "2.0.0"

# 5. Commit changes
git add CHANGELOG.md composer.json
git commit -m "chore: release v2.0.0"

# 6. Create and push tag
make tag VERSION=2.0.0

# 7. Push commits
git push origin main

# 8. Create GitHub release (manual or gh CLI)
gh release create v2.0.0 \
  --title "Release v2.0.0" \
  --notes-file CHANGELOG.md \
  --generate-notes
```

#### Automated Release Script
```bash
#!/bin/bash
# scripts/release.sh

set -e

VERSION=$1

if [ -z "$VERSION" ]; then
    echo "Usage: ./scripts/release.sh VERSION"
    exit 1
fi

echo "Preparing release $VERSION..."

# 1. Run release checks
make release

# 2. Update version in composer.json
sed -i "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" composer.json

# 3. Update CHANGELOG
DATE=$(date +%Y-%m-%d)
sed -i "s/## \[Unreleased\]/## [$VERSION] - $DATE/" CHANGELOG.md

# 4. Commit
git add composer.json CHANGELOG.md
git commit -m "chore: release v$VERSION"

# 5. Tag
make tag VERSION=$VERSION

# 6. Push
git push origin main

echo "✓ Release $VERSION complete!"
```

---

## Project Information

### Environment Details
```bash
make info

# Shows comprehensive project information:
# 
# ╔════════════════════════════════════════════════════════╗
# ║                Project Information                     ║
# ╚════════════════════════════════════════════════════════╝
# 
# PHP Version:        8.4.14
# PHP Binary:         /usr/bin/php
# Composer:           /usr/local/bin/composer
# Project Directory:  /home/user/kariricode-devkit
# Source Directory:   src
# Test Directory:     tests
# 
# ╔════════════════════════════════════════════════════════╗
# ║                  Installed Tools                       ║
# ╚════════════════════════════════════════════════════════╝
# 
# PHPUnit:            ✓
# PHPStan:            ✓
# Psalm:              ✓
# PHPCS:              ✓
# PHP-CS-Fixer:       ✓
# Infection:          ✓
# PHPBench:           ✓
```

### Use Cases

**New Developer Onboarding:**
```bash
# Clone repository
git clone https://github.com/KaririCode-Framework/kariricode-devkit.git
cd kariricode-devkit

# Verify environment
make info

# If tools missing (✗):
make install-dev
make info  # Should all show ✓
```

**Troubleshooting:**
```bash
# Check environment before reporting issues
make info > environment.txt

# Include in bug report
```

**Documentation:**
```bash
# Generate environment report for README
make info
# Copy output to documentation
```

---

## Statistics & Metrics

### Project Statistics
```bash
make stats

# Comprehensive project metrics:
# 
# ╔════════════════════════════════════════════════════════╗
# ║               Project Statistics                       ║
# ╚════════════════════════════════════════════════════════╝
# 
# Total PHP files:    150
# Total test files:   95
# Lines of code:      12,456
# Lines of tests:     8,932
# 
# ╔════════════════════════════════════════════════════════╗
# ║               Directory Sizes                          ║
# ╚════════════════════════════════════════════════════════╝
# 
# src      2.3M
# tests    1.8M
# vendor   45M
```

### Lines of Code
```bash
make loc

# Quick line count:
# 
# → Counting lines of code...
# Source: 12456 lines
# Tests:  8932 lines
```

### Metrics Over Time
```bash
# Track code growth weekly
{
    echo "$(date): $(make loc 2>&1 | grep Source)"
} >> metrics/weekly-loc.txt

# Visualize growth
cat metrics/weekly-loc.txt
# 2025-01-08: Source: 11234 lines
# 2025-01-15: Source: 12456 lines
# Growth: +1222 lines (+10.9%)
```

### Test Coverage Ratio
```bash
# Calculate test-to-code ratio
SRC_LINES=$(find src -name '*.php' -exec wc -l {} \; | awk '{sum += $1} END {print sum}')
TEST_LINES=$(find tests -name '*.php' -exec wc -l {} \; | awk '{sum += $1} END {print sum}')

RATIO=$(echo "scale=2; $TEST_LINES / $SRC_LINES" | bc)
echo "Test-to-Code Ratio: $RATIO"

# Good ratios:
# 0.5-0.8:  Healthy (50-80% test code relative to source)
# 0.8-1.2:  Excellent (comprehensive testing)
# > 1.2:    Possibly over-testing
```

---

## Best Practices

### 1. Benchmark Regularly
```bash
# Weekly performance checks
make bench REF=main REPORT=1

# Before optimization
make bench STORE=1 TAG=pre-optimization

# After optimization
make bench REF=pre-optimization REPORT=1
```

### 2. Enforce Pre-commit Hooks
```bash
# Team setup script
# setup-team.sh
#!/bin/bash

echo "Setting up development environment..."
make install-dev
make git-hooks-setup
make info

echo "✓ Setup complete. Pre-commit hooks active."
```

### 3. Document Releases Thoroughly
```markdown
<!-- CHANGELOG.md template -->
## [2.0.0] - 2025-01-15

### Added
- New parser architecture with AST nodes
- Benchmark suite for performance tracking

### Changed
- **BREAKING**: Renamed `Lexer::parse()` to `Lexer::tokenize()`
- Improved error messages with context

### Fixed
- Memory leak in recursive parsing
- Thread safety in token cache

### Performance
- 25% faster tokenization (see benchmarks/v2.0.0.txt)
- 40% reduction in memory usage

### Security
- Fixed XSS vulnerability in error output (CVE-2025-XXXX)
```

### 4. Automate Release Process
```bash
# .github/workflows/release.yml
name: Release

on:
  workflow_dispatch:
    inputs:
      version:
        description: 'Version to release (e.g., 1.0.0)'
        required: true

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
      
      - name: Run release checks
        run: make release
      
      - name: Update version
        run: |
          sed -i "s/\"version\": \".*\"/\"version\": \"${{ github.event.inputs.version }}\"/" composer.json
          git config user.name "GitHub Actions"
          git config user.email "actions@github.com"
          git add composer.json
          git commit -m "chore: release v${{ github.event.inputs.version }}"
      
      - name: Create tag
        run: make tag VERSION=${{ github.event.inputs.version }}
```

---

## Command Reference

### Benchmarking
```bash
make bench                          # Run benchmarks
make bench REF=main                 # Compare against main
make bench STORE=1 TAG=feature      # Store with tag
make bench REF=main REPORT=1        # Compare and save report
make bench-help                     # Show parameter help
```

### Git Hooks
```bash
make git-hooks-setup                # Install pre-commit hook
make git-hooks-check                # Verify installation
make git-hooks-remove               # Remove hook
```

### Release Management
```bash
make tag VERSION=X.Y.Z              # Create version tag
make release                        # Release preparation
```

### Information & Stats
```bash
make info                           # Environment information
make stats                          # Project statistics
make loc                            # Lines of code count
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║          Development Helpers Quick Reference              ║
╠═══════════════════════════════════════════════════════════╣
║ BENCHMARK    │ make bench                                 ║
║ COMPARE      │ make bench REF=main                        ║
║ STORE        │ make bench STORE=1 TAG=name                ║
║ REPORT       │ make bench REF=main REPORT=1               ║
║──────────────┼────────────────────────────────────────────║
║ SETUP HOOKS  │ make git-hooks-setup                       ║
║ CHECK HOOKS  │ make git-hooks-check                       ║
║ REMOVE HOOKS │ make git-hooks-remove                      ║
║──────────────┼────────────────────────────────────────────║
║ CREATE TAG   │ make tag VERSION=1.0.0                     ║
║ RELEASE      │ make release                               ║
║──────────────┼────────────────────────────────────────────║
║ INFO         │ make info                                  ║
║ STATS        │ make stats                                 ║
║ LOC          │ make loc                                   ║
╚═══════════════════════════════════════════════════════════╝

Benchmark Workflow:
  1. make bench STORE=1 TAG=main ENFORCE_MAIN=1
  2. (make changes)
  3. make bench REF=main
  4. make bench STORE=1 TAG=feature-name

Release Workflow:
  1. make release
  2. Update CHANGELOG.md + composer.json
  3. git commit -m "chore: release vX.Y.Z"
  4. make tag VERSION=X.Y.Z
```

---

**Version**: 1.0.0  
**Module**: `Makefile.helpers.mk`    
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
