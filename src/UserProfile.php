<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use JsonSerializable;
use Random\Randomizer;
use SensitiveParameter;
use Stringable;

// --------------------------------------------------------------
// Main Domain Class
// --------------------------------------------------------------

/**
 * UserProfile — Modern PHP 8.4 Domain Model
 *
 * A clean, production-ready domain class demonstrating modern PHP features.
 *
 * This class showcases:
 * - Readonly classes and properties
 * - Enums with methods
 * - Attributes for validation/metadata
 * - Value Objects
 * - Named arguments for constructor clarity
 * - Declarative hydration (`fromArray`)
 * - Immutability through `with*` methods
 * - SensitiveParameter attribute
 * - Static variable caching in methods
 * * ## Usage
 *
 * ```php
 * $user = UserProfile::new('Walmir Silva', 'walmir@example.com')
 * ->with2FA('secret')
 * ->promote();
 *
 * echo $user->displayLabel(); // "Walmir Silva (editor)"
 * ```
 *
 * @package      KaririCode\DevKit
 * @category   Query Filtering
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
final readonly class UserProfile implements JsonSerializable, Stringable
{
    public const string MODEL = 'UserProfile';

    public const int VERSION = 1;

    public function __construct(
        #[Length(min: 1, max: 50)]
        public string $id,
        #[Length(min: 2, max: 120)]
        public string $name,
        public Email $email,
        public UserRole $role = UserRole::VIEWER,
        public UserStatus $status = UserStatus::ACTIVE,
        #[SensitiveParameter]
        public ?string $twoFactorSecret = null,
        public ?array $meta = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
    }

    /**
     * Creates a new UserProfile instance with a generated ID and current timestamps.
     *
     * @param string $name The user's full name.
     * @param Email|string $email The user's email address. Can be an Email object or a string.
     * @param UserRole $role The user's role, defaults to VIEWER.
     * @return UserProfile A new instance.
     */
    public static function new(
        string $name,
        Email|string $email,
        UserRole $role = UserRole::VIEWER,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: self::generateId(),
            name: $name,
            email: $email instanceof Email ? $email : new Email($email),
            role: $role,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    /**
     * Creates a UserProfile from a raw data array (e.g., from a database or API).
     * This is the elegant, declarative hydration method.
     * @param array $data An associative array containing user data. Expected keys:
     * - 'id' (string, optional): User ID. If not provided, a new one will be generated.
     * - 'name' (string): User's full name.
     * - 'email' (string|Email): User's email address.
     * - 'role' (string|UserRole, optional): User's role. Defaults to 'VIEWER'.
     * - 'status' (string|UserStatus, optional): User's status. Defaults to 'ACTIVE'.
     * - 'twoFactorSecret' (string, optional): Two-factor authentication secret.
     * - 'meta' (array, optional): Additional metadata.
     * - 'createdAt' (string|DateTimeImmutable, optional): Creation timestamp.
     * - 'updatedAt' (string|DateTimeImmutable, optional): Last update timestamp.
     * * @return UserProfile A new UserProfile instance hydrated with the provided data.
     * @throws InvalidArgumentException If required data is missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? self::generateId(),
            name: $data['name'] ?? '',
            email: self::hydrateEmail($data),
            role: self::hydrateRole($data),
            status: self::hydrateStatus($data),
            twoFactorSecret: $data['twoFactorSecret'] ?? null,
            meta: $data['meta'] ?? null,
            createdAt: self::hydrateDate($data, 'createdAt'),
            updatedAt: self::hydrateDate($data, 'updatedAt'),
        );
    }

    // ----------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------

    public function canEdit(): bool
    {
        return $this->status->isActive() && $this->role->canEdit();
    }

    public function has2FA(): bool
    {
        return $this->twoFactorSecret !== null;
    }

    // ----------------------------------------------------------
    // Domain Behavior
    // ----------------------------------------------------------

    public function promote(): self
    {
        $newRole = $this->role === UserRole::ADMIN ? UserRole::ADMIN : UserRole::EDITOR;

        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            role: $newRole,
            status: $this->status,
            twoFactorSecret: $this->twoFactorSecret,
            meta: $this->meta,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function suspend(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            role: $this->role,
            status: UserStatus::SUSPENDED,
            twoFactorSecret: $this->twoFactorSecret,
            meta: $this->meta,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function with2FA(#[SensitiveParameter] string $secret): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            role: $this->role,
            status: $this->status,
            twoFactorSecret: $secret,
            meta: $this->meta,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function withMeta(array $meta): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            role: $this->role,
            status: $this->status,
            twoFactorSecret: $this->twoFactorSecret,
            meta: $meta,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function displayLabel(): string
    {
        return "{$this->name} ({$this->role->label()})";
    }

    // ----------------------------------------------------------
    // Serialization
    // ----------------------------------------------------------

    public function jsonSerialize(): array
    {
        return [
            'model' => self::MODEL,
            'version' => self::VERSION,
            'id' => $this->id,
            'name' => $this->name,
            'email' => (string) $this->email,
            'role' => $this->role->value,
            'status' => $this->status->value,
            'has2FA' => $this->has2FA(),
            'meta' => $this->meta,
            'createdAt' => $this->createdAt?->format('c'),
            'updatedAt' => $this->updatedAt?->format('c'),
        ];
    }

    public function __toString(): string
    {
        return sprintf('%s#%s<%s>', self::MODEL, $this->id, $this->role->value);
    }

    // ----------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------

    /**
     * Converts 'email' data from an array into an Email object.
     */
    private static function hydrateEmail(array $data): Email
    {
        $emailInput = $data['email'] ?? '';

        return $emailInput instanceof Email ? $emailInput : new Email($emailInput);
    }

    /**
     * Converts 'role' data from an array into a UserRole enum.
     * Uses UserRole::tryFrom for safe conversion, defaulting to VIEWER.
     */
    private static function hydrateRole(array $data): UserRole
    {
        $role = $data['role'] ?? null;

        if ($role instanceof UserRole) {
            return $role;
        }

        if (is_string($role) && $role !== '') {
            return UserRole::tryFrom($role) ?? UserRole::VIEWER;
        }

        return UserRole::VIEWER;
    }

    /**
     * Converts 'status' data from an array into a UserStatus enum.
     * Uses UserStatus::tryFrom for safe conversion, defaulting to ACTIVE.
     */
    private static function hydrateStatus(array $data): UserStatus
    {
        $status = $data['status'] ?? null;

        if ($status instanceof UserStatus) {
            return $status;
        }

        if (is_string($status) && $status !== '') {
            return UserStatus::tryFrom($status) ?? UserStatus::ACTIVE;
        }

        return UserStatus::ACTIVE;
    }

    /**
     * Converts a date string from an array into a DateTimeImmutable object.
     * Returns null if the key is missing, empty, or the date is invalid.
     */
    private static function hydrateDate(array $data, string $key): ?DateTimeImmutable
    {
        if (empty($data[$key])) {
            return null;
        }

        if ($data[$key] instanceof DateTimeImmutable) {
            return $data[$key];
        }

        try {
            return new DateTimeImmutable($data[$key]);
        } catch (Exception $e) {
            // Log error if needed: error_log("Failed to hydrate date '{$key}': {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Generates a random ID.
     * Uses a static *variable* for caching the Randomizer,
     * which is allowed in readonly classes.
     */
    private static function generateId(): string
    {
        static $rng = null;
        $rng ??= new Randomizer();

        return bin2hex($rng->getBytes(8));
    }
}
