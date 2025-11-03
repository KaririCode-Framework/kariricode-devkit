<div align="center">

# Setup & Installation

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php)](https://www.php.net)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?style=for-the-badge&logo=composer)](https://getcomposer.org)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation Methods](#installation-methods)
4. [Dependency Management](#dependency-management)
5. [Validation & Security](#validation--security)
6. [Cleanup Operations](#cleanup-operations)
7. [Troubleshooting](#troubleshooting)
8. [Best Practices](#best-practices)

---

## Overview

### Scope

The setup module (`Makefile.setup.mk`) provides targets for:
- **PHP version validation** (8.4.0+ enforcement)
- **Dependency installation** (Composer-based)
- **Security auditing** (vulnerability scanning)
- **Environment verification** (tool availability)
- **Cleanup operations** (artifact removal)

### Module Architecture
```
Setup Module (.make/local/Makefile.setup.mk)
│
├── Prerequisites
│   └── check-php              → PHP version validation
│
├── Installation
│   ├── install                → Standard installation
│   ├── install-dev            → Development installation
│   ├── fresh-install          → Clean installation
│   └── update                 → Dependency updates
│
├── Validation
│   ├── verify-install         → Post-install verification
│   ├── validate               → composer.json validation
│   ├── security               → Vulnerability scan
│   ├── security-strict        → Strict security audit
│   └── outdated               → Outdated dependency check
│
└── Cleanup
    ├── clean                  → Remove artifacts
    └── clean-all              → Deep clean + vendor
```

---

## Prerequisites

### System Requirements

#### Minimum Requirements

| Component | Minimum Version | Verification Command |
|-----------|-----------------|---------------------|
| **PHP** | 8.4.0+ | `php -v` |
| **Composer** | 2.0+ | `composer --version` |
| **Git** | 2.0+ | `git --version` |
| **Make** | 3.81+ | `make --version` |

#### Required PHP Extensions
```bash
# Check installed extensions
php -m

# Required:
- json          # JSON processing
- mbstring      # Multibyte string support
- xml           # XML parsing
- tokenizer     # PHP tokenization
- pcre          # Regular expressions

# Recommended:
- opcache       # Performance optimization
- xdebug        # Debugging & coverage
- redis         # Caching support
```

### Verification

#### Check PHP Version
```bash
# Validate PHP version requirement
make check-php

# Output:
# → Checking PHP version...
# ✓ PHP version 8.4.14 OK (>= 8.4.0)
```

**Version Comparison Logic:**
```makefile
# From Makefile.functions.mk
CURRENT_VERSION="8.4.14"
MIN_VERSION="8.4.0"
LOWEST=$(printf '%s\n%s' "$MIN_VERSION" "$CURRENT_VERSION" | sort -V | head -n1)

if [ "$LOWEST" != "$MIN_VERSION" ]; then
    echo "✗ PHP 8.4.0+ required, found $CURRENT_VERSION"
    exit 1
fi
```

#### Debug Composer Configuration
```bash
# Show Composer paths and availability
make debug-composer

# Output:
# COMPOSER_BIN = '/usr/local/bin/composer'
# COMPOSER     = '/usr/local/bin/composer'
# Composer version 2.8.4 2024-12-12

# If not found:
# COMPOSER_BIN = ''
# COMPOSER     = 'composer'
# Composer not found with command -v
```

#### Environment Information
```bash
# Show complete environment info
make info

# Output:
# ╔════════════════════════════════════════════════════════╗
# Project Information
# ────────────────────────────────────────────────────────
# PHP Version:        8.4.14
# PHP Binary:         /usr/bin/php
# Composer:           /usr/local/bin/composer
# Project Directory:  /home/user/kariricode-devkit
# Source Directory:   src
# Test Directory:     tests
#
# Installed Tools
# ────────────────────────────────────────────────────────
# PHPUnit:            ✓
# PHPStan:            ✓
# Psalm:              ✓
# PHPCS:              ✓
# PHP-CS-Fixer:       ✓
# Infection:          ✓
# PHPBench:           ✓
# ╚════════════════════════════════════════════════════════╝
```

---

## Installation Methods

### Standard Installation

#### When to Use
- **First time setup** from existing `composer.lock`
- **After cloning** repository
- **Restoring** from version control

#### Command
```bash
make install

# Workflow:
# 1. Check PHP version (make check-php)
# 2. Validate composer.json
# 3. Install from composer.lock (if valid)
# 4. OR update if lock file outdated
# 5. Optimize autoloader
# 6. Verify installation
```

#### Output Example
```bash
$ make install

→ Checking PHP version...
✓ PHP version 8.4.14 OK (>= 8.4.0)

→ Installing Composer dependencies...
Loading composer repositories with package information
Installing dependencies from lock file (including require-dev)
Package operations: 42 installs, 0 updates, 0 removals
  - Installing symfony/polyfill-php80 (v1.28.0): Extracting archive
  - Installing psr/container (2.0.2): Extracting archive
  ...
Generating optimized autoload files
✓ Installation complete
```

#### What Gets Installed
```bash
# Production dependencies
composer.json → require
├── php: ^8.4
└── kariricode/*

# Development tools
composer.json → require-dev
├── phpunit/phpunit: ^11.0
├── phpstan/phpstan: ^2.0
├── vimeo/psalm: ^5.0
├── squizlabs/php_codesniffer: ^3.7
├── friendsofphp/php-cs-fixer: ^3.0
└── infection/infection: ^0.27
```

### Development Installation

#### When to Use
- **Development environment** setup
- **Contributing** to the project
- **Need all tools** for QA

#### Command
```bash
make install-dev

# Differences from 'install':
# - Preserves composer.lock without validation
# - Installs ALL dev dependencies
# - No autoloader optimization
# - Keeps debugging info
```

#### Use Case
```bash
# Scenario: Setting up development environment
git clone https://github.com/KaririCode-Framework/kariricode-devkit.git
cd kariricode-devkit

# Install with all dev tools
make install-dev

# Verify tools are available
make info
# All tools should show ✓
```

### Fresh Installation

#### When to Use
- **Corrupted** `composer.lock`
- **Dependency conflicts**
- **Major version updates**
- **Clean slate** needed

#### Command
```bash
make fresh-install

# Workflow:
# 1. Remove composer.lock
# 2. Install fresh dependencies
# 3. Generate new lock file
# 4. Optimize autoloader
# 5. Verify installation
```

⚠️ **Warning**: This regenerates `composer.lock` and may update packages to newer versions within version constraints.

#### Output Example
```bash
$ make fresh-install

→ Removing composer.lock...
→ Installing fresh dependencies...
No composer.lock file present. Updating dependencies to latest version.
Loading composer repositories with package information
Updating dependencies
Lock file operations: 42 installs, 0 updates, 0 removals
  - Locking phpunit/phpunit (11.5.2)
  - Locking phpstan/phpstan (2.1.5)
  ...
Writing lock file
✓ Fresh installation complete
```

#### When to Commit New Lock File
```bash
# Review changes
git diff composer.lock

# If intentional update:
git add composer.lock
git commit -m "chore: regenerate composer.lock"

# If accidental:
git checkout composer.lock
make install  # Restore from existing lock
```

---

## Dependency Management

### Update Dependencies

#### Update All Dependencies
```bash
make update

# Executes:
# composer update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader

# Updates:
# - Direct dependencies
# - Transitive dependencies
# - Respects version constraints in composer.json
```

#### Update Specific Package
```bash
# Use composer directly
make exec-php CMD="composer update vendor/package"

# Example:
make exec-php CMD="composer update phpunit/phpunit"
```

#### Update with Constraints
```bash
# Update within patch versions
make exec-php CMD="composer update phpunit/phpunit --prefer-lowest"

# Update with stability
make exec-php CMD="composer update --prefer-stable"
```

### Add New Dependencies
```bash
# Production dependency
make exec-php CMD="composer require vendor/package"

# Development dependency
make exec-php CMD="composer require --dev vendor/package"

# Example: Add new KaririCode component
make exec-php CMD="composer require kariricode/new-component"
```

### Remove Dependencies
```bash
# Remove package
make exec-php CMD="composer remove vendor/package"

# Example:
make exec-php CMD="composer remove phpunit/phpunit"
```

### Check Outdated Packages
```bash
make outdated

# Output shows direct dependencies needing updates:
# ╔════════════════════════════════════════════════════════╗
# Direct dependencies required in composer.json:
# phpunit/phpunit        11.5.1  → 11.5.2  (patch update)
# phpstan/phpstan        2.1.3   → 2.1.5   (patch update)
# psalm/psalm            5.26.1  → 5.27.0  (minor update)
# ╚════════════════════════════════════════════════════════╝

# Ignores indirect dependencies by default
```

---

## Validation & Security

### Validate composer.json

#### Syntax & Structure Validation
```bash
make validate

# Checks:
# - JSON syntax
# - Schema compliance
# - Required fields (name, description, license)
# - Version constraints syntax
# - PSR-4 autoload mappings

# Output:
# → Validating composer.json...
# ./composer.json is valid
# ✓ composer.json is valid
```

#### Common Validation Errors

**Invalid JSON:**
```json
{
    "name": "kariricode/devkit"
    "description": "Missing comma"  ← Error
}
```

**Invalid Version Constraint:**
```json
{
    "require": {
        "php": "8.4"  ← Should be "^8.4" or ">=8.4"
    }
}
```

**Invalid PSR-4 Namespace:**
```json
{
    "autoload": {
        "psr-4": {
            "KaririCode\\": "src"  ← Missing trailing backslash
        }
    }
}
```

### Security Auditing

#### Standard Security Check
```bash
make security

# Executes:
# composer audit --no-dev --locked

# Checks for:
# - Known security vulnerabilities
# - CVEs in dependencies
# - Abandoned packages (warning only)
```

#### Output Examples

**No Vulnerabilities:**
```bash
$ make security

→ Checking for security vulnerabilities...
Found 0 security vulnerability advisories affecting 0 packages
✓ No security vulnerabilities found
```

**Vulnerabilities Found:**
```bash
$ make security

→ Checking for security vulnerabilities...
Found 2 security vulnerability advisories affecting 1 package:

symfony/http-kernel (v6.2.0)
├── CVE-2023-XXXXX (high severity)
│   Fixed in: 6.2.6
│   Description: Information disclosure vulnerability
└── See: https://github.com/advisories/GHSA-xxxx-yyyy

✗ Security vulnerabilities found
```

**Action Steps:**
```bash
# Update affected package
make exec-php CMD="composer update symfony/http-kernel"

# Verify fix
make security
```

#### Abandoned Packages
```bash
$ make security

⚠  Found abandoned packages (informational only):
─────────────────────────────────────────────────
Package: vendor/old-package
Replacement: vendor/new-package
─────────────────────────────────────────────────
✓ No security vulnerabilities found
```

#### Strict Security Mode
```bash
make security-strict

# Differences from 'security':
# - Includes dev dependencies
# - Treats abandoned packages as errors
# - More verbose output
# - Fails on any issue

# Use in:
# - CI/CD pipelines
# - Pre-release checks
# - Security-critical projects
```

---

## Cleanup Operations

### Clean Build Artifacts
```bash
make clean

# Removes:
# ├── build/              (Build outputs)
# ├── coverage/           (Code coverage reports)
# ├── reports/            (Static analysis reports)
# ├── var/cache/          (Application cache)
# ├── .phpunit.cache      (PHPUnit cache)
# ├── .phpunit.result.cache
# ├── .php-cs-fixer.cache (CS Fixer cache)
# ├── infection.log       (Mutation test logs)
# └── infection.html

# Does NOT remove:
# - vendor/               (Dependencies)
# - composer.lock         (Lock file)
# - Source code
```

#### When to Use

- **Before commit** (remove temporary files)
- **Before benchmarks** (clean state)
- **Disk space** (reclaim space)
- **Fresh start** (consistent state)

### Deep Clean
```bash
make clean-all

# Removes everything from 'clean' PLUS:
# ├── vendor/             (All dependencies)
# └── composer.lock       (Dependency lock file)

# Requires 're-install' after:
# make install
```

⚠️ **Warning**: This removes `vendor/` and requires re-downloading all dependencies.

#### When to Use

- **Before fresh-install**
- **Switching branches** with different dependencies
- **Major PHP version** upgrades
- **Corrupted vendor** directory

### Cleanup Workflow
```bash
# Daily development cleanup
make clean

# Weekly deep cleanup
make clean-all
make install

# Before release
make clean
make ci-full  # Ensures clean build
```

---

## Troubleshooting

### Issue 1: Composer Not Found

**Symptoms:**
```bash
$ make install
COMPOSER_BIN = ''
COMPOSER     = 'composer'
composer: command not found
```

**Diagnosis:**
```bash
make debug-composer
which composer
echo $PATH
```

**Solutions:**

**Option A: Install Composer Globally**
```bash
# Download installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Verify installer (optional)
php -r "if (hash_file('sha384', 'composer-setup.php') === 'EXPECTED_HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install globally
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Cleanup
php -r "unlink('composer-setup.php');"

# Verify
composer --version
```

**Option B: Use Docker**
```bash
# Use Docker for Composer commands
make docker-composer CMD="install"
```

### Issue 2: PHP Version Mismatch

**Symptoms:**
```bash
$ make check-php
✗ PHP 8.4.0+ required, found 8.3.12
```

**Solutions:**

**Option A: Install PHP 8.4 (Ubuntu/Debian)**
```bash
# Add PPA
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 8.4
sudo apt install php8.4 php8.4-cli php8.4-{mbstring,xml,curl}

# Set as default
sudo update-alternatives --set php /usr/bin/php8.4

# Verify
php -v
make check-php
```

**Option B: Use Docker**
```bash
# All commands in Docker with PHP 8.4
make docker-install
make docker-ci
```

### Issue 3: Memory Limit Errors

**Symptoms:**
```bash
$ make install
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solutions:**

**Option A: Increase PHP Memory Limit**
```bash
# Temporary (single command)
php -d memory_limit=512M $(which composer) install

# Permanent (php.ini)
echo "memory_limit = 512M" | sudo tee -a /etc/php/8.4/cli/php.ini

# Verify
php -i | grep memory_limit
```

**Option B: Use Environment Variable**
```bash
# Set in .env
echo "COMPOSER_MEMORY_LIMIT=-1" >> .env

# Or export globally
export COMPOSER_MEMORY_LIMIT=-1
make install
```

### Issue 4: Lock File Out of Date

**Symptoms:**
```bash
$ make install
Warning: The lock file is not up to date with the latest changes in composer.json
```

**Diagnosis:**
```bash
# Check what changed
composer validate --strict
```

**Solutions:**

**Option A: Update Lock File**
```bash
# If intentional changes
make update

# Commit new lock file
git add composer.lock
git commit -m "chore: update composer.lock"
```

**Option B: Restore composer.json**
```bash
# If accidental changes
git checkout composer.json
make install
```

### Issue 5: Authentication Required

**Symptoms:**
```bash
$ make install
Authentication required (gitlab.com):
```

**Solutions:**

**Option A: Add Auth Token**
```bash
# GitHub token
composer config --global github-oauth.github.com YOUR_TOKEN

# GitLab token
composer config --global gitlab-oauth.gitlab.com YOUR_TOKEN

# Verify
cat ~/.composer/auth.json
```

**Option B: SSH Keys**
```bash
# Use SSH instead of HTTPS
composer config --global use-github-api false

# Ensure SSH key is added
ssh-add ~/.ssh/id_rsa
```

### Issue 6: Network Timeouts

**Symptoms:**
```bash
$ make install
  Failed to download symfony/http-kernel from dist: connection timed out
```

**Solutions:**

**Option A: Increase Timeout**
```bash
# Increase process timeout
export COMPOSER_PROCESS_TIMEOUT=600
make install
```

**Option B: Use Different Mirror**
```bash
# Configure packagist mirror
composer config --global repo.packagist composer https://packagist.com
```

**Option C: Retry**
```bash
# Sometimes transient network issues
make install  # Try again
```

---

## Best Practices

### 1. Version Control

#### Commit These Files
```bash
✅ composer.json        # Dependency definitions
✅ composer.lock        # Locked versions
✅ Makefile            # Build automation
✅ .make/              # Makefile modules
```

#### Ignore These Files
```bash
❌ vendor/             # Downloaded dependencies
❌ .phpunit.cache      # Test cache
❌ .php-cs-fixer.cache # CS cache
❌ build/              # Build artifacts
❌ coverage/           # Coverage reports
```

**.gitignore Example:**
```gitignore
# Dependencies
/vendor/

# Build artifacts
/build/
/coverage/
/reports/

# Caches
/.phpunit.cache
/.phpunit.result.cache
/.php-cs-fixer.cache
/var/cache/

# Infection
infection.log
infection.html
```

### 2. Update Strategy

#### Semantic Versioning Approach
```json
{
    "require": {
        "kariricode/router": "^2.0",     // Major: Breaking changes
        "symfony/console": "~6.4.0",     // Minor: New features
        "psr/log": "3.0.*"               // Patch: Bug fixes only
    }
}
```

**Recommended Constraints:**
- `^2.0` - Allow minor and patch updates (2.0, 2.1, 2.1.1)
- `~2.1.0` - Allow patch updates only (2.1.0, 2.1.1, 2.1.2)
- `2.1.*` - Alias for `~2.1.0`
- `>=2.0 <3.0` - Explicit range

#### Update Workflow
```bash
# Weekly: Check for updates
make outdated

# Review release notes for each package
# https://github.com/vendor/package/releases

# Update patch versions (safe)
make exec-php CMD="composer update --prefer-stable"

# Test thoroughly
make ci-full

# Commit if successful
git add composer.lock
git commit -m "chore: update dependencies (patch)"
```

### 3. Security Hygiene
```bash
# Daily (automated in CI)
make security

# Weekly (manual review)
make outdated
make security-strict

# Monthly (dependency audit)
make update
make ci-full
```

### 4. Environment Consistency

#### Team Setup
```bash
# Document in README.md
## Requirements
- PHP 8.4+
- Composer 2.x

## Setup
make check-php
make install-dev
make info
```

#### CI/CD Setup
```yaml
# .github/workflows/ci.yml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.4'
    coverage: xdebug

- name: Validate Environment
  run: make check-php

- name: Install Dependencies
  run: make install

- name: Verify Installation
  run: make verify-install
```

### 5. Cleanup Routine
```bash
# Before commit
make clean

# Weekly
make clean
make install

# Monthly (or disk space low)
make clean-all
make fresh-install
```

---

## Command Reference

### Prerequisites
```bash
make check-php          # Validate PHP version (8.4+)
make debug-composer     # Debug Composer configuration
make info               # Show environment information
```

### Installation
```bash
make install            # Standard installation from lock
make install-dev        # Development installation
make fresh-install      # Clean installation (regenerates lock)
make update             # Update dependencies
make verify-install     # Verify installation success
```

### Validation & Security
```bash
make validate           # Validate composer.json
make security           # Security vulnerability scan
make security-strict    # Strict security audit
make outdated           # Check outdated dependencies
```

### Cleanup
```bash
make clean              # Remove build artifacts
make clean-all          # Deep clean (includes vendor/)
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║              Setup & Installation Quick Reference         ║
╠═══════════════════════════════════════════════════════════╣
║ CHECK PHP    │ make check-php                             ║
║ INSTALL      │ make install                               ║
║ FRESH START  │ make fresh-install                         ║
║ UPDATE       │ make update                                ║
║──────────────┼────────────────────────────────────────────║
║ VERIFY       │ make verify-install                        ║
║ VALIDATE     │ make validate                              ║
║ SECURITY     │ make security                              ║
║ OUTDATED     │ make outdated                              ║
║──────────────┼────────────────────────────────────────────║
║ CLEAN        │ make clean                                 ║
║ DEEP CLEAN   │ make clean-all                             ║
║ INFO         │ make info                                  ║
╚═══════════════════════════════════════════════════════════╝

Daily Workflow:
  make install → make clean → make test

Weekly Workflow:
  make outdated → make security → make update

Fresh Start:
  make clean-all → make fresh-install → make verify-install
```

---

**Version**: 1.0.0  
**Module**: `Makefile.setup.mk`    
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
