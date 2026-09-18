<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class provides shortcut functions to the Roundcube database access.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

defined("BOOL") || define("BOOL", "bool");
defined("INT") || define("INT", "int");

abstract class DatabaseGeneric
{
    const BOOL = "bool";
    const INT = "int";
    protected $prefix;
    protected $db;

    /**
     * Database constructor
     */
    public function __construct()
    {
        $rcmail = xrc();
        $this->db = $rcmail->get_dbh();
        $this->prefix = $rcmail->config->get("db_prefix");
    }

    /**
     * Returns the db provider.
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->db->db_provider;
    }

    /**
     * Quotes the column name that is the same as a keyword. This is different in different db types, the standard
     * is a double quote (used in postgres & sqlite) but for example mysql uses backticks.
     *
     * @param string $string
     * @return string
     */
    public function col(string $string): string
    {
        return $this->db->quote_identifier($string);
    }

    /**
     * Convert bool or int values into actual bool or int values. (PDO returns int and bool as strings, which later
     * causes problems when the values are sent to javascript.)
     *
     * @param array $data
     * @param $type
     * @param array $names
     */
    public function fix(array &$data, $type, array $names): void
    {
        foreach ($names as $name) {
            if ($type == BOOL) {
                $data[$name] = (bool)$data[$name];
            } else if ($type == INT) {
                $data[$name] = (int)$data[$name];
            }
        }
    }

    /**
     * Returns the last insert id.
     *
     * @param string $table
     * @return mixed
     */
    public function lastInsertId(string $table): mixed
    {
        return $this->db->insert_id($table);
    }

    /**
     * Returns the last error message.
     *
     * @return string|null
     */
    public function lastError(): ?string
    {
        return $this->db->is_error();
    }

    /**
     * Begins a transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->startTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->endTransaction();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->db->rollbackTransaction();
    }

    /**
     * Fetches the query result.
     *
     * @param string $query
     * @param string|array $parameters
     * @return array|bool
     */
    public function fetch(string $query, string|array $parameters = []): bool|array
    {
        if (!($statement = $this->query($query, $parameters))) {
            return false;
        }

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve a single row from the database.
     *
     * @param string $table
     * @param array $whereParams
     * @return array|bool
     */
    public function row(string $table, array $whereParams): bool|array
    {
        $this->createWhereParams($whereParams, $where, $param);
        return $this->fetch("SELECT * FROM {" .$table . "} WHERE $where LIMIT 1", $param);
    }

    /**
     * Returns record count.
     *
     * @param string $table
     * @param array $whereParams
     * @return string|null
     */
    public function count(string $table, array $whereParams): ?string
    {
        return $this->value("COUNT(*)", $table, $whereParams);
    }

    /**
     * Retrieves a single value from the database.
     *
     * @param string $field
     * @param string $table
     * @param array $whereParams
     * @return string|null
     */
    public function value(string $field, string $table, array $whereParams): ?string
    {
        $this->createWhereParams($whereParams, $where, $param);
        if (!($row = $this->fetch("SELECT $field FROM {" .$table . "} WHERE $where LIMIT 1", $param))) {
            return null;
        }

        if ($field == "COUNT(*)" && !array_key_exists($field, $row) && array_key_exists("count", $row)) {
            // @codeCoverageIgnoreStart
            $field = "count";
            // @codeCoverageIgnoreEnd
        }

        return array_key_exists($field, $row) ? $row[$field] : null;
    }

    /**
     * Retrieve multiple rows from the database as associate array.
     *
     * @param string $query
     * @param string|array|null $parameters
     * @param string $resultKeyField
     * @return array|boolean
     */
    public function all(string $query, string|array|null $parameters = [], string $resultKeyField = ""): bool|array
    {
        if (!($statement = $this->query($query, $parameters))) {
            return false;
        }

        $array = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // if $resultKeyField specified, place the requested field as the resulting array key
        if (!empty($array) && $resultKeyField) {
            $result = [];
            foreach ($array as $item) {
                $result[$item[$resultKeyField]] = $item;
            }
            return $result;
        }

        return $array;
    }

    /**
     * Inserts a record into the database.
     *
     * @param string $table
     * @param array $data
     * @param bool $logErrors
     * @return bool
     */
    public function insert(string $table, array $data, bool $logErrors = true): bool
    {
        $data = $this->fixWriteData($data);
        $fields = [];
        $markers = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $this->col($field);
            $markers[] = "?";
            $values[] = $value;
        }

        $fields = implode(",", $fields);
        $markers = implode(",", $markers);

        if (!$logErrors) {
            $this->setOption('ignore_errors', true);
            $this->setOption('ignore_key_errors', true);
        }

        try {
            if (!$this->query("INSERT INTO {" . $table . "} ($fields) VALUES ($markers)", $values)) {
                return false;
            }
        } finally {
            if (!$logErrors) {
                $this->setOption('ignore_errors', false);
                $this->setOption('ignore_key_errors', false);
            }
        }

        return true;
    }

    /**
     * Logs last query error to the Roundcube error log.
     *
     * @codeCoverageIgnore
     */
    public function logLastError(): void
    {
        if (class_exists("\\rcube")) {
            \rcube::write_log('errors', $this->lastError());
        }
    }

    /**
     * Updates records in a table.
     *
     * @param string $table
     * @param array $data
     * @param array $whereParams
     * @return bool
     */
    public function update(string $table, array $data, array $whereParams): bool
    {
        $data = $this->fixWriteData($data);
        $fields = [];
        $param = [];
        $where = [];

        foreach ($data as $key => $val) {
            $fields[] = $this->col($key) . "=?";
            $param[] = $val;
        }

        $this->createWhereParams($whereParams, $where, $param);
        $fields = implode(",", $fields);

        if (!$this->query("UPDATE {" . $table . "} SET $fields WHERE $where", $param)) {
            $this->logLastError();
            return false;
        }

        return true;
    }

    /**
     * Removes records from a table.
     *
     * @param string $table
     * @param array $whereParams
     * @param bool $addPrefix
     * @return bool
     */
    public function remove(string $table, array $whereParams, bool $addPrefix = true): bool
    {
        if ($addPrefix) {
            $table = "{" . $table . "}";
        }

        $this->createWhereParams($whereParams, $where, $param);

        if (!$this->query("DELETE FROM $table WHERE $where", $param)) {
            $this->logLastError();
            return false;
        }

        return true;
    }

    /**
     * Truncates a table.
     *
     * @param string $table
     * @param bool $addPrefix
     * @return bool
     */
    public function truncate(string $table, bool $addPrefix = true): bool
    {
        if ($addPrefix) {
            $table = "{" . $table . "}";
        }

        // we don't use truncate because of foreign key problems
        if (!$this->query("DELETE FROM $table")) {
            $this->logLastError();
            return false;
        }

        return true;
    }

    /**
     * Run a database query. Returns PDO statement.
     *
     * @param string $query
     * @param string|array|null $parameters
     * @return \PDOStatement|bool
     */
    public function query(string $query, string|array|null $parameters = []): bool|\PDOStatement
    {
        return $this->db->query(
            $this->prepareQuery($query),
            is_array($parameters) ? $parameters : [$parameters]
        );
    }

    /**
     * Returns the table name prefixed with the db_prefix config setting.
     *
     * @param string $table
     * @param bool $quote
     * @return string
     */
    public function getTableName(string $table, bool $quote = true): string
    {
        $table = $this->prefix . $table;
        return $quote ? $this->db->quote_identifier($table) : $table;
    }

    /**
     * Replaces table names in queries enclosed in { } prefixing them with the db_prefix config setting.
     *
     * @param string $query
     * @return string
     */
    public function prepareQuery(string $query): string
    {
        return preg_replace_callback("/{([^}]+)}/", [$this, "pregQueryReplace"], $query);
    }

    /**
     * Executes a query or a collection of queries. Executing a collection of queries using query() won't work in
     * sqlite, only the first query will execute. Use this function instead.
     *
     * @param string $script
     * @return bool
     */
    public function script(string $script): bool
    {
        return $this->db->exec_script($script);
    }

    /**
     * Sets a database option.
     *
     * @param string $name
     * @param $value
     * @return void
     */
    public function setOption(string $name, $value): void
    {
        $this->db->set_option($name, $value);
    }

    /**
     * Terminate database connection.
     */
    public function closeConnection(): void
    {
        $this->db->closeConnection();
    }

    /**
     * @param string $column
     * @param string $table
     * @param bool $addPrefix
     * @return bool
     */
    public function hasColumn(string $column, string $table, bool $addPrefix = false): bool
    {
        return in_array($column, $this->getColumns($table, $addPrefix));
    }

    /**
     * Returns the number of rows affected by the last query (or the given result).
     *
     * @param mixed|null $result Result handle from query(); if null, uses the last result.
     * @return int
     */
    public function affectedRows(mixed $result = null): int
    {
        return $this->db->affected_rows($result);
    }

    /**
     * Fixes the data that is about to be written to database, for example, RC will try to write bool false as an
     * empty string, which might cause problems with some databases.
     *
     * @param array $data
     * @return array
     */
    private function fixWriteData(array $data): array
    {
        foreach ($data as $key => $val) {
            if (is_bool($val)) {
                $data[$key] = (int)$val;
            }
        }

        return $data;
    }

    /**
     * @param array $matches
     * @return string
     */
    protected function pregQueryReplace(array $matches): string
    {
        return " " . $this->getTableName($matches[1]) . " ";
    }

    /**
     * @param array $whereParameters
     * @param string|array $where
     * @param string|array $param
     */
    protected function createWhereParams(array $whereParameters, &$where, &$param): void
    {
        is_array($where) || ($where = []);
        is_array($param) || ($param = []);

        foreach ($whereParameters as $key => $val) {
            // if key is not specified (is numeric), just add the $value to WHERE as is, this enables us to use
            // syntax like: IS NULL, IS NOT NULL, note that IS NULL can also be accomplished by setting value to null
            if (is_numeric($key)) {
                $where[] = $val;
            } else if ($val === null) {
                $where[] = $this->col($key) . ' IS NULL';
            } else {
                $where[] = $this->col($key) . '=?';
                $param[] = $val;
            }
        }

        $where = implode(' AND ', $where);
    }
}