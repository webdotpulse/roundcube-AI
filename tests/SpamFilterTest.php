<?php
/**
 * Test Suite: Advanced Spam Filter & Self-Learning Engine
 *
 * Verifies:
 * 1. Bayesian statistical classifier (Robinson & Graham smoothing)
 * 2. Bi-directional learning & auto-correction un-learning
 * 3. Pre-flight Whitelist & Blacklist matching
 * 4. Header authentication heuristics (SPF, DKIM, DMARC)
 * 5. Persistence, atomic disk storage, and database reset
 * 6. Integration hooks in lifeprisma_ai.php
 * 7. Background CLI worker spam inspection & routing
 * 8. Frontend JavaScript bundles & toolbar actions
 * 9. Skin CSS styles in both elastic and gmail_plus
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

echo "=== ADVANCED SPAM FILTER & SELF-LEARNING ENGINE TEST SUITE ===\n\n";

// Require the spam filter engine
require_once __DIR__ . '/../src/LpaiSpamFilter.php';

// Setup isolated test directory for spam model
$test_data_dir = __DIR__ . '/../scratch/test_spam_data';
if (!is_dir($test_data_dir)) {
    @mkdir($test_data_dir, 0755, true);
}

// Clean any pre-existing test files
array_map('unlink', glob("{$test_data_dir}/*.*"));

// --- Test Group 1: Core Bayesian Engine & Initial Heuristics ---
echo "--- Group 1: Core Bayesian Classifier & Tokenization --- \n";

$filter = new LpaiSpamFilter('test_user@example.com', $test_data_dir);
$stats = $filter->get_stats();

assert_true($stats['user'] === 'test_user@example.com', "Filter initialized for test user");
assert_true($stats['spam_messages'] === 0, "Initial spam message count is 0");
assert_true($stats['ham_messages'] === 0, "Initial ham message count is 0");
assert_true($stats['total_tokens'] > 0, "Seed baseline tokens pre-loaded for immediate heuristic protection");

// Classify clear spam message
$spam_subject = "CLAIM YOUR $10,000,000 LOTTERY WINNINGS TODAY!! URGENT WIRE TRANSFER";
$spam_body = "CONGRATULATIONS DEAR FRIEND! You have won the international mega jackpot lottery of $10,000,000 USD! Click here now to claim your prize wire transfer. Buy cheap viagra casino pills immediately!";
$spam_headers = [
    'from' => 'lottery-winner@free-money-scam-domain.xyz',
    'subject' => $spam_subject,
    'date' => date('r'),
];

$spam_result = $filter->classify($spam_body, $spam_headers, 0.70);
assert_true($spam_result['is_spam'] === true, "Obvious lottery/viagra spam classified as SPAM");
assert_true($spam_result['score'] >= 0.70, "Spam score meets or exceeds 0.70 threshold (got: " . round($spam_result['score'], 3) . ")");
assert_true(is_array($spam_result['reasons']) && count($spam_result['reasons']) > 0, "Spam result includes diagnostic reasons");

// Classify clear ham message
$ham_subject = "Quarterly financial invoice and meeting notes";
$ham_body = "Hi Koen,\n\nPlease find attached the quarterly consulting invoice for August, along with the project notes for our review call on Thursday afternoon at 2 PM CET.\n\nLooking forward to speaking with you.\n\nBest regards,\nSarah Miller\nFinance Director";
$ham_headers = [
    'from' => 'sarah.miller@trusted-partner.com',
    'subject' => $ham_subject,
    'date' => date('r'),
];

$ham_result = $filter->classify($ham_body, $ham_headers, 0.70);
assert_true($ham_result['is_spam'] === false, "Legitimate business email classified as HAM");
assert_true($ham_result['score'] < 0.50, "Ham score is low (got: " . round($ham_result['score'], 3) . ")");

// --- Test Group 2: Bi-Directional Continuous Learning & Auto-Correction ---
echo "\n--- Group 2: Bi-Directional Learning & Auto-Correction --- \n";

$sneaky_spam_subject = "Exclusive proposal regarding your website optimization";
$sneaky_spam_body = "Hello webmaster, we noticed several backlink opportunities for your platform. We offer guaranteed SEO rankings.";
$sneaky_headers = [
    'from' => 'proposals@marketing-coldoutreach.info',
    'subject' => $sneaky_spam_subject,
];

// Initial classification before learning
$pre_learn = $filter->classify($sneaky_spam_body, $sneaky_headers, 0.85);

// Train as SPAM
$learn_spam_res = $filter->learn_spam($sneaky_spam_body, $sneaky_headers);
assert_true($learn_spam_res['success'] === true, "learn_spam() executed successfully");
assert_true($learn_spam_res['spam_count'] === 1, "Spam message count incremented to 1");

$post_learn = $filter->classify($sneaky_spam_body, $sneaky_headers, 0.85);
assert_true($post_learn['score'] > $pre_learn['score'], "Spam score increased after training on message tokens (pre: " . round($pre_learn['score'], 3) . ", post: " . round($post_learn['score'], 3) . ")");

// Test AUTO-CORRECTION: User accidentally marked legitimate message as spam, now untags it (reverses spam mark & learns as ham)
$correct_res = $filter->learn_ham($sneaky_spam_body, $sneaky_headers, true);
assert_true($correct_res['success'] === true, "learn_ham(..., reverse_spam=true) executed successfully");
assert_true($correct_res['spam_count'] === 0, "Spam message count decremented back to 0 (un-learned)");
assert_true($correct_res['ham_count'] === 1, "Ham message count incremented to 1");

$post_correction = $filter->classify($sneaky_spam_body, $sneaky_headers, 0.85);
assert_true($post_correction['score'] < $post_learn['score'], "Score reduced significantly after reversing spam mark and training as ham (was: " . round($post_learn['score'], 3) . ", now: " . round($post_correction['score'], 3) . ")");

// --- Test Group 3: Whitelist & Blacklist Precedence ---
echo "\n--- Group 3: Whitelist and Blacklist Priority Matching --- \n";

// Set whitelist
$filter->set_whitelist(["vip-client.com", "boss@corp.com"]);
$wl_headers = [
    'from' => 'important@vip-client.com',
    'subject' => "Wire transfer jackpot prize notification", // Would otherwise look like spam
];
$wl_result = $filter->classify("Click here for your million dollar prize", $wl_headers);
assert_true($wl_result['is_spam'] === false, "Whitelisted sender is never marked as spam");
assert_true($wl_result['score'] === 0.0, "Whitelisted sender receives 0.0 score");
assert_true(strpos(implode(' ', $wl_result['reasons']), 'Whitelist') !== false, "Diagnostic confirms Whitelist match");

// Set blacklist
$filter->set_blacklist(["known-phisher.ru", "malicious-spammer.com"]);
$bl_headers = [
    'from' => 'support@known-phisher.ru',
    'subject' => "Harmless regular message",
];
$bl_result = $filter->classify("Hello just checking in.", $bl_headers);
assert_true($bl_result['is_spam'] === true, "Blacklisted sender is instantly marked as spam");
assert_true($bl_result['score'] === 1.0, "Blacklisted sender receives 1.0 score");
assert_true(strpos(implode(' ', $bl_result['reasons']), 'Blacklist') !== false, "Diagnostic confirms Blacklist match");

// Custom Keywords
$filter->set_custom_keywords(["crypto doubled", "nft giveaway instant"]);
$kw_result = $filter->classify("Join our nft giveaway instant now!", ['from' => 'random@test.com', 'subject' => 'test']);
assert_true(strpos(implode(' ', $kw_result['reasons']), 'keyword') !== false, "Custom high-risk keyword detected and flagged in reasons");

// --- Test Group 4: Header Heuristics (SPF, DKIM, DMARC) ---
echo "\n--- Group 4: Header Authentication & Security Heuristics --- \n";

$failed_auth_headers = [
    'from' => 'security@paypal.com',
    'subject' => 'Account Verification Required',
    'authentication-results' => 'mx.google.com; spf=fail (google.com: domain does not designate 1.2.3.4 as permitted sender) dkim=fail (bad sig) dmarc=fail',
    'received-spf' => 'Fail (protection.outlook.com: domain of paypal.com does not designate 1.2.3.4 as permitted sender)',
];
$auth_result = $filter->classify("Please verify your account password immediately.", $failed_auth_headers);
assert_true(strpos(implode(' ', $auth_result['reasons']), 'SPF') !== false || strpos(implode(' ', $auth_result['reasons']), 'Authentication') !== false, "Header heuristics detect SPF / DKIM authentication failure");

// --- Test Group 5: Disk Persistence & Database Reset ---
echo "\n--- Group 5: Atomic Storage Persistence & Database Reset --- \n";

// Train on a few more items
$filter->learn_spam("Viagra online pharmacy discounts pills", ['from' => 'rx@pharma.biz', 'subject' => 'Discounts']);
$filter->learn_ham("Meeting minutes from yesterday", ['from' => 'colleague@work.org', 'subject' => 'Minutes']);

// Instantiate a new filter instance pointing to the same user and folder to verify disk reload
$reloaded_filter = new LpaiSpamFilter('test_user@example.com', $test_data_dir);
$reloaded_stats = $reloaded_filter->get_stats();

assert_true($reloaded_stats['spam_messages'] >= 1, "Spam message count persisted across instances");
assert_true($reloaded_stats['ham_messages'] >= 2, "Ham message count persisted across instances");
assert_true($reloaded_stats['total_tokens'] > 0, "Learned tokens persisted in JSON store");

// Test Reset
$reset_res = $reloaded_filter->reset_database();
assert_true($reset_res === true, "reset_database() returned true");
$cleared_stats = $reloaded_filter->get_stats();
assert_true($cleared_stats['spam_messages'] === 0, "Spam count reset to 0");
assert_true($cleared_stats['ham_messages'] === 0, "Ham count reset to 0");

// --- Test Group 6: lifeprisma_ai.php Integration & Hooks ---
echo "\n--- Group 6: lifeprisma_ai.php Integration & Hooks --- \n";

$php_code = file_get_contents(__DIR__ . '/../lifeprisma_ai.php');

assert_true(strpos($php_code, "LpaiSpamFilter.php") !== false, "lifeprisma_ai.php requires LpaiSpamFilter.php");
assert_true(strpos($php_code, "plugin.lifeprisma_ai_spam_tag") !== false, "Registers plugin.lifeprisma_ai_spam_tag action");
assert_true(strpos($php_code, "plugin.lifeprisma_ai_spam_untag") !== false, "Registers plugin.lifeprisma_ai_spam_untag action");
assert_true(strpos($php_code, "plugin.lifeprisma_ai_spam_stats") !== false, "Registers plugin.lifeprisma_ai_spam_stats action");
assert_true(strpos($php_code, "plugin.lifeprisma_ai_spam_reset") !== false, "Registers plugin.lifeprisma_ai_spam_reset action");
assert_true(strpos($php_code, "plugin.lifeprisma_ai_spam_batch_train") !== false, "Registers plugin.lifeprisma_ai_spam_batch_train action");

assert_true(strpos($php_code, "function handle_new_messages") !== false, "Defines handle_new_messages hook handler");
assert_true(strpos($php_code, "function handle_messages_move") !== false, "Defines handle_messages_move hook handler");
assert_true(strpos($php_code, "function spam_preferences_list") !== false, "Defines spam_preferences_list for Settings section");
assert_true(strpos($php_code, "function spam_preferences_save") !== false, "Defines spam_preferences_save for Settings section");
assert_true(strpos($php_code, "function get_spam_filter") !== false, "Defines get_spam_filter helper");
assert_true(strpos($php_code, "resolve_junk_folder") !== false || strpos($php_code, "get_junk_folder") !== false, "Defines Junk folder resolver");
assert_true(strpos($php_code, "Junk") !== false && strpos($php_code, "\$Label1") !== false, "Sets Junk and \$Label1 (Red badge) flags on spam");

// --- Test Group 7: Background Worker Integration ---
echo "\n--- Group 7: Background CLI Worker Spam Integration --- \n";

$worker_code = file_get_contents(__DIR__ . '/../bin/worker.php');

assert_true(strpos($worker_code, "LpaiSpamFilter.php") !== false, "bin/worker.php requires LpaiSpamFilter.php");
assert_true(strpos($worker_code, "resolve_junk_folder") !== false, "LpaiImapClient implements resolve_junk_folder()");
assert_true(strpos($worker_code, "move_message") !== false, "LpaiImapClient implements move_message()");
assert_true(strpos($worker_code, "remove_flags") !== false, "LpaiImapClient implements remove_flags()");
assert_true(strpos($worker_code, "[SPAM DETECTED]") !== false, "Worker logs [SPAM DETECTED] when intercepting spam");
assert_true(strpos($worker_code, "continue;") !== false, "Worker skips auto-draft reply when message is routed to Junk");

// --- Test Group 8: JavaScript Frontend Bundle & Actions ---
echo "\n--- Group 8: Frontend JavaScript Verification --- \n";

$js_src = file_get_contents(__DIR__ . '/../src/lifeprisma_ai.js');
$js_min = file_get_contents(__DIR__ . '/../lifeprisma_ai.min.js');
$js_elastic = file_get_contents(__DIR__ . '/../skins/elastic/lifeprisma_ai.min.js');
$js_gmail = file_get_contents(__DIR__ . '/../skins/gmail_plus/lifeprisma_ai.min.js');

assert_true(strpos($js_src, "lpai_mark_spam") !== false, "src/lifeprisma_ai.js defines lpai_mark_spam()");
assert_true(strpos($js_src, "lpai_mark_ham") !== false, "src/lifeprisma_ai.js defines lpai_mark_ham()");
assert_true(strpos($js_src, "lpai_reset_spam_db") !== false, "src/lifeprisma_ai.js defines lpai_reset_spam_db()");
assert_true(strpos($js_src, "lpai_batch_train_folder") !== false, "src/lifeprisma_ai.js defines lpai_batch_train_folder()");
assert_true(strpos($js_src, "lpai-spam-banner") !== false, "src/lifeprisma_ai.js creates .lpai-spam-banner");
assert_true(strpos($js_src, "lpai-spam-badge") !== false, "src/lifeprisma_ai.js creates .lpai-spam-badge");
assert_true(strpos($js_src, "plugin.lifeprisma_ai_spam_tag") !== false, "src/lifeprisma_ai.js calls spam_tag endpoint");
assert_true(strpos($js_src, "plugin.lifeprisma_ai_spam_untag") !== false, "src/lifeprisma_ai.js calls spam_untag endpoint");

assert_true(strpos($js_src, "confirm_spam") !== false, "src/lifeprisma_ai.js looks up confirm_spam localization");
assert_true(strpos($js_src, "rcmail.confirm") !== false && strpos($js_src, "window.confirm") !== false, "src/lifeprisma_ai.js prompts confirmation dialog before spam marking");

// Bundle synchronization
assert_true(strpos($js_min, "lpai_mark_spam") !== false, "lifeprisma_ai.min.js contains compiled lpai_mark_spam");
assert_true(strpos($js_min, "confirm_spam") !== false, "lifeprisma_ai.min.js contains compiled confirm_spam logic");
assert_true(strpos($js_elastic, "lpai_mark_spam") !== false, "skins/elastic contains compiled lpai_mark_spam");
assert_true(strpos($js_gmail, "lpai_mark_spam") !== false, "skins/gmail_plus contains compiled lpai_mark_spam");
assert_true($js_min === $js_elastic && $js_min === $js_gmail, "All 3 minified JS files are 100% byte-for-byte identical");

// Localization verification
$labels = [];
include __DIR__ . '/../localization/en_US.inc';
assert_true(!empty($labels['confirm_spam']), "localization/en_US.inc defines confirm_spam");

$labels = [];
include __DIR__ . '/../localization/nl_NL.inc';
assert_true(!empty($labels['confirm_spam']), "localization/nl_NL.inc defines confirm_spam");

// --- Test Group 9: CSS Skin Styles Verification ---
echo "\n--- Group 9: Skin CSS Styles Verification --- \n";

$el_css = file_get_contents(__DIR__ . '/../skins/elastic/style.css');
$el_min = file_get_contents(__DIR__ . '/../skins/elastic/style.min.css');
$gp_css = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.css');
$gp_min = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.min.css');

assert_true(strpos($el_css, ".lpai-spam-badge") !== false, "elastic style.css defines .lpai-spam-badge");
assert_true(strpos($el_min, ".lpai-spam-badge") !== false, "elastic style.min.css contains .lpai-spam-badge");
assert_true(strpos($gp_css, ".lpai-spam-badge") !== false, "gmail_plus style.css defines .lpai-spam-badge");
assert_true(strpos($gp_min, ".lpai-spam-badge") !== false, "gmail_plus style.min.css contains .lpai-spam-badge");

assert_true(strpos($el_css, ".lpai-spam-banner") !== false, "elastic style.css defines .lpai-spam-banner");
assert_true(strpos($el_min, ".lpai-spam-banner") !== false, "elastic style.min.css contains .lpai-spam-banner");
assert_true(strpos($gp_css, ".lpai-spam-banner") !== false, "gmail_plus style.css defines .lpai-spam-banner");
assert_true(strpos($gp_min, ".lpai-spam-banner") !== false, "gmail_plus style.min.css contains .lpai-spam-banner");

assert_true(strpos($el_css, ".lpai-spam-stat-card") !== false, "elastic style.css defines .lpai-spam-stat-card");
assert_true(strpos($gp_css, ".lpai-spam-stat-card") !== false, "gmail_plus style.css defines .lpai-spam-stat-card");

// --- Test Group 10: Configuration File Documentation ---
echo "\n--- Group 10: Configuration Defaults in config.inc.php.dist --- \n";

$config_dist = file_get_contents(__DIR__ . '/../config.inc.php.dist');
assert_true(strpos($config_dist, "lifeprisma_ai_spam_filter_enabled") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_filter_enabled");
assert_true(strpos($config_dist, "lifeprisma_ai_spam_action") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_action");
assert_true(strpos($config_dist, "lifeprisma_ai_spam_threshold") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_threshold");
assert_true(strpos($config_dist, "lifeprisma_ai_spam_auto_learn") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_auto_learn");
assert_true(strpos($config_dist, "lifeprisma_ai_spam_whitelist") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_whitelist");
assert_true(strpos($config_dist, "lifeprisma_ai_spam_blacklist") !== false, "config.inc.php.dist documents lifeprisma_ai_spam_blacklist");

// --- Test Group 11: 404 Asset Integrity & 500 Preview Prevention ---
echo "\n--- Group 11: 404 Assets & 500 Error Prevention --- \n";

assert_true(file_exists(__DIR__ . '/../skins/custom.css') && filesize(__DIR__ . '/../skins/custom.css') > 0, "skins/custom.css exists and is non-empty");
assert_true(file_exists(__DIR__ . '/../skins/elastic/custom.css') && filesize(__DIR__ . '/../skins/elastic/custom.css') > 0, "skins/elastic/custom.css exists and is non-empty");
assert_true(file_exists(__DIR__ . '/../skins/gmail_plus/custom.css') && filesize(__DIR__ . '/../skins/gmail_plus/custom.css') > 0, "skins/gmail_plus/custom.css exists and is non-empty");

assert_true(file_exists(__DIR__ . '/../skins/watermark.png') && filesize(__DIR__ . '/../skins/watermark.png') > 0, "skins/watermark.png exists and is non-empty");
assert_true(file_exists(__DIR__ . '/../skins/elastic/watermark.png') && filesize(__DIR__ . '/../skins/elastic/watermark.png') > 0, "skins/elastic/watermark.png exists and is non-empty");
assert_true(file_exists(__DIR__ . '/../skins/gmail_plus/watermark.png') && filesize(__DIR__ . '/../skins/gmail_plus/watermark.png') > 0, "skins/gmail_plus/watermark.png exists and is non-empty");

$watermark_bytes = file_get_contents(__DIR__ . '/../skins/watermark.png', false, null, 0, 8);
assert_true(substr($watermark_bytes, 1, 3) === 'PNG', "skins/watermark.png is a valid binary PNG file");

assert_true(strpos($php_code, "get_message_flags(") === false, "lifeprisma_ai.php does not call undefined get_message_flags (prevents 500 on preview)");
assert_true(strpos($php_code, "'flags' => \$flags") !== false, "fetch_message_context returns flags array");
assert_true(strpos($php_code, "[RENDER PREVIEW CONTEXT ERROR]") !== false, "render_page wraps message context extraction in try-catch to prevent 500 preview crash");

// --- Test Group 12: Theme-Uniform Spam Button in Sidebar & Topbar ---
echo "\n--- Group 12: Theme-Uniform Spam Button in Sidebar & Topbar --- \n";

assert_true(strpos($js_src, "isJunk ? 'notjunk' : 'junk'") !== false || strpos($js_src, "'junk'") !== false, "lifeprisma_ai.js sets standard junk class on topbar spam button");
assert_true(strpos($js_src, "lpai-sidebar-spam-btn") !== false, "lifeprisma_ai.js identifies sidebar spam button");
assert_true(strpos($js_src, "item.setAttribute('role', 'menuitem')") !== false || strpos($js_src, "role', 'menuitem'") !== false, "lifeprisma_ai.js wraps topbar menuitem in li with role=menuitem");
assert_true(strpos($js_src, "inner button-inner") !== false, "lifeprisma_ai.js creates standard inner button-inner markup");
assert_true(strpos($js_src, "btn.setAttribute('role', 'button')") !== false, "lifeprisma_ai.js sets role=button for accessibility");
assert_true(strpos($js_src, "btn.setAttribute('tabindex', '0')") !== false, "lifeprisma_ai.js sets tabindex=0 for accessibility");

assert_true(strpos($el_css, ".toolbar a.button.lpai-toolbar-spam-btn") !== false, "elastic style.css styles .toolbar a.button.lpai-toolbar-spam-btn");
assert_true(strpos($el_min, "toolbar a.button.lpai-toolbar-spam-btn") !== false, "elastic style.min.css contains .toolbar a.button.lpai-toolbar-spam-btn");
assert_true(strpos($el_css, "RcpIconFont") !== false, "elastic style.css declares RcpIconFont for spam button font icons");
assert_true(strpos($el_css, "\\ead3") !== false || strpos($el_css, "\\eaf7") !== false, "elastic style.css defines standard font icon glyph for spam");
assert_true(strpos($el_css, "\\ec76") !== false, "elastic style.css maps outline shield icon glyph for spam (\\ec76)");
assert_true(strpos($el_min, "\\ec76") !== false, "elastic style.min.css contains outline shield glyph (\\ec76)");
assert_true(strpos($el_css, "#layout-sidebar a.button.lpai-toolbar-spam-btn") !== false, "elastic style.css adapts button for sidebar");

assert_true(strpos($gp_css, ".toolbar a.button.lpai-toolbar-spam-btn") !== false, "gmail_plus style.css styles .toolbar a.button.lpai-toolbar-spam-btn");
assert_true(strpos($gp_min, "toolbar a.button.lpai-toolbar-spam-btn") !== false, "gmail_plus style.min.css contains .toolbar a.button.lpai-toolbar-spam-btn");
assert_true(strpos($gp_css, "RcpIconFont") !== false, "gmail_plus style.css declares RcpIconFont font family");
assert_true(strpos($gp_css, "\\ec76") !== false, "gmail_plus style.css maps outline shield icon glyph for spam (\\ec76)");
assert_true(strpos($gp_min, "\\ec76") !== false, "gmail_plus style.min.css contains outline shield glyph (\\ec76)");
assert_true(strpos($gp_css, "\\ed2b") !== false, "gmail_plus style.css maps material icon glyph for spam (\\ed2b)");
assert_true(strpos($gp_css, "\\ed2c") !== false, "gmail_plus style.css maps material icon glyph for ham (\\ed2c)");
assert_true(strpos($gp_min, "\\ed2b") !== false, "gmail_plus style.min.css contains material spam glyph");
assert_true(strpos($gp_css, "#layout-sidebar a.button.lpai-toolbar-spam-btn") !== false, "gmail_plus style.css adapts button for sidebar");
assert_true(strpos($gp_css, "border-radius: 18px") !== false || strpos($gp_css, "border-radius:18px") !== false, "gmail_plus uses theme-uniform pill button shape");
assert_true(strpos($js_src, "#layout-menu, #taskmenu, .sidebar, #layout-sidebar, #folderlist-footer") !== false, "lifeprisma_ai.js excludes right side menu bar / sidebar from spam button injection");
assert_true(strpos($gp_css, "#layout-menu .lpai-toolbar-spam-btn") !== false && strpos($gp_css, "#layout-menu a.junk") !== false, "gmail_plus style.css suppresses spam buttons in right side menu bar (#layout-menu)");
assert_true(strpos($el_css, "#layout-menu .lpai-toolbar-spam-btn") !== false && strpos($el_css, "#layout-menu a.junk") !== false, "elastic style.css suppresses spam buttons in right side menu bar (#layout-menu)");
assert_true(strpos($js_src, "button-gemini-ai xi-ai") !== false, "lifeprisma_ai.js sets xi-ai outline icon class on sidebar AI button");
assert_true(strpos($gp_css, "\\ec89") !== false, "gmail_plus style.css maps outline ai icon glyph (\\ec89)");
assert_true(strpos($gp_min, "\\ec89") !== false, "gmail_plus style.min.css contains outline ai glyph (\\ec89)");
assert_true(strpos($el_css, "\\ec89") !== false, "elastic style.css maps outline ai icon glyph (\\ec89)");
assert_true(strpos($el_min, "\\ec89") !== false, "elastic style.min.css contains outline ai glyph (\\ec89)");
assert_true(strpos($gp_css, "top: 0.6rem !important;") !== false, "gmail_plus style.css offsets spam button icon with top: 0.6rem to align with siblings");
assert_true(strpos($el_css, "top: 0.6rem !important;") !== false, "elastic style.css offsets spam button icon with top: 0.6rem to align with siblings");
assert_true(strpos($gp_css, "#mailtoolbar a.junk") !== false, "gmail_plus style.css targets #mailtoolbar a.junk for base toolbar sizing");
assert_true(strpos($el_css, "#mailtoolbar a.junk") !== false, "elastic style.css targets #mailtoolbar a.junk for base toolbar sizing");
assert_true(strpos($gp_css, "font-size: 1.5em !important;") !== false, "gmail_plus style.css aligns spam icon font size with sibling buttons (1.5em)");
assert_true(strpos($el_css, "font-size: 1.5em !important;") !== false, "elastic style.css aligns spam icon font size with sibling buttons (1.5em)");
assert_true(strpos($gp_css, "display: inline-flex !important;") !== false, "gmail_plus style.css uses inline-flex for toolbar spam button alignment");
assert_true(strpos($el_css, "display: inline-flex !important;") !== false, "elastic style.css uses inline-flex for toolbar spam button alignment");

// Cleanup test scratch directory
array_map('unlink', glob("{$test_data_dir}/*.*"));
@rmdir($test_data_dir);

echo "\n============================================\n";
echo "TEST RESULTS: {$passed_count} / {$test_count} tests passed\n";
if ($passed_count === $test_count) {
    echo "*** ALL ADVANCED SPAM FILTER TESTS PASSED (100%) ***\n";
    exit(0);
} else {
    echo "!!! SOME SPAM FILTER TESTS FAILED !!!\n";
    exit(1);
}
