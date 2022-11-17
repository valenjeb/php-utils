<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Exceptions\ValidationError;
use Devly\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateE164PhoneNumber(): void
    {
        $this->assertTrue(Validator::validatePhoneNumber('+1-202-555-0198', Validator::PHONE_PATTERN_E164));
    }

    public function testInvalidPhoneNumberThrowsException(): void
    {
        $this->expectException(ValidationError::class);

        Validator::validatePhoneNumber('+01-202-555-0198', Validator::PHONE_PATTERN_E164);
    }

    public function testValidateLocalPhoneNumber(): void
    {
        $this->assertTrue(Validator::validatePhoneNumber('(541) 754-3010', Validator::PHONE_PATTERN_LOCAL));
    }

    public function testValidateIsraeliIdNumber(): void
    {
        $this->assertTrue(Validator::validateIsraeliIdNumber('123456782'));
    }

    public function testInvalidIsraeliIdNumberThrowsException(): void
    {
        $this->expectException(ValidationError::class);

        Validator::validateIsraeliIdNumber('133456782');
    }

    public function testValidateEmail(): void
    {
        $this->assertTrue(Validator::validateEmail('me@example.com'));
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(ValidationError::class);

        Validator::validateEmail('me@example');
    }
}
