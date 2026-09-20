<?php
/**
 * Test suite for AI Modal CSS (#lpai-panel 800px) and Dropdown Selects functionality.
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

echo "=== AI MODAL CSS & DROPDOWN SELECTS TEST SUITE ===\n\n";

// --- 1. CSS Verification in gmail_plus skin ---
echo "--- Test 1: gmail_plus Skin CSS --- \n";
$gp_css = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.css');
$gp_min_css = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.min.css');

assert_true(strpos($gp_css, 'width: 800px !important;') !== false, "gmail_plus style.css defines width: 800px !important for #lpai-panel");
assert_true(strpos($gp_min_css, 'width:800px!important') !== false || strpos($gp_min_css, 'width: 800px !important') !== false, "gmail_plus style.min.css includes width 800px for #lpai-panel");
assert_true(strpos($gp_css, 'z-index: 10000 !important;') !== false, "gmail_plus #lpai-panel has z-index: 10000");
assert_true(strpos($gp_css, 'pointer-events: auto !important;') !== false, "gmail_plus selects have pointer-events: auto !important");
assert_true(strpos($gp_css, 'z-index: 10 !important;') !== false, "gmail_plus selects have z-index: 10 !important");
assert_true(strpos($gp_css, 'select.lpai-select option') !== false, "gmail_plus styles option elements for light mode");
assert_true(strpos($gp_css, 'html.dark-mode select.lpai-select option') !== false, "gmail_plus styles option elements for dark mode");

// --- 2. CSS Verification in elastic skin ---
echo "\n--- Test 2: elastic Skin CSS --- \n";
$el_css = file_get_contents(__DIR__ . '/../skins/elastic/style.css');
$el_min_css = file_get_contents(__DIR__ . '/../skins/elastic/style.min.css');

assert_true(strpos($el_css, 'width: 800px !important;') !== false, "elastic style.css defines width: 800px !important for #lpai-panel");
assert_true(strpos($el_min_css, 'width:800px!important') !== false || strpos($el_min_css, 'width: 800px !important') !== false, "elastic style.min.css includes width 800px for #lpai-panel");
assert_true(strpos($el_css, 'pointer-events: auto !important;') !== false, "elastic selects have pointer-events: auto !important");
assert_true(strpos($el_css, 'select.lpai-select option') !== false, "elastic styles option elements for light mode");
assert_true(strpos($el_css, 'html.dark-mode select.lpai-select option') !== false, "elastic styles option elements for dark mode");

// --- 3. JavaScript Modal and Selects Logic ---
echo "\n--- Test 3: JavaScript Modal & Select Logic --- \n";
$js_src = file_get_contents(__DIR__ . '/../src/lifeprisma_ai.js');
$js_min = file_get_contents(__DIR__ . '/../lifeprisma_ai.min.js');

assert_true(strpos($js_src, 'lpai_get_modal_doc') !== false, "src/lifeprisma_ai.js defines lpai_get_modal_doc");
assert_true(strpos($js_src, 'lpai_sync_select_elements') !== false, "src/lifeprisma_ai.js defines lpai_sync_select_elements");
assert_true(strpos($js_src, 'lpai_sync_select_elements();') !== false, "src/lifeprisma_ai.js calls lpai_sync_select_elements in lpai_open_panel");
assert_true(strpos($js_src, "t.id === 'lpai-model-select'") !== false, "src/lifeprisma_ai.js listens to change on lpai-model-select");
assert_true(strpos($js_src, "t.id === 'lpai-tone-select'") !== false, "src/lifeprisma_ai.js listens to change on lpai-tone-select");
assert_true(strpos($js_src, "t.id === 'lpai-lang-select'") !== false, "src/lifeprisma_ai.js listens to change on lpai-lang-select");
assert_true(strpos($js_src, ".lpai-model-tag") !== false, "src/lifeprisma_ai.js updates .lpai-model-tag on select change");
assert_true(strpos($js_src, "lpai_save_prefs()") !== false, "src/lifeprisma_ai.js persists select preferences");
assert_true(strpos($js_min, "lpai_get_modal_doc") !== false, "lifeprisma_ai.min.js contains compiled lpai_get_modal_doc");

// --- 4. Headless Chrome Real-world Execution Test ---
echo "\n--- Test 4: Headless Chrome Browser Integration Test --- \n";

$test_html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="file://' . realpath(__DIR__ . '/../skins/gmail_plus/style.css') . '">
<script>
window.rcmail = {
    env: { task: "mail", action: "compose", request_token: "test_token" },
    addEventListener: function() {},
    url: function(a) { return a; }
};
</script>
<script src="file://' . realpath(__DIR__ . '/../src/lifeprisma_ai.js') . '"></script>
</head>
<body>
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
        <div id="lpai-controls">
            <div class="lpai-control-group">
                <label for="lpai-model-select">Model</label>
                <select id="lpai-model-select" class="lpai-select">
                    <option value="gemini-3.8-flash" selected>gemini-3.8-flash</option>
                    <option value="gemini-3.8-flash-cyber">gemini-3.8-flash-cyber</option>
                    <option value="gemini-3.7-flash">gemini-3.7-flash</option>
                </select>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-tone-select">Tone</label>
                <select id="lpai-tone-select" class="lpai-select">
                    <option value="professional" selected>Professional</option>
                    <option value="concise">Concise</option>
                    <option value="friendly">Friendly</option>
                </select>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-lang-select">Language</label>
                <select id="lpai-lang-select" class="lpai-select">
                    <option value="English" selected>English</option>
                    <option value="Dutch">Dutch</option>
                    <option value="Spanish">Spanish</option>
                </select>
            </div>
        </div>
        <div id="lpai-input-wrapper">
            <textarea id="lpai-input"></textarea>
        </div>
    </div>
</div>

<script>
window.onload = function() {
    lpai_bind_events();
    lpai_open_panel("compose");

    var panel = document.getElementById("lpai-panel");
    var cs = window.getComputedStyle(panel);
    console.log("CHROME_MODAL_WIDTH:" + cs.width);

    var mSel = document.getElementById("lpai-model-select");
    var mCs = window.getComputedStyle(mSel);
    console.log("CHROME_SELECT_POINTER_EVENTS:" + mCs.pointerEvents);
    console.log("CHROME_SELECT_Z_INDEX:" + mCs.zIndex);

    // Test changing model select
    mSel.value = "gemini-3.7-flash";
    mSel.dispatchEvent(new Event("change", { bubbles: true }));

    console.log("CHROME_UPDATED_OPTION_MODEL:" + lpai_options.model);
    var tag = document.querySelector(".lpai-model-tag");
    console.log("CHROME_UPDATED_TAG_TEXT:" + (tag ? tag.textContent : "none"));

    var savedPrefs = localStorage.getItem("lpai_prefs");
    console.log("CHROME_SAVED_PREFS:" + savedPrefs);
};
</script>
</body>
</html>';

$tmp_file = '/tmp/test_ai_modal_browser.html';
file_put_contents($tmp_file, $test_html);

$cmd = 'google-chrome-stable --headless --disable-gpu --no-sandbox --window-size=1280,800 --run-all-compositor-stages-before-draw --virtual-time-budget=2000 --enable-logging=stderr ' . escapeshellarg($tmp_file) . ' 2>&1';
$output = shell_exec($cmd);
unlink($tmp_file);

$modal_width = null;
$pointer_events = null;
$z_index = null;
$updated_model = null;
$updated_tag = null;
$saved_prefs = null;

if ($output) {
    foreach (explode("\n", $output) as $line) {
        if (preg_match('/CHROME_MODAL_WIDTH:(.*?)"/', $line, $m)) {
            $modal_width = trim($m[1]);
        }
        if (preg_match('/CHROME_SELECT_POINTER_EVENTS:(.*?)"/', $line, $m)) {
            $pointer_events = trim($m[1]);
        }
        if (preg_match('/CHROME_SELECT_Z_INDEX:(.*?)"/', $line, $m)) {
            $z_index = trim($m[1]);
        }
        if (preg_match('/CHROME_UPDATED_OPTION_MODEL:(.*?)"/', $line, $m)) {
            $updated_model = trim($m[1]);
        }
        if (preg_match('/CHROME_UPDATED_TAG_TEXT:(.*?)"/', $line, $m)) {
            $updated_tag = trim($m[1]);
        }
        if (preg_match('/CHROME_SAVED_PREFS:(.*)", source:/', $line, $m)) {
            $saved_prefs = trim(stripslashes($m[1]));
        }
    }
}

assert_true($modal_width === '800px', "Computed modal width in browser is exactly 800px (got: {$modal_width})");
assert_true($pointer_events === 'auto', "Dropdown select pointer-events is 'auto' (got: {$pointer_events})");
assert_true($z_index === '10', "Dropdown select z-index is '10' (got: {$z_index})");
assert_true($updated_model === 'gemini-3.7-flash', "Changing select updates lpai_options.model to 'gemini-3.7-flash' (got: {$updated_model})");
assert_true($updated_tag === 'gemini-3.7-flash', "Changing select updates modal header tag to 'gemini-3.7-flash' (got: {$updated_tag})");
assert_true($saved_prefs !== null && strpos($saved_prefs, 'gemini-3.7-flash') !== false, "Changing select persists updated model into localStorage (got: {$saved_prefs})");

echo "\n============================================\n";
echo "TEST RESULTS: {$passed_count} / {$test_count} tests passed\n";
if ($passed_count === $test_count) {
    echo "*** ALL AI MODAL & DROPDOWN TESTS PASSED (100%) ***\n";
    exit(0);
} else {
    echo "!!! SOME TESTS FAILED !!!\n";
    exit(1);
}
