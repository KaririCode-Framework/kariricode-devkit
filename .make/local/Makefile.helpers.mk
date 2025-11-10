# ==============================================================================
# KaririCode\DevKit - Developer Helper Targets
# ==============================================================================

.PHONY: bench bench-help git-hooks-setup git-hooks-remove \
		git-hooks-check watch-test server shell info tag release stats loc

# ==============================================================================
# BENCHMARKING & PERFORMANCE (single target, param-driven)
# ==============================================================================

# --- Tooling & Config ---
PHPBENCH         ?= vendor/bin/phpbench
# This is the correct way: execute the phpbench script *with* a clean PHP binary.
PHP_CLEAN_RUN    := $(PHP) -d xdebug.mode=off -d pcov.enabled=0 -d opcache.enable=1

# --- Directories ---
BENCHMARK_DIR    ?= benchmarks
BENCH_REPORT_DIR ?= $(BUILD_DIR)/benchmarks

# --- Default Parameters (Trimmed whitespace) ---
REF          ?= auto
STORE        ?= 0
TAG          ?=
ENFORCE_MAIN ?= 0
REPORT       ?= 0

# --- Encapsulated Benchmark Script ---
# This "encapsulates" the shell logic to hide command echoing
define BENCH_SCRIPT
	@set -e; \
	BENCH_FLAGS="--progress=dots"; \
	\
	# ------------------ Comparison (REF) ------------------
	if [ "$(REF)" = "auto" ]; then \
		if $(PHP_CLEAN_RUN) $(PHPBENCH) log | grep -E -q "Tag:[[:space:]]+main"; then \
			printf "$(GREEN)✓ 'main' reference found. Enabling comparison…$(RESET)\n"; \
			BENCH_FLAGS="$$BENCH_FLAGS --ref=main"; \
		else \
			printf "$(YELLOW)⚠ No 'main' reference found. Running without comparison.$(RESET)\n"; \
			printf "$(YELLOW)  Hint: make bench STORE=1 TAG=main ENFORCE_MAIN=1$(RESET)\n"; \
		fi; \
	elif [ -n "$(REF)" ]; then \
		printf "$(CYAN)→ Comparing against reference: %s$(RESET)\n" "$(REF)"; \
		BENCH_FLAGS="$$BENCH_FLAGS --ref=$(REF)"; \
	fi; \
	\
	# ------------------ Execution / Storage ------------------
	if [ "$(STORE)" = "1" ]; then \
		if [ -z "$(TAG)" ]; then \
			printf "$(RED)✗ TAG is required when STORE=1 (e.g., TAG=my-feature)$(RESET)\n"; \
			exit 1; \
		fi; \
		if [ "$(TAG)" = "main" ] && [ "$(ENFORCE_MAIN)" = "1" ]; then \
			CURRENT_BRANCH=$$(git rev-parse --abbrev-ref HEAD); \
			if [ "$$CURRENT_BRANCH" != "main" ]; then \
				printf "$(RED)✗ This action requires branch 'main' (current: %s)$(RESET)\n" "$$CURRENT_BRANCH"; \
				exit 1; \
			fi; \
		fi; \
		printf "$(BLUE)→ Storing run with tag '%s'…$(RESET)\n" "$(TAG)"; \
		if [ "$(REPORT)" = "1" ]; then \
			$(PHP_CLEAN_RUN) $(PHPBENCH) run $$BENCH_FLAGS --store --tag="$(TAG)" | tee "$(BENCH_REPORT_DIR)/last.txt"; \
			printf "$(GREEN)✓ Output saved to %s/last.txt$(RESET)\n" "$(BENCH_REPORT_DIR)"; \
		else \
			$(PHP_CLEAN_RUN) $(PHPBENCH) run $$BENCH_FLAGS --store --tag="$(TAG)"; \
		fi; \
	else \
		if [ "$(REPORT)" = "1" ]; then \
			$(PHP_CLEAN_RUN) $(PHPBENCH) run $$BENCH_FLAGS | tee "$(BENCH_REPORT_DIR)/last.txt"; \
			printf "$(GREEN)✓ Output saved to %s/last.txt$(RESET)\n" "$(BENCH_REPORT_DIR)"; \
		else \
			$(PHP_CLEAN_RUN) $(PHPBENCH) run $$BENCH_FLAGS; \
		fi; \
	fi; \
	\
	printf "$(GREEN)✓ Benchmarks complete$(RESET)\n"
endef

bench: ## Run benchmarks (unified target). See 'make bench-help'.
	@printf "$(BLUE)→ Running benchmarks (unified target)…$(RESET)\n"
	@mkdir -p "$(BENCHMARK_DIR)"
	@mkdir -p "$(BENCH_REPORT_DIR)"
	@$(BENCH_SCRIPT)

bench-help: ## Show usage help for the unified bench command
	@echo -e "$(BOLD)$(CYAN)Benchmark Command Help$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@echo -e "Single entrypoint: $(BOLD)make bench$(RESET)"
	@echo -e ""
	@echo -e "$(BOLD)Parameters:$(RESET)"
	@echo -e "  $(CYAN)REF=auto|main|<tag>$(RESET)     Compare against a stored tag"
	@echo -e "                   auto → try 'main' if available (default)"
	@echo -e "                   main → compare against main reference"
	@echo -e "                   <tag> → compare against any other tag"
	@echo -e ""
	@echo -e "  $(CYAN)STORE=1 TAG=<tag>$(RESET)     Store benchmarks with a given tag"
	@echo -e "                   ex.: STORE=1 TAG=feature-x"
	@echo -e ""
	@echo -e "  $(CYAN)ENFORCE_MAIN=1$(RESET)      When TAG=main, ensures you are on branch 'main'"
	@echo -e ""
	@echo -e "  $(CYAN)REPORT=1$(RESET)            Save text output to $(BENCH_REPORT_DIR)/last.txt"
	@echo -e ""
	@echo -e "$(BOLD)Examples:$(RESET)"
	@echo -e "  make bench                         # Run normal benchmarks"
	@echo -e "  make bench REF=main                # Compare against 'main'"
	@echo -e "  make bench REF=my-tag              # Compare against custom tag"
	@echo -e "  make bench STORE=1 TAG=feat        # Store results under tag 'feat'"
	@echo -e "  make bench STORE=1 TAG=main ENFORCE_MAIN=1 # Store results as 'main'"
	@echo -e "  make bench REF=main REPORT=1       # Compare and save output to last.txt"
	@echo -e ""
	@echo -e "$(GREEN)✓ Tip: Run 'make bench-help' anytime to see this guide$(RESET)"

# ==============================================================================
# DEVELOPMENT HELPERS
# ==============================================================================
git-hooks-setup: ## Setup git hooks for development workflow
	@echo -e "$(BLUE)→ Setting up git hooks...$(RESET)"
	@mkdir -p .git/hooks
	@if [ -f .git/hooks/pre-commit ] && [ ! -f .git/hooks/pre-commit.bak ]; then \
		echo -e "$(YELLOW)⚠ Existing pre-commit hook found. Backing up...$(RESET)"; \
		mv .git/hooks/pre-commit .git/hooks/pre-commit.bak; \
	fi
	@echo '#!/bin/sh' > .git/hooks/pre-commit
	@echo 'set -e' >> .git/hooks/pre-commit
	@echo 'make pre-commit' >> .git/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit
	@echo -e "$(GREEN)✓ Git hooks set up$(RESET)"

git-hooks-remove: ## Remove git hooks and restore backups if any
	@echo -e "$(BLUE)→ Cleaning up git hooks...$(RESET)"
	@if [ -f .git/hooks/pre-commit.bak ]; then \
		echo -e "$(YELLOW)↩ Restoring backup pre-commit hook...$(RESET)"; \
		mv .git/hooks/pre-commit.bak .git/hooks/pre-commit; \
	elif [ -f .git/hooks/pre-commit ]; then \
		echo -e "$(RED)✗ Removing generated pre-commit hook...$(RESET)"; \
		rm .git/hooks/pre-commit; \
	else \
		echo -e "$(YELLOW)⚠ No pre-commit hook found$(RESET)"; \
	fi
	@echo -e "$(GREEN)✓ Git hooks cleaned$(RESET)"

git-hooks-check: ## Check if git hooks are installed correctly
	@echo -e "$(BLUE)→ Verifying git hooks...$(RESET)"
	@if [ -f .git/hooks/pre-commit ]; then \
		if grep -q "make pre-commit" .git/hooks/pre-commit; then \
			echo -e "$(GREEN)✓ pre-commit hook is installed correctly$(RESET)"; \
		else \
			echo -e "$(RED)✗ pre-commit hook exists but was not installed by this Makefile$(RESET)"; \
		fi \
	else \
		echo -e "$(RED)✗ pre-commit hook not found$(RESET)"; \
	fi

info: ## Show PHP and project information
	@echo -e "$(BOLD)$(CYAN)Project Information$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@echo -e "PHP Version:        $(PHP_VERSION)"
	@echo -e "PHP Binary:         $(PHP)"
	@echo -e "Composer:           $(COMPOSER)"
	@echo -e "Project Directory:  $(shell pwd)"
	@echo -e "Source Directory:   $(SRC_DIR)"
	@echo -e "Test Directory:     $(TEST_DIR)"
	@echo -e ""
	@echo -e "$(BOLD)$(CYAN)Installed Tools$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@test -f $(PHPUNIT) && echo -e "PHPUnit:            ✓" || echo -e "PHPUnit:            ✗"
	@test -f $(PHPSTAN) && echo -e "PHPStan:            ✓" || echo -e "PHPStan:            ✗"
	@test -f $(PSALM) && echo -e "Psalm:              ✓" || echo -e "Psalm:              ✗"
	@test -f $(PHPCS) && echo -e "PHPCS:              ✓" || echo -e "PHPCS:              ✗"
	@test -f $(PHP_CS_FIXER) && echo -e "PHP-CS-Fixer:       ✓" || echo -e "PHP-CS-Fixer:       ✗"
	@test -f $(INFECTION) && echo -e "Infection:          ✓" || echo -e "Infection:          ✗"
	@test -f $(PHPBENCH) && echo -e "PHPBench:           ✓" || echo -e "PHPBench:           ✗"

# ==============================================================================
# RELEASE MANAGEMENT
# ==============================================================================

tag: ## Create a new git tag (usage: make tag VERSION=1.0.0)
	@if [ -z "$(VERSION)" ]; then \
		echo -e "$(RED)✗ VERSION is required. Usage: make tag VERSION=1.0.0$(RESET)"; \
		exit 1; \
	fi
	@echo -e "$(BLUE)→ Creating tag v$(VERSION)...$(RESET)"
	@git tag -a "v$(VERSION)" -m "Release v$(VERSION)"
	@git push origin "v$(VERSION)"
	@echo -e "$(GREEN)✓ Tag v$(VERSION) created and pushed$(RESET)"

release: cd ## Prepare release (run full CD pipeline)
	@echo -e "$(BOLD)$(GREEN)✓ Release preparation complete$(RESET)"
	@echo ""
	@echo -e "$(CYAN)Next steps:$(RESET)"
	@echo -e "  1. Update CHANGELOG.md"
	@echo -e "  2. Update version in composer.json"
	@echo -e "  3. Commit changes"
	@echo -e "  4. Run: make tag VERSION=X.Y.Z"
	@echo -e "  5. Push to GitHub"
	@echo -e "  6. Create GitHub release"

# ==============================================================================
# STATS & METRICS
# ==============================================================================

stats: ## Show project statistics
	@echo -e "$(BOLD)$(CYAN)Project Statistics$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@echo -e "Total PHP files:    $$(find $(SRC_DIR) -name '*.php' | wc -l)"
	@echo -e "Total test files:   $$(find $(TEST_DIR) -name '*.php' | wc -l)"
	@echo -e "Lines of code:      $$(find $(SRC_DIR) -name '*.php' -exec cat {} \; | wc -l)"
	@echo -e "Lines of tests:     $$(find $(TEST_DIR) -name '*.php' -exec cat {} \; | wc -l)"
	@echo -e ""
	@echo -e "$(BOLD)$(CYAN)Directory Sizes$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@du -sh $(SRC_DIR) 2>/dev/null || true
	@du -sh $(TEST_DIR) 2>/dev/null || true
	@du -sh vendor 2>/dev/null || true

loc: ## Count lines of code
	@echo -e "$(BLUE)→ Counting lines of code...$(RESET)"
	@find $(SRC_DIR) -name '*.php' -exec wc -l {} \; | awk '{sum += $$1} END {print "Source: " sum " lines"}'
	@find $(TEST_DIR) -name '*.php' -exec wc -l {} \; | awk '{sum += $$1} END {print "Tests:  " sum " lines"}'
