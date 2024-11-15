<?php

/**
 * Spider: A lightweight PHP web-engine for developing dynamic websites & web-applications.
 *
 * Fleeing fast with frantic stride,
 * I forgot I’d tried to hide.
 * Like a spider, making web
 * Out of worries, out of dread.
 *
 * This file defines the main Spider framework class and interface,
 * which together form the core of the framework's functionality.
 *
 * @author Yehor Oblyvantsov
 * @copyright 2024 © Oblyvantsov.com
 * @version 1.0.0
 * @license MIT License
 */

/**
 * Defines the required methods for a Spider core component.
 * All implementing classes must provide a version via the `getVersion` method.
 */
interface SpiderCoreComponent
{
    /**
     * Retrieves the version of the core component.
     *
     * @return string Version of the component.
     */
    public static function getVersion();
}

/**
 * Main framework class that manages core loading, component registration, and versioning.
 */
class Spider implements SpiderCoreComponent
{
    /**
     * Framework version.
     */
    const VERSION = "1.0.0";

    /**
     * PHP version the framework runs on.
     */
    const PLATFORM_VERSION = PHP_VERSION;

    /**
     * Registered components with their versions.
     *
     * @var array
     */
    private static $components = [];

    /**
     * Initializes the Spider framework.
     *
     * - Loads core files.
     * - Registers available components.
     * - Ensures the `Response` class is loaded for content type management.
     *
     * @throws RuntimeException If the `Response` class is not found.
     * @return void
     */
    public static function web()
    {
        self::loadCore(); // Load core PHP files.
        self::registerComponents(); // Register all core components.

        if (class_exists('Response')) {
            // Initialize content types using the Response class.
            Response::getContentTypes();
        } else {
            // Throw an error if the Response class is not found.
            throw new RuntimeException("Class 'Response' not found. Please ensure it is included and autoloaded properly.");
        }
    }

    /**
     * Retrieves the framework version.
     *
     * @return string Current framework version.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Loads core PHP files from the current directory, excluding the main file (`spider.php`).
     *
     * - Scans the directory for PHP files.
     * - Includes each valid file.
     * - Logs a warning if a file is missing.
     *
     * @return void
     */
    private static function loadCore()
    {
        $dir = __DIR__; // Get the current directory.
        $phpFiles = array_diff(scandir($dir), ['.', '..', 'spider.php']); // List all PHP files excluding specific ones.

        foreach ($phpFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file; // Build the full path to the file.
                if (file_exists($filePath)) {
                    include_once $filePath; // Include the file.
                } else {
                    // Log a warning if the file does not exist.
                    trigger_error("File not found: $filePath", E_USER_WARNING);
                }
            }
        }
    }

    /**
     * Registers all classes implementing the `SpiderCoreComponent` interface.
     *
     * - Iterates through declared classes.
     * - Checks if each class implements `SpiderCoreComponent`.
     * - Stores the component name and version in the `$components` array.
     *
     * @return void
     */
    private static function registerComponents()
    {
        foreach (get_declared_classes() as $className) {
            if (
                interface_exists(SpiderCoreComponent::class) &&
                in_array(SpiderCoreComponent::class, class_implements($className), true)
            ) {
                // Store the component name and version.
                self::$components[$className] = $className::getVersion();
            }
        }
    }

    /**
     * Generates a hash representing the state of the core components.
     *
     * - Concatenates all component versions into a single string.
     * - Hashes the string using SHA-256.
     * - Returns a truncated hash.
     *
     * @param int $length Length of the returned hash. Default is 8.
     * @return string Hashed representation of the core components.
     */
    public static function getCoreHash($length = 8)
    {
        $versionString = implode('', self::$components); // Concatenate all component versions.
        return substr(hash('sha256', $versionString), 0, $length); // Generate and truncate the hash.
    }

    /**
     * Retrieves the list of registered components and their versions.
     *
     * @return array Associative array of component names and versions.
     */
    public static function getComponents()
    {
        return self::$components;
    }
}
