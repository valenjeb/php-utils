<?php

declare(strict_types=1);

namespace Devly\Utils;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

use function class_parents;
use function explode;
use function is_array;
use function is_callable;
use function is_string;

class Obj
{
    /**
     * Create reflection from a callable or a class name.
     *
     * @param string|callable|class-string<T>|T $definition A callable or a class name to reflect.
     *
     * @return ReflectionFunction|ReflectionMethod|ReflectionClass<T|object>
     *
     * @throws ReflectionException
     *
     * @template T of object
     */
    public static function createReflection($definition)
    {
        if (is_callable($definition) || $definition instanceof Closure) {
            if (is_string($definition) && Str::contains($definition, '::')) {
                $definition = explode('::', $definition);
            }

            if (is_array($definition)) {
                return new ReflectionMethod($definition[0], $definition[1]);
            }

            return new ReflectionFunction($definition);
        }

        if (is_string($definition) && Str::contains($definition, '@')) {
            $definition = explode('@', $definition);

            return new ReflectionMethod($definition[0], $definition[1]);
        }

        return new ReflectionClass($definition);
    }

    /**
     * Gets an array of methods for the class
     *
     * @param ReflectionClass<T>|class-string $class       A class name or instance of ReflectionClass.
     * @param int|null                        $filter      Filter the results to include only methods with.
     *                                                     certain attributes. Defaults to no filtering.
     * @param bool                            $withParents Whether to include the parent object methods.
     * @param bool                            $withTraits  Whether to include the traits methods.
     *
     * @return ReflectionMethod[]
     *
     * @throws ReflectionException
     *
     * @template T of object
     */
    public static function getMethods(
        $class,
        ?int $filter = null,
        bool $withParents = false,
        bool $withTraits = false
    ): array {
        if (is_string($class)) {
            $class = new ReflectionClass($class);
        }

        $methods = $class->getMethods($filter);

        if (! $withTraits) {
            foreach ($class->getTraits() as $trait) {
                $methods += $trait->getMethods($filter);
            }
        }

        if (! $withParents) {
            return $methods;
        }

        $parents = class_parents($class->getName());

        if (empty($parents)) {
            return $methods;
        }

        foreach ($parents as $parent) {
            $rc = new ReflectionClass($parent);

            $methods += $rc->getMethods($filter);
        }

        return $methods;
    }
}
