<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Contract;

use KaririCode\Devkit\Core\ProjectContext;

/**
 * Generates a tool-specific configuration file for `.kcode/`.
 *
 * Each implementation targets a single tool (PHPUnit, PHPStan, etc.)
 * and produces deterministic output from the project context.
 * Manual edits go in `devkit.php` (project root) overrides — generated
 * files are regenerated on every `kcode init`.
 *
 * @since 1.0.0
 */
interface ConfigGenerator
{
    /** Filesystem-safe identifier (e.g. "phpunit", "phpstan"). */
    public function toolName(): string;

    /** Relative path inside `.kcode/` for the generated file. */
    public function outputPath(): string;

    /** Render config content for the given project context. */
    public function generate(ProjectContext $context): string;
}
