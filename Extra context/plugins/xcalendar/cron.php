<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2018, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 *
 * DEPRECATION WARNING: Using this file to execute cron jobs is not supported on Roundcube 1.7.
 */

const INSTALL_PATH = __DIR__ . "/../../";
chdir(INSTALL_PATH);
$_SERVER['REMOTE_ADDR'] = "127.0.0.1";
$_GET['xcalendar-cron'] = "1";
require("index.php");