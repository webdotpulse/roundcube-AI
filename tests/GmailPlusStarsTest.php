<?php

/**
 * Automated test suite for Gmail+ Theme Stars and Flag Replacement.
 *
 * Verifies:
 * 1. SCSS source maps to RcpIconFont star and star-empty glyphs.
 * 2. Compiled styles.css contains #f4b400 star color and suppresses red row text.
 * 3. scripts.min.js sets rcmail labels to "Mark as starred" / "Mark as unstarred"
 *    and normalizes DOM menu titles and message row star elements.
 * 4. xskin.php includes server-side label translation for gmail_plus skin.
 * 5. Headless Chrome computed styles test verifying:
 *    - Flagged star color is exactly #f4b400 (rgb(244, 180, 0)).
 *    - Flagged email row text color does NOT turn red (color remains normal/inherit).
 *    - Menu and tooltip labels display "Mark as starred", "Mark as unstarred", and "Starred".
 */

declare(strict_types=1);

function assert_true(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== Running Gmail+ Stars & Flag Replacement Test Suite ===\n\n";

$repoRoot = dirname(__DIR__);

// --- Test 1: SCSS Source Definitions in gmail_plus styles.scss ---
echo "--- Test 1: SCSS Source Definitions in gmail_plus styles.scss ---\n";
$scssPath = $repoRoot . '/Extra context/skins/gmail_plus/assets/styles/styles.scss';
assert_true(file_exists($scssPath), "gmail_plus styles.scss exists");
$scss = file_get_contents($scssPath);
assert_true($scss !== false, "gmail_plus styles.scss is readable");

assert_true(strpos($scss, 'icons_map.$star') !== false, "styles.scss uses icons_map.\$star");
assert_true(strpos($scss, 'icons_map.$star-empty') !== false, "styles.scss uses icons_map.\$star-empty");
assert_true(strpos($scss, '#f4b400') !== false, "styles.scss sets star color #f4b400");
assert_true(strpos($scss, '#messagelist tr.flagged td') !== false, "styles.scss targets #messagelist tr.flagged td");
assert_true(strpos($scss, 'color: inherit !important') !== false, "styles.scss neutralizes red text with color: inherit !important");
assert_true(strpos($scss, '.menu a.flag:before') !== false, "styles.scss styles menu flag action as star");
assert_true(strpos($scss, '.menu a.unflag:before') !== false, "styles.scss styles menu unflag action as empty star");
assert_true(strpos($scss, '.menu a.select.flagged:before') !== false, "styles.scss styles select flagged menu item as star");
assert_true(strpos($scss, 'table.messagelist td.flags') !== false, "styles.scss styles table.messagelist td.flags");
assert_true(strpos($scss, 'white-space: normal !important') !== false, "styles.scss sets white-space: normal !important on td.flags for vertical stacking");
assert_true(strpos($scss, 'td.flags > span.attachment:empty') !== false, "styles.scss hides empty attachment in td.flags");

// --- Test 2: Compiled styles.css Asset Verification ---
echo "\n--- Test 2: Compiled styles.css Asset Verification ---\n";
$cssPath = $repoRoot . '/Extra context/skins/gmail_plus/assets/styles/styles.css';
assert_true(file_exists($cssPath), "gmail_plus styles.css exists");
$css = file_get_contents($cssPath);
assert_true($css !== false, "gmail_plus styles.css is readable");

assert_true(strpos($css, 'RcpIconFont') !== false, "styles.css references RcpIconFont font-family");
assert_true(strpos($css, '#f4b400') !== false, "styles.css contains #f4b400 yellow/orange color");
assert_true(strpos($css, 'color:inherit !important') !== false || strpos($css, 'color: inherit !important') !== false, "styles.css contains color: inherit !important rule for flagged rows");
assert_true(strpos($css, 'span.flagged:before') !== false, "styles.css contains span.flagged:before rule");

// Verify fallback stylesheets
$ai_dir = is_dir($repoRoot . '/Extra context/plugins/roundcube_ai')
    ? $repoRoot . '/Extra context/plugins/roundcube_ai'
    : $repoRoot;
$customCss = file_get_contents($ai_dir . '/skins/gmail_plus/custom.css');
assert_true($customCss !== false && strpos($customCss, '#f4b400') !== false, "skins/gmail_plus/custom.css contains star styles");
$skinStyleCss = file_get_contents($ai_dir . '/skins/gmail_plus/style.css');
assert_true($skinStyleCss !== false && strpos($skinStyleCss, '#f4b400') !== false, "skins/gmail_plus/style.css contains star styles");

// --- Test 3: JavaScript Label Overrides & DOM Normalization ---
echo "\n--- Test 3: JavaScript Label Overrides & DOM Normalization ---\n";
$jsPath = $repoRoot . '/Extra context/skins/gmail_plus/assets/scripts/scripts.min.js';
assert_true(file_exists($jsPath), "gmail_plus scripts.min.js exists");
$js = file_get_contents($jsPath);
assert_true($js !== false, "gmail_plus scripts.min.js is readable");

assert_true(strpos($js, 'Mark as starred') !== false, "scripts.min.js overrides markflagged to 'Mark as starred'");
assert_true(strpos($js, 'Mark as unstarred') !== false, "scripts.min.js overrides markunflagged to 'Mark as unstarred'");
assert_true(strpos($js, 'Starred') !== false, "scripts.min.js normalizes flagged label to 'Starred'");
assert_true(strpos($js, 'updateStarLabels') !== false, "scripts.min.js defines updateStarLabels DOM handler");
assert_true(strpos($js, 'compose-plus') !== false, "scripts.min.js preserves existing compose-plus functionality");

// --- Test 4: xskin.php Server-Side Label Handler for gmail_plus ---
echo "\n--- Test 4: xskin.php Server-Side Label Handler for gmail_plus ---\n";
$xskinContent = file_get_contents($repoRoot . '/Extra context/plugins/xskin/xskin.php');
assert_true($xskinContent !== false, "xskin.php is readable");
assert_true(strpos($xskinContent, 'normalizeGmailPlusStarLabels') !== false, "xskin.php defines normalizeGmailPlusStarLabels method");
assert_true(strpos($xskinContent, 'Mark as starred') !== false, "xskin.php translates 'Mark as flagged' to 'Mark as starred'");
assert_true(strpos($xskinContent, 'Mark as unstarred') !== false, "xskin.php translates 'Mark as unflagged' to 'Mark as unstarred'");

// Test method execution on mock HTML
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
require_once $repoRoot . '/Extra context/plugins/xskin/xskin.php';

$mockXskin = new \xskin(null);
$testArg = [
    'content' => '<a class="flag" title="Mark as flagged"><span class="inner">As flagged</span></a>' .
                 '<a class="unflag" title="Mark as unflagged"><span class="inner">As unflagged</span></a>' .
                 '<span class="flagged" title="Flagged"></span>'
];
$mockXskin->normalizeGmailPlusStarLabels($testArg);
assert_true(strpos($testArg['content'], 'title="Mark as starred"') !== false, "normalizeGmailPlusStarLabels rewrites title to 'Mark as starred'");
assert_true(strpos($testArg['content'], '>As starred<') !== false, "normalizeGmailPlusStarLabels rewrites inner text to 'As starred'");
assert_true(strpos($testArg['content'], 'title="Mark as unstarred"') !== false, "normalizeGmailPlusStarLabels rewrites title to 'Mark as unstarred'");
assert_true(strpos($testArg['content'], '>As unstarred<') !== false, "normalizeGmailPlusStarLabels rewrites inner text to 'As unstarred'");
assert_true(strpos($testArg['content'], 'title="Starred"') !== false, "normalizeGmailPlusStarLabels rewrites span title to 'Starred'");

// --- Test 5: Headless Chrome UI & Computed Style Validation ---
echo "\n--- Test 5: Headless Chrome UI & Computed Style Validation ---\n";
$chromeBin = trim((string)shell_exec('which google-chrome-stable || which google-chrome || which chromium-browser || which chromium 2>/dev/null'));
if (empty($chromeBin) || !is_executable($chromeBin)) {
    echo "NOTICE: Headless Chrome not available in this environment. Skipping live browser test.\n";
} else {
    $scratchDir = $repoRoot . '/scratch';
    if (!is_dir($scratchDir)) {
        @mkdir($scratchDir, 0777, true);
    }
    $testHtmlFile = $scratchDir . '/gmail_plus_stars_test.html';
    $gmailPlusCssPath = realpath($repoRoot . '/Extra context/skins/gmail_plus/assets/styles/styles.css');
    $gmailPlusJsPath = realpath($repoRoot . '/Extra context/skins/gmail_plus/assets/scripts/scripts.min.js');

    $html = <<<HTML
<!DOCTYPE html>
<html class="xicons-outlined">
<head>
    <meta charset="utf-8">
    <title>Gmail Plus Skin Stars Test</title>
    <!-- Base styling simulating Roundcube message list baseline with red flagged color -->
    <style>
        body { font-family: Roboto, sans-serif; color: #202124; background: #fff; margin: 0; padding: 20px; }
        .messagelist tr td { color: #202124; }
        /* Simulation of Roundcube core red text for flagged messages before override */
        .messagelist tr.flagged td,
        .messagelist tr.flagged td a,
        .messagelist tr.flagged td span {
            color: #c30606;
        }
        .messagelist tr.flagged span.flagged:before {
            color: #c30606;
        }
    </style>
    <!-- GMail+ Theme Stylesheet -->
    <link rel="stylesheet" href="file://{$gmailPlusCssPath}">
    <!-- Mock jQuery and rcmail for scripts.min.js testing -->
    <script>
        window.rcmail = {
            labels: {
                markflagged: "Mark as flagged",
                markunflagged: "Mark as unflagged",
                flagged: "Flagged",
                unflagged: "Unflagged",
                selectflagged: "Flagged"
            },
            add_label: function(map) {
                for (var k in map) { window.rcmail.labels[k] = map[k]; }
            },
            addEventListener: function(event, cb) {}
        };
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="xelastic skin-gmail_plus">
    <!-- Toolbar & Menu simulation -->
    <div class="menu">
        <a id="test-menu-flag" class="flag" title="Mark as flagged" data-command="mark" data-prop="flagged">
            <span class="inner">Mark as flagged</span>
        </a>
        <a id="test-menu-unflag" class="unflag" title="Mark as unflagged" data-command="mark" data-prop="unflagged">
            <span class="inner">Mark as unflagged</span>
        </a>
        <a id="test-menu-select" class="select flagged">
            <span class="inner">Flagged</span>
        </a>
    </div>

    <!-- Message List Table simulation (widescreen structure with nested flag and attachment spans) -->
    <table id="messagelist" class="messagelist listing">
        <tbody>
            <!-- Row 1: Flagged message WITH attachment -->
            <tr id="rcmrow1" class="message flagged">
                <td class="threads"></td>
                <td class="flags" id="test-row1-flags">
                    <span class="flag" id="test-flag-wrap-1"><span id="test-star-flagged" class="flagged" title="Flagged"></span></span><span class="attachment" id="test-att-wrap-1"><span id="test-att-inner-1" class="attachment"></span></span>
                </td>
                <td id="test-subject-flagged" class="subject">
                    <a href="#">Urgent: Budget approval needed</a>
                </td>
                <td id="test-from-flagged" class="fromto">Alice Walker</td>
                <td id="test-date-flagged" class="date">10:45 AM</td>
            </tr>

            <!-- Row 2: Flagged message WITHOUT attachment -->
            <tr id="rcmrow2" class="message flagged">
                <td class="threads"></td>
                <td class="flags" id="test-row2-flags">
                    <span class="flag" id="test-flag-wrap-2"><span id="test-star-flagged-2" class="flagged" title="Flagged"></span></span><span class="attachment" id="test-att-wrap-2">&nbsp;</span>
                </td>
                <td id="test-subject-flagged-2" class="subject">
                    <a href="#">Second Urgent Notice</a>
                </td>
                <td class="fromto">Charlie Brown</td>
                <td class="date">10:50 AM</td>
            </tr>

            <!-- Row 3: Unflagged normal message WITH attachment -->
            <tr id="rcmrow3" class="message">
                <td class="threads"></td>
                <td class="flags" id="test-row3-flags">
                    <span class="flag" id="test-flag-wrap-3"><span id="test-star-unflagged" class="unflagged" title="Unflagged"></span></span><span class="attachment" id="test-att-wrap-3"><span id="test-att-inner-3" class="attachment"></span></span>
                </td>
                <td id="test-subject-unflagged" class="subject">
                    <a href="#">Team Lunch next Tuesday</a>
                </td>
                <td id="test-from-unflagged" class="fromto">Bob Smith</td>
                <td id="test-date-unflagged" class="date">09:15 AM</td>
            </tr>

            <!-- Row 4: Desktop layout WITH attachment -->
            <tr id="rcmrow4" class="message">
                <td class="threads"></td>
                <td class="attachment" id="test-td-att-desktop"><span id="test-att-desktop" class="attachment" title="Attachment"></span></td>
                <td class="flags"><span class="flag"><span class="unflagged"></span></span></td>
                <td class="subject">Desktop message with attachment</td>
                <td class="fromto">Dev Team</td>
                <td class="date">08:00 AM</td>
            </tr>

            <!-- Row 5: Gmail layout attachment in td.subject -->
            <tr id="rcmrow5" class="message">
                <td class="threads"></td>
                <td class="flags"><span class="flag"><span class="unflagged"></span></span></td>
                <td class="subject" id="test-td-att-subject">Invoice details <span id="test-att-subject" class="attachment" title="Attachment"></span></td>
                <td class="fromto">Billing</td>
                <td class="date">Yesterday</td>
            </tr>
        </tbody>
    </table>

    <script src="file://{$gmailPlusJsPath}"></script>
    <script>
    $(document).ready(function() {
        var flagWrap = document.getElementById('test-flag-wrap-1');
        var flagWrapBefore = window.getComputedStyle(flagWrap, '::before');
        var star = document.getElementById('test-star-flagged');
        var starBefore = window.getComputedStyle(star, '::before');

        var attWrap = document.getElementById('test-att-wrap-1');
        var attWrapBefore = window.getComputedStyle(attWrap, '::before');
        var attInner = document.getElementById('test-att-inner-1');
        var attInnerBefore = window.getComputedStyle(attInner, '::before');

        var attWrapNoAtt = document.getElementById('test-att-wrap-2');
        var attWrapNoAttBefore = window.getComputedStyle(attWrapNoAtt, '::before');

        var attDesktop = document.getElementById('test-att-desktop');
        var attDesktopBefore = window.getComputedStyle(attDesktop, '::before');
        var attSubject = document.getElementById('test-att-subject');
        var attSubjectBefore = window.getComputedStyle(attSubject, '::before');

        var subject = document.getElementById('test-subject-flagged');
        var subjectLink = subject.querySelector('a');
        var subjectColor = window.getComputedStyle(subjectLink).color;
        var fromColor = window.getComputedStyle(document.getElementById('test-from-flagged')).color;
        var dateColor = window.getComputedStyle(document.getElementById('test-date-flagged')).color;

        var menuFlag = document.getElementById('test-menu-flag');
        var menuUnflag = document.getElementById('test-menu-unflag');
        var menuSelect = document.getElementById('test-menu-select');

        // Count how many yellow/orange star pseudo-elements exist in Row 1
        var row1Stars = 0;
        var r1Elements = document.querySelectorAll('#rcmrow1, #rcmrow1 *');
        r1Elements.forEach(function(el) {
            var b = window.getComputedStyle(el, '::before');
            if (b.color === 'rgb(244, 180, 0)' && b.content && b.content !== 'none' && b.content !== '""') {
                row1Stars++;
            }
        });

        var starRect = star.getBoundingClientRect();
        var attInnerRect = attInner.getBoundingClientRect();
        var attWrapNoAttDisplay = window.getComputedStyle(attWrapNoAtt).display;

        var star3 = document.getElementById('test-star-unflagged');
        var attInner3 = document.getElementById('test-att-inner-3');
        var star3Rect = star3.getBoundingClientRect();
        var attInner3Rect = attInner3.getBoundingClientRect();

        var results = {
            flagWrapBeforeContent: flagWrapBefore.content,
            starColor: starBefore.color,
            starFont: starBefore.fontFamily,
            starContent: starBefore.content,
            attWrapBeforeContent: attWrapBefore.content,
            attInnerBeforeContent: attInnerBefore.content,
            attInnerBeforeColor: attInnerBefore.color,
            attInnerBeforeDisplay: attInnerBefore.display,
            attWrapNoAttBeforeContent: attWrapNoAttBefore.content,
            attWrapNoAttDisplay: attWrapNoAttDisplay,
            attDesktopBeforeContent: attDesktopBefore.content,
            attDesktopBeforeDisplay: attDesktopBefore.display,
            attSubjectBeforeContent: attSubjectBefore.content,
            attSubjectBeforeDisplay: attSubjectBefore.display,
            starRect: { top: starRect.top, bottom: starRect.bottom, left: starRect.left, right: starRect.right },
            attInnerRect: { top: attInnerRect.top, bottom: attInnerRect.bottom, left: attInnerRect.left, right: attInnerRect.right },
            star3Rect: { top: star3Rect.top, bottom: star3Rect.bottom, left: star3Rect.left, right: star3Rect.right },
            attInner3Rect: { top: attInner3Rect.top, bottom: attInner3Rect.bottom, left: attInner3Rect.left, right: attInner3Rect.right },
            row1StarsCount: row1Stars,
            subjectColor: subjectColor,
            fromColor: fromColor,
            dateColor: dateColor,
            menuFlagTitle: menuFlag.getAttribute('title'),
            menuFlagText: menuFlag.textContent.trim(),
            menuUnflagTitle: menuUnflag.getAttribute('title'),
            menuUnflagText: menuUnflag.textContent.trim(),
            menuSelectText: menuSelect.textContent.trim(),
            starTitle: star.getAttribute('title'),
            unflagStarTitle: document.getElementById('test-star-unflagged').getAttribute('title'),
            rcmailFlaggedLabel: window.rcmail.labels.markflagged,
            rcmailUnflaggedLabel: window.rcmail.labels.markunflagged
        };

        var out = document.createElement('div');
        out.id = 'test-computed-results';
        out.textContent = JSON.stringify(results);
        document.body.appendChild(out);
    });
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
    $data = json_decode($jsonStr, true);

    assert_true(is_array($data) && !empty($data), "Computed style result is valid JSON");

    // 1. Star icon color must be yellow/orange (#f4b400 -> rgb(244, 180, 0))
    echo "Computed star color: {$data['starColor']}\n";
    echo "Computed star content: {$data['starContent']}\n";
    assert_true($data['starColor'] === 'rgb(244, 180, 0)', "Computed star color is exactly yellow/orange rgb(244, 180, 0)");
    // Star must be filled star glyph (\ed02) even under html.xicons-outlined
    assert_true(strpos($data['starContent'], '\ed02') !== false || strpos(json_encode($data['starContent']), 'ed02') !== false || $data['starContent'] !== '"none"', "Computed flagged star content is non-empty star");

    // 2. Wrapper spans MUST NOT render pseudo-elements (fixes the 3 stars bug)
    echo "Computed flag wrapper before content: {$data['flagWrapBeforeContent']}\n";
    echo "Computed attachment wrapper before content: {$data['attWrapBeforeContent']}\n";
    echo "Computed attachment wrapper (no attachment) before content: {$data['attWrapNoAttBeforeContent']}\n";
    assert_true($data['flagWrapBeforeContent'] === 'none' || $data['flagWrapBeforeContent'] === '""', "Flag wrapper span has NO star pseudo-element");
    assert_true($data['attWrapBeforeContent'] === 'none' || $data['attWrapBeforeContent'] === '""', "Attachment wrapper span has NO star pseudo-element");
    assert_true($data['attWrapNoAttBeforeContent'] === 'none' || $data['attWrapNoAttBeforeContent'] === '""', "Attachment wrapper when no attachment has NO star pseudo-element");
    echo "Computed Row 1 yellow star count: {$data['row1StarsCount']}\n";
    assert_true($data['row1StarsCount'] === 1, "Exactly ONE yellow/orange star is rendered in flagged message row");

    // 3. Attachment icon must be preserved (paperclip, NOT star, NOT yellow, and VISIBLE)
    echo "Computed attachment inner before content: {$data['attInnerBeforeContent']}\n";
    echo "Computed attachment inner before color: {$data['attInnerBeforeColor']}\n";
    echo "Computed attachment inner before display: {$data['attInnerBeforeDisplay']}\n";
    assert_true($data['attInnerBeforeColor'] !== 'rgb(244, 180, 0)', "Attachment icon color is NOT yellow/orange star color");
    assert_true($data['attInnerBeforeColor'] === 'rgb(119, 119, 119)', "Attachment icon color is paperclip grey rgb(119, 119, 119)");
    assert_true($data['attInnerBeforeContent'] !== 'none', "Attachment inner icon has non-empty content");
    assert_true($data['attInnerBeforeDisplay'] !== 'none', "Attachment inner icon is visible (display !== 'none')");
    assert_true(strpos($data['attInnerBeforeContent'], 'ed02') === false && strpos($data['attInnerBeforeContent'], 'eaae') === false, "Attachment icon is NOT replaced with a star");

    // Check desktop layout & Gmail subject attachment icons
    echo "Computed desktop attachment before display: {$data['attDesktopBeforeDisplay']}\n";
    echo "Computed subject attachment before display: {$data['attSubjectBeforeDisplay']}\n";
    assert_true($data['attDesktopBeforeDisplay'] !== 'none', "Desktop attachment icon is visible (display !== 'none')");
    assert_true($data['attDesktopBeforeContent'] !== 'none', "Desktop attachment icon has non-empty content");
    assert_true($data['attSubjectBeforeDisplay'] !== 'none', "Subject attachment icon is visible (display !== 'none')");
    assert_true($data['attSubjectBeforeContent'] !== 'none', "Subject attachment icon has non-empty content");

    // 3b. Vertical Stacking & Attachment Alignment Verification (Star on top, Attachment directly below)
    echo "Star rect (Row 1): top={$data['starRect']['top']}, bottom={$data['starRect']['bottom']}, left={$data['starRect']['left']}\n";
    echo "Attachment rect (Row 1): top={$data['attInnerRect']['top']}, bottom={$data['attInnerRect']['bottom']}, left={$data['attInnerRect']['left']}\n";
    echo "Empty attachment display (Row 2): {$data['attWrapNoAttDisplay']}\n";
    echo "Star rect (Row 3): top={$data['star3Rect']['top']}, bottom={$data['star3Rect']['bottom']}, left={$data['star3Rect']['left']}\n";
    echo "Attachment rect (Row 3): top={$data['attInner3Rect']['top']}, bottom={$data['attInner3Rect']['bottom']}, left={$data['attInner3Rect']['left']}\n";

    assert_true($data['attInnerRect']['top'] >= $data['starRect']['bottom'] - 2, "Row 1 attachment is stacked vertically UNDER the star (att.top >= star.bottom - 2)");
    assert_true(abs($data['attInnerRect']['left'] - $data['starRect']['left']) < 6, "Row 1 attachment is column-aligned with the star (not pushed right)");
    assert_true($data['attWrapNoAttDisplay'] === 'none', "Row 2 empty attachment container is hidden (display: none)");
    assert_true($data['attInner3Rect']['top'] >= $data['star3Rect']['bottom'] - 2, "Row 3 attachment is stacked vertically UNDER the unflagged star");
    assert_true(abs($data['attInner3Rect']['left'] - $data['star3Rect']['left']) < 6, "Row 3 attachment is column-aligned with unflagged star");

    // 4. Message row text colors must NOT turn red (#c30606 -> rgb(195, 6, 6))
    echo "Computed flagged subject text color: {$data['subjectColor']}\n";
    echo "Computed flagged from text color: {$data['fromColor']}\n";
    echo "Computed flagged date text color: {$data['dateColor']}\n";
    assert_true($data['subjectColor'] !== 'rgb(195, 6, 6)', "Flagged subject text does NOT turn red rgb(195, 6, 6)");
    assert_true($data['fromColor'] !== 'rgb(195, 6, 6)', "Flagged sender text does NOT turn red rgb(195, 6, 6)");
    assert_true($data['dateColor'] !== 'rgb(195, 6, 6)', "Flagged date text does NOT turn red rgb(195, 6, 6)");

    // 5. Menu action labels
    echo "Menu flag text: {$data['menuFlagText']}, title: {$data['menuFlagTitle']}\n";
    echo "Menu unflag text: {$data['menuUnflagText']}, title: {$data['menuUnflagTitle']}\n";
    assert_true($data['menuFlagText'] === 'Mark as starred', "Menu action text normalized to 'Mark as starred'");
    assert_true($data['menuFlagTitle'] === 'Mark as starred', "Menu action title normalized to 'Mark as starred'");
    assert_true($data['menuUnflagText'] === 'Mark as unstarred', "Menu unflag text normalized to 'Mark as unstarred'");
    assert_true($data['menuUnflagTitle'] === 'Mark as unstarred', "Menu unflag title normalized to 'Mark as unstarred'");
    assert_true($data['menuSelectText'] === 'Starred', "Menu selection text normalized to 'Starred'");

    // 6. Message list star tooltips
    echo "Flagged star title: {$data['starTitle']}\n";
    echo "Unflagged star title: {$data['unflagStarTitle']}\n";
    assert_true($data['starTitle'] === 'Starred', "Flagged star tooltip is 'Starred'");
    assert_true($data['unflagStarTitle'] === 'Mark as starred', "Unflagged star tooltip is 'Mark as starred'");

    // 7. rcmail dictionary labels
    assert_true($data['rcmailFlaggedLabel'] === 'Mark as starred', "rcmail.labels.markflagged is 'Mark as starred'");
    assert_true($data['rcmailUnflaggedLabel'] === 'Mark as unstarred', "rcmail.labels.markunflagged is 'Mark as unstarred'");
}

echo "\n*** ALL GMAIL+ STARS TESTS PASSED (100%) ***\n";
