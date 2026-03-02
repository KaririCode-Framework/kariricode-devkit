<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Core;

use KaririCode\Devkit\Contract\ConfigGenerator;
use KaririCode\Devkit\Contract\ToolRunner;
use KaririCode\Devkit\Exception\DevkitException;
use KaririCode\Devkit\ValueObject\QualityReport;
use KaririCode\Devkit\ValueObject\ToolResult;

/**
 * Top-level orchestrator for all devkit operations.
 *
 * Wired in `bin/kcode`. Holds config generators, tool runners,
 * and the project context. Every public method is a high-level
 * operation exposed through the CLI.
 *
 * @since 1.0.0
 */
final class Devkit
{
    private const string VERSION = '1.0.0';

    private ?ProjectContext $context = null;

    /** @var array<string, ConfigGenerator> */
    private array $generators = [];

    /** @var array<string, ToolRunner> */
    private array $runners = [];

    public function __construct(
        private readonly ProjectDetector $detector,
    ) {
    }

    public static function version(): string
    {
        return self::VERSION;
    }

    // ── Registration ──────────────────────────────────────────────

    public function addGenerator(ConfigGenerator $generator): void
    {
        $this->generators[$generator->toolName()] = $generator;
    }

    public function addRunner(ToolRunner $runner): void
    {
        $this->runners[$runner->toolName()] = $runner;
    }

    // ── Context ───────────────────────────────────────────────────

    public function context(string $workingDirectory = '.'): ProjectContext
    {
        return $this->context ??= $this->detector->detect(
            \realpath($workingDirectory) ?: $workingDirectory,
        );
    }

    // ── Init ──────────────────────────────────────────────────────

    /** Generate all config files inside `.kcode/`. Returns file count. */
    public function init(string $workingDirectory = '.'): int
    {
        $ctx = $this->context($workingDirectory);
        $this->ensureDirectories($ctx);

        $count = 0;

        foreach ($this->generators as $generator) {
            $path = $ctx->configPath($generator->outputPath());

            $dir = \dirname($path);
            if (!\is_dir($dir)) {
                \mkdir($dir, 0755, true);
            }

            \file_put_contents($path, $generator->generate($ctx));
            ++$count;
        }

        $this->appendGitIgnore($ctx);

        return $count;
    }

    // ── Run ───────────────────────────────────────────────────────

    /** @param list<string> $arguments */
    public function run(string $toolName, array $arguments = []): ToolResult
    {
        $runner = $this->runners[$toolName] ?? null;

        if (null === $runner) {
            throw new DevkitException(\sprintf(
                'Unknown tool "%s". Available: %s',
                $toolName,
                \implode(', ', \array_keys($this->runners)),
            ));
        }

        return $runner->run($arguments);
    }

    /** Check if a tool runner is registered and its binary is available. */
    public function isToolAvailable(string $toolName): bool
    {
        return isset($this->runners[$toolName]) && $this->runners[$toolName]->isAvailable();
    }

    // ── Quality Pipeline ──────────────────────────────────────────

    /**
     * Full quality pipeline: cs-check → analyse → test.
     *
     * Skips unavailable tools instead of failing.
     *
     * @param list<string> $onlyTools Restrict to these tools (empty = all).
     */
    public function quality(array $onlyTools = []): QualityReport
    {
        $pipeline = [] !== $onlyTools
            ? $onlyTools
            : ['cs-fixer', 'phpstan', 'psalm', 'phpunit'];

        $results = [];

        foreach ($pipeline as $tool) {
            if (!$this->isToolAvailable($tool)) {
                continue;
            }

            $extraArgs = match ($tool) {
                'cs-fixer' => ['--dry-run', '--diff'],
                'rector'   => ['--dry-run'],
                default    => [],
            };

            $results[] = $this->run($tool, $extraArgs);
        }

        return new QualityReport($results);
    }

    // ── Clean ─────────────────────────────────────────────────────

    public function clean(string $workingDirectory = '.'): void
    {
        $buildDir = $this->context($workingDirectory)->buildDir;

        if (\is_dir($buildDir)) {
            $this->removeRecursive($buildDir);
        }

        \mkdir($buildDir, 0755, true);
    }

    /** @return list<string> */
    public function registeredTools(): array
    {
        return \array_keys($this->runners);
    }

    // ── Internals ─────────────────────────────────────────────────

    private function ensureDirectories(ProjectContext $ctx): void
    {
        foreach ([$ctx->devkitDir, $ctx->buildDir] as $dir) {
            if (!\is_dir($dir) && !\mkdir($dir, 0755, true)) {
                throw DevkitException::directoryNotWritable($dir);
            }
        }
    }

    private function appendGitIgnore(ProjectContext $ctx): void
    {
        $gitignore = $ctx->projectRoot . \DIRECTORY_SEPARATOR . '.gitignore';
        $entry = '.kcode/';

        // Create .gitignore if it doesn't exist
        if (!\is_file($gitignore)) {
            \file_put_contents(
                $gitignore,
                '# KaririCode Devkit — generated configs and build artifacts' . \PHP_EOL
                . $entry . \PHP_EOL,
            );

            return;
        }

        $content = \file_get_contents($gitignore);

        // Already covered
        if (\str_contains($content, $entry)) {
            return;
        }

        // Migrate: if old .kcode/build/ entry exists, replace with .kcode/
        $legacyEntry = '.kcode/build/';
        if (\str_contains($content, $legacyEntry)) {
            $content = \str_replace($legacyEntry, $entry, $content);
            \file_put_contents($gitignore, $content);

            return;
        }

        \file_put_contents(
            $gitignore,
            \PHP_EOL . '# KaririCode Devkit — generated configs and build artifacts' . \PHP_EOL
            . $entry . \PHP_EOL,
            \FILE_APPEND,
        );
    }

    private function removeRecursive(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? \rmdir($item->getPathname()) : \unlink($item->getPathname());
        }

        \rmdir($dir);
    }
}
