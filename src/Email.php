<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Email — Value Object for a user's email address.
 *
 * Ensures that email addresses are always valid.
 *
 * @package      KaririCode\DevKit
 * @category   Query Filtering
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
final readonly class Email implements Stringable, JsonSerializable
{
    public function __construct(
        #[Length(min: 3, max: 320)]
        public string $value,
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$value}");
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
