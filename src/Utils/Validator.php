<?php

declare(strict_types=1);

namespace Devly\Utils;

use Devly\Exceptions\ValidationError;

use function array_reduce;
use function is_numeric;
use function preg_match;
use function preg_replace;
use function str_split;
use function substr;

class Validator
{
    use StaticClass;

    public const PHONE_PATTERN_E164  = '/^(\+)?[1-9]{1,3}[0-9]{1,3}[0-9]{7,8}$/';
    public const PHONE_PATTERN_LOCAL = '/^[0-9]{2,3}[0-9]{7,8}$/';

    /** @throws ValidationError */
    public static function validateEmail(string $value): bool
    {
        if (
            ! preg_match(
                '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', // phpcs:ignore
                $value
            )
        ) {
            throw new ValidationError('Invalid email address');
        }

        return true;
    }

    /** @throws ValidationError */
    public static function validateIsraeliIdNumber(string $value): bool
    {
        if (! is_numeric($value) || Str::length($value) > 9) {
            throw new ValidationError('Invalid ID number');
        }

        $id    = Str::length($value) < 9 ? substr('00000000' . $value, -9) : $value;
        $parts = str_split($id);
        $i     = 0;
        $valid = array_reduce($parts, static function ($prev, $next) use (&$i) {
                $step = (int) $next * (($i % 2) + 1);
                $i++;

                return $prev + ($step > 9 ? $step - 9 : $step);
        }, 0) % 10 === 0;

        if (! $valid) {
            throw new ValidationError('Invalid ID number');
        }

        return true;
    }

    /** @throws ValidationError */
    public static function validatePhoneNumber(string $phone, string $pattern, bool $sanitize = true): bool
    {
        if ($sanitize) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        if (preg_match($pattern, $phone) !== 1) {
            throw new ValidationError('Invalid phone number.');
        }

        return preg_match($pattern, $phone) === 1;
    }
}
