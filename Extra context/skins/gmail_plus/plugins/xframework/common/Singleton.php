<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 * @codeCoverageIgnore
 */

trait Singleton {
    protected static $instance;

    public static function instance($parameters = null): static
    {
        return static::$instance ?? static::$instance = new static($parameters);
    }

    public static function hasInstance(): bool
    {
        return (bool)static::$instance;
    }

    public static function deleteInstance(): void
    {
        if (static::hasInstance()) {
            static::$instance = null;
        }
    }

    final public function __clone(): void
    {
        throw new \LogicException('Cloning is not allowed.');
    }

    final public function __sleep(): array
    {
        throw new \LogicException('Serialization is not allowed.');
    }

    final public function __wakeup(): void
    {
        throw new \LogicException('Unserialization is not allowed.');
    }
}