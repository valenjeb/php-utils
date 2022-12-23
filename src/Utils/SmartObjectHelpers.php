<?php

declare(strict_types=1);

namespace Devly\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function array_map;
use function get_class;
use function get_parent_class;
use function is_object;
use function preg_match_all;

use const PREG_SET_ORDER;

class SmartObjectHelpers
{
    /**
     * Returns array of magic properties defined by annotation `@property`.
     *
     * @internal
     *
     * @param class-string $class
     *
     * @return array<string, int> of [name => bit mask] @property.
     *
     * @throws ReflectionException
     */
    public static function getMagicProperties(string $class): array
    {
        static $cache;
        $props = &$cache[$class];
        if ($props !== null) {
            return $props;
        }

        $rc = new ReflectionClass($class);
        preg_match_all(
            '~^  [ \t*]*  @property(|-read|-write|-deprecated)  [ \t]+  [^\s]+  [ \t]+  \$  (\w+)  ()~mx',
            (string) $rc->getDocComment(),
            $matches,
            PREG_SET_ORDER
        );

        $props = [];
        foreach ($matches as [, $type, $name]) {
            $uname = Str::classify($name);

            $write = $type !== '-read'
                && $rc->hasMethod($nm = 'set' . $uname)
                && ($rm = $rc->getMethod($nm))->name === $nm && ! $rm->isPrivate() && ! $rm->isStatic();
            $read = $type !== '-write'
                && ($rc->hasMethod($nm = 'get' . $uname) || $rc->hasMethod($nm = 'is' . $uname))
                && ($rm = $rc->getMethod($nm))->name === $nm && ! $rm->isPrivate() && ! $rm->isStatic();

            if (! $read && ! $write) {
                continue;
            }

            $props[$name] = $read << 0
                | ($nm[0] === 'g') << 1 // @phpstan-ignore-line
                | $rm->returnsReference() << 2 // @phpstan-ignore-line
                | $write << 3
                | ($type === '-deprecated') << 4;
        }

        foreach ($rc->getTraits() as $trait) {
            $props += self::getMagicProperties($trait->name);
        }

        $parent = get_parent_class($class);

        if ($parent) {
            $props += self::getMagicProperties($parent);
        }

        return $props;
    }

    /**
     * @param class-string $class
     *
     * @return ReflectionMethod[]
     *
     * @throws ReflectionException
     */
    public static function getMagicMethods(string $class): array
    {
        static $cache;

        $props = &$cache[$class];
        if ($props !== null) {
            return $props;
        }

        $rc = new ReflectionClass($class);

        preg_match_all(
            '~^  [ \t*]*  @method  [ \t]+  [^\s]+  [ \t]+  (\w+)\( ~mx',
            (string) $rc->getDocComment(),
            $matches,
            PREG_SET_ORDER
        );

        $props = array_map(static function ($match) {
            return $match[1];
        }, $matches);

        foreach ($rc->getTraits() as $trait) {
            $props += self::getMagicMethods($trait->name);
        }

        $parent = get_parent_class($class);
        if ($parent) {
            $props += self::getMagicMethods($parent);
        }

        return $props;
    }

    /**
     * Checks whether the provided object has the specified property.
     *
     * @param object|class-string $class
     */
    public static function hasProperty($class, string $name): bool
    {
        $class = is_object($class) ? get_class($class) : $class;

        static $cache;
        $prop = &$cache[$class][$name];
        if ($prop === null) {
            $prop = false;
            try {
                $rp = new ReflectionProperty($class, $name);
                if ($rp->isPublic() && ! $rp->isStatic()) {
                    $prop = true;
                }
            } catch (ReflectionException $e) {
            }
        }

        return $prop;
    }
}
