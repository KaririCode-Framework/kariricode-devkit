# ==============================================================================
# KaririCode\DevKit - Professional Development Makefile
# ==============================================================================
#
# Makefile modular seguindo SOLID principles e DRY
# Organização semântica por responsabilidade
#
# Usage:
#   make <target>
#   make help     - Display all available targets
#
# Author: Walmir Silva <walmir.silva@kariricode.org>
# URL: https:\/\/github.com/KaririCode-Framework/kariricode-devkit
# ==============================================================================

.DEFAULT_GOAL := help
.PHONY: help

# ==============================================================================
# CORE INCLUDES (ordem de dependência)
# ==============================================================================

MAKE_DIR := .make

# 1. Core - Variáveis e funções compartilhadas
-include $(MAKE_DIR)/core/Makefile.variables.mk
-include $(MAKE_DIR)/core/Makefile.functions.mk

# 2. Local - Targets locais
-include $(MAKE_DIR)/local/Makefile.setup.mk
-include $(MAKE_DIR)/local/Makefile.qa.mk
-include $(MAKE_DIR)/local/Makefile.helpers.mk

# 3. Pipeline - Orquestração
-include $(MAKE_DIR)/pipeline/Makefile.orchestration.mk

# 4. Docker - Targets Docker
-include $(MAKE_DIR)/docker/Makefile.docker-core.mk
-include $(MAKE_DIR)/docker/Makefile.docker-compose.mk
-include $(MAKE_DIR)/docker/Makefile.docker-qa.mk
-include $(MAKE_DIR)/docker/Makefile.docker-image.mk
-include $(MAKE_DIR)/docker/Makefile.docker-tools.mk

# ==============================================================================
# HELP SYSTEM
# ==============================================================================

define AWK_HELP_SCRIPT
BEGIN { \
    FS = ":.*?## "; \
    header_printed = 0; \
} \
/^[a-zA-Z0-9_-]+:.*?## / { \
    if (header_printed == 0) { \
        printf "\n$(BOLD)%s$(RESET)\n", TITLE; \
        header_printed = 1; \
    } \
    printf "  $(CYAN)%-20s$(RESET) %s\n", $$1, $$2; \
}
endef

help: ## Display this help message
	@echo ""
	@echo -e "$(BOLD)$(CYAN)KaririCode\\DevKit - Development Makefile$(RESET)"
	@echo -e "$(BLUE)═══════════════════════════════════════════════════════$(RESET)"

	@awk -v TITLE="🚀 Main Pipeline"           '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/pipeline/Makefile.orchestration.mk
	@awk -v TITLE="🛠️  Setup & Maintenance"     '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/local/Makefile.setup.mk
	@awk -v TITLE="🧪 Quality Assurance (Local)" '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/local/Makefile.qa.mk
	@awk -v TITLE="🧰 Developer Helpers"       '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/local/Makefile.helpers.mk
	@awk -v TITLE="🐳 Docker QA Pipeline"     '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/docker/Makefile.docker-qa.mk
	@awk -v TITLE="🐳 Docker Compose"         '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/docker/Makefile.docker-compose.mk
	@awk -v TITLE="🐳 Docker Core"            '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/docker/Makefile.docker-core.mk
	@awk -v TITLE="🐳 Docker Tools"           '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/docker/Makefile.docker-tools.mk
	@awk -v TITLE="🐳 Docker Image"           '$(AWK_HELP_SCRIPT)' $(MAKE_DIR)/docker/Makefile.docker-image.mk 

	@echo ""
	@echo -e "$(BOLD)Usage Examples:$(RESET)"
	@echo -e "  $(YELLOW)make install$(RESET)         # Install all dependencies"
	@echo -e "  $(YELLOW)make ci$(RESET)              # Run local CI pipeline"
	@echo -e "  $(YELLOW)make docker-ci$(RESET)       # Run CI in Docker (isolated)"
	@echo -e "  $(YELLOW)make docker-shell$(RESET)    # Open interactive Docker shell"
	@echo ""

debug-composer: ## Debug composer configuration
	@echo "COMPOSER_BIN = '$(COMPOSER_BIN)'"
	@echo "COMPOSER     = '$(COMPOSER)'"
	@command -v composer || echo "Composer not found with command -v"
	@which composer || echo "Composer not found with which"
	@type composer || echo "Composer not found with type"
