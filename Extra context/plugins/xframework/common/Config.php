<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class handles the date/time formats and their conversions to the formats used by different systems/components.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

class Config
{
    private \rcube $rcmail;
    private const TYPES = ['int', 'float', 'string', 'bool', 'array'];
    private bool $hasData;

    public function __construct(private array $data = []) {
        $this->rcmail = xrc();
        $this->hasData = !empty($data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->hasData) {
            return $this->data[$key] ?? $default;
        }

        return $this->rcmail->config->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->hasData) {
            $this->data[$key] = $value;
        } else {
            $this->rcmail->config->set($key, $value);
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @throws \Exception
     */
    public function validate(array $configSchema): void
    {
        foreach ($configSchema as $key => $schema) {
            if (!is_array($schema)) {
                throw new \Exception("Config schema for '$key' must be an array. (273884)");
            }

            if (!is_string($schema['type'] ?? null) || !in_array($schema['type'], self::TYPES, true)) {
                throw new \Exception("Invalid config schema type for '$key'. (473882)");
            }

            if (!array_key_exists('default', $schema)) {
                throw new \Exception("Missing config schema default for '$key'. (472884)");
            }

            if (isset($schema['options']) && !is_array($schema['options'])) {
                throw new \Exception("Config schema options for '$key' must be an array. (582910)");
            }

            if (isset($schema['pattern']) &&
                (!is_string($schema['pattern']) || @preg_match($schema['pattern'], '') === false)
            ) {
                throw new \Exception("Invalid config schema pattern for '$key'. (629471)");
            }

            if (isset($schema['min']) && !is_int($schema['min']) && !is_float($schema['min'])) {
                throw new \Exception("Config schema min for '$key' must be numeric. (739284)");
            }

            if (isset($schema['max']) && !is_int($schema['max']) && !is_float($schema['max'])) {
                throw new \Exception("Config schema max for '$key' must be numeric. (628194)");
            }

            // get value, either from $data, if specified, or rcmail->config
            $value = $this->get($key);

            // set default if missing
            if ($value === null) {
                $this->set($key, $schema['default']);
                continue;
            }

            // convert the value to the configured type
            $valid = true;

            switch ($schema['type']) {
                case 'string':
                    $value = is_array($value) ? '' : (string)$value;
                    break;

                case 'bool':
                    $value = (bool)$value;
                    break;

                case 'int':
                    if (is_string($value)) {
                        $value = trim($value);
                    }

                    if (!is_numeric($value)) {
                        $valid = false;
                        break;
                    }

                    $value = (int)$value;
                    break;

                case 'float':
                    if (is_string($value)) {
                        $value = trim($value);
                    }

                    if (!is_numeric($value)) {
                        $valid = false;
                        break;
                    }

                    $value = (float)$value;
                    break;

                case 'array':
                    if (!is_array($value)) {
                        $valid = false;
                    }
                    break;
            }

            // check value
            if ($valid &&
                isset($schema['options']) &&
                !in_array($value, $schema['options'], true)
            ) {
                $valid = false;
            }

            if ($valid &&
                is_string($value) &&
                isset($schema['pattern']) &&
                preg_match($schema['pattern'], $value) !== 1
            ) {
                $valid = false;
            }

            if ($valid &&
                (is_int($value) || is_float($value)) &&
                isset($schema['min']) && $value < $schema['min']
            ) {
                $valid = false;
            }

            if ($valid &&
                (is_int($value) || is_float($value)) &&
                isset($schema['max']) && $value > $schema['max']
            ) {
                $valid = false;
            }

            if (!$valid) {
                $value = $schema['default'];
            }

            $this->set($key, $value);
        }
    }
}