<?php

declare(strict_types=1);

namespace KaririCode\DevKit\Tests\Integration;

use DateTimeImmutable;
use KaririCode\DevKit\UserProfile;
use KaririCode\DevKit\UserRole;
use KaririCode\DevKit\UserStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests a complete UserProfile usage flow,
 * integrating hydration, business methods, and serialization.
 */
#[CoversClass(UserProfile::class)]
final class UserProfileFlowTest extends TestCase
{
    /**
     * Simulates a complete user lifecycle:
     * 1. Loaded from the database (via fromArray) as VIEWER (ACTIVE).
     * 2. Promoted to EDITOR (ACTIVE) -> can edit.
     * 3. Activates 2FA.
     * 4. Verifies the final state and JSON serialization.
     */
    #[Test]
    public function itHandlesACompleteUserLifecycleFlow(): void
    {
        // --- 1) Simulate DB load (VIEWER + ACTIVE) ---
        $dbData = [
            'id' => 'flow-user-001',
            'name' => 'Utilizador de Fluxo',
            'email' => 'flow@exemplo.com',
            'role' => 'VIEWER',
            'status' => 'ACTIVE',
            'createdAt' => '2025-01-01T10:00:00+00:00',
            'updatedAt' => '2025-01-01T10:00:00+00:00',
        ];

        $user = UserProfile::fromArray($dbData);

        // Initial assertions
        $this->assertSame(UserRole::VIEWER, $user->role);
        $this->assertSame(UserStatus::ACTIVE, $user->status);
        $this->assertFalse($user->has2FA());
        $this->assertFalse($user->canEdit(), 'Viewer ativo não deve poder editar.');

        // --- 2) Business logic: promote (VIEWER -> EDITOR). Now can edit. ---
        usleep(10);
        $promotedUser = $user->promote();

        $this->assertNotSame($user, $promotedUser); // immutable
        $this->assertSame(UserRole::VIEWER, $user->role);       // original intact
        $this->assertSame(UserRole::EDITOR, $promotedUser->role);
        $this->assertSame(UserStatus::ACTIVE, $promotedUser->status);
        $this->assertTrue($promotedUser->canEdit(), 'Editor ativo deve poder editar.');
        $this->assertNotEquals($user->updatedAt, $promotedUser->updatedAt);

        // --- 3) Business logic: enable 2FA (still EDITOR + ACTIVE). ---
        usleep(10);
        $secureUser = $promotedUser->with2FA('segredo-super-secreto-123');

        $this->assertNotSame($promotedUser, $secureUser); // immutable
        $this->assertFalse($promotedUser->has2FA()); // previous intact
        $this->assertTrue($secureUser->has2FA());
        $this->assertSame(UserRole::EDITOR, $secureUser->role);
        $this->assertSame(UserStatus::ACTIVE, $secureUser->status);
        $this->assertTrue($secureUser->canEdit(), 'Editor ativo deve poder editar.');
        $this->assertNotEquals($promotedUser->updatedAt, $secureUser->updatedAt);

        // --- 4) Final state + serialization checks ---
        $finalJson = $secureUser->jsonSerialize();

        $this->assertSame('UserProfile', $finalJson['model']);
        $this->assertSame('flow-user-001', $finalJson['id']);
        $this->assertSame('Utilizador de Fluxo', $finalJson['name']);
        $this->assertSame('EDITOR', $finalJson['role']);
        $this->assertSame('ACTIVE', $finalJson['status']);
        $this->assertTrue($finalJson['has2FA']);
        $this->assertSame('2025-01-01T10:00:00+00:00', $finalJson['createdAt']);

        // updatedAt must be newer than createdAt
        $this->assertGreaterThan(
            new DateTimeImmutable($finalJson['createdAt']),
            new DateTimeImmutable($finalJson['updatedAt'])
        );
    }
}
