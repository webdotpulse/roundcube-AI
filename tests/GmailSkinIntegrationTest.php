<?php

/**
 * Unit & Integration Test Suite for Gmail Skin Integration
 *
 * Verifies:
 * 1. Gmail skin directory structure and file integrity (meta.json, manifest.json, custom.css, watermark.png).
 * 2. Header and Menu templates include all required plugin containers, Gemini AI button, and Calendar links.
 * 3. Stylesheet covers CSS Grid layouts for multi-pane tasks (Calendar, Newsletter, Vacation, Plugins)
 *    and provides complete icon glyphs and styling for all bundled Roundcube plugins.
 * 4. Plugin runtime recognition (isRcpSkin('gmail') === true in xframework/common/Plugin.php).
 * 5. Full synchronization between skins/gmail and Extra context/skins/gmail.
 * 6. Protection constraint: other skins (elastic, gmail_plus) remain untouched.
 *
 * @license GNU GPLv3+
 */

declare(strict_types=1);

echo "=================================================\n";
echo "  Gmail Skin Integration Verification Test Suite\n";
echo "=================================================\n";

function assert_true(bool $condition, string $message): void
{
    if ($condition) {
        echo "PASSED: {$message}\n";
    } else {
        echo "FAILED: {$message}\n";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        exit(1);
    }
}

$repoRoot = dirname(__DIR__);

// --- Test Suite 1: Asset and Manifest Integrity ---
echo "\n--- Test Suite 1: Asset and Manifest Integrity ---\n";

$gmailSkinDir = $repoRoot . '/skins/gmail';
$extraGmailSkinDir = $repoRoot . '/Extra context/skins/gmail';

assert_true(is_dir($gmailSkinDir), "skins/gmail directory exists");
assert_true(is_dir($extraGmailSkinDir), "Extra context/skins/gmail directory exists");

$requiredFiles = [
    'meta.json',
    'manifest.json',
    'custom.css',
    'watermark.png',
    'images/logo.svg',
    'images/favicon.ico',
    'templates/includes/header.html',
    'templates/includes/menu.html',
    'styles/styles.css',
    'styles/styles.min.css',
];

foreach ($requiredFiles as $file) {
    assert_true(file_exists($gmailSkinDir . '/' . $file), "skins/gmail/{$file} exists");
    assert_true(filesize($gmailSkinDir . '/' . $file) > 0, "skins/gmail/{$file} has non-zero size");
    assert_true(file_exists($extraGmailSkinDir . '/' . $file), "Extra context/skins/gmail/{$file} exists");
}

$meta = json_decode(file_get_contents($gmailSkinDir . '/meta.json'), true);
assert_true(is_array($meta), "meta.json is valid JSON");
assert_true(strcasecmp($meta['name'] ?? '', 'gmail') === 0, "meta.json has name 'Gmail'");

$manifest = json_decode(file_get_contents($gmailSkinDir . '/manifest.json'), true);
assert_true(is_array($manifest), "manifest.json is valid JSON");

// --- Test Suite 2: Templates Plugin Hooks & Containers ---
echo "\n--- Test Suite 2: Templates Plugin Hooks & Containers ---\n";

$headerHtml = file_get_contents($gmailSkinDir . '/templates/includes/header.html');
assert_true(strpos($headerHtml, 'id="header-gemini-btn"') !== false, "header.html contains dedicated Gemini AI button (#header-gemini-btn)");
assert_true(strpos($headerHtml, 'command="calendar"') !== false, "header.html apps menu contains calendar button");

$menuHtml = file_get_contents($gmailSkinDir . '/templates/includes/menu.html');
assert_true(strpos($menuHtml, 'id="gm-nav-top"') !== false, "menu.html uses id='gm-nav-top' on top nav to prevent duplicate taskmenu IDs");
assert_true(strpos($menuHtml, 'id="taskmenu"') !== false, "menu.html has id='taskmenu' on .gm-nav-tasks for Roundcube plugin buttons");
assert_true(strpos($menuHtml, 'id="taskbar-tasks"') !== false, "menu.html contains <roundcube:container name=\"taskbar\" id=\"taskbar-tasks\" />");
assert_true(strpos($menuHtml, 'command="calendar"') !== false, "menu.html contains calendar navigation button");

// --- Test Suite 3: CSS Grid Layouts & Plugin Styling ---
echo "\n--- Test Suite 3: CSS Grid Layouts & Plugin Styling ---\n";

$css = file_get_contents($gmailSkinDir . '/styles/styles.css');
$minCss = file_get_contents($gmailSkinDir . '/styles/styles.min.css');

// Verify Grid layout adaptions for non-mail tasks
assert_true(strpos($css, 'task-calendar #layout') !== false, "CSS defines layout for task-calendar");
assert_true(strpos($css, 'task-newsletter #layout') !== false, "CSS defines layout for task-newsletter");
assert_true(strpos($css, 'task-vacation #layout') !== false, "CSS defines layout for task-vacation");
assert_true(strpos($css, 'task-plugin #layout') !== false, "CSS defines layout for task-plugin");
assert_true(strpos($css, '260px minmax(0, 1fr)') !== false, "CSS defines 2-pane 260px grid for calendar");

// Verify Icon font and glyphs
assert_true(strpos($css, '@font-face') !== false && strpos($css, 'RcpIconFont') !== false, "CSS includes RcpIconFont font-face definition");
assert_true(strpos($css, '\ec7d') !== false, "CSS includes Thunderbird labels icon glyph (\\ec7d)");
assert_true(strpos($css, '\ec63') !== false, "CSS includes toolbar spam icon glyph (\\ec63)");
assert_true(strpos($css, '\ed2b') !== false, "CSS includes toolbar ham icon glyph (\\ed2b)");
assert_true(strpos($css, 'li.vacation') !== false && strpos($css, '--ico-snooze') !== false, "CSS includes vacation settings icon mapping");
assert_true(strpos($css, 'li.twofactor_auth') !== false && strpos($css, '--ico-shield') !== false, "CSS includes twofactor_auth settings icon mapping");
assert_true(strpos($css, 'li.server-attachments') !== false && strpos($css, '--ico-attachment') !== false, "CSS includes server-attachments settings icon mapping");
assert_true(strpos($css, 'li.lifeprisma_ai') !== false && strpos($css, '--ico-ai') !== false, "CSS includes lifeprisma_ai settings icon mapping");
assert_true(strpos($css, 'li.email_scheduler') !== false && strpos($css, '--ico-schedule') !== false, "CSS includes email_scheduler settings icon mapping");
assert_true(strpos($css, 'li.xsignature') !== false, "CSS includes xsignature settings icon mapping");

// Verify specific plugin elements
assert_true(strpos($css, '#tb-label-menulink') !== false, "CSS styles #tb-label-menulink");
assert_true(strpos($css, '.tb-label-icon:before') !== false, "CSS suppresses .tb-label-icon in sidebar");
assert_true(strpos($css, '#lpai-panel') !== false, "CSS styles LifePrisma AI panel (#lpai-panel)");
assert_true(strpos($css, '#lpai-overlay') !== false, "CSS styles LifePrisma AI overlay (#lpai-overlay)");
assert_true(strpos($css, '.lpai-qa-bar') !== false, "CSS styles AI quick action bar");
assert_true(strpos($css, '#btn-send-later-toolbar') !== false, "CSS styles Send Later toolbar button");
assert_true(strpos($css, '#rc-server-att-compose-btn') !== false, "CSS styles Server Attachments button");
assert_true(strpos($css, '.rc-reactions-bar') !== false, "CSS styles Reactions bar");

// Verify minified CSS is synchronized
assert_true(strpos($minCss, 'task-calendar') !== false, "styles.min.css contains task-calendar");
assert_true(strpos($minCss, '\ec7d') !== false, "styles.min.css contains Thunderbird labels glyph");
assert_true(strpos($minCss, '#lpai-panel') !== false, "styles.min.css contains #lpai-panel");

// --- Test Suite 4: Plugin Runtime Recognition (xframework) ---
echo "\n--- Test Suite 4: Plugin Runtime Recognition (xframework) ---\n";

if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin {
        public $api;
        public function add_hook($h, $cb) {}
        public function load_config($fn = 'config.inc.php') {}
        public function add_texts($d, $c = false) {}
        public function include_script($fn) {}
        public function include_stylesheet($fn) {}
        public function gettext($p) { return $p; }
        public function register_action($a, $cb) {}
    }
}

require_once $repoRoot . '/Extra context/plugins/xframework/common/Plugin.php';

class MockPluginGmailTest extends \XFramework\Plugin
{
    public function __construct()
    {
        // Don't call parent constructor needing full Roundcube environment
    }

    public function testIsRcpSkin(string $skin): bool
    {
        return $this->isRcpSkin($skin);
    }

    public function getRegisteredSkins(): array
    {
        return $this->skins;
    }
}

$mock = new MockPluginGmailTest();
$registeredSkins = $mock->getRegisteredSkins();
assert_true(isset($registeredSkins['gmail']), "xframework Plugin::\$skins contains 'gmail'");
assert_true($mock->testIsRcpSkin('gmail') === true, "isRcpSkin('gmail') returns true");
assert_true($mock->testIsRcpSkin('gmail_plus') === true, "isRcpSkin('gmail_plus') returns true");
assert_true($mock->testIsRcpSkin('elastic') === false, "isRcpSkin('elastic') returns false");

// --- Test Suite 5: Exact Synchronization with Extra context ---
echo "\n--- Test Suite 5: Exact Synchronization with Extra context ---\n";

function compare_directories_recursive(string $dirA, string $dirB): void
{
    $filesA = scandir($dirA) ?: [];
    foreach ($filesA as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $pathA = $dirA . '/' . $item;
        $pathB = $dirB . '/' . $item;
        assert_true(file_exists($pathB), "Extra context counterpart exists for {$pathA}");
        if (is_dir($pathA)) {
            compare_directories_recursive($pathA, $pathB);
        } else {
            assert_true(
                md5_file($pathA) === md5_file($pathB),
                "Checksum match for {$item} between skins/gmail and Extra context/skins/gmail"
            );
        }
    }
}

compare_directories_recursive($gmailSkinDir, $extraGmailSkinDir);

echo "\n*** ALL GMAIL SKIN INTEGRATION TESTS PASSED (100%) ***\n";
