<?php

declare(strict_types=1);

namespace KaririCode\DevKit\Tests\Unit;

use KaririCode\DevKit\UserRole;
use KaririCode\DevKit\UserStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the internal logic of the Enums.
 */
#[CoversClass(UserRole::class)]
#[CoversClass(UserStatus::class)]
class EnumsTest extends TestCase
{
    /**
     * Provides data for the canEdit test.
     *
     * @return array<string, array{0: UserRole, 1: bool}>
     */
    public static function roleEditProvider(): array
    {
        return [
            'Admin pode editar' => [UserRole::ADMIN, true],
            'Editor pode editar' => [UserRole::EDITOR, true],
            'Viewer não pode editar' => [UserRole::VIEWER, false],
        ];
    }

    /**
     * Tests the edit permission logic.
     */
    #[Test]
    #[DataProvider('roleEditProvider')]
    public function userRoleCanEdit(UserRole $role, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $role->canEdit());
    }

    /**
     * Tests the labels of the roles.
     */
    #[Test]
    public function userRoleLabels(): void
    {
        $this->assertSame('admin', UserRole::ADMIN->label());
        $this->assertSame('editor', UserRole::EDITOR->label());
        $this->assertSame('viewer', UserRole::VIEWER->label());
    }

    /**
     * Tests the 'active' status logic.
     */
    #[Test]
    public function userStatusIsActive(): void
    {
        $this->assertTrue(UserStatus::ACTIVE->isActive());
        $this->assertFalse(UserStatus::SUSPENDED->isActive());
    }
}
