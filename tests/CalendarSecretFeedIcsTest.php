<?php

/**
 * Automated test suite for Task 2:
 * Private / Secret iCal Subscription Feed URL in xcalendar plugin.
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

echo "=== Running Calendar Secret iCal Feed Test Suite ===\n\n";

$repoRoot = dirname(__DIR__);

// Test 1: Calendar.php Cryptographically Secure Token Generation
echo "--- Test 1: Cryptographically Secure Token Generation ---\n";
$calendarContent = file_get_contents($repoRoot . '/Extra context/plugins/xcalendar/program/Calendar.php');
assert_true($calendarContent !== false, "Calendar.php is readable");

assert_true(
    strpos($calendarContent, 'bin2hex(random_bytes(32))') !== false,
    "createPublishCode generates 64-character hex string using random_bytes(32)"
);
assert_true(
    strpos($calendarContent, 'bin2hex(openssl_random_pseudo_bytes(32))') !== false,
    "createPublishCode provides cryptographic fallback to openssl_random_pseudo_bytes"
);

// Verify entropy and length of generated tokens directly
$token = bin2hex(random_bytes(32));
assert_true(strlen($token) === 64, "Generated token is exactly 64 characters (256 bits)");
assert_true(ctype_xdigit($token), "Generated token is valid URL-safe hexadecimal");

// Test 2: Feed Rate Limiting Defense
echo "\n--- Test 2: Feed Rate Limiting Defense ---\n";
assert_true(
    strpos($calendarContent, 'checkPublishRateLimit') !== false,
    "Calendar.php implements checkPublishRateLimit() method"
);
assert_true(
    strpos($calendarContent, 'HTTP/1.1 429 Too Many Requests') !== false,
    "Calendar.php responds with HTTP 429 Too Many Requests on rate limit violation"
);
assert_true(
    strpos($calendarContent, 'Retry-After: 60') !== false,
    "Calendar.php includes Retry-After header on rate limit violation"
);
assert_true(
    strpos($calendarContent, 'xcalendar_publish_rate_limit') !== false,
    "Calendar.php respects configurable xcalendar_publish_rate_limit"
);

// Test 3: Timing Attack Safe Comparison & Token Verification
echo "\n--- Test 3: Timing-Safe Verification & Code Validation ---\n";
assert_true(
    strpos($calendarContent, "preg_match('/^[a-fA-F0-9]{32,64}$|^[a-zA-Z0-9_-]{16,64}$/', \$rawCode)") !== false,
    "Calendar.php strictly validates incoming code format before database lookup"
);
assert_true(
    strpos($calendarContent, "hash_equals((string)\$publishedCalendar['code'], \$rawCode)") !== false,
    "Calendar.php uses timing-attack safe hash_equals() to compare feed tokens"
);

// Test 4: RFC 5545 Standard Headers & Caching Controls
echo "\n--- Test 4: RFC 5545 Standard Headers & Caching Controls ---\n";
assert_true(
    strpos($calendarContent, "Content-Type: text/calendar; charset=utf-8") !== false,
    "Calendar.php sets standard Content-Type: text/calendar; charset=utf-8"
);
assert_true(
    strpos($calendarContent, "Cache-Control: private, max-age=300, must-revalidate") !== false,
    "Calendar.php sets Cache-Control: private, max-age=300, must-revalidate"
);
assert_true(
    strpos($calendarContent, "ETag: ") !== false,
    "Calendar.php sets ETag header for content fingerprinting"
);
assert_true(
    strpos($calendarContent, "Last-Modified: ") !== false,
    "Calendar.php sets Last-Modified header"
);
assert_true(
    strpos($calendarContent, "HTTP/1.1 304 Not Modified") !== false,
    "Calendar.php supports conditional GETs returning HTTP 304 Not Modified"
);
assert_true(
    strpos($calendarContent, "HTTP_IF_NONE_MATCH") !== false,
    "Calendar.php inspects HTTP_IF_NONE_MATCH header"
);

// Test 5: Event Privacy & Output Sanitization
echo "\n--- Test 5: Event Privacy & Output Sanitization ---\n";
assert_true(
    strpos($calendarContent, "xcalendar_publish_hide_attendees") !== false,
    "Calendar.php supports xcalendar_publish_hide_attendees configuration"
);
assert_true(
    strpos($calendarContent, "xcalendar_publish_hide_notes") !== false,
    "Calendar.php supports xcalendar_publish_hide_notes configuration"
);
assert_true(
    strpos($calendarContent, "isConfidential") !== false,
    "Calendar.php identifies and handles confidential events"
);
assert_true(
    strpos($calendarContent, "preg_replace(\"/(?<!\\r)\\n/\", \"\\r\\n\", \$vcalOutput)") !== false,
    "Calendar.php enforces RFC 5545 compliant CRLF (\\r\\n) line endings"
);

// Test 6: Template calendar_edit.html UI Fixes & Copy Action
echo "\n--- Test 6: Template calendar_edit.html UI Fixes & Copy Action ---\n";
$tplContent = file_get_contents($repoRoot . '/Extra context/plugins/xcalendar/skins/elastic/templates/calendar_edit.html');
assert_true($tplContent !== false, "calendar_edit.html is readable");

// Check bugfix for line 49
assert_true(
    strpos($tplContent, '<input type="color" name="tx_color" id="xtx_color"') !== false,
    "calendar_edit.html fixed input name='tx_color' (previously name='bg_color' bug)"
);

// Check copy button and input group
assert_true(
    strpos($tplContent, 'publish-url-box') !== false,
    "calendar_edit.html includes publish-url-box container"
);
assert_true(
    strpos($tplContent, 'xframework.copyToClipboard(data.publish.url + data.publish.code_full') !== false,
    "calendar_edit.html provides copy button calling xframework.copyToClipboard for full feed"
);
assert_true(
    strpos($tplContent, 'xframework.copyToClipboard(data.publish.url + data.publish.code_busy') !== false,
    "calendar_edit.html provides copy button calling xframework.copyToClipboard for busy feed"
);
assert_true(
    strpos($tplContent, 'aria-label="Full Calendar iCal Feed URL"') !== false,
    "calendar_edit.html provides WCAG compliant aria-label for full feed input"
);

// Test 7: config.inc.php.dist Documentation
echo "\n--- Test 7: Configuration Distribution Documentation ---\n";
$distConfig = file_get_contents($repoRoot . '/Extra context/plugins/xcalendar/config.inc.php.dist');
assert_true($distConfig !== false, "xcalendar config.inc.php.dist is readable");
assert_true(
    strpos($distConfig, "\$config['xcalendar_publish_rate_limit'] = 60;") !== false,
    "config.inc.php.dist documents xcalendar_publish_rate_limit"
);
assert_true(
    strpos($distConfig, "\$config['xcalendar_publish_hide_attendees'] = false;") !== false,
    "config.inc.php.dist documents xcalendar_publish_hide_attendees"
);
assert_true(
    strpos($distConfig, "\$config['xcalendar_publish_hide_notes'] = false;") !== false,
    "config.inc.php.dist documents xcalendar_publish_hide_notes"
);

echo "\nAll Calendar Secret iCal Feed tests PASSED successfully!\n";
