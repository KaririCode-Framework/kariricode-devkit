<div align="center">

# Docker Compose Management

[![KaririCode](https://img.shields.io/badge/KaririCode-DevKit-6D00CC?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)
[![Docker Compose](https://img.shields.io/badge/Docker%20Compose-Full%20Stack-2496ED?style=for-the-badge&logo=docker)](https://docs.docker.com/compose/)
[![Services](https://img.shields.io/badge/Services-PHP%20%7C%20Redis%20%7C%20Memcached-00C853?style=for-the-badge)](https://github.com/KaririCode-Framework/kariricode-devkit)

**kariricode/devkit** - Professional development environment for KaririCode Framework

Part of the [KaririCode Framework](https://kariricode.org) ecosystem

[Main Documentation](MAKEFILE.md) | [GitHub Repository](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Environment Setup](#environment-setup)
3. [Lifecycle Management](#lifecycle-management)
4. [Service Monitoring](#service-monitoring)
5. [Container Interaction](#container-interaction)
6. [Configuration Management](#configuration-management)
7. [Troubleshooting](#troubleshooting)
8. [Advanced Workflows](#advanced-workflows)
9. [Production Considerations](#production-considerations)

---

## Overview

### Architecture
```
┌─────────────────────────────────────────────┐
│  Docker Compose Stack                       │
├─────────────────────────────────────────────┤
│                                             │
│  ┌─────────────┐      ┌──────────────┐    │
│  │ PHP-FPM +   │◄────►│  Memcached   │    │
│  │  Nginx +    │      │  (11211)     │    │
│  │  Redis      │      └──────────────┘    │
│  │  (80, 6379) │                           │
│  └─────────────┘                           │
│        │                                    │
│        │  Volume Mount                     │
│        ▼                                    │
│  /var/www/html ◄────► ./                  │
│                                             │
└─────────────────────────────────────────────┘
         │
         │ Bridge Network
         │ kariricode_network
         │
    ┌────▼────┐
    │  Host   │
    │ Machine │
    └─────────┘
```

### Services

| Service | Image | Purpose | Ports | Health Check |
|---------|-------|---------|-------|--------------|
| **php** | kariricode/php-api-stack:dev | Application runtime | 80, 6379 | Process check |
| **memcached** | memcached:1.6-alpine | Memory caching | 11211 | netcat probe |

### Features

✅ **Live Code Sync**: Volume mounting for instant updates  
✅ **Isolated Network**: Bridge network for service communication  
✅ **Health Monitoring**: Built-in health checks  
✅ **Xdebug Support**: Configurable debugging  
✅ **Auto-restart**: Service recovery on failure  
✅ **Environment Variables**: Flexible configuration via `.env`

---

## Environment Setup

### Initial Configuration

#### Step 1: Create Environment File
```bash
# Auto-created on first 'make up', or manually:
cp .env.example .env
```

#### Step 2: Configure Variables
```bash
# Edit .env
nano .env
```

**Key Configuration Variables:**
```bash
# Application
APP_NAME=kariricode-devkit
APP_ENV=development
APP_DEBUG=true
APP_PORT=8089                    # Host port → container:80

# Demo & Health Check
DEMO_MODE=false
HEALTH_CHECK_INSTALL=true

# Cache Services
REDIS_PORT=63777                 # Host port → container:6379
MEMCACHED_PORT=11210             # Host port → container:11211

# Xdebug
XDEBUG_MODE=off                  # off/debug/coverage/profile
XDEBUG_CLIENT_HOST=host.docker.internal

# Composer
COMPOSER_MEMORY_LIMIT=-1
```

#### Step 3: Verify Configuration
```bash
# Check .env file
make env-check

# Output:
# ╔════════════════════════════════════════════════════════╗
# ✓ .env file exists
# APP_NAME=kariricode-devkit
# APP_PORT=8089
# REDIS_PORT=63777
# ...
# ╚════════════════════════════════════════════════════════╝
```

### Port Conflict Resolution

**Problem:** Port already in use
```bash
# 1. Identify conflicting process
sudo lsof -i :8089
# COMMAND   PID  USER
# nginx    1234  root

# 2. Option A: Stop conflicting service
sudo systemctl stop nginx

# 3. Option B: Change port in .env
echo "APP_PORT=8090" >> .env

# 4. Restart
make down && make up
```

**Common Port Conflicts:**

| Port | Service | Solution |
|------|---------|----------|
| 8089 | Nginx/Apache | Change `APP_PORT` |
| 6379 | Redis | Change `REDIS_PORT` |
| 11211 | Memcached | Change `MEMCACHED_PORT` |

---

## Lifecycle Management

### Starting Services

#### Standard Start
```bash
# Start all services in detached mode
make up

# What happens:
# 1. Checks for .env (creates if missing)
# 2. Pulls images if needed
# 3. Creates network
# 4. Starts containers
# 5. Shows status

# Output:
# ✓ Services started
# ╔════════════════════════════════════════════════════════╗
# NAME                          STATUS         PORTS
# kariricode-devkit_php         Up 5 seconds   0.0.0.0:8089->80/tcp
# kariricode-devkit_memcached   Up 5 seconds   0.0.0.0:11210->11211/tcp
# ╚════════════════════════════════════════════════════════╝
```

#### Start with Build
```bash
# Force rebuild images before starting
make up-build

# Use cases:
# - Dockerfile changes
# - Base image updates
# - Fresh start needed
```

### Stopping Services
```bash
# Stop and remove containers (preserves volumes)
make down
# ✓ Services stopped and removed

# Stop without removing
make stop
# Containers remain, can be started with 'make start'

# Start existing stopped containers
make start
```

### Restarting Services
```bash
# Restart all services
make restart

# Equivalent to:
# docker compose restart

# Use cases:
# - Configuration changes in docker-compose.yml
# - Memory leaks
# - Service recovery
# - .env changes (some variables)
```

### Complete Rebuild
```bash
# Nuclear option: destroy and recreate everything
make rebuild

# Executes:
# 1. make down           → Stop and remove containers
# 2. make clean-volumes  → Delete ALL volumes
# 3. make up-build       → Rebuild images and start

# ⚠️  WARNING: This destroys ALL data!
# Are you sure? [y/N]
```

---

## Service Monitoring

### Status Checks

#### Current Status
```bash
# Show service status
make status
make ps  # Alias

# Output:
# ╔════════════════════════════════════════════════════════╗
# NAME                          STATE    STATUS
# kariricode-devkit_php         Up       healthy
# kariricode-devkit_memcached   Up       healthy
# ╚════════════════════════════════════════════════════════╝
```

#### Health Checks
```bash
# Detailed health status
make health

# Output shows:
# kariricode-devkit_php: healthy - Up 2 minutes
# kariricode-devkit_memcached: healthy - Up 2 minutes

# Health check definitions:
# - Memcached: nc -z 127.0.0.1 11211 (every 10s)
# - PHP: Process running check
```

### Logs

#### View Logs
```bash
# All services
make logs

# Specific service
make logs SERVICE=php
make logs SERVICE=memcached

# Example output:
# kariricode-devkit_php         | [15-Jan-2025 10:30:00] NOTICE: fpm is running
# kariricode-devkit_memcached   | <5 new connections
```

#### Follow Logs (Real-time)
```bash
# Tail all logs
make logs-follow

# Tail specific service
make logs-follow SERVICE=php

# Press Ctrl+C to stop following
```

**Log Use Cases:**

| Scenario | Command | What to Look For |
|----------|---------|------------------|
| Application errors | `make logs-follow SERVICE=php` | PHP errors, warnings |
| Service crashes | `make logs` | Exit codes, error messages |
| Performance issues | `make logs SERVICE=php` | Slow query logs, timeouts |
| Cache debugging | `make logs SERVICE=memcached` | Connection stats, evictions |

### Port Mappings
```bash
# Show exposed ports
make ports

# Output:
# ╔════════════════════════════════════════════════════════╗
# NAME                 PORTS
# *_php               0.0.0.0:8089->80/tcp, :::6379->6379/tcp
# *_memcached         0.0.0.0:11210->11211/tcp
# ╚════════════════════════════════════════════════════════╝
```

**Access Services:**
```bash
# Application HTTP
curl http://localhost:8089

# Redis (from host, if exposed)
redis-cli -h localhost -p 63777

# Memcached (from host)
echo "stats" | nc localhost 11210
```

---

## Container Interaction

### PHP Container

#### Interactive Shell
```bash
# Open bash shell
make exec-php
make shell  # Alias

# Inside container:
root@abc123:/var/www/html# php -v
root@abc123:/var/www/html# composer --version
root@abc123:/var/www/html# ls -la
```

#### Execute Commands
```bash
# Single command execution
make exec-php CMD="php -v"
# PHP 8.4.14 (cli) (built: Jan 15 2025) (NTS)

make exec-php CMD="composer --version"
# Composer version 2.8.4

make exec-php CMD="php artisan migrate"
make exec-php CMD="./bin/console cache:clear"
```

**Common Tasks:**
```bash
# Check PHP modules
make exec-php CMD="php -m"

# Test Redis connection
make exec-php CMD="php -r 'new Redis();'"

# View logs
make exec-php CMD="tail -f var/log/app.log"

# Run application commands
make exec-php CMD="make test"
```

### Memcached Container
```bash
# Connect to Memcached container
make exec-memcached

# Inside container:
/ # echo "stats" | nc localhost 11211
STAT pid 1
STAT uptime 3600
STAT curr_connections 5
...

/ # echo "flush_all" | nc localhost 11211
OK
```

---

## Configuration Management

### Validate Configuration

#### Syntax Validation
```bash
# Check docker-compose.yml syntax
make validate-compose

# Output:
# ✓ docker-compose.yml is valid

# Errors show line numbers and details
```

#### View Resolved Configuration
```bash
# Show merged configuration
make config

# Output includes:
# - Environment variable substitution
# - Service definitions
# - Network configuration
# - Volume mounts
# - All resolved values

# Useful for debugging:
# - Environment variable issues
# - Configuration inheritance
# - Overrides from .env
```

### Environment Verification
```bash
# Check .env file status
make env-check

# Shows:
# 1. File existence
# 2. First 20 variables
# 3. Validation warnings

# If missing:
# ✗ .env file not found
#   Run: cp .env.example .env
```

### Network Inspection
```bash
# Inspect Docker network
make network-inspect

# Output:
# ╔════════════════════════════════════════════════════════╗
# [
#   {
#     "Name": "kariricode-devkit_network",
#     "Driver": "bridge",
#     "Scope": "local",
#     "Subnet": "172.20.0.0/16",
#     "Gateway": "172.20.0.1",
#     "Containers": {
#       "php": {"IPv4Address": "172.20.0.2"},
#       "memcached": {"IPv4Address": "172.20.0.3"}
#     }
#   }
# ]
# ╚════════════════════════════════════════════════════════╝
```

---

## Composer Integration

### Install Dependencies
```bash
# Install from composer.lock
make composer-install

# Executes in PHP container:
# composer install --no-interaction --prefer-dist --optimize-autoloader
```

### Update Dependencies
```bash
# Update all dependencies
make composer-update

# Executes:
# composer update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader
```

**Advantages of Container Execution:**
- ✅ No local PHP version conflicts
- ✅ Consistent dependencies across team
- ✅ Matches production environment
- ✅ Isolated from host system

---

## Troubleshooting

### Issue 1: Services Won't Start

**Symptoms:**
- Container exits immediately
- `make up` fails

**Diagnosis:**
```bash
# Check logs
make logs

# Common error messages:
# - "Address already in use" → Port conflict
# - "Invalid reference format" → .env syntax error
# - "Pull access denied" → Image not found
```

**Solutions:**
```bash
# Port conflict
echo "APP_PORT=8090" >> .env
make down && make up

# Invalid .env
make env-check
# Fix syntax errors

# Missing image
make docker-pull
```

### Issue 2: Can't Connect to Services

**Symptoms:**
- `Connection refused`
- `curl: (7) Failed to connect`

**Diagnosis:**
```bash
# Check if services are running
make status

# Check ports
make ports

# Check network
make network-inspect
```

**Solutions:**
```bash
# Service not running
make restart

# Wrong port
curl http://localhost:$(grep APP_PORT .env | cut -d= -f2)

# Network issue
make down
docker network prune
make up
```

### Issue 3: Permission Denied

**Symptoms:**
- `mkdir: cannot create directory: Permission denied`
- `composer: Failed to download`

**Diagnosis:**
```bash
# Check file ownership
make exec-php CMD="ls -la /var/www/html"
```

**Solution:**
```bash
# Fix ownership on host
sudo chown -R $USER:$USER .

# Or fix inside container
make exec-php CMD="chown -R www-data:www-data /var/www/html"

# Restart
make restart
```

### Issue 4: Volume Data Corruption

**Symptoms:**
- Stale data
- Inconsistent state
- Old files persist

**Solution:**
```bash
# Nuclear option: destroy volumes
make clean-volumes

# ⚠️  WARNING: Confirmation required
# This will delete ALL volume data!
# Are you sure? [y/N] y

# ✓ Volumes removed

# Rebuild
make up-build
```

### Issue 5: Xdebug Not Working

**Diagnosis:**
```bash
# Check Xdebug is loaded
make exec-php CMD="php -v"
# Should show: with Xdebug v3.x

# Check mode
make exec-php CMD="php -r 'echo ini_get(\"xdebug.mode\");'"
```

**Solution:**
```bash
# Enable in .env
sed -i 's/XDEBUG_MODE=off/XDEBUG_MODE=debug/' .env

# Restart services
make restart

# Verify
make exec-php CMD="php -m | grep xdebug"
```

---

## Advanced Workflows

### Development with Live Reload
```bash
# 1. Start services
make up

# 2. Open separate terminal for logs
make logs-follow SERVICE=php

# 3. Edit code in your IDE
# Changes are immediately visible (volume mount)

# 4. Test changes
curl http://localhost:8089/health

# 5. No restart needed!
```

### Multi-Environment Configuration

#### Development Environment
```bash
# Use .env.development
cp .env.development .env
make up

# Characteristics:
# - XDEBUG_MODE=debug
# - APP_DEBUG=true
# - Verbose logging
```

#### Testing Environment
```bash
# Use .env.testing
cp .env.testing .env
make rebuild

# Characteristics:
# - XDEBUG_MODE=coverage
# - Clean state
# - Test database
```

#### Production Simulation
```bash
# Use .env.production
cp .env.production .env
make up-build

# Characteristics:
# - XDEBUG_MODE=off
# - APP_DEBUG=false
# - Optimized settings
```

### Debugging Workflows

#### Xdebug with IDE

**Step 1: Configure .env**
```bash
echo "XDEBUG_MODE=debug" > .env
echo "XDEBUG_CLIENT_HOST=host.docker.internal" >> .env
make restart
```

**Step 2: IDE Configuration (PHPStorm)**
```
Settings → PHP → Servers:
- Name: kariricode-devkit
- Host: localhost
- Port: 8089
- Path mappings: ./  → /var/www/html
```

**Step 3: Start Listening**
```
PHPStorm: Run → Start Listening for PHP Debug Connections
```

**Step 4: Trigger Breakpoint**
```bash
curl http://localhost:8089/debug
```

#### Performance Profiling
```bash
# Enable profiling
sed -i 's/XDEBUG_MODE=off/XDEBUG_MODE=profile/' .env
make restart

# Run application
curl http://localhost:8089/api/endpoint

# View profile files
make exec-php CMD="ls -lh /tmp/xdebug"
# cachegrind.out.XXXX files

# Download for analysis
docker cp kariricode-devkit_php:/tmp/xdebug/cachegrind.out.1234 ./
```

**Analyze with:**
- **KCacheGrind** (Linux)
- **QCacheGrind** (macOS/Windows)
- **Webgrind** (Web-based)

### Integration Testing Workflow
```bash
# 1. Start full stack
make up

# 2. Wait for services
sleep 3

# 3. Run integration tests
make test-compose

# Workflow in make test-compose:
# - Verifies services are up
# - Executes: docker compose exec php make test
# - Returns test exit code

# 4. View logs on failure
make logs

# 5. Cleanup
make down
```

---

## Production Considerations

### ⚠️ Development Only

This Docker Compose setup is **NOT production-ready**. It's designed for:
- ✅ Local development
- ✅ Integration testing
- ✅ Rapid prototyping
- ✅ Team onboarding

### Production Limitations

| Concern | Development Setup | Production Requirement |
|---------|-------------------|------------------------|
| **SSL/TLS** | No HTTPS | Terminate SSL at load balancer |
| **Secrets** | .env file | Vault/Secrets Manager |
| **Scaling** | Single container | Horizontal scaling, orchestration |
| **Monitoring** | Docker logs | Prometheus, Grafana, ELK |
| **High Availability** | No redundancy | Multi-node, failover |
| **Resource Limits** | Unlimited | CPU/Memory constraints |
| **Backup** | No strategy | Automated backups, replication |
| **Security** | Debug enabled | Hardened images, least privilege |

### Production Recommendations

**For Production Deployment:**

1. **Use Orchestration Platforms**
```
   - Kubernetes (EKS, GKE, AKS)
   - Docker Swarm (simple cases)
   - AWS ECS/Fargate
```

2. **Separate Stateful Services**
```
   - Managed Redis (AWS ElastiCache, Redis Cloud)
   - Managed Memcached
   - External databases
```

3. **Implement Security Best Practices**
```
   - Run as non-root user
   - Scan images for vulnerabilities
   - Use minimal base images (Alpine)
   - Regular security updates
```

4. **Add Monitoring & Logging**
```
   - Centralized logging (ELK, Loki)
   - Metrics collection (Prometheus)
   - Distributed tracing (Jaeger)
   - Alerting (PagerDuty, Opsgenie)
```

5. **Implement CI/CD**
```
   - Automated testing
   - Image scanning
   - Deployment automation
   - Rollback capabilities
```

---

## CI/CD Integration

### GitHub Actions Example
```yaml
name: Integration Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Create .env
        run: cp .env.example .env
      
      - name: Start Docker Compose
        run: make up
      
      - name: Wait for services
        run: sleep 5
      
      - name: Run tests
        run: make test-compose
      
      - name: Show logs on failure
        if: failure()
        run: make logs
      
      - name: Cleanup
        if: always()
        run: make down
```

### GitLab CI Example
```yaml
integration-tests:
  stage: test
  image: docker:latest
  services:
    - docker:dind
  
  variables:
    DOCKER_DRIVER: overlay2
  
  before_script:
    - apk add --no-cache make
    - cp .env.example .env
    - make up
  
  script:
    - make test-compose
  
  after_script:
    - make logs
    - make down
  
  artifacts:
    when: always
    reports:
      junit: build/reports/junit.xml
    paths:
      - coverage/
```

---

## Command Reference

### Lifecycle
```bash
make up                 # Start services
make up-build           # Start with build
make down               # Stop and remove
make stop               # Stop (preserve containers)
make start              # Start existing containers
make restart            # Restart services
make rebuild            # Complete rebuild
```

### Monitoring
```bash
make status             # Service status
make ps                 # Alias for status
make health             # Health checks
make logs               # View logs
make logs-follow        # Follow logs (real-time)
make ports              # Show port mappings
```

### Interaction
```bash
make exec-php           # PHP shell
make shell              # Alias for exec-php
make exec-memcached     # Memcached shell
make composer-install   # Install dependencies
make composer-update    # Update dependencies
make test-compose       # Run tests in compose
```

### Configuration
```bash
make config             # View resolved config
make validate-compose   # Validate syntax
make env-check          # Check .env file
make network-inspect    # Inspect network
make inspect-php        # PHP container details
```

### Cleanup
```bash
make prune              # Remove unused resources
make clean-volumes      # Delete volumes (destructive)
```

---

## Best Practices

### 1. Use .env for Configuration
```bash
# ❌ Bad: Hardcode in docker-compose.yml
ports:
  - "8089:80"

# ✅ Good: Use environment variables
ports:
  - "${APP_PORT:-8089}:80"
```

### 2. Clean Up Regularly
```bash
# Weekly cleanup
make prune

# Before major changes
make rebuild
```

### 3. Monitor Health
```bash
# Add to daily routine
make health

# Investigate unhealthy services immediately
make logs SERVICE=<unhealthy-service>
```

### 4. Version Control
```bash
# ✅ Commit
- docker-compose.yml
- .env.example
- Makefile

# ❌ Don't commit
- .env
- Volumes
- Build artifacts
```

### 5. Document Custom Changes
```yaml
# docker-compose.yml
services:
  php:
    # Custom: Added for X feature (see issue #123)
    environment:
      CUSTOM_VAR: value
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║                Docker Compose Quick Reference             ║
╠═══════════════════════════════════════════════════════════╣
║ START        │ make up                                    ║
║ STOP         │ make down                                  ║
║ RESTART      │ make restart                               ║
║ REBUILD      │ make rebuild                               ║
║──────────────┼────────────────────────────────────────────║
║ STATUS       │ make status                                ║
║ LOGS         │ make logs-follow                           ║
║ HEALTH       │ make health                                ║
║──────────────┼────────────────────────────────────────────║
║ SHELL        │ make shell                                 ║
║ COMMAND      │ make exec-php CMD="php -v"                 ║
║ COMPOSE TEST │ make test-compose                          ║
║──────────────┼────────────────────────────────────────────║
║ VALIDATE     │ make validate-compose                      ║
║ CHECK ENV    │ make env-check                             ║
║ INSPECT      │ make inspect-php                           ║
╚═══════════════════════════════════════════════════════════╝
```

---

**Version**: 1.0.0  
**Module**: `Makefile.docker-compose.mk`   
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
