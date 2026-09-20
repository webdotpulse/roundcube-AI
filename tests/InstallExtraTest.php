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
assert_true(!is_dir($tempDir . '/plugins/xmultibox'), "Dry-run does NOT create xmultibox in plugins");
assert_true(!is_dir($tempDir . '/plugins/xsignature'), "Dry-run does NOT create xsignature in plugins");
assert_true(!is_dir($tempDir . '/data/xsignature'), "Dry-run does NOT create data/xsignature directory");
assert_true(!is_dir($tempDir . '/skins/gmail_plus'), "Dry-run does NOT create gmail_plus in skins");

// Test 2: Full Installation with --activate
echo "\n--- Test 2: Full Installation with --activate ---\n";
$installerFull = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installerFull->parseCliArgs(['--activate', '--roundcube-path=' . $tempDir]);
$retFull = $installerFull->execute();
assert_true($retFull === 0, "Installer returns exit code 0 on full install");

// Verify skins installed
assert_true(is_dir($tempDir . '/skins/gmail_plus'), "Skin 'gmail_plus' installed in skins/");
assert_true(file_exists($tempDir . '/skins/gmail_plus/meta.json'), "gmail_plus skin meta.json exists");
assert_true(file_exists($tempDir . '/skins/gmail_plus/config.inc.php'), "gmail_plus config.inc.php initialized from sample");

// Verify companion plugins installed
assert_true(is_dir($tempDir . '/plugins/xskin'), "Plugin 'xskin' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/xframework'), "Plugin 'xframework' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/customizr'), "Plugin 'customizr' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/thread_drafts'), "Plugin 'thread_drafts' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/thunderbird_labels'), "Plugin 'thunderbird_labels' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/xcalendar'), "Plugin 'xcalendar' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/roundcube_loader'), "Plugin 'roundcube_loader' installed in plugins/");
assert_true(file_exists($tempDir . '/plugins/roundcube_loader/config.inc.php'), "roundcube_loader config.inc.php initialized from dist");
assert_true(is_dir($tempDir . '/plugins/xmultibox'), "Plugin 'xmultibox' installed in plugins/");
assert_true(is_dir($tempDir . '/plugins/xsignature'), "Plugin 'xsignature' installed in plugins/");
assert_true(file_exists($tempDir . '/plugins/xsignature/config.inc.php'), "xsignature config.inc.php initialized from dist");

// Test 3: xcalendar Specific Post-Install Verifications
echo "\n--- Test 3: xcalendar Specific Post-Install Verifications ---\n";
assert_true(file_exists($tempDir . '/plugins/xcalendar/config.inc.php'), "xcalendar config.inc.php initialized from dist");
assert_true(is_dir($tempDir . '/plugins/xcalendar/attachments'), "xcalendar attachments directory created");
assert_true(file_exists($tempDir . '/plugins/xcalendar/attachments/.htaccess'), "xcalendar attachments .htaccess created");

$htaccessContent = file_get_contents($tempDir . '/plugins/xcalendar/attachments/.htaccess');
assert_true(strpos($htaccessContent, 'Deny from all') !== false, "Attachments .htaccess prevents direct web access");

// Test 3b: xsignature Specific Post-Install Verifications
echo "\n--- Test 3b: xsignature Specific Post-Install Verifications ---\n";
assert_true(file_exists($tempDir . '/plugins/xsignature/config.inc.php'), "xsignature config.inc.php exists");
assert_true(is_dir($tempDir . '/plugins/xsignature/data'), "xsignature plugins/xsignature/data logo directory created");
assert_true(file_exists($tempDir . '/plugins/xsignature/data/.htaccess'), "xsignature plugins/xsignature/data .htaccess created");
assert_true(file_exists($tempDir . '/plugins/xsignature/data/.gitignore'), "xsignature plugins/xsignature/data .gitignore created");
assert_true(is_dir($tempDir . '/data/xsignature'), "xsignature legacy data/xsignature logo directory created");
$sigCfg = file_get_contents($tempDir . '/plugins/xsignature/config.inc.php');
assert_true(strpos($sigCfg, "plugins/xsignature/data") !== false, "xsignature config.inc.php configured with plugins/xsignature/data");

// Test 4: Configuration Activation Verification
echo "\n--- Test 4: Configuration Activation Verification ---\n";
$updatedConfig = file_get_contents($tempDir . '/config/config.inc.php');
assert_true(strpos($updatedConfig, "'skin'] = 'gmail_plus'") !== false, "Skin updated to gmail_plus in config");
assert_true(strpos($updatedConfig, "'xcalendar'") !== false, "xcalendar added to plugins array in config");
assert_true(strpos($updatedConfig, "'xskin'") !== false, "xskin added to plugins array in config");
assert_true(strpos($updatedConfig, "'customizr'") !== false, "customizr added to plugins array in config");
assert_true(strpos($updatedConfig, "'roundcube_loader'") !== false, "roundcube_loader added to plugins array in config");
assert_true(strpos($updatedConfig, "'xmultibox'") !== false, "xmultibox added to plugins array in config");
assert_true(strpos($updatedConfig, "'xsignature'") !== false, "xsignature added to plugins array in config");
assert_true(strpos($updatedConfig, "'archive'") !== false, "Existing 'archive' plugin preserved in config");
assert_true(strpos($updatedConfig, "'zipdownload'") !== false, "Existing 'zipdownload' plugin preserved in config");
assert_true(strpos($updatedConfig, "\$config['license_key'] = 'RCPLUSFREE20266u'") !== false, "license_key initialized with valid key in config");
assert_true(strpos($updatedConfig, "\$config['remove_vendor_branding'] = true") !== false, "remove_vendor_branding enabled in config");

// Verify 'xskin' is the FIRST element in $config['plugins']
preg_match('/\$config\[[\'"]plugins[\'"]\]\s*=\s*(?:array\s*\((.*?)\)|\[(.*?)\])\s*;/is', $updatedConfig, $pm);
$inner = ($pm[1] !== '') ? $pm[1] : ($pm[2] ?? '');
preg_match_all("/['\"]([a-zA-Z0-9_\-]+)['\"]/", $inner, $matches);
$pluginOrder = $matches[1] ?? [];
assert_true(!empty($pluginOrder) && $pluginOrder[0] === 'xskin', "xskin is at index 0 (the beginning) of plugins array");
assert_true(array_search('archive', $pluginOrder) > 0, "'archive' appears after 'xskin' in plugins array");

// Test 5: Re-running installer preserves user customized config
echo "\n--- Test 5: Config Preservation on Update ---\n";
file_put_contents($tempDir . '/plugins/xcalendar/config.inc.php', "<?php // User modified xcalendar config\n\$config['custom'] = 123;\n");
file_put_contents($tempDir . '/plugins/xsignature/config.inc.php', "<?php // User modified xsignature config\n\$config['custom_sig'] = 456;\n");
$installerUpdate = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installerUpdate->parseCliArgs(['--roundcube-path=' . $tempDir]);
$installerUpdate->execute();
$preservedConfig = file_get_contents($tempDir . '/plugins/xcalendar/config.inc.php');
assert_true(strpos($preservedConfig, "\$config['custom'] = 123;") !== false, "Preserves modified user config in plugins/xcalendar/config.inc.php");
$preservedSigConfig = file_get_contents($tempDir . '/plugins/xsignature/config.inc.php');
assert_true(strpos($preservedSigConfig, "\$config['custom_sig'] = 456;") !== false, "Preserves modified user config in plugins/xsignature/config.inc.php");

// Test 6: Upgrading empty license_key to RCPLUSFREE20266u
echo "\n--- Test 6: Empty License Key Upgrade Verification ---\n";
file_put_contents($tempDir . '/config/config.inc.php', str_replace("'RCPLUSFREE20266u'", "''", file_get_contents($tempDir . '/config/config.inc.php')));
$installerUpgrade = new RoundcubeExtraContentInstaller(dirname(__DIR__), $tempDir);
$installerUpgrade->parseCliArgs(['--roundcube-path=' . $tempDir, '--activate']);
$installerUpgrade->execute();
$upgradedConfig = file_get_contents($tempDir . '/config/config.inc.php');
assert_true(strpos($upgradedConfig, "\$config['license_key'] = 'RCPLUSFREE20266u'") !== false, "Empty license_key automatically upgraded to RCPLUSFREE20266u");

// Test 7: Adding 'xskin' at the beginning when other plugins already exist: $config['plugins'] = array('other_plugin', 'one_more_plugin')
echo "\n--- Test 7: 'xskin' Added at Beginning of Existing Plugins Array ---\n";
$test7Dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rc_test7_' . uniqid();
@mkdir($test7Dir . '/program/include', 0777, true);
@mkdir($test7Dir . '/plugins', 0777, true);
@mkdir($test7Dir . '/skins', 0777, true);
@mkdir($test7Dir . '/config', 0777, true);
file_put_contents($test7Dir . '/index.php', "<?php\n");
file_put_contents($test7Dir . '/program/include/iniset.php', "<?php\n");

$configWithOtherPlugins = <<<PHP
<?php
\$config = [];
\$config['skin'] = 'gmail_plus';
\$config['license_key'] = 'RCPLUSFREE20266u';
\$config['remove_vendor_branding'] = true;
\$config['plugins'] = array('other_plugin', 'one_more_plugin');
PHP;
file_put_contents($test7Dir . '/config/config.inc.php', $configWithOtherPlugins);

$installer7 = new RoundcubeExtraContentInstaller(dirname(__DIR__), $test7Dir);
$installer7->parseCliArgs(['--roundcube-path=' . $test7Dir, '--activate']);
$installer7->execute();

$res7 = file_get_contents($test7Dir . '/config/config.inc.php');
preg_match('/\$config\[[\'"]plugins[\'"]\]\s*=\s*(?:array\s*\((.*?)\)|\[(.*?)\])\s*;/is', $res7, $pm7);
$inner7 = ($pm7[1] !== '') ? $pm7[1] : ($pm7[2] ?? '');
preg_match_all("/['\"]([a-zA-Z0-9_\-]+)['\"]/", $inner7, $matches7);
$order7 = $matches7[1] ?? [];

assert_true(!empty($order7) && $order7[0] === 'xskin', "Test 7: 'xskin' is first element in plugins array");
assert_true(isset($order7[1]) && $order7[1] === 'other_plugin', "Test 7: 'other_plugin' follows 'xskin'");
assert_true(isset($order7[2]) && $order7[2] === 'one_more_plugin', "Test 7: 'one_more_plugin' follows 'other_plugin'");
remove_dir_recursive($test7Dir);

// Test 8: Reordering when 'xskin' is already in the array but not at the beginning
echo "\n--- Test 8: 'xskin' Reordered to Beginning When Found in Middle ---\n";
$test8Dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rc_test8_' . uniqid();
@mkdir($test8Dir . '/program/include', 0777, true);
@mkdir($test8Dir . '/plugins', 0777, true);
@mkdir($test8Dir . '/skins', 0777, true);
@mkdir($test8Dir . '/config', 0777, true);
file_put_contents($test8Dir . '/index.php', "<?php\n");
file_put_contents($test8Dir . '/program/include/iniset.php', "<?php\n");

$configXskinMiddle = <<<PHP
<?php
\$config = [];
\$config['skin'] = 'gmail_plus';
\$config['license_key'] = 'RCPLUSFREE20266u';
\$config['remove_vendor_branding'] = true;
\$config['plugins'] = array('first_plugin', 'xskin', 'last_plugin');
PHP;
file_put_contents($test8Dir . '/config/config.inc.php', $configXskinMiddle);

$installer8 = new RoundcubeExtraContentInstaller(dirname(__DIR__), $test8Dir);
$installer8->parseCliArgs(['--roundcube-path=' . $test8Dir, '--activate']);
$installer8->execute();

$res8 = file_get_contents($test8Dir . '/config/config.inc.php');
preg_match('/\$config\[[\'"]plugins[\'"]\]\s*=\s*(?:array\s*\((.*?)\)|\[(.*?)\])\s*;/is', $res8, $pm8);
$inner8 = ($pm8[1] !== '') ? $pm8[1] : ($pm8[2] ?? '');
preg_match_all("/['\"]([a-zA-Z0-9_\-]+)['\"]/", $inner8, $matches8);
$order8 = $matches8[1] ?? [];

assert_true(!empty($order8) && $order8[0] === 'xskin', "Test 8: 'xskin' moved to index 0");
assert_true(count(array_keys($order8, 'xskin')) === 1, "Test 8: 'xskin' appears exactly once");
assert_true(in_array('first_plugin', $order8, true) && in_array('last_plugin', $order8, true), "Test 8: other plugins preserved");
remove_dir_recursive($test8Dir);

// Cleanup
remove_dir_recursive($tempDir);

echo "\n*** ALL INSTALLER TESTS PASSED SUCCESSFULLY ***\n";
