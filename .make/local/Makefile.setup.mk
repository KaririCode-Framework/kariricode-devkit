# ==============================================================================
# KaririCode\DevKit - Setup, Install & Clean Targets
# ==============================================================================

.PHONY: check-php install install-dev fresh-install update verify-install \
		clean clean-all validate security security-strict outdated

# ==============================================================================
# SETUP & INSTALLATION
# ==============================================================================

check-php: ## Check PHP version requirement
	$(call check_php_version)

install: check-php ## Install dependencies
	@echo "$(BLUE)→ Installing Composer dependencies...$(RESET)"
	@if ! $(COMPOSER) validate --no-check-publish 2>/dev/null; then \
		echo "$(YELLOW)⚠ composer.lock outdated, updating dependencies...$(RESET)"; \
		$(COMPOSER) update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader; \
	else \
		$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader; \
	fi
	@$(MAKE) verify-install
	@echo "$(GREEN)✓ Installation complete$(RESET)"

install-dev: check-php ## Install dependencies with dev tools
	@echo "$(BLUE)→ Installing Composer dependencies (dev mode)...$(RESET)"
	@$(COMPOSER) install --no-interaction --prefer-dist
	@$(MAKE) verify-install
	@echo "$(GREEN)✓ Development installation complete$(RESET)"

fresh-install: check-php ## Fresh install (removes lock file)
	@echo "$(BLUE)→ Removing composer.lock...$(RESET)"
	@rm -f composer.lock
	@echo "$(BLUE)→ Installing fresh dependencies...$(RESET)"
	@$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader
	@$(MAKE) verify-install
	@echo "$(GREEN)✓ Fresh installation complete$(RESET)"

update: ## Update dependencies
	@echo "$(BLUE)→ Updating Composer dependencies...$(RESET)"
	@$(COMPOSER) update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader
	@echo "$(GREEN)✓ Dependencies updated$(RESET)"

verify-install: ## Verify installation
	@echo "$(BLUE)→ Verifying installation...$(RESET)"
	$(call check_file,vendor/autoload.php,Autoloader)
	$(call check_file,$(PHPUNIT),PHPUnit)
	$(call check_file,$(PHPSTAN),PHPStan)
	@echo "$(GREEN)✓ Installation verified$(RESET)"

# ==============================================================================
# CLEANUP
# ==============================================================================

clean: ## Clean build artifacts and caches
	@echo "$(BLUE)→ Cleaning build artifacts...$(RESET)"
	@rm -rf $(BUILD_DIR)
	@rm -rf $(COVERAGE_DIR)
	@rm -rf $(REPORTS_DIR)
	@rm -rf $(CACHE_DIR)
	@rm -rf .phpunit.cache
	@rm -rf .phpunit.result.cache
	@rm -rf .php-cs-fixer.cache
	@rm -f infection.log infection.html
	@echo "$(GREEN)✓ Clean complete$(RESET)"

clean-all: clean ## Clean everything including vendor
	@echo "$(BLUE)→ Removing vendor directory...$(RESET)"
	@rm -rf vendor
	@rm -f composer.lock
	@echo "$(GREEN)✓ Deep clean complete$(RESET)"

# ==============================================================================
# VALIDATION & SECURITY
# ==============================================================================

validate: ## Validate composer.json
	@echo "$(BLUE)→ Validating composer.json...$(RESET)"
	@if [ ! -f "$(COMPOSER_BIN)" ]; then \
		echo "$(RED)✗ Composer not found. Please install Composer first.$(RESET)"; \
		echo "$(YELLOW)  Visit: https://getcomposer.org/download/$(RESET)"; \
		exit 1; \
	fi
	@$(COMPOSER) validate --strict --no-check-publish
	@echo "$(GREEN)✓ composer.json is valid$(RESET)"

security: ## Check for security vulnerabilities
	@echo "$(BLUE)→ Checking for security vulnerabilities...$(RESET)"
	@if $(COMPOSER) audit --no-dev --locked 2>&1 | grep -q "security vulnerability"; then \
		echo "$(RED)✗ Security vulnerabilities found$(RESET)"; \
		$(COMPOSER) audit --no-dev --locked; \
		exit 1; \
	elif $(COMPOSER) audit --no-dev --locked 2>&1 | grep -q "abandoned"; then \
		echo "$(YELLOW)⚠ Found abandoned packages (informational only):$(RESET)"; \
		$(COMPOSER) audit --no-dev --locked || true; \
		echo "$(GREEN)✓ No security vulnerabilities found$(RESET)"; \
	else \
		echo "$(GREEN)✓ No security vulnerabilities found$(RESET)"; \
	fi

security-strict: ## Check for security vulnerabilities (strict mode)
	@echo "$(BLUE)→ Checking for security vulnerabilities (strict)...$(RESET)"
	@$(COMPOSER) audit
	@echo "$(GREEN)✓ No security vulnerabilities or abandoned packages$(RESET)"

outdated: ## Check for outdated dependencies
	@echo "$(BLUE)→ Checking for outdated dependencies...$(RESET)"
	@$(COMPOSER) outdated --direct
