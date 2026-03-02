<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Contract;

use KaririCode\Devkit\ValueObject\ToolResult;

/**
 * Executes an external quality tool and captures structured output.
 *
 * Handles binary resolution (PHAR-internal → project vendor → global),
 * argument building, and exit-code interpretation. Never modifies
 * config files — consumes whatever `.kcode/` provides.
 *
 * @since 1.0.0
 */
interface ToolRunner
{
    public function toolName(): string;

    /** Whether the tool binary is resolvable in the current environment. */
    public function isAvailable(): bool;

    /** @param list<string> $arguments Extra CLI args forwarded to the tool. */
    public function run(array $arguments = []): ToolResult;
}
