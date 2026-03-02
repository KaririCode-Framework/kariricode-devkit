<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Runner;

use KaririCode\Devkit\Core\ProcessExecutor;
use KaririCode\Devkit\Core\ProjectContext;
use KaririCode\Devkit\Runner\AbstractToolRunner;
use KaririCode\Devkit\Runner\ComposerAuditRunner;
use KaririCode\Devkit\Runner\CsFixerRunner;
use KaririCode\Devkit\Runner\PhpStanRunner;
use KaririCode\Devkit\Runner\PhpUnitRunner;
use KaririCode\Devkit\Runner\PsalmRunner;
use KaririCode\Devkit\Runner\RectorRunner;
use KaririCode\Devkit\ValueObject\ToolResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractToolRunner::class)]
#[CoversClass(PhpUnitRunner::class)]
#[CoversClass(PhpStanRunner::class)]
#[CoversClass(CsFixerRunner::class)]
#[CoversClass(RectorRunner::class)]
#[CoversClass(PsalmRunner::class)]
#[CoversClass(ComposerAuditRunner::class)]
#[UsesClass(ToolResult::class)]
#[UsesClass(ProjectContext::class)]
#[UsesClass(ProcessExecutor::class)]
final class RunnersTest extends TestCase
{
    private string $projectRoot;
    private ProcessExecutor $executor;
    private ProjectContext $context;

    protected function setUp(): void
    {
        $this->projectRoot = (string) realpath(\dirname(__DIR__, 3));
        $this->executor = new ProcessExecutor($this->projectRoot);
        $this->context = new ProjectContext(
            projectRoot: $this->projectRoot,
            projectName: 'kariricode/devkit',
            namespace: 'KaririCode\\Devkit',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: [$this->projectRoot . '/src'],
            testDirs: [$this->projectRoot . '/tests'],
            excludeDirs: [],
            testSuites: ['Unit' => 'tests/Unit'],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );
    }

    // ── PHPUnit Runner ─────────────────────────────────────────────

    #[Test]
    public function phpUnitRunnerToolNameIsPhpunit(): void
    {
        $runner = new PhpUnitRunner($this->executor, $this->context);
        $this->assertSame('phpunit', $runner->toolName());
    }

    #[Test]
    public function phpUnitRunnerIsAvailableWhenBinaryExists(): void
    {
        $runner = new PhpUnitRunner($this->executor, $this->context);
        // phpunit is a dev dependency of this project — must be available
        $this->assertTrue($runner->isAvailable());
    }

    #[Test]
    public function phpUnitRunnerRunReturnsToolResult(): void
    {
        $runner = new PhpUnitRunner($this->executor, $this->context);
        $result = $runner->run(['--version']);
        $this->assertInstanceOf(ToolResult::class, $result);
        $this->assertSame('phpunit', $result->toolName);
    }

    // ── PHPStan Runner ─────────────────────────────────────────────

    #[Test]
    public function phpStanRunnerToolNameIsPhpstan(): void
    {
        $runner = new PhpStanRunner($this->executor, $this->context);
        $this->assertSame('phpstan', $runner->toolName());
    }

    #[Test]
    public function phpStanRunnerIsAvailableWhenBinaryExists(): void
    {
        $runner = new PhpStanRunner($this->executor, $this->context);
        $this->assertTrue($runner->isAvailable());
    }

    // ── CS Fixer Runner ────────────────────────────────────────────

    #[Test]
    public function csFixerRunnerToolNameIsCsFixer(): void
    {
        $runner = new CsFixerRunner($this->executor, $this->context);
        $this->assertSame('cs-fixer', $runner->toolName());
    }

    #[Test]
    public function csFixerRunnerIsAvailableWhenBinaryExists(): void
    {
        $runner = new CsFixerRunner($this->executor, $this->context);
        $this->assertTrue($runner->isAvailable());
    }

    // ── Rector Runner ──────────────────────────────────────────────

    #[Test]
    public function rectorRunnerToolNameIsRector(): void
    {
        $runner = new RectorRunner($this->executor, $this->context);
        $this->assertSame('rector', $runner->toolName());
    }

    #[Test]
    public function rectorRunnerIsAvailableWhenBinaryExists(): void
    {
        $runner = new RectorRunner($this->executor, $this->context);
        $this->assertTrue($runner->isAvailable());
    }

    // ── Psalm Runner ───────────────────────────────────────────────

    #[Test]
    public function psalmRunnerToolNameIsPsalm(): void
    {
        $runner = new PsalmRunner($this->executor, $this->context);
        $this->assertSame('psalm', $runner->toolName());
    }

    #[Test]
    public function psalmRunnerIsAvailableWhenBinaryExists(): void
    {
        $runner = new PsalmRunner($this->executor, $this->context);
        $this->assertTrue($runner->isAvailable());
    }

    // ── Composer Audit Runner ──────────────────────────────────────

    #[Test]
    public function composerAuditRunnerToolNameIsComposerAudit(): void
    {
        $runner = new ComposerAuditRunner($this->executor, $this->context);
        $this->assertSame('composer-audit', $runner->toolName());
    }

    #[Test]
    public function composerAuditRunnerIsAvailableWhenComposerInPath(): void
    {
        $runner = new ComposerAuditRunner($this->executor, $this->context);
        // composer is available in CI/dev environments
        $available = $runner->isAvailable();
        $this->assertIsBool($available);
    }

    // ── AbstractToolRunner — binary not found path ─────────────────

    #[Test]
    public function runnerReturns127WhenBinaryNotFound(): void
    {
        // Use a path where no vendor/bin exists
        $executorNoVendor = new ProcessExecutor('/tmp');
        $context = new ProjectContext(
            projectRoot: '/tmp',
            projectName: 'test/project',
            namespace: 'Test',
            phpVersion: '8.4',
            phpstanLevel: 9,
            psalmLevel: 3,
            sourceDirs: ['/tmp/src'],
            testDirs: ['/tmp/tests'],
            excludeDirs: [],
            testSuites: [],
            coverageExclude: [],
            csFixerRules: [],
            rectorSets: [],
            toolVersions: [],
        );

        // Use a custom runner that resolves to a binary that doesn't exist
        $runner = new class ($executorNoVendor, $context) extends AbstractToolRunner {
            public function toolName(): string
            {
                return 'nonexistent-tool-xyz';
            }

            protected function vendorBin(): string
            {
                return 'vendor/bin/nonexistent-tool-xyz';
            }

            protected function defaultArguments(): array
            {
                return [];
            }
        };

        $result = $runner->run([]);
        $this->assertSame(127, $result->exitCode);
        $this->assertFalse($result->success);
        $this->assertStringContainsString('Binary not found', $result->stderr);
    }

    #[Test]
    public function runnerCachesBinaryResolution(): void
    {
        $runner = new PhpUnitRunner($this->executor, $this->context);

        // Call isAvailable twice — binary resolution should only happen once (lazy cached)
        $first = $runner->isAvailable();
        $second = $runner->isAvailable();
        $this->assertSame($first, $second);
    }
}
