# ╔══════════════════════════════════════════════════════════════╗
# ║  KaririCode Devkit — Build & Quality Automation             ║
# ║  https://github.com/kariricode/devkit                       ║
# ╚══════════════════════════════════════════════════════════════╝
#
#   make               Show available targets
#   make quality       Run full quality pipeline via kcode
#   make release       Full release: quality → build → verify
#   make build         Compile kcode.phar
#
# Requirements:
#   PHP ≥ 8.4  ·  Composer 2.x  ·  humbug/box 4.x (for PHAR builds)
#

.PHONY: help install install-prod build verify self-test \
        quality test analyse cs-check cs-fix rector format security lint \
        clean distclean release check-env

.DEFAULT_GOAL := help
SHELL         := /bin/bash

# ── Configuration ──────────────────────────────────────────

PHP         ?= php
COMPOSER    ?= composer
BOX         ?= box
KCODE       := vendor/bin/kcode

BUILD_DIR   := build
PHAR        := $(BUILD_DIR)/kcode.phar

# Version: prefer git tag, fall back to box.json metadata, then 'dev'
VERSION     := $(shell git describe --tags --abbrev=0 2>/dev/null \
               || $(PHP) -r "echo json_decode(file_get_contents('box.json'),true)['metadata']['version'] ?? 'dev';" 2>/dev/null \
               || echo "dev")
COMMIT      := $(shell git rev-parse --short HEAD 2>/dev/null || echo "unknown")
TIMESTAMP   := $(shell date -u +"%Y-%m-%dT%H:%M:%SZ")

# ── Colors ─────────────────────────────────────────────────

_RESET  := \033[0m
_BOLD   := \033[1m
_DIM    := \033[2m
_GREEN  := \033[32m
_YELLOW := \033[33m
_CYAN   := \033[36m
_RED    := \033[31m
_RULE   := ──────────────────────────────────────────────────

define _header
	@printf "\n$(_CYAN)$(_RULE)$(_RESET)\n"
	@printf "  $(_BOLD)$(1)$(_RESET)\n"
	@printf "$(_CYAN)$(_RULE)$(_RESET)\n\n"
endef

define _ok
	@printf "  $(_GREEN)✓$(_RESET) $(1)\n"
endef

define _warn
	@printf "  $(_YELLOW)⚠$(_RESET) $(1)\n"
endef

define _fail
	@printf "  $(_RED)✗$(_RESET) $(1)\n"
endef

define _info
	@printf "  $(_DIM)$(1)$(_RESET)\n"
endef

# ══════════════════════════════════════════════════════════════
#  Help
# ══════════════════════════════════════════════════════════════

help: ## Show available targets
	@printf "\n"
	@printf "  $(_BOLD)KaririCode Devkit$(_RESET) v$(VERSION) $(_DIM)($(COMMIT))$(_RESET)\n"
	@printf "  $(_DIM)Unified quality toolchain for KaririCode Framework$(_RESET)\n"
	@printf "\n"
	@printf "  $(_YELLOW)Dependencies & Build$(_RESET)\n"
	@grep -E '^(install|install-prod|build|verify|self-test|check-env|clean|distclean|release):.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "    $(_GREEN)%-16s$(_RESET) %s\n", $$1, $$2}'
	@printf "\n"
	@printf "  $(_YELLOW)Quality & Toolchain$(_RESET)\n"
	@grep -E '^(quality|test|analyse|cs-check|cs-fix|rector|format|security|lint):.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "    $(_GREEN)%-16s$(_RESET) %s\n", $$1, $$2}'
	@printf "\n"
	@printf "  $(_DIM)Override defaults:  PHP=php8.4 COMPOSER=composer2 make build$(_RESET)\n"
	@printf "  $(_DIM)Pass extra args:   make test ARGS=\"--filter=testFoo\"$(_RESET)\n"
	@printf "\n"

# ══════════════════════════════════════════════════════════════
#  Dependencies
# ══════════════════════════════════════════════════════════════

install: ## Install all Composer dependencies (dev + prod)
	$(call _header,Installing dependencies)
	@$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
	$(call _ok,Dependencies installed)

install-prod: ## Install without dev deps (for PHAR compilation)
	$(call _header,Installing production dependencies)
	@$(COMPOSER) install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
	$(call _ok,Production dependencies installed)

# ══════════════════════════════════════════════════════════════
#  Quality (via kcode CLI)
# ══════════════════════════════════════════════════════════════

quality: | _require-vendor _require-kcode ## Full pipeline: cs:check → analyse → test (via kcode quality)
	$(call _header,Quality Pipeline)
	@$(KCODE) init
	@$(KCODE) quality

test: | _require-vendor _require-kcode ## Run PHPUnit tests
	@$(KCODE) test $(ARGS)

analyse: | _require-vendor _require-kcode ## Run static analysis (PHPStan + Psalm)
	@$(KCODE) analyse $(ARGS)

cs-check: | _require-vendor _require-kcode ## Check code style — dry run (no files changed)
	@$(KCODE) cs:fix --check $(ARGS)

cs-fix: | _require-vendor _require-kcode ## Fix code style with PHP-CS-Fixer
	@$(KCODE) cs:fix $(ARGS)

rector: | _require-vendor _require-kcode ## Run Rector in dry-run mode (use ARGS=--fix to apply)
	@$(KCODE) rector $(ARGS)

format: | _require-vendor _require-kcode ## Apply all formatting: cs:fix + rector --fix
	@$(KCODE) format $(ARGS)

security: | _require-vendor _require-kcode ## Run composer audit for known vulnerabilities
	@$(KCODE) security

lint: cs-check analyse ## Lint: code style check + static analysis (no fixes applied)

# ══════════════════════════════════════════════════════════════
#  Build (PHAR)
# ══════════════════════════════════════════════════════════════

build: | _require-box _require-vendor ## Compile kcode.phar via humbug/box
	$(call _header,Building kcode.phar v$(VERSION))
	@mkdir -p $(BUILD_DIR)
	@START=$$(date +%s%N); \
	$(PHP) -d phar.readonly=0 $$(command -v $(BOX)) compile --config=box.json && \
	END=$$(date +%s%N); \
	ELAPSED=$$(( (END - START) / 1000000 )); \
	SECS=$$(( ELAPSED / 1000 )); \
	MS=$$(( ELAPSED % 1000 )); \
	printf "\n"; \
	printf "  $(_GREEN)✓$(_RESET) PHAR compiled: $(_BOLD)$(PHAR)$(_RESET)\n"; \
	printf "  $(_GREEN)✓$(_RESET) Size: $$(du -h $(PHAR) | cut -f1)\n"; \
	printf "  $(_GREEN)✓$(_RESET) Built in $${SECS}.$${MS}s\n"; \
	printf "  $(_DIM)  Version: $(VERSION)  Commit: $(COMMIT)  Time: $(TIMESTAMP)$(_RESET)\n"

# ══════════════════════════════════════════════════════════════
#  Verification
# ══════════════════════════════════════════════════════════════

verify: | _require-phar ## Verify PHAR integrity and smoke-test all commands
	$(call _header,Verifying kcode.phar)
	@PASS=0; FAIL=0; \
	\
	printf "  $(_BOLD)Signature$(_RESET)\n"; \
	$(PHP) $(PHAR) --version > /dev/null 2>&1 \
		&& { printf "    $(_GREEN)✓$(_RESET) --version\n"; PASS=$$((PASS+1)); } \
		|| { printf "    $(_RED)✗$(_RESET) --version\n"; FAIL=$$((FAIL+1)); }; \
	\
	printf "\n  $(_BOLD)Commands$(_RESET)\n"; \
	for cmd in init migrate test analyse cs:fix rector security quality format clean; do \
		$(PHP) $(PHAR) --help 2>/dev/null | grep -q "$$cmd" \
			&& { printf "    $(_GREEN)✓$(_RESET) $$cmd\n"; PASS=$$((PASS+1)); } \
			|| { printf "    $(_RED)✗$(_RESET) $$cmd\n"; FAIL=$$((FAIL+1)); }; \
	done; \
	\
	printf "\n"; \
	if [ $$FAIL -eq 0 ]; then \
		printf "  $(_GREEN)✓ All $$PASS checks passed$(_RESET)\n"; \
	else \
		printf "  $(_RED)✗ $$FAIL of $$((PASS+FAIL)) checks failed$(_RESET)\n"; \
		exit 1; \
	fi

self-test: | _require-phar ## Run kcode.phar against this project (init + migrate dry-run)
	$(call _header,Self-test — kcode.phar on devkit project)
	@$(PHP) $(PHAR) init
	@$(PHP) $(PHAR) migrate --dry-run
	$(call _ok,Self-test passed)

# ══════════════════════════════════════════════════════════════
#  Clean
# ══════════════════════════════════════════════════════════════

clean: ## Remove build artefacts (build/ and .kcode/build/)
	$(call _header,Cleaning)
	@rm -rf $(BUILD_DIR)
	@rm -rf .kcode/build
	$(call _ok,Build artefacts removed)

distclean: clean ## Full clean: artefacts + vendor (for a clean composer install)
	@rm -rf vendor
	$(call _ok,Vendor removed — run 'make install' to restore)

# ══════════════════════════════════════════════════════════════
#  Release
# ══════════════════════════════════════════════════════════════

release: quality build verify ## Full release pipeline: quality → build → verify
	@printf "\n"
	@printf "  $(_GREEN)═══════════════════════════════════════════════════$(_RESET)\n"
	@printf "  $(_GREEN)  kcode.phar v$(VERSION) ready for release         $(_RESET)\n"
	@printf "  $(_GREEN)═══════════════════════════════════════════════════$(_RESET)\n"
	@printf "\n"
	@printf "  $(_BOLD)Artefact$(_RESET)   $(PHAR)\n"
	@printf "  $(_BOLD)Size$(_RESET)       $$(du -h $(PHAR) | cut -f1)\n"
	@printf "  $(_BOLD)Commit$(_RESET)     $(COMMIT)\n"
	@printf "  $(_BOLD)Time$(_RESET)       $(TIMESTAMP)\n"
	@printf "\n"
	@printf "  $(_DIM)Tag and push to trigger GitHub Release:$(_RESET)\n"
	@printf "    $(_CYAN)git tag v$(VERSION) && git push --tags$(_RESET)\n"
	@printf "\n"

# ══════════════════════════════════════════════════════════════
#  Diagnostics
# ══════════════════════════════════════════════════════════════

check-env: ## Show build environment info (PHP, Composer, Box, Git, phar.readonly)
	$(call _header,Build Environment)
	@printf "  $(_BOLD)PHP$(_RESET)            $$($(PHP) -v 2>/dev/null | head -1 || echo 'not found')\n"
	@printf "  $(_BOLD)Composer$(_RESET)       "; $(COMPOSER) --version 2>/dev/null | head -1 || printf "not found\n"
	@printf "  $(_BOLD)Box$(_RESET)            "; $(BOX) --version 2>/dev/null | head -1 || printf "not found (install: https://github.com/box-project/box)\n"
	@printf "  $(_BOLD)kcode$(_RESET)          "; test -f $(KCODE) && $(PHP) $(KCODE) --version 2>/dev/null || printf "not found — run make install\n"
	@printf "  $(_BOLD)Git tag$(_RESET)        $(VERSION) ($(COMMIT))\n"
	@printf "  $(_BOLD)phar.readonly$(_RESET)  $$($(PHP) -r 'echo ini_get("phar.readonly") ? "On (WARN: use php -d phar.readonly=0 for builds)" : "Off (OK)";' 2>/dev/null)\n"
	@printf "\n"

# ── Guards ─────────────────────────────────────────────────

_require-box:
	@command -v $(BOX) > /dev/null 2>&1 || { \
		printf "\n"; \
		printf "  $(_RED)✗$(_RESET) $(_BOLD)humbug/box$(_RESET) not found\n"; \
		printf "\n"; \
		printf "  Install standalone:\n"; \
		printf "    $(_CYAN)wget -O box https://github.com/box-project/box/releases/latest/download/box.phar$(_RESET)\n"; \
		printf "    $(_CYAN)chmod +x box && sudo mv box /usr/local/bin/box$(_RESET)\n"; \
		printf "\n"; \
		exit 1; \
	}

_require-vendor:
	@test -d vendor || { \
		printf "\n  $(_RED)✗$(_RESET) vendor/ not found. Run $(_BOLD)make install$(_RESET) first.\n\n"; \
		exit 1; \
	}

_require-kcode:
	@test -f $(KCODE) || { \
		printf "\n  $(_RED)✗$(_RESET) $(KCODE) not found. Run $(_BOLD)make install$(_RESET) first.\n\n"; \
		exit 1; \
	}

_require-phar:
	@test -f $(PHAR) || { \
		printf "\n  $(_RED)✗$(_RESET) $(PHAR) not found. Run $(_BOLD)make build$(_RESET) first.\n\n"; \
		exit 1; \
	}
