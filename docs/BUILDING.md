# Building kcode.phar

This document covers how to compile `kcode.phar` from source, verify the artifact, and automate releases.

## Prerequisites

| Requirement | Minimum Version | Purpose |
|---|---|---|
| PHP | 8.4+ | Runtime and PHAR compilation |
| Composer | 2.x | Dependency installation |

### PHP Configuration

PHAR compilation requires `phar.readonly=0`. The Makefile and `bin/build-phar.php` pass this automatically. For manual builds:

```bash
php -d phar.readonly=0 bin/build-phar.php
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
# 1. Install dependencies (dev tools are bundled in the PHAR)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 2. Compile PHAR using the native builder
php -d phar.readonly=0 bin/build-phar.php

# 3. Verify
php build/kcode.phar --version
php build/kcode.phar --help
```

## PHAR Builder — `bin/build-phar.php`

The project uses a native PHP PHAR builder (`bin/build-phar.php`) instead of `humbug/box`.

**Why:** Box 4.x has a known compatibility issue with PHP 8.4 (`chdir(): Not a directory (errno 20)` during `endBuffering()`). The native builder avoids this bug entirely with no external dependency.

**What it does:**
1. Collects all `src/` PHP files (38 files)
2. Collects `vendor/` PHP + JSON files, excluding test/doc directories
3. Adds `LICENSE`
4. Sets `bin/kcode` as the entry-point stub
5. GZ-compresses the archive
6. Sets permissions to `0755`

```bash
# Output
📦 Building kcode.phar...
  + src/: 38 PHP files
  + vendor/: <N> files
  + LICENSE
  + stub (bin/kcode entry point)

✅ Built: build/kcode.phar (X.XX MB)
   Files: <total>
```

## Build Output

```
build/
└── kcode.phar        # GZ compressed PHAR
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

### PHAR Signature

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

### PHAR too large

If the PHAR exceeds 30 MB:

1. Check that GZ compression is enabled in `bin/build-phar.php` (`Phar::GZ`).
2. Verify test/doc directories are excluded by the builder's `$excludeDirs` list.

### Binary not found inside PHAR

If `kcode.phar test` reports "Binary not found for phpunit":

1. Verify dependencies were installed before building: `composer install`
2. Check that `vendor/bin/phpunit` exists before compilation.

### Platform-specific issues

The PHAR uses `#!/usr/bin/env php` as the shebang. On systems where PHP is not in PATH:

```bash
php kcode.phar quality    # Explicit PHP invocation
```

## Version Bumping

The version is stored in one place:

1. `src/Core/Devkit.php` → `private const string VERSION = '1.0.0';`

The Makefile resolves the version via `git describe --tags --abbrev=0`, falling back to `'dev'`. Always tag releases with `git tag vX.Y.Z` before running `make release`.
