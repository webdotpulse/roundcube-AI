<?php
/**
 * Test Suite for xsignature logo directory & URL resolution, legacy migration,
 * and permissions policy header.
 */

declare(strict_types=1);

if (!defined('RCMAIL_VERSION')) {
    define('RCMAIL_VERSION', '1.7.4');
}

if (!defined('RCUBE_INSTALL_PATH')) {
    define('RCUBE_INSTALL_PATH', sys_get_temp_dir() . '/rcube_test_' . uniqid() . '/');
}

// Setup fake install path
mkdir(RCUBE_INSTALL_PATH . 'data/xsignature/000/000', 0777, true);
$testLegacyLogo = RCUBE_INSTALL_PATH . 'data/xsignature/000/000/testlogo.png';
file_put_contents($testLegacyLogo, 'FAKE_PNG_DATA');

// Mock dependencies
class rcube_config_mock
{
    private array $data = [];

    public function __construct(array $initial = [])
    {
        $this->data = $initial;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $val): void
    {
        $this->data[$key] = $val;
    }
}

class rcmail_mock
{
    public $config;
    public $task = 'settings';
    public $action = '';
    public $output;

    public function __construct()
    {
        $this->config = new rcube_config_mock();
        $this->output = new class {
            private array $env = [];
            public function get_env(string $key) { return $this->env[$key] ?? []; }
            public function set_env(string $key, $val): void { $this->env[$key] = $val; }
            public function add_label(...$labels): void {}
        };
    }
}

class rcube_plugin
{
    public $ID = 'xsignature';
    public $api;
    public $home = '';
    public function __construct($api = null) {
        $this->api = $api;
    }
    public function add_hook(string $hook, $callback): void {}
    public function register_action(string $action, $callback): void {}
    public function gettext($name): string { return is_array($name) ? ($name['name'] ?? '') : (string)$name; }
}

class rcube extends rcmail_mock {}

// Mock xrc() and Utils if needed
require_once __DIR__ . '/../Extra context/plugins/xframework/common/Utils.php';
$_SERVER['HTTP_HOST'] = 'webmail.thechargegrid.com';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/?_task=settings';

// Subclass xsignature for unit testing protected methods
require_once __DIR__ . '/../Extra context/plugins/xsignature/xsignature.php';

class MockDb extends \XFramework\DatabaseGeneric
{
    private array $allResults = [];
    public function __construct(array $allResults = []) {
        $this->allResults = $allResults;
    }
    public function all(string $query, string|array|null $parameters = [], string $resultKeyField = ""): bool|array {
        return $this->allResults;
    }
    public function update(string $table, array $data, array $whereParams): bool {
        return true;
    }
}

class TestableXsignature extends xsignature
{
    public function __construct(rcube $rcmail)
    {
        $this->rcmail = $rcmail;
        $this->userId = 1;
        $this->db = new MockDb();
    }

    public function testGetLogoDirectory(): string
    {
        return $this->getLogoDirectory();
    }

    public function testGetLogoUrlBase(): string
    {
        return $this->getLogoUrlBase();
    }

    public function testFixLogoUrl(string $url): string
    {
        return $this->fixLogoUrl($url);
    }

    public function testMigrateLegacyLogo(string $relPath): void
    {
        $this->migrateLegacyLogo($relPath);
    }
}

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        echo "FAILED: $message\n";
        exit(1);
    }
    echo "PASSED: $message\n";
}

echo "=================================================\n";
echo "  xsignature Logo & Roundcube 1.7+ Tests\n";
echo "=================================================\n";

$rcmail = new rcube();
$plugin = new TestableXsignature($rcmail);

// 1. Default logo directory on Roundcube 1.7+ should be inside plugins/xsignature/data
echo "\n--- Test 1: Default Logo Directory on RC 1.7+ ---\n";
$dir = $plugin->testGetLogoDirectory();
test_assert(
    str_ends_with($dir, 'Extra context/plugins/xsignature/data/'),
    "Default logo_dir on RC 1.7+ points to plugins/xsignature/data/ (got: {$dir})"
);

// 2. Default logo URL base on Roundcube 1.7+ should use static.php/plugins/xsignature/data/
echo "\n--- Test 2: Default Logo URL Base on RC 1.7+ ---\n";
$url = $plugin->testGetLogoUrlBase();
test_assert(
    str_contains($url, 'static.php/plugins/xsignature/data/'),
    "Default logo_url on RC 1.7+ routes through static.php/plugins/xsignature/data/ (got: {$url})"
);

// 3. Respect custom logo_dir and logo_url if configured
echo "\n--- Test 3: Custom logo_dir & logo_url Configuration ---\n";
$customDir = sys_get_temp_dir() . '/custom_logos';
$rcmail->config->set('logo_dir', $customDir);
$rcmail->config->set('logo_url', 'https://cdn.example.com/logos');
test_assert(
    $plugin->testGetLogoDirectory() === $customDir . '/',
    "Custom logo_dir is respected"
);
test_assert(
    $plugin->testGetLogoUrlBase() === 'https://cdn.example.com/logos/',
    "Custom logo_url is respected"
);

// Reset config back to default
$rcmail->config->set('logo_dir', null);
$rcmail->config->set('logo_url', null);

// 4. Legacy URL resolution & automatic disk migration
echo "\n--- Test 4: fixLogoUrl & Legacy File Migration ---\n";
$legacyUrl = "https://webmail.thechargegrid.com/data/xsignature/000/000/testlogo.png";
$fixedUrl = $plugin->testFixLogoUrl($legacyUrl);
test_assert(
    str_contains($fixedUrl, 'static.php/plugins/xsignature/data/000/000/testlogo.png'),
    "fixLogoUrl converts legacy data/xsignature URL to static.php/plugins/xsignature/data (got: {$fixedUrl})"
);

$migratedFile = $plugin->testGetLogoDirectory() . '000/000/testlogo.png';
test_assert(
    file_exists($migratedFile) && file_get_contents($migratedFile) === 'FAKE_PNG_DATA',
    "Legacy logo file on disk was automatically migrated to plugins/xsignature/data"
);

// Clean up migrated file & temp dir
@unlink($migratedFile);
@unlink($testLegacyLogo);
@rmdir(dirname($testLegacyLogo));
@rmdir(dirname(dirname($testLegacyLogo)));
@rmdir(dirname(dirname(dirname($testLegacyLogo))));
@rmdir(RCUBE_INSTALL_PATH);

// 5. Legacy data/xsignature URL rewriting in renderCompose()
echo "\n--- Test 5: Legacy URL Rewriting in renderCompose() ---\n";
$composePlugin = new class($rcmail) extends xsignature {
    public function __construct(rcube $rcmail) {
        $this->rcmail = $rcmail;
        $this->userId = 1;
        $this->db = new MockDb([
            [
                'id' => 1,
                'enabled' => 1,
                'html' => '<p>Best regards,<br><img src="https://webmail.thechargegrid.com/data/xsignature/000/000/logo.png"></p>',
                'plain' => 'Best regards,',
            ]
        ]);
    }
};

$res = $composePlugin->renderCompose([]);
$signatures = $rcmail->output->get_env('signatures');
test_assert(
    isset($signatures[1]['html']),
    "Compose signature was generated"
);
test_assert(
    str_contains($signatures[1]['html'], 'static.php/plugins/xsignature/data/'),
    "renderCompose dynamically rewrote legacy data/xsignature/ URL to static.php/plugins/xsignature/data/"
);
test_assert(
    !str_contains($signatures[1]['html'], '/data/xsignature/'),
    "Legacy /data/xsignature/ URL was completely replaced in compose HTML"
);

echo "\n*** ALL XSIGNATURE LOGO TESTS PASSED SUCCESSFULLY ***\n";
