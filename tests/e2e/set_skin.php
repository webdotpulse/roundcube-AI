<?php
declare(strict_types=1);

$skin = $argv[1] ?? 'gmail_plus';
$dbPath = __DIR__ . '/../../deployment/roundcube.db';
$pdo = new PDO('sqlite:' . $dbPath);
$stmt = $pdo->query('SELECT preferences FROM users WHERE user_id = 1');
$raw = $stmt->fetchColumn();
$prefs = is_string($raw) ? @unserialize($raw) : [];
if (!is_array($prefs)) {
    $prefs = [];
}
$prefs['skin'] = $skin;
$upd = $pdo->prepare('UPDATE users SET preferences = ? WHERE user_id = 1');
$upd->execute([serialize($prefs)]);
$pdo->exec('DELETE FROM session');
echo "User skin successfully updated to: {$skin}\n";
