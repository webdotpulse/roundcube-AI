<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This class provides functions that contain Postgres-specific queries.
 *
 * Copyright 2018, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/DatabaseGeneric.php';
require_once __DIR__ . '/DatabaseInterface.php';

class DatabasePostgres extends DatabaseGeneric implements DatabaseInterface
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
        $all = $this->all(
            "SELECT column_name FROM information_schema.columns WHERE table_name = ?",
            [$addPrefix ? $this->prefix . $table : $table]
        );
        $columns = [];

        if (is_array($all)) {
            foreach ($all as $item) {
                $columns[] = $item["column_name"];
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

        $tables = $this->all(
            "SELECT tablename FROM pg_catalog.pg_tables ".
            "WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'"
        );

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
        $result = $this->all(
            "SELECT tablename FROM pg_catalog.pg_tables ".
            "WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' ".
            "AND tablename = '" . $this->getTableName($table, false) . "'");
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

        if (!$this->query("DELETE FROM $table WHERE $dateField < NOW() - INTERVAL '$seconds seconds'")) {
            $this->logLastError();
            return false;
        }

        return true;
    }

}