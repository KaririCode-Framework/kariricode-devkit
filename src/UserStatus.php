<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

/**
 * UserStatus — Enum for user account status.
 *
 * Defines the possible states for a user account (e.g., active, suspended).
 *
 * @package      KaririCode\DevKit
 * @category   Query Filtering
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
