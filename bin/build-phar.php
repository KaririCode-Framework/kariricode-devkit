<?php
declare(strict_types=1);

/**
 * Manual PHAR builder for kcode — bypasses Box chdir() bug on PHP 8.4
 */

$root    = dirname(__DIR__);
$output  = $root . '/build/kcode.phar';

if (file_exists($output)) {
    unlink($output);
}

@mkdir($root . '/build', 0755, true);

$phar = new Phar($output, 0, 'kcode.phar');
$phar->startBuffering();

echo "📦 Building kcode.phar...\n";

// ── 1. Add src/ ────────────────────────────────────────────
$added = 0;
$srcDir = $root . '/src';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relative = 'src/' . $it->getSubPathname();
        $phar[$relative] = file_get_contents($file->getPathname());
        $added++;
    }
}
echo "  + src/: $added PHP files\n";

// ── 2. Add vendor/composer/ autoload files only ────────────
// No PHP production dependencies — only the generated autoloader is needed.
$autoloadFiles = [
    'vendor/autoload.php',
    'vendor/composer/autoload_classmap.php',
    'vendor/composer/autoload_namespaces.php',
    'vendor/composer/autoload_psr4.php',
    'vendor/composer/autoload_real.php',
    'vendor/composer/autoload_static.php',
    'vendor/composer/ClassLoader.php',
    'vendor/composer/platform_check.php',
];

$vendorAdded = 0;
foreach ($autoloadFiles as $rel) {
    $abs = $root . '/' . $rel;
    if (file_exists($abs)) {
        $phar[$rel] = file_get_contents($abs);
        $vendorAdded++;
    }
}
echo "  + vendor/composer/: $vendorAdded autoload files\n";


// ── 3. Add LICENSE ──────────────────────────────────────────
$phar['LICENSE'] = file_get_contents($root . '/LICENSE');
echo "  + LICENSE\n";

// ── 4. Set bin/kcode as the entry point (stub) ─────────────
$kcodeContent = file_get_contents($root . '/bin/kcode');
$phar['bin/kcode'] = $kcodeContent;

$stub = <<<'STUB'
#!/usr/bin/env php
<?php
/*
 * KaririCode Devkit — kcode
 *
 * Unified quality toolchain for KaririCode Framework.
 *
 * (c) Walmir Silva <walmir.silva@kariricode.org>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
Phar::mapPhar('kcode.phar');
require 'phar://kcode.phar/bin/kcode';
__HALT_COMPILER();
STUB;

$phar->setStub($stub);
echo "  + stub (bin/kcode entry point)\n";

// ── 5. Finalize ─────────────────────────────────────────────
$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);
chmod($output, 0755);

$size = round(filesize($output) / 1024 / 1024, 2);
echo "\n✅ Built: $output ($size MB)\n";
echo "   Files: " . count($phar) . "\n";
