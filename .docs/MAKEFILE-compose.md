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
3. [Port Conflict Detection & Resolution](#port-conflict-detection--resolution)
4. [Lifecycle Management](#lifecycle-management)
5. [Service Monitoring](#service-monitoring)
6. [Container Interaction](#container-interaction)
7. [Configuration Management](#configuration-management)
8. [Troubleshooting](#troubleshooting)
9. [Advanced Workflows](#advanced-workflows)
10. [Production Considerations](#production-considerations)

---

## Overview

### Architecture
```
┌───────────────────────────────────────────┐
│  Docker Compose Stack                     │
├───────────────────────────────────────────┤
│                                           │
│  ┌─────────────┐      ┌──────────────┐   │
│  │ PHP-FPM +   │◄────►│  Memcached   │   │
│  │  Nginx +    │      │  (11211)     │   │
│  │  Redis      │      └──────────────┘   │
│  │  (80, 6379) │                          │
│  └─────────────┘                          │
│        │                                   │
│        │  Volume Mount                    │
│        ▼                                   │
│  /var/www/html ◄────► ./                 │
│                                           │
└───────────────────────────────────────────┘
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
✅ **Port Conflict Detection**: Automatic detection and resolution

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
REDIS_PORT=6379                  # Host port → container:6379
MEMCACHED_PORT=11211             # Host port → container:11211

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
# REDIS_PORT=6379
# ...
# ╚════════════════════════════════════════════════════════╝
```

---

## Port Conflict Detection & Resolution

### Overview

**The Problem**: Port conflicts prevent Docker containers from starting, causing cryptic error messages like:
```
Error: failed to bind host port for 0.0.0.0:11211:172.20.0.2:11211/tcp: address already in use
```

**The Solution**: Automated detection and resolution tools that identify conflicting processes and free up ports.

---

### Quick Start

#### Safe Startup (Recommended)
```bash
# Automatically checks ports before starting
make up-safe

# Workflow:
# 1. Cleans orphaned Docker containers
# 2. Checks for Docker port conflicts
# 3. Checks for system port conflicts
# 4. Starts services if all clear
# 5. Shows status

# If conflicts detected:
# ✗ Port 6379 in use by system process PID 1234 (redis-server)
# 
# Resolution options:
#   1. Run: make diagnose-ports for detailed info
#   2. Run: make fix-ports to auto-resolve
#   3. Run: make kill-port PORT=6379 for specific port
```

---

### Port Conflict Commands

#### 1. Check Ports
```bash
# Quick check for conflicts
make check-ports

# What it does:
# 1. Cleans orphaned Docker containers
# 2. Checks Docker containers for port usage
# 3. Checks system processes for port usage
# 4. Reports status

# Success output:
# → Checking system ports for conflicts...
# ✓ Port 8089 is available
# ✓ Port 6379 is available
# ✓ Port 11211 is available
# ✓ All ports available

# Failure output:
# ✗ Port 6379 in use by system process PID 1234 (redis-server)
# 
# Resolution options:
#   1. Run: make diagnose-ports for detailed info
#   2. Run: make fix-ports to auto-resolve
#   3. Run: make kill-port PORT=6379 for specific port
```

#### 2. Diagnose Ports
```bash
# Detailed port conflict analysis
make diagnose-ports

# Output example:
# Port Conflict Diagnosis
# ╔════════════════════════════════════════════════════════╗
# 
# Required Ports:
#   APP_PORT:        8089
#   REDIS_PORT:      6379
#   MEMCACHED_PORT:  11211
# 
# System Port Status:
# 
# Port 8089:
#   Status: AVAILABLE
# 
# Port 6379:
#   PID: 1234 | Command: redis-server | User: redis
#   Status: IN USE (lsof)
#   To kill: make kill-port PORT=6379
#   Or stop service: sudo systemctl stop redis
# 
# Port 11211:
#   PID: 5678 | Command: memcached | User: memcache
#   Status: IN USE (ss)
#   To kill: make kill-port PORT=11211
#   Or stop service: sudo systemctl stop memcached
# 
# Docker Containers (All):
#   None found
# 
# ╚════════════════════════════════════════════════════════╝
# 
# Suggested Actions:
#   1. make fix-ports - Auto-kill conflicting processes
#   2. make kill-port PORT=<port> - Kill specific process
#   3. sudo systemctl stop redis - Stop Redis service
#   4. sudo systemctl stop memcached - Stop Memcached service
#   5. Edit .env to use different ports:
#      REDIS_PORT=6380
#      MEMCACHED_PORT=11212
```

#### 3. Fix Ports (Automatic Resolution)
```bash
# Interactive automatic fix
make fix-ports

# Workflow:
# ⚠  This will attempt to free up conflicting ports
#    Docker containers and system processes will be terminated
# 
# Continue? [y/N] y
# 
# → Cleaning orphaned Docker resources...
# ✓ Docker cleanup complete
# 
# → Scanning and fixing system port conflicts...
# → Terminating redis-server (PID 1234) on port 6379...
# ✓ Port 6379 freed
# → Terminating memcached (PID 5678) on port 11211...
# ✓ Port 11211 freed
# 
# → Checking system ports for conflicts...
# ✓ Port 8089 is available
# ✓ Port 6379 is available
# ✓ Port 11211 is available
# ✓ All ports available
```

**What `fix-ports` does:**
1. Cleans orphaned Docker containers
2. Identifies processes using required ports
3. Attempts graceful shutdown (SIGTERM)
4. Forces shutdown if needed (SIGKILL)
5. Verifies ports are freed
6. Re-checks all ports

**Safety Features:**
- ✅ Interactive confirmation required
- ✅ Graceful shutdown first (15 seconds wait)
- ✅ Force only if graceful fails
- ✅ Verifies success after each action
- ✅ Final check confirms all ports clear

#### 4. Kill Specific Port
```bash
# Kill process on specific port
make kill-port PORT=6379

# Workflow:
# → Checking port 6379...
# Found process: PID 1234 (redis-server)
# Attempting graceful shutdown...
# ✓ Port 6379 is now free

# For stubborn processes:
# Process didn't stop, forcing shutdown...
# ✓ Port 6379 is now free
```

**Use Cases:**
- Kill specific conflicting process
- Don't want to touch other ports
- Selective cleanup

#### 5. Port Scanner
```bash
# Scan common ports for conflicts
make port-scan

# Output:
# Port Scanner
# ╔════════════════════════════════════════════════════════╗
# 
# ✓ Port 80: Available
# ✗ Port 443: IN USE (PID 890 - nginx)
# ✓ Port 3000: Available
# ✗ Port 3306: IN USE (PID 1122 - mysqld)
# ✓ Port 5432: Available
# ✗ Port 6379: IN USE (PID 1234 - redis-server)
# ✓ Port 8000: Available
# ✓ Port 8080: Available
# ✓ Port 8089: Available
# ✓ Port 9000: Available
# ✗ Port 11211: IN USE (PID 5678 - memcached)
# ✓ Port 27017: Available
# 
# ╚════════════════════════════════════════════════════════╝
```

**Use Cases:**
- Quick overview of port availability
- Planning port assignments
- Identifying system services
- Troubleshooting network issues

#### 6. Clean Docker Resources
```bash
# Remove orphaned containers and networks
make clean-docker

# Output:
# → Cleaning orphaned Docker resources...
#   Removing stopped containers...
# Deleted Containers:
# a9d5c257ff5f
# 34704960e194
# 
#   Removing unused networks...
# Deleted Networks:
# kariricode-devkit_network
# 
# ✓ Docker cleanup complete
```

**When to use:**
- Before starting services
- After failed `docker compose up`
- When seeing "address already in use" errors
- Regular maintenance

---

### Port Conflict Workflows

#### Workflow 1: First Time Setup
```bash
# 1. Clone repository
git clone https://github.com/KaririCode-Framework/kariricode-devkit.git
cd kariricode-devkit

# 2. Create .env
cp .env.example .env

# 3. Check for conflicts
make check-ports

# If conflicts found:
# 4. View detailed info
make diagnose-ports

# 5. Resolve conflicts
make fix-ports

# 6. Start services safely
make up-safe
```

#### Workflow 2: Port Already in Use Error
```bash
# Scenario: You tried 'make up' and got:
# Error: address already in use

# 1. Diagnose the problem
make diagnose-ports

# 2. Option A: Kill conflicting processes
make fix-ports

# 3. Option B: Change ports in .env
nano .env
# Change: REDIS_PORT=6380
#         MEMCACHED_PORT=11212

# 4. Retry startup
make up-safe
```

#### Workflow 3: System Service Conflicts
```bash
# Scenario: Local Redis/Memcached running

# 1. Identify services
make diagnose-ports
# → Redis (6379): PID 1234
# → Memcached (11211): PID 5678

# 2. Option A: Stop system services
sudo systemctl stop redis
sudo systemctl stop memcached

# 3. Option B: Disable autostart
sudo systemctl disable redis
sudo systemctl disable memcached

# 4. Option C: Use different ports
echo "REDIS_PORT=6380" >> .env
echo "MEMCACHED_PORT=11212" >> .env

# 5. Start Docker services
make up-safe
```

#### Workflow 4: Orphaned Docker Containers
```bash
# Scenario: Previous containers not cleaned up

# 1. Clean Docker resources
make clean-docker

# 2. Verify cleanup
docker ps -a
# Should show no kariricode-devkit containers

# 3. Check ports again
make check-ports

# 4. Start fresh
make up
```

#### Workflow 5: Multiple Developers
```bash
# Scenario: Different developers, different ports

# Developer A (.env)
APP_PORT=8089
REDIS_PORT=6379
MEMCACHED_PORT=11211

# Developer B (.env) - all different
APP_PORT=8090
REDIS_PORT=6380
MEMCACHED_PORT=11212

# Each developer:
make check-ports  # Verify their ports are free
make up-safe      # Start with their configuration
```

---

### Common Port Conflict Scenarios

#### Scenario 1: Local Redis Running
**Problem:**
```
✗ Port 6379 in use by system process PID 1234 (redis-server)
```

**Solutions:**

**Option A: Stop system Redis**
```bash
# Temporary stop
sudo systemctl stop redis

# Permanent disable
sudo systemctl disable redis

# Start Docker services
make up-safe
```

**Option B: Use different port**
```bash
# Change Docker port
echo "REDIS_PORT=6380" >> .env

# Restart
make up-safe

# Access Redis on new port
redis-cli -h localhost -p 6380
```

**Option C: Kill process**
```bash
make kill-port PORT=6379
make up-safe
```

#### Scenario 2: Local Memcached Running
**Problem:**
```
✗ Port 11211 in use by system process PID 5678 (memcached)
```

**Solutions:**

**Option A: Stop system Memcached**
```bash
sudo systemctl stop memcached
make up-safe
```

**Option B: Change port**
```bash
echo "MEMCACHED_PORT=11212" >> .env
make up-safe
```

#### Scenario 3: Orphaned Docker Containers
**Problem:**
```
Error: failed to bind host port for 0.0.0.0:11211
✓ Port 11211 is available (via lsof)  # Confusing!
```

**Cause:** Docker containers in "Created" or "Exited" state still hold port bindings

**Solution:**
```bash
# Clean orphaned containers
make clean-docker

# Verify cleanup
docker ps -a | grep kariricode
# Should be empty

# Start fresh
make up
```

#### Scenario 4: Multiple Projects
**Problem:** Multiple projects using same ports

**Solution 1: Port Namespacing**
```bash
# Project A
APP_PORT=8089
REDIS_PORT=6379
MEMCACHED_PORT=11211

# Project B  
APP_PORT=8189
REDIS_PORT=6479
MEMCACHED_PORT=11311

# Project C
APP_PORT=8289
REDIS_PORT=6579
MEMCACHED_PORT=11411
```

**Solution 2: Stop Other Projects**
```bash
# In other project directories
make down

# In current project
make up-safe
```

---

### Troubleshooting Port Issues

#### Issue: "Permission Denied" When Killing Process

**Symptoms:**
```
✗ Failed to free port 6379 (may require sudo)
```

**Solution:**
```bash
# Option A: Use sudo
sudo make kill-port PORT=6379

# Option B: Stop service properly
sudo systemctl stop redis

# Option C: Change port
echo "REDIS_PORT=6380" >> .env
```

#### Issue: Port Shows Available But Still Fails

**Diagnosis:**
```bash
# Check with multiple tools
lsof -i :6379
ss -ltn | grep :6379
netstat -tlnp | grep :6379

# Check Docker specifically
docker ps -a --format "{{.Names}}\t{{.Ports}}" | grep 6379
```

**Solution:**
```bash
# Clean everything
make clean-docker
docker system prune -f

# Verify
make check-ports

# Start fresh
make up
```

#### Issue: Ports Freed But Container Won't Start

**Diagnosis:**
```bash
# Check detailed error
make up
make logs

# Common causes:
# - Image not found
# - Invalid .env syntax
# - Volume permission issues
```

**Solution:**
```bash
# Pull latest image
docker pull kariricode/php-api-stack:dev

# Validate .env
make env-check

# Fix permissions
sudo chown -R $USER:$USER .

# Retry
make up-safe
```

---

### Best Practices

#### 1. Always Use `up-safe` for First Start
```bash
# ✅ Good: Checks ports first
make up-safe

# ❌ Avoid: May fail with cryptic errors
make up
```

#### 2. Regular Cleanup
```bash
# Weekly maintenance
make clean-docker
make check-ports

# After failed starts
make clean-docker
make up-safe
```

#### 3. Port Reservation Strategy
```bash
# .env - Use port ranges per project
# Project A: 808X, 637X, 1121X
APP_PORT=8089
REDIS_PORT=6379
MEMCACHED_PORT=11211

# Project B: 818X, 647X, 1131X
APP_PORT=8189
REDIS_PORT=6479
MEMCACHED_PORT=11311
```

#### 4. Document Custom Ports
```bash
# .env
# Custom ports to avoid conflict with local Redis
REDIS_PORT=6380  # Changed from 6379
MEMCACHED_PORT=11212  # Changed from 11211
```

#### 5. Automated Conflict Resolution in Scripts
```bash
#!/bin/bash
# start-dev.sh

# Ensure ports are free
make check-ports || make fix-ports

# Start services
make up-safe

# Wait for healthy
until make health | grep -q "healthy"; do
    echo "Waiting for services..."
    sleep 2
done

echo "✓ Development environment ready"
```

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
# kariricode-devkit_memcached   Up 5 seconds   0.0.0.0:11211->11211/tcp
# ╚════════════════════════════════════════════════════════╝
```

#### Safe Start (Recommended)
```bash
# Start with port conflict detection
make up-safe

# Executes:
# 1. make check-ports (cleans Docker, checks ports)
# 2. make up (starts services)

# Use this for:
# - First time setup
# - After system reboot
# - When unsure about port conflicts
# - Team onboarding
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
# *_memcached         0.0.0.0:11211->11211/tcp
# ╚════════════════════════════════════════════════════════╝
```

**Access Services:**
```bash
# Application HTTP
curl http://localhost:8089

# Redis (from host, if exposed)
redis-cli -h localhost -p 6379

# Memcached (from host)
echo "stats" | nc localhost 11211
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

### Port-Related Issues

See complete port troubleshooting in [Port Conflict Detection & Resolution](#port-conflict-detection--resolution) section.

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
make diagnose-ports
make fix-ports

# Invalid .env
make env-check
# Fix syntax errors

# Missing image
docker pull kariricode/php-api-stack:dev
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
make up-safe
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
make up-safe

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
make up-safe

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
make up-safe

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
      
      - name: Check for port conflicts
        run: make check-ports
      
      - name: Start Docker Compose
        run: make up-safe
      
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
    - apk add --no-cache make lsof
    - cp .env.example .env
    - make clean-docker
    - make check-ports
    - make up-safe
  
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

### Port Management
```bash
make check-ports        # Check for port conflicts
make diagnose-ports     # Detailed port analysis
make fix-ports          # Auto-fix port conflicts (interactive)
make kill-port PORT=X   # Kill specific port
make port-scan          # Scan common ports
make clean-docker       # Clean orphaned containers
make up-safe            # Safe startup with port check
```

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

### 2. Always Check Ports Before Starting
```bash
# ✅ Recommended workflow
make check-ports && make up

# ✅ Or use safe startup
make up-safe
```

### 3. Clean Up Regularly
```bash
# Weekly cleanup
make clean-docker
make prune

# Before major changes
make rebuild
```

### 4. Monitor Health
```bash
# Add to daily routine
make health

# Investigate unhealthy services immediately
make logs SERVICE=<unhealthy-service>
```

### 5. Version Control
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

### 6. Document Port Changes
```bash
# .env
# Custom ports to avoid local Redis conflict
REDIS_PORT=6380  # Changed from 6379 (local Redis running)
MEMCACHED_PORT=11212  # Changed from 11211 (local Memcached)
```

---

## Quick Reference Card
```
╔═══════════════════════════════════════════════════════════╗
║                Docker Compose Quick Reference             ║
╠═══════════════════════════════════════════════════════════╣
║ SAFE START   │ make up-safe                               ║
║ START        │ make up                                    ║
║ STOP         │ make down                                  ║
║ RESTART      │ make restart                               ║
║ REBUILD      │ make rebuild                               ║
║──────────────┼────────────────────────────────────────────║
║ CHECK PORTS  │ make check-ports                           ║
║ DIAGNOSE     │ make diagnose-ports                        ║
║ FIX PORTS    │ make fix-ports                             ║
║ KILL PORT    │ make kill-port PORT=6379                   ║
║ PORT SCAN    │ make port-scan                             ║
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

Port Conflict Workflow:
  1. make diagnose-ports   # Identify conflicts
  2. make fix-ports        # Auto-resolve
  3. make up-safe          # Start safely
  
Or change ports:
  echo "REDIS_PORT=6380" >> .env
  echo "MEMCACHED_PORT=11212" >> .env
  make up-safe
```

---

**Version**: 1.0.0  
**Module**: `Makefile.docker-compose.mk`  
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>