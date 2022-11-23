<?php

declare(strict_types=1);

namespace Devly\Utils;

use function explode;
use function preg_match_all;
use function sprintf;
use function str_replace;

class Path
{
    protected static string $paramRegex = '/(\{[a-zA-Z_\-]+(:.*)?})/';
    /** @var string[] */
    protected static array $matchTypes = [
        'i'     => '\d+',
        'd'     => '\d+',
        'a'     => '[A-Za-z]+',
        'alnum' => '[A-Za-z0-9]+',
        'w'     => '\w+',
        ''      => '[-\w]+',
    ];

    public static function processPattern(string $pattern): string
    {
        $count = preg_match_all(self::$paramRegex, $pattern, $matches);

        if (! $count) {
            return $pattern;
        }

        foreach ($matches[0] as $match) {
            $match = str_replace(['{', '}'], [''], $match);

            if (Str::contains($match, ':')) {
                [$key, $regex] = explode(':', $match);
            } else {
                $key   = $match;
                $regex = '';
            }

            $regex   = self::$matchTypes[$regex] ?? $regex;
            $pattern = str_replace(
                '{' . $match . '}',
                sprintf('(?P<%s>%s)', $key, $regex),
                $pattern,
            );
        }

        return $pattern;
    }
}
