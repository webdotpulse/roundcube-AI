<?php
namespace XFramework;

class Session
{
    protected string $section;
    
    public function __construct(string $section)
    {
        $this->section = $section;
        
        if (!isset($_SESSION[$this->section])) {
            $_SESSION[$this->section] = [];
        }
    }

    /**
     * Checks if a key exists in a specific session section.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION[$this->section]);
    }

    /**
     * Sets a value in a specific session section and key.
     *
     * @param string $key The key to set.
     * @param mixed $value The value to store.
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$this->section][$key] = $value;
    }

    /**
     * Retrieves a value from a specific session section and key.
     *
     * @param string $key The key to retrieve.
     * @return mixed|null The value or null if not found.
     */
    public function get(string $key, $default = null): mixed
    {
        return $_SESSION[$this->section][$key] ?? $default;
    }

    /**
     * Removes a variable from session.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        if (isset($_SESSION[$this->section][$key])) {
            unset($_SESSION[$this->section][$key]);
        }
    }
}