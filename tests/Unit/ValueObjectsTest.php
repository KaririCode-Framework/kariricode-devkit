<?php

declare(strict_types=1);

namespace KaririCode\DevKit\Tests\Unit;

use InvalidArgumentException;
use KaririCode\DevKit\Email;
use KaririCode\DevKit\UserId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validation and behavior of Value Objects.
 * This class ensures that Email and UserId value objects correctly handle valid
 * and invalid inputs, and that their string and JSON serialization methods work as expected.
 */
#[CoversClass(Email::class)]
#[CoversClass(UserId::class)]
class ValueObjectsTest extends TestCase
{
    /**
     * Tests that an Email object is successfully created with a valid email address.
     * It also verifies the __toString() and jsonSerialize() methods.
     */
    #[Test]
    public function emailIsCreatedWithValidValue(): void
    {
        $email = new Email('teste@exemplo.com');
        $this->assertSame('teste@exemplo.com', $email->value);
        // Verify string casting and JSON serialization
        $this->assertSame('teste@exemplo.com', (string) $email);
        $this->assertSame('teste@exemplo.com', $email->jsonSerialize());
    }

    #[Test]
    public function emailThrowsExceptionForInvalidValue(): void
    {
        // Expect an InvalidArgumentException to be thrown for an invalid email format
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email: email-invalido');

        // Attempt to create an Email object with an invalid string, which should trigger the exception
        new Email('email-invalido');
    }

    /**
     * Tests that a UserId object is successfully created with a valid, non-empty ID string.
     * It also verifies the __toString() and jsonSerialize() methods.
     */
    #[Test]
    public function userIdIsCreatedWithValidValue(): void
    {
        $id = new UserId('id-12345');
        $this->assertSame('id-12345', $id->value);
        // Verify string casting and JSON serialization
        $this->assertSame('id-12345', (string) $id);
        $this->assertSame('id-12345', $id->jsonSerialize());
    }

    /**
     * Tests that a UserId object throws an exception when initialized with an empty string.
     */
    #[Test]
    public function userIdThrowsExceptionForEmptyValue(): void
    {
        // Expect an InvalidArgumentException to be thrown for an empty user ID
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID cannot be empty.');

        new UserId(''); // Isto deve falhar
    }
}
