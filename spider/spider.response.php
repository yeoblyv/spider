<?php

/**
 * Response class for handling HTTP responses, routing, and file processing.
 * 
 * Implements the `SpiderCoreComponent` interface and provides essential
 * methods for managing content types, routing requests, and determining file accessibility.
 */
class Response implements SpiderCoreComponent
{
    /**
     * Version of the `Response` component.
     */
    const VERSION = '1.0.0';

    /**
     * Supported content format constants.
     */
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';

    /**
     * @var array|null List of content types loaded from an external file.
     */
    private static $contentTypes;

    /**
     * @var string|null The root directory path.
     */
    private static $rootPath;

    /**
     * @var int|null HTTP status code.
     */
    private static $statusCode;

    /**
     * @var bool Indicates whether the currently processed file is an index file.
     */
    private static $isIndex = false;

    /**
     * Returns the version of the `Response` component.
     *
     * @return string Version string.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Loads and returns the list of supported content types.
     * 
     * If the content type file is missing, it initializes an empty list and logs a warning.
     *
     * @return array List of content types.
     */
    public static function getContentTypes()
    {
        if (self::$contentTypes === null) {
            $filePath = __DIR__ . '/spider.mimetypes.php';
            if (file_exists($filePath)) {
                self::$contentTypes = include $filePath;
            } else {
                trigger_error("Mime types file not found: $filePath", E_USER_WARNING);
                self::$contentTypes = [];
            }
        }
        return self::$contentTypes;
    }

    /**
     * Redirects to the specified path.
     *
     * @param string $path The target path for redirection.
     * @return void
     */
    public static function route($path)
    {
        if (!headers_sent()) {
            header('Location: ' . self::getRootLink() . $path);
            exit;
        } else {
            trigger_error("Headers already sent. Cannot redirect to $path.", E_USER_WARNING);
        }
    }

    /**
     * Sets the root path for the application.
     *
     * @param string $subDirectory Optional subdirectory path.
     * @return void
     */
    public static function setRootPath($subDirectory = "")
    {
        self::$rootPath = $_SERVER["DOCUMENT_ROOT"] . $subDirectory;
    }

    /**
     * Sets the response page format and clears the output buffer if required.
     *
     * @param string $extension File extension for the response format.
     * @param bool $wipe_buffer Whether to clear the output buffer.
     * @return void
     */
    public static function setPageFormat($extension = self::FORMAT_HTML, $wipe_buffer = true)
    {
        if ($wipe_buffer && ob_get_level()) {
            ob_end_clean();
        }

        $contentTypes = self::getContentTypes();

        if (isset($contentTypes[$extension])) {
            if (!headers_sent()) {
                header("Content-Type: " . $contentTypes[$extension]);
            }
        } else {
            trigger_error("Content type for extension '$extension' not found.", E_USER_WARNING);
        }
    }

    /**
     * Checks whether a file is accessible.
     *
     * @param string $path The file path to check.
     * @return bool True if the file exists and is readable.
     */
    public static function isFileAccessible($path)
    {
        return file_exists($path) && is_file($path) && is_readable($path);
    }

    /**
     * Returns the root path of the application.
     *
     * @return string|null Root path.
     */
    public static function getRootPath()
    {
        return self::$rootPath;
    }

    /**
     * Returns the public directory path.
     *
     * @return string Public path.
     */
    public static function getPublicPath()
    {
        return self::$rootPath . "/public";
    }

    /**
     * Returns the private directory path.
     *
     * @return string Private path.
     */
    public static function getPrivatePath()
    {
        return self::$rootPath . "/private";
    }

    /**
     * Returns the plugins directory path.
     *
     * @return string Plugins path.
     */
    public static function getPluginsPath()
    {
        return self::$rootPath . "/private/plugins";
    }

    /**
     * Retrieves the current domain name.
     *
     * @return string Domain name.
     */
    public static function getDomain()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $domain = parse_url($protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST);
        return $domain;
    }

    /**
     * Retrieves the root link of the application.
     *
     * @return string Root URL.
     */
    public static function getRootLink()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $currentUrl = $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $urlParts = parse_url($currentUrl);
        return $protocol . '://' . ($urlParts['host'] ?? 'localhost');
    }

    /**
     * Processes an HTTP request and displays the requested file if accessible.
     *
     * @param string $filePath Path to the requested file.
     * @return void
     */
    public static function processRequest($filePath)
    {
        $fullPath = self::getFullPath($filePath);

        if (self::isFileAccessible($fullPath)) {
            self::displayFileContent($fullPath);
            if (class_exists('Request')) {
                Request::setStatusCode(Request::HTTP_CODE_OK);
            }
        } else {
            if (class_exists('Request')) {
                Request::setStatusCode(Request::HTTP_CODE_NOT_FOUND);
            }
        }
    }

    /**
     * Resolves the full path to a requested file, including default index files if applicable.
     *
     * @param string $filePath The requested file path.
     * @return string Full resolved file path.
     */
    private static function getFullPath($filePath)
    {
        $parsedPath = parse_url($filePath, PHP_URL_PATH);
        $fullPath = self::getPublicPath() . $parsedPath;
        $fileExtension = pathinfo($fullPath, PATHINFO_EXTENSION);

        if (is_dir($fullPath) || !$fileExtension) {
            $fullPath = rtrim($fullPath, '/') . '/index.php';
        }
        return $fullPath;
    }

    /**
     * Displays the content of a file, setting appropriate headers.
     *
     * @param string $filePath Path to the file.
     * @return void
     */
    private static function displayFileContent($filePath)
    {
        if (!self::isFileAccessible($filePath)) {
            if (class_exists('Request')) {
                Request::setStatusCode(Request::HTTP_CODE_NOT_FOUND);
            }
            return;
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentTypes = self::getContentTypes();
        $contentTypes['php'] = 'text/html';

        if (isset($contentTypes[$fileExtension])) {
            if (!headers_sent()) {
                header('Content-Type: ' . $contentTypes[$fileExtension]);
            }
        }

        if ($fileExtension === 'php') {
            include $filePath;
            self::$isIndex = true;
        } else {
            readfile($filePath);
            self::$isIndex = false;
        }

        if (class_exists('Request')) {
            Request::setStatusCode(Request::HTTP_CODE_OK);
        }
    }

    /**
     * Checks if a directory contains an `index.php` file.
     *
     * @param string $path Directory path.
     * @return bool True if the directory contains `index.php`.
     */
    public static function isDirectoryWithIndex($path)
    {
        return is_dir($path) && file_exists($path . '/index.php');
    }

    /**
     * Retrieves the current operating system family.
     *
     * @return string OS family.
     */
    public static function getOS()
    {
        return PHP_OS_FAMILY;
    }

    /**
     * Checks if the specified file is an index file.
     *
     * @param string $filePath File path.
     * @return void
     */
    public static function checkIfIndex($filePath)
    {
        $fullPath = self::getFullPath($filePath);
        self::$isIndex = self::isFileAccessible($fullPath) && basename($fullPath) === 'index.php';
    }

    /**
     * Returns whether the last processed file is an index file.
     *
     * @return bool True if the last processed file is an index file.
     */
    public static function isIndex()
    {
        return self::$isIndex;
    }
}
