<?php

// Register an autoloader for plugins.
spl_autoload_register(function ($plugin) {
    /**
     * Construct the file path based on the plugin name.
     *
     * - Replaces namespace separators (`\`) with the system directory separator.
     * - Appends `.php` to create the expected file name.
     */
    $pluginPath = str_replace('\\', DIRECTORY_SEPARATOR, $plugin) . '.php';
    $file = Response::getPluginsPath() . DIRECTORY_SEPARATOR . $pluginPath;

    /**
     * Security checks to prevent directory traversal or invalid plugin names.
     *
     * - Rejects plugin names containing `..` (parent directory).
     * - Rejects plugin names containing `/` (for UNIX systems).
     * - Rejects plugin names starting with a backslash (`\`), indicating root traversal.
     */
    if (strpos($plugin, '..') !== false || strpos($plugin, '/') !== false || strpos($plugin, '\\') === 0) {
        trigger_error("Invalid plugin name: $plugin", E_USER_WARNING);
        return;
    }

    /**
     * Check if the plugin file exists before attempting to include it.
     *
     * - If the file exists, require it to load the plugin.
     * - If the file is missing, log an error to help with debugging.
     */
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Plugin file not found: $file");
    }
});
