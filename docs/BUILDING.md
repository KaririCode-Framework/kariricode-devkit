# Building kcode.phar

This document covers how to compile `kcode.phar` from source, verify the artifact, and automate releases.

## Prerequisites

| Requirement | Minimum Version | Purpose |
|---|---|---|
| PHP | 8.4+ | Runtime and PHAR compilation |
| Composer | 2.x | Dependency installation |
| humbug/box | 4.x | PHAR compiler |

### Installing humbug/box

Option 1 — Composer global:

```bash
composer global require humbug/box
```

Option 2 — Standalone PHAR:

```bash
wget -O box https://github.com/box-project/box/releases/latest/download/box.phar
chmod +x box
sudo mv box /usr/local/bin/box
```

Verify installation:

```bash
box --version
# humbug/box 4.x.x
```

### PHP Configuration

PHAR compilation requires `phar.readonly=0`. The Makefile passes this automatically via `-d phar.readonly=0`. For manual builds:

```bash
php -d phar.readonly=0 box compile
```

Alternatively, set it in `php.ini`:

```ini
phar.readonly = Off
```

## Building

### Via Makefile (recommended)

```bash
# Full release pipeline: quality checks → build → verify
make release

# Or step by step:
make install      # Install dependencies
make build        # Compile kcode.phar
make verify       # Verify integrity
make self-test    # Run against this project
```

### Manual Build

```bash
# 1. Install dependencies (with dev — quality tools are bundled in the PHAR)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 2. Compile PHAR
php -d phar.readonly=0 box compile --config=box.json

# 3. Verify
php build/kcode.phar --version
php build/kcode.phar --help
```

## Build Output

```
build/
└── kcode.phar        # ~15-20 MB (GZ compressed)
```

The PHAR includes:

| Content | Source |
|---|---|
| Devkit source | `src/` (38 PHP files) |
| Entry point | `bin/kcode` |
| PHPUnit | `vendor/phpunit/` + transitive deps |
| PHPStan | `vendor/phpstan/` + transitive deps |
| PHP-CS-Fixer | `vendor/friendsofphp/` + transitive deps |
| Rector | `vendor/rector/` + transitive deps |
| Psalm | `vendor/vimeo/` + transitive deps |
| Autoloader | `vendor/autoload.php` + `vendor/composer/` |
| License | `LICENSE` |

Test files, documentation, and examples are excluded from the PHAR via the `exclude` and `blacklist` directives in `box.json`.

## box.json Configuration

Key settings:

```json
{
    "main": "bin/kcode",             // Entry point
    "output": "build/kcode.phar",    // Output path
    "compression": "GZ",             // GZ compression (~40% size reduction)
    "chmod": "0755",                 // Executable permission
    "stub": true,                    // Auto-generated stub with shebang
    "alias": "kcode.phar",           // Internal PHAR alias for Phar::running()

    "directories": ["src"],          // Devkit source
    "finder": [{                     // Vendor dependencies
        "name": "*.php",
        "in": ["vendor"],
        "exclude": ["Tests", "tests", "test", "doc", "docs", "examples"]
    }],

    "compactors": [                  // Strip comments/whitespace from PHP files
        "KevinGH\\Box\\Compactor\\Php"
    ]
}
```

## Verification

After building, verify the PHAR works correctly:

```bash
# Version check
php build/kcode.phar --version
# → KaririCode Devkit 1.0.0

# Help output
php build/kcode.phar --help
# → Shows all 10 commands

# Self-test against a real project
cd /path/to/kariricode-component
php /path/to/kcode.phar init
php /path/to/kcode.phar quality
```

### PHAR Signature Verification

Box signs the PHAR with SHA-256 by default. Verify:

```bash
php -r "echo (new Phar('build/kcode.phar'))->getSignature()['hash'];"
```

## Distribution

### GitHub Releases

The recommended distribution method. See `.github/workflows/release.yml`:

1. Tag a release: `git tag v1.0.0 && git push --tags`
2. CI compiles the PHAR and attaches it to the GitHub release.
3. Users download via:

```bash
wget https://github.com/kariricode/devkit/releases/latest/download/kcode.phar
chmod +x kcode.phar
sudo mv kcode.phar /usr/local/bin/kcode
```

### Self-Update (Future)

A `kcode self-update` command is planned for v1.1 to download the latest PHAR from GitHub releases.

## Troubleshooting

### `phar.readonly = On`

```
Creating a phar archive is disabled by the php.ini setting phar.readonly
```

**Fix:** Pass `-d phar.readonly=0` to PHP or set `phar.readonly = Off` in php.ini.

### Box not found

```
✗ humbug/box not found.
```

**Fix:** Install box globally (see Prerequisites above).

### PHAR too large

If the PHAR exceeds 30 MB:

1. Verify `exclude` in box.json filters out test files.
2. Check that `compression: "GZ"` is set.
3. Run `box info build/kcode.phar` to inspect contents.

### Binary not found inside PHAR

If `kcode.phar test` reports "Binary not found for phpunit":

1. Verify dependencies were installed before building: `composer install`
2. Check that `vendor/bin/phpunit` exists in the project before compilation.
3. Run `box info --list build/kcode.phar | grep phpunit` to verify inclusion.

### Platform-specific issues

The PHAR uses `#!/usr/bin/env php` as the shebang. On systems where PHP is not in PATH:

```bash
php kcode.phar quality    # Explicit PHP invocation
```

## Version Bumping

The version is stored in two places — keep them in sync:

1. `src/Core/Devkit.php` → `private const string VERSION = '1.0.0';`
2. `box.json` → `metadata.version`

The Makefile resolves the version via `git describe --tags --abbrev=0` first, falling back to `box.json` metadata, then `'dev'`. Always tag releases with `git tag vX.Y.Z` before running `make release`.
