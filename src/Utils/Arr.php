<?php

declare(strict_types=1);

namespace Devly\Utils;

use InvalidArgumentException;

use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_search;
use function array_walk_recursive;
use function count;
use function end;
use function explode;
use function func_num_args;
use function is_array;
use function is_callable;
use function key;
use function krsort;
use function ksort;
use function reset;
use function rsort;
use function sort;
use function sprintf;
use function uksort;
use function usort;

use const ARRAY_FILTER_USE_BOTH;
use const SORT_REGULAR;

class Arr
{
    use StaticClass;

    /**
     * Returns item from array. If it does not exist, it throws an exception, unless a default value is set.
     *
     * @param  array<T>              $array
     * @param  array-key|array-key[] $key
     * @param  ?T                    $default
     *
     * @return ?T
     *
     * @template T
     */
    public static function get(array $array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        if (Str::contains($key, '.') === false) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! self::exists($array, $segment)) {
                return $default;
            }

            $array = &$array[$segment];
        }

        return $array;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array<T>  $array
     * @param array-key $key
     *
     * @template T
     */
    public static function exists(array $array, $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * Returns reference to array item. If the index does not exist, new one is created with value null.
     *
     * @param  array<T>              $array
     * @param  array-key|array-key[] $key
     *
     * @return ?T
     *
     * @template T
     */
    public static function &getRef(array &$array, $key)
    {
        foreach (is_array($key) ? $key : [$key] as $k) {
            if (! is_array($array) && $array !== null) {
                throw new InvalidArgumentException('Traversed item is not an array.');
            }

            $array = &$array[$k];
        }

        return $array;
    }

    /**
     * Recursively merges two fields
     *
     * It is useful, for example, for merging tree structures. It behaves as
     * the + operator for array, i.e. it adds a key/value pair from the second array to the first one and retains
     * the value from the first array in the case of a key collision.
     *
     * @param  array<T1> $array1
     * @param  array<T2> $array2
     *
     * @return array<T1|T2>
     *
     * @template T1
     * @template T2
     */
    public static function mergeTree(array $array1, array $array2): array
    {
        $res = $array1 + $array2;
        foreach (array_intersect_key($array1, $array2) as $k => $v) {
            if (! is_array($v) || ! is_array($array2[$k])) {
                continue;
            }

            $res[$k] = self::mergeTree($v, $array2[$k]);
        }

        return $res;
    }

    /**
     * Returns zero-indexed position of given array key. Returns null if key is not found.
     *
     * @param array<T>  $array
     * @param array-key $key
     *
     * @return int|null offset if it is found, null otherwise
     *
     * @template T
     */
    public static function getKeyOffset(array $array, $key): ?int
    {
        $value = array_search(self::toKey($key), array_keys($array), true);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Returns the first item from the array or null if array is empty.
     *
     * @param  array<T> $array
     *
     * @return ?T
     *
     * @template T
     */
    public static function first(array $array)
    {
        return count($array) ? reset($array) : null;
    }

    /**
     * Returns the first item from the array passing a given truth test.
     *
     * @param array<T>                     $array
     * @param callable(TValue, TKey): bool $callback
     *
     * @return ?TValue
     *
     * @template T
     * @template TKey of T
     * @template TValue of T
     */
    public static function firstPass(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Returns the last item from the array or null if array is empty.
     *
     * @param  array<T> $array
     *
     * @return ?T
     *
     * @template T
     */
    public static function last(array $array)
    {
        return count($array) ? end($array) : null;
    }

    /**
     * Renames key in array.
     *
     * @param array<T>  $array
     * @param array-key $oldKey
     * @param array-key $newKey
     *
     * @template T
     */
    public static function renameKey(array &$array, $oldKey, $newKey): bool
    {
        $offset = self::getKeyOffset($array, $oldKey);

        if ($offset === null) {
            return false;
        }

        $val            = &$array[$oldKey];
        $keys           = array_keys($array);
        $keys[$offset]  = $newKey;
        $array          = array_combine($keys, $array);
        $array[$newKey] = &$val;

        return true;
    }

    /**
     * Transforms multidimensional array to flat array.
     *
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function flatten(array $array, bool $preserveKeys = false): array
    {
        $res = [];
        $cb  = $preserveKeys
            ? static function ($v, $k) use (&$res): void {
                $res[$k] = $v;
            }
            : static function ($v) use (&$res): void {
                $res[] = $v;
            };

        array_walk_recursive($array, $cb);

        return $res;
    }

    /**
     * Transforms a multi-dimensional array into a single level array
     * that uses "dot" notation to indicate depth
     *
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $flatten = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $flatten[] = self::dot($value, $prepend . $key . '.');
            } else {
                $flatten[] = [$prepend . $key => $value];
            }
        }

        return array_merge(...$flatten);
    }

    /**
     * Returns and removes the value of an item from an array.
     *
     * @param  array<T>  $array
     * @param  array-key $key
     * @param  ?T        $default
     *
     * @return ?T
     *
     * @template T
     */
    public static function pick(array &$array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        if (func_num_args() < 3) {
            throw new InvalidArgumentException(sprintf("Missing item '%s'.", $key));
        }

        return $default;
    }

    /**
     * Tests whether at least one element in the array passes the test  implemented by the provided
     * callback which has the signature `function ($value, $key, array $array): bool`.
     *
     * @param iterable<T>    $array
     * @param callable(mixed $value, array-key $key, array<T> $array): bool $callback
     *
     * @template T
     */
    public static function some(iterable $array, callable $callback): bool
    {
        foreach ($array as $k => $v) {
            if ($callback($v, $k, $array)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tests whether all elements in the array pass the test implemented by the provided function,
     * which has the signature `function ($value, $key, array $array): bool`.
     *
     * @param iterable<T>    $array
     * @param callable(mixed $value, array-key $key, array<T> $array): bool $callback
     *
     * @template T
     */
    public static function every(iterable $array, callable $callback): bool
    {
        foreach ($array as $k => $v) {
            if (! $callback($v, $k, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calls $callback on all elements in the array and returns the array of return values.
     * The callback has the signature `function ($value, $key, array $array): bool`.
     *
     * @param iterable<T>    $array
     * @param callable(mixed $value, array-key $key, array<T> $array): mixed $callback
     *
     * @return array<T>
     *
     * @template T
     */
    public static function map(iterable $array, callable $callback): array
    {
        $res = [];
        foreach ($array as $k => $v) {
            $res[$k] = $callback($v, $k, $array);
        }

        return $res;
    }

    /**
     * Invoke callback on each item in the array.
     *
     * @param array<T>       $array
     * @param callable(mixed $value, array-key $key, array<T> $array): void $callback
     *
     * @template T
     */
    public static function each(array $array, callable $callback): void
    {
        foreach ($array as $k => $v) {
            $callback($v, $k, $array);
        }
    }

    /**
     * Invokes all callbacks and returns array of results.
     *
     * @param callable[] $callbacks
     * @param mixed      ...$args
     *
     * @return array<array-key, mixed>
     */
    public static function invoke(iterable $callbacks, ...$args): array
    {
        $res = [];
        foreach ($callbacks as $k => $cb) {
            $res[$k] = $cb(...$args);
        }

        return $res;
    }

    /**
     * Invokes method on every object in an array and returns array of results.
     *
     * @param object[] $objects
     * @param mixed    ...$args
     *
     * @return array<array-key, mixed>
     */
    public static function invokeMethod(iterable $objects, string $method, ...$args): array
    {
        $res = [];
        foreach ($objects as $k => $obj) {
            $res[$k] = $obj->$method(...$args);
        }

        return $res;
    }

    /**
     * Copies the elements of the $array array to the $object object and then returns it.
     *
     * @param iterable<T1> $array
     * @param T2           $object
     *
     * @return T2
     *
     * @template T1
     * @template T2 of object
     */
    public static function toObject(iterable $array, $object)
    {
        foreach ($array as $k => $v) {
            $object->$k = $v;
        }

        return $object;
    }

    /**
     * Converts value to array key.
     *
     * @param mixed $value
     *
     * @return array-key
     */
    public static function toKey($value)
    {
        return key([$value => null]);
    }

    /**
     * @param array<T>  $array
     * @param array-key $key
     * @param mixed     $value
     *
     * @return array<T>
     *
     * @template T
     */
    public static function add(array $array, $key, $value): array
    {
        if (! isset($array[$key])) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param  array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Filter items where the value is not null.
     *
     * @param  array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function whereNotNull(array $array): array
    {
        return static::where($array, static function ($value) {
            return $value !== null;
        });
    }

    /**
     * Sort through each item with a callback.
     *
     * @param array<T>                                 $array
     * @param callable(array-key, mixed): int|int|null $callback
     *
     * @return array<T>
     *
     * @template T
     */
    public static function sort(array $array, $callback = null): array
    {
        $callback && is_callable($callback)
            ? usort($array, $callback)
            : sort($array, $callback ?? SORT_REGULAR);

        return $array;
    }

    /**
     * Sort items in descending order.
     *
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function sortDesc(array $array, int $options = SORT_REGULAR): array
    {
        rsort($array, $options);

        return $array;
    }

    /**
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function sortRecursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (! is_array($value)) {
                continue;
            }

            $value = static::sortRecursive($value, $options, $descending);
        }

        if (! self::isList($array)) {
            $descending
                ? krsort($array, $options)
                : ksort($array, $options);
        } else {
            $descending
                ? rsort($array, $options)
                : sort($array, $options);
        }

        return $array;
    }

    /**
     * Checks whether a given array is a list.
     *
     * Determines if the given array is a list. An array is considered a list
     * if its keys consist of consecutive numbers from 0 to count($array)-1.
     *
     * @param array<T> $array
     *
     * @template T
     */
    public static function isList(array $array): bool
    {
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sort the collection keys.
     *
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function sortKeys(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        $descending ? krsort($array, $options) : ksort($array, $options);

        return $array;
    }

    /**
     * Sort the collection keys in descending order.
     *
     * @param array<T> $array
     *
     * @return array<T>
     *
     * @template T
     */
    public static function sortKeysDesc(array $array, int $options = SORT_REGULAR): array
    {
        return self::sortKeys($array, $options, true);
    }

    /**
     * Sort the collection keys using a callback.
     *
     * @param array<T>                  $array
     * @param callable(TKey, TKey): int $callback
     *
     * @return array<T>
     *
     * @template T
     * @template TKey of array-key
     */
    public static function sortKeysUsing(array $array, callable $callback): array
    {
        uksort($array, $callback);

        return $array;
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param array<T>|T $value
     *
     * @return array<T>
     *
     * @template T
     */
    public static function wrap($value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}
