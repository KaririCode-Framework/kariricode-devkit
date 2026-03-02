<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Configuration;

use KaririCode\Devkit\Configuration\KcodeComposerGenerator;
use KaririCode\Devkit\Core\ProjectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(KcodeComposerGenerator::class)]
#[UsesClass(ProjectContext::class)]
final class KcodeComposerGeneratorTest extends TestCase
{
    private ProjectContext $context;

    protected function setUp(): void
    {
        $this->context = new ProjectContext(
            projectRoot: '/tmp/test-project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: ['/tmp/test-project/src'],
            testDirs: ['/tmp/test-project/tests'],
            excludeDirs: ['src/Contract'],
            testSuites: ['Unit' => 'tests/Unit'],
            coverageExclude: ['src/Exception'],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );
    }

    #[Test]
    public function toolNameIsKcodeComposer(): void
    {
        $generator = new KcodeComposerGenerator();
        $this->assertSame('kcode-composer', $generator->toolName());
    }

    #[Test]
    public function outputPathIsComposerJson(): void
    {
        $generator = new KcodeComposerGenerator();
        $this->assertSame('composer.json', $generator->outputPath());
    }

    #[Test]
    public function generateProducesValidJsonWithDefaultVersions(): void
    {
        $generator = new KcodeComposerGenerator();
        $output = $generator->generate($this->context);

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertSame('kariricode/devkit-tools', $manifest['name']);
        $this->assertIsArray($manifest['require']);

        // All 5 default tools must be present
        $require = $manifest['require'];
        $this->assertArrayHasKey('phpunit/phpunit', $require);
        $this->assertArrayHasKey('phpstan/phpstan', $require);
        $this->assertArrayHasKey('friendsofphp/php-cs-fixer', $require);
        $this->assertArrayHasKey('rector/rector', $require);
        $this->assertArrayHasKey('vimeo/psalm', $require);
    }

    #[Test]
    public function generateRespectsUserToolVersionOverrides(): void
    {
        $contextWithVersions = new ProjectContext(
            projectRoot: '/tmp/test-project',
            projectName: 'test/project',
            namespace: 'Test\\Project',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: ['/tmp/test-project/src'],
            testDirs: ['/tmp/test-project/tests'],
            excludeDirs: [],
            testSuites: [],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: ['phpunit' => '^11.0', 'phpstan' => '^1.12'],
        );

        $generator = new KcodeComposerGenerator();
        $output = $generator->generate($contextWithVersions);

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

        $require = $manifest['require'];
        $this->assertSame('^11.0', $require['phpunit/phpunit']);
        $this->assertSame('^1.12', $require['phpstan/phpstan']);
        // Unoverridden tool still uses default
        $this->assertStringStartsWith('^', $require['rector/rector']);
    }

    #[Test]
    public function generateIncludesComposerConfigSection(): void
    {
        $generator = new KcodeComposerGenerator();
        $output = $generator->generate($this->context);

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertIsArray($manifest['config']);
        $this->assertSame('full', $manifest['config']['bin-compat']);
        $this->assertTrue($manifest['config']['optimize-autoloader']);
    }

    #[Test]
    public function generateOutputEndsWithNewline(): void
    {
        $generator = new KcodeComposerGenerator();
        $output = $generator->generate($this->context);

        $this->assertStringEndsWith(\PHP_EOL, $output);
    }
}
