<?php

/**
 * Automated Test Suite for Easy Unsubscribe Roundcube Plugin
 * Tests header parsing, RFC 8058 One-Click detection, RFC 2369 Mailto extraction,
 * SSRF security filtering, message sending logic, and client-side assets.
 */

$test_count = 0;
$passed_count = 0;

function assert_true($cond, $desc) {
    global $test_count, $passed_count;
    $test_count++;
    if ($cond) {
        $passed_count++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

function assert_equals($expected, $actual, $desc) {
    assert_true($expected === $actual, "{$desc} (Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true) . ")");
}

echo "====================================================\n";
echo "=== EASY UNSUBSCRIBE PLUGIN - COMPREHENSIVE TESTS ===\n";
echo "====================================================\n\n";

// --- Mock Roundcube Environment ---
if (!class_exists('rcube')) {
    class rcube {
        public static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;
        public $task = 'mail';
        public $action = 'show';
        public $delivered_messages = [];

        public function __construct() {
            $this->config = new rcube_config_mock();
            $this->output = new rcmail_output_mock();
            $this->storage = new rcube_storage_mock();
            $this->user = new rcube_user_mock();
        }

        public static function get_instance() {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_storage() {
            return $this->storage;
        }

        public function deliver_message($raw_msg, $from, $to, &$error = null) {
            $this->delivered_messages[] = [
                'raw' => $raw_msg,
                'from' => $from,
                'to' => $to,
            ];
            return true;
        }

        public static function write_log($name, $msg) {}
    }

    class rcmail extends rcube {}

    class rcube_config_mock {
        public $data = [
            'skin' => 'elastic',
            'easy_unsubscribe_prefer_oneclick' => true,
            'easy_unsubscribe_allow_http_oneclick' => false,
            'easy_unsubscribe_timeout' => 15,
            'easy_unsubscribe_ssrf_protection' => true,
            'easy_unsubscribe_persist_state' => true,
        ];

        public function get($key, $default = null) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
        }

        public function set($key, $val) {
            $this->data[$key] = $val;
        }
    }

    class rcmail_output_mock {
        public $env = [];
        public $messages = [];
        public $commands = [];

        public function set_env($key, $val) {
            $this->env[$key] = $val;
        }

        public function show_message($msg, $type = 'notice') {
            $this->messages[] = ['msg' => $msg, 'type' => $type];
        }

        public function command($cmd, $data = null) {
            $this->commands[] = ['cmd' => $cmd, 'data' => $data];
        }

        public function send() {}
    }

    class rcube_storage_mock {
        public $messages = [];

        public function get_message($uid, $mbox) {
            return $this->messages[$mbox][$uid] ?? null;
        }
    }

    class rcube_user_mock {
        public $prefs = [];

        public function get_identity($id = null) {
            return [
                'identity_id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ];
        }

        public function list_identities() {
            return [$this->get_identity()];
        }

        public function save_prefs($prefs) {
            $this->prefs = array_merge($this->prefs, $prefs);
            return true;
        }
    }

    class rcube_plugin {
        public $home;
        public $api;

        public function __construct($api = null) {
            $this->api = $api;
            $this->home = is_dir(dirname(__DIR__) . '/Extra context/plugins/easy_unsubscribe')
                ? dirname(__DIR__) . '/Extra context/plugins/easy_unsubscribe'
                : dirname(__DIR__) . '/easy_unsubscribe';
        }

        public function load_config($fname = 'config.inc.php') {}
        public function add_hook($hook, $handler) {}
        public function register_action($action, $handler) {}
        public function add_texts($dir, $client = false) {}
        public function include_script($script) {}
        public function include_stylesheet($css) {}
        public function local_skin_path() { return 'skins/elastic'; }
        public function gettext($key) {
            global $labels;
            return $labels[$key] ?? $key;
        }
    }

    class rcube_utils {
        const INPUT_POST = 1;
        const INPUT_GET = 2;
        public static $mock_inputs = [];

        public static function get_input_value($name, $mode, $allow_html = false) {
            return self::$mock_inputs[$name] ?? null;
        }
    }

    class rcube_message {
        public $uid;
        public $folder;
        public $headers;

        public function __construct($uid = 1, $folder = 'INBOX') {
            $this->uid = $uid;
            $this->folder = $folder;
            $this->headers = new stdClass();
            $this->headers->others = [];
        }

        public function get_header($name) {
            $lower = strtolower($name);
            return $this->headers->others[$lower] ?? null;
        }
    }
}

$plugin_dir = is_dir(__DIR__ . '/../Extra context/plugins/easy_unsubscribe')
    ? __DIR__ . '/../Extra context/plugins/easy_unsubscribe'
    : __DIR__ . '/../easy_unsubscribe';

// Load localization labels
require_once $plugin_dir . '/localization/en_US.inc';

// Load main plugin class
require_once $plugin_dir . '/easy_unsubscribe.php';

$plugin = new easy_unsubscribe();
$rcmail = rcmail::get_instance();

// ----------------------------------------------------
// TEST SUITE 1: RFC 2369 & RFC 8058 Header Parsing
// ----------------------------------------------------
echo "--- Test Suite 1: Header Parsing & Protocol Detection ---\n";

// Case 1.1: RFC 8058 One-Click POST + Mailto (Priority Check)
$unsub_header = '<https://newsletter.example.com/unsubscribe?token=abc123xyz>, <mailto:unsub@newsletter.example.com?subject=unsubscribe>';
$post_header = 'List-Unsubscribe=One-Click';
$sender_info = ['display_name' => 'Daily Tech Briefing', 'email' => 'news@newsletter.example.com'];

$parsed = $plugin->parse_unsubscribe_headers($unsub_header, $post_header, $sender_info, 101, 'INBOX');

assert_true($parsed !== null, "Parsed RFC 8058 message header successfully");
assert_equals('one_click', $parsed['type'], "Priority is RFC 8058 One-Click POST when List-Unsubscribe-Post is present");
assert_equals('https://newsletter.example.com/unsubscribe?token=abc123xyz', $parsed['url'], "Extracted HTTPS One-Click URL");
assert_true(!empty($parsed['mailto']), "Retained Mailto URI information as alternative");
assert_equals('unsub@newsletter.example.com', $parsed['mailto']['email'], "Extracted Mailto destination email");
assert_equals('unsubscribe', $parsed['mailto']['subject'], "Extracted Mailto subject query parameter");
assert_equals(true, $parsed['is_rfc8058'], "Flagged is_rfc8058 as true");

// Case 1.2: Case-insensitive List-Unsubscribe-Post
$post_header_ci = "  list-unsubscribe=one-click  \r\n";
$parsed_ci = $plugin->parse_unsubscribe_headers($unsub_header, $post_header_ci, $sender_info, 102, 'INBOX');
assert_equals('one_click', $parsed_ci['type'], "Handles whitespace and case variations in List-Unsubscribe-Post");

// Case 1.3: Mailto only (RFC 2369)
$unsub_mailto_only = '<mailto:optout@service.org?subject=remove&body=please_unsubscribe>';
$parsed_mailto = $plugin->parse_unsubscribe_headers($unsub_mailto_only, null, $sender_info, 103, 'INBOX');
assert_equals('mailto', $parsed_mailto['type'], "Resolved type is mailto when no RFC 8058 header is present");
assert_equals('optout@service.org', $parsed_mailto['mailto']['email'], "Parsed mailto address");
assert_equals('remove', $parsed_mailto['mailto']['subject'], "Parsed mailto subject");
assert_equals('please_unsubscribe', $parsed_mailto['mailto']['body'], "Parsed mailto body");

// Case 1.4: Web landing page only (HTTP/HTTPS)
$unsub_web_only = '<https://company.com/preferences/manage>';
$parsed_web = $plugin->parse_unsubscribe_headers($unsub_web_only, null, $sender_info, 104, 'INBOX');
assert_equals('http', $parsed_web['type'], "Resolved type is http for standard web URL without RFC 8058 POST");
assert_equals('https://company.com/preferences/manage', $parsed_web['url'], "Extracted web landing page URL");

// Case 1.5: Missing or invalid headers
$unsub_empty = '';
$parsed_empty = $plugin->parse_unsubscribe_headers($unsub_empty, null, $sender_info, 105, 'INBOX');
assert_true($parsed_empty === null, "Returns null when List-Unsubscribe header is empty");

$unsub_invalid = 'Not a bracketed url';
$parsed_invalid = $plugin->parse_unsubscribe_headers($unsub_invalid, null, $sender_info, 106, 'INBOX');
assert_true($parsed_invalid === null, "Returns null when List-Unsubscribe contains no valid angle-bracketed URIs");


// ----------------------------------------------------
// TEST SUITE 2: Sender & List-ID Identification
// ----------------------------------------------------
echo "\n--- Test Suite 2: Sender & List-ID Extraction ---\n";

$from_clean = '"The Morning Brew" <crew@morningbrew.com>';
$list_id = '"Morning Brew Newsletter" <list.morningbrew.com>';
$info = $plugin->extract_sender_info($from_clean, $list_id);

assert_equals('Morning Brew Newsletter', $info['display_name'], "Prioritizes List-ID name when available");
assert_equals('crew@morningbrew.com', $info['email'], "Extracted clean sender email");

$from_simple = 'support@service.com';
$info_simple = $plugin->extract_sender_info($from_simple, null);
assert_equals('support@service.com', $info_simple['display_name'], "Falls back to raw email if no display name or List-ID");


// ----------------------------------------------------
// TEST SUITE 3: SSRF Protection & URL Sanitization
// ----------------------------------------------------
echo "\n--- Test Suite 3: SSRF Protection & URL Sanitization ---\n";

// Public URLs should be permitted
assert_true($plugin->is_safe_url('https://example.com/unsub'), "Public HTTPS URL is allowed");
assert_true($plugin->is_safe_url('http://example.org/optout'), "Public HTTP URL is allowed");

// Dangerous/Private IP ranges must be blocked
assert_true(!$plugin->is_safe_url('http://127.0.0.1/admin'), "Loopback IPv4 127.0.0.1 is blocked");
assert_true(!$plugin->is_safe_url('http://localhost:8080/unsub'), "Hostname localhost is blocked");
assert_true(!$plugin->is_safe_url('http://10.0.0.5/api'), "Private IPv4 10.x.x.x is blocked");
assert_true(!$plugin->is_safe_url('http://192.168.1.1/config'), "Private IPv4 192.168.x.x is blocked");
assert_true(!$plugin->is_safe_url('http://172.16.0.10/status'), "Private IPv4 172.16.x.x is blocked");
assert_true(!$plugin->is_safe_url('http://169.254.169.254/latest/meta-data/'), "AWS/GCP Cloud Metadata IP 169.254.169.254 is blocked");

// Invalid or non-http protocols must be blocked
assert_true(!$plugin->is_safe_url('file:///etc/passwd'), "file:// scheme is blocked");
assert_true(!$plugin->is_safe_url('gopher://evil.com/'), "gopher:// scheme is blocked");
assert_true(!$plugin->is_safe_url('javascript:alert(1)'), "javascript: scheme is blocked");
assert_true(!$plugin->is_safe_url(''), "Empty URL is blocked");


// ----------------------------------------------------
// TEST SUITE 4: Storage Init Hook (IMAP fetch_headers)
// ----------------------------------------------------
echo "\n--- Test Suite 4: Storage Init Hook ---\n";

$p = ['fetch_headers' => 'FROM TO SUBJECT DATE'];
$p_out = $plugin->storage_init($p);
assert_true(strpos($p_out['fetch_headers'], 'LIST-UNSUBSCRIBE') !== false, "storage_init adds LIST-UNSUBSCRIBE to fetch_headers");
assert_true(strpos($p_out['fetch_headers'], 'LIST-UNSUBSCRIBE-POST') !== false, "storage_init adds LIST-UNSUBSCRIBE-POST to fetch_headers");
assert_true(strpos($p_out['fetch_headers'], 'LIST-ID') !== false, "storage_init adds LIST-ID to fetch_headers");


// ----------------------------------------------------
// TEST SUITE 5: Message Load Hook & Status Check
// ----------------------------------------------------
echo "\n--- Test Suite 5: Message Load Hook & State Tracking ---\n";

$msg = new rcube_message(501, 'INBOX');
$msg->headers->others['list-unsubscribe'] = '<https://example.com/unsub>, <mailto:unsub@example.com>';
$msg->headers->others['list-unsubscribe-post'] = 'List-Unsubscribe=One-Click';
$msg->headers->from = 'Updates <updates@example.com>';

$args = ['object' => $msg];
$plugin->message_load($args);

// Verify env was set in template_object_messageheaders
$p_head = ['content' => '<div class="headers"></div>'];
$plugin->template_object_messageheaders($p_head);
$env_data = $rcmail->output->env['easy_unsubscribe_data'] ?? null;

assert_true(!empty($env_data), "message_load correctly populated easy_unsubscribe_data into rcmail environment");
assert_equals('one_click', $env_data['type'], "Environment contains correct one_click type");
assert_equals('active', $env_data['status'], "Status is active initially");

// Mark message as unsubscribed and verify status changes
$plugin->mark_as_unsubscribed(501, 'INBOX');
assert_true($plugin->is_already_unsubscribed(501, 'INBOX'), "is_already_unsubscribed returns true after marking");

$plugin->message_load($args);
$plugin->template_object_messageheaders($p_head);
$env_data_updated = $rcmail->output->env['easy_unsubscribe_data'] ?? null;
assert_equals('unsubscribed', $env_data_updated['status'], "Status updates to 'unsubscribed' for previously unsubscribed message");


// ----------------------------------------------------
// TEST SUITE 6: Automated Mailto Dispatch
// ----------------------------------------------------
echo "\n--- Test Suite 6: Automated Mailto Dispatch ---\n";

$mailto_target = [
    'email' => 'leave-list@example.com',
    'subject' => 'Unsubscribe Request',
    'body' => 'Please remove me from this list',
];

$res = $plugin->execute_mailto_send($mailto_target, 'Test List');
assert_true($res['success'], "execute_mailto_send completed successfully via Roundcube deliver_message API");
assert_true(count($rcmail->delivered_messages) > 0, "A message was delivered to internal mail transport");
$sent_record = end($rcmail->delivered_messages);
assert_equals('leave-list@example.com', $sent_record['to'], "Delivered message recipient matches mailto target");
assert_equals('john.doe@example.com', $sent_record['from'], "Delivered message sender matches active user identity");
assert_true(strpos($sent_record['raw'], 'Subject: Unsubscribe Request') !== false, "Message contains specified subject");
assert_true(strpos($sent_record['raw'], 'Please remove me from this list') !== false, "Message contains specified body");


// ----------------------------------------------------
// TEST SUITE 7: Asset & File Integrity Verification
// ----------------------------------------------------
echo "\n--- Test Suite 7: Assets & Delivery Files Integrity ---\n";

// 7.1 composer.json validation
$composer_file = $plugin_dir . '/composer.json';
assert_true(file_exists($composer_file), "easy_unsubscribe/composer.json exists");
$composer_json = json_decode(file_get_contents($composer_file), true);
assert_true(json_last_error() === JSON_ERROR_NONE, "composer.json is valid JSON");
assert_equals('webdotpulse/easy_unsubscribe', $composer_json['name'], "composer.json specifies correct package name");
assert_equals('roundcube-plugin', $composer_json['type'], "composer.json specifies roundcube-plugin type");

// 7.2 localization/en_US.inc validation
$loc_file = $plugin_dir . '/localization/en_US.inc';
assert_true(file_exists($loc_file), "easy_unsubscribe/localization/en_US.inc exists");
$required_keys = ['unsubscribe', 'unsubscribing', 'unsubscribed', 'modal_title', 'btn_unsubscribe', 'btn_visit_website', 'success_oneclick', 'error_failed'];
foreach ($required_keys as $k) {
    assert_true(isset($labels[$k]), "localization file contains required key: '{$k}'");
}

// 7.3 easy_unsubscribe.js validation
$js_file = $plugin_dir . '/easy_unsubscribe.js';
assert_true(file_exists($js_file), "easy_unsubscribe/easy_unsubscribe.js exists");
$js_content = file_get_contents($js_file);
assert_true(strpos($js_content, 'rcmail.addEventListener(\'plugin.easy_unsubscribe_result\'') !== false, "easy_unsubscribe.js listens to plugin.easy_unsubscribe_result");
assert_true(strpos($js_content, 'rcmail.http_post(\'plugin.easy_unsubscribe\'') !== false, "easy_unsubscribe.js calls plugin.easy_unsubscribe action");
assert_true(strpos($js_content, 'header-from') !== false, "easy_unsubscribe.js contains Elastic skin selectors");
assert_true(strpos($js_content, '#messageheader td.from') !== false, "easy_unsubscribe.js contains Larry skin selectors");
assert_true(strpos($js_content, 'openConfirmModal') !== false, "easy_unsubscribe.js implements openConfirmModal");
assert_true(strpos($js_content, 'transformButtonToBadge') !== false, "easy_unsubscribe.js implements transformButtonToBadge");

// 7.4 skins/elastic/easy_unsubscribe.css validation
$elastic_css = $plugin_dir . '/skins/elastic/easy_unsubscribe.css';
assert_true(file_exists($elastic_css), "easy_unsubscribe/skins/elastic/easy_unsubscribe.css exists");
$elastic_css_content = file_get_contents($elastic_css);
assert_true(strpos($elastic_css_content, '.easy-unsubscribe-btn') !== false, "Elastic CSS defines .easy-unsubscribe-btn");
assert_true(strpos($elastic_css_content, '.easy-unsubscribe-badge.unsubscribed') !== false, "Elastic CSS defines .easy-unsubscribe-badge.unsubscribed");
assert_true(strpos($elastic_css_content, '.easy-unsubscribe-modal-overlay') !== false, "Elastic CSS defines .easy-unsubscribe-modal-overlay");
assert_true(strpos($elastic_css_content, 'prefers-color-scheme: dark') !== false, "Elastic CSS includes dark mode media query");
assert_true(strpos($elastic_css_content, 'html.dark-mode') !== false, "Elastic CSS includes html.dark-mode rules");

// 7.5 skins/larry/easy_unsubscribe.css validation
$larry_css = $plugin_dir . '/skins/larry/easy_unsubscribe.css';
assert_true(file_exists($larry_css), "easy_unsubscribe/skins/larry/easy_unsubscribe.css exists");
$larry_css_content = file_get_contents($larry_css);
assert_true(strpos($larry_css_content, '.easy-unsubscribe-btn') !== false, "Larry CSS defines .easy-unsubscribe-btn");

// 7.6 config.inc.php.dist validation
$config_dist = $plugin_dir . '/config.inc.php.dist';
assert_true(file_exists($config_dist), "easy_unsubscribe/config.inc.php.dist exists");

// 7.7 README.md validation
$readme = $plugin_dir . '/README.md';
assert_true(file_exists($readme), "easy_unsubscribe/README.md exists");


// --- Final Summary ---
echo "\n====================================================\n";
echo "Tests Passed: {$passed_count} / {$test_count}\n";
if ($passed_count === $test_count) {
    echo "STATUS: ALL TESTS PASSED! PRODUCTION READY.\n";
} else {
    echo "STATUS: FAILURES DETECTED.\n";
}
echo "====================================================\n";

exit($passed_count === $test_count ? 0 : 1);
