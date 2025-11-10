<div align="center">

# Docker Commands

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?style=for-the-badge&logo=docker)](https://www.docker.com)
[![PHP Stack](https://img.shields.io/badge/PHP%20Stack-8.4-777BB4?style=for-the-badge&logo=php)](https://hub.docker.com/r/kariricode/php-api-stack)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Docker Infrastructure](#docker-infrastructure)
3. [Docker Core Commands](#docker-core-commands)
4. [Docker QA Pipeline](#docker-qa-pipeline)
5. [Docker Image Management](#docker-image-management)
6. [Docker Utility Tools](#docker-utility-tools)
7. [Troubleshooting](#troubleshooting)
8. [Best Practices](#best-practices)

---
## Overview

### Scope

The Docker modules provide containerized execution for:
- **Isolated Environment**: Consistent PHP 8.4 environment
- **CI/CD Simulation**: Match production conditions locally
- **Team Consistency**: Same environment across all developers
- **Clean State**: No local configuration interference

### Module Architecture
```tree
.
├── .make/docker/
│   ├── Makefile.docker-core.mk    # → Core: Shell, Composer, PHP execution
│   ├── Makefile.docker-compose.mk # → Stack: Full stack management (up, down, logs)
│   ├── Makefile.docker-qa.mk      # → QA: CI/CD pipeline in containers
│   ├── Makefile.docker-image.mk   # → Image: Pull, info, clean
│   └── Makefile.docker-tools.mk   # → Tools: htop, jq, vim, network diagnostics
│
└── arfa-runtime-stack/docker/
    ├── entrypoint.sh          # → Entrypoint: Container initialization script
    ├── config-processor.sh    # → Config: Processes PHP/Swoole configs from env vars
    └── health-check.sh        # → Health: Docker health check script
```

### Docker vs Local Execution

| Aspect | Local | Docker |
|--------|-------|--------|
| **Speed** | ⚡ Fastest | 🐢 Slower (container overhead) |
| **Consistency** | ⚠️ Varies by environment | ✅ Always consistent |
| **IDE Integration** | ✅ Full support | ⚠️ Requires configuration |
| **Debugging** | ✅ Native Xdebug | ⚠️ Network setup needed |
| **CI/CD Match** | ❌ May differ | ✅ Identical |
| **Team Sync** | ❌ Varies | ✅ Identical |
| **Clean State** | ❌ Cached configs | ✅ Fresh each run |

**When to Use Docker:**
- ✅ CI/CD pipelines (mandatory)
- ✅ Pre-commit final validation
- ✅ Testing specific PHP versions
- ✅ New team member onboarding
- ✅ Isolating global configuration

**When to Use Local:**
- ✅ Daily development
- ✅ IDE integration (debugging, intellisense)
- ✅ Fast iteration cycles
- ✅ Custom Xdebug configuration

---

## Docker Infrastructure

### Container Image
```bash
# Image details
Image:    kariricode/php-api-stack:dev
Base:     php:8.4-fpm-alpine
Size:     ~350MB
Registry: Docker Hub

# Included tools:
- PHP 8.4.14 (CLI + FPM)
- Composer 2.8.4
- Xdebug 3.x
- Git
- Make
- Common PHP extensions
```

### Volume Mounting
```bash
# Project directory mounted at:
Host:       $(PWD)
Container:  /var/www/html

# Live sync: Changes immediately visible in container
```

### Network Configuration
```bash
# Default: Host network mode
- No port mapping needed for basic commands
- Container can access host services

# Compose: Bridge network
- Isolated network for services
- See MAKEFILE-compose.md for details
```

---

## Docker Core Commands

### Interactive Shell

#### Open Bash Shell
```bash
make docker-shell

# Output:
# → Opening Docker shell (kariricode/php-api-stack:dev)...
# 
# root@abc123:/var/www/html#

# Inside container:
root@abc123:/var/www/html# php -v
PHP 8.4.14 (cli) (built: Jan 15 2025) (NTS)

root@abc123:/var/www/html# ls -la
total 120
drwxr-xr-x  8 root root  4096 Jan 15 10:30 .
drwxr-xr-x  1 root root  4096 Jan 15 10:20 ..
drwxr-xr-x  3 root root  4096 Jan 15 10:25 src
drwxr-xr-x  3 root root  4096 Jan 15 10:25 tests
-rw-r--r--  1 root root  2456 Jan 15 10:22 composer.json
...

root@abc123:/var/www/html# exit
```

#### Use Cases
```bash
# Explore environment
make docker-shell
> php -m              # Check modules
> php -i | grep xdebug # Check Xdebug
> composer --version   # Verify Composer

# Debug issues
make docker-shell
> ls -la vendor/       # Check dependencies
> php -l src/File.php  # Lint specific file

# Run custom commands
make docker-shell
> phpunit --filter testSpecificMethod
> php bin/console custom:command
```

### Composer Commands
```bash
# Generic Composer execution
make docker-composer CMD="<command>"

# Examples:

# Install dependencies
make docker-composer CMD="install"

# Update dependencies
make docker-composer CMD="update"

# Show installed packages
make docker-composer CMD="show --installed"

# Require new package
make docker-composer CMD="require kariricode/new-component"

# Remove package
make docker-composer CMD="remove vendor/package"

# Validate composer.json
make docker-composer CMD="validate --strict"

# Diagnose issues
make docker-composer CMD="diagnose"
```

### PHP Commands
```bash
# Generic PHP execution
make docker-php CMD="<command>"

# Examples:

# Check PHP version
make docker-php CMD="-v"

# Show PHP info
make docker-php CMD="-i"

# List loaded modules
make docker-php CMD="-m"

# Check configuration
make docker-php CMD="-i | grep xdebug"

# Execute script
make docker-php CMD="script.php"

# Evaluate expression
make docker-php CMD="-r 'echo PHP_VERSION;'"
```

---

## Docker QA Pipeline

### Individual QA Commands

All local QA commands have Docker equivalents:
```bash
# Testing
make docker-test                # All tests
make docker-test-unit           # Unit tests only
make docker-test-integration    # Integration tests

# Static Analysis
make docker-phpstan             # PHPStan
make docker-psalm               # Psalm
make docker-analyse             # All analysis

# Code Style
make docker-cs-check            # Check style
make docker-format              # Auto-fix style
make docker-lint                # Syntax check

# Coverage & Mutation
make docker-coverage            # Code coverage
make docker-mutation            # Mutation testing

# Benchmarks
make docker-bench               # Performance benchmarks
```

### Docker CI Pipelines

#### Fast CI in Docker
```bash
make docker-ci

# Executes in isolated container:
# ╔════════════════════════════════════════════════════════╗
# ║  Docker CI Pipeline (Isolated Environment)             ║
# ╚════════════════════════════════════════════════════════╝
# 
# → Running make ci in Docker...
# [Complete CI pipeline output...]
# ✓ Docker make ci complete
# 
# ✓ Docker CI pipeline completed

# Advantages:
# - No local config interference
# - Same environment as production
# - Clean state every run
```

#### Full CI in Docker
```bash
make docker-ci-full

# Complete quality validation in container:
# ╔════════════════════════════════════════════════════════╗
# ║  Docker Full CI Pipeline (Isolated Environment)        ║
# ╚════════════════════════════════════════════════════════╝
# 
# → Running make ci-full in Docker...
# [Full CI pipeline with coverage & mutation...]
# ✓ Docker make ci-full complete
# 
# ✓ Docker full CI pipeline completed
```

### Comparison: Local vs Docker CI
```bash
# Local CI (fast, uses your PHP config)
make ci
# Time: ~1-2 minutes
# Uses: Local PHP, local cache

# Docker CI (isolated, production-like)
make docker-ci
# Time: ~2-3 minutes
# Uses: Container PHP, no cache interference

# Use case:
# - Daily: make ci (fast feedback)
# - Pre-push: make docker-ci (final validation)
```

---

## Docker Image Management

### Pull Image
```bash
make docker-pull

# Downloads latest image from Docker Hub:
# → Pulling Docker image kariricode/php-api-stack:dev...
# dev: Pulling from kariricode/php-api-stack
# a1234567890b: Pull complete
# b2345678901c: Pull complete
# ...
# Status: Downloaded newer image for kariricode/php-api-stack:dev
# ✓ Docker image pulled
```

### Docker Environment Info
```bash
make docker-info

# Comprehensive environment details:
# ╔════════════════════════════════════════════════════════╗
# ║          Docker Environment Information                ║
# ╚════════════════════════════════════════════════════════╝
# 
# Docker Image:       kariricode/php-api-stack:dev
# Mount Point:        /home/user/project:/app
# 
# ╔════════════════════════════════════════════════════════╗
# ║              Container PHP Info                        ║
# ╚════════════════════════════════════════════════════════╝
# 
# PHP 8.4.14 (cli) (built: Jan 15 2025 12:34:56) (NTS)
# Copyright (c) The PHP Group
# Zend Engine v4.4.14
# 
# Composer version 2.8.4 2024-12-12
```

### Clean Docker Resources
```bash
make docker-clean

# Removes unused Docker resources:
# → Cleaning Docker resources...
# Deleted Containers:
# abc123def456
# 
# Deleted Images:
# untagged: old-image:tag
# 
# Total reclaimed space: 1.5GB
# ✓ Docker cleanup complete
```

---

## Docker Utility Tools

### Text Processing

#### JSON Processing (jq)
```bash
# Extract data from JSON files
make docker-jq CMD="'.version' composer.json"

# Output:
# → Running jq '.version' composer.json in Docker...
# "2.0.0"

# More examples:
make docker-jq CMD="'.require' composer.json"
make docker-jq CMD="'.authors[0].name' composer.json"
make docker-jq CMD="keys composer.json"
```

#### YAML Processing (yq)
```bash
# Parse YAML files
make docker-yq CMD="'.services' docker-compose.yml"

# Output:
# → Running yq '.services' docker-compose.yml in Docker...
# php:
#   image: kariricode/php-api-stack:dev
#   ...

# More examples:
make docker-yq CMD="'.services.php.image' docker-compose.yml"
```

### File Viewing & Editing

#### View Files (less)
```bash
# View file contents
make docker-less CMD="composer.json"

# Opens less viewer in container
# Press 'q' to quit
```

#### Edit Files (vim)
```bash
# Edit files with vim
make docker-vim CMD="src/Parser.php"

# Opens vim editor in container
# :wq to save and quit
# :q! to quit without saving
```

**Note**: File paths relative to `/var/www/html` (container working directory)

### System Diagnostics

#### List Open Files (lsof)
```bash
# Check open ports
make docker-lsof CMD="-i"

# Check specific port
make docker-lsof CMD="-i :8080"

# Check open files by process
make docker-lsof CMD="-p 1234"
```

**Requires**: `--cap-add=SYS_PTRACE` (automatically added by Makefile)

#### Trace System Calls (strace)
```bash
# Trace PHP execution
make docker-strace CMD="php -v"

# Output shows system calls:
# execve("/usr/local/bin/php", ["php", "-v"], ...)
# brk(NULL)
# ...

# Trace script execution
make docker-strace CMD="php script.php"
```

**Requires**: `--cap-add=SYS_PTRACE` (automatically added)

### Network Diagnostics

#### IP Configuration
```bash
# Show network interfaces
make docker-ip CMD="addr"

# Output:
# 1: lo: <LOOPBACK,UP> mtu 65536
#     inet 127.0.0.1/8
# 2: eth0: <BROADCAST,MULTICAST,UP> mtu 1500
#     inet 172.17.0.2/16

# Show routing table
make docker-ip CMD="route"
```

#### Network Connectivity (netcat)
```bash
# Test port connectivity
make docker-nc CMD="-vz localhost 9000"

# Output:
# localhost [127.0.0.1] 9000 (?) open

# Listen on port
make docker-nc CMD="-l 8080"

# Connect to service
make docker-nc CMD="redis 6379"
```

---

## Troubleshooting

### Issue 1: Container Exits Immediately

**Symptoms:**
```bash
$ make docker-shell
→ Opening Docker shell...
# (exits immediately)
```

**Diagnosis:**
```bash
# Check Docker daemon
docker ps

# Check image integrity
docker images | grep kariricode
```

**Solutions:**
```bash
# Pull fresh image
make docker-pull

# Verify image
docker run --rm kariricode/php-api-stack:dev php -v

# Check Docker daemon
sudo systemctl status docker
```

### Issue 2: Permission Denied Errors

**Symptoms:**
```bash
$ make docker-composer CMD="install"
Permission denied: composer.lock
```

**Cause:** User ID mismatch between host and container

**Solutions:**

**Option A: Fix Ownership**
```bash
# On host
sudo chown -R $USER:$USER .

# Or specific files
sudo chown $USER composer.lock
```

**Option B: Run as Non-root**
```bash
# Edit Makefile.docker-core.mk
DOCKER_RUN := docker run --rm \
    -v $(PWD):/var/www/html \
    -w /var/www/html \
    -u $(shell id -u):$(shell id -g) \  # Add this line
    kariricode/php-api-stack:dev
```

### Issue 3: Xdebug Not Working

**Symptoms:**
```bash
$ make docker-test
# Tests run but no coverage data
```

**Diagnosis:**
```bash
make docker-php CMD="-m | grep xdebug"
# If empty, Xdebug not loaded
```

**Solutions:**
```bash
# Check Xdebug configuration
make docker-shell
> php -i | grep xdebug.mode
# Should show: xdebug.mode => coverage

# If not, configure in docker-compose.yml
# See MAKEFILE-compose.md for Xdebug setup
```

### Issue 4: Slow Docker Performance

**Symptoms:**
- Commands take significantly longer in Docker
- File operations are slow

**Solutions:**

**Linux: Use Docker Native**
```bash
# Already optimal on Linux
# No additional configuration needed
```

**macOS: Optimize Volume Mounts**
```bash
# Use :delegated flag (already in Makefile)
-v $(PWD):/var/www/html:delegated

# Or use Docker volumes for vendor/
# docker volume create --name vendor-cache
-v vendor-cache:/var/www/html/vendor
```

**Windows: Enable WSL2**
```powershell
# Use WSL2 backend (faster than Hyper-V)
wsl --set-default-version 2
```

### Issue 5: Image Pull Fails

**Symptoms:**
```bash
$ make docker-pull
Error response from daemon: manifest not found
```

**Solutions:**
```bash
# Check image exists
docker search kariricode/php-api-stack

# Try explicit tag
docker pull kariricode/php-api-stack:dev

# Check Docker Hub status
curl -s https://status.docker.com/api/v2/status.json
```

---

## Best Practices

### 1. Pre-commit Workflow
```bash
# Fast local checks
make pre-commit

# Final validation in Docker (before push)
make docker-ci

# Catches environment-specific issues
```

### 2. CI/CD Strategy
```bash
# GitHub Actions / GitLab CI
# Always use Docker for consistency

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run CI in Docker
        run: make docker-ci
```

### 3. Team Onboarding
```bash
# New developer setup (no local PHP needed)
git clone https://github.com/org/repo.git
cd repo

# Pull image
make docker-pull

# Install dependencies in container
make docker-composer CMD="install"

# Run tests
make docker-test

# ✅ Ready to develop!
```

### 4. Debugging in Docker
```bash
# Interactive debugging session
make docker-shell

# Inside container:
root@abc:/var/www/html# php -d xdebug.mode=debug \
  -d xdebug.start_with_request=yes \
  vendor/bin/phpunit --filter testMethod

# Configure IDE to listen on host:9003
```

### 5. Cache Optimization
```bash
# Use Docker volumes for vendor/
# Speeds up repeated container starts

# Create volume
docker volume create composer-cache

# Use in Makefile
DOCKER_RUN := docker run --rm \
  -v $(PWD):/var/www/html \
  -v composer-cache:/var/www/html/vendor \
  kariricode/php-api-stack:dev
```

### 6. Resource Limits
```bash
# Prevent runaway containers

# Limit memory
DOCKER_RUN := docker run --rm \
  -v $(PWD):/var/www/html \
  --memory="1g" \
  --memory-swap="1g" \
  kariricode/php-api-stack:dev

# Limit CPU
DOCKER_RUN := docker run --rm \
  -v $(PWD):/var/www/html \
  --cpus="2.0" \
  kariricode/php-api-stack:dev
```

---

## Command Reference

### Core Commands
```bash
make docker-shell               # Interactive bash shell
make docker-composer CMD="..."  # Run Composer command
make docker-php CMD="..."       # Run PHP command
```

### QA Commands
```bash
make docker-test                # Run tests
make docker-test-unit           # Unit tests
make docker-phpstan             # Static analysis
make docker-psalm               # Type checking
make docker-analyse             # All analysis
make docker-cs-check            # Code style check
make docker-format              # Auto-fix style
make docker-lint                # Syntax check
make docker-coverage            # Code coverage
make docker-bench               # Benchmarks
```

### CI Pipelines
```bash
make docker-ci                  # Fast CI pipeline
make docker-ci-full             # Full CI pipeline
```

### Image Management
```bash
make docker-pull                # Pull latest image
make docker-info                # Show environment info
make docker-clean               # Clean unused resources
```

### Utility Tools
```bash
make docker-jq CMD="..."        # JSON processing
make docker-yq CMD="..."        # YAML processing
make docker-less CMD="..."      # View files
make docker-vim CMD="..."       # Edit files
make docker-lsof CMD="..."      # List open files
make docker-strace CMD="..."    # Trace system calls
make docker-ip CMD="..."        # Network config
make docker-nc CMD="..."        # Network connectivity
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║              Docker Commands Quick Reference              ║
╠═══════════════════════════════════════════════════════════╣
║ SHELL        │ make docker-shell                          ║
║ COMPOSER     │ make docker-composer CMD="install"         ║
║ PHP          │ make docker-php CMD="-v"                   ║
║──────────────┼────────────────────────────────────────────║
║ TEST         │ make docker-test                           ║
║ ANALYSE      │ make docker-analyse                        ║
║ FORMAT       │ make docker-format                         ║
║ COVERAGE     │ make docker-coverage                       ║
║──────────────┼────────────────────────────────────────────║
║ FAST CI      │ make docker-ci                             ║
║ FULL CI      │ make docker-ci-full                        ║
║──────────────┼────────────────────────────────────────────║
║ PULL IMAGE   │ make docker-pull                           ║
║ INFO         │ make docker-info                           ║
║ CLEAN        │ make docker-clean                          ║
╚═══════════════════════════════════════════════════════════╝

When to Use Docker:
  ✅ CI/CD pipelines (always)
  ✅ Pre-push validation
  ✅ Team consistency
  ✅ Testing specific PHP versions
  
When to Use Local:
  ✅ Daily development
  ✅ Fast iteration
  ✅ IDE integration
```

---

**Version**: 1.0.0  
**Modules**: `Makefile.docker-*.mk` (except `docker-compose.mk`)  
  
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
**Note**: For Docker Compose full-stack environment management, see [MAKEFILE-compose.md](MAKEFILE-compose.md)
```
