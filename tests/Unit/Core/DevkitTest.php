<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Contract\ConfigGenerator;
use KaririCode\Devkit\Contract\ToolRunner;
use KaririCode\Devkit\Core\Devkit;
use KaririCode\Devkit\Core\ProjectContext;
use KaririCode\Devkit\Core\ProjectDetector;
use KaririCode\Devkit\Exception\DevkitException;
use KaririCode\Devkit\ValueObject\QualityReport;
use KaririCode\Devkit\ValueObject\ToolResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Devkit::class)]
#[UsesClass(ProjectContext::class)]
#[UsesClass(DevkitException::class)]
#[UsesClass(QualityReport::class)]
#[UsesClass(ToolResult::class)]
final class DevkitTest extends TestCase
{
    private string $tmpDir;
    private ProjectDetector $detector;
    private Devkit $devkit;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/devkit_devkit_test_' . uniqid();
        mkdir($this->tmpDir, 0o777, true);

        // Create a minimal composer.json so ProjectDetector doesn't throw
        file_put_contents($this->tmpDir . '/composer.json', json_encode([
            'name' => 'test/project',
            'autoload' => ['psr-4' => ['Test\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['Test\\Tests\\' => 'tests/']],
        ]));

        mkdir($this->tmpDir . '/src', 0o777, true);
        mkdir($this->tmpDir . '/tests', 0o777, true);

        $this->detector = new ProjectDetector();
        $this->devkit = new Devkit($this->detector);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    #[Test]
    public function versionReturnsString(): void
    {
        $this->assertIsString(Devkit::version());
        $this->assertNotEmpty(Devkit::version());
    }

    #[Test]
    public function contextReturnsProjectContext(): void
    {
        $ctx = $this->devkit->context($this->tmpDir);
        $this->assertInstanceOf(ProjectContext::class, $ctx);
    }

    #[Test]
    public function contextIsCached(): void
    {
        $ctx1 = $this->devkit->context($this->tmpDir);
        $ctx2 = $this->devkit->context($this->tmpDir);
        $this->assertSame($ctx1, $ctx2);
    }

    #[Test]
    public function addGeneratorRegistersGenerator(): void
    {
        $generator = $this->createMock(ConfigGenerator::class);
        $generator->method('toolName')->willReturn('test-gen');

        $this->devkit->addGenerator($generator);
        // No exception = registered successfully
        $this->assertTrue(true);
    }

    #[Test]
    public function addRunnerRegistersRunner(): void
    {
        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('test-runner');

        $this->devkit->addRunner($runner);
        $this->assertContains('test-runner', $this->devkit->registeredTools());
    }

    #[Test]
    public function registeredToolsReturnsToolNames(): void
    {
        $runner1 = $this->createMock(ToolRunner::class);
        $runner1->method('toolName')->willReturn('phpunit');

        $runner2 = $this->createMock(ToolRunner::class);
        $runner2->method('toolName')->willReturn('phpstan');

        $this->devkit->addRunner($runner1);
        $this->devkit->addRunner($runner2);

        $this->assertContains('phpunit', $this->devkit->registeredTools());
        $this->assertContains('phpstan', $this->devkit->registeredTools());
    }

    #[Test]
    public function isToolAvailableReturnsTrueWhenRunnerIsAvailable(): void
    {
        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('my-tool');
        $runner->method('isAvailable')->willReturn(true);

        $this->devkit->addRunner($runner);
        $this->assertTrue($this->devkit->isToolAvailable('my-tool'));
    }

    #[Test]
    public function isToolAvailableReturnsFalseWhenRunnerNotAvailable(): void
    {
        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('my-tool');
        $runner->method('isAvailable')->willReturn(false);

        $this->devkit->addRunner($runner);
        $this->assertFalse($this->devkit->isToolAvailable('my-tool'));
    }

    #[Test]
    public function isToolAvailableReturnsFalseForUnknownTool(): void
    {
        $this->assertFalse($this->devkit->isToolAvailable('nonexistent-tool'));
    }

    #[Test]
    public function runDelegatesToRegisteredRunner(): void
    {
        $expectedResult = new ToolResult(
            toolName: 'my-tool',
            exitCode: 0,
            stdout: 'ok',
            stderr: '',
            elapsedSeconds: 0.1,
        );

        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('my-tool');
        $runner->method('run')->willReturn($expectedResult);

        $this->devkit->addRunner($runner);
        $result = $this->devkit->run('my-tool', []);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function runThrowsForUnknownTool(): void
    {
        $this->expectException(DevkitException::class);
        $this->devkit->run('nonexistent', []);
    }

    #[Test]
    public function qualityRunsPipelineAndReturnsReport(): void
    {
        $result = new ToolResult(
            toolName: 'cs-fixer',
            exitCode: 0,
            stdout: '',
            stderr: '',
            elapsedSeconds: 0.1,
        );

        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('cs-fixer');
        $runner->method('isAvailable')->willReturn(true);
        $runner->method('run')->willReturn($result);

        $this->devkit->addRunner($runner);
        $report = $this->devkit->quality(['cs-fixer']);
        $this->assertInstanceOf(QualityReport::class, $report);
        $this->assertTrue($report->passed);
    }

    #[Test]
    public function qualitySkipsUnavailableTools(): void
    {
        $runner = $this->createMock(ToolRunner::class);
        $runner->method('toolName')->willReturn('phpstan');
        $runner->method('isAvailable')->willReturn(false);
        $runner->method('run')->willThrowException(new \RuntimeException('Should not be called'));

        $this->devkit->addRunner($runner);
        $report = $this->devkit->quality(['phpstan']);
        $this->assertInstanceOf(QualityReport::class, $report);
    }

    #[Test]
    public function qualityWithEmptyOnlyToolsUsesDefaultPipeline(): void
    {
        // No runners registered — should return empty report without error
        $report = $this->devkit->quality([]);
        $this->assertInstanceOf(QualityReport::class, $report);
    }

    #[Test]
    public function initCreatesConfigFiles(): void
    {
        $generator = $this->createMock(ConfigGenerator::class);
        $generator->method('toolName')->willReturn('test-gen');
        $generator->method('outputPath')->willReturn('test-config.txt');
        $generator->method('generate')->willReturn('# test config');

        $this->devkit->addGenerator($generator);
        $count = $this->devkit->init($this->tmpDir);
        $this->assertSame(1, $count);
        $this->assertFileExists($this->tmpDir . '/.kcode/test-config.txt');
    }

    #[Test]
    public function initCreatesGitIgnore(): void
    {
        $this->devkit->init($this->tmpDir);
        $gitignore = $this->tmpDir . '/.gitignore';
        $this->assertFileExists($gitignore);
        $this->assertStringContainsString('.kcode/', file_get_contents($gitignore));
    }

    #[Test]
    public function initUpdatesExistingGitIgnoreWithLegacyEntry(): void
    {
        file_put_contents($this->tmpDir . '/.gitignore', ".kcode/build/\n");
        $this->devkit->init($this->tmpDir);
        $content = file_get_contents($this->tmpDir . '/.gitignore');
        $this->assertStringContainsString('.kcode/', $content);
    }

    #[Test]
    public function initSkipsGitIgnoreEntryIfAlreadyPresent(): void
    {
        file_put_contents($this->tmpDir . '/.gitignore', ".kcode/\n");
        $this->devkit->init($this->tmpDir);
        $content = file_get_contents($this->tmpDir . '/.gitignore');
        // Should not duplicate the entry
        $this->assertSame(1, substr_count($content, '.kcode/'));
    }

    #[Test]
    public function cleanRemovesAndRecreatsBuildDir(): void
    {
        $this->devkit->init($this->tmpDir);
        $buildDir = $this->tmpDir . '/.kcode/build';
        file_put_contents($buildDir . '/old-file.txt', 'old');

        $this->devkit->clean($this->tmpDir);
        $this->assertDirectoryExists($buildDir);
        $this->assertFileDoesNotExist($buildDir . '/old-file.txt');
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
