<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class provides functions that contain MySQL-specific queries.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/DatabaseGeneric.php';
require_once __DIR__ . '/DatabaseInterface.php';

class DatabaseMysql extends DatabaseGeneric implements DatabaseInterface
{
    /**
     * Returns the columns in the database table.
     * We're not using $this->rcmail->db->list_cols($table) because in some cases it doesn't return reliable results.
     *
     * @param string $table
     * @param bool $addPrefix
     * @return array
     */
    public function getColumns(string $table, bool $addPrefix = true): array
    {
        if ($addPrefix) {
            $table = "{" . $table . "}";
        }

        $all = $this->all("SHOW COLUMNS FROM $table");
        $columns = [];

        if (is_array($all)) {
            foreach ($all as $item) {
                $columns[] = $item["Field"];
            }
        }

        return $columns;
    }

    /**
     * Returns the names of all the tables in the database.
     *
     * @return array
     */
    public function getTables(): array
    {
        $result = [];

        $tables = $this->all("SHOW TABLES");
        if (is_array($tables)) {
            foreach ($tables as $table) {
                if ($values = array_values($table)) {
                    $result[] = $values[0];
                }
            }
        }

        return $result;
    }

    /**
     * Checks if a table exists in the database.
     *
     * @param string $table
     * @return bool
     */
    public function hasTable(string $table): bool
    {
        $result = $this->all("SHOW TABLES LIKE '" . $this->getTableName($table, false) . "'");
        return !empty($result);
    }

    /**
     * Removes records that are older than the specified amount of seconds.
     *
     * @param string $table
     * @param string $dateField
     * @param int $seconds
     * @param bool $addPrefix
     * @return bool
     */
    public function removeOld(string $table, string $dateField = "created_at", int $seconds = 3600,
                              bool $addPrefix = true): bool
    {
        if ($addPrefix) {
            $table = "{" . $table . "}";
        }

        if (!$this->query("DELETE FROM $table WHERE $dateField < DATE_SUB(NOW(), INTERVAL $seconds SECOND)")) {
            $this->logLastError();
            return false;
        }

        return true;
    }
}