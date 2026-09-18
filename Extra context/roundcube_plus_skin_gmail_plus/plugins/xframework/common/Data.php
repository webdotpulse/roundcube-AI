<?php
namespace XFramework;
/**
 * Roundcube Plus Framework plugin.
 *
 * This file provides a base class for the Roundcube Plus plugins.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once "Singleton.php";

/**
 * A singleton class that stores instances of classes and data available to all the plugins.
 * @package XFramework
 */
class Data {
    use Singleton;
    private array $data = [];

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function has($key): bool
    {
        return isset($this->data[$key]);
    }
}