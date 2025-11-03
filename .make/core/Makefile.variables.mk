# ==============================================================================
# KaririCode\DevKit - Core Variables & Configuration
# ==============================================================================
# Centraliza todas as variáveis compartilhadas entre módulos
# ==============================================================================

# --- Cores & Formatação ---
RESET   := \033[0m
BOLD    := \033[1m
RED     := \033[31m
GREEN   := \033[32m
YELLOW  := \033[33m
BLUE    := \033[34m
MAGENTA := \033[35m
CYAN    := \033[36m

# --- Docker Configuration ---
DOCKER_IMAGE   := kariricode/php-api-stack:dev
DOCKER_RUN     := docker run --rm -v $(PWD):/var/www/html -w /var/www/html
DOCKER_RUN_IT  := docker run --rm -it -v $(PWD):/var/www/html -w /var/www/html

# --- PHP Configuration ---
PHP             := $(shell which php)
PHP_VERSION     := $(shell $(PHP) -r 'echo PHP_VERSION;')
PHP_MIN_VERSION := 8.4.0
PHP_CLEAN_RUN   := $(PHP) -d xdebug.mode=off -d pcov.enabled=0 -d opcache.enable=1

# --- Composer Configuration ---
# Prevent the 'COMPOSER' make variable from conflicting with the
# 'COMPOSER' environment variable used by the tool itself.
unexport COMPOSER
COMPOSER_BIN := $(shell command -v composer 2>/dev/null || echo "")
# Use the full path for execution. Fallback to 'composer' if not found.
COMPOSER     := $(if $(COMPOSER_BIN),$(COMPOSER_BIN),composer)

# --- Directories ---
SRC_DIR          := src
TEST_DIR         := tests
BENCHMARK_DIR    := benchmarks
BUILD_DIR        := build
COVERAGE_DIR     := coverage
REPORTS_DIR      := reports
CACHE_DIR        := var/cache
BENCH_REPORT_DIR := $(BUILD_DIR)/benchmarks

# --- Vendor Binaries ---
VENDOR_BIN       := vendor/bin
PHPUNIT          := $(VENDOR_BIN)/phpunit
PHPSTAN          := $(VENDOR_BIN)/phpstan
PSALM            := $(VENDOR_BIN)/psalm
PHPCS            := $(VENDOR_BIN)/phpcs
PHPCBF           := $(VENDOR_BIN)/phpcbf
PHP_CS_FIXER     := $(VENDOR_BIN)/php-cs-fixer
INFECTION        := $(VENDOR_BIN)/infection
PHPBENCH         := $(VENDOR_BIN)/phpbench

# --- Export all for subshells ---
export
