<?php

declare(strict_types=1);

namespace KaririCode\DevKit;

use Attribute;

/**
 * Length — Attribute for property length validation.
 *
 * Defines minimum and maximum length constraints for string properties.
 *
 * @package    KaririCode\DevKit
 * @category   Validation
 * @author     Walmir Silva <walmir.silva@kariricode.org>
 * @copyright  2025 KaririCode
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Length
{
    public function __construct(
        public readonly int $min = 0,
        public readonly int $max = PHP_INT_MAX,
    ) {
    }
}
