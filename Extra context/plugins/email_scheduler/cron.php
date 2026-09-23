<?php

/**
 * Standalone CLI / Cron Worker for Email Scheduler Plugin
 *
 * Usage:
 *   * * * * * php /path/to/roundcube/plugins/email_scheduler/cron.php
 * Or via web:
 *   * * * * * wget -q -O - https://webmail.example.com/?_action=plugin.email_scheduler-cron
 *
 * @license MIT
 */

declare(strict_types=1);

// Locate Roundcube bootstrap
$rcDir = dirname(__DIR__, 2);
if (file_exists($rcDir . '/program/include/iniset.php')) {
    require_once $rcDir . '/program/include/iniset.php';
} elseif (file_exists($rcDir . '/include/iniset.php')) {
    require_once $rcDir . '/include/iniset.php';
}

$rcmail = rcmail::get_instance();
$plugin = new email_scheduler($rcmail->plugins);
$plugin->init();
$processed = $plugin->processDueMessages();

if (php_sapi_name() === 'cli') {
    echo "[" . date('Y-m-d H:i:s') . "] Email Scheduler Worker: Processed {$processed} due message(s).\n";
}
