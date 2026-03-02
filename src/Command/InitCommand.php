<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Command;

use KaririCode\Devkit\Core\Devkit;
use KaririCode\Devkit\Core\MigrationDetector;

/**
 * Generates all config files inside `.kcode/`.
 *
 * With `--config`, scaffolds a `devkit.php` override file in the project root.
 *
 * @since 1.0.0
 */
final class InitCommand extends AbstractCommand
{
    #[\Override]
    public function name(): string
    {
        return 'init';
    }

    #[\Override]
    public function description(): string
    {
        return 'Generate .kcode/ configs (--config to scaffold devkit.php)';
    }

    #[\Override]
    public function execute(Devkit $devkit, array $arguments): int
    {
        $this->banner('KaririCode Devkit — Init');

        $context = $devkit->context();
        $this->info("Project: {$context->projectName}");
        $this->info("Namespace: {$context->namespace}");
        $this->info("PHP: {$context->phpVersion}");

        $count = $devkit->init();

        $this->line();
        $this->info("Generated {$count} config file(s) in .kcode/");
        $this->info(".kcode/ added to .gitignore (regenerate with kcode init)");

        // Scaffold devkit.php if requested
        if ($this->hasFlag($arguments, '--config')) {
            $this->scaffoldDevkitConfig($context->projectRoot);
        }

        // Hint: detect redundant root-level configs and dev dependencies
        $detector = new MigrationDetector();
        $migration = $detector->detect($context->projectRoot);

        if ($migration->hasRedundancies) {
            $this->line();
            $this->warning(\sprintf(
                'Found %d redundant item(s) that kcode replaces.',
                $migration->totalItems,
            ));
            $this->line('  Run \033[1mkcode migrate\033[0m to review and clean up.');
        }

        return 0;
    }

    private function scaffoldDevkitConfig(string $projectRoot): void
    {
        $configPath = $projectRoot . \DIRECTORY_SEPARATOR . 'devkit.php';

        if (is_file($configPath)) {
            $this->warning('devkit.php already exists — skipping scaffold.');

            return;
        }

        $content = <<<'PHP_WRAP'
            <?php

            declare(strict_types=1);

            /**
             * KaririCode Devkit — Project Overrides
             *
             * This file customizes the devkit behavior for this project.
             * Only uncomment the keys you need to change — unset keys use
             * auto-detected values from composer.json + KaririCode defaults.
             *
             * Merge semantics:
             *   - cs_fixer_rules → MERGED with KaririCode defaults (your rules win on conflict)
             *   - rector_sets    → REPLACES KaririCode defaults entirely
             *   - All others     → REPLACES the auto-detected value
             *
             * After editing, run: kcode init
             *
             * @see https://github.com/kariricode/devkit
             */

            return [
                // ── Project Identity ──────────────────────────────────────
                // 'project_name'  => 'kariricode/my-component',
                // 'namespace'     => 'KaririCode\\MyComponent',

                // ── PHP Version ───────────────────────────────────────────
                // 'php_version'   => '8.4',

                // ── Static Analysis ───────────────────────────────────────
                // 'phpstan_level' => 9,       // 0–9 (default: 9)
                // 'psalm_level'   => 3,       // 1–9 (default: 3)

                // ── Directories ───────────────────────────────────────────
                // 'source_dirs'   => ['src'],
                // 'test_dirs'     => ['tests'],
                // 'exclude_dirs'  => ['src/Contract'],  // excluded from static analysis

                // ── Test Suites ───────────────────────────────────────────
                // 'test_suites' => [
                //     'Unit'        => 'tests/Unit',
                //     'Integration' => 'tests/Integration',
                // ],

                // ── Coverage ──────────────────────────────────────────────
                // 'coverage_exclude' => ['src/Exception'],

                // ── Code Style (MERGED with KaririCode defaults) ──────────
                // 'cs_fixer_rules' => [
                //     'concat_space' => ['spacing' => 'one'],
                //     'yoda_style'   => false,
                // ],

                // ── Rector (REPLACES KaririCode defaults) ─────────────────
                // 'rector_sets' => [
                //     'LevelSetList::UP_TO_PHP_84',
                //     'SetList::CODE_QUALITY',
                //     'SetList::DEAD_CODE',
                //     'SetList::EARLY_RETURN',
                //     'SetList::TYPE_DECLARATION',
                // ],

                // ── Tool Versions (informational) ─────────────────────────
                // 'tools' => [
                //     'phpunit'      => '^11.0',
                //     'phpstan'      => '^2.0',
                //     'php-cs-fixer' => '^3.64',
                //     'rector'       => '^2.0',
                //     'psalm'        => '^6.0',
                // ],
            ];
            PHP_WRAP;

        file_put_contents($configPath, $content . \PHP_EOL);

        $this->line();
        $this->info('Scaffolded devkit.php in project root.');
        $this->line('  Edit it, then run \033[1mkcode init\033[0m to regenerate configs.');
    }
}
