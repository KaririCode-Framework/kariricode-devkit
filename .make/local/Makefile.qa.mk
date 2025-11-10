# ==============================================================================
# KaririCode\DevKit - Quality Assurance Targets
# ==============================================================================

.PHONY: test test-unit test-integration test-functional coverage coverage-text \
		mutation mutation-report analyse phpstan phpstan-baseline psalm \
		psalm-baseline psalm-taint cs-check format format-dry lint

# ==============================================================================
# TESTING
# ==============================================================================

test: ## Run PHPUnit tests
	@echo -e "$(BLUE)→ Running tests...$(RESET)"
	@$(PHPUNIT) --colors=always --testdox
	@echo -e "$(GREEN)✓ Tests passed$(RESET)"

test-unit: ## Run unit tests only
	@echo -e "$(BLUE)→ Running unit tests...$(RESET)"
	@$(PHPUNIT) --colors=always --testdox --testsuite=Unit
	@echo -e "$(GREEN)✓ Unit tests passed$(RESET)"

test-integration: ## Run integration tests only
	@echo -e "$(BLUE)→ Running integration tests...$(RESET)"
	@$(PHPUNIT) --colors=always --testdox --testsuite=Integration
	@echo -e "$(GREEN)✓ Integration tests passed$(RESET)"

test-functional: ## Run functional tests only
	@echo -e "$(BLUE)→ Running functional tests...$(RESET)"
	@$(PHPUNIT) --colors=always --testdox --testsuite=Functional
	@echo -e "$(GREEN)✓ Functional tests passed$(RESET)"

coverage: ## Generate code coverage report
	@echo -e "$(BLUE)→ Generating code coverage report...$(RESET)"
	@mkdir -p $(COVERAGE_DIR)
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html $(COVERAGE_DIR)/html \
		--coverage-clover $(COVERAGE_DIR)/clover.xml \
		--coverage-text=$(COVERAGE_DIR)/coverage.txt
	@echo -e "$(GREEN)✓ Coverage report generated: $(COVERAGE_DIR)/html/index.html$(RESET)"

coverage-text: ## Show coverage in terminal
	@echo -e "$(BLUE)→ Generating text coverage...$(RESET)"
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-text

mutation: ## Run mutation testing
	@echo -e "$(BLUE)→ Running mutation tests...$(RESET)"
	@mkdir -p $(CACHE_DIR)/infection
	@XDEBUG_MODE=coverage $(PHP) -d pcov.enabled=0 $(INFECTION) --threads=4 --min-msi=80 --min-covered-msi=90 --show-mutations
	@echo -e "$(GREEN)✓ Mutation testing complete$(RESET)"

mutation-report: ## Generate mutation testing report
	@echo -e "$(BLUE)→ Generating mutation report...$(RESET)"
	@XDEBUG_MODE=coverage $(PHP) -d pcov.enabled=0 $(INFECTION) --threads=4 --min-msi=80 --min-covered-msi=90 \
		--log-verbosity=all --show-mutations
	@echo -e "$(GREEN)✓ Report generated: infection.html$(RESET)"

# ==============================================================================
# STATIC ANALYSIS
# ==============================================================================

analyse: ## Run all static analysis tools
	@echo -e "$(BLUE)→ Running static analysis...$(RESET)"
	@$(MAKE) phpstan
	@$(MAKE) psalm
	@$(MAKE) cs-check
	@echo -e "$(GREEN)✓ All analysis passed$(RESET)"

phpstan: ## Run PHPStan analysis
	@echo -e "$(BLUE)→ Running PHPStan...$(RESET)"
	@mkdir -p $(CACHE_DIR)/phpstan
	@if [ -n "$$(find $(SRC_DIR) -name '*.php' 2>/dev/null)" ]; then \
		$(PHPSTAN) analyse $(SRC_DIR) --level=max --memory-limit=512M && \
		echo -e "$(GREEN)✓ PHPStan analysis passed$(RESET)"; \
	else \
		echo -e "$(YELLOW)⚠ No PHP files found in $(SRC_DIR), skipping PHPStan$(RESET)"; \
	fi

phpstan-baseline: ## Generate PHPStan baseline
	@echo -e "$(BLUE)→ Generating PHPStan baseline...$(RESET)"
	@$(PHPSTAN) analyse $(SRC_DIR) --level=max --generate-baseline
	@echo -e "$(GREEN)✓ Baseline generated: phpstan-baseline.neon$(RESET)"

psalm: ## Run Psalm analysis
	@echo -e "$(BLUE)→ Running Psalm...$(RESET)"
	@$(PSALM) --show-info=true --stats --no-cache
	@echo -e "$(GREEN)✓ Psalm analysis passed$(RESET)"

psalm-baseline: ## Generate Psalm baseline
	@echo -e "$(BLUE)→ Generating Psalm baseline...$(RESET)"
	@$(PSALM) --set-baseline=psalm-baseline.xml
	@echo -e "$(GREEN)✓ Baseline generated: psalm-baseline.xml$(RESET)"

psalm-taint: ## Run Psalm taint analysis
	@echo -e "$(BLUE)→ Running Psalm taint analysis...$(RESET)"
	@$(PSALM) --taint-analysis
	@echo -e "$(GREEN)✓ Taint analysis complete$(RESET)"

# ==============================================================================
# CODE STYLE & FORMATTING
# ==============================================================================

cs-check: ## Check coding standards (PHPCS)
	@echo -e "$(BLUE)→ Checking coding standards...$(RESET)"
	@$(PHPCS) --standard=phpcs.xml --colors $(SRC_DIR) $(TEST_DIR)
	@echo -e "$(GREEN)✓ Coding standards check passed$(RESET)"

cbf-fix: ## Fix coding standards (PHPCS)
	@echo -e "$(BLUE)→ Fixing coding standards...$(RESET)"
	@$(PHPCBF) --standard=phpcs.xml --colors $(SRC_DIR) $(TEST_DIR)
	@echo -e "$(GREEN)✓ Coding standards fixed$(RESET)"

format: ## Format code with PHP-CS-Fixer
	@echo -e "$(BLUE)→ Formatting code...$(RESET)"
	@$(PHP_CS_FIXER) fix --config=.php-cs-fixer.php --verbose --diff
	@$(MAKE) cbf-fix
	@echo -e "$(GREEN)✓ Code formatted$(RESET)"

format-dry: ## Show formatting changes without applying
	@echo -e "$(BLUE)→ Dry-run code formatting...$(RESET)"
	@$(PHP_CS_FIXER) fix --config=.php-cs-fixer.php --verbose --diff --dry-run

lint: ## Lint PHP files for syntax errors
	@echo -e "$(BLUE)→ Linting PHP files...$(RESET)"
	@find $(SRC_DIR) $(TEST_DIR) -name "*.php" -print0 | xargs -0 -n1 $(PHP) -l > /dev/null
	@echo -e "$(GREEN)✓ All PHP files are valid$(RESET)"
