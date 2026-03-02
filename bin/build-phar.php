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

// ── 2. Minimal PSR-4 autoloader (no Composer vendor/ needed) ─
// The project has zero PHP production dependencies.
// We generate a lean autoloader that maps KaririCode\Devkit → src/.
$autoloader = <<<'PHP'
<?php
spl_autoload_register(static function (string $class): void {
    $prefix = 'KaririCode\\Devkit\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = 'phar://kcode.phar/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
PHP;
$phar['vendor/autoload.php'] = $autoloader;
echo "  + vendor/autoload.php: inline PSR-4 autoloader\n";


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
