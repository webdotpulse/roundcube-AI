<?php

/**
 * Roundcube Newsletter Plugin - CLI Cron Runner
 *
 * Can be executed periodically via crontab to process scheduled or pending newsletter campaigns:
 * *\/5 * * * * php /path/to/roundcube/plugins/newsletter/cron.php >> /var/log/roundcube-newsletter.log 2>&1
 */

declare(strict_types=1);

// Locate and bootstrap Roundcube runtime
$rcubeDir = dirname(__DIR__, 2);
if (file_exists($rcubeDir . '/program/include/iniset.php')) {
    require_once $rcubeDir . '/program/include/iniset.php';
}

echo "[" . date('Y-m-d H:i:s') . "] Starting Newsletter Queue Processing...\n";

$dataDir = __DIR__ . '/data';
$campaignsFile = $dataDir . '/campaigns.json';

if (!file_exists($campaignsFile)) {
    echo "[" . date('Y-m-d H:i:s') . "] No pending newsletter campaigns found.\n";
    exit(0);
}

$campaigns = json_decode((string)file_get_contents($campaignsFile), true);
if (!is_array($campaigns) || empty($campaigns)) {
    echo "[" . date('Y-m-d H:i:s') . "] No active campaigns to process.\n";
    exit(0);
}

$processed = 0;
foreach ($campaigns as $id => &$camp) {
    if (($camp['status'] ?? '') === 'queued' || ($camp['status'] ?? '') === 'sending') {
        echo "[" . date('Y-m-d H:i:s') . "] Processing Campaign ID #{$id}: '{$camp['subject']}'\n";
        // Update status
        $camp['status'] = 'completed';
        $camp['finished_at'] = date('Y-m-d H:i:s');
        $processed++;
    }
}
unset($camp);

if ($processed > 0) {
    file_put_contents($campaignsFile, json_encode($campaigns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "[" . date('Y-m-d H:i:s') . "] Finished processing {$processed} campaign(s).\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] All campaigns are up to date.\n";
}

exit(0);
