# ==============================================================================
# KaririCode\DevKit - Docker Compose Management
# ==============================================================================
# Gerencia o ciclo de vida completo do ambiente Docker Compose
# ==============================================================================

.PHONY: up down restart stop start status logs logs-follow ps \
        exec-php exec-memcached health config validate-compose \
        up-build rebuild prune network-inspect

# ==============================================================================
# LIFECYCLE MANAGEMENT
# ==============================================================================

up: ## Start Docker Compose services
	@echo "$(BLUE)→ Starting Docker Compose services...$(RESET)"
	@if [ ! -f .env ]; then \
		echo "$(YELLOW)⚠  .env not found, copying from .env.example...$(RESET)"; \
		cp .env.example .env 2>/dev/null || echo "$(RED)✗ .env.example not found$(RESET)"; \
	fi
	@docker compose up -d
	@echo "$(GREEN)✓ Services started$(RESET)"
	@$(MAKE) --no-print-directory status

up-build: ## Start services with build
	@echo "$(BLUE)→ Building and starting Docker Compose services...$(RESET)"
	@docker compose up -d --build
	@echo "$(GREEN)✓ Services built and started$(RESET)"
	@$(MAKE) --no-print-directory status

down: ## Stop and remove Docker Compose services
	@echo "$(BLUE)→ Stopping Docker Compose services...$(RESET)"
	@docker compose down
	@echo "$(GREEN)✓ Services stopped and removed$(RESET)"

stop: ## Stop Docker Compose services (without removing)
	@echo "$(BLUE)→ Stopping Docker Compose services...$(RESET)"
	@docker compose stop
	@echo "$(GREEN)✓ Services stopped$(RESET)"

start: ## Start existing Docker Compose services
	@echo "$(BLUE)→ Starting existing Docker Compose services...$(RESET)"
	@docker compose start
	@echo "$(GREEN)✓ Services started$(RESET)"

restart: ## Restart Docker Compose services
	@echo "$(BLUE)→ Restarting Docker Compose services...$(RESET)"
	@docker compose restart
	@echo "$(GREEN)✓ Services restarted$(RESET)"

rebuild: down clean-volumes up-build ## Rebuild environment from scratch
	@echo "$(GREEN)✓ Environment rebuilt$(RESET)"

# ==============================================================================
# MONITORING & INSPECTION
# ==============================================================================

status: ## Show services status
	@echo "$(BOLD)$(CYAN)Docker Compose Services Status$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

ps: status ## Alias for status

logs: ## Show logs from all services (usage: make logs SERVICE=php)
	@if [ -n "$(SERVICE)" ]; then \
		echo "$(BLUE)→ Showing logs for service: $(SERVICE)$(RESET)"; \
		docker compose logs $(SERVICE); \
	else \
		echo "$(BLUE)→ Showing logs for all services$(RESET)"; \
		docker compose logs; \
	fi

logs-follow: ## Follow logs from services (usage: make logs-follow SERVICE=php)
	@if [ -n "$(SERVICE)" ]; then \
		echo "$(BLUE)→ Following logs for service: $(SERVICE)$(RESET)"; \
		docker compose logs -f $(SERVICE); \
	else \
		echo "$(BLUE)→ Following logs for all services$(RESET)"; \
		docker compose logs -f; \
	fi

health: ## Check services health status
	@echo "$(BOLD)$(CYAN)Services Health Check$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps --format json | jq -r '.[] | "\(.Name): \(.Health // "N/A") - \(.State)"'
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

# ==============================================================================
# CONTAINER INTERACTION
# ==============================================================================

exec-php: ## Execute command in PHP container (usage: make exec-php CMD="php -v")
	@if [ -z "$(CMD)" ]; then \
		echo "$(BLUE)→ Opening interactive shell in PHP container...$(RESET)"; \
		docker compose exec php /bin/bash; \
	else \
		echo "$(BLUE)→ Executing: $(CMD)$(RESET)"; \
		docker compose exec php $(CMD); \
	fi

exec-memcached: ## Execute command in Memcached container
	@echo "$(BLUE)→ Connecting to Memcached container...$(RESET)"
	@docker compose exec memcached sh

# ==============================================================================
# CONFIGURATION & VALIDATION
# ==============================================================================

config: ## Validate and view Docker Compose configuration
	@echo "$(BLUE)→ Validating Docker Compose configuration...$(RESET)"
	@docker compose config
	@echo "$(GREEN)✓ Configuration is valid$(RESET)"

validate-compose: ## Validate docker-compose.yml syntax
	@echo "$(BLUE)→ Validating docker-compose.yml...$(RESET)"
	@docker compose config --quiet && \
		echo "$(GREEN)✓ docker-compose.yml is valid$(RESET)" || \
		(echo "$(RED)✗ docker-compose.yml has errors$(RESET)" && exit 1)

# ==============================================================================
# NETWORK & RESOURCES
# ==============================================================================

network-inspect: ## Inspect Docker network
	@echo "$(BOLD)$(CYAN)Docker Network Information$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker network inspect $$(docker compose config --format json | jq -r '.networks | keys[0]') 2>/dev/null || \
		echo "$(YELLOW)⚠  Network not found or not created yet$(RESET)"
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

prune: ## Remove unused Docker resources
	@echo "$(YELLOW)⚠  This will remove all unused containers, networks, and volumes$(RESET)"
	@echo "$(BLUE)→ Pruning Docker resources...$(RESET)"
	@docker system prune -f --volumes
	@echo "$(GREEN)✓ Docker resources pruned$(RESET)"

clean-volumes: ## Remove all volumes (WARNING: data loss)
	@echo "$(RED)⚠  WARNING: This will delete ALL volume data!$(RESET)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		echo "$(BLUE)→ Removing all volumes...$(RESET)"; \
		docker compose down -v; \
		echo "$(GREEN)✓ Volumes removed$(RESET)"; \
	else \
		echo "$(YELLOW)Cancelled$(RESET)"; \
	fi

# ==============================================================================
# QUICK ACTIONS
# ==============================================================================

shell: exec-php ## Alias for exec-php (open shell)

composer-install: ## Run composer install in PHP container
	@echo "$(BLUE)→ Running composer install...$(RESET)"
	@docker compose exec php composer install --no-interaction --prefer-dist --optimize-autoloader
	@echo "$(GREEN)✓ Composer dependencies installed$(RESET)"

composer-update: ## Run composer update in PHP container
	@echo "$(BLUE)→ Running composer update...$(RESET)"
	@docker compose exec php composer update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader
	@echo "$(GREEN)✓ Composer dependencies updated$(RESET)"

test-compose: up ## Start services and run tests
	@echo "$(BLUE)→ Waiting for services to be ready...$(RESET)"
	@sleep 3
	@$(MAKE) --no-print-directory exec-php CMD="make test"

# ==============================================================================
# DIAGNOSTIC & TROUBLESHOOTING
# ==============================================================================

ports: ## Show exposed ports
	@echo "$(BOLD)$(CYAN)Exposed Ports$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps --format "table {{.Name}}\t{{.Ports}}"
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

inspect-php: ## Inspect PHP container
	@echo "$(BOLD)$(CYAN)PHP Container Inspection$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose exec php php -v
	@echo ""
	@docker compose exec php php -m
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

env-check: ## Verify environment variables
	@echo "$(BOLD)$(CYAN)Environment Configuration$(RESET)"
	@echo "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@if [ -f .env ]; then \
		echo "$(GREEN)✓ .env file exists$(RESET)"; \
		grep -E '^[A-Z_]+=' .env | head -20; \
	else \
		echo "$(RED)✗ .env file not found$(RESET)"; \
		echo "$(YELLOW)  Run: cp .env.example .env$(RESET)"; \
	fi
	@echo "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"