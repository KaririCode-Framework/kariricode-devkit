# ==============================================================================
# KaririCode\DevKit - Reusable Functions
# ==============================================================================
# Define funções shell reutilizáveis seguindo DRY principle
# ==============================================================================

# --- Validação de Parâmetros ---
define validate_param
	@if [ -z "$($(1))" ]; then \
		echo -e "$(RED)✗ $(1) required. Usage: $(2)$(RESET)"; \
		exit 1; \
	fi
endef

# --- Execução Docker Genérica ---
define docker_exec
	@echo -e "$(BLUE)→ Running $(1) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) $(2)
	@echo -e "$(GREEN)✓ $(1) complete$(RESET)"
endef

define docker_exec_make
	@echo -e "$(BLUE)→ Running make $(1) in Docker...$(RESET)"
	@$(DOCKER_RUN) $(DOCKER_IMAGE) make $(1)
	@echo -e "$(GREEN)✓ Docker make $(1) complete$(RESET)"
endef

# --- Verificação de Arquivo ---
define check_file
	@test -f $(1) || (echo "$(RED)✗ $(2) not found$(RESET)" && exit 1)
endef

# --- Criação de Diretório ---
define ensure_dir
	@mkdir -p $(1)
endef

# --- Header de Pipeline ---
define pipeline_header
	@echo -e "$(BOLD)$(CYAN)╔════════════════════════════════════════════════════════╗$(RESET)"
	@echo -e "$(BOLD)$(CYAN)║  $(1)$(RESET)"
	@echo -e "$(BOLD)$(CYAN)╚════════════════════════════════════════════════════════╝$(RESET)"
	@echo -e ""
endef

# --- Verificação de Branch Git ---
define check_git_branch
	@CURRENT_BRANCH=$$(git rev-parse --abbrev-ref HEAD); \
	if [ "$$CURRENT_BRANCH" != "$(1)" ]; then \
		echo -e "$(RED)✗ This action requires branch '$(1)' (current: $$CURRENT_BRANCH)$(RESET)"; \
		exit 1; \
	fi
endef

# --- Verificação de Versão PHP ---
define check_php_version
	@echo -e "$(BLUE)→ Checking PHP version...$(RESET)"; \
	CURRENT_VERSION="$(PHP_VERSION)"; \
	MIN_VERSION="$(PHP_MIN_VERSION)"; \
	LOWEST=$$(printf '%s\n%s' "$$MIN_VERSION" "$$CURRENT_VERSION" | sort -V | head -n1); \
	if [ "$$LOWEST" != "$$MIN_VERSION" ]; then \
		echo -e "$(RED)✗ PHP $$MIN_VERSION+ required, found $$CURRENT_VERSION$(RESET)"; \
		exit 1; \
	fi; \
	echo -e "$(GREEN)✓ PHP version $$CURRENT_VERSION OK (>= $$MIN_VERSION)$(RESET)"
endef
