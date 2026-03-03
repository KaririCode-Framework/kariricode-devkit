<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Configuration;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

use KaririCode\Devkit\Contract\ConfigGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use Override;

use const PHP_EOL;

/**
 * Generates `.kcode/composer.json` — the self-contained dev-toolchain manifest.
 *
 * When `kcode init` runs, this file is written to `.kcode/` and then
 * `composer install --working-dir=.kcode/ --no-interaction` is executed
 * by the InitCommand. Tools are installed into `.kcode/vendor/bin/`,
 * keeping the target project's own composer.json free of dev-tool deps.
 *
 * Version constraints come from `devkit.php` → `tools` key (optional).
 * Falls back to KaririCode-certified defaults when not specified.
 *
 * @since 1.0.0
 */
final class KcodeComposerGenerator implements ConfigGenerator
{
    private const array DEFAULT_TOOL_VERSIONS = [
        'phpunit/phpunit' => '^12.5',
        'phpstan/phpstan' => '^2.0',
        'friendsofphp/php-cs-fixer' => '^3.64',
        'rector/rector' => '^2.0',
        'vimeo/psalm' => '^6.0',
    ];

    /** @var array<string, string> Maps devkit.php tool short-names → Composer package names */
    private const array TOOL_SHORT_NAME_MAP = [
        'phpunit' => 'phpunit/phpunit',
        'phpstan' => 'phpstan/phpstan',
        'php-cs-fixer' => 'friendsofphp/php-cs-fixer',
        'rector' => 'rector/rector',
        'psalm' => 'vimeo/psalm',
    ];

    #[Override]
    public function toolName(): string
    {
        return 'kcode-composer';
    }

    #[Override]
    public function outputPath(): string
    {
        return 'composer.json';
    }

    #[Override]
    public function generate(ProjectContext $context): string
    {
        $require = $this->resolveVersions($context->toolVersions);

        $manifest = [
            'name' => 'kariricode/devkit-tools',
            'description' => 'Dev toolchain managed by kcode — do not edit manually.',
            'require' => $require,
            'config' => [
                'bin-compat' => 'full',
                'optimize-autoloader' => true,
                'sort-packages' => true,
                'preferred-install' => 'dist',
                'allow-plugins' => [
                    'infection/extension-installer' => false,
                ],
            ],
            'minimum-stability' => 'stable',
            'prefer-stable' => true,
        ];

        return (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    /**
     * Merge user-supplied version constraints with defaults.
     * User constraints win on conflict; short-names are resolved to package names.
     *
     * @param  array<string, string> $userVersions  From devkit.php → tools
     * @return array<string, string>
     */
    private function resolveVersions(array $userVersions): array
    {
        $resolved = self::DEFAULT_TOOL_VERSIONS;

        foreach ($userVersions as $shortName => $constraint) {
            $package = self::TOOL_SHORT_NAME_MAP[$shortName] ?? $shortName;
            $resolved[$package] = $constraint;
        }

        ksort($resolved);

        return $resolved;
    }
}
