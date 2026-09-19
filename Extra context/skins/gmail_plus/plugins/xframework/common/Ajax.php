<?php
namespace XFramework;

require_once "Singleton.php";

class Ajax
{
    use Singleton;
    private \rcube $rcmail;
    private ?array $phpInputData = null;

    public function __construct()
    {
        $this->rcmail = \rcube::get_instance();
    }

    /**
     * Returns a POST variable
     *
     * @param string $key The POST key to retrieve.
     */
    public function get(string $key)
    {
        $value = \rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST);

        if ($value === null) {
            $value = $this->getPhpInputData()[$key] ?? null;
        }

        return $value;
    }

    /**
     * Returns a POST variable cast to string.
     *
     * @param string $key
     * @return string
     */
    public function getString(string $key): string
    {
        $value = $this->get($key);
        return is_array($value) ? '' : (string)$value;
    }

    /**
     * Returns a POST variable cast to bool.
     *
     * @param string $key
     * @return bool
     */
    public function getBool(string $key): bool
    {
        $value = $this->get($key);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        return (bool)$value;
    }

    /**
     * Returns a POST variable cast to int.
     *
     * @param string $key
     * @return int
     */
    public function getInt(string $key): int
    {
        $value = $this->get($key);

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * Returns a POST variable cast to float.
     *
     * @param string $key
     * @return float
     */
    public function getFloat(string $key): float
    {
        $value = $this->get($key);

        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        return 0.0;
    }

    /**
     * Returns a POST variable as array.
     *
     * @param string $key
     * @return array
     */
    public function getArray(string $key): array
    {
        $value = $this->get($key);

        return is_array($value) ? $value : [];
    }

    /**
     * Returns all POST variables.
     *
     * @return array
     */
    public function getAll(): array
    {
        if (!empty($_POST)) {
            $result = [];
            foreach ($_POST as $key => $unused) {
                $result[$key] = \rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST);
            }
            return $result;
        }

        return $this->getPhpInputData();
    }

    /**
     * Checks if a POST variable exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (\rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST) !== null) {
            return true;
        }

        return isset($this->getPhpInputData()[$key]);
    }

    /**
     * Verifies the Roundcube request token.
     *
     * @return bool
     */
    public function verifyToken(): bool
    {
        if (\rcube_utils::request_header('X-Roundcube-Request') === $this->rcmail->get_request_token()) {
            return true;
        }

        http_response_code(403);
        exit();
    }

    /**
     * Sends a successful AJAX response.
     *
     * @param string $command The client-side command to trigger.
     * @param array $data The payload data to send.
     * @return void
     */
    public function success(string $command, array $data = []): void
    {
        $this->send($command, ['success' => true, 'data' => $data]);
    }

    /**
     * Sends an error AJAX response.
     *
     * @param string $command The client-side command to trigger.
     * @param string $message An optional error message.
     * @param array $data Additional payload data.
     * @param bool $showPopupError Whether to display the error message as a popup in Roundcube.
     * @return void
     */
    public function error(string $command, string $message = '', array $data = [], bool $showPopupError = false): void
    {
        if ($message && $showPopupError) {
            $this->showMessage($message, 'error');
        }

        $this->send($command, ['success' => false, 'message' => $message, 'data' => $data]);
    }

    /**
     * Sends an AJAX response. Automatically adds the request ID from the POST payload
     * so the frontend can match the response to the correct callback.
     *
     * @param string $command The client-side command to trigger.
     * @param array $payload The response data to send.
     * @return void
     */
    public function send(string $command, array $payload): void
    {
        $payload['x_request_id'] = $this->getString('x_request_id');
        $this->rcmail->output->command($command, $payload);
        $this->rcmail->output->send();
    }

    /**
     * Displays a popup message in the frontend.
     *
     * @param string $message The message to display, can be a gettext id or a text.
     * @param string $type The message type: 'notice', 'confirm', or 'error'.
     * @param array $vars Optional key-value pairs to replace in localized text.
     * @param bool $override Whether to override the last message.
     * @param int $timeout Duration to display the message, in seconds.
     * @return void
     */
    public function showMessage(string $message, string $type = 'notice', array $vars = [],
                                bool $override = true, int $timeout = 0): void
    {
        $this->rcmail->output->show_message($message, $type, $vars, $override, $timeout);
    }

    /**
     * Triggers a frontend redirect.
     *
     * @param string|array $target A string with the action name or an array of URL parameters.
     * @param int $delay Delay before the redirect, in seconds.
     * @return void
     */
    public function redirect(string|array $target = [], int $delay = 1): void
    {
        $this->rcmail->output->redirect($target, $delay);
    }

    /**
     * Returns parsed JSON input data.
     *
     * @return array
     */
    private function getPhpInputData(): array
    {
        if ($this->phpInputData === null) {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->phpInputData = is_array($data) ? $data : [];
        }

        return $this->phpInputData;
    }
}