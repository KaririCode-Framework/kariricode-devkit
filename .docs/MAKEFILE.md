<div align="center">

# KaririCode DevKit

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php)](https://www.php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker)](https://www.docker.com)
[![Make](https://img.shields.io/badge/Make-Automation-6D00CC?style=for-the-badge&logo=gnu)](https://www.gnu.org/software/make/)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**Professional development environment for KaririCode Framework components**

[Website](https://kariricode.org) | [Documentation](https://kariricode.org/docs) | [GitHub](https://github.com/KaririCode-Framework/kariricode-devkit)

</div>

---

## 📚 Documentation Index

| Document | Scope | Makefile Modules |
|----------|-------|------------------|
| **[Setup & Installation](MAKEFILE-setup.md)** | Dependency management, environment setup | `Makefile.setup.mk` |
| **[Quality Assurance](MAKEFILE-qa.md)** | Testing, linting, static analysis | `Makefile.qa.mk` |
| **[CI/CD Pipelines](MAKEFILE-pipeline.md)** | Orchestrated workflows, pre-commit hooks | `Makefile.orchestration.mk` |
| **[Development Helpers](MAKEFILE-helpers.md)** | Benchmarks, git hooks, release management | `Makefile.helpers.mk` |
| **[Docker Commands](MAKEFILE-docker.md)** | Docker execution, isolated environments | `Makefile.docker-*.mk` |
| **[Docker Compose](MAKEFILE-compose.md)** | Full stack environment management | `Makefile.docker-compose.mk` |

---

## 🚀 Quick Start

### First Time Setup
```bash
# 1. Check prerequisites
make check-php              # Verify PHP 8.4+

# 2. Install dependencies
make install                # Production dependencies
make install-dev            # + Development tools

# 3. Verify installation
make info                   # Show environment info
```

### Daily Development Workflow
```bash
# Local development
make format                 # Auto-fix code style
make test                   # Run tests
make analyse                # Static analysis

# Docker environment
make up                     # Start services
make test-compose           # Integration tests
make down                   # Stop services
```

### CI/CD Integration
```bash
# Fast CI (1-2 min)
make ci                     # Essential checks

# Full CI (3-5 min)
make ci-full                # Complete validation

# Docker CI (isolated)
make docker-ci              # Consistent environment
```

---

## 📖 Architecture Overview

### Module Organization
```
.make/
├── core/                    # 🔧 Shared infrastructure
│   ├── Makefile.variables.mk    # Colors, paths, tools
│   └── Makefile.functions.mk    # Reusable shell functions
│
├── local/                   # 💻 Local development
│   ├── Makefile.setup.mk        # Install, update, clean
│   ├── Makefile.qa.mk           # Test, lint, analyse
│   └── Makefile.helpers.mk      # Bench, hooks, stats
│
├── pipeline/                # 🔄 CI/CD workflows
│   └── Makefile.orchestration.mk # Composed pipelines
│
└── docker/                  # 🐳 Docker execution
    ├── Makefile.docker-core.mk      # Shell, composer, php
    ├── Makefile.docker-compose.mk   # Service lifecycle
    ├── Makefile.docker-qa.mk        # QA in containers
    ├── Makefile.docker-image.mk     # Image management
    └── Makefile.docker-tools.mk     # Utilities
```

### Design Principles

**Single Responsibility Principle (SRP)**
- Each `.mk` file has one clear purpose
- Setup ≠ QA ≠ Docker ≠ Pipeline

**DRY (Don't Repeat Yourself)**
- Shared logic in `core/Makefile.functions.mk`
- Variables centralized in `core/Makefile.variables.mk`

**Composability**
- Complex workflows built from simple targets
- `make ci` = lint + analyse + test
- `make cd` = ci-full + bench + release prep

**Flexibility**
- Local execution: `make test`
- Docker execution: `make docker-test`
- Same interface, different environment

---

## 🎯 Command Categories

### By Frequency

**Every Commit**
```bash
make format                 # Auto-fix style
make lint                   # Syntax check
make test-unit              # Fast tests
```

**Before Push**
```bash
make ci                     # Full local CI
make analyse                # Deep static analysis
```

**Weekly**
```bash
make update                 # Update dependencies
make security               # Security audit
make outdated               # Check for updates
```

**Release**
```bash
make cd                     # Complete validation
make tag VERSION=X.Y.Z      # Create tag
```

### By Environment

**Local Machine**
- Fast iteration
- IDE integration
- Custom configuration

**Docker Container**
- Isolated environment
- Consistent results
- CI/CD simulation

**Docker Compose**
- Full stack (PHP + Redis + Memcached)
- Integration tests
- Service dependencies

---

## 📋 Command Reference

### Essential Commands
```bash
# Help
make help                   # Show all commands
make <module>-help          # Module-specific help

# Setup
make install                # Install dependencies
make clean                  # Clean artifacts
make verify-install         # Verify setup

# Quality
make test                   # Run tests
make analyse                # Static analysis
make format                 # Auto-fix code

# Pipelines
make ci                     # Fast CI
make ci-full                # Complete CI
make pre-commit             # Quick checks

# Docker
make docker-ci              # CI in Docker
make up                     # Start compose
make down                   # Stop compose
```

### Getting Help
```bash
# General help
make help

# Module-specific
make bench-help             # Benchmark parameters

# Debug
make info                   # Environment info
make debug-composer         # Composer config
make env-check              # Docker env vars
```

---

## 🔍 Troubleshooting

### Quick Diagnostics
```bash
# Check environment
make info                   # PHP, Composer, tools
make check-php              # PHP version check

# Verify installation
make verify-install         # Check dependencies

# Docker issues
make docker-info            # Docker environment
make validate-compose       # docker-compose.yml syntax
make logs                   # Service logs
```

### Common Issues

| Issue | Quick Fix | Documentation |
|-------|-----------|---------------|
| Tests not executing | `export XDEBUG_MODE=off` | [MAKEFILE-qa.md](MAKEFILE-qa.md#troubleshooting) |
| Port conflicts | Edit `.env`, change `APP_PORT` | [MAKEFILE-compose.md](MAKEFILE-compose.md#port-conflicts) |
| Composer errors | `make debug-composer` | [MAKEFILE-setup.md](MAKEFILE-setup.md#troubleshooting) |
| PHPStan errors | `make phpstan-baseline` | [MAKEFILE-qa.md](MAKEFILE-qa.md#static-analysis) |

---

## 🎓 Learning Path

### Beginner (Day 1)

1. Read: [Setup & Installation](MAKEFILE-setup.md)
2. Run: `make install && make info`
3. Test: `make test`

### Intermediate (Week 1)

1. Read: [Quality Assurance](MAKEFILE-qa.md)
2. Setup: `make git-hooks-setup`
3. Practice: `make pre-commit` workflow

### Advanced (Month 1)

1. Read: [Docker Compose](MAKEFILE-compose.md)
2. Setup: `make up`
3. Integrate: `make test-compose`

### Expert (Month 3)

1. Read: [CI/CD Pipelines](MAKEFILE-pipeline.md)
2. Customize: Add project-specific targets
3. Optimize: Benchmark and tune

---

## 🤝 Contributing

### Adding New Targets

1. **Choose the right module**
   - Setup related? → `Makefile.setup.mk`
   - Testing related? → `Makefile.qa.mk`
   - Docker related? → `Makefile.docker-*.mk`

2. **Follow conventions**
```makefile
   target-name: ## Description for help
       @echo "$(BLUE)→ Action...$(RESET)"
       # implementation
       @echo "$(GREEN)✓ Success$(RESET)"
```

3. **Document in corresponding .md file**

4. **Test locally and in Docker**
```bash
   make target-name
   make docker-<target-name>  # if applicable
```

### Documentation Standards

- Use **semantic organization**
- Include **working examples**
- Add **troubleshooting sections**
- Maintain **consistent formatting**
- Update **command reference tables**

---

## 📞 Support

### Resources

- **Issues**: Found a bug? [Open an issue](https://github.com/KaririCode-Framework/kariricode-devkit/issues)
- **Discussions**: Questions? [Start a discussion](https://github.com/KaririCode-Framework/kariricode-devkit/discussions)
- **Documentation**: [Full documentation index](#-documentation-index)

### Quick Links

- [Prerequisites](MAKEFILE-setup.md#prerequisites)
- [Installation Guide](MAKEFILE-setup.md#installation)
- [Command Reference](#-command-reference)
- [Troubleshooting](#-troubleshooting)
- [Best Practices](MAKEFILE-pipeline.md#best-practices)

---

**Version**: 1.0.0    
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
