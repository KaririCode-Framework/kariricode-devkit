# ==============================================================================
# KaririCode\DevKit - Docker CI/QA Pipeline
# ==============================================================================
# Pipeline completo de CI/QA em ambiente Docker isolado
# ==============================================================================

.PHONY: docker-test docker-test-unit docker-test-integration docker-coverage \
        docker-analyse docker-phpstan docker-psalm docker-cs-check \
        docker-format docker-lint docker-ci docker-ci-full docker-bench

# ==============================================================================
# DOCKER QA TARGETS (Individual)
# ==============================================================================

docker-test: ## Run tests in Docker
	$(call docker_exec_make,test)

docker-test-unit: ## Run unit tests in Docker
	$(call docker_exec_make,test-unit)

docker-test-integration: ## Run integration tests in Docker
	$(call docker_exec_make,test-integration)

docker-coverage: ## Generate coverage in Docker
	@echo "$(BLUE)→ Generating coverage in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) make coverage
	@echo "$(GREEN)✓ Coverage report: $(COVERAGE_DIR)/html/index.html$(RESET)"

docker-analyse: ## Run static analysis in Docker
	$(call docker_exec_make,analyse)

docker-phpstan: ## Run PHPStan in Docker
	$(call docker_exec_make,phpstan)

docker-psalm: ## Run Psalm in Docker
	$(call docker_exec_make,psalm)

docker-cs-check: ## Check coding standards in Docker
	$(call docker_exec_make,cs-check)

docker-format: ## Format code in Docker
	$(call docker_exec_make,format)

docker-lint: ## Lint PHP files in Docker
	$(call docker_exec_make,lint)

# ==============================================================================
# DOCKER CI PIPELINES (Orchestration)
# ==============================================================================

docker-ci: ## Run CI pipeline in Docker
	$(call pipeline_header,"Docker CI Pipeline (Isolated Environment)       ")
	$(call docker_exec_make,ci)
	@echo ""
	@echo "$(BOLD)$(GREEN)✓ Docker CI pipeline completed$(RESET)"

docker-ci-full: ## Run full CI pipeline in Docker
	$(call pipeline_header,"Docker Full CI Pipeline (Isolated Environment)     ")
	$(call docker_exec_make,ci-full)
	@echo ""
	@echo "$(BOLD)$(GREEN)✓ Docker full CI pipeline completed$(RESET)"

docker-bench: ## Run benchmarks in Docker
	$(call docker_exec_make,bench)
