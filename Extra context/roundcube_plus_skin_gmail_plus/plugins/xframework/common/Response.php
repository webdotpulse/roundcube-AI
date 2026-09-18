<?php
namespace XFramework;

/**
 * New ajax response class. Replacing Plugin::sendResponse()
 */
class Response
{
    public static function success(mixed $data = [], ?string $message = ""): void
    {
        self::send(true, $data, $message);
    }

    public static function error(?string $message = ""): void
    {
        self::send(false, [], $message);
    }

    public static function send(bool $success = true, mixed $data = [], ?string $message = "", int $statusCode = 200): void
    {
        ob_get_contents() && @ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);

        exit(json_encode([
            "success" => $success,
            "message" => (string)$message,
            "data" => $data,
        ]));
    }
}