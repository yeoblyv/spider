<?php

/**
 * Utils class provides utility methods for common tasks.
 *
 * Implements the `SpiderCoreComponent` interface and includes methods for
 * generating strings, handling global values, calculating execution times, and retrieving URLs.
 */
class Utils implements SpiderCoreComponent
{
    /**
     * Version of the `Utils` component.
     */
    const VERSION = "1.0.0";

    /**
     * Returns the version of the `Utils` component.
     *
     * @return string Version string.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Generates a random string of a specified length using the provided characters.
     *
     * - Uses the `mt_rand()` function for randomness.
     * - Default character set includes numbers, lowercase, and uppercase letters.
     *
     * @param int $length The length of the generated string. Default is 32.
     * @param string $characters A string of characters to use for generating the random string.
     * @return string The generated random string.
     */
    public static function generateString($length = 32, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $randomString = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $maxIndex)];
        }

        return $randomString;
    }

    /**
     * Retrieves the base link (protocol and domain) of the current URL.
     *
     * - Determines the protocol (`http` or `https`) based on the `HTTPS` server variable.
     * - Uses the `HTTP_HOST` server variable to get the domain name.
     * - Defaults to `localhost` if the host is not available.
     *
     * @return string The base URL, including the protocol and domain.
     */
    public static function getLink()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $urlParts = parse_url("$protocol://$host");
        $domain = $urlParts['host'] ?? 'localhost';

        return $protocol . '://' . $domain;
    }

    /**
     * Calculates the execution time in seconds since a global `$startTime` was set.
     *
     * - Assumes `$startTime` is a `float` representing the script start time in microseconds.
     * - Logs a warning if `$startTime` is not set, and returns 0.
     *
     * @global float $startTime The start time of the script.
     * @return float The execution time rounded to 4 decimal places.
     */
    public static function getLoadTime()
    {
        global $startTime;

        if (!isset($startTime)) {
            trigger_error("Start time is not set. Returning 0 as load time.", E_USER_WARNING);
            return 0;
        }

        $endTime = microtime(true);
        $execution_time = ($endTime - $startTime);
        return round($execution_time, 4);
    }

    /**
     * Sets a key-value pair in a global `$values` array.
     *
     * - If the `$values` array is not defined, initializes it as an empty array.
     *
     * @global array $values The global values array.
     * @param string $key The key under which the value will be stored.
     * @param mixed $value The value to store.
     * @return void
     */
    public static function setValue($key, $value)
    {
        global $values;

        if (!isset($values) || !is_array($values)) {
            $values = [];
        }

        $values[$key] = $value;
    }

    /**
     * Retrieves a value by key from the global `$values` array.
     *
     * - Returns `null` if the `$values` array is not defined or if the key does not exist.
     *
     * @global array $values The global values array.
     * @param string $key The key to retrieve the value for.
     * @return mixed|null The value associated with the key, or `null` if not found.
     */
    public static function getValue($key)
    {
        global $values;

        if (isset($values) && is_array($values) && array_key_exists($key, $values)) {
            return $values[$key];
        }

        return null;
    }
}
