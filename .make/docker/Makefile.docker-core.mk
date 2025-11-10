# ==============================================================================
# KaririCode\DevKit - Docker Core Functions
# ==============================================================================
# Abstrai lógica compartilhada de execução Docker
# ==============================================================================

.PHONY: docker-shell docker-composer docker-php

# ==============================================================================
# CORE DOCKER ENTRYPOINTS
# ==============================================================================

docker-shell: ## Open interactive shell in Docker
	@echo "$(BLUE)→ Opening Docker shell ($(DOCKER_IMAGE))...$(RESET)"
	@$(DOCKER_RUN_IT) $(DOCKER_IMAGE) /bin/bash

docker-composer: ## Run composer in Docker (usage: make docker-composer CMD="install")
	$(call validate_param,CMD,make docker-composer CMD='install')
	@echo "$(BLUE)→ Running composer $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) composer $(CMD)

docker-php: ## Run PHP command in Docker (usage: make docker-php CMD="-v")
	$(call validate_param,CMD,make docker-php CMD='-v')
	@echo "$(BLUE)→ Running php $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) php $(CMD)
