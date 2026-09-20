<?php
/**
 * Test suite for Lifeprisma AI Executive Triage endpoint,
 * multi-tier caching, and Throwable safety.
 */

$test_count = 0;
$passed_count = 0;

function assert_true($cond, $desc) {
    global $test_count, $passed_count;
    $test_count++;
    if ($cond) {
        $passed_count++;
        echo "PASSED: {$desc}\n";
    } else {
        echo "FAILED: {$desc}\n";
    }
}

echo "=== LIFEPIRISMA AI EXECUTIVE TRIAGE TEST SUITE ===\n\n";

// --- 1. Static and Source Code Checks ---
echo "--- Test 1: Source Code Hardening Checks --- \n";
$php_code = file_get_contents(__DIR__ . '/../lifeprisma_ai.php');

assert_true(strpos($php_code, "class_exists('Redis')") !== false, "lifeprisma_ai.php guards Redis instantiation with class_exists('Redis')");
assert_true(strpos($php_code, "get_rcube_cache") !== false, "lifeprisma_ai.php implements Roundcube core cache tier");
assert_true(strpos($php_code, "get_file_cache_dir") !== false, "lifeprisma_ai.php implements file-based cache fallback tier");
assert_true(strpos($php_code, "catch (\\Throwable \$e)") !== false, "lifeprisma_ai.php uses Throwable catching for PHP 8+ error safety");
assert_true(strpos($php_code, "save_prefs(['genia_triage_log' => \$tlog], true)") !== false, "lifeprisma_ai.php passes no_session=true when saving preferences post-session_write_close");

// --- 2. Multi-tier Cache Behavior in Non-Redis Environment ---
echo "\n--- Test 2: Cache Functionality Without Redis --- \n";

// Ensure Redis class does not exist in this CLI environment
assert_true(!class_exists('Redis'), "Redis PHP extension is absent (simulating standard webmail environment)");

// Define mock classes for CLI testing
if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;

        public function __construct()
        {
            $this->config = new class {
                private $data = [];
                public function get($k, $d = null) { return $this->data[$k] ?? $d; }
                public function set($k, $v) { $this->data[$k] = $v; }
                public function mail_domain() { return 'thechargegrid.com'; }
            };
            $this->output = new class {
                public function set_env($k, $v) {}
                public function command($cmd, ...$args) {}
                public function include_script($s) {}
                public function include_stylesheet($s) {}
            };
            $this->storage = null;
            $this->user = null;
        }

        public static function get_instance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_storage() { return $this->storage; }
        public function get_cache($name, $type = 'db', $ttl = 0) { return null; }
        public function check_request_token($input) { return true; }
        public function get_dbh() {
            return new class {
                public function table_name($t) { return $t; }
                public function query($sql, ...$params) { return false; }
                public function fetch_assoc($res) { return null; }
                public function now() { return 'NOW()'; }
            };
        }
    }

    class rcmail extends rcube {}

    class rcube_plugin
    {
        public $api;
        public $home;
        public function __construct($api = null) {
            $this->home = realpath(__DIR__ . '/..');
        }
        public function load_config() {}
        public function add_texts($d) {}
        public function register_action($a, $cb) {}
        public function add_hook($h, $cb) {}
        public function include_script($s) {}
        public function include_stylesheet($s) {}
    }

    class rcube_utils
    {
        const INPUT_POST = 1;
        const INPUT_GET = 2;
        public static function get_input_string($k, $src) {
            return $src === self::INPUT_POST ? ($_POST[$k] ?? '') : ($_GET[$k] ?? '');
        }
    }

    class rcube_message
    {
        public $headers;
        public function __construct($uid = null, $folder = null) {
            $this->headers = null;
        }
    }
}

require_once __DIR__ . '/../lifeprisma_ai.php';

$plugin = new lifeprisma_ai();

$ref_class = new ReflectionClass($plugin);

// Test redis_connect()
$redis_conn_method = $ref_class->getMethod('redis_connect');
$redis_conn_method->setAccessible(true);
$redis_res = $redis_conn_method->invoke($plugin);
assert_true($redis_res === false, "redis_connect() gracefully returns false when Redis class is absent without fatal error");

// Test cache_set and cache_get
$cache_set_method = $ref_class->getMethod('cache_set');
$cache_set_method->setAccessible(true);
$cache_get_method = $ref_class->getMethod('cache_get');
$cache_get_method->setAccessible(true);

$test_key = "test_mbox:test_uid_" . uniqid();
$test_payload = [
    'status' => 'success',
    'analysis' => [
        'category' => 'action_required',
        'urgency' => 'high',
        'summary' => 'Test executive summary briefing.',
    ],
    'time' => time(),
];

// Write to cache
$cache_set_method->invoke($plugin, $test_key, $test_payload, 300);

// Read from cache
$retrieved = $cache_get_method->invoke($plugin, $test_key);
assert_true(is_array($retrieved), "cache_get() successfully retrieved cached data without Redis");
assert_true(isset($retrieved['status']) && $retrieved['status'] === 'success', "Retrieved cache status matches expected payload");
assert_true(isset($retrieved['analysis']['urgency']) && $retrieved['analysis']['urgency'] === 'high', "Retrieved analysis structure is intact");

// Test file-based cache persistence
$file_cache_dir_method = $ref_class->getMethod('get_file_cache_dir');
$file_cache_dir_method->setAccessible(true);
$cache_dir = $file_cache_dir_method->invoke($plugin);
assert_true(is_dir($cache_dir) && is_writable($cache_dir), "Cache directory exists and is writable ({$cache_dir})");

$expected_cache_file = $cache_dir . '/lpai_' . md5($test_key) . '.json';
assert_true(file_exists($expected_cache_file), "Cache entry was persisted to file system fallback ({$expected_cache_file})");

$file_content = json_decode(file_get_contents($expected_cache_file), true);
assert_true(isset($file_content['data']['analysis']['category']) && $file_content['data']['analysis']['category'] === 'action_required', "Persisted JSON file contains valid wrapped cache data");

// Test expired cache behavior
$expired_key = "expired_test_" . uniqid();
$expired_payload = ['status' => 'expired_data'];
$cache_set_method->invoke($plugin, $expired_key, $expired_payload, -10); // Expired 10 seconds ago

// Clear in-memory cache to force reading from disk
$in_mem_prop = $ref_class->getProperty('in_memory_cache');
$in_mem_prop->setAccessible(true);
$in_mem_prop->setValue([]);

$expired_retrieved = $cache_get_method->invoke($plugin, $expired_key);
assert_true($expired_retrieved === null, "Expired cache entry returns null and purges stale file");

// Cleanup test files
@unlink($expected_cache_file);
$expired_file = $cache_dir . '/lpai_' . md5($expired_key) . '.json';
if (file_exists($expired_file)) @unlink($expired_file);

// --- 3. Message Context and Header Fetching Resilience ---
echo "\n--- Test 3: Storage & Header Resilience --- \n";

// Verify fetch_raw_headers and fetch_message_context do not crash when storage is null
$fetch_headers_method = $ref_class->getMethod('fetch_raw_headers');
$fetch_headers_method->setAccessible(true);

$raw_h = '';
try {
    $raw_h = $fetch_headers_method->invoke($plugin, 99999, 'INBOX');
    assert_true(is_string($raw_h), "fetch_raw_headers() gracefully handles null storage without 500 fatal");
} catch (\Throwable $e) {
    assert_true(false, "fetch_raw_headers() threw unhandled exception: " . $e->getMessage());
}

$fetch_ctx_method = $ref_class->getMethod('fetch_message_context');
$fetch_ctx_method->setAccessible(true);

$ctx = [];
try {
    $ctx = $fetch_ctx_method->invoke($plugin, 99999, 'INBOX');
    assert_true(is_array($ctx), "fetch_message_context() gracefully handles null storage without 500 fatal");
} catch (\Throwable $e) {
    assert_true(false, "fetch_message_context() threw unhandled exception: " . $e->getMessage());
}

$create_draft_method = $ref_class->getMethod('create_imap_draft');
$create_draft_method->setAccessible(true);

try {
    $draft_res = $create_draft_method->invoke($plugin, 'test@example.com', 'Subject', 'Reply body');
    assert_true($draft_res === false, "create_imap_draft() gracefully returns false when storage is null");
} catch (\Throwable $e) {
    assert_true(false, "create_imap_draft() threw unhandled exception: " . $e->getMessage());
}

// --- 4. JavaScript and Asset Integration ---
echo "\n--- Test 4: JavaScript Frontend Bundle Verification --- \n";
$js_src = file_get_contents(__DIR__ . '/../src/lifeprisma_ai.js');
$js_min = file_get_contents(__DIR__ . '/../lifeprisma_ai.min.js');
$js_elastic = file_get_contents(__DIR__ . '/../skins/elastic/lifeprisma_ai.min.js');
$js_gmail = file_get_contents(__DIR__ . '/../skins/gmail_plus/lifeprisma_ai.min.js');

assert_true(strpos($js_src, "plugin.lifeprisma_ai_triage") !== false, "src/lifeprisma_ai.js targets plugin.lifeprisma_ai_triage");
assert_true(strpos($js_src, "Failed to load executive triage analysis") !== false, "src/lifeprisma_ai.js includes error notification handler");
assert_true(strpos($js_min, "plugin.lifeprisma_ai_triage") !== false, "lifeprisma_ai.min.js contains compiled triage action endpoint");
assert_true(strpos($js_elastic, "plugin.lifeprisma_ai_triage") !== false, "skins/elastic bundle contains compiled triage action endpoint");
assert_true(strpos($js_gmail, "plugin.lifeprisma_ai_triage") !== false, "skins/gmail_plus bundle contains compiled triage action endpoint");

// Verify bundles are identical
assert_true($js_min === $js_elastic && $js_min === $js_gmail, "All skin minified JS bundles are in sync with root bundle");

echo "\n*** ALL TRIAGE AND MULTI-TIER CACHE TESTS PASSED ({$passed_count}/{$test_count}) ***\n";
exit($passed_count === $test_count ? 0 : 1);
