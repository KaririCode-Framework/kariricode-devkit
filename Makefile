# ╔══════════════════════════════════════════════════════════════╗
# ║  KaririCode Devkit — Build & Quality Automation             ║
# ║  https://github.com/kariricode/devkit                       ║
# ╚══════════════════════════════════════════════════════════════╝
#
#   make               Show available targets
#   make release       Full pipeline: lint → test → build → verify
#   make build         Compile kcode.phar
#   make quality       Run full quality pipeline
#
# Requirements:
#   PHP ≥ 8.4  ·  Composer 2.x  ·  humbug/box 4.x (for PHAR builds)
#

.PHONY: help install install-prod build verify self-test \
        quality test analyse cs-check cs-fix rector format \
        clean distclean release lint check-env

.DEFAULT_GOAL := help
SHELL         := /bin/bash

# ── Configuration ──────────────────────────────────────────

PHP         ?= php
COMPOSER    ?= composer
BOX         ?= box
KCODE       := vendor/bin/kcode

BUILD_DIR   := build
PHAR        := $(BUILD_DIR)/kcode.phar
VERSION     := $(shell $(PHP) -r "echo json_decode(file_get_contents('box.json'))->metadata->version ?? 'dev';" 2>/dev/null || echo "dev")
GIT_TAG     := $(shell git describe --tags --abbrev=0 2>/dev/null || echo "untagged")
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
	@printf "  $(_YELLOW)Build$(_RESET)\n"
	@grep -E '^(install|install-prod|build|verify|self-test|clean|distclean|release|check-env):.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "    $(_GREEN)%-16s$(_RESET) %s\n", $$1, $$2}'
	@printf "\n"
	@printf "  $(_YELLOW)Quality$(_RESET)\n"
	@grep -E '^(quality|test|analyse|cs-check|cs-fix|rector|format|lint):.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "    $(_GREEN)%-16s$(_RESET) %s\n", $$1, $$2}'
	@printf "\n"
	@printf "  $(_DIM)Override defaults:  PHP=php8.4 COMPOSER=composer2 make build$(_RESET)\n"
	@printf "  $(_DIM)Pass extra args:   make test ARGS=\"--filter=testFoo\"$(_RESET)\n"
	@printf "\n"

# ══════════════════════════════════════════════════════════════
#  Dependencies
# ══════════════════════════════════════════════════════════════

install: ## Install all Composer dependencies
	$(call _header,Installing dependencies)
	@$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader
	$(call _ok,Dependencies installed)

install-prod: ## Install without dev deps (minimal PHAR)
	$(call _header,Installing production dependencies)
	@$(COMPOSER) install --no-dev --no-interaction --prefer-dist --optimize-autoloader
	$(call _ok,Production dependencies installed)

# ══════════════════════════════════════════════════════════════
#  Quality
# ══════════════════════════════════════════════════════════════

quality: | _require-vendor ## Full pipeline: cs-check → analyse → test
	$(call _header,Quality Pipeline)
	@$(KCODE) init
	@$(KCODE) quality

test: | _require-vendor ## Run PHPUnit tests
	@$(KCODE) test $(ARGS)

analyse: | _require-vendor ## Run PHPStan + Psalm
	@$(KCODE) analyse $(ARGS)

cs-check: | _require-vendor ## Check code style (dry-run)
	@$(KCODE) cs:fix --check $(ARGS)

cs-fix: | _require-vendor ## Fix code style
	@$(KCODE) cs:fix $(ARGS)

rector: | _require-vendor ## Run Rector (dry-run)
	@$(KCODE) rector $(ARGS)

format: | _require-vendor ## Apply all formatting (cs-fix + rector)
	@$(KCODE) format $(ARGS)

lint: cs-check analyse ## Lint: code style + static analysis

# ══════════════════════════════════════════════════════════════
#  Build
# ══════════════════════════════════════════════════════════════

build: | _require-box _require-vendor ## Compile kcode.phar
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

verify: | _require-phar ## Verify PHAR integrity and functionality
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

self-test: | _require-phar ## Run kcode.phar against this project
	$(call _header,Self-test — kcode.phar on devkit project)
	@$(PHP) $(PHAR) init
	@$(PHP) $(PHAR) migrate --dry-run
	$(call _ok,Self-test passed)

# ══════════════════════════════════════════════════════════════
#  Clean
# ══════════════════════════════════════════════════════════════

clean: ## Remove build artifacts
	$(call _header,Cleaning)
	@rm -rf $(BUILD_DIR)
	@rm -rf .kcode/build
	$(call _ok,Build artifacts removed)

distclean: clean ## Full clean: artifacts + vendor + lock
	@rm -rf vendor
	@rm -f composer.lock
	$(call _ok,Vendor and lock file removed)

# ══════════════════════════════════════════════════════════════
#  Release
# ══════════════════════════════════════════════════════════════

release: quality build verify ## Full release: quality → build → verify
	@printf "\n"
	@printf "  $(_GREEN)═══════════════════════════════════════════════════$(_RESET)\n"
	@printf "  $(_GREEN)  kcode.phar v$(VERSION) ready for release         $(_RESET)\n"
	@printf "  $(_GREEN)═══════════════════════════════════════════════════$(_RESET)\n"
	@printf "\n"
	@printf "  $(_BOLD)Artifact$(_RESET)   $(PHAR)\n"
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

check-env: ## Show build environment info
	$(call _header,Build Environment)
	@printf "  $(_BOLD)PHP$(_RESET)            $$($(PHP) -v 2>/dev/null | head -1 || echo 'not found')\n"
	@printf "  $(_BOLD)Composer$(_RESET)       "; $(COMPOSER) --version 2>/dev/null | head -1 || printf "not found\n"
	@printf "  $(_BOLD)Box$(_RESET)            "; $(BOX) --version 2>/dev/null | head -1 || printf "not found\n"
	@printf "  $(_BOLD)Git$(_RESET)            $(GIT_TAG) ($(COMMIT))\n"
	@printf "  $(_BOLD)Version$(_RESET)        $(VERSION)\n"
	@printf "  $(_BOLD)phar.readonly$(_RESET)  $$($(PHP) -r 'echo ini_get("phar.readonly") ? "On ⚠" : "Off ✓";' 2>/dev/null)\n"
	@printf "\n"

# ── Guards ─────────────────────────────────────────────────

_require-box:
	@command -v $(BOX) > /dev/null 2>&1 || { \
		printf "\n"; \
		printf "  $(_RED)✗$(_RESET) $(_BOLD)humbug/box$(_RESET) not found\n"; \
		printf "\n"; \
		printf "  Install via Composer:\n"; \
		printf "    $(_CYAN)composer global require humbug/box$(_RESET)\n"; \
		printf "\n"; \
		printf "  Or download standalone:\n"; \
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

_require-phar:
	@test -f $(PHAR) || { \
		printf "\n  $(_RED)✗$(_RESET) $(PHAR) not found. Run $(_BOLD)make build$(_RESET) first.\n\n"; \
		exit 1; \
	}
