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
	@echo -e "$(BLUE)→ Pulling Docker image $(DOCKER_IMAGE)...$(RESET)"
	@docker pull $(DOCKER_IMAGE)
	@echo -e "$(GREEN)✓ Docker image pulled$(RESET)"

docker-info: ## Show Docker environment info
	@echo -e "$(BOLD)$(CYAN)Docker Environment Information$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@echo -e "Docker Image:       $(DOCKER_IMAGE)"
	@echo -e "Mount Point:        $(PWD):/app"
	@echo -e ""
	@echo -e "$(BOLD)$(CYAN)Container PHP Info$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════════$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) php -v
	@echo -e ""
	@$(DOCKER_RUN) $(DOCKER_IMAGE) composer --version

docker-clean: ## Clean Docker resources
	@echo -e "$(BLUE)→ Cleaning Docker resources...$(RESET)"
	@docker system prune -f
	@echo -e "$(GREEN)✓ Docker cleanup complete$(RESET)"
