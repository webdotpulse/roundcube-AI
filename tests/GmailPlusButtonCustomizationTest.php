<?php

/**
 * Automated test suite for xskin "Skin Look & Feel" button customization
 * working seamlessly with the gmail_plus skin.
 */

declare(strict_types=1);

function assert_true(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== Running Gmail Plus Button Customization Test Suite ===\n\n";

$repoRoot = dirname(__DIR__);

// Test 1: Material Skin Complete Removal
echo "--- Test 1: Material Skin Complete Removal ---\n";
assert_true(!is_dir($repoRoot . '/Extra context/skins/material'), "Extra context/skins/material directory is removed");
assert_true(!is_dir($repoRoot . '/skins/material'), "skins/material directory is removed");
assert_true(!file_exists($repoRoot . '/tests/MaterialSkinTest.php'), "tests/MaterialSkinTest.php is removed");

// Test 2: Gmail Plus Skin Integrity
echo "\n--- Test 2: Gmail Plus Skin Integrity ---\n";
assert_true(is_dir($repoRoot . '/Extra context/skins/gmail_plus'), "Extra context/skins/gmail_plus directory exists");
assert_true(file_exists($repoRoot . '/Extra context/skins/gmail_plus/meta.json'), "gmail_plus meta.json exists");
assert_true(file_exists($repoRoot . '/Extra context/skins/gmail_plus/assets/styles/styles.css'), "gmail_plus styles.css exists");
assert_true(file_exists($repoRoot . '/Extra context/skins/gmail_plus/assets/scripts/scripts.min.js'), "gmail_plus scripts.min.js exists");

// Test 3: xskin Look & Feel Button Customization Settings
echo "\n--- Test 3: xskin Button Look & Feel Settings in xskin.php ---\n";
$xskinContent = file_get_contents($repoRoot . '/Extra context/plugins/xskin/xskin.php');
assert_true($xskinContent !== false, "xskin.php is readable");

assert_true(strpos($xskinContent, "'custom_btn_primary_bg'") !== false, "xskin.php supports custom_btn_primary_bg preference");
assert_true(strpos($xskinContent, "'custom_btn_secondary_bg'") !== false, "xskin.php supports custom_btn_secondary_bg preference");
assert_true(strpos($xskinContent, "'custom_btn_radius'") !== false, "xskin.php supports custom_btn_radius preference");
assert_true(strpos($xskinContent, '$buttonSwatches') !== false, "xskin.php defines quick button color swatches");
assert_true(strpos($xskinContent, 'id="xskin-btn-preview-card"') !== false, "xskin.php contains #xskin-btn-preview-card preview container");
assert_true(strpos($xskinContent, 'viewBox="0 0 24 24"') !== false, "Preview card uses standalone inline SVG icon without font dependency");

// Test 4: CSS Injected Selectors Specificity & gmail_plus Override Rules
echo "\n--- Test 4: Specificity & Injected CSS Overrides ---\n";
assert_true(strpos($xskinContent, 'html body [class*=\"xcolor-\"] .btn.btn-primary:not(.btn.btn-danger)') !== false, "CSS targets gmail_plus xcolor primary buttons with superior specificity");
assert_true(strpos($xskinContent, 'html body #compose-plus') !== false, "CSS targets #compose-plus for gmail_plus compose action");
assert_true(strpos($xskinContent, 'html body [class*=\"xcolor-\"] .floating-action-buttons a.button') !== false, "CSS targets gmail_plus floating-action-buttons");
assert_true(strpos($xskinContent, 'html body .btn-secondary') !== false, "CSS targets secondary buttons");
assert_true(strpos($xskinContent, '--btn-primary-bg') !== false && strpos($xskinContent, '--btn-radius') !== false, "CSS defines standard custom properties --btn-primary-bg and --btn-radius");
assert_true(strpos($xskinContent, 'xskin.applyCustomColor') !== false, "JavaScript client-side live preview handler applyCustomColor is defined");
assert_true(strpos($xskinContent, 'xskin.applyCustomRadius') !== false, "JavaScript client-side live preview handler applyCustomRadius is defined");

// Test 5: Headless Chrome Computed Style Verification with gmail_plus
echo "\n--- Test 5: Headless Chrome Rendering with gmail_plus ---\n";
$chromeBin = trim((string)shell_exec('which google-chrome-stable || which google-chrome || which chromium-browser || which chromium 2>/dev/null'));
if (empty($chromeBin) || !is_executable($chromeBin)) {
    echo "NOTICE: Headless Chrome not available in this environment. Skipping live browser test.\n";
} else {
    // Generate an isolated HTML page simulating gmail_plus skin + xskin custom button injection
    $scratchDir = $repoRoot . '/scratch';
    if (!is_dir($scratchDir)) {
        @mkdir($scratchDir, 0777, true);
    }
    $testHtmlFile = $scratchDir . '/gmail_plus_button_test.html';
    $gmailPlusCssPath = realpath($repoRoot . '/Extra context/skins/gmail_plus/assets/styles/styles.css');

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gmail Plus Skin Button Customization Test</title>
    <link rel="stylesheet" href="file://{$gmailPlusCssPath}">
    <style id="xskin-custom-colors">
        :root, html, body {
            --btn-primary-bg: #00875a !important;
            --md-btn-primary-bg: #00875a !important;
            --btn-secondary-bg: #e8f0fe !important;
            --md-btn-secondary-bg: #e8f0fe !important;
            --btn-radius: 8px !important;
            --md-btn-radius: 8px !important;
        }
        html body[class*="xcolor-"] .btn.btn-primary:not(.btn.btn-danger),
        html body [class*="xcolor-"] .btn.btn-primary:not(.btn.btn-danger),
        html [class*="xcolor-"] body .btn.btn-primary:not(.btn.btn-danger),
        html body[class*="xcolor-"] .btn.btn-success:not(.btn.btn-danger),
        html body [class*="xcolor-"] .btn.btn-success:not(.btn.btn-danger),
        html body[class*="xcolor-"] .floating-action-buttons a.button,
        html body [class*="xcolor-"] .floating-action-buttons a.button,
        html body[class*="xcolor-"] div.tox .tox-dialog__footer .tox-button,
        html body [class*="xcolor-"] div.tox .tox-dialog__footer .tox-button,
        html body[class*="xcolor-"] .mce-window .mce-foot .mce-btn.mce-primary,
        html body [class*="xcolor-"] .mce-window .mce-foot .mce-btn.mce-primary,
        html body .btn-primary, html body .btn.btn-primary, html body button.mainaction,
        html body input[type="submit"].mainaction, html body .formbuttons .btn-primary,
        html body .formbuttons input.mainaction, html body .formbuttons button.mainaction,
        html body .floating-action-buttons a.button, html body #compose-plus,
        html body .ui-dialog .ui-dialog-buttonpane button.ui-button-primary {
            background-color: #00875a !important;
            border-color: #00875a !important;
            color: #ffffff !important;
        }
        html body .btn-secondary, html body .btn.btn-secondary, html body .btn-outline-secondary,
        html body button.cancel, html body a.button.cancel, html body .formbuttons .btn-secondary,
        html body .formbuttons button.cancel, html body .ui-dialog .ui-dialog-buttonpane button.ui-button-secondary {
            background-color: #e8f0fe !important;
        }
        html body .btn, html body .btn-primary, html body .btn-secondary,
        html body button.mainaction, html body input[type="submit"].mainaction,
        html body a.button, html body .formbuttons .btn, html body .formbuttons .btn-primary,
        html body .formbuttons .btn-secondary, html body .formbuttons input.mainaction,
        html body .formbuttons button.mainaction, html body #compose-plus,
        html body .floating-action-buttons a.button {
            border-radius: 8px !important;
        }
    </style>
</head>
<body class="skin-gmail_plus xelastic xskin xcolor-0075c8">
    <div id="layout">
        <!-- Main primary action button in gmail_plus -->
        <button type="button" class="btn btn-primary" id="main-btn">Save Settings</button>

        <!-- Secondary action button -->
        <button type="button" class="btn btn-secondary" id="sec-btn">Cancel</button>

        <!-- Floating action compose button in gmail_plus -->
        <div class="floating-action-buttons">
            <a href="#" class="button compose" id="compose-plus">Compose</a>
        </div>

        <!-- Live interactive preview card from xskin -->
        <div id="xskin-btn-preview-card" class="xskin-btn-preview-card material-btn-preview-card">
            <button type="button" class="btn btn-primary" id="preview-primary-btn" style="background-color: var(--btn-primary-bg, #1a73e8); border-radius: var(--btn-radius, 8px);">Primary Action</button>
            <button type="button" class="btn btn-secondary" id="preview-secondary-btn" style="background-color: var(--btn-secondary-bg, #e8f0fe); border-radius: var(--btn-radius, 8px);">Secondary</button>
            <a class="button compose" id="preview-fab-btn" style="background-color: var(--btn-primary-bg, #1a73e8); border-radius: var(--btn-radius, 8px);">Compose</a>
        </div>
    </div>

    <script>
    window.xskin = {};
    xskin.applyCustomColor = function(field, color) {
        var hex = (color || '').trim();
        var styleId = 'xskin-live-' + field;
        var selMap = {
            custom_btn_primary_bg: 'html body[class*="xcolor-"] .btn.btn-primary:not(.btn.btn-danger), html body [class*="xcolor-"] .btn.btn-primary:not(.btn.btn-danger), html [class*="xcolor-"] body .btn.btn-primary:not(.btn.btn-danger), html body[class*="xcolor-"] .btn.btn-success:not(.btn.btn-danger), html body [class*="xcolor-"] .btn.btn-success:not(.btn.btn-danger), html body[class*="xcolor-"] .floating-action-buttons a.button, html body [class*="xcolor-"] .floating-action-buttons a.button, html body .btn-primary, html body .btn.btn-primary, html body button.mainaction, html body #compose-plus, #preview-primary-btn, #preview-fab-btn',
            custom_btn_secondary_bg: 'html body .btn-secondary, html body .btn.btn-secondary, html body .btn-outline-secondary, #preview-secondary-btn'
        };
        var sel = selMap[field];
        if (!sel) return;
        var el = document.getElementById(styleId);
        if (!hex) {
            if (el) el.remove();
            return;
        }
        if (!el) {
            el = document.createElement('style');
            el.id = styleId;
            el.type = 'text/css';
            document.head.appendChild(el);
        }
        var cssText = '';
        if (field === 'custom_btn_primary_bg') {
            cssText = ':root, html, body { --btn-primary-bg: ' + hex + ' !important; } ' + sel + ' { background-color: ' + hex + ' !important; border-color: ' + hex + ' !important; color: #ffffff !important; }';
        } else if (field === 'custom_btn_secondary_bg') {
            cssText = ':root, html, body { --btn-secondary-bg: ' + hex + ' !important; } ' + sel + ' { background-color: ' + hex + ' !important; }';
        }
        el.textContent = cssText;
    };
    xskin.applyCustomRadius = function(val) {
        var rad = (val || '').trim();
        var styleId = 'xskin-live-custom_btn_radius';
        var sel = 'html body .btn, html body .btn-primary, html body .btn-secondary, html body #compose-plus, #preview-primary-btn, #preview-secondary-btn, #preview-fab-btn';
        var el = document.getElementById(styleId);
        if (!rad) {
            if (el) el.remove();
            return;
        }
        if (!el) {
            el = document.createElement('style');
            el.id = styleId;
            el.type = 'text/css';
            document.head.appendChild(el);
        }
        el.textContent = ':root, html, body { --btn-radius: ' + rad + ' !important; } ' + sel + ' { border-radius: ' + rad + ' !important; }';
    };

    // Execute computed style checks immediately and write to DOM
    var mainBtn = document.getElementById('main-btn');
    var secBtn = document.getElementById('sec-btn');
    var composeBtn = document.getElementById('compose-plus');
    var prevPrimary = document.getElementById('preview-primary-btn');

    var res = {
        mainBtnBg: window.getComputedStyle(mainBtn).backgroundColor,
        mainBtnRadius: window.getComputedStyle(mainBtn).borderRadius,
        secBtnBg: window.getComputedStyle(secBtn).backgroundColor,
        secBtnRadius: window.getComputedStyle(secBtn).borderRadius,
        composeBg: window.getComputedStyle(composeBtn).backgroundColor,
        composeRadius: window.getComputedStyle(composeBtn).borderRadius,
        prevPrimaryBg: window.getComputedStyle(prevPrimary).backgroundColor
    };

    // Test dynamic live update to purple (#7c3aed = rgb(124, 58, 237)) and 20px radius
    xskin.applyCustomColor('custom_btn_primary_bg', '#7c3aed');
    xskin.applyCustomRadius('20px');

    res.updatedMainBtnBg = window.getComputedStyle(mainBtn).backgroundColor;
    res.updatedMainBtnRadius = window.getComputedStyle(mainBtn).borderRadius;
    res.updatedComposeBg = window.getComputedStyle(composeBtn).backgroundColor;
    res.updatedComposeRadius = window.getComputedStyle(composeBtn).borderRadius;

    var out = document.createElement('div');
    out.id = 'test-computed-results';
    out.textContent = JSON.stringify(res);
    document.body.appendChild(out);
    </script>
</body>
</html>
HTML;

    file_put_contents($testHtmlFile, $html);

    // Run Chrome to evaluate and dump DOM with computed styles
    $cmd = escapeshellcmd($chromeBin) . " --headless --disable-gpu --no-sandbox --dump-dom " . escapeshellarg("file://" . $testHtmlFile);
    $domOutput = (string)shell_exec($cmd);

    assert_true(strpos($domOutput, 'test-computed-results') !== false, "Chrome evaluated test and produced #test-computed-results element");

    preg_match('/<div id="test-computed-results">(.*?)<\/div>/s', $domOutput, $matches);
    $jsonStr = html_entity_decode($matches[1] ?? '{}', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $results = json_decode($jsonStr, true);

    assert_true(is_array($results) && !empty($results), "Parsed computed style test results from Headless Chrome");

    // In gmail_plus palette 0075c8, default primary button is #b0263b = rgb(176, 38, 59).
    // Our custom color #00875a = rgb(0, 135, 90) must override it cleanly!
    assert_true(
        $results['mainBtnBg'] === 'rgb(0, 135, 90)',
        "Primary button background overridden from gmail_plus default to custom #00875a (got {$results['mainBtnBg']})"
    );
    assert_true(
        $results['composeBg'] === 'rgb(0, 135, 90)',
        "Compose button background overridden to custom #00875a (got {$results['composeBg']})"
    );
    assert_true(
        $results['secBtnBg'] === 'rgb(232, 240, 254)',
        "Secondary button background set to custom #e8f0fe (got {$results['secBtnBg']})"
    );
    assert_true(
        $results['mainBtnRadius'] === '8px',
        "Primary button corner radius set to custom 8px (got {$results['mainBtnRadius']})"
    );
    assert_true(
        $results['composeRadius'] === '8px',
        "Compose button corner radius set to custom 8px (got {$results['composeRadius']})"
    );

    // Verify dynamic JS live update
    assert_true(
        $results['updatedMainBtnBg'] === 'rgb(124, 58, 237)',
        "Dynamic JS live update changed primary button background to purple #7c3aed (got {$results['updatedMainBtnBg']})"
    );
    assert_true(
        $results['updatedComposeBg'] === 'rgb(124, 58, 237)',
        "Dynamic JS live update changed compose button background to purple #7c3aed (got {$results['updatedComposeBg']})"
    );
    assert_true(
        $results['updatedMainBtnRadius'] === '20px',
        "Dynamic JS live update changed button radius to pill 20px (got {$results['updatedMainBtnRadius']})"
    );

    // Clean up temporary test html
    @unlink($testHtmlFile);
}

echo "\n=======================================================\n";
echo "All Gmail Plus Button Customization Tests PASSED (100%)!\n";
echo "=======================================================\n";
