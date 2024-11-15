<?php

/**
 * Vocabulary class
 *
 * This class handles language management, including loading translations
 * from files, determining the current language, and providing access
 * to translated strings.
 *
 * Implements the SpiderCoreComponent interface.
 */
class Vocabulary implements SpiderCoreComponent
{
    /**
     * Version of the `Vocabulary` component.
     */
    const VERSION = "1.0.0";

    /**
     * @var array Holds the loaded translation data.
     */
    private static $data = [];

    /**
     * @var string|null The default language for the application.
     */
    private static $defaultLang;

    /**
     * @var array List of available languages (detected from files).
     */
    private static $availableLangs = [];

    /**
     * @var string|null Directory where language files are stored.
     */
    private static $langDirectory;

    /**
     * Returns the version of the `Vocabulary` component.
     *
     * @return string Version string.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Sets the default language and initializes the language system.
     *
     * - Creates the language directory if it doesn't exist.
     * - Loads the list of available languages.
     * - Determines the current language based on query parameters or cookies.
     * - Loads translations for the determined language.
     *
     * @param string $lang Default language code (e.g., 'en', 'fr').
     * @return void
     */
    public static function setDefaultLanguage($lang)
    {
        self::$defaultLang = $lang;
        self::$langDirectory = Response::getPrivatePath() . '/vocabulary';

        // Ensure the language directory exists
        if (!file_exists(self::$langDirectory)) {
            mkdir(self::$langDirectory, 0777, true);
        }

        // Initialize the list of available languages
        self::getAvailableLanguages();

        // Determine the current language and load its translations
        $language = self::determineLanguage();
        self::loadTranslations($language);
    }

    /**
     * Retrieves a list of available languages by scanning the language directory.
     *
     * - Looks for files with `.json` or `.lang` extensions.
     *
     * @return array List of available language codes.
     */
    private static function getAvailableLanguages()
    {
        $files = glob(self::$langDirectory . '/*.{json,lang}', GLOB_BRACE);
        self::$availableLangs = [];

        foreach ($files as $file) {
            $langCode = pathinfo($file, PATHINFO_FILENAME);
            self::$availableLangs[] = $langCode;
        }

        return self::$availableLangs;
    }

    /**
     * Loads translations for the specified language.
     *
     * - Checks for `.json` or `.lang` files matching the language code.
     * - Parses the file and populates the translation data.
     *
     * @param string $lang Language code.
     * @return void
     */
    private static function loadTranslations($lang)
    {
        $filePath = self::$langDirectory . '/' . $lang;

        // Check for .json or .lang file formats
        if (file_exists($filePath . '.json') || file_exists($filePath . '.lang')) {
            $content = file_get_contents(file_exists($filePath . '.json') ? $filePath . '.json' : $filePath . '.lang');
            self::$data = json_decode($content, true) ?: self::parseLangFile($content);
        } elseif (file_exists($filePath . '.spl')) {
            // Placeholder for processing .spl files
            self::$data = self::parseSplFile(file_get_contents($filePath . '.spl'));
        } else {
            // Clear data if no file is found
            self::$data = [];
        }
    }

    /**
     * Parses a `.lang` file format (key=value pairs).
     *
     * @param string $content Content of the `.lang` file.
     * @return array Parsed translations as an associative array.
     */
    private static function parseLangFile($content)
    {
        $translations = [];
        $lines = explode(PHP_EOL, $content);

        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $translations[trim($key)] = trim($value);
            }
        }

        return $translations;
    }

    /**
     * Placeholder for parsing `.spl` files.
     *
     * - This method can be customized to process `.spl` file formats.
     *
     * @param string $content Content of the `.spl` file.
     * @return array Parsed translations.
     */
    private static function parseSplFile($content)
    {
        return [];
    }

    /**
     * Determines the current language based on query parameters or cookies.
     *
     * - Checks for `lang` in the query string.
     * - Falls back to `lang` stored in cookies.
     * - Defaults to the application's default language.
     *
     * @return string The determined language code.
     */
    private static function determineLanguage()
    {
        $selectedLang = self::$defaultLang;

        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$availableLangs)) {
            // Set language from query string and update the cookie
            $selectedLang = $_GET['lang'];
            setcookie('lang', $selectedLang, time() + 10 * 365 * 24 * 60 * 60, '/');
        } elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], self::$availableLangs)) {
            // Use language from cookies
            $selectedLang = $_COOKIE['lang'];
        } else {
            // Default language if nothing is found
            setcookie('lang', self::$defaultLang, time() + 10 * 365 * 24 * 60 * 60, '/');
        }

        return $selectedLang;
    }

    /**
     * Sets a translation key-value pair.
     *
     * @param string $key The key for the translation.
     * @param string $value The translated value.
     * @return void
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * Retrieves a translated value by key.
     *
     * @param string $key The translation key.
     * @return string|null The translated value, or null if the key is not found.
     */
    public static function get($key)
    {
        return self::$data[$key] ?? null;
    }

    /**
     * Forcefully loads a specific language file.
     *
     * - Clears the current translations and loads the specified language.
     *
     * @param string $lang Language code to load.
     * @return void
     */
    public static function loadLanguage($lang)
    {
        self::loadTranslations($lang);
    }
}
