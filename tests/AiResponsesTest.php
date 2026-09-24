<?php
/**
 * Test suite for Gemini AI Assistant in Settings -> Responses ("Reacties").
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

echo "=== AI ASSISTANT IN SETTINGS -> RESPONSES TEST SUITE ===\n\n";

// Mock Roundcube environment
if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;
        public $action = 'responses';

        public function __construct()
        {
            $this->config = new class {
                private $data = ['skin' => 'elastic'];
                public function get($k, $d = null) { return $this->data[$k] ?? $d; }
                public function set($k, $v) { $this->data[$k] = $v; }
            };
            $this->output = new class {
                public $env = [];
                public $footer = '';
                public function set_env($k, $v) { $this->env[$k] = $v; }
                public function add_footer($html) { $this->footer .= $html; }
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
                public function query($sql, ...$params) {
                    return new class {
                        public function fetch_assoc() { return []; }
                    };
                }
                public function fetch_assoc($res) { return []; }
                public function now() { return 'NOW()'; }
            };
        }
    }

    class rcmail extends rcube {}

    class rcube_plugin
    {
        public $home;
        public function __construct() {
            $this->home = realpath(__DIR__ . '/..');
        }
        public function load_config() {}
        public function add_texts($d) {}
        public function register_action($a, $cb) {}
        public function add_hook($h, $cb) {}
        public function add_button($btn, $container) {}
        public function include_script($s) {}
        public function include_stylesheet($s) {}
        public function local_skin_path() { return 'skins/elastic'; }
    }

    class rcube_utils
    {
        const INPUT_GET = 1;
        const INPUT_POST = 2;
        const INPUT_GP = 3;
        public static function get_input_string($name, $source) { return ''; }
        public static function get_boolean($name, $source) { return false; }
    }
}

$ai_dir = is_dir(__DIR__ . '/../Extra context/plugins/roundcube_ai')
    ? __DIR__ . '/../Extra context/plugins/roundcube_ai'
    : dirname(__DIR__);

require_once $ai_dir . '/lifeprisma_ai.php';

// --- 1. Backend PHP: Template & Action Detection in render_page ---
echo "--- Test 1: PHP render_page() Detection for Responses --- \n";
$plugin = new lifeprisma_ai();
$ref_class = new ReflectionClass('lifeprisma_ai');

$render_method = $ref_class->getMethod('render_page');
$rcmail = rcmail::get_instance();

// Test with template 'responseedit' (the iframe form)
$rcmail->output->env = [];
$rcmail->output->footer = '';
$res = $render_method->invoke($plugin, ['template' => 'responseedit', 'content' => '<div>Form</div>']);
assert_true(isset($rcmail->output->env['lpai_gemini']), "render_page sets lpai_gemini on template 'responseedit'");
assert_true(isset($rcmail->output->env['lpai_skin']), "render_page sets lpai_skin on template 'responseedit'");
assert_true(strpos($rcmail->output->footer, 'id="lpai-panel"') !== false, "render_page adds lpai-panel modal footer on template 'responseedit'");

// Test with template 'responses' (the outer list view)
$rcmail->output->env = [];
$rcmail->output->footer = '';
$res2 = $render_method->invoke($plugin, ['template' => 'responses', 'content' => '<div>List</div>']);
assert_true(isset($rcmail->output->env['lpai_gemini']), "render_page sets lpai_gemini on template 'responses'");
assert_true(strpos($rcmail->output->footer, 'id="lpai-panel"') !== false, "render_page adds lpai-panel modal footer on template 'responses'");

// Test with action 'response-edit'
$rcmail->action = 'response-edit';
$rcmail->output->env = [];
$rcmail->output->footer = '';
$res3 = $render_method->invoke($plugin, ['template' => '', 'content' => '<div>Content</div>']);
assert_true(isset($rcmail->output->env['lpai_gemini']), "render_page sets lpai_gemini on action 'response-edit'");
assert_true(strpos($rcmail->output->footer, 'id="lpai-panel"') !== false, "render_page adds modal footer on action 'response-edit'");

// --- 2. Backend PHP: System & User Prompts for Responses ---
echo "\n--- Test 2: PHP System & User Prompt Builder --- \n";
$bsp_method = $ref_class->getMethod('build_system_prompt');
$bsp_method->setAccessible(true);

$prompt_name = $bsp_method->invoke($plugin, 'suggest_response_name');
assert_true(strpos($prompt_name, 'canned responses') !== false || strpos($prompt_name, 'titles') !== false, "build_system_prompt handles suggest_response_name");
assert_true(strpos($prompt_name, '2-4 word') !== false, "suggest_response_name asks for concise 2-4 word title");

$bup_method = $ref_class->getMethod('build_user_prompt');
$bup_method->setAccessible(true);
$user_prompt = $bup_method->invoke($plugin, 'suggest_response_name', 'Generate title', 'Thank you for your order.', '', 'Confirmation', 'Dutch', 'professional', 'User');
assert_true(strpos($user_prompt, 'Subject/Title: Confirmation') !== false, "build_user_prompt includes Subject/Title");
assert_true(strpos($user_prompt, 'Current Text/Template:') !== false, "build_user_prompt formats Current Text/Template");

// --- 3. Frontend JavaScript: Source Code Verification ---
echo "\n--- Test 3: JavaScript Frontend Source Verification --- \n";
$js_src = file_get_contents($ai_dir . '/src/lifeprisma_ai.js');
$js_min = file_get_contents($ai_dir . '/lifeprisma_ai.min.js');
$js_elastic = file_get_contents($ai_dir . '/skins/elastic/lifeprisma_ai.min.js');
$js_gmail = file_get_contents($ai_dir . '/skins/gmail_plus/lifeprisma_ai.min.js');

assert_true(strpos($js_src, 'lpai_init_responses()') !== false, "src/lifeprisma_ai.js calls lpai_init_responses in settings task");
assert_true(strpos($js_src, 'function lpai_init_responses()') !== false, "src/lifeprisma_ai.js defines lpai_init_responses");
assert_true(strpos($js_src, 'function lpai_add_response_quick_actions(') !== false, "src/lifeprisma_ai.js defines lpai_add_response_quick_actions");
assert_true(strpos($js_src, 'function lpai_response_quick(') !== false, "src/lifeprisma_ai.js defines lpai_response_quick");
assert_true(strpos($js_src, 'function lpai_suggest_response_name(') !== false, "src/lifeprisma_ai.js defines lpai_suggest_response_name");
assert_true(strpos($js_src, 'function lpai_get_response_name()') !== false, "src/lifeprisma_ai.js defines lpai_get_response_name");
assert_true(strpos($js_src, 'fftext') !== false, "src/lifeprisma_ai.js targets fftext response editor");
assert_true(strpos($js_src, 'ffname') !== false, "src/lifeprisma_ai.js targets ffname response title input");
assert_true(strpos($js_src, 'lpai-qa-bar-response') !== false, "src/lifeprisma_ai.js creates lpai-qa-bar-response element");
assert_true(strpos($js_src, 'Insert into Response') !== false, "src/lifeprisma_ai.js customizes modal apply button for responses");

// Bundle synchronization
assert_true(strpos($js_min, 'lpai_init_responses') !== false, "lifeprisma_ai.min.js contains compiled lpai_init_responses");
assert_true(strpos($js_min, 'lpai_add_response_quick_actions') !== false, "lifeprisma_ai.min.js contains compiled lpai_add_response_quick_actions");
assert_true($js_min === $js_elastic && $js_min === $js_gmail, "Root and skin minified JS bundles are 100% in sync");

// --- 4. CSS Verification across Skins ---
echo "\n--- Test 4: CSS Verification in Skins --- \n";
$el_css = file_get_contents($ai_dir . '/skins/elastic/style.css');
$el_min_css = file_get_contents($ai_dir . '/skins/elastic/style.min.css');
$gp_css = file_get_contents($ai_dir . '/skins/gmail_plus/style.css');
$gp_min_css = file_get_contents($ai_dir . '/skins/gmail_plus/style.min.css');

assert_true(strpos($el_css, '.lpai-qa-bar-response') !== false, "elastic style.css defines .lpai-qa-bar-response");
assert_true(strpos($el_min_css, '.lpai-qa-bar-response') !== false, "elastic style.min.css includes .lpai-qa-bar-response");
assert_true(strpos($gp_css, '.lpai-qa-bar-response') !== false, "gmail_plus style.css defines .lpai-qa-bar-response");
assert_true(strpos($gp_min_css, '.lpai-qa-bar-response') !== false, "gmail_plus style.min.css includes .lpai-qa-bar-response");

// --- 5. Headless Chrome Browser Integration Test ---
echo "\n--- Test 5: Headless Chrome Browser Integration Test --- \n";

$test_html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="file://' . realpath($ai_dir . '/skins/elastic/style.css') . '">
<script>
window.rcmail = {
    env: { task: "settings", action: "response-edit", request_token: "test_token" },
    addEventListener: function(evt, cb) {
        if (evt === "init") {
            window.__init_cb = cb;
        }
    },
    url: function(a) { return a; },
    display_message: function(m, t) { window.__last_msg = m; }
};
</script>
<script src="file://' . realpath($ai_dir . '/src/lifeprisma_ai.js') . '"></script>
</head>
<body>
<div id="layout-content">
    <form id="responseform">
        <table class="propform">
            <tr>
                <th><label for="ffname">Name</label></th>
                <td><input type="text" id="ffname" name="_name" value=""></td>
            </tr>
            <tr>
                <th><label for="fftext">Response text</label></th>
                <td>
                    <textarea id="fftext" name="_text" rows="10" cols="60">We hebben uw bericht ontvangen en reageren zo spoedig mogelijk.</textarea>
                </td>
            </tr>
        </table>
    </form>
</div>

<!-- Modal panel injected into footer -->
<div id="lpai-overlay" style="display:none"></div>
<div id="lpai-panel" style="display:none">
    <div id="lpai-header">
        <div class="lpai-title-wrapper">
            <span class="lpai-gemini-sparkle"></span>
            <span id="lpai-title">Gemini Assistant</span>
            <span class="lpai-model-tag">gemini-3.8-flash</span>
        </div>
        <button type="button" id="lpai-close">&times;</button>
    </div>
    <div id="lpai-body">
        <div id="lpai-actions">
            <button type="button" class="lpai-action-btn active" data-action="compose"><span>Compose</span></button>
            <button type="button" class="lpai-action-btn" data-action="rewrite"><span>Rewrite</span></button>
            <button type="button" class="lpai-action-btn" data-action="suggest_subject"><span>Subject Lines</span></button>
        </div>
        <div id="lpai-controls">
            <select id="lpai-model-select" class="lpai-select"><option value="gemini-3.8-flash">gemini-3.8-flash</option></select>
            <select id="lpai-tone-select" class="lpai-select"><option value="professional">Professional</option></select>
            <select id="lpai-lang-select" class="lpai-select"><option value="Dutch">Dutch</option></select>
        </div>
        <div id="lpai-input-wrapper">
            <textarea id="lpai-input"></textarea>
        </div>
        <div id="lpai-preview" style="display:none">
            <div id="lpai-preview-content"></div>
        </div>
    </div>
    <div id="lpai-footer">
        <button type="button" id="lpai-apply" class="lpai-btn-apply" style="display:none">Insert into Email</button>
        <button type="button" id="lpai-generate" class="lpai-btn-generate"><span>Generate</span></button>
    </div>
</div>

<script>
window.onload = function() {
    if (window.__init_cb) window.__init_cb();

    var bar = document.getElementById("lpai-qa-bar-response");
    var btnCount = bar ? bar.querySelectorAll(".lpai-qa-btn").length : 0;
    console.log("CHROME_BAR_EXISTS:" + (!!bar && btnCount >= 4 ? "yes" : "no"));

    lpai_open_panel("response");
    var panel = document.getElementById("lpai-panel");
    console.log("CHROME_MODAL_VISIBLE:" + (panel && panel.style.display === "flex" ? "yes" : "no"));

    var titleEl = document.getElementById("lpai-title");
    console.log("CHROME_MODAL_TITLE:" + (titleEl ? titleEl.textContent : ""));

    var applyBtn = document.getElementById("lpai-apply");
    console.log("CHROME_APPLY_BTN:" + (applyBtn ? applyBtn.textContent : ""));

    var initContent = lpai_get_editor_content();
    console.log("CHROME_INITIAL_CONTENT:" + initContent.substring(0, 30));

    lpai_apply_with_preserve("Dit is een door Gemini gegenereerde reactie voor de klant.");
    var newContent = document.getElementById("fftext").value;
    console.log("CHROME_NEW_CONTENT:" + newContent.substring(0, 30));

    var autoTitle = document.getElementById("ffname").value;
    console.log("CHROME_AUTO_TITLE:" + autoTitle);
};
</script>
</body>
</html>';

$tmp_html_file = '/tmp/lpai_response_browser_test.html';
file_put_contents($tmp_html_file, $test_html);

$cmd = 'google-chrome-stable --headless --disable-gpu --no-sandbox --window-size=1280,800 --run-all-compositor-stages-before-draw --virtual-time-budget=2000 --enable-logging=stderr ' . escapeshellarg($tmp_html_file) . ' 2>&1';
$output = shell_exec($cmd);
@unlink($tmp_html_file);

$bar_exists = false;
$modal_visible = false;
$modal_title = '';
$apply_btn = '';
$initial_content = '';
$new_content = '';
$auto_title = '';

if ($output) {
    foreach (explode("\n", $output) as $line) {
        if (preg_match('/CHROME_BAR_EXISTS:(.*?)"/', $line, $m)) $bar_exists = (trim($m[1]) === 'yes');
        if (preg_match('/CHROME_MODAL_VISIBLE:(.*?)"/', $line, $m)) $modal_visible = (trim($m[1]) === 'yes');
        if (preg_match('/CHROME_MODAL_TITLE:(.*?)"/', $line, $m)) $modal_title = trim($m[1]);
        if (preg_match('/CHROME_APPLY_BTN:(.*?)"/', $line, $m)) $apply_btn = trim($m[1]);
        if (preg_match('/CHROME_INITIAL_CONTENT:(.*?)"/', $line, $m)) $initial_content = trim($m[1]);
        if (preg_match('/CHROME_NEW_CONTENT:(.*?)"/', $line, $m)) $new_content = trim($m[1]);
        if (preg_match('/CHROME_AUTO_TITLE:(.*?)"/', $line, $m)) $auto_title = trim($m[1]);
    }
}

assert_true($bar_exists, "Browser: #lpai-qa-bar-response attached with all action buttons");
assert_true($modal_visible, "Browser: Assistant panel opens as flex modal");
assert_true($modal_title === 'Gemini Response Assistant', "Browser: Modal title adapts to 'Gemini Response Assistant'");
assert_true($apply_btn === 'Insert into Response', "Browser: Apply button adapts to 'Insert into Response'");
assert_true(strpos($initial_content, 'We hebben uw bericht') !== false, "Browser: Correctly extracted existing fftext content");
assert_true(strpos($new_content, 'Dit is een door Gemini') !== false, "Browser: Inserted new response text into fftext");
assert_true(!empty($auto_title), "Browser: Auto-suggested response name for #ffname when empty (got: {$auto_title})");

echo "\n============================================\n";
echo "TEST RESULTS: {$passed_count} / {$test_count} tests passed\n";
if ($passed_count === $test_count) {
    echo "*** ALL SETTINGS -> RESPONSES TESTS PASSED (100%) ***\n";
    exit(0);
} else {
    echo "!!! SOME TESTS FAILED !!!\n";
    exit(1);
}
