# 🚀 KaririCode DevKit

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Code Quality](https://img.shields.io/badge/Code%20Quality-Level%20Max-brightgreen)](phpstan.neon)
[![Docker](https://img.shields.io/badge/Docker-Supported-blue)](docker-compose.yml)
[![Automated Setup](https://img.shields.io/badge/Setup-Automated-success)](install.sh)

> **Professional development environment for KaririCode Framework components**  
> Fully automated installation, standardized tooling, and production-ready setup for PHP 8.4+ projects

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Requirements](#-requirements)
- [Quick Start](#-quick-start)
- [Usage](#-usage)
- [Available Commands](#-available-commands)
- [Configuration](#-configuration)
- [Best Practices](#-best-practices)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

**KaririCode DevKit** é um ambiente de desenvolvimento profissional e padronizado para todos os componentes do **KaririCode Framework**. Ele fornece:

- **Ambiente containerizado** usando Docker com imagem centralizada
- **Ferramentas de qualidade de código** pré-configuradas (PHPStan, PHP CS Fixer, PHPMD)
- **Makefile profissional** com mais de 50 comandos úteis
- **Configurações consistentes** para garantir qualidade e manutenibilidade
- **Integração CI/CD** pronta para GitHub Actions

### Princípios de Design

- ✅ **SOLID** - Segregação de responsabilidades e inversão de dependência
- ✅ **Clean Code** - Código limpo, legível e manutenível
- ✅ **PSR Compliant** - Segue PSR-4, PSR-12 e PSR-6/16
- ✅ **Strong Typing** - Tipagem forte em PHP 8.1+
- ✅ **Test-Driven** - Estrutura completa para TDD
- ✅ **Performance-First** - Otimizado para produção

---

## ✨ Features

### 🐳 Docker Environment

- Usa imagem centralizada: `kariricode/php-api-stack`
- Redis 7 Alpine para caching
- Memcached 1.6 Alpine para caching distribuído
- Xdebug 3 para debugging (ativação sob demanda)
- Configuração otimizada para desenvolvimento

### 🛠️ Code Quality Tools

| Tool | Version | Purpose | Config File |
|------|---------|---------|-------------|
| **PHPUnit** | 11.4 | Unit & Integration Testing | `phpunit.xml` |
| **PHPStan** | 2.0 | Static Analysis (Level Max) | `phpstan.neon` |
| **PHP CS Fixer** | 3.64 | Code Style (PSR-12+) | `.php-cs-fixer.php` |
| **PHPMD** | 2.15 | Mess Detection | `devkit/.config/phpmd/ruleset.xml` |
| **Rector** | 1.2 | Automated Refactoring | `rector.php` |
| **PHPBench** | 1.3 | Performance Benchmarking | N/A |

### 📊 Coverage & Reports

- **HTML Coverage Report** - Visual coverage analysis
- **Clover XML** - For CI integration
- **JUnit XML** - For CI integration
- **TestDox** - Human-readable test documentation

### 🎨 Editor Integration

- **EditorConfig** - Consistent formatting across editors
- **PHPStorm/VSCode** - Pre-configured settings
- **Git Hooks** - Pre-commit quality checks (optional)

---

## 📦 Requirements

### Essential

- **Docker** >= 20.10
- **Docker Compose** >= 2.0
- **Make** (usually pre-installed on Unix systems)

### Optional (for local development without Docker)

- **PHP** >= 8.3
- **Composer** >= 2.5
- **Redis** (for cache testing)
- **Memcached** (for cache testing)

---

## 🚀 Quick Start

### Step 1: Clone for New Component

```bash
# Clone the DevKit
git clone https://github.com/KaririCode-Framework/kariricode-devkit.git my-component

cd my-component

# Remove DevKit Git history
rm -rf .git

# Initialize new repository
git init
```

### Step 2: Customize the Project

```bash
# Edit composer.json with your component information
nano composer.json

# Create directory structure
mkdir -p src tests/{Unit,Integration}
```

### Step 3: Initialize the Environment

```bash
# Starts containers, installs dependencies
make init
```

### Step 4: Start Developing

```bash
# Access container shell
make shell

# Run tests
make test

# Check code quality
make check
```

---

## 💻 Usage

### Typical Development Flow

```bash
# 1. Start environment
make up

# 2. Install dependencies
make install

# 3. Develop and test continuously
make test          # Run all tests
make test-filter FILTER=MyTest  # Test specific

# 4. Check quality before commit
make qa            # Fix code style + Run tests + Static analysis

# 5. Stop environment
make down
```

### Git Integration

```bash
# Recommended: run before each commit
make qa

# Or configure pre-commit hook (optional)
cat << 'EOF' > .git/hooks/pre-commit
#!/bin/bash
make qa || exit 1
EOF
chmod +x .git/hooks/pre-commit
```

---

## 📖 Available Commands

### Docker Management

```bash
make up              # Start all containers
make down            # Stop all containers
make restart         # Restart containers
make status          # Show container status
make logs            # Show all logs
make logs-php        # Show PHP container logs
make shell           # Access PHP container shell
make shell-root      # Access container as root
```

### Dependency Management

```bash
make install         # Install dependencies
make update          # Update dependencies
make require PKG=vendor/package        # Add package
make require-dev PKG=vendor/package    # Add dev package
make autoload        # Dump autoload
make validate        # Validate composer.json
make outdated        # Show outdated packages
```

### Testing

```bash
make test                    # Run all tests
make test-coverage          # Generate HTML coverage
make test-coverage-text     # Show coverage in terminal
make test-unit              # Run unit tests only
make test-integration       # Run integration tests
make test-filter FILTER=TestName  # Run specific test
```

### Code Quality

```bash
make cs-check        # Check code style (dry-run)
make cs-fix          # Fix code style
make analyse         # Run PHPStan (level max)
make analyse-baseline # Generate PHPStan baseline
make phpmd           # Run PHP Mess Detector
make rector          # Run Rector (dry-run)
make rector-fix      # Apply Rector changes
```

### Comprehensive Checks

```bash
make check           # Run CS check + PHPStan + PHPMD
make fix             # Fix all auto-fixable issues
make qa              # Complete pipeline: fix + test + check
```

### Security

```bash
make security        # Check for vulnerabilities (composer audit)
```

### Cache Operations

```bash
make redis-cli       # Access Redis CLI
make redis-flush     # Flush Redis cache
make redis-info      # Show Redis information
make memcached-stats # Show Memcached statistics
make memcached-flush # Flush Memcached
```

### Utilities

```bash
make clean           # Clean generated files
make reset           # Clean + remove containers
make rebuild         # Complete rebuild from scratch
make permissions     # Fix file permissions
```

### Xdebug

```bash
make xdebug-on       # Enable Xdebug
make xdebug-off      # Disable Xdebug
make xdebug-status   # Show Xdebug status
```

### Information

```bash
make php-version     # Show PHP version
make php-info        # Show PHP configuration
make php-extensions  # List PHP extensions
make composer-version # Show Composer version
make env             # Show environment variables
```

### CI/CD Simulation

```bash
make ci              # Simulate complete CI pipeline
```

### Development Helpers

```bash
make watch-tests     # Watch and run tests on changes
make init            # Initialize new component (up + install)
```

---

## ⚙️ Configuration

### Environment Variables

Create a `.env` file in the project root (optional):

```env
# Project name (affects container names)
COMPOSE_PROJECT_NAME=kariricode_cache

# Xdebug
XDEBUG_MODE=off

# Redis
REDIS_PORT=6379

# Memcached
MEMCACHED_PORT=11211
```

### Tool Customization

#### PHPUnit

Edit `phpunit.xml` to adjust:
- Test suites
- Coverage settings
- Environment variables

#### PHPStan

Edit `phpstan.neon` to:
- Adjust analysis level (not recommended to lower from `max`)
- Add excluded paths
- Ignore specific errors (use sparingly)

#### PHP CS Fixer

Edit `.php-cs-fixer.php` to:
- Customize style rules
- Add exceptions

#### PHPMD

Edit `devkit/.config/phpmd/ruleset.xml` to:
- Adjust complexity limits
- Disable specific rules

### Recommended Directory Structure

```
your-component/
├── devkit/                      # DevKit files (do not commit to component)
│   └── .config/
│       ├── php/
│       │   ├── xdebug.ini
│       │   └── error-reporting.ini
│       └── phpmd/
│           └── ruleset.xml
├── src/                         # Component source code
│   ├── Adapter/
│   ├── Contract/
│   ├── Exception/
│   └── ...
├── tests/                       # Tests
│   ├── Unit/
│   ├── Integration/
│   └── bootstrap.php
├── .editorconfig
├── .gitignore
├── .php-cs-fixer.php
├── composer.json
├── docker-compose.yml
├── Makefile
├── phpstan.neon
├── phpunit.xml
├── README.md
└── LICENSE
```

---

## 🎯 Best Practices

### 1. Always Use Strong Typing

```php
<?php

declare(strict_types=1);

namespace KaririCode\Component;

final class Example
{
    public function __construct(
        private readonly string $name,
        private readonly int $age
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```

### 2. Follow SOLID

```php
// ✅ Good: Dependency Injection
public function __construct(
    private readonly CacheInterface $cache
) {
}

// ❌ Bad: Direct instantiation
public function __construct()
{
    $this->cache = new RedisCache();
}
```

### 3. Write Tests First (TDD)

```php
// tests/Unit/ExampleTest.php
final class ExampleTest extends TestCase
{
    public function testItWorks(): void
    {
        $example = new Example('John', 30);
        
        $this->assertSame('John', $example->getName());
    }
}
```

### 4. Documente Código Complexo

```php
/**
 * Processa dados usando estratégia de cache multinível.
 *
 * Este método implementa cache em camadas com fallback automático.
 * Primeiro tenta L1 (memória), depois L2 (Redis), e finalmente
 * busca da fonte original.
 *
 * Performance characteristics:
 * - Time complexity: O(1) para cache hit
 * - Space complexity: O(n) onde n é o tamanho dos dados
 *
 * @param array<string, mixed> $data Dados a processar
 * @param positive-int         $ttl  Tempo de vida em segundos
 *
 * @return array<string, mixed> Dados processados e cacheados
 *
 * @throws CacheException           Se cache falhar
 * @throws InvalidArgumentException Se dados inválidos
 *
 * @example
 * ```php
 * $result = $cache->process(['key' => 'value'], 3600);
 * ```
 *
 * @see https://kariricode.org/docs/cache/multilayer
 * @since 1.0.0
 */
public function process(array $data, int $ttl): array
{
    // Implementation
}
```

**Padrão de Documentação:**
- ✅ Descrição clara e concisa
- ✅ Explicação detalhada quando necessário
- ✅ Performance characteristics (opcional)
- ✅ Todos os @param com tipos precisos
- ✅ @return com tipo de retorno
- ✅ @throws para todas as exceções
- ✅ @example com código funcional
- ✅ @see para referências externas
- ✅ @since para versionamento

> **PHP CS Fixer está configurado para MANTER toda a documentação!**  
> Diferente de configurações padrão, nossa setup preserva @author, @package,  
> @category, e todos os outros tags importantes.

### 5. Use Make Commands

```bash
# Antes de cada commit
make qa

# Durante desenvolvimento
make test-filter FILTER=MyNewFeature
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. **Containers won't start**

```bash
# Check if ports are in use
docker ps -a

# Remove old containers
make clean
docker system prune -a

# Rebuild
make rebuild
```

#### 2. **File permissions**

```bash
# Fix permissions
make permissions

# Or manually
docker-compose exec -u root php chown -R app:app /var/www
```

#### 3. **Dependencies won't install**

```bash
# Clear Composer cache
docker-compose exec php composer clear-cache

# Reinstall
make clean
make install
```

#### 4. **Xdebug not working**

```bash
# Check if enabled
make xdebug-status

# Enable
make xdebug-on

# Configure IDE for port 9003
# host: localhost
# port: 9003
```

#### 5. **Tests fail on CI but pass locally**

```bash
# Simulate CI environment
make ci

# Check environment differences
docker-compose exec php php -v
docker-compose exec php php -m
```

### Logs and Debug

```bash
# View all logs
make logs

# Specific logs
make logs-php

# Access shell for debugging
make shell
```

---

## 🤝 Contributing

Contributions are welcome! To contribute:

1. **Fork** the repository
2. **Create** a branch for your feature (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Guidelines

- Follow SOLID principles and Clean Code
- Maintain test coverage >= 80%
- Run `make qa` before committing
- Document changes in README if necessary
- Use [Conventional Commits](https://www.conventionalcommits.org/)

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 🔗 Useful Links

- **KaririCode Framework**: https://kariricode.org
- **Documentation**: https://kariricode.org/docs
- **GitHub Organization**: https://github.com/KaririCode-Framework
- **Packagist**: https://packagist.org/packages/kariricode/

---

## 👥 Maintainers

- **Walmir Silva** - [walmir.silva@kariricode.org](mailto:walmir.silva@kariricode.org)

---

## 🙏 Acknowledgments

- PHP-FIG for PSR standards
- Symfony Cache Component for inspiration
- Docker community for containerization best practices
- All contributors to the KaririCode Framework

---

**Built with ❤️ by the KaririCode Team**

*Empowering developers to build robust, maintainable, and professional PHP applications*