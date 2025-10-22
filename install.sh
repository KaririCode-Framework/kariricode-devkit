#!/bin/bash

################################################################################
# KaririCode DevKit - Professional Component Installer
################################################################################
# This script automates the setup of a new KaririCode Framework component
# with a fully configured development environment.
#
# Usage: ./install.sh
################################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Emoji support
CHECK="✓"
CROSS="✗"
ARROW="→"
ROCKET="🚀"
PACKAGE="📦"
WRENCH="🔧"
TEST="🧪"
CLEAN="🧹"

################################################################################
# UTILITY FUNCTIONS
################################################################################

print_header() {
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║     KaririCode DevKit - Component Installation Wizard         ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step() {
    echo -e "${BLUE}${ARROW}${NC} $1"
}

print_success() {
    echo -e "${GREEN}${CHECK}${NC} $1"
}

print_error() {
    echo -e "${RED}${CROSS}${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}!${NC} $1"
}

prompt_input() {
    local prompt="$1"
    local var_name="$2"
    local default="$3"
    
    if [ -n "$default" ]; then
        read -p "$(echo -e ${BLUE}${prompt}${NC} [${default}]: )" input
        eval $var_name="${input:-$default}"
    else
        read -p "$(echo -e ${BLUE}${prompt}${NC}: )" input
        eval $var_name="$input"
    fi
}

prompt_confirm() {
    local prompt="$1"
    read -p "$(echo -e ${YELLOW}${prompt}${NC} [y/N]: )" -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]]
}

validate_namespace() {
    local namespace="$1"
    if [[ ! "$namespace" =~ ^[A-Z][a-zA-Z0-9]*$ ]]; then
        return 1
    fi
    return 0
}

################################################################################
# MAIN INSTALLATION STEPS
################################################################################

collect_information() {
    print_step "Collecting component information..."
    echo ""
    
    # Component Name
    while true; do
        prompt_input "Component name (e.g., Cache, Validator, Router)" COMPONENT_NAME
        
        if [ -z "$COMPONENT_NAME" ]; then
            print_error "Component name cannot be empty"
            continue
        fi
        
        if ! validate_namespace "$COMPONENT_NAME"; then
            print_error "Name must start with uppercase letter and contain only letters and numbers"
            continue
        fi
        
        break
    done
    
    # Convert to lowercase for package name
    PACKAGE_NAME=$(echo "$COMPONENT_NAME" | sed 's/\([A-Z]\)/-\1/g' | sed 's/^-//' | tr '[:upper:]' '[:lower:]')
    
    # Description
    prompt_input "Component description" COMPONENT_DESCRIPTION "Professional ${COMPONENT_NAME} implementation for KaririCode Framework"
    
    # Author information
    prompt_input "Author name" AUTHOR_NAME "Walmir Silva"
    prompt_input "Author email" AUTHOR_EMAIL "walmir.silva@kariricode.org"
    
    # PHP Version
    prompt_input "Minimum PHP version" PHP_VERSION "8.4"
    
    echo ""
    print_success "Information collected successfully!"
    echo ""
    echo -e "${BLUE}Component:${NC} $COMPONENT_NAME"
    echo -e "${BLUE}Package:${NC} kariricode/$PACKAGE_NAME"
    echo -e "${BLUE}Namespace:${NC} KaririCode\\$COMPONENT_NAME"
    echo -e "${BLUE}Description:${NC} $COMPONENT_DESCRIPTION"
    echo ""
    
    if ! prompt_confirm "Confirm the information above?"; then
        print_error "Installation cancelled by user"
        exit 0
    fi
}

create_directory_structure() {
    print_step "Creating directory structure..."
    
    mkdir -p src/{Adapter,Contract,Exception,Factory}
    print_success "Created: src/ with subdirectories"
    
    mkdir -p tests/{Unit,Integration,Fixtures}
    print_success "Created: tests/ with subdirectories"
    
    mkdir -p docs
    print_success "Created: docs/"
    
    # Create .gitkeep files
    touch src/.gitkeep
    touch tests/Unit/.gitkeep
    touch tests/Integration/.gitkeep
    touch tests/Fixtures/.gitkeep
    
    print_success "Directory structure created"
}

generate_composer_json() {
    print_step "Generating composer.json..."
    
    cat > composer.json <<EOF
{
    "name": "kariricode/${PACKAGE_NAME}",
    "description": "${COMPONENT_DESCRIPTION}",
    "type": "library",
    "keywords": [
        "kariricode",
        "framework",
        "${PACKAGE_NAME}",
        "php",
        "php8",
        "solid",
        "clean-code",
        "design-patterns"
    ],
    "license": "MIT",
    "authors": [
        {
            "name": "${AUTHOR_NAME}",
            "email": "${AUTHOR_EMAIL}",
            "homepage": "https://kariricode.org",
            "role": "Developer"
        }
    ],
    "homepage": "https://github.com/KaririCode-Framework/kariricode-${PACKAGE_NAME}",
    "support": {
        "issues": "https://github.com/KaririCode-Framework/kariricode-${PACKAGE_NAME}/issues",
        "source": "https://github.com/KaririCode-Framework/kariricode-${PACKAGE_NAME}",
        "docs": "https://kariricode.org/docs/${PACKAGE_NAME}"
    },
    "require": {
        "php": "^${PHP_VERSION}",
        "psr/simple-cache": "^3.0",
        "kariricode/contract": "^1.0"
    },
    "require-dev": {
        "phpunit/phpunit": "12.4.1",
        "friendsofphp/php-cs-fixer": "^3.64",
        "phpstan/phpstan": "^2.0",
        "phpstan/phpstan-strict-rules": "^2.0",
        "phpmd/phpmd": "^2.15",
        "rector/rector": "2.2.3"
    },
    "autoload": {
        "psr-4": {
            "KaririCode\\\\${COMPONENT_NAME}\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "KaririCode\\\\${COMPONENT_NAME}\\\\Tests\\\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "test-coverage": "phpunit --coverage-html=coverage",
        "cs-check": "php-cs-fixer fix --dry-run --diff --verbose",
        "cs-fix": "php-cs-fixer fix",
        "analyse": "phpstan analyse src tests --level=max",
        "phpmd": "phpmd src text devkit/.config/phpmd/ruleset.xml",
        "rector": "rector process --dry-run",
        "rector-fix": "rector process",
        "benchmark": "phpbench run --report=default",
        "qa": [
            "@cs-fix",
            "@test",
            "@analyse",
            "@phpmd"
        ]
    },
    "scripts-descriptions": {
        "test": "Run unit and integration tests",
        "test-coverage": "Run tests with HTML coverage report",
        "cs-check": "Check code style (dry-run)",
        "cs-fix": "Fix code style issues automatically",
        "analyse": "Run static analysis with PHPStan",
        "phpmd": "Run PHP Mess Detector",
        "rector": "Check for automated refactoring opportunities",
        "rector-fix": "Apply automated refactoring",
        "benchmark": "Run performance benchmarks",
        "qa": "Run complete quality assurance pipeline"
    },
    "config": {
        "sort-packages": true,
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "allow-plugins": {
            "php-http/discovery": true
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
EOF
    
    print_success "composer.json generated successfully"
}

generate_readme() {
    print_step "Generating README.md..."
    
    cat > README.md <<EOF
# KaririCode ${COMPONENT_NAME}

[![PHP Version](https://img.shields.io/badge/PHP-${PHP_VERSION}%2B-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Code Quality](https://img.shields.io/badge/Code%20Quality-Level%20Max-brightgreen)](phpstan.neon)

${COMPONENT_DESCRIPTION}

## Installation

\`\`\`bash
composer require kariricode/${PACKAGE_NAME}
\`\`\`

## Usage

\`\`\`php
<?php

use KaririCode\\${COMPONENT_NAME}\\${COMPONENT_NAME};

// Example usage will be documented here
\`\`\`

## Development

### Requirements

- Docker & Docker Compose
- Make

### Setup

\`\`\`bash
# Start environment
make up

# Install dependencies
make install

# Run tests
make test

# Check code quality
make qa
\`\`\`

## Testing

\`\`\`bash
# Run all tests
make test

# Run with coverage
make test-coverage

# Run specific test
make test-filter FILTER=TestClassName
\`\`\`

## Code Quality

\`\`\`bash
# Fix code style
make cs-fix

# Run static analysis
make analyse

# Run mess detector
make phpmd

# Complete QA pipeline
make qa
\`\`\`

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: https://kariricode.org/docs/${PACKAGE_NAME}
- **Issues**: https://github.com/KaririCode-Framework/kariricode-${PACKAGE_NAME}/issues
- **Community**: https://kariricode.org/community

## Credits

Developed and maintained by ${AUTHOR_NAME} and the KaririCode Team.

Built with ❤️ by the KaririCode Team
EOF
    
    print_success "README.md generated"
}

update_env_file() {
    print_step "Configuring .env file..."
    
    cat > .env <<EOF
# KaririCode ${COMPONENT_NAME} - Environment Configuration

# Project
COMPOSE_PROJECT_NAME=kariricode_${PACKAGE_NAME}

# PHP
PHP_VERSION=${PHP_VERSION}

# Xdebug
XDEBUG_MODE=off

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Memcached
MEMCACHED_HOST=memcached
MEMCACHED_PORT=11211
EOF
    
    print_success ".env file configured"
}

start_docker_environment() {
    print_step "Starting Docker environment..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed"
        return 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed"
        return 1
    fi
    
    docker-compose up -d
    
    # Wait for containers to be ready
    print_step "Waiting for containers to be ready..."
    sleep 5
    
    print_success "Docker environment started"
}

install_dependencies() {
    print_step "Installing Composer dependencies..."
    
    docker-compose exec -T php composer install --no-interaction --prefer-dist
    
    print_success "Dependencies installed"
}

run_tests() {
    print_step "Running environment tests..."
    
    # Test PHP version
    print_step "Checking PHP version..."
    docker-compose exec -T php php -v
    
    # Test Composer
    print_step "Checking Composer..."
    docker-compose exec -T php composer --version
    
    # Test autoload
    print_step "Testing autoload..."
    docker-compose exec -T php composer dump-autoload
    
    print_success "Environment tests completed"
}

run_quality_checks() {
    print_step "Running quality checks..."
    
    # Validate composer.json
    print_step "Validating composer.json..."
    docker-compose exec -T php composer validate --strict
    
    # Check PHP syntax
    print_step "Checking PHP syntax..."
    docker-compose exec -T php find src -name "*.php" -print0 | xargs -0 -n1 php -l
    
    print_success "Quality checks completed"
}

run_installation_checklist() {
    print_step "Running installation checklist..."
    echo ""
    
    local all_passed=true
    
    # Check directories
    echo -e "${BLUE}Checking directory structure:${NC}"
    for dir in src tests docs devkit; do
        if [ -d "$dir" ]; then
            print_success "Directory $dir exists"
        else
            print_error "Directory $dir not found"
            all_passed=false
        fi
    done
    echo ""
    
    # Check configuration files
    echo -e "${BLUE}Checking configuration files:${NC}"
    for file in composer.json docker-compose.yml Makefile phpunit.xml phpstan.neon .php-cs-fixer.php; do
        if [ -f "$file" ]; then
            print_success "File $file exists"
        else
            print_error "File $file not found"
            all_passed=false
        fi
    done
    echo ""
    
    # Check Docker containers
    echo -e "${BLUE}Checking Docker containers:${NC}"
    if docker-compose ps | grep -q "Up"; then
        print_success "Containers are running"
    else
        print_error "Containers are not running"
        all_passed=false
    fi
    echo ""
    
    # Check vendor directory
    echo -e "${BLUE}Checking dependencies:${NC}"
    if [ -d "vendor" ]; then
        print_success "Dependencies installed"
    else
        print_error "Dependencies not installed"
        all_passed=false
    fi
    echo ""
    
    if $all_passed; then
        print_success "All checks passed!"
        return 0
    else
        print_error "Some checks failed"
        return 1
    fi
}

cleanup_installation_files() {
    print_step "Removing installation files..."
    
    # Remove installation files
    rm -f install.sh
    print_success "Removed: install.sh"
    
    # Remove DevKit .git if exists
    if [ -d ".git" ]; then
        rm -rf .git
        print_success "Removed: .git (DevKit history)"
    fi
    
    # Remove template files
    rm -f .github/workflows/devkit-*.yml 2>/dev/null || true
    
    print_success "Installation files removed"
}

initialize_git_repository() {
    print_step "Initializing Git repository..."
    
    git init
    git add .
    git commit -m "feat: initial commit - KaririCode ${COMPONENT_NAME} component

- Setup development environment
- Configure quality tools
- Add basic project structure

Generated by KaririCode DevKit"
    
    print_success "Git repository initialized"
}

display_next_steps() {
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║          Installation Completed Successfully! ${ROCKET}                 ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}${PACKAGE} Component:${NC} KaririCode ${COMPONENT_NAME}"
    echo -e "${BLUE}${PACKAGE} Namespace:${NC} KaririCode\\${COMPONENT_NAME}"
    echo -e "${BLUE}${PACKAGE} Package:${NC} kariricode/${PACKAGE_NAME}"
    echo ""
    echo -e "${YELLOW}Next steps:${NC}"
    echo ""
    echo -e "  ${ARROW} Access container shell:"
    echo -e "    ${GREEN}make shell${NC}"
    echo ""
    echo -e "  ${ARROW} Create your classes in ${BLUE}src/${NC}"
    echo -e "    ${GREEN}src/Contract/${COMPONENT_NAME}Interface.php${NC}"
    echo -e "    ${GREEN}src/${COMPONENT_NAME}.php${NC}"
    echo ""
    echo -e "  ${ARROW} Write tests in ${BLUE}tests/${NC}"
    echo -e "    ${GREEN}tests/Unit/${COMPONENT_NAME}Test.php${NC}"
    echo ""
    echo -e "  ${ARROW} Run tests:"
    echo -e "    ${GREEN}make test${NC}"
    echo ""
    echo -e "  ${ARROW} Check code quality:"
    echo -e "    ${GREEN}make qa${NC}"
    echo ""
    echo -e "  ${ARROW} See all available commands:"
    echo -e "    ${GREEN}make help${NC}"
    echo ""
    echo -e "${BLUE}Documentation:${NC} https://kariricode.org/docs"
    echo -e "${BLUE}GitHub:${NC} https://github.com/KaririCode-Framework"
    echo ""
    echo -e "${GREEN}Happy coding! ${ROCKET}${NC}"
    echo ""
}

################################################################################
# MAIN EXECUTION
################################################################################

main() {
    clear
    print_header
    
    # Step 1: Collect information
    collect_information
    
    # Step 2: Create directory structure
    create_directory_structure
    
    # Step 3: Generate composer.json
    generate_composer_json
    
    # Step 4: Generate README
    generate_readme
    
    # Step 5: Update .env
    update_env_file
    
    # Step 6: Start Docker environment
    if ! start_docker_environment; then
        print_error "Falha ao iniciar ambiente Docker"
        exit 1
    fi
    
    # Step 7: Install dependencies
    install_dependencies
    
    # Step 8: Run tests
    run_tests
    
    # Step 9: Run quality checks
    run_quality_checks
    
    # Step 10: Run checklist
    if ! run_installation_checklist; then
        print_warning "Some checks failed, but installation can continue"
    fi
    
    # Step 11: Cleanup
    cleanup_installation_files
    
    # Step 12: Initialize Git
    initialize_git_repository
    
    # Step 13: Display next steps
    display_next_steps
}

# Run main function
main "$@"