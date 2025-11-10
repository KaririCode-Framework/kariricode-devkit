# ==============================================================================
# KaririCode\DevKit - Docker Image Management
# ==============================================================================
#
# Provides targets for managing the Docker image lifecycle, such as
# pulling, inspecting, and cleaning.
#
# Usage:
#   make docker-pull
#   make docker-info
#
# Author: Walmir Silva <walmir.silva@kariricode.org>
# ==============================================================================

# ==============================================================================
# DOCKER IMAGE TARGETS
# ==============================================================================

.PHONY: docker-pull docker-info docker-clean

docker-pull: ## Pull Docker image from registry
	@echo "$(BLUE)→ Pulling Docker image $(DOCKER_IMAGE)...$(RESET)"
	@docker pull $(DOCKER_IMAGE)
	@echo "$(GREEN)✓ Docker image pulled$(RESET)"

docker-info: ## Show Docker environment info
	@echo "$(BOLD)$(CYAN)Docker Environment Information$(RESET)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@echo "Docker Image:       $(DOCKER_IMAGE)"
	@echo "Mount Point:        $(PWD):/app"
	@echo ""
	@echo "$(BOLD)$(CYAN)Container PHP Info$(RESET)"
	@echo "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) php -v
	@echo ""
	@$(DOCKER_RUN) $(DOCKER_IMAGE) composer --version

docker-clean: ## Clean Docker resources
	@echo "$(BLUE)→ Cleaning Docker resources...$(RESET)"
	@docker system prune -f
	@echo "$(GREEN)✓ Docker cleanup complete$(RESET)"
