# ==============================================================================
# KaririCode\DevKit - Docker Compose Management
# ==============================================================================
# Gerencia o ciclo de vida completo do ambiente Docker Compose
# ==============================================================================

.PHONY: up down restart stop start status logs logs-follow ps \
        exec-php exec-memcached health config validate-compose \
        up-build rebuild prune network-inspect \
        check-ports diagnose-ports fix-ports kill-port port-scan up-safe \
        clean-docker check-docker-ports

# Force bash for interactive commands
SHELL := /bin/bash

# ==============================================================================
# DOCKER CLEANUP (executa antes das verificações de porta)
# ==============================================================================

clean-docker: ## Clean orphaned Docker containers and networks
	@echo -e "$(BLUE)→ Cleaning orphaned Docker resources...$(RESET)"
	@echo -e "$(YELLOW)  Removing stopped containers...$(RESET)"
	@docker compose down 2>/dev/null || true
	@docker container prune -f 2>/dev/null || true
	@echo -e "$(YELLOW)  Removing unused networks...$(RESET)"
	@docker network prune -f 2>/dev/null || true
	@echo -e "$(GREEN)✓ Docker cleanup complete$(RESET)"

check-docker-ports: ## Check Docker containers using required ports
	@echo -e "$(BLUE)→ Checking Docker containers for port conflicts...$(RESET)"
	@bash -c ' \
	if [ -f .env ]; then \
		source .env 2>/dev/null || true; \
	fi; \
	APP_PORT=$${APP_PORT:-8089}; \
	REDIS_PORT=$${REDIS_PORT:-6379}; \
	MEMCACHED_PORT=$${MEMCACHED_PORT:-11211}; \
	CONFLICTS=0; \
	echo ""; \
	echo -e "$(CYAN)Checking all Docker containers (running and stopped):$(RESET)"; \
	for port in $$APP_PORT $$REDIS_PORT $$MEMCACHED_PORT; do \
		CONTAINERS=$$(docker ps -a --format "{{.Names}}\t{{.Ports}}" 2>/dev/null | grep ":$$port" || true); \
		if [ -n "$$CONTAINERS" ]; then \
			echo ""; \
			echo -e "$(RED)✗ Port $$port is bound to Docker containers:$(RESET)"; \
			echo "$$CONTAINERS" | while read line; do echo "  $$line"; done; \
			CONFLICTS=$$((CONFLICTS + 1)); \
		else \
			echo -e "$(GREEN)✓ Port $$port not bound to Docker containers$(RESET)"; \
		fi; \
	done; \
	if [ $$CONFLICTS -gt 0 ]; then \
		echo ""; \
		echo -e "$(YELLOW)Run: make clean-docker$(RESET) to remove orphaned containers"; \
		exit 1; \
	fi \
	'

# ==============================================================================
# PORT CONFLICT DETECTION & RESOLUTION
# ==============================================================================

check-ports: clean-docker check-docker-ports ## Check for port conflicts (includes Docker cleanup)
	@echo -e "$(BLUE)→ Checking system ports for conflicts...$(RESET)"
	@bash -c ' \
	if [ -f .env ]; then \
		source .env 2>/dev/null || true; \
	fi; \
	CONFLICTS=0; \
	PORTS="$${APP_PORT:-8089} $${REDIS_PORT:-6379} $${MEMCACHED_PORT:-11211}"; \
	for port in $$PORTS; do \
		if lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
			PROCESS=$$(lsof -Pi :$$port -sTCP:LISTEN -t | head -1); \
			CMD=$$(ps -p $$PROCESS -o comm= 2>/dev/null || echo "unknown"); \
			echo -e "$(RED)✗ Port $$port in use by system process PID $$PROCESS ($$CMD)$(RESET)"; \
			CONFLICTS=$$((CONFLICTS + 1)); \
		elif ss -ltn | grep -q ":$$port "; then \
			echo -e "$(RED)✗ Port $$port in use (detected by ss)$(RESET)"; \
			CONFLICTS=$$((CONFLICTS + 1)); \
		else \
			echo -e "$(GREEN)✓ Port $$port is available$(RESET)"; \
		fi; \
	done; \
	if [ $$CONFLICTS -gt 0 ]; then \
		echo ""; \
		echo -e "$(YELLOW)Resolution options:$(RESET)"; \
		echo -e "  1. Run: $(CYAN)make diagnose-ports$(RESET) for detailed info"; \
		echo -e "  2. Run: $(CYAN)make fix-ports$(RESET) to auto-resolve"; \
		echo -e "  3. Run: $(CYAN)make kill-port PORT=<port>$(RESET) for specific port"; \
		exit 1; \
	fi; \
	echo -e "$(GREEN)✓ All ports available$(RESET)" \
	'

diagnose-ports: ## Detailed port conflict diagnosis
	@echo -e "$(BOLD)$(CYAN)Port Conflict Diagnosis$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@echo ""
	@bash -c ' \
	if [ -f .env ]; then \
		source .env 2>/dev/null || true; \
	fi; \
	APP_PORT=$${APP_PORT:-8089}; \
	REDIS_PORT=$${REDIS_PORT:-6379}; \
	MEMCACHED_PORT=$${MEMCACHED_PORT:-11211}; \
	echo -e "$(CYAN)Required Ports:$(RESET)"; \
	echo "  APP_PORT:        $$APP_PORT"; \
	echo "  REDIS_PORT:      $$REDIS_PORT"; \
	echo "  MEMCACHED_PORT:  $$MEMCACHED_PORT"; \
	echo ""; \
	echo -e "$(CYAN)System Port Status:$(RESET)"; \
	for port in $$APP_PORT $$REDIS_PORT $$MEMCACHED_PORT; do \
		echo ""; \
		echo -e "$(YELLOW)Port $$port:$(RESET)"; \
		if lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
			lsof -Pi :$$port -sTCP:LISTEN | awk "NR>1 {printf \"  PID: %s | Command: %s | User: %s\n\", \$$2, \$$1, \$$3}"; \
			echo -e "  $(RED)Status: IN USE (lsof)$(RESET)"; \
		elif ss -ltn | grep -q ":$$port "; then \
			echo -e "  $(RED)Status: IN USE (ss)$(RESET)"; \
			ss -ltnp | grep ":$$port " | awk "{print \"  \" \$$0}"; \
		else \
			echo -e "  $(GREEN)Status: AVAILABLE$(RESET)"; \
		fi; \
	done; \
	echo ""; \
	echo -e "$(CYAN)Docker Containers (All):$(RESET)"; \
	docker ps -a --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null | grep -E "$$APP_PORT|$$REDIS_PORT|$$MEMCACHED_PORT" || echo "  None found"; \
	echo ""; \
	echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"; \
	echo ""; \
	echo -e "$(CYAN)Suggested Actions:$(RESET)"; \
	echo -e "  1. $(YELLOW)make clean-docker$(RESET) - Remove orphaned Docker containers"; \
	echo -e "  2. $(YELLOW)make fix-ports$(RESET) - Auto-fix all conflicts"; \
	echo -e "  3. $(YELLOW)make kill-port PORT=<port>$(RESET) - Kill specific process"; \
	echo "  4. Edit .env to change ports"; \
	'

fix-ports: ## Automatically fix port conflicts (interactive)
	@echo -e "$(YELLOW)⚠  This will attempt to free up conflicting ports$(RESET)"
	@echo -e "$(YELLOW)   Docker containers and processes will be terminated$(RESET)"
	@echo ""
	@bash -c ' \
	read -p "Continue? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(MAKE) --no-print-directory clean-docker; \
		$(MAKE) --no-print-directory _do_fix_ports; \
	else \
		echo -e "$(YELLOW)Cancelled$(RESET)"; \
	fi \
	'

_do_fix_ports:
	@echo -e "$(BLUE)→ Scanning and fixing system port conflicts...$(RESET)"
	@bash -c ' \
	if [ -f .env ]; then \
		source .env 2>/dev/null || true; \
	fi; \
	PORTS="$${APP_PORT:-8089} $${REDIS_PORT:-6379} $${MEMCACHED_PORT:-11211}"; \
	for port in $$PORTS; do \
		if lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
			PROCESS=$$(lsof -Pi :$$port -sTCP:LISTEN -t | head -1); \
			CMD=$$(ps -p $$PROCESS -o comm= 2>/dev/null || echo "unknown"); \
			echo -e "$(YELLOW)→ Terminating process $$PROCESS ($$CMD) on port $$port...$(RESET)"; \
			kill -15 $$PROCESS 2>/dev/null && sleep 1; \
			if lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
				echo -e "$(RED)  Process didnt stop gracefully, forcing...$(RESET)"; \
				kill -9 $$PROCESS 2>/dev/null || sudo kill -9 $$PROCESS 2>/dev/null || true; \
			fi; \
			if ! lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
				echo -e "$(GREEN)✓ Port $$port freed$(RESET)"; \
			else \
				echo -e "$(RED)✗ Failed to free port $$port$(RESET)"; \
			fi; \
		fi; \
	done \
	'
	@echo ""
	@$(MAKE) --no-print-directory check-ports

kill-port: ## Kill process using specific port (usage: make kill-port PORT=11211)
	@if [ -z "$(PORT)" ]; then \
		echo -e "$(RED)✗ PORT parameter required$(RESET)"; \
		echo -e "$(YELLOW)Usage: make kill-port PORT=11211$(RESET)"; \
		exit 1; \
	fi
	@echo -e "$(BLUE)→ Checking port $(PORT)...$(RESET)"
	@bash -c ' \
	if lsof -Pi :$(PORT) -sTCP:LISTEN -t >/dev/null 2>&1; then \
		PROCESS=$$(lsof -Pi :$(PORT) -sTCP:LISTEN -t | head -1); \
		CMD=$$(ps -p $$PROCESS -o comm= 2>/dev/null || echo "unknown"); \
		echo -e "$(YELLOW)Found process: PID $$PROCESS ($$CMD)$(RESET)"; \
		echo -e "$(YELLOW)Attempting graceful shutdown...$(RESET)"; \
		kill -15 $$PROCESS 2>/dev/null && sleep 2; \
		if lsof -Pi :$(PORT) -sTCP:LISTEN -t >/dev/null 2>&1; then \
			echo -e "$(RED)Process didnt stop, forcing shutdown...$(RESET)"; \
			kill -9 $$PROCESS 2>/dev/null || sudo kill -9 $$PROCESS 2>/dev/null || true; \
		fi; \
		if ! lsof -Pi :$(PORT) -sTCP:LISTEN -t >/dev/null 2>&1; then \
			echo -e "$(GREEN)✓ Port $(PORT) is now free$(RESET)"; \
		else \
			echo -e "$(RED)✗ Failed to free port $(PORT)$(RESET)"; \
		fi; \
	else \
		echo -e "$(GREEN)✓ Port $(PORT) is already free$(RESET)"; \
	fi \
	'

port-scan: ## Scan common ports for conflicts
	@echo -e "$(BOLD)$(CYAN)Port Scanner$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@echo ""
	@for port in 80 443 3000 3306 5432 6379 8000 8080 8089 9000 11211 27017; do \
		if lsof -Pi :$$port -sTCP:LISTEN -t >/dev/null 2>&1; then \
			PROCESS=$$(lsof -Pi :$$port -sTCP:LISTEN -t | head -1); \
			CMD=$$(ps -p $$PROCESS -o comm= 2>/dev/null || echo "unknown"); \
			echo -e "$(RED)✗ Port $$port: IN USE$(RESET) (PID $$PROCESS - $$CMD)"; \
		else \
			echo -e "$(GREEN)✓ Port $$port: Available$(RESET)"; \
		fi; \
	done
	@echo ""
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

# ==============================================================================
# LIFECYCLE MANAGEMENT
# ==============================================================================

up-safe: check-ports up ## Start services after checking ports (recommended)

up: ## Start Docker Compose services
	@echo -e "$(BLUE)→ Starting Docker Compose services...$(RESET)"
	@if [ ! -f .env ]; then \
		echo -e "$(YELLOW)⚠  .env not found, copying from .env.example...$(RESET)"; \
		cp .env.example .env 2>/dev/null || echo -e "$(RED)✗ .env.example not found$(RESET)"; \
	fi
	@docker compose --profile development up -d || { \
		echo ""; \
		echo -e "$(RED)✗ Failed to start services$(RESET)"; \
		echo -e "$(YELLOW)Possible port conflict with orphaned containers$(RESET)"; \
		echo -e "$(YELLOW)Run 'make diagnose-ports' for analysis$(RESET)"; \
		echo -e "$(YELLOW)Run 'make clean-docker' to remove orphaned containers$(RESET)"; \
		exit 1; \
	}
	@echo -e "$(GREEN)✓ Services started$(RESET)"
	@sleep 2
	@$(MAKE) --no-print-directory status

up-build: ## Start services with build
	@echo -e "$(BLUE)→ Building and starting Docker Compose services...$(RESET)"
	@docker compose --profile development up -d --build || { \
		echo ""; \
		echo -e "$(RED)✗ Failed to build/start services$(RESET)"; \
		echo -e "$(YELLOW)Run 'make clean-docker' to clean orphaned containers$(RESET)"; \
		exit 1; \
	}
	@echo -e "$(GREEN)✓ Services built and started$(RESET)"
	@$(MAKE) --no-print-directory status

down: ## Stop and remove Docker Compose services
	@echo -e "$(BLUE)→ Stopping Docker Compose services...$(RESET)"
	@docker compose down -v --remove-orphans
	@echo -e "$(GREEN)✓ Services stopped and removed$(RESET)"

stop: ## Stop Docker Compose services (without removing)
	@echo -e "$(BLUE)→ Stopping Docker Compose services...$(RESET)"
	@docker compose stop
	@echo -e "$(GREEN)✓ Services stopped$(RESET)"

start: ## Start existing Docker Compose services
	@echo -e "$(BLUE)→ Starting existing Docker Compose services...$(RESET)"
	@docker compose start
	@echo -e "$(GREEN)✓ Services started$(RESET)"

restart: ## Restart Docker Compose services
	@echo -e "$(BLUE)→ Restarting Docker Compose services...$(RESET)"
	@docker compose restart
	@echo -e "$(GREEN)✓ Services restarted$(RESET)"

rebuild: down clean-volumes up-build ## Rebuild environment from scratch
	@echo -e "$(GREEN)✓ Environment rebuilt$(RESET)"

# ==============================================================================
# MONITORING & INSPECTION
# ==============================================================================

status: ## Show services status
	@echo -e "$(BOLD)$(CYAN)Docker Compose Services Status$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

ps: status ## Alias for status

logs: ## Show logs from all services (usage: make logs SERVICE=php)
	@if [ -n "$(SERVICE)" ]; then \
		echo -e "$(BLUE)→ Showing logs for service: $(SERVICE)$(RESET)"; \
		docker compose logs $(SERVICE); \
	else \
		echo -e "$(BLUE)→ Showing logs for all services$(RESET)"; \
		docker compose logs; \
	fi

logs-follow: ## Follow logs from services (usage: make logs-follow SERVICE=php)
	@if [ -n "$(SERVICE)" ]; then \
		echo -e "$(BLUE)→ Following logs for service: $(SERVICE)$(RESET)"; \
		docker compose logs -f $(SERVICE); \
	else \
		echo -e "$(BLUE)→ Following logs for all services$(RESET)"; \
		docker compose logs -f; \
	fi

health: ## Check services health status
	@echo -e "$(BOLD)$(CYAN)Services Health Check$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps --format json 2>/dev/null | jq -r '.[] | "\(.Name): \(.Health // "N/A") - \(.State)"' 2>/dev/null || docker compose ps
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

# ==============================================================================
# CONTAINER INTERACTION
# ==============================================================================

exec-php: ## Execute command in PHP container (usage: make exec-php CMD="php -v")
	@if [ -z "$(CMD)" ]; then \
		echo -e "$(BLUE)→ Opening interactive shell in PHP container...$(RESET)"; \
		docker compose exec php /bin/bash; \
	else \
		echo -e "$(BLUE)→ Executing: $(CMD)$(RESET)"; \
		docker compose exec php $(CMD); \
	fi

exec-memcached: ## Execute command in Memcached container
	@echo -e "$(BLUE)→ Connecting to Memcached container...$(RESET)"
	@docker compose exec memcached sh

# ==============================================================================
# CONFIGURATION & VALIDATION
# ==============================================================================

config: ## Validate and view Docker Compose configuration
	@echo -e "$(BLUE)→ Validating Docker Compose configuration...$(RESET)"
	@docker compose config
	@echo -e "$(GREEN)✓ Configuration is valid$(RESET)"

validate-compose: ## Validate docker-compose.yml syntax
	@echo -e "$(BLUE)→ Validating docker-compose.yml...$(RESET)"
	@docker compose config --quiet && \
		echo -e "$(GREEN)✓ docker-compose.yml is valid$(RESET)" || \
		(echo -e "$(RED)✗ docker-compose.yml has errors$(RESET)" && exit 1)

# ==============================================================================
# NETWORK & RESOURCES
# ==============================================================================

network-inspect: ## Inspect Docker network
	@echo -e "$(BOLD)$(CYAN)Docker Network Information$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker network inspect $$(docker compose config --format json | jq -r '.networks | keys[0]') 2>/dev/null || \
		echo -e "$(YELLOW)⚠  Network not found or not created yet$(RESET)"
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

prune: ## Remove unused Docker resources
	@echo -e "$(YELLOW)⚠  This will remove all unused containers, networks, and volumes$(RESET)"
	@echo -e "$(BLUE)→ Pruning Docker resources...$(RESET)"
	@docker system prune -f --volumes
	@echo -e "$(GREEN)✓ Docker resources pruned$(RESET)"

clean-volumes: ## Remove all volumes (WARNING: data loss)
	@echo -e "$(RED)⚠  WARNING: This will delete ALL volume data!$(RESET)"
	@bash -c ' \
	read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		echo -e "$(BLUE)→ Removing all volumes...$(RESET)"; \
		docker compose down -v; \
		echo -e "$(GREEN)✓ Volumes removed$(RESET)"; \
	else \
		echo -e "$(YELLOW)Cancelled$(RESET)"; \
	fi \
	'

# ==============================================================================
# QUICK ACTIONS
# ==============================================================================

shell: exec-php ## Alias for exec-php (open shell)

composer-install: ## Run composer install in PHP container
	@echo -e "$(BLUE)→ Running composer install...$(RESET)"
	@docker compose exec php composer install --no-interaction --prefer-dist --optimize-autoloader
	@echo -e "$(GREEN)✓ Composer dependencies installed$(RESET)"

composer-update: ## Run composer update in PHP container
	@echo -e "$(BLUE)→ Running composer update...$(RESET)"
	@docker compose exec php composer update --with-all-dependencies --no-interaction --prefer-dist --optimize-autoloader
	@echo -e "$(GREEN)✓ Composer dependencies updated$(RESET)"

test-compose: up ## Start services and run tests
	@echo -e "$(BLUE)→ Waiting for services to be ready...$(RESET)"
	@sleep 3
	@$(MAKE) --no-print-directory exec-php CMD="make test"

# ==============================================================================
# DIAGNOSTIC & TROUBLESHOOTING
# ==============================================================================

ports: ## Show exposed ports
	@echo -e "$(BOLD)$(CYAN)Exposed Ports$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose ps --format "table {{.Names}}\t{{.Ports}}"
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

inspect-php: ## Inspect PHP container
	@echo -e "$(BOLD)$(CYAN)PHP Container Inspection$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@docker compose exec php php -v
	@echo ""
	@docker compose exec php php -m
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"

env-check: ## Verify environment variables
	@echo -e "$(BOLD)$(CYAN)Environment Configuration$(RESET)"
	@echo -e "$(BLUE)╔════════════════════════════════════════════════════════╗$(RESET)"
	@if [ -f .env ]; then \
		echo -e "$(GREEN)✓ .env file exists$(RESET)"; \
		grep -E '^[A-Z_]+=' .env | head -20; \
	else \
		echo -e "$(RED)✗ .env file not found$(RESET)"; \
		echo -e "$(YELLOW)  Run: cp .env.example .env$(RESET)"; \
	fi
	@echo -e "$(BLUE)╚════════════════════════════════════════════════════════╝$(RESET)"