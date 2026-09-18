<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class retrieves js request data sent in standard post or php://input.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/Singleton.php';

class Input
{
    use Singleton;
    private array $data = [];
    private bool $initialized = false;

    /**
     * Gets all input data.
     *
     * @param bool $skipTokenCheck
     * @return array
     */
    public function getAll(bool $skipTokenCheck = false): array
    {
        $this->init($skipTokenCheck);
        return $this->data;
    }

    /**
     * Get a variable from the post.
     *
     * @param string $key
     * @param bool $skipTokenCheck
     * @return mixed
     */

    public function get(string $key, bool $skipTokenCheck = false): mixed
    {
        $this->init($skipTokenCheck);
        return array_key_exists($key, $this->data) ? $this->data[$key] : "";
    }

    /**
     * Check whether a variable exists in the post.
     *
     * @param string $key
     * @param bool $skipTokenCheck
     * @return boolean
     */
    public function has(string $key, bool $skipTokenCheck = false): bool
    {
        $this->init($skipTokenCheck);
        return isset($this->data[$key]);
    }

    /**
     * Fills an array with values from the POST. The array should contain a list of keys as values, the return will
     * contain those keys as keys and values from post as values.
     *
     * @param array $fields
     * @param bool $skipTokenCheck
     * @return array
     */
    public function fill(array $fields, bool $skipTokenCheck = false): array
    {
        $this->init($skipTokenCheck);
        $result = [];

        foreach ($fields as $key) {
            $result[$key] = array_key_exists($key, $this->data) ? $this->data[$key] : false;
        }

        return $result;
    }

    /**
     * Checks the Roundcube token sent with the request.
     * @codeCoverageIgnore
     */
    public function checkToken(): void
    {
        if (empty($_SERVER["HTTP_X_CSRF_TOKEN"]) ||
            $_SERVER["HTTP_X_CSRF_TOKEN"] != xrc()->get_request_token()
        ) {
            http_response_code(403);
            exit();
        }
    }

    /**
     * This is used for unit tesitng.
     * @param array $data
     * @codeCoverageIgnore
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->initialized = true;
    }

    /**
     * Fills the data directly from the php input.
     * @codeCoverageIgnore
     */
    protected function init(bool $skipTokenCheck): void
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $data = json_decode(file_get_contents('php://input'), true);
            $this->data = is_array($data) ? $data : [];

            if (!$skipTokenCheck) {
                $this->checkToken();
            }
        }
    }
}