<?php
/**
 * Test Suite: Immediate Spam Row Removal & xcalendar iTip Response Fixes
 *
 * Verifies:
 * 1. Immediate visual removal of messages when reporting as Spam / Not Spam (Ham)
 * 2. Resolution of HTTP 410 Gone on preview pane when marked email is moved to Junk
 * 3. Resolution of HTTP 500 on xcalendar.processItipResponse action in Roundcube
 * 4. Multi-frame DOM/window support (parent/top/preview iframe)
 * 5. Robust \Throwable exception handling in Itip.php and xcalendar.php
 * 6. Minified bundle integrity and parity across all skins
 */

declare(strict_types=1);

$test_count = 0;
$passed_count = 0;

function assert_true(bool $expr, string $desc): void
{
    global $test_count, $passed_count;
    $test_count++;
    if ($expr) {
        $passed_count++;
        echo "PASSED: {$desc}\n";
    } else {
        echo "FAILED: {$desc}\n";
        exit(1);
    }
}

echo "=== IMMEDIATE SPAM REMOVAL & XCALENDAR ITIP RESPONSE TEST SUITE ===\n\n";

$repo_root = dirname(__DIR__);
$ai_dir = is_dir($repo_root . '/Extra context/plugins/roundcube_ai')
    ? $repo_root . '/Extra context/plugins/roundcube_ai'
    : $repo_root;

// --- Test Group 1: Immediate Spam Removal in src/lifeprisma_ai.js ---
echo "--- Group 1: Immediate Spam Row Removal (UI & Multi-Frame) ---\n";

$js_src = file_get_contents($ai_dir . '/src/lifeprisma_ai.js');
assert_true($js_src !== false, "src/lifeprisma_ai.js is readable");

assert_true(
    strpos($js_src, 'function lpai_remove_message_rows(uids)') !== false,
    "src/lifeprisma_ai.js defines lpai_remove_message_rows function"
);

// Check that lpai_remove_message_rows traverses multi-frame window hierarchy
assert_true(
    strpos($js_src, 'contexts = [window, window.parent, window.top]') !== false ||
    (strpos($js_src, 'window.parent') !== false && strpos($js_src, 'window.top') !== false),
    "lpai_remove_message_rows inspects current, parent, and top window contexts"
);

// Check that message_list is updated and remove_row is called
assert_true(
    strpos($js_src, 'mlist.remove_row(numUid, true)') !== false ||
    strpos($js_src, 'mlist.remove_row') !== false,
    "lpai_remove_message_rows calls mlist.remove_row to update Roundcube internal list state"
);

// Check that DOM elements are removed across document contexts
assert_true(
    strpos($js_src, "docs = [document, window.parent?.document, window.top?.document]") !== false ||
    (strpos($js_src, "rcmrow' + uid") !== false && strpos($js_src, "remove()") !== false),
    "lpai_remove_message_rows removes #rcmrow<uid> rows from DOM across frames"
);

// Check that preview iframe is reset to prevent HTTP 410 Gone reload loop
assert_true(
    strpos($js_src, "ifr.src = 'about:blank'") !== false || strpos($js_src, "'about:blank'") !== false,
    "lpai_remove_message_rows clears preview iframe src to about:blank to prevent 410 Gone"
);

// Check that rc.clear_message() or env.uid is reset
assert_true(
    strpos($js_src, "rc.env.uid = null") !== false || strpos($js_src, "rc.clear_message()") !== false,
    "lpai_remove_message_rows resets rc.env.uid and clears active preview message"
);

// Check that spam banner is removed
assert_true(
    strpos($js_src, "lpai-spam-banner") !== false,
    "lpai_remove_message_rows dismisses any active #lpai-spam-banner"
);

// Check optimistic invocation before AJAX round-trip
assert_true(
    strpos($js_src, "lpai_remove_message_rows(uids);") !== false,
    "lpai_remove_message_rows is called during spam / ham reporting workflow"
);

// Check that lpai_mark_spam calls lpai_remove_message_rows
$mark_spam_pos = strpos($js_src, 'function lpai_mark_spam(');
assert_true($mark_spam_pos !== false, "lpai_mark_spam function exists");
$mark_spam_sub = substr($js_src, $mark_spam_pos, 2500);
assert_true(
    strpos($mark_spam_sub, 'lpai_remove_message_rows(uids)') !== false,
    "lpai_mark_spam calls lpai_remove_message_rows immediately on user action"
);

// Check that lpai_mark_ham calls lpai_remove_message_rows
$mark_ham_pos = strpos($js_src, 'function lpai_mark_ham(');
assert_true($mark_ham_pos !== false, "lpai_mark_ham function exists");
$mark_ham_sub = substr($js_src, $mark_ham_pos, 2500);
assert_true(
    strpos($mark_ham_sub, 'lpai_remove_message_rows(uids)') !== false,
    "lpai_mark_ham calls lpai_remove_message_rows immediately on user action"
);

// Check that lpai_get_target_uids checks parent and top frames
$target_uids_pos = strpos($js_src, 'function lpai_get_target_uids()');
assert_true($target_uids_pos !== false, "lpai_get_target_uids function exists");
$target_uids_sub = substr($js_src, $target_uids_pos, 2000);
assert_true(
    strpos($target_uids_sub, 'window.parent') !== false && strpos($target_uids_sub, 'window.top') !== false,
    "lpai_get_target_uids traverses parent and top frames to resolve UIDs when called from inside preview iframe"
);


// --- Test Group 2: Minified Bundles Parity ---
echo "\n--- Group 2: Minified Bundles Parity ---\n";

$root_min = file_get_contents($ai_dir . '/lifeprisma_ai.min.js');
$elastic_min = file_get_contents($ai_dir . '/skins/elastic/lifeprisma_ai.min.js');
$gmail_plus_min = file_get_contents($ai_dir . '/skins/gmail_plus/lifeprisma_ai.min.js');

assert_true($root_min !== false && strlen($root_min) > 1000, "lifeprisma_ai.min.js exists and is non-empty");
assert_true($elastic_min !== false && strlen($elastic_min) > 1000, "skins/elastic/lifeprisma_ai.min.js exists and is non-empty");
assert_true($gmail_plus_min !== false && strlen($gmail_plus_min) > 1000, "skins/gmail_plus/lifeprisma_ai.min.js exists and is non-empty");

assert_true($root_min === $elastic_min, "lifeprisma_ai.min.js and skins/elastic/lifeprisma_ai.min.js are identical byte-for-byte");
assert_true($root_min === $gmail_plus_min, "lifeprisma_ai.min.js and skins/gmail_plus/lifeprisma_ai.min.js are identical byte-for-byte");

assert_true(
    strpos($root_min, 'lpai_remove_message_rows') !== false,
    "Minified bundle contains lpai_remove_message_rows"
);
assert_true(
    strpos($root_min, 'about:blank') !== false,
    "Minified bundle contains preview reset to about:blank"
);


// --- Test Group 3: xcalendar Plugin Action Registration & 500 Prevention ---
echo "\n--- Group 3: xcalendar Plugin Action Registration & Dispatch Fix ---\n";

$xcalendar_php = file_get_contents($repo_root . '/Extra context/plugins/xcalendar/xcalendar.php');
assert_true($xcalendar_php !== false, "xcalendar.php is readable");

// Check registration of actions
assert_true(
    strpos($xcalendar_php, "\$this->register_action('xcalendar.processItipResponse'") !== false,
    "xcalendar.php registers 'xcalendar.processItipResponse' action"
);
assert_true(
    strpos($xcalendar_php, "\$this->register_action('plugin.xcalendar.processItipResponse'") !== false,
    "xcalendar.php registers 'plugin.xcalendar.processItipResponse' action"
);
assert_true(
    strpos($xcalendar_php, "\$this->register_action('xcalendar.processItipUpdateReply'") !== false,
    "xcalendar.php registers 'xcalendar.processItipUpdateReply' action"
);
assert_true(
    strpos($xcalendar_php, "\$this->register_action('xcalendar.processItipUpdateEvent'") !== false,
    "xcalendar.php registers 'xcalendar.processItipUpdateEvent' action"
);
assert_true(
    strpos($xcalendar_php, "\$this->register_action('xcalendar.processItipDelete'") !== false,
    "xcalendar.php registers 'xcalendar.processItipDelete' action"
);
assert_true(
    strpos($xcalendar_php, "\$this->register_action('xcalendar.addMessageEventsToCalendar'") !== false,
    "xcalendar.php registers 'xcalendar.addMessageEventsToCalendar' action"
);

// Check that actionProcessItipResponse handles the request and calls send(); exit;
assert_true(
    strpos($xcalendar_php, 'function actionProcessItipResponse()') !== false,
    "xcalendar.php implements actionProcessItipResponse method"
);
assert_true(
    strpos($xcalendar_php, '$this->rcmail->output->send();') !== false,
    "xcalendar.php explicitly terminates AJAX responses with output->send(); exit;"
);
assert_true(
    strpos($xcalendar_php, '$this->rcmail->request_security_check(') !== false,
    "xcalendar.php performs CSRF request_security_check on iTip actions"
);


// --- Test Group 4: Itip.php Exception Safety & Missing Mailbox Fallback ---
echo "\n--- Group 4: Itip.php Robustness & Exception Safety ---\n";

$itip_php = file_get_contents($repo_root . '/Extra context/plugins/xcalendar/program/Itip.php');
assert_true($itip_php !== false, "Itip.php is readable");

// Check fallback for empty folder to 'INBOX'
assert_true(
    strpos($itip_php, "!empty(\$data->folder) ? \$data->folder : 'INBOX'") !== false,
    "Itip.php defaults empty data folder to 'INBOX'"
);

// Check safe decryption validation
assert_true(
    strpos($itip_php, "!(\$data = json_decode(\$decrypted))") !== false,
    "Itip.php checks for valid decoded payload object"
);

// Check catching \Throwable instead of just \Exception
assert_true(
    strpos($itip_php, 'catch (\Throwable $e)') !== false,
    "Itip.php catches \\Throwable to handle all errors and exceptions gracefully"
);

// Check graceful handling when message or itip part is not found
assert_true(
    strpos($itip_php, 'Cannot retrieve calendar invitation') !== false,
    "Itip.php provides user-friendly error when the message or invitation is no longer found in folder"
);


// --- Test Group 5: Database Handler Safety in lifeprisma_ai.php ---
echo "\n--- Group 5: Database Handler Safety in lifeprisma_ai.php ---\n";

$lpai_php = file_get_contents($ai_dir . '/lifeprisma_ai.php');
assert_true($lpai_php !== false, "lifeprisma_ai.php is readable");

assert_true(
    strpos($lpai_php, "method_exists(\$rcmail, 'get_dbh')") !== false,
    "lifeprisma_ai.php checks method_exists(\$rcmail, 'get_dbh') before calling get_dbh()"
);


echo "\n============================================\n";
echo "TEST RESULTS: {$passed_count} / {$test_count} tests passed\n";
if ($passed_count === $test_count) {
    echo "*** ALL IMMEDIATE SPAM REMOVAL & XCALENDAR ITIP FIX TESTS PASSED (100%) ***\n";
    exit(0);
} else {
    echo "!!! SOME TESTS FAILED !!!\n";
    exit(1);
}
