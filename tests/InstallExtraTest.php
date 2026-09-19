<?php

/**
 * Automated test suite for Roundcube Extra Content Installer (bin/install-extra.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bin/install-extra.php';

function assert_true(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

function remove_dir_recursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $files = scandir($dir) ?: [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            remove_dir_recursive($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

echo "=== Running Roundcube Extra Content Installer Test Suite ===\n\n";

// Setup temporary mock Roundcube directory
$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rc_test_' . uniqid();
@mkdir($tempDir, 0777, true);
@mkdir($tempDir . '/program/include', 0777, true);
@mkdir($tempDir . '/plugins', 0777, true);
@mkdir($tempDir . '/skins', 0777, true);
@mkdir($tempDir . '/config', 0777, true);

// Create required Roundcube marker files
file_put_contents($tempDir . '/index.php', "<?php // Roundcube mock index\n");
file_put_contents($tempDir . '/program/include/iniset.php', "<?php // Roundcube mock iniset\n");

// Create initial config.inc.php
$initialConfig = <<<PHP
<?php
\$config = [];
\$config['db_dsnw'] = 'sqlite:///:memory:';
\$config['skin'] = 'elastic';
\$config['plugins'] = [
    'archive',
    'zipdownload',
];
PHP;
file_put_contents($tempDir . '/config/config.inc.php', $initialConfig);

// Test 1: Dry-run execution
echo "--- Test 1: Dry-Run Execution ---\n";
$installer = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installer->parseCliArgs(['--dry-run', '--roundcube-path=' . $tempDir]);
$ret = $installer->execute();
assert_true($ret === 0, "Installer returns exit code 0 on dry run");
assert_true(!is_dir($tempDir . '/plugins/xcalendar'), "Dry-run does NOT create xcalendar in plugins");
assert_true(!is_dir($tempDir . '/skins/gmail_plus'), "Dry-run does NOT create gmail_plus in skins");

// Test 2: Full Installation with --activate
echo "\n--- Test 2: Full Installation with --activate ---\n";
$installerFull = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installerFull->parseCliArgs(['--activate', '--roundcube-path=' . $tempDir]);
$retFull = $installerFull->execute();
assert_true($retFull === 0, "Installer returns exit code 0 on full install");

// Verify skin installed
assert_true(is_dir($tempDir . '/skins/gmail_plus'), "Skin 'gmail_plus' installed in skins/");

// Verify companion plugins installed
assert_true(is_dir($tempDir . '/plugins/xskin'), "Plugin 'xskin' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/xframework'), "Plugin 'xframework' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/customizr'), "Plugin 'customizr' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/thread_drafts'), "Plugin 'thread_drafts' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/thunderbird_labels'), "Plugin 'thunderbird_labels' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/xcalendar'), "Plugin 'xcalendar' installed in plugins/");

// Test 3: xcalendar Specific Post-Install Verifications
echo "\n--- Test 3: xcalendar Specific Post-Install Verifications ---\n";
assert_true(file_exists($tempDir . '/plugins/xcalendar/config.inc.php'), "xcalendar config.inc.php initialized from dist");
assert_true(is_dir($tempDir . '/plugins/xcalendar/attachments'), "xcalendar attachments directory created");
assert_true(file_exists($tempDir . '/plugins/xcalendar/attachments/.htaccess'), "xcalendar attachments .htaccess created");

$htaccessContent = file_get_contents($tempDir . '/plugins/xcalendar/attachments/.htaccess');
assert_true(strpos($htaccessContent, 'Deny from all') !== false, "Attachments .htaccess prevents direct web access");

// Test 4: Configuration Activation Verification
echo "\n--- Test 4: Configuration Activation Verification ---\n";
$updatedConfig = file_get_contents($tempDir . '/config/config.inc.php');
assert_true(strpos($updatedConfig, "'skin'] = 'gmail_plus'") !== false, "Skin updated to gmail_plus in config");
assert_true(strpos($updatedConfig, "'xcalendar'") !== false, "xcalendar added to plugins array in config");
assert_true(strpos($updatedConfig, "'xskin'") !== false, "xskin added to plugins array in config");
assert_true(strpos($updatedConfig, "'customizr'") !== false, "customizr added to plugins array in config");
assert_true(strpos($updatedConfig, "'archive'") !== false, "Existing 'archive' plugin preserved in config");
assert_true(strpos($updatedConfig, "'zipdownload'") !== false, "Existing 'zipdownload' plugin preserved in config");

// Test 5: Re-running installer preserves user customized config
echo "\n--- Test 5: Config Preservation on Update ---\n";
file_put_contents($tempDir . '/plugins/xcalendar/config.inc.php', "<?php // User modified xcalendar config\n\$config['custom'] = 123;\n");
$installerUpdate = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installerUpdate->parseCliArgs(['--roundcube-path=' . $tempDir]);
$installerUpdate->execute();
$preservedConfig = file_get_contents($tempDir . '/plugins/xcalendar/config.inc.php');
assert_true(strpos($preservedConfig, "\$config['custom'] = 123;") !== false, "Preserves modified user config in plugins/xcalendar/config.inc.php");

// Cleanup
remove_dir_recursive($tempDir);

echo "\n*** ALL INSTALLER TESTS PASSED SUCCESSFULLY ***\n";
