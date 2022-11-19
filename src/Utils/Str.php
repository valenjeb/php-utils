<?php

declare(strict_types=1);

namespace Devly\Utils;

use InvalidArgumentException;
use Devly\Exceptions\NotSupportedException;
use Exception;
use voku\helper\ASCII;

use function class_exists;
use function function_exists;
use function is_array;
use function is_string;
use function lcfirst;
use function mb_convert_case;
use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_last_error;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_split;
use function rawurlencode;
use function sprintf;
use function str_replace;
use function str_word_count;
use function strlen;
use function strncmp;
use function strpos;
use function strtolower;
use function substr;
use function ucwords;
use function utf8_decode;

use const MB_CASE_TITLE;
use const PREG_SPLIT_DELIM_CAPTURE;

class Str
{
    use StaticClass;

    /**
     * Transliterate a UTF-8 value to ASCII.
     */
    public static function ascii(string $value, string $language = 'en'): string
    {
        if (! class_exists('voku\helper\ASCII')) {
            throw new NotSupportedException(__METHOD__ . '() requires "voku/portable-ascii".');
        }

        return ASCII::to_ascii($value, $language); // @phpstan-ignore-line
    }

    /**
     * Checks if a string is 7 bit ASCII.
     */
    public static function isAscii(string $string): bool
    {
        return ASCII::is_ascii($string);
    }

    /**
     * Starts the $haystack string with the prefix $needle?
     *
     * @param string|string[] $needle
     */
    public static function startsWith(string $haystack, $needle): bool
    {
        if (is_string($needle)) {
            return strncmp($haystack, $needle, strlen($needle)) === 0;
        }

        if (! is_array($needle)) {
            throw new InvalidArgumentException(
                __METHOD__ . '() #2 parameter $needle must be a string or an array of strings.'
            );
        }

        foreach ($needle as $n) {
            if (self::startsWith($haystack, $n)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ends the $haystack string with the suffix $needle?
     *
     * @param string|string[] $needle
     */
    public static function endsWith(string $haystack, $needle): bool
    {
        if (is_string($needle)) {
            return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
        }

        if (! is_array($needle)) {
            throw new InvalidArgumentException(
                __METHOD__ . '() #2 parameter $needle must be a string or an array of strings.'
            );
        }

        foreach ($needle as $n) {
            if (self::endsWith($haystack, $n)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does $haystack contain $needle?
     *
     * @param string|string[] $needle
     */
    public static function contains(string $haystack, $needle, bool $strict = true): bool
    {
        $contains = false;

        foreach ((array) $needle as $n) {
            if (strpos($haystack, $n) === false) {
                if ($strict) {
                    return false;
                }
            } else {
                $contains = true;
            }
        }

        return $contains;
    }

    /**
     * Replaces all occurrences matching regular expression.
     *
     * @param string|string[] $search      Regular expression or array in the
     *                                     form `pattern => replacement`.
     * @param string|string[] $replacement If pattern is a string this will
     */
    public static function replace($search, $replacement, string $subject): string
    {
        return str_replace($search, $replacement, $subject);
    }

    /**
     * Checks if given string matches a regular expression.
     *
     * @return string|string[]|null
     */
    public static function match(string $pattern, string $subject, bool $all = false)
    {
        if ($all) {
            preg_match_all($pattern, $subject, $matches);
        } else {
            preg_match($pattern, $subject, $matches);
        }

        if (! $matches || empty($matches[0])) {
            return null;
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Split string by a regular expression
     *
     * @return string[]
     *
     * @throws Exception if error occurs during execution.
     */
    public static function split(string $subject, string $pattern, int $flags = 0): array
    {
        $split = @preg_split($pattern, $subject, -1, $flags | PREG_SPLIT_DELIM_CAPTURE);

        if ($split === false && preg_last_error()) {
            throw new Exception(preg_last_error_msg(), preg_last_error());
        }

        return $split;
    }

    public static function classify(string $word): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }

    /**
     * converts the given string to camelCase
     */
    public static function camelize(string $word): string
    {
        return lcfirst(static::classify($word));
    }

    public static function kebab(string $word): string
    {
        return self::snake($word, '-');
    }

    public static function snake(string $word, string $delimiter = '_'): string
    {
        $replacement = sprintf('$1%s', $delimiter);
        $s           = preg_replace('#([^.])(?=[A-Z])#', $replacement, $word);

        return rawurlencode(strtolower($s));
    }

    /**
     * Converts all characters of UTF-8 string to lower case.
     */
    public static function lower(string $s): string
    {
        return mb_strtolower($s, 'UTF-8');
    }

    /**
     * Converts all characters of a UTF-8 string to upper case.
     */
    public static function upper(string $s): string
    {
        return mb_strtoupper($s, 'UTF-8');
    }

    /**
     * Make a string's first character lowercase.
     */
    public static function lcfirst(string $string): string
    {
        return static::lower(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    /**
     * Make a string's first character uppercase.
     */
    public static function ucfirst(string $string): string
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     */
    public static function substr(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Converts the first character of every word of a UTF-8 string to upper case and the others to lower case.
     */
    public static function capitalize(string $s): string
    {
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Returns number of characters (not bytes) in UTF-8 string.
     *
     * That is the number of Unicode code points which may differ from the number of graphemes.
     */
    public static function length(string $s): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($s, 'UTF-8')
            : strlen(utf8_decode($s));
    }

    /**
     * Returns the number of words that a string contains
     */
    public static function wordCount(string $string, string $characters = ''): int
    {
        return str_word_count($string, 0, $characters);
    }
}
