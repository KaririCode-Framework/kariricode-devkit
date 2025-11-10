<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

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
 * @package    KaririCode\DevKit
 * @category   Query Filtering
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case EDITOR = 'EDITOR';
    case VIEWER = 'VIEWER';

    public function canEdit(): bool
    {
        return match ($this) {
            self::ADMIN, self::EDITOR => true,
            self::VIEWER => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::EDITOR => 'editor',
            self::VIEWER => 'viewer',
        };
    }
}
