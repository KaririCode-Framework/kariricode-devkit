<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * UserId — Value Object for a user's unique identifier.
 *
 * Ensures that user IDs are always non-empty strings.
 *
 * @package      KaririCode\DevKit
 * @category   Query Filtering
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
final readonly class UserId implements Stringable, JsonSerializable
{
    public function __construct(
        #[Length(min: 1, max: 50)]
        public string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('User ID cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
