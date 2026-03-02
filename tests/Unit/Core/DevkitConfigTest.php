<?php

declare(strict_types=1);

namespace KaririCode\Devkit\Tests\Unit\Core;

use KaririCode\Devkit\Core\DevkitConfig;
use KaririCode\Devkit\Exception\ConfigurationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DevkitConfig::class)]
final class DevkitConfigTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/devkit_config_test_' . uniqid();
        mkdir($this->tmpDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        // Clean up devkit.php if left
        $configPath = $this->tmpDir . '/devkit.php';
        if (file_exists($configPath)) {
            unlink($configPath);
        }
        if (is_dir($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }

    #[Test]
    public function withoutConfigFileOverridesAreEmpty(): void
    {
        $config = new DevkitConfig($this->tmpDir);

        $this->assertFalse($config->hasOverrides());
        $this->assertSame([], $config->overrides);
    }

    #[Test]
    public function getReturnsDefaultWhenKeyNotSet(): void
    {
        $config = new DevkitConfig($this->tmpDir);

        $this->assertSame(9, $config->get('phpstan_level', 9));
        $this->assertSame('8.4', $config->get('php_version', '8.4'));
    }

    #[Test]
    public function withValidConfigFileOverridesAreLoaded(): void
    {
        file_put_contents(
            $this->tmpDir . '/devkit.php',
            "<?php return ['phpstan_level' => 5, 'php_version' => '8.3'];",
        );

        $config = new DevkitConfig($this->tmpDir);

        $this->assertTrue($config->hasOverrides());
        $this->assertSame(5, $config->get('phpstan_level', 9));
        $this->assertSame('8.3', $config->get('php_version', '8.4'));
    }

    #[Test]
    public function getReturnsDefaultForUnknownKeyEvenWithConfigFile(): void
    {
        file_put_contents(
            $this->tmpDir . '/devkit.php',
            "<?php return ['phpstan_level' => 5];",
        );

        $config = new DevkitConfig($this->tmpDir);

        $this->assertSame(3, $config->get('psalm_level', 3));
    }

    #[Test]
    public function getThrowsConfigurationExceptionOnTypeMismatch(): void
    {
        file_put_contents(
            $this->tmpDir . '/devkit.php',
            "<?php return ['phpstan_level' => 'nine'];", // string instead of int
        );

        $config = new DevkitConfig($this->tmpDir);

        $this->expectException(ConfigurationException::class);
        $config->get('phpstan_level', 9); // expects int, got string
    }

    #[Test]
    public function invalidConfigFileThrowsConfigurationException(): void
    {
        file_put_contents(
            $this->tmpDir . '/devkit.php',
            "<?php return 'not an array';",
        );

        $this->expectException(ConfigurationException::class);
        new DevkitConfig($this->tmpDir);
    }

    #[Test]
    public function toolVersionsReturnsEmptyArrayWhenNotConfigured(): void
    {
        $config = new DevkitConfig($this->tmpDir);

        $this->assertSame([], $config->toolVersions());
    }

    #[Test]
    public function toolVersionsReturnsToolsArrayFromConfig(): void
    {
        file_put_contents(
            $this->tmpDir . '/devkit.php',
            "<?php return ['tools' => ['phpunit' => '^11.0', 'phpstan' => '^2.0']];",
        );

        $config = new DevkitConfig($this->tmpDir);

        $this->assertSame(
            ['phpunit' => '^11.0', 'phpstan' => '^2.0'],
            $config->toolVersions(),
        );
    }
}
