<?php

declare(strict_types=1);

namespace Devly\Utils;

use Error;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function debug_backtrace;
use function in_array;
use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

trait SmartObject
{
    /**
     * @return mixed
     *
     * @throws Error
     */
    public function &__get(string $name)
    {
        $class = static::class;
        $props = SmartObjectHelpers::getMagicProperties($class);

        $prop = $props[$name] ?? null;

        if ($prop) {
            if (! ($prop & 0b0001)) {
                throw new Error(sprintf('Cannot read a write-only property %s::$%s.', $class, $name));
            }

            $m = ($prop & 0b0010 ? 'get' : 'is') . Str::classify($name);
            if ($prop & 0b10000) {
                $trace = debug_backtrace(0, 1)[0]; // suppose this method is called from __call()
                $loc   = isset($trace['file'], $trace['line'])
                    ? ' in ' . $trace['file'] . ' on line ' . $trace['line']
                    : '';
                trigger_error(
                    sprintf('Property %s::$%s is deprecated, use %s::%s() method%s.', $class, $name, $class, $m, $loc),
                    E_USER_DEPRECATED
                );
            }

            if ($prop & 0b0100) { // return by reference
                return $this->$m();
            }

            $val = $this->$m();

            return $val;
        }

        $rc = new ReflectionClass(static::class);
        if (! $rc->hasProperty($name)) {
            throw new Error(sprintf('Property %s::$%s does not exist', $class, $name));
        }

        $rp     = $rc->getProperty($name);
        $access = $rp->isPrivate() ? 'private' : 'protected';

        throw new Error(sprintf('Cannot read a %s property %s::$%s.', $access, $class, $name));
    }

    /**
     * @param mixed $value
     *
     * @throws Error
     */
    public function __set(string $name, $value): void
    {
        $class = static::class;

        if (SmartObjectHelpers::hasProperty($class, $name)) { // unsetted property
            $this->$name = $value;

            return;
        } elseif ($prop = SmartObjectHelpers::getMagicProperties(static::class)[$name] ?? null) { // phpcs:ignore
            if (! ($prop & 0b1000)) {
                throw new Error(sprintf('Cannot write to a read-only property %s::$%s.', $class, $name));
            }

            $m = 'set' . Str::classify($name);
            if ($prop & 0b10000) {
                $trace = debug_backtrace(0, 1)[0]; // suppose this method is called from __call()
                $loc   = isset($trace['file'], $trace['line'])
                    ? sprintf(' in %s on line %s', $trace['file'], $trace['line'])
                    : '';
                trigger_error(
                    sprintf('Property %s::$%s is deprecated, use %s::%s() method%s.', $class, $name, $class, $m, $loc),
                    E_USER_DEPRECATED
                );
            }

            $this->$m($value);

            return;
        }

        $rc = new ReflectionClass(static::class);

        if (! $rc->hasProperty($name)) {
            throw new Error(sprintf('Property %s::$%s does not exist', $class, $name));
        }

        $rp     = $rc->getProperty($name);
        $access = $rp->isPrivate() ? 'private' : 'protected';

        throw new Error(sprintf('Cannot write to a %s property %s::$%s.', $access, $class, $name));
    }

    /**
     * @param array<T> $arguments
     *
     * @return mixed
     *
     * @throws Error
     *
     * @template T
     */
    public function __call(string $method, array $arguments)
    {
        try {
            $methods = SmartObjectHelpers::getMagicMethods(static::class);

            if (! in_array($method, $methods)) {
                throw new ReflectionException();
            }

            $mn = Str::camelize($method);

            $rm = new ReflectionMethod($this, $mn);
        } catch (ReflectionException $e) {
            throw new Error(sprintf('Method %s::%s() does not exist', static::class, $method));
        }

        if ($rm->isPublic() && ! $rm->isAbstract()) {
            try {
                return $rm->invokeArgs($this, $arguments);
            } catch (ReflectionException $e) {
                throw new Error($e->getMessage(), $e->getCode());
            }
        }

        $access = $rm->isAbstract() ? 'abstract' : ($rm->isProtected() ? 'protected' : 'private');

        throw new Error(sprintf(
            'Call to %s method %s::%s().',
            $access,
            static::class,
            $method
        ));
    }
}
