<div align="center">

# CI/CD Pipeline Orchestration

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![CI](https://img.shields.io/badge/CI-Automated-2088FF?style=for-the-badge&logo=github-actions)](https://github.com/features/actions)
[![CD](https://img.shields.io/badge/CD-Release%20Ready-00C853?style=for-the-badge&logo=gitbook)](https://github.com/features/actions)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Pipeline Architecture](#pipeline-architecture)
3. [CI Pipelines](#ci-pipelines)
4. [CD Pipeline](#cd-pipeline)
5. [Pre-commit Hooks](#pre-commit-hooks)
6. [CI/CD Integration](#cicd-integration)
7. [Pipeline Optimization](#pipeline-optimization)
8. [Best Practices](#best-practices)

---

## Overview

### Scope

The pipeline module (`Makefile.orchestration.mk`) provides:
- **CI Pipeline**: Fast continuous integration checks
- **Full CI Pipeline**: Comprehensive quality validation
- **CD Pipeline**: Release preparation workflow
- **Pre-commit Pipeline**: Developer quality gates

### Design Philosophy
```
┌─────────────────────────────────────────────────────────┐
│         Pipeline Orchestration Philosophy               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  COMPOSABILITY    → Build complex from simple          │
│  FAIL-FAST        → Stop on first error                │
│  FEEDBACK SPEED   → Critical checks first              │
│  IDEMPOTENCY      → Same result every run              │
│  ISOLATION        → No side effects between stages     │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Pipeline Composition
```makefile
# Simple targets (atomic operations)
lint:        @vendor/bin/phplint
cs-check:    @vendor/bin/phpcs
phpstan:     @vendor/bin/phpstan
test:        @vendor/bin/phpunit

# Composed pipelines (orchestrated workflows)
ci:          lint + cs-check + phpstan + psalm + test
ci-full:     ci + coverage + mutation + security
cd:          ci-full + bench + release-prep
```

---

## Pipeline Architecture

### Execution Flow
```
┌─────────────────────────────────────────────────────────┐
│                   Pipeline Stages                       │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Stage 1: Prerequisites                                 │
│  ├─ check-php        (PHP version validation)          │
│  └─ verify-install   (Dependencies check)              │
│         ↓                                               │
│  Stage 2: Fast Checks (< 30 seconds)                   │
│  ├─ lint             (Syntax errors)                    │
│  ├─ cs-check         (Code style)                       │
│  └─ validate         (composer.json)                    │
│         ↓                                               │
│  Stage 3: Static Analysis (1-2 minutes)                │
│  ├─ phpstan          (Type errors)                      │
│  └─ psalm            (Logic errors)                     │
│         ↓                                               │
│  Stage 4: Testing (1-3 minutes)                        │
│  └─ test             (Unit + Integration)               │
│         ↓                                               │
│  Stage 5: Quality Metrics (3-5 minutes) [OPTIONAL]    │
│  ├─ coverage         (Code coverage)                    │
│  └─ mutation         (Mutation testing)                 │
│         ↓                                               │
│  Stage 6: Security (< 30 seconds)                      │
│  └─ security         (Vulnerability scan)               │
│         ↓                                               │
│  Stage 7: Performance (2-5 minutes) [CD ONLY]         │
│  └─ bench            (Benchmarking)                     │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Fail-Fast Strategy
```makefile
# Each stage must pass before proceeding
ci:
    @$(MAKE) --no-print-directory check-php    # FAIL → STOP
    @$(MAKE) --no-print-directory lint          # FAIL → STOP
    @$(MAKE) --no-print-directory cs-check      # FAIL → STOP
    @$(MAKE) --no-print-directory phpstan       # FAIL → STOP
    @$(MAKE) --no-print-directory psalm         # FAIL → STOP
    @$(MAKE) --no-print-directory test          # FAIL → STOP
    @echo "✓ CI pipeline completed"             # ALL PASSED
```

---

## CI Pipelines

### Fast CI Pipeline

#### Purpose

**Optimize for developer feedback speed:**
- ✅ Essential quality checks only
- ✅ Target execution: < 2 minutes
- ✅ Run on every commit
- ✅ High signal-to-noise ratio

#### Command
```bash
make ci

# Executes in sequence:
# 1. check-php      → PHP 8.4+ validation
# 2. lint           → Syntax errors
# 3. cs-check       → Code style violations
# 4. phpstan        → Static analysis (level max)
# 5. psalm          → Type checking
# 6. test           → Test suite execution

# Total time: ~1-2 minutes
```

#### Output Example
```bash
$ make ci

╔════════════════════════════════════════════════════════╗
║  KaririCode\DevKit CI Pipeline                         ║
╚════════════════════════════════════════════════════════╝

→ Checking PHP version...
✓ PHP version 8.4.14 OK (>= 8.4.0)

→ Linting PHP files...
✓ All PHP files are valid

→ Checking coding standards...
✓ Coding standards check passed

→ Running PHPStan...
✓ PHPStan analysis passed

→ Running Psalm...
✓ Psalm analysis passed

→ Running tests...
OK (150 tests, 420 assertions)
✓ Tests passed

✓ CI pipeline completed successfully

Time: 1m 23s
```

#### When to Use

| Scenario | Use CI |
|----------|--------|
| Local development | ✅ Before every commit |
| Pull request checks | ✅ Required status check |
| Branch protection | ✅ Merge requirement |
| Pre-push hook | ✅ Optional automation |

### Full CI Pipeline

#### Purpose

**Comprehensive quality validation:**
- ✅ All CI checks + extended quality metrics
- ✅ Target execution: 3-5 minutes
- ✅ Run before release
- ✅ Production readiness verification

#### Command
```bash
make ci-full

# Executes ALL stages:
# 1. check-php      → PHP version
# 2. validate       → composer.json validation
# 3. security       → Vulnerability scan
# 4. lint           → Syntax check
# 5. cs-check       → Code style
# 6. phpstan        → Static analysis
# 7. psalm          → Type checking
# 8. test           → All tests
# 9. coverage       → Code coverage (requires Xdebug)
# 10. mutation      → Mutation testing (slow)

# Total time: ~3-5 minutes
```

#### Output Example
```bash
$ make ci-full

╔════════════════════════════════════════════════════════╗
║  KaririCode\DevKit Full CI Pipeline                    ║
╚════════════════════════════════════════════════════════╝

→ Checking PHP version...
✓ PHP version 8.4.14 OK (>= 8.4.0)

→ Validating composer.json...
✓ composer.json is valid

→ Checking for security vulnerabilities...
✓ No security vulnerabilities found

→ Linting PHP files...
✓ All PHP files are valid

→ Checking coding standards...
✓ Coding standards check passed

→ Running PHPStan...
✓ PHPStan analysis passed

→ Running Psalm...
✓ Psalm analysis passed

→ Running tests...
OK (150 tests, 420 assertions)
✓ Tests passed

→ Generating code coverage report...
Code Coverage: 79.24% (1234/1557 lines)
✓ Coverage report generated: coverage/html/index.html

→ Running mutation tests...
Mutation Score Indicator (MSI): 82%
Covered Code MSI: 92%
✓ Mutation testing complete

✓ Full CI pipeline completed successfully

Time: 4m 37s
```

#### When to Use

| Scenario | Use CI-Full |
|----------|-------------|
| Release candidates | ✅ Required |
| Main branch merges | ✅ Recommended |
| Weekly quality check | ✅ Best practice |
| Production deployment | ✅ Mandatory |

### Pipeline Comparison

| Feature | CI (Fast) | CI-Full |
|---------|-----------|---------|
| **Execution Time** | ~1-2 min | ~3-5 min |
| **PHP Version Check** | ✅ | ✅ |
| **Syntax Linting** | ✅ | ✅ |
| **Code Style** | ✅ | ✅ |
| **Static Analysis** | ✅ | ✅ |
| **Tests** | ✅ | ✅ |
| **Composer Validation** | ❌ | ✅ |
| **Security Scan** | ❌ | ✅ |
| **Code Coverage** | ❌ | ✅ |
| **Mutation Testing** | ❌ | ✅ |
| **Use Case** | Every commit | Pre-release |

---

## CD Pipeline

### Purpose

**Complete release readiness validation:**
- ✅ Full CI-Full pipeline
- ✅ Performance benchmarking
- ✅ Release preparation checklist
- ✅ Final production verification

### Command
```bash
make cd

# Executes:
# 1. ci-full         → Complete quality validation
# 2. bench           → Performance baseline
# 3. Release checklist display

# Total time: ~5-8 minutes
```

### Output Example
```bash
$ make cd

╔════════════════════════════════════════════════════════╗
║  KaririCode\DevKit CD Pipeline                         ║
╚════════════════════════════════════════════════════════╝

[Running ci-full pipeline...]
✓ Full CI pipeline completed successfully

→ Running benchmarks...
✓ Benchmarks complete

✓ CD pipeline completed - Ready for release

Next steps:
  1. Update CHANGELOG.md
  2. Update version in composer.json
  3. Commit changes
  4. Run: make tag VERSION=X.Y.Z
  5. Push to GitHub
  6. Create GitHub release

Time: 6m 12s
```

### Release Workflow Integration
```bash
# Complete release workflow

# 1. Ensure clean state
git checkout main
git pull origin main

# 2. Run CD pipeline
make cd

# 3. Update version files
nano CHANGELOG.md
nano composer.json

# 4. Commit changes
git add CHANGELOG.md composer.json
git commit -m "chore: release v2.0.0"

# 5. Create and push tag
make tag VERSION=2.0.0

# 6. Push changes
git push origin main --tags

# 7. Create GitHub release (manual or automated)
```

---

## Pre-commit Hooks

### Purpose

**Developer quality gate before commit:**
- ✅ Fast feedback (30-60 seconds)
- ✅ Auto-fix common issues
- ✅ Essential checks only
- ✅ Prevent broken commits

### Command
```bash
make pre-commit

# Executes:
# 1. format         → Auto-fix code style
# 2. lint           → Syntax validation
# 3. analyse        → Static analysis (phpstan + psalm)
# 4. test-unit      → Unit tests only (fast)

# Total time: ~30-60 seconds
```

### Output Example
```bash
$ make pre-commit

→ Running pre-commit checks...

→ Formatting code...
Fixed 3 files in 1.234 seconds
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

Time: 0m 42s
```

### Git Hook Installation

#### Setup Hook
```bash
# Install pre-commit hook
make git-hooks-setup

# What it does:
# 1. Creates .git/hooks/pre-commit
# 2. Backs up existing hook (if any)
# 3. Makes hook executable
# 4. Configures to run 'make pre-commit'

# Output:
# → Setting up git hooks...
# ✓ Git hooks set up
```

#### Verify Installation
```bash
make git-hooks-check

# Output:
# → Verifying git hooks...
# ✓ pre-commit hook is installed correctly
```

#### Remove Hook
```bash
make git-hooks-remove

# What it does:
# 1. Removes .git/hooks/pre-commit
# 2. Restores backup (if exists)

# Output:
# → Cleaning up git hooks...
# ↩ Restoring backup pre-commit hook...
# ✓ Git hooks cleaned
```

### Hook Bypass (Emergency)
```bash
# Bypass hook for emergency commits
git commit --no-verify -m "emergency: fix critical bug"

# ⚠️ Use sparingly - still run checks after:
make pre-commit
```

### Custom Pre-commit Configuration
```bash
# Edit .git/hooks/pre-commit after installation
nano .git/hooks/pre-commit

# Example: Skip tests if changing docs only
#!/bin/sh
set -e

# Get changed files
CHANGED=$(git diff --cached --name-only)

# Skip tests if only docs changed
if echo "$CHANGED" | grep -qv "\.md$"; then
    make pre-commit
else
    echo "Only docs changed, skipping full checks"
    make format
    make lint
fi
```

---

## CI/CD Integration

### GitHub Actions

#### Basic CI Workflow
```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  ci:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pcov
          coverage: pcov
      
      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-
      
      - name: Install dependencies
        run: make install
      
      - name: Run CI pipeline
        run: make ci
      
      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: test-results
          path: build/reports/
```

#### Full CI Workflow (with coverage)
```yaml
# .github/workflows/ci-full.yml
name: Full CI

on:
  push:
    branches: [ main ]
  schedule:
    - cron: '0 0 * * 0'  # Weekly on Sunday

jobs:
  ci-full:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: xdebug
      
      - name: Install dependencies
        run: make install
      
      - name: Run Full CI
        run: make ci-full
      
      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage/clover.xml
          fail_ci_if_error: true
      
      - name: Upload mutation report
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: infection-report
          path: infection.html
```

#### Release Workflow
```yaml
# .github/workflows/release.yml
name: Release

on:
  push:
    tags:
      - 'v*.*.*'

jobs:
  release:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
      
      - name: Install dependencies
        run: make install
      
      - name: Run CD pipeline
        run: make cd
      
      - name: Create GitHub Release
        uses: softprops/action-gh-release@v1
        with:
          files: |
            coverage/clover.xml
            infection.html
            build/benchmarks/last.txt
          generate_release_notes: true
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

### GitLab CI
```yaml
# .gitlab-ci.yml
stages:
  - validate
  - test
  - quality
  - release

variables:
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.composer-cache"

cache:
  paths:
    - vendor/
    - .composer-cache/

# Fast CI (on every commit)
ci:
  stage: test
  image: kariricode/php-api-stack:dev
  script:
    - make install
    - make ci
  artifacts:
    reports:
      junit: build/reports/junit.xml
  only:
    - merge_requests
    - branches

# Full CI (on main branch)
ci-full:
  stage: quality
  image: kariricode/php-api-stack:dev
  script:
    - make install
    - make ci-full
  coverage: '/Lines:\s+(\d+\.\d+)%/'
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage/clover.xml
    paths:
      - coverage/
      - infection.html
    expire_in: 30 days
  only:
    - main

# Release preparation
cd:
  stage: release
  image: kariricode/php-api-stack:dev
  script:
    - make install
    - make cd
  artifacts:
    paths:
      - build/benchmarks/
  only:
    - tags
```

### Jenkins Pipeline
```groovy
// Jenkinsfile
pipeline {
    agent any
    
    environment {
        COMPOSER_HOME = "${WORKSPACE}/.composer"
    }
    
    stages {
        stage('Setup') {
            steps {
                sh 'make check-php'
                sh 'make install'
            }
        }
        
        stage('Fast CI') {
            when {
                not { branch 'main' }
            }
            steps {
                sh 'make ci'
            }
        }
        
        stage('Full CI') {
            when { branch 'main' }
            steps {
                sh 'make ci-full'
            }
            post {
                always {
                    junit 'build/reports/junit.xml'
                    publishHTML([
                        reportDir: 'coverage/html',
                        reportFiles: 'index.html',
                        reportName: 'Coverage Report'
                    ])
                }
            }
        }
        
        stage('CD') {
            when { tag "v*" }
            steps {
                sh 'make cd'
            }
        }
    }
    
    post {
        failure {
            mail to: 'team@example.com',
                 subject: "Build Failed: ${env.JOB_NAME} - ${env.BUILD_NUMBER}",
                 body: "Check console output at ${env.BUILD_URL}"
        }
    }
}
```

---

## Pipeline Optimization

### Parallel Execution
```makefile
# Example: Parallel static analysis
analyse-parallel:
    @echo "Running static analysis in parallel..."
    @$(PHPSTAN) analyse src --level=max & \
    PHPSTAN_PID=$$!; \
    $(PSALM) --show-info=true --stats --no-cache & \
    PSALM_PID=$$!; \
    wait $$PHPSTAN_PID $$PSALM_PID
    @echo "✓ Analysis complete"
```

### Caching Strategy
```yaml
# GitHub Actions cache example
- name: Cache tools
  uses: actions/cache@v3
  with:
    path: |
      ~/.composer/cache
      var/cache/phpstan
      .php-cs-fixer.cache
    key: ${{ runner.os }}-tools-${{ hashFiles('**/composer.lock') }}
```

### Incremental Testing
```bash
# Test only changed files
CHANGED_FILES=$(git diff --name-only main...HEAD | grep '\.php$')
vendor/bin/phpunit $(echo $CHANGED_FILES | sed 's/src/tests/g')
```

### Skip Slow Tests in CI
```xml
<!-- phpunit.xml -->
<groups>
    <exclude>
        <group>slow</group>
        <group>integration</group>
    </exclude>
</groups>
```
```bash
# Fast CI: Skip slow tests
make test  # Excludes @slow and @integration

# Full CI: Include all tests
vendor/bin/phpunit --group slow,integration
```

---

## Best Practices

### 1. Pipeline Selection
```bash
# Development (every commit)
make pre-commit           # 30-60s

# Pull request
make ci                   # 1-2 min

# Main branch merge
make ci-full              # 3-5 min

# Release
make cd                   # 5-8 min
```

### 2. Fail-Fast Principle
```bash
# ✅ Good: Stop on first failure
make ci
# Lint fails → STOP (no point running tests)

# ❌ Bad: Continue despite errors
make lint || true
make analyse || true
make test
# Wastes time, unclear which check failed
```

### 3. Feedback Speed Optimization
```bash
# Order checks by speed (fast → slow)
1. lint         (5-10s)   ← Fast feedback
2. cs-check     (10-20s)
3. phpstan      (30-60s)
4. psalm        (30-60s)
5. test         (1-2min)
6. coverage     (2-3min)  ← Slow, run less often
7. mutation     (3-5min)
```

### 4. Branch Protection Rules

**GitHub Branch Protection:**
```
Settings → Branches → main
├─ Require status checks to pass
│  ├─ CI (required)
│  ├─ Full CI (optional)
│  └─ CD (for tags only)
├─ Require conversation resolution
└─ Require linear history
```

### 5. Badge Integration
```markdown
# README.md
![CI](https://github.com/user/repo/workflows/CI/badge.svg)
![Coverage](https://codecov.io/gh/user/repo/branch/main/graph/badge.svg)
![Mutation](https://img.shields.io/endpoint?url=https://badge-api.stryker-mutator.io/github.com/user/repo/main)
```

### 6. Quality Metrics Tracking
```bash
# Weekly quality report
{
    echo "Quality Report - $(date)"
    echo "================================"
    make ci-full 2>&1 | tee quality-report.txt
    
    echo ""
    echo "Coverage:"
    grep "Lines:" quality-report.txt
    
    echo ""
    echo "Mutation Score:"
    grep "MSI:" quality-report.txt
} | mail -s "Weekly Quality Report" team@example.com
```

---

## Command Reference

### Pipeline Commands
```bash
make check              # Alias for 'ci'
make ci                 # Fast CI pipeline (~1-2 min)
make ci-full            # Full CI pipeline (~3-5 min)
make cd                 # CD pipeline (~5-8 min)
make pre-commit         # Pre-commit checks (~30-60s)
```

### Git Hook Management
```bash
make git-hooks-setup    # Install pre-commit hook
make git-hooks-check    # Verify hook installation
make git-hooks-remove   # Remove and restore backup
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║           CI/CD Pipeline Quick Reference                  ║
╠═══════════════════════════════════════════════════════════╣
║ PRE-COMMIT   │ make pre-commit        (~30-60s)          ║
║ FAST CI      │ make ci                (~1-2 min)         ║
║ FULL CI      │ make ci-full           (~3-5 min)         ║
║ CD RELEASE   │ make cd                (~5-8 min)         ║
║──────────────┼───────────────────────────────────────────║
║ GIT HOOKS    │ make git-hooks-setup                      ║
║ VERIFY HOOKS │ make git-hooks-check                      ║
║ REMOVE HOOKS │ make git-hooks-remove                     ║
╚═══════════════════════════════════════════════════════════╝

Pipeline Selection:
  Local development:  make pre-commit
  Pull request:       make ci
  Main branch:        make ci-full
  Release tag:        make cd

Execution Order (fail-fast):
  1. PHP version → 2. Lint → 3. Style → 4. Analysis → 5. Tests
```

---

**Version**: 1.0.0  
**Module**: `Makefile.orchestration.mk`    
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
