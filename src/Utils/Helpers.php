<?php

declare(strict_types=1);

namespace Devly\Utils;

use ReflectionClass;
use ReflectionException;
use Throwable;

use function gettype;
use function is_callable;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function rand;
use function strlen;

class Helpers
{
    /**
     * Executes a callback and returns the captured output as a string.
     *
     * @throws Throwable If Exception thrown during execution.
     */
    public static function capture(callable $func): string
    {
        ob_start(static function (): void {
        });
        try {
            $func();

            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }
    }

    /**
     * Converts `false` value to `null`
     *
     * @param T $value
     *
     * @return ?T
     *
     * @template T
     */
    public static function falseToNull($value)
    {
        return $value === false ? null : $value;
    }

    /**
     * Get the type of variable
     *
     * @param mixed $value The value to be checked
     */
    public static function getType($value): string
    {
        $type = gettype($value);

        switch ($type) {
            case 'integer':
                return 'int';

            case 'boolean':
                return 'bool';

            case 'NULL':
                return 'null';

            case 'object':
                try {
                    return (new ReflectionClass($value))->getName();
                } catch (ReflectionException $e) {
                    return 'object';
                }
            case 'string':
                if (is_callable($value)) {
                    return 'callable';
                }

                return 'string';

            case 'array':
                if (is_callable($value)) {
                    return 'callable';
                }

                return 'array';

            default:
                return $type;
        }
    }

    public static function generateRandomString(int $length = 6): string
    {
        $chars      = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charlength = strlen($chars);
        $str        = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $charlength - 1)];
        }

        return $str;
    }
}
