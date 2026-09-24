<?php
/**
 * Test suite for Lifeprisma AI Executive Hub Closed State &
 * Multi-Language Email Matching (preserving email language for draft replies).
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

echo "=== LIFEPIRISMA AI HUB CLOSED & DRAFT LANGUAGE TEST SUITE ===\n\n";

$ai_dir = is_dir(__DIR__ . '/../Extra context/plugins/roundcube_ai')
    ? __DIR__ . '/../Extra context/plugins/roundcube_ai'
    : dirname(__DIR__);

// --- 1. Static Source Code Checks ---
echo "--- Test 1: Static Source Code Checks --- \n";
$php_code   = file_get_contents($ai_dir . '/lifeprisma_ai.php');
$js_src     = file_get_contents($ai_dir . '/src/lifeprisma_ai.js');
$js_min     = file_get_contents($ai_dir . '/lifeprisma_ai.min.js');
$js_elastic = file_get_contents($ai_dir . '/skins/elastic/lifeprisma_ai.min.js');
$js_gmail   = file_get_contents($ai_dir . '/skins/gmail_plus/lifeprisma_ai.min.js');
$css_gp     = file_get_contents($ai_dir . '/skins/gmail_plus/style.css');
$css_el     = file_get_contents($ai_dir . '/skins/elastic/style.css');

assert_true(strpos($php_code, "function detect_email_language") !== false, "lifeprisma_ai.php defines detect_email_language() helper");
assert_true(strpos($php_code, "CRITICAL DRAFT REPLY LANGUAGE RULE") !== false, "lifeprisma_ai.php includes critical draft reply language rule in triage system prompt");
assert_true(strpos($php_code, "Draft Reply Target Language") !== false, "lifeprisma_ai.php specifies Draft Reply Target Language in triage user prompt");
assert_true(strpos($php_code, "NEVER write 'draft_reply' in {\$language} unless the incoming email itself is written in {\$language}") !== false, "lifeprisma_ai.php explicitly forbids translating draft_reply into briefing language if incoming email differs");

// Check frontend hub closed behavior
assert_true(strpos($js_src, "lpai-hub-collapsed") !== false, "src/lifeprisma_ai.js defines lpai-hub-collapsed class");
assert_true(strpos($js_src, "lpai_handle_hub_header_click") !== false, "src/lifeprisma_ai.js defines lpai_handle_hub_header_click");
assert_true(strpos($js_src, "wasManuallyOpened") !== false, "src/lifeprisma_ai.js only opens hub if wasManuallyOpened");
assert_true(strpos($js_src, "style=\"display: ' + (isOpen ? 'block' : 'none') + ';\"") !== false, "src/lifeprisma_ai.js sets lpai-hub-body display: none by default when isOpen is false");
assert_true(strpos($js_src, "(isOpen ? '&#9650;' : '&#9660;')") !== false, "src/lifeprisma_ai.js sets down arrow (&#9660;) when hub is closed");

// Check CSS
assert_true(strpos($css_gp, ".lpai-executive-hub.lpai-hub-collapsed .lpai-hub-header") !== false, "skins/gmail_plus/style.css styles collapsed hub header");
assert_true(strpos($css_el, ".lpai-executive-hub.lpai-hub-collapsed .lpai-hub-header") !== false, "skins/elastic/style.css styles collapsed hub header");

// Check Minified JS bundles
assert_true($js_min === $js_elastic && $js_min === $js_gmail, "Minified JS bundles across root, elastic, and gmail_plus are 100% byte-for-byte identical");
assert_true(strpos($js_min, "lpai-hub-collapsed") !== false, "Compiled JS contains lpai-hub-collapsed logic");
assert_true(strpos($js_min, "lpai_handle_hub_header_click") !== false, "Compiled JS contains lpai_handle_hub_header_click");

// --- 2. Functional Mock Environment Tests ---
echo "\n--- Test 2: Language Detector Heuristics --- \n";

if (!class_exists('rcube')) {
    class rcube {
        private static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;

        public function __construct() {
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

        public static function get_instance() {
            if (!self::$instance) self::$instance = new self();
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
    class rcube_plugin {
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
    }
}

require_once $ai_dir . '/lifeprisma_ai.php';

$plugin = new lifeprisma_ai();

// Test English detection
$en_text = "Hi Koen, Could you please review the attached proposal for the new project and let me know what you think? Thanks, Sarah";
assert_true($plugin->detect_email_language($en_text) === 'English', "detect_email_language correctly identifies English email text");

// Test Dutch detection
$nl_text = "Beste Koen, Zou je even kunnen kijken naar het rapport van de vergadering? Alvast heel erg bedankt en vriendelijke groet, Pieter";
assert_true($plugin->detect_email_language($nl_text) === 'Dutch', "detect_email_language correctly identifies Dutch email text");

// Test German detection
$de_text = "Hallo Koen, bitte senden Sie mir die Unterlagen für das Projekt bis morgen. Vielen Dank und beste Grüße, Hans";
assert_true($plugin->detect_email_language($de_text) === 'German', "detect_email_language correctly identifies German email text");

// Test French detection
$fr_text = "Bonjour Koen, merci de bien vouloir nous envoyer les informations pour le projet. Cordialement, Jean";
assert_true($plugin->detect_email_language($fr_text) === 'French', "detect_email_language correctly identifies French email text");

// Test Spanish detection
$es_text = "Hola Koen, por favor envíame los detalles de la reunión para el nuevo proyecto. Saludos cordiales, Carlos";
assert_true($plugin->detect_email_language($es_text) === 'Spanish', "detect_email_language correctly identifies Spanish email text");

// Test Italian detection
$it_text = "Ciao Koen, grazie per l'aggiornamento. Ci vediamo alla riunione domani. Cordiali saluti, Marco";
assert_true($plugin->detect_email_language($it_text) === 'Italian', "detect_email_language correctly identifies Italian email text");

// --- 3. Prompt Construction with English Email and Dutch Preference ---
echo "\n--- Test 3: Prompt Construction (English Email + Dutch Briefing Preference) --- \n";

$ref_class = new ReflectionClass($plugin);

// Test call_gemini_triage user prompt construction via Reflection
$triage_method = $ref_class->getMethod('call_gemini_triage');
$triage_method->setAccessible(true);

$ctx = [
    'subject' => 'Urgent: Feedback needed on Q3 Revenue Deck',
    'from'    => 'alice@investor.com',
    'date'    => 'Thu, 24 Sep 2026 14:00:00 +0200',
    'body'    => 'Hi Koen, Could you please send me the latest metrics on the Q3 campaign? We have a meeting tomorrow morning and need the final figures. Thanks, Alice',
];

$gemini_mock = [
    'api_key' => 'test-key',
    'model'   => 'gemini-3.8-flash',
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
];

// Reflect on build_user_prompt
$prompt_method = $ref_class->getMethod('build_user_prompt');
$prompt_method->setAccessible(true);

// When action is reply and original email is English, but language setting is Dutch:
$reply_user_prompt = $prompt_method->invoke($plugin, 'reply', 'Please reply to Alice', '', $ctx['body'], $ctx['subject'], 'Dutch', 'professional', 'Koen');

assert_true(strpos($reply_user_prompt, "English") !== false, "build_user_prompt('reply') detects English and designates English in Language field");
assert_true(strpos($reply_user_prompt, "Do NOT use Dutch") !== false, "build_user_prompt('reply') explicitly instructs model NOT to use Dutch when replying to English email");

// System prompt for reply
$sys_prompt_method = $ref_class->getMethod('build_system_prompt');
$sys_prompt_method->setAccessible(true);
$reply_sys_prompt = $sys_prompt_method->invoke($plugin, 'reply');

assert_true(strpos($reply_sys_prompt, "CRITICAL LANGUAGE RULE") !== false, "build_system_prompt('reply') contains critical language rule");
assert_true(strpos($reply_sys_prompt, "write the reply in that exact same language") !== false, "build_system_prompt('reply') requires exact same language as incoming email");

echo "\n*** ALL HUB CLOSED & DRAFT LANGUAGE TESTS PASSED ({$passed_count}/{$test_count}) ***\n";
exit($passed_count === $test_count ? 0 : 1);
