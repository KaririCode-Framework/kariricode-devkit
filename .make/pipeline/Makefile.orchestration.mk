# ==============================================================================
# KaririCode\DevKit - CI/CD Orchestration
# ==============================================================================
# Orquestra pipelines de CI/CD com composição de targets
# ==============================================================================

.PHONY: check ci ci-full cd pre-commit

# --- Quality Checks ---
check: lint analyse test ## Run all quality checks
	@echo "$(GREEN)✓ All quality checks passed$(RESET)"

# --- CI Pipeline ---
ci: ## Run CI pipeline (fast checks)
	$(call pipeline_header,"KaririCode\\DevKit CI Pipeline")
	@$(MAKE) --no-print-directory check-php
	@$(MAKE) --no-print-directory lint
	@$(MAKE) --no-print-directory cs-check
	@$(MAKE) --no-print-directory phpstan
	@$(MAKE) --no-print-directory psalm
	@$(MAKE) --no-print-directory test
	@echo "$(BOLD)$(GREEN)✓ CI pipeline completed successfully$(RESET)"

# --- Full CI Pipeline ---
ci-full: ## Run full CI pipeline (with coverage)
	$(call pipeline_header,"KaririCode\\DevKit Full CI Pipeline")
	@$(MAKE) --no-print-directory check-php
	@$(MAKE) --no-print-directory validate
	@$(MAKE) --no-print-directory security
	@$(MAKE) --no-print-directory lint
	@$(MAKE) --no-print-directory cs-check
	@$(MAKE) --no-print-directory phpstan
	@$(MAKE) --no-print-directory psalm
	@$(MAKE) --no-print-directory test
	@$(MAKE) --no-print-directory coverage
	@$(MAKE) --no-print-directory mutation
	@echo "$(BOLD)$(GREEN)✓ Full CI pipeline completed successfully$(RESET)"

# --- CD Pipeline ---
cd: ## Run CD pipeline (release preparation)
	$(call pipeline_header,"KaririCode\\DevKit CD Pipeline")
	@$(MAKE) --no-print-directory ci-full
	@$(MAKE) --no-print-directory bench
	@echo "$(BOLD)$(GREEN)✓ CD pipeline completed - Ready for release$(RESET)"

# --- Pre-commit Hook ---
pre-commit: ## Run pre-commit checks
	@echo "$(BLUE)→ Running pre-commit checks...$(RESET)"
	@$(MAKE) --no-print-directory format
	@$(MAKE) --no-print-directory lint
	@$(MAKE) --no-print-directory analyse
	@$(MAKE) --no-print-directory test-unit
	@echo "$(GREEN)✓ Pre-commit checks passed$(RESET)"
