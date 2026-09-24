<?php

namespace Sphp\Core;

class App
{
    protected static ?self $instance = null;
    protected array $bindings = [];
    protected array $instances = [];

    public function __construct()
    {
        static::$instance = $this;
    }

    public static function getInstance(): self
    {
        return static::$instance ??= new static();
    }

    /**
     * Set the globally available instance of the container.
     */
    public static function setInstance(?self $container = null): ?self
    {
        return static::$instance = $container;
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $key, mixed $instance): mixed
    {
        $this->instances[$key] = $instance;
        return $instance;
    }

    /**
     * Register a binding with the container.
     */
    public function bind(string $key, callable|string $resolver): void
    {
        unset($this->instances[$key]);
        $this->bindings[$key] = $resolver;
    }

    /**
     * Register a shared binding in the container (singleton).
     */
    public function singleton(string $key, callable|string $resolver): void
    {
        $this->bind($key, function (self $app, ...$parameters) use ($key, $resolver) {
            if (!isset($this->instances[$key])) {
                $this->instances[$key] = is_callable($resolver)
                    ? $resolver($app, ...$parameters)
                    : new $resolver(...$parameters);
            }
            return $this->instances[$key];
        });
    }

    /**
     * Check if the container has a binding or instance for the given key.
     */
    public function has(string $key): bool
    {
        return isset($this->instances[$key]) || isset($this->bindings[$key]);
    }

    /**
     * Resolve the given type from the container.
     */
    public function make(string $key, array $parameters = []): mixed
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        if (isset($this->bindings[$key])) {
            $resolver = $this->bindings[$key];
            return is_callable($resolver)
                ? $resolver($this, ...$parameters)
                : new $resolver(...$parameters);
        }

        if (class_exists($key)) {
            return new $key(...$parameters);
        }

        throw new \InvalidArgumentException("Target binding [{$key}] does not exist in container.");
    }

    /**
     * Alias for make().
     */
    public function get(string $key): mixed
    {
        return $this->make($key);
    }
}
