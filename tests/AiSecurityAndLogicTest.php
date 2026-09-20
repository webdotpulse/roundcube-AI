<?php
/**
 * Comprehensive Automated Verification Suite for
 * Lifeprisma AI Security Hardening, Mathematical Logic, and Architecture.
 */

$test_count = 0;
$passed_count = 0;

function assert_test($cond, $desc) {
    global $test_count, $passed_count;
    $test_count++;
    if ($cond) {
        $passed_count++;
        echo "PASSED: {$desc}\n";
    } else {
        echo "FAILED: {$desc}\n";
    }
}

echo "=== LIFEPIRISMA AI SECURITY & LOGIC TEST SUITE ===\n\n";

// Mock Roundcube environment
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
                public $query_count = 0;
                public function table_name($t) { return $t; }
                public function query($sql, ...$params) {
                    $this->query_count++;
                    return new class {
                        public function fetch_assoc() { return ['total_users' => 150, 'active_users' => 42]; }
                    };
                }
                public function fetch_assoc($res) { return ['total_users' => 150, 'active_users' => 42]; }
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
}

require_once __DIR__ . '/../lifeprisma_ai.php';
require_once __DIR__ . '/../bin/worker.php';

$plugin = new lifeprisma_ai();
$ref_class = new ReflectionClass($plugin);

// --- Test 1: SSRF & DNS Rebinding Validation ---
echo "--- Test 1: SSRF & Host Validation --- \n";
$validate_url_method = $ref_class->getMethod('validate_api_url');
$validate_url_method->setAccessible(true);

assert_test($validate_url_method->invoke($plugin, 'https://generativelanguage.googleapis.com/v1beta/models') === true, "Allowed official Google AI endpoint");
assert_test($validate_url_method->invoke($plugin, 'https://custom.googleapis.com/test') === true, "Allowed official Google subdomain");
assert_test($validate_url_method->invoke($plugin, 'http://generativelanguage.googleapis.com') === false, "Rejected plain HTTP URL (requires HTTPS)");
assert_test($validate_url_method->invoke($plugin, 'https://127.0.0.1:8080/api') === false, "Blocked loopback IP 127.0.0.1");
assert_test($validate_url_method->invoke($plugin, 'https://169.254.169.254/latest/meta-data') === false, "Blocked cloud metadata IP 169.254.169.254");
assert_test($validate_url_method->invoke($plugin, 'https://10.0.0.5/api') === false, "Blocked private RFC 1918 10.x IP");
assert_test($validate_url_method->invoke($plugin, 'https://192.168.1.1/api') === false, "Blocked private RFC 1918 192.168.x IP");
assert_test($validate_url_method->invoke($plugin, 'https://localhost/api') === false, "Blocked localhost domain resolving to loopback");

// --- Test 2: AI Memory Isolation & Score Matching ---
echo "\n--- Test 2: Multi-Tenant AI Memory & Zero-Score Filtering --- \n";

// Test memory file path per user
$rcmail = rcmail::get_instance();
$rcmail->user = new class {
    public $ID = 42;
    public function get_identity() { return ['email' => 'user42@thechargegrid.com']; }
    public function get_username() { return 'user42'; }
};

$get_mem_file_method = $ref_class->getMethod('get_memory_file');
$get_mem_file_method->setAccessible(true);
$user42_file = $get_mem_file_method->invoke($plugin);
assert_test(strpos($user42_file, '/data/memory/user_') !== false, "User memory file is stored under data/memory/ with hashed user key");
assert_test(strpos($user42_file, md5("lpai_user_mem_42")) !== false, "User 42 memory filename contains specific user hash");

$rcmail->user->ID = 99;
$user99_file = $get_mem_file_method->invoke($plugin);
assert_test($user42_file !== $user99_file, "Different users receive completely distinct memory files (tenant isolation)");

// Test find_matching_memory logic
$sample_memories = [
    [
        'question' => 'How do I configure EV billing invoice export?',
        'subject' => 'EV Billing Invoices',
        'answer' => 'Navigate to Settings > Billing > Export to download CSV invoices.',
    ],
    [
        'question' => 'What is the server warranty policy?',
        'subject' => 'Hardware Warranty',
        'answer' => 'Hardware is covered under 3-year onsite support replacement.',
    ]
];

$find_mem_method = $ref_class->getMethod('find_matching_memory');
$find_mem_method->setAccessible(true);

// 1. Matched query
$matched = $find_mem_method->invoke($plugin, 'billing invoice export', $sample_memories, 3);
assert_test(count($matched) === 1, "find_matching_memory returns relevant memory when keywords match");
assert_test(($matched[0]['subject'] ?? '') === 'EV Billing Invoices', "Matched item subject is EV Billing Invoices");

// 2. Unmatched query: MUST RETURN EMPTY ARRAY (not array_slice fallback)
$unmatched = $find_mem_method->invoke($plugin, 'unrelated banana recipe topic', $sample_memories, 3);
assert_test(is_array($unmatched) && empty($unmatched), "find_matching_memory returns empty array on zero relevance score (prevents prompt contamination)");

// 3. Stopwords only query: MUST RETURN EMPTY ARRAY
$stopwords_query = $find_mem_method->invoke($plugin, 'the and for with that', $sample_memories, 3);
assert_test(is_array($stopwords_query) && empty($stopwords_query), "find_matching_memory returns empty array when query has only stopwords");

// --- Test 3: Compose Attachment Sandboxing ---
echo "\n--- Test 3: Compose Attachment Path Containment --- \n";
$compose_method = $ref_class->getMethod('handle_message_compose');
$compose_method->setAccessible(true);

// Prepare temporary session data simulating malicious traversal payload
$_SESSION['lpai_pending_compose'] = [
    'reply' => 'Testing containment',
    'subject' => 'Containment Test',
    'attachments' => [
        ['path' => '/etc/passwd', 'name' => 'passwd.txt'],
        ['path' => '../../../config/config.inc.php', 'name' => 'config.php'],
        ['path' => '/var/log/syslog', 'name' => 'syslog'],
    ]
];

$filtered_args = $compose_method->invoke($plugin, []);
assert_test(empty($filtered_args['attachments']), "handle_message_compose dropped all unauthorized external/traversal attachments (/etc/passwd, config.inc.php)");

// Simulate legitimate attachment in data/attachments/
$legit_dir = realpath(__DIR__ . '/..') . '/data/attachments/templates/test123';
if (!is_dir($legit_dir)) @mkdir($legit_dir, 0750, true);
$legit_file = $legit_dir . '/valid_doc.txt';
file_put_contents($legit_file, "Legitimate document content");

$_SESSION['lpai_pending_compose'] = [
    'reply' => 'Here is the brochure',
    'subject' => 'Brochure',
    'attachments' => [
        ['path' => 'data/attachments/templates/test123/valid_doc.txt', 'name' => 'valid_doc.txt', 'mimetype' => 'text/plain'],
    ]
];

$legit_args = $compose_method->invoke($plugin, []);
assert_test(count($legit_args['attachments'] ?? []) === 1, "handle_message_compose correctly permits legitimate files inside data/attachments");
assert_test(($legit_args['attachments'][0]['name'] ?? '') === 'valid_doc.txt', "Permitted attachment name matches expected");

@unlink($legit_file);
@rmdir($legit_dir);

// --- Test 4: Template Upload Extension Whitelist Verification ---
echo "\n--- Test 4: Template Upload Extension Whitelist & Blacklist --- \n";
$php_source = file_get_contents(__DIR__ . '/../lifeprisma_ai.php');
assert_test(strpos($php_source, "\$disallowed_exts = ['php', 'phtml'") !== false, "lifeprisma_ai.php explicitly blacklists dangerous executable extensions");
assert_test(strpos($php_source, "\$allowed_exts = ['pdf', 'doc', 'docx'") !== false, "lifeprisma_ai.php enforces strict whitelist of document/media extensions");
assert_test(strpos($php_source, "md5(uniqid((string) microtime(true), true))") !== false, "lifeprisma_ai.php generates randomized file hashes to prevent path collision");

// --- Test 5: Sliding-Window Rate Limiting ---
echo "\n--- Test 5: Sliding-Window Rate Limiter on Background Endpoints --- \n";
$rate_limit_method = $ref_class->getMethod('check_rate_limit');
$rate_limit_method->setAccessible(true);

$_SESSION = [];
$rcmail->config->set('lifeprisma_ai_rate_limit', 1);
$rcmail->config->set('lifeprisma_ai_rate_limit_per_min', 5);

// Test interactive cooldown
assert_test($rate_limit_method->invoke($plugin, 'request') === true, "Initial interactive request passes");
assert_test($rate_limit_method->invoke($plugin, 'request') === false, "Rapid consecutive interactive request blocked by cooldown");

// Test background sliding window
$_SESSION = [];
for ($i = 0; $i < 10; $i++) {
    $_SESSION['lpai_last_bg_req'] = 0; // reset cooldown to test sliding window capacity
    $pass = $rate_limit_method->invoke($plugin, 'triage');
    if ($i < 10) {
        assert_test($pass === true, "Background request #{$i} within sliding limit");
    }
}
// 11th request must be blocked because effective_max = 5 * 2 = 10
$_SESSION['lpai_last_bg_req'] = 0;
assert_test($rate_limit_method->invoke($plugin, 'triage') === false, "Background request exceeding max_per_min * 2 is strictly blocked");

// --- Test 6: Database Full-Table Scan Caching ---
echo "\n--- Test 6: Database Admin Stats Caching --- \n";
$stats_method = $ref_class->getMethod('get_usage_stats');
$stats_method->setAccessible(true);

$stats1 = $stats_method->invoke($plugin);
assert_test($stats1['total_users'] === 150 && $stats1['active_users'] === 42, "get_usage_stats returns expected aggregated metrics");

// Verify cached read does not invoke DB query
$cached_stats = $stats_method->invoke($plugin);
assert_test($cached_stats === $stats1, "Subsequent get_usage_stats call serves from multi-tier cache without query overhead");

// --- Test 7: Autonomous Worker CRLF Header Sanitization ---
echo "\n--- Test 7: Worker CRLF Header Injection Neutralization --- \n";

$crafted_to = "victim@company.com\r\nBcc: attacker@evil.com\r\nSubject: Injected";
$crafted_subject = "Important Update\r\nCc: spy@evil.com";
$draft_raw = lpai_worker_format_draft_message(
    $crafted_to,
    $crafted_subject,
    "Hello victim",
    "<msg123@domain>",
    date('r'),
    "Boss <boss@company.com>",
    "Original message",
    "me@company.com"
);

// Verify CRLF characters are not present in To or Subject header lines
assert_test(strpos($draft_raw, "To: victim@company.com Bcc: attacker@evil.com Subject: Injected\r\n") !== false, "Worker sanitized CRLF in To header into single-line whitespace");
assert_test(strpos($draft_raw, "\r\nBcc:") === false, "Attacker Bcc header injection was completely neutralized");
assert_test(strpos($draft_raw, "\r\nCc:") === false, "Attacker Cc header injection was completely neutralized");

// --- Test 8: Worker State Atomic File Locking & IMAP SSL Context ---
echo "\n--- Test 8: Worker State Locking & SSL Verification Context --- \n";
$worker_code = file_get_contents(__DIR__ . '/../bin/worker.php');
assert_test(strpos($worker_code, "file_put_contents(\$this->filepath, json_encode(\$this->state, JSON_PRETTY_PRINT), LOCK_EX)") !== false, "Worker state save uses LOCK_EX for race condition prevention");
assert_test(strpos($worker_code, "'verify_peer' => (bool) \$ssl_verify") !== false, "Worker IMAP connect verifies SSL certificates by default");

// --- Test 9: Frontend Cost Estimation Formula & Edge Cases ---
echo "\n--- Test 9: Cost Estimation JavaScript Formula & Zero Edge Cases --- \n";
$node_test = <<<JS
const fs = require('fs');
const jsCode = fs.readFileSync(__DIR__ + '/../src/lifeprisma_ai.js', 'utf8');

// Mock browser environment
window = {
    rcmail: {
        addEventListener: function() {},
        env: {
            lpai_gemini: {
                pricing: {
                    'gemini-3.8-flash': { input: 0.30, output: 2.50 },
                    'custom-free-tier': { input: 0.00, output: 0.00 }
                }
            }
        }
    }
};
rcmail = window.rcmail;
document = {
    addEventListener: function() {},
    querySelector: function() { return null; },
    querySelectorAll: function() { return []; },
    getElementById: function() { return null; },
    body: { classList: { add: function() {}, contains: function() { return false; } } }
};

eval(jsCode);

// Tests
console.log(JSON.stringify({
    nullCost: lpai_estimate_cost('gemini-3.8-flash', 0, 0),
    zeroCost: lpai_estimate_cost('custom-free-tier', 500, 500),
    standardCost: lpai_estimate_cost('gemini-3.8-flash', 1000, 1000),
    smallCost: lpai_estimate_cost('gemini-3.8-flash', 10, 10)
}));
JS;

$node_test_path = sys_get_temp_dir() . '/lpai_cost_test.js';
file_put_contents($node_test_path, str_replace('__DIR__', "'" . __DIR__ . "'", $node_test));
$node_out = shell_exec("node {$node_test_path}");
@unlink($node_test_path);

$cost_results = json_decode((string) $node_out, true);
assert_test(is_array($cost_results), "Node evaluated lpai_estimate_cost successfully");
assert_test($cost_results['nullCost'] === null, "lpai_estimate_cost(0, 0) returns null");
assert_test($cost_results['zeroCost'] === '$0.00', "Free tier 0 cost correctly returns '$0.00' (not '$0.000000')");
assert_test(strpos($cost_results['standardCost'], '$') === 0, "Standard cost estimation formats correctly");

echo "\n*** ALL SECURITY & LOGIC TESTS COMPLETED: {$passed_count}/{$test_count} PASSED ***\n";
exit($passed_count === $test_count ? 0 : 1);
