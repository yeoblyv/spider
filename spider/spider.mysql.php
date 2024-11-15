<?php

/**
 * MySQL class
 *
 * This class handles the connection to a MySQL database and provides
 * utility methods for executing queries, managing data, and interacting
 * with the database using PDO. 
 * CRUD operations: Support for INSERT, UPDATE, DELETE, and SELECT operations.
 *
 * Implements the SpiderCoreComponent interface.
 */
class MySQL implements SpiderCoreComponent
{
    /**
     * Version of the `MySQL` component.
     */
    const VERSION = "1.0.0";

    /**
     * @var \PDO|null The current PDO connection instance.
     */
    private static $pdo = null;

    /**
     * @var string|null The name of the currently selected database.
     */
    private static $currentDb;

    /**
     * Returns the version of the `MySQL` component.
     *
     * @return string Version string.
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
        // Prevents external instantiation
    }

    /**
     * Establishes a connection to the MySQL database.
     *
     * - Uses a DSN string to connect.
     * - Sets the PDO error mode to exceptions.
     * - Caches the connection for future use.
     *
     * @param string $host The hostname of the database server.
     * @param string $dbname The name of the database.
     * @param string $username The database username.
     * @param string $password The database password.
     * @param string $charset The character set to use. Default is 'utf8mb4'.
     * @return \PDO The PDO instance.
     * @throws Exception If the connection fails.
     */
    public static function connect($host, $dbname, $username, $password, $charset = 'utf8mb4')
    {
        if (self::$pdo === null || self::$currentDb !== $dbname) {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            try {
                self::$pdo = new \PDO($dsn, $username, $password);
                self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$currentDb = $dbname; // Store the current database name
            } catch (\PDOException $e) {
                error_log("Could not connect to the database: " . $e->getMessage());
                throw new Exception("Database connection failed.");
            }
        }
        return self::$pdo;
    }

    /**
     * Returns the current PDO instance.
     *
     * @return \PDO The current PDO instance.
     * @throws Exception If no connection has been established.
     */
    public static function getInstance()
    {
        if (self::$pdo === null) {
            throw new Exception("Database not connected. Please call connect first.");
        }
        return self::$pdo;
    }

    /**
     * Switches to a different database.
     *
     * @param string $dbname The name of the database to switch to.
     * @return void
     */
    public static function setDatabase($dbname)
    {
        if (self::$pdo !== null && self::$currentDb !== $dbname) {
            self::$pdo->exec("USE `" . self::escapeIdentifier($dbname) . "`");
            self::$currentDb = $dbname;
        }
    }

    /**
     * Escapes a value for safe use in a query.
     *
     * @param mixed $value The value to escape.
     * @return string Escaped value, or "NULL" if the value is null.
     * @throws Exception If no connection has been established.
     */
    public static function escape($value)
    {
        if (self::$pdo === null) {
            throw new Exception("Database not connected. Please call connect first.");
        }

        if ($value === null) {
            return "NULL";
        }

        return self::$pdo->quote($value);
    }

    /**
     * Executes a SQL query and returns the result set.
     *
     * @param string $sql The SQL query to execute.
     * @return array The result set as an associative array.
     */
    public static function query($sql)
    {
        self::checkConnection();
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error executing query: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Inserts a record into a table.
     *
     * @param string $table The table name.
     * @param array $data An associative array of column-value pairs.
     * @return bool True on success, false on failure.
     */
    public static function insert($table, $data)
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Data for insert cannot be empty.");
        }

        self::checkConnection();
        $keys = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';

        $sql = "INSERT INTO `" . self::escapeIdentifier($table) . "` (" . implode(', ', array_map([self::class, 'escapeIdentifier'], $keys)) . ") VALUES ($placeholders)";
        try {
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns the ID of the last inserted row.
     *
     * @return string The ID of the last inserted row.
     */
    public static function getLastInsertId()
    {
        self::checkConnection();
        return self::$pdo->lastInsertId();
    }

    /**
     * Updates records in a table.
     *
     * @param string $table The table name.
     * @param array $data An associative array of column-value pairs.
     * @param string $condition The WHERE clause for the update.
     * @return bool True on success, false on failure.
     */
    public static function update($table, $data, $condition)
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Data for update cannot be empty.");
        }

        self::checkConnection();
        $set = [];
        $values = [];
        foreach ($data as $key => $value) {
            $set[] = "`" . self::escapeIdentifier($key) . "` = ?";
            $values[] = $value;
        }

        $sql = "UPDATE `" . self::escapeIdentifier($table) . "` SET " . implode(', ', $set) . " WHERE $condition";
        try {
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes records from a table.
     *
     * @param string $table The table name.
     * @param string $condition The WHERE clause for the delete.
     * @param array $params Optional parameters for the WHERE clause.
     * @return bool True on success, false on failure.
     */
    public static function delete($table, $condition, $params = [])
    {
        self::checkConnection();
        $sql = "DELETE FROM `" . self::escapeIdentifier($table) . "` WHERE $condition";
        try {
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Escapes table or column identifiers to prevent SQL injection.
     *
     * @param string $identifier The identifier to escape.
     * @return string Escaped identifier.
     */
    private static function escapeIdentifier($identifier)
    {
        return str_replace('`', '``', $identifier);
    }

    /**
     * Checks if the database connection is established.
     *
     * @return void
     * @throws Exception If no connection has been established.
     */
    private static function checkConnection()
    {
        if (self::$pdo === null) {
            throw new Exception("Database not connected. Please call connect first.");
        }
    }
}
