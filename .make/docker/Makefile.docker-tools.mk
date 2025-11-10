# ==============================================================================
# KaririCode\DevKit - Docker Development Tools
# ==============================================================================
# Ferramentas de desenvolvimento em ambiente Docker
# ==============================================================================

.PHONY: docker-htop docker-vim docker-less docker-jq docker-yq \
        docker-lsof docker-strace docker-ip docker-nc

# ==============================================================================
# INTERACTIVE TOOLS
# ==============================================================================

docker-htop: ## Run htop utility in Docker
	@echo -e "$(BLUE)→ Running htop in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) $(DOCKER_IMAGE) htop

# ==============================================================================
# TEXT EDITORS & VIEWERS
# ==============================================================================

docker-vim: ## Edit files with vim in Docker (usage: make docker-vim CMD="src/file.php")
	$(call validate_param,CMD,make docker-vim CMD='src/file.php')
	@echo -e "$(BLUE)→ Running vim $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) $(DOCKER_IMAGE) vim $(CMD)

docker-less: ## View files with less in Docker (usage: make docker-less CMD="README.md")
	$(call validate_param,CMD,make docker-less CMD='README.md')
	@echo -e "$(BLUE)→ Running less $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) $(DOCKER_IMAGE) less $(CMD)

# ==============================================================================
# DATA PROCESSING TOOLS
# ==============================================================================

docker-jq: ## Run jq utility in Docker (usage: make docker-jq CMD="'.version' composer.json")
	$(call validate_param,CMD,make docker-jq CMD="'.version' composer.json")
	@echo -e "$(BLUE)→ Running jq $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) sh -c "jq $(CMD)"

docker-yq: ## Run yq utility in Docker (usage: make docker-yq CMD="'.services' docker-compose.yml")
	$(call validate_param,CMD,make docker-yq CMD="'.services' docker-compose.yml")
	@echo -e "$(BLUE)→ Running yq $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) sh -c "yq $(CMD)"

# ==============================================================================
# SYSTEM DIAGNOSTIC TOOLS
# ==============================================================================

docker-lsof: ## Run lsof utility in Docker (usage: make docker-lsof CMD="-i")
	@echo -e "$(BLUE)→ Running lsof $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) --cap-add=SYS_PTRACE --cap-add=SYS_ADMIN $(DOCKER_IMAGE) lsof $(CMD)

docker-strace: ## Run strace utility in Docker (usage: make docker-strace CMD="php -v")
	$(call validate_param,CMD,make docker-strace CMD='php -v')
	@echo -e "$(BLUE)→ Running strace $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) --cap-add=SYS_PTRACE $(DOCKER_IMAGE) strace $(CMD)

# ==============================================================================
# NETWORK DIAGNOSTIC TOOLS
# ==============================================================================

docker-ip: ## Run iproute2 utility in Docker (usage: make docker-ip CMD="addr")
	$(call validate_param,CMD,make docker-ip CMD='addr')
	@echo -e "$(BLUE)→ Running ip $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) ip $(CMD)

docker-nc: ## Run netcat utility in Docker (usage: make docker-nc CMD="-vz localhost 9000")
	$(call validate_param,CMD,make docker-nc CMD='-vz localhost 9000')
	@echo -e "$(BLUE)→ Running netcat $(CMD) in Docker...$(RESET)"
	@$(DOCKER_RUN_IT) $(DOCKER_IMAGE) nc $(CMD)
