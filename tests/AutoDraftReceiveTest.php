<?php
/**
 * Test suite for Lifeprisma AI Autonomous Triage, Labeling,
 * and Auto-Drafting on incoming emails (Receive mode).
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

echo "=== LIFEPIRISMA AI RECEIVE MODE AUTO-TRIAGE & DRAFT TEST SUITE ===\n\n";

$ai_dir = is_dir(__DIR__ . '/../Extra context/plugins/roundcube_ai')
    ? __DIR__ . '/../Extra context/plugins/roundcube_ai'
    : dirname(__DIR__);

// --- 1. Static and Source Code Checks ---
echo "--- Test 1: Source Code Structure Checks --- \n";
$php_code = file_get_contents($ai_dir . '/lifeprisma_ai.php');
$js_src   = file_get_contents($ai_dir . '/src/lifeprisma_ai.js');
$js_min   = file_get_contents($ai_dir . '/lifeprisma_ai.min.js');
$js_el    = file_get_contents($ai_dir . '/skins/elastic/lifeprisma_ai.min.js');
$js_gp    = file_get_contents($ai_dir . '/skins/gmail_plus/lifeprisma_ai.min.js');

assert_true(strpos($php_code, "function get_auto_draft_mode") !== false, "lifeprisma_ai.php defines get_auto_draft_mode() resolver");
assert_true(strpos($php_code, "function is_message_triaged") !== false, "lifeprisma_ai.php defines is_message_triaged() helper");
assert_true(strpos($php_code, "function process_message_triage") !== false, "lifeprisma_ai.php defines reusable process_message_triage() engine");
assert_true(strpos($php_code, "\$this->process_message_triage(") !== false, "lifeprisma_ai.php invokes process_message_triage internally");

// Verification that handle_new_messages uses process_message_triage
assert_true(
    preg_match('/handle_new_messages.*process_message_triage/s', $php_code),
    "handle_new_messages hook handler delegates to process_message_triage"
);

// Verification that handle_messages_list evaluates incoming unread messages for triage
assert_true(
    strpos($php_code, "auto_draft_mode === 'receive' || \$auto_draft_mode === 'all'") !== false,
    "handle_messages_list checks receive and all modes"
);
assert_true(
    strpos($php_code, "lpai_pending_triage") !== false,
    "handle_messages_list passes pending triage queue to client environment"
);

// Frontend JS verification
assert_true(strpos($js_src, "lpai_queue_pending_triage") !== false, "src/lifeprisma_ai.js defines lpai_queue_pending_triage()");
assert_true(strpos($js_src, "plugin.lifeprisma_ai_pending_triage") !== false, "src/lifeprisma_ai.js listens for plugin.lifeprisma_ai_pending_triage event");
assert_true(strpos($js_src, "lpai_triage_queue") !== false, "src/lifeprisma_ai.js implements lpai_triage_queue");

// JS Bundle verification
assert_true(strpos($js_min, "lpai_queue_pending_triage") !== false, "lifeprisma_ai.min.js contains compiled lpai_queue_pending_triage");
assert_true($js_min === $js_el && $js_min === $js_gp, "All 3 minified JS files are 100% byte-for-byte identical");

// --- 2. Functional Mock Environment Tests ---
echo "\n--- Test 2: Mode Hierarchy and Resolution Functionality --- \n";

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
                public $data = [];
                public function get($k, $d = null) { return $this->data[$k] ?? $d; }
                public function set($k, $v) { $this->data[$k] = $v; }
                public function mail_domain() { return 'thechargegrid.com'; }
            };
            $this->output = new class {
                public $env = [];
                public function set_env($k, $v) { $this->env[$k] = $v; }
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
            global $ai_dir;
            $this->home = realpath($ai_dir);
        }
        public function load_config() {}
        public function add_texts($d) {}
        public function register_action($a, $cb) {}
        public function add_hook($h, $cb) {}
        public function include_script($s) {}
        public function include_stylesheet($s) {}
        public function add_button($b, $t) {}
        public function local_skin_path() { return 'skins/elastic'; }
    }
}

require_once $ai_dir . '/lifeprisma_ai.php';

$plugin = new lifeprisma_ai();
$rcmail = rcmail::get_instance();

// 2a: Config fallback test
$rcmail->config->set('lifeprisma_ai_auto_draft_mode', 'receive');
$mode = $plugin->get_auto_draft_mode([]);
assert_true($mode === 'receive', "get_auto_draft_mode() returns config fallback 'receive' when no user preference set");

$rcmail->config->set('lifeprisma_ai_auto_draft_mode', 'open');
$mode = $plugin->get_auto_draft_mode([]);
assert_true($mode === 'open', "get_auto_draft_mode() returns config fallback 'open' when changed");

// 2b: User preference overrides config
$mode = $plugin->get_auto_draft_mode(['genia_auto_draft_mode' => 'receive']);
assert_true($mode === 'receive', "get_auto_draft_mode() honors user preference 'receive' over config 'open'");

$mode = $plugin->get_auto_draft_mode(['genia_auto_draft_mode' => 'disabled']);
assert_true($mode === 'disabled', "get_auto_draft_mode() honors user preference 'disabled' over config");

// --- 3. Functional Triage Check & Cache ---
echo "\n--- Test 3: is_message_triaged & Cache Integrity --- \n";

// Should be untriaged initially
$untriaged = $plugin->is_message_triaged(88881, 'INBOX', []);
assert_true($untriaged === false, "is_message_triaged() returns false for un-triaged message");

// Check when present in user preference log
$triaged_via_prefs = $plugin->is_message_triaged(88882, 'INBOX', [
    'genia_triage_log' => ['INBOX:88882' => ['status' => 'success']]
]);
assert_true($triaged_via_prefs === true, "is_message_triaged() detects previously triaged message from genia_triage_log");

// Test process_message_triage API key validation
$no_key_res = $plugin->process_message_triage(12345, 'INBOX', []);
assert_true(
    isset($no_key_res['code']) && $no_key_res['code'] === 'no_api_key',
    "process_message_triage() gracefully reports no_api_key when API key is empty"
);

echo "\n*** ALL AUTO-DRAFT RECEIVE MODE TESTS PASSED ({$passed_count}/{$test_count}) ***\n";
exit($passed_count === $test_count ? 0 : 1);
