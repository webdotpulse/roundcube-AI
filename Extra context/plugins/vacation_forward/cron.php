<?php

/**
 * Autonomous Standalone CLI / Cron Worker for Vacation & Forwarding Plugin
 *
 * Usage:
 *   * * * * * php /path/to/roundcube/plugins/vacation_forward/cron.php
 * Or via web:
 *   * * * * * wget -q -O - https://webmail.example.com/?_action=plugin.vacation_forward-cron
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

// Locate Roundcube bootstrap
$rcDir = dirname(__DIR__, 2);
if (file_exists($rcDir . '/program/include/iniset.php')) {
    require_once $rcDir . '/program/include/iniset.php';
} elseif (file_exists($rcDir . '/include/iniset.php')) {
    require_once $rcDir . '/include/iniset.php';
}

require_once __DIR__ . '/vacation_forward.php';

$rcmail = rcmail::get_instance();
$plugin = new vacation_forward($rcmail->plugins);
$processed = $plugin->runBatchCron();

if (php_sapi_name() === 'cli') {
    echo "[" . date('Y-m-d H:i:s') . "] Vacation & Forwarding Worker: Processed {$processed} mailbox schedule(s).\n";
}
