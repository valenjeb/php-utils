<?php

declare(strict_types=1);

namespace Devly\Utils;

use Closure;
use RuntimeException;
use Throwable;

use function array_merge;
use function array_pad;
use function array_reduce;
use function array_reverse;
use function explode;
use function func_get_args;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function method_exists;

class Pipeline
{
    /**
     * The container implementation.
     */
    protected ?object $container;

    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
     *
     * @var array<callable|object>
     */
    protected array $pipes = [];

    /**
     * The method to call on each pipe.
     */
    protected string $method = 'handle';

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(?object $container = null)
    {
        $this->container = $container;
    }

    public static function create(?object $container = null): self
    {
        return new self($container);
    }

    /**
     * Set the object being sent through the pipeline.
     *
     * @param mixed $passable
     */
    public function send($passable): self
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param array<callable|object|string>|callable|object|string $pipes
     */
    public function through($pipes): self
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Set the method to call on the pipes.
     */
    public function via(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    /**
     * Run the pipeline and return the result.
     *
     * @return mixed
     */
    public function thenReturn()
    {
        return $this->then(static function ($passable) {
            return $passable;
        });
    }

    /**
     * Get the final piece of the Closure onion.
     */
    protected function prepareDestination(Closure $destination): Closure
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        // If the pipe is a callable, then we will call it directly, but otherwise we
                        // will resolve the pipes out of the dependency container and call it with
                        // the appropriate method and arguments, returning the results back out.
                        return $pipe($passable, $stack);
                    }

                    if (! is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);

                        // If the pipe is a string we will parse the string and resolve the class out
                        // of the dependency injection container. We can then build a callable and
                        // execute the pipe function giving in the parameters that are required.
                        $pipe = $this->getContainer()->get($name);

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        // If the pipe is already an object we'll just make a callable and pass it to
                        // the pipe as-is. There is no need to do any extra parsing and formatting
                        // since the object we're given was already a fully instantiated object.
                        $parameters = [$passable, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @return array<int, mixed>
     */
    protected function parsePipeString(string $pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Get the array of configured pipes.
     *
     * @return array<callable|object|string>
     */
    protected function pipes(): array
    {
        return $this->pipes;
    }

    /**
     * Get the container instance.
     *
     * @throws RuntimeException
     */
    protected function getContainer(): ?object
    {
        if (! $this->container) {
            throw new RuntimeException('A container instance has not been passed to the Pipeline.');
        }

        return $this->container;
    }

    /**
     * Set the container instance.
     */
    public function setContainer(object $container): self
    {
        if (! method_exists($container, 'get')) {
            throw new RuntimeException('The provided container does not implements `get($key)` method.');
        }

        $this->container = $container;

        return $this;
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     *
     * @param mixed $carry
     *
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @param mixed $passable
     *
     * @return mixed
     *
     * @throws Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        throw $e;
    }
}
