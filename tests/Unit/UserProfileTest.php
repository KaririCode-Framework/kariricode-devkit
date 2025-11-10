<?php

declare(strict_types=1);

namespace KaririCode\DevKit\Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use KaririCode\DevKit\UserProfile;
use KaririCode\DevKit\UserRole;
use KaririCode\DevKit\UserStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica de negócio e os métodos da classe UserProfile.
 */
#[CoversClass(UserProfile::class)]
class UserProfileTest extends TestCase
{
    /**
     * Tests the creation of a new user with the 'new' factory.
     */
    #[Test]
    public function itCreatesNewUserWithFactory(): void
    {
        $user = UserProfile::new(
            name: 'Utilizador Teste',
            email: 'teste@exemplo.com',
            role: UserRole::EDITOR,
        );

        $this->assertInstanceOf(UserProfile::class, $user);
        $this->assertSame('Utilizador Teste', $user->name);
        $this->assertSame('teste@exemplo.com', (string) $user->email);
        $this->assertSame(UserRole::EDITOR, $user->role); // Verifies the role
        $this->assertSame(UserStatus::ACTIVE, $user->status); // Verifies the default status
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->createdAt);
        $this->assertNotNull($user->updatedAt);
        $this->assertNull($user->twoFactorSecret);
    }

    /**
     * Tests creation from an array.
     */
    #[Test]
    public function itHydratesFromArray(): void
    {
        $expectedCreated = new DateTimeImmutable('2025-10-22T22:03:20+00:00');
        $data = [
            'id' => 'user-123',
            'name' => 'Nome do Array',
            'email' => 'array@exemplo.com',
            'role' => 'ADMIN',
            'status' => 'SUSPENDED',
            'twoFactorSecret' => 'segredo123',
            'meta' => ['key' => 'value'],
            'createdAt' => $expectedCreated->format('c'),
        ];

        $user = UserProfile::fromArray($data);

        $this->assertSame('user-123', $user->id);
        $this->assertSame('Nome do Array', $user->name);
        $this->assertSame('array@exemplo.com', (string) $user->email);
        $this->assertSame(UserRole::ADMIN, $user->role);
        $this->assertSame(UserStatus::SUSPENDED, $user->status);
        $this->assertSame('segredo123', $user->twoFactorSecret);
        $this->assertSame(['key' => 'value'], $user->meta);
        $this->assertSame(
            $expectedCreated->format('Y-m-d\TH:i:sP'),
            $user->createdAt?->format('Y-m-d\TH:i:sP'),
        );

        $this->assertNull($user->updatedAt);
    }

    /**
     * Tests default values when hydrating with minimal data.
     */
    #[Test]
    public function itHydratesFromArrayWithDefaults(): void
    {
        $data = [
            'name' => 'Utilizador Mínimo',
            'email' => 'minimo@exemplo.com',
        ];

        $user = UserProfile::fromArray($data);

        $this->assertNotNull($user->id); // ID is generated
        $this->assertSame('Utilizador Mínimo', $user->name);
        $this->assertSame(UserRole::VIEWER, $user->role); // Default role
        $this->assertSame(UserStatus::ACTIVE, $user->status); // Default status
        $this->assertNull($user->createdAt);
    }

    /**
     * Tests if 'fromArray' fails with an invalid email.
     */
    #[Test]
    public function itFailsHydrationWithInvalidEmail(): void
    {
        $data = [
            'name' => 'Utilizador Falhado',
            'email' => 'email-invalido',
        ];

        // The exception comes from the Email constructor
        $this->expectException(InvalidArgumentException::class);
        UserProfile::fromArray($data);
    }

    /**
     * Tests 'with*' methods to ensure immutability.
     * The UserProfile class is 'readonly', but 'with*' methods
     * create NEW instances.
     */
    #[Test]
    public function itIsImmutableAndCreatesNewInstances(): void
    {
        $user1 = UserProfile::new('Original', 'original@exemplo.com');
        $user1CreatedAt = $user1->createdAt;

        // A microsecond delay is needed to ensure the timestamp changes
        usleep(10);

        // 1. Promote
        $user2 = $user1->promote();
        $this->assertNotSame($user1, $user2); // They are different objects
        $this->assertSame(UserRole::VIEWER, $user1->role); // Original does not change
        $this->assertSame(UserRole::EDITOR, $user2->role); // New object changes
        $this->assertNotEquals($user1->updatedAt, $user2->updatedAt);
        $this->assertEquals($user1CreatedAt, $user2->createdAt); // createdAt is maintained

        // 2. Suspend
        $user3 = $user2->suspend();
        $this->assertNotSame($user2, $user3);
        $this->assertSame(UserStatus::ACTIVE, $user2->status); // Previous does not change
        $this->assertSame(UserStatus::SUSPENDED, $user3->status); // New object changes

        // 3. Add 2FA
        $user4 = $user3->with2FA('segredo');
        $this->assertNotSame($user3, $user4);
        $this->assertNull($user3->twoFactorSecret); // Previous does not change
        $this->assertSame('segredo', $user4->twoFactorSecret);
        $this->assertTrue($user4->has2FA());

        // 4. Add Meta
        $user5 = $user4->withMeta(['foo' => 'bar']);
        $this->assertNotSame($user4, $user5);
        $this->assertNull($user4->meta); // Previous does not change
        $this->assertSame(['foo' => 'bar'], $user5->meta);
    }

    /**
     * Testa a lógica de promoção (não deve promover quem já é admin).
     */
    #[Test]
    public function itPromotesCorrectly(): void
    {
        $viewer = UserProfile::new('Viewer', 'v@v.com', UserRole::VIEWER);
        $editor = UserProfile::new('Editor', 'e@e.com', UserRole::EDITOR);
        $admin = UserProfile::new('Admin', 'a@a.com', UserRole::ADMIN);

        // Viewer is promoted to Editor
        $this->assertSame(UserRole::EDITOR, $viewer->promote()->role);
        // Editor remains Editor
        $this->assertSame(UserRole::EDITOR, $editor->promote()->role);
        // Admin remains Admin
        $this->assertSame(UserRole::ADMIN, $admin->promote()->role);
    }

    /**
     * Provides data for the 'canEdit' test.
     */
    public static function editPermissionProvider(): array
    {
        return [
            // Role, Status, Expected
            'Admin Ativo' => [UserRole::ADMIN, UserStatus::ACTIVE, true],
            'Editor Ativo' => [UserRole::EDITOR, UserStatus::ACTIVE, true],
            'Viewer Ativo' => [UserRole::VIEWER, UserStatus::ACTIVE, false],
            'Admin Suspenso' => [UserRole::ADMIN, UserStatus::SUSPENDED, false],
            'Editor Suspenso' => [UserRole::EDITOR, UserStatus::SUSPENDED, false],
            'Viewer Suspenso' => [UserRole::VIEWER, UserStatus::SUSPENDED, false],
        ];
    }

    /**
     * Tests the 'canEdit' logic (combination of Role and Status).
     */
    #[Test]
    #[DataProvider('editPermissionProvider')]
    public function itChecksEditPermissions(UserRole $role, UserStatus $status, bool $expected): void
    {
        // We use fromArray to "force" the status
        $user = UserProfile::fromArray([
            'name' => 'Teste Permissão',
            'email' => 'p@p.com',
            'role' => $role,
            'status' => $status,
        ]);

        $this->assertSame($expected, $user->canEdit());
    }

    /**
     * Tests serialization outputs.
     */
    #[Test]
    public function itSerializesCorrectly(): void
    {
        $user = UserProfile::new('Serializar', 's@s.com');
        $id = $user->id;

        // Test __toString
        $this->assertSame("UserProfile#{$id}<VIEWER>", (string) $user);

        // Teste displayLabel
        $this->assertSame('Serializar (viewer)', $user->displayLabel());

        // Teste jsonSerialize
        $json = $user->jsonSerialize();

        $this->assertSame('UserProfile', $json['model']);
        $this->assertSame(1, $json['version']);
        $this->assertSame($id, $json['id']);
        $this->assertSame('Serializar', $json['name']);
        $this->assertSame('s@s.com', $json['email']);
        $this->assertSame('VIEWER', $json['role']);
        $this->assertSame('ACTIVE', $json['status']);
        $this->assertFalse($json['has2FA']);
        $this->assertNotNull($json['createdAt']);
    }
}
