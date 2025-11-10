# VS Code Configuration - KaririCode\Parser

Professional VS Code workspace configuration for KaririCode\Parser development, integrating all quality tools and preserving premium documentation standards.

## 📁 Files Overview

```
.vscode/
├── settings.json           # Workspace settings (tools integration)
├── extensions.json         # Recommended extensions
├── tasks.json             # Makefile integration
├── launch.json            # Debug configurations
├── php.code-snippets      # Documentation snippets
└── README.md              # This file
```

## 🚀 Quick Start

### 1. Install Required Tools

First, ensure your system has PHP 8.4+ and Composer:

```bash
php -v  # Should show 8.4+
composer --version
```

### 2. Install Project Dependencies

```bash
make install
# or manually:
composer install
```

### 3. Install VS Code Extensions

Open VS Code and press `Ctrl+Shift+P` (or `Cmd+Shift+P` on macOS), then:

1. Type: `Extensions: Show Recommended Extensions`
2. Click "Install All" or install individually:
   - **Essential**: Intelephense, PHP-CS-Fixer, PHPStan, Psalm
   - **Highly Recommended**: Better Comments, Error Lens, Todo Tree
   - **Optional**: See `extensions.json` for full list

### 4. Configure XDebug (Optional)

For debugging, install and configure XDebug 3.x:

```bash
# Ubuntu/Debian
sudo apt-get install php8.4-xdebug

# macOS (Homebrew)
brew install php@8.4-xdebug
```

Add to `php.ini` (find with `php --ini`):

```ini
[XDebug]
zend_extension=xdebug.so
xdebug.mode=debug,coverage
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1
xdebug.idekey=VSCODE
```

Restart PHP and verify:

```bash
php -v  # Should show "with Xdebug"
```

## 🛠️ Tool Integration

### PHP-CS-Fixer

**What it does**: Formats PHP code according to PSR-12 and custom rules while **preserving premium comments**.

**How to use**:
- Via Command Palette: `Format Document` (when PHP file is active)
- Via Task: `Ctrl+Shift+B` → "Format Code (PHP-CS-Fixer)"
- Via Makefile: `make format`
- Via Terminal: `vendor/bin/php-cs-fixer fix`

**Important**: Auto-format on save is **disabled** to preserve premium documentation. Format manually when needed.

### PHPStan

**What it does**: Static analysis at maximum strictness level.

**How to use**:
- Automatic: Errors appear in "Problems" panel as you type
- Via Task: `Ctrl+Shift+B` → "Run PHPStan"
- Via Makefile: `make phpstan`
- Via Terminal: `vendor/bin/phpstan analyse`

**Configuration**: `phpstan.neon` (level: max)

### Psalm

**What it does**: Additional static analysis with different heuristics.

**How to use**:
- Automatic: Errors appear alongside PHPStan
- Via Task: `Ctrl+Shift+B` → "Run Psalm"
- Via Makefile: `make psalm`
- Via Terminal: `vendor/bin/psalm`

**Configuration**: `psalm.xml` (errorLevel: 1)

### PHPCS (PHP_CodeSniffer)

**What it does**: Checks PSR-12 compliance with custom rules.

**How to use**:
- Automatic: Warnings appear in "Problems" panel
- Via Task: "Check Coding Standards"
- Via Makefile: `make cs-check`
- Auto-fix: `make cs-fix`

**Configuration**: `phpcs.xml`

### PHPUnit

**What it does**: Runs test suites.

**How to use**:
- Via Test Explorer (sidebar icon)
- Via Task: `Ctrl+Shift+B` → "Run All Tests"
- Via Debug: `F5` → Select "PHPUnit: Debug..."
- Via Makefile: `make test`

**Configuration**: `phpunit.xml`

## ⌨️ Keyboard Shortcuts

### Essential Commands

| Shortcut | Action |
|----------|--------|
| `Ctrl+Shift+B` | Open Build Tasks menu |
| `F5` | Start debugging |
| `Shift+F5` | Stop debugging |
| `F9` | Toggle breakpoint |
| `F10` | Step over |
| `F11` | Step into |
| `Shift+F11` | Step out |
| `Ctrl+Shift+P` | Command Palette |
| `Ctrl+P` | Quick Open File |
| `Ctrl+Shift+F` | Search in Files |

### Custom Tasks (via `Ctrl+Shift+B`)

**Testing**:
- Run All Tests
- Run Unit Tests
- Run Integration Tests
- Generate Coverage Report
- Run Mutation Tests

**Analysis**:
- Run All Analysis
- Run PHPStan
- Run Psalm
- Check Coding Standards

**Formatting**:
- Format Code (PHP-CS-Fixer)
- Fix Coding Standards
- Lint PHP Files

**Quality**:
- Run All Quality Checks
- Run CI Pipeline
- Run Full CI Pipeline
- Run Pre-Commit Checks

**Docker**:
- Docker: Run Tests
- Docker: Run CI
- Docker: Open Shell

## 🐛 Debugging Guide

### Debug Current Test File

1. Open test file (e.g., `tests/Unit/SomeTest.php`)
2. Press `F5`
3. Select "PHPUnit: Debug Current Test File"
4. Set breakpoints with `F9`

### Debug Specific Test Method

1. Select test method name
2. Press `F5`
3. Select "PHPUnit: Debug Current Test Method"

### Debug PHP Script

1. Open PHP file
2. Press `F5`
3. Select "Launch Currently Open Script"

### Listen for XDebug (Browser/CLI)

1. Press `F5`
2. Select "Listen for XDebug"
3. Trigger PHP execution with `XDEBUG_TRIGGER=1`

Example:
```bash
XDEBUG_MODE=debug XDEBUG_TRIGGER=1 php script.php
```

## 📝 Code Snippets

Type these prefixes and press `Tab`:

### Documentation

| Prefix | Description |
|--------|-------------|
| `kc-class-doc` | Premium class documentation (800-1200 lines) |
| `kc-method-doc` | Method documentation with examples |
| `kc-doc` | Simple PHPDoc block |
| `kc-prop-doc` | Property documentation |
| `kc-perf` | Performance annotation |
| `kc-algo` | Algorithm documentation |

### Templates

| Prefix | Description |
|--------|-------------|
| `kc-class` | Class template |
| `kc-interface` | Interface template |
| `kc-trait` | Trait template |
| `kc-enum` | Enum template |
| `kc-exception` | Exception class |
| `kc-test` | Test class template |
| `kc-test-method` | Test method |

### Common Patterns

| Prefix | Description |
|--------|-------------|
| `kc-construct` | Constructor with promoted properties |
| `kc-get` | Getter method |

Example usage:

```php
// Type 'kc-class-doc' and press Tab
/**
 * Brief description of the class
 *
 * Detailed description...
 * [Full premium documentation template]
 */
```

## 🔧 Troubleshooting

### Extensions Not Working

1. Reload VS Code: `Ctrl+Shift+P` → "Developer: Reload Window"
2. Check extension status: `Ctrl+Shift+X`
3. Verify paths in `settings.json`:
   ```json
   "phpstan.path": "${workspaceFolder}/vendor/bin/phpstan"
   ```
4. Ensure Composer dependencies installed: `make install`

### XDebug Not Connecting

1. Verify XDebug installed:
   ```bash
   php -m | grep xdebug
   ```
2. Check XDebug configuration:
   ```bash
   php -i | grep xdebug
   ```
3. Verify port 9003 is not in use:
   ```bash
   netstat -an | grep 9003
   ```
4. Check `launch.json` port matches `php.ini`:
   ```json
   "port": 9003
   ```

### PHPStan/Psalm Errors Not Showing

1. Open "Output" panel (`Ctrl+Shift+U`)
2. Select "PHPStan" or "Psalm" from dropdown
3. Check for error messages
4. Verify cache directory writable:
   ```bash
   mkdir -p var/cache/{phpstan,psalm}
   chmod 755 var/cache/{phpstan,psalm}
   ```

### Format Not Working

1. Ensure PHP-CS-Fixer installed:
   ```bash
   test -f vendor/bin/php-cs-fixer && echo "OK"
   ```
2. Check `.php-cs-fixer.php` exists
3. Run manually to see errors:
   ```bash
   make format
   ```
4. Verify VS Code setting:
   ```json
   "[php]": {
     "editor.defaultFormatter": "junstyle.php-cs-fixer"
   }
   ```

### Tests Not Found in Test Explorer

1. Reload tests: Click refresh icon in Test Explorer
2. Verify PHPUnit config:
   ```bash
   vendor/bin/phpunit --list-tests
   ```
3. Check `phpunit.xml` is valid XML
4. Restart PHP Language Server:
   `Ctrl+Shift+P` → "PHP: Restart Language Server"

## 📚 Additional Resources

### Documentation
- [PHP-CS-Fixer Rules](https://cs.symfony.com/)
- [PHPStan Levels](https://phpstan.org/user-guide/rule-levels)
- [Psalm Error Levels](https://psalm.dev/docs/running_psalm/error_levels/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [XDebug Documentation](https://xdebug.org/docs/)

### KaririCode Standards
- [COMMENTING_GUIDELINES.md](../COMMENTING_GUIDELINES.md) - Premium documentation philosophy
- [Makefile](../Makefile) - Development workflow automation
- [composer.json](../composer.json) - Dependencies and scripts

### VS Code
- [PHP Development](https://code.visualstudio.com/docs/languages/php)
- [Debugging](https://code.visualstudio.com/docs/editor/debugging)
- [Tasks](https://code.visualstudio.com/docs/editor/tasks)

## 🔐 Security Note

**Never commit**:
- `.vscode/*.log` - Debug logs
- `.vscode/settings.local.json` - Personal overrides
- XDebug profiler output

These are already in `.gitignore`.

## 🎯 Pro Tips

1. **Split View Testing**: Open test and source side-by-side with `Ctrl+\`

2. **Quick Test Run**: Use Makefile shortcuts
   ```bash
   make test-unit      # Fast unit tests
   make coverage       # Generate coverage
   make mutation       # Mutation testing
   ```

3. **Multi-cursor Editing**: `Alt+Click` to add cursors, great for batch PHPDoc updates

4. **Search in Tests Only**:
   - Press `Ctrl+Shift+F`
   - Click "..." → "files to include"
   - Enter: `tests/**/*.php`

5. **Format Only Specific File**:
   ```bash
   vendor/bin/php-cs-fixer fix src/Specific/File.php
   ```

6. **Quick CI Check Before Commit**:
   ```bash
   make pre-commit
   ```

7. **Docker Isolation**: Use Docker tasks for CI/CD consistency
   ```bash
   make docker-ci
   ```

8. **Zen Mode**: `Ctrl+K Z` for distraction-free coding

## 🆘 Support

Issues with VS Code configuration? Check:
1. This README first
2. [COMMENTING_GUIDELINES.md](../COMMENTING_GUIDELINES.md)
3. Project issues: https://github.com/KaririCode-Framework/kariricode-parser/issues

---

**Last Updated**: 2025-01-27  
**Version**: 1.0.0  
**Maintainer**: Walmir Silva <walmir.silva@kariricode.org>
