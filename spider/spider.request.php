<?php

/**
 * Request class for handling HTTP requests and responses.
 *
 * Implements the `SpiderCoreComponent` interface and provides methods
 * for managing query strings, redirecting requests, and setting HTTP status codes.
 */
class Request implements SpiderCoreComponent
{
    /**
     * Version of the `Request` component.
     */
    const VERSION = '1.0.0';

    /**
     * HTTP status codes.
     */
    const HTTP_CODE_OK = 200;
    const HTTP_CODE_NOT_FOUND = 404;

    /**
     * @var int|null Current HTTP status code.
     */
    private static $statusCode;

    /**
     * Returns the version of the `Request` component.
     *
     * @return string Version string.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Retrieves query string parameters from the request.
     *
     * - If a key is provided, returns the sanitized value of the corresponding query parameter.
     * - If no key is provided, returns an array of all sanitized query parameters.
     *
     * @param string|null $key Specific key to retrieve from the query string.
     * @return mixed The sanitized value of the query parameter, or all parameters if no key is specified.
     */
    public static function getQueryString($key = null)
    {
        if ($key !== null) {
            return isset($_GET[$key]) ? filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING) : null;
        }
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING) ?: [];
    }

    /**
     * Retrieves the current URI from the server.
     *
     * @return string The requested URI, or `/` if not available.
     */
    public static function get()
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Redirects to the specified path.
     *
     * - Sends a `Location` header for redirection.
     * - Logs a warning if headers have already been sent.
     *
     * @param string $path The target path for redirection.
     * @return void
     */
    public static function redirect($path)
    {
        if (!headers_sent()) {
            header('Location: ' . $path);
            exit;
        } else {
            trigger_error("Headers already sent. Cannot redirect to $path.", E_USER_WARNING);
        }
    }

    /**
     * Sets the current HTTP status code and optionally sends it in the response header.
     *
     * - Supports both modern `http_response_code` and a fallback for older PHP versions.
     *
     * @param int $httpCode The HTTP status code to set.
     * @param bool $set_header Whether to send the status code in the response header.
     * @return void
     */
    public static function setStatusCode($httpCode, $set_header = false)
    {
        self::$statusCode = $httpCode;

        if ($set_header) {
            // Fallback for http_response_code in PHP < 5.4.
            if (function_exists('http_response_code')) {
                http_response_code($httpCode);
            } else {
                header('X-PHP-Response-Code: ' . $httpCode, true, $httpCode);
            }
        }
    }

    /**
     * Retrieves the current HTTP status code.
     *
     * @return int|null The current HTTP status code, or `null` if not set.
     */
    public static function getStatusCode()
    {
        return self::$statusCode;
    }
}
