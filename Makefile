# ============================================================================
# KaririCode DevKit - Professional Development Environment
# ============================================================================
# Repository: https://github.com/KaririCode-Framework/kariricode-devkit
# License: MIT
# ============================================================================

.DEFAULT_GOAL := help
.PHONY: help

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
RED := \033[0;31m
NC := \033[0m # No Color

# Docker Compose command
DOCKER_COMPOSE := docker-compose
EXEC_PHP := $(DOCKER_COMPOSE) exec php
EXEC_PHP_ROOT := $(DOCKER_COMPOSE) exec -u root php

# ============================================================================
# HELP
# ============================================================================

help: ## Show this help message
	@echo "$(BLUE)╔════════════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(BLUE)║          KaririCode DevKit - Available Commands               ║$(NC)"
	@echo "$(BLUE)╚════════════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""

# ============================================================================
# DOCKER MANAGEMENT
# ============================================================================: ## Create required directory structure
up: ## Start all containers in detached mode
	@echo "$(BLUE)🚀 Starting containers...$(NC)"
	@$(DOCKER_COMPOSE) up -d 
	@echo "$(GREEN)✓ Containers started successfully$(NC)"

down: ## Stop and remove all containers
	@echo "$(YELLOW)🛑 Stopping containers...$(NC)"
	@$(DOCKER_COMPOSE) down
	@echo "$(GREEN)✓ Containers stopped$(NC)"

restart: down up ## Restart all containers

status: ## Show status of all containers
	@$(DOCKER_COMPOSE) ps

logs: ## Show logs from all containers (use CTRL+C to exit)
	@$(DOCKER_COMPOSE) logs -f

logs-php: ## Show PHP container logs
	@$(DOCKER_COMPOSE) logs -f php

logs-redis: ## Show Redis container logs
	@$(DOCKER_COMPOSE) logs -f redis

shell: ## Access PHP container shell as app user
	@$(EXEC_PHP) /bin/bash

shell-root: ## Access PHP container shell as root user
	@$(EXEC_PHP_ROOT) /bin/bash

# ============================================================================
# DEPENDENCY MANAGEMENT
# ============================================================================

install: ## Install composer dependencies
	@echo "$(BLUE)📦 Installing dependencies...$(NC)"
	@if [ -f composer.json ]; then \
		$(EXEC_PHP) composer install --no-interaction --prefer-dist --optimize-autoloader; \
		echo "$(GREEN)✓ Dependencies installed$(NC)"; \
	else \
		echo "$(YELLOW)⚠ No composer.json found$(NC)"; \
		echo "$(YELLOW)  Run './install.sh' to create a new component$(NC)"; \
	fi

update: ## Update composer dependencies
	@echo "$(BLUE)🔄 Updating dependencies...$(NC)"
	@$(EXEC_PHP) composer update
	@echo "$(GREEN)✓ Dependencies updated$(NC)"

require: ## Install a new package (use: make require PKG=vendor/package)
	@test -n "$(PKG)" || (echo "$(RED)Error: PKG variable is required. Usage: make require PKG=vendor/package$(NC)" && exit 1)
	@$(EXEC_PHP) composer require $(PKG)

require-dev: ## Install a new dev package (use: make require-dev PKG=vendor/package)
	@test -n "$(PKG)" || (echo "$(RED)Error: PKG variable is required. Usage: make require-dev PKG=vendor/package$(NC)" && exit 1)
	@$(EXEC_PHP) composer require --dev $(PKG)

autoload: ## Dump composer autoload
	@$(EXEC_PHP) composer dump-autoload

validate: ## Validate composer.json
	@$(EXEC_PHP) composer validate --strict

outdated: ## Show outdated packages
	@$(EXEC_PHP) composer outdated --direct

# ============================================================================
# TESTING
# ============================================================================

test: ## Run all tests
	@echo "$(BLUE)🧪 Running tests...$(NC)"
	@$(EXEC_PHP) vendor/bin/phpunit --no-coverage --testdox
	@echo "$(GREEN)✓ Tests completed$(NC)"

test-coverage: ## Run tests with coverage report (HTML)
	@echo "$(BLUE)🧪 Running tests with coverage...$(NC)"
	@$(EXEC_PHP) vendor/bin/phpunit --coverage-html=coverage
	@echo "$(GREEN)✓ Coverage report generated in ./coverage$(NC)"

test-coverage-text: ## Run tests with coverage report (terminal)
	@$(EXEC_PHP) vendor/bin/phpunit --coverage-text

test-unit: ## Run unit tests only
	@$(EXEC_PHP) vendor/bin/phpunit --testsuite=Unit

test-integration: ## Run integration tests only
	@$(EXEC_PHP) vendor/bin/phpunit --testsuite=Integration

test-filter: ## Run specific test (use: make test-filter FILTER=TestClassName)
	@test -n "$(FILTER)" || (echo "$(RED)Error: FILTER variable is required$(NC)" && exit 1)
	@$(EXEC_PHP) vendor/bin/phpunit --filter $(FILTER)

# ============================================================================
# CODE QUALITY
# ============================================================================

cs-check: ## Check code style (dry-run)
	@echo "$(BLUE)🔍 Checking code style...$(NC)"
	@$(EXEC_PHP) vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

cs-fix: ## Fix code style
	@echo "$(BLUE)✨ Fixing code style...$(NC)"
	@$(EXEC_PHP) vendor/bin/php-cs-fixer fix
	@echo "$(GREEN)✓ Code style fixed$(NC)"

analyse: ## Run static analysis with PHPStan (max level)
	@echo "$(BLUE)🔬 Running static analysis...$(NC)"
	@$(EXEC_PHP) vendor/bin/phpstan analyse src tests --level=max
	@echo "$(GREEN)✓ Static analysis completed$(NC)"

analyse-baseline: ## Generate PHPStan baseline
	@$(EXEC_PHP) vendor/bin/phpstan analyse src tests --level=max --generate-baseline

phpmd: ## Run PHP Mess Detector
	@echo "$(BLUE)🔍 Running PHP Mess Detector...$(NC)"
	@$(EXEC_PHP) vendor/bin/phpmd src text devkit/.config/phpmd/ruleset.xml

rector: ## Run Rector (dry-run)
	@echo "$(BLUE)🔧 Running Rector...$(NC)"
	@$(EXEC_PHP) vendor/bin/rector process --dry-run

rector-fix: ## Run Rector and apply changes
	@$(EXEC_PHP) vendor/bin/rector process

# ============================================================================
# COMPREHENSIVE QUALITY CHECKS
# ============================================================================

check: cs-check analyse phpmd ## Run all quality checks (CS, PHPStan, PHPMD)
	@echo "$(GREEN)✓ All quality checks completed$(NC)"

fix: cs-fix ## Fix all auto-fixable issues

qa: fix test check ## Complete QA pipeline: fix, test, and check

# ============================================================================
# SECURITY
# ============================================================================

security: ## Check for security vulnerabilities
	@echo "$(BLUE)🔒 Checking security vulnerabilities...$(NC)"
	@$(EXEC_PHP) composer audit
	@echo "$(GREEN)✓ Security check completed$(NC)"

# ============================================================================
# CACHE OPERATIONS
# ============================================================================

redis-cli: ## Access Redis CLI
	@$(DOCKER_COMPOSE) exec redis redis-cli

redis-flush: ## Flush Redis cache
	@echo "$(YELLOW)🗑️  Flushing Redis cache...$(NC)"
	@$(DOCKER_COMPOSE) exec redis redis-cli FLUSHALL
	@echo "$(GREEN)✓ Redis cache flushed$(NC)"

redis-info: ## Show Redis information
	@$(DOCKER_COMPOSE) exec redis redis-cli INFO

memcached-stats: ## Show Memcached statistics
	@$(DOCKER_COMPOSE) exec memcached sh -c 'echo stats | nc localhost 11211'

memcached-flush: ## Flush Memcached
	@echo "$(YELLOW)🗑️  Flushing Memcached...$(NC)"
	@$(DOCKER_COMPOSE) exec memcached sh -c 'echo flush_all | nc localhost 11211'
	@echo "$(GREEN)✓ Memcached flushed$(NC)"

# ============================================================================
# UTILITIES
# ============================================================================

clean: ## Clean all generated files and caches
	@echo "$(YELLOW)🧹 Cleaning generated files...$(NC)"
	@sudo rm -rf vendor/
	@sudo rm -rf coverage/
	@sudo rm -rf .phpunit.cache/
	@sudo rm -rf .php-cs-fixer.cache
	@sudo rm -rf composer.lock
	@echo "$(GREEN)✓ Cleanup completed$(NC)"

reset: clean down ## Complete reset (clean + remove containers)
	@echo "$(GREEN)✓ Environment reset completed$(NC)"

rebuild: reset force-rebuild install ## Rebuild environment from scratch
	@echo "$(GREEN)✓ Environment rebuilt$(NC)"

force-rebuild: ## Force rebuild images (no cache) and recreate containers
	@echo "$(YELLOW)🔥 Forcing image rebuild (no cache)...$(NC)"
	@$(DOCKER_COMPOSE) build --no-cache
	@echo "$(YELLOW)🔥 Recreating containers...$(NC)"
	@$(DOCKER_COMPOSE) up --force-recreate -d
	@echo "$(GREEN)✓ Rebuild and recreate complete$(NC)"

permissions: ## Fix file permissions
	@echo "$(BLUE)🔧 Fixing permissions...$(NC)"
	@$(EXEC_PHP_ROOT) chown -R app:app /var/www/html
	@echo "$(GREEN)✓ Permissions fixed$(NC)"

# ============================================================================
# XDEBUG
# ============================================================================

xdebug-on: ## Enable Xdebug
	@echo "$(BLUE)🐛 Enabling Xdebug...$(NC)"
	@echo "XDEBUG_MODE=debug,coverage" > .env.xdebug
	@$(DOCKER_COMPOSE) restart php
	@echo "$(GREEN)✓ Xdebug enabled$(NC)"

xdebug-off: ## Disable Xdebug
	@echo "$(BLUE)🐛 Disabling Xdebug...$(NC)"
	@echo "XDEBUG_MODE=off" > .env.xdebug
	@$(DOCKER_COMPOSE) restart php
	@echo "$(GREEN)✓ Xdebug disabled$(NC)"

xdebug-status: ## Show Xdebug status
	@$(EXEC_PHP) php -v | grep -i xdebug || echo "$(YELLOW)Xdebug is disabled$(NC)"

# ============================================================================
# INFORMATION
# ============================================================================

php-version: ## Show PHP version
	@$(EXEC_PHP) php -v

php-info: ## Show PHP information
	@$(EXEC_PHP) php -i

php-extensions: ## List installed PHP extensions
	@$(EXEC_PHP) php -m

composer-version: ## Show Composer version
	@$(EXEC_PHP) composer --version

env: ## Show environment variables
	@$(EXEC_PHP) printenv

# ============================================================================
# DOCUMENTATION
# ============================================================================

docs: ## Generate API documentation (requires phpDocumentor)
	@echo "$(BLUE)📚 Generating documentation...$(NC)"
	@$(EXEC_PHP) vendor/bin/phpdoc
	@echo "$(GREEN)✓ Documentation generated in ./docs$(NC)"

# ============================================================================
# CI/CD SIMULATION
# ============================================================================

ci: install test check security ## Simulate CI pipeline
	@echo "$(GREEN)✓ CI pipeline completed successfully$(NC)"

# ============================================================================
# ADVANCED OPERATIONS
# ============================================================================

profile: ## Profile application performance
	@$(EXEC_PHP) vendor/bin/phpbench run --report=default

benchmark: ## Run benchmarks
	@$(EXEC_PHP) vendor/bin/phpbench run

metrics: ## Generate code metrics
	@$(EXEC_PHP) vendor/bin/phpmetrics --report-html=metrics src

# ============================================================================
# DEVELOPMENT HELPERS
# ============================================================================

watch-tests: ## Watch and run tests on file changes (requires watchman or inotifywait)
	@echo "$(BLUE)👀 Watching for changes...$(NC)"
	@while true; do \
		inotifywait -r -e modify src/ tests/ 2>/dev/null && make test; \
	done

init: ## Initialize new component development environment
	@echo "$(BLUE)🎬 Initializing development environment...$(NC)"
	@$(MAKE) up
	@$(MAKE) install
	@echo "$(GREEN)✓ Development environment ready!$(NC)"
	@echo ""
	@echo "$(YELLOW)Next steps:$(NC)"
	@echo "  1. Edit your code in ./src"
	@echo "  2. Run $(GREEN)make test$(NC) to execute tests"
	@echo "  3. Run $(GREEN)make qa$(NC) for complete quality assurance"
	@echo ""