<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/Singleton.php';
require_once __DIR__ . '/DatabaseMysql.php';
require_once __DIR__ . '/DatabaseSqlite.php';
require_once __DIR__ . '/DatabasePostgres.php';

class Database
{
    use Singleton;
    static private ?string $provider;

    /**
     * @throws \Exception
     */
    public static function instance(?string $provider = null): DatabaseGeneric
    {
        if (static::$instance && (!$provider || $provider == static::$provider)) {
            return static::$instance;
        }

        static::$provider = $provider ?? xrc()->get_dbh()->db_provider;

        return match (static::$provider) {
            "mysql" => static::$instance = new DatabaseMysql(),
            "sqlite" => static::$instance = new DatabaseSqlite(),
            "postgres" => static::$instance = new DatabasePostgres(),
            default => throw new \Exception("Error: This plugin does not support " . static::$provider . "."),
        };
    }
}