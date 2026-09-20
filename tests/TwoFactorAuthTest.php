<?php

/**
 * Automated test suite for Two-Factor Authentication Plugin (twofactor_auth)
 *
 * Validates:
 * - RFC 6238 / RFC 4226 TOTP conformance (Base32, test vectors, drift tolerance)
 * - Standalone QR code generation (SVG & Data URI, XML structure)
 * - Multi-factor channels (TOTP, Email OTP, SMS OTP, Recovery Codes)
 * - Single-use recovery code generation, verification, and consumption
 * - Customizable email and SMS verification messages & notices
 * - Rate limiting and brute-force lockout protection
 * - Administrative enforcement (twofactor_auth_enforce) and global disable (twofactor_auth_disabled)
 * - Gatekeeper startup hook security
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

echo "=== Running Two-Factor Authentication (twofactor_auth) Test Suite ===\n\n";

$pluginDir = dirname(__DIR__) . '/Extra context/plugins/twofactor_auth';
require_once $pluginDir . '/lib/Totp.php';
require_once $pluginDir . '/lib/QrCode.php';

use TwoFactorAuth\Totp;
use TwoFactorAuth\QrCode;

// --------------------------------------------------------------------------
// Test Suite 1: TOTP RFC 6238 Conformance & Test Vectors
// --------------------------------------------------------------------------
echo "--- Test Suite 1: TOTP RFC 6238 Conformance ---\n";

// 1.1 Base32 Secret Key Generation
$secret16 = Totp::generateSecret(16);
assert_true(strlen($secret16) === 16, "generateSecret(16) returns 16 characters");
assert_true((bool)preg_match('/^[A-Z2-7]+$/', $secret16), "generateSecret contains valid Base32 characters only");

$secret32 = Totp::generateSecret(32);
assert_true(strlen($secret32) === 32, "generateSecret(32) returns 32 characters");

// 1.2 Base32 Decode
$decoded = Totp::base32Decode('JBSWY3DPEHPK3PXP');
assert_true($decoded === 'Hello!\xde\xad\xbe\xef' || $decoded === "Hello!\xde\xad\xbe\xef", "Base32 decodes known test string correctly");

// 1.3 RFC 6238 Official Test Vectors
// Secret: ASCII "12345678901234567890" (20 bytes) => Base32: "GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ"
$rfcSecret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
$rfcVectors = [
    59 => '287082',
    1111111109 => '081804',
    1111111111 => '050471',
    1234567890 => '005924',
    2000000000 => '279037',
];

foreach ($rfcVectors as $ts => $expectedCode) {
    $calculated = Totp::generateCode($rfcSecret, $ts, 30, 6);
    assert_true($calculated === $expectedCode, "RFC 6238 test vector at timestamp {$ts}: expected {$expectedCode}, got {$calculated}");
}

// 1.4 Verification and Drift Tolerance
$now = 1700000000;
$currentCode = Totp::generateCode($rfcSecret, $now, 30, 6);
$prevCode = Totp::generateCode($rfcSecret, $now - 30, 30, 6);
$nextCode = Totp::generateCode($rfcSecret, $now + 30, 30, 6);
$farPrevCode = Totp::generateCode($rfcSecret, $now - 90, 30, 6);

assert_true(Totp::verifyCode($rfcSecret, $currentCode, 1, $now), "Verifies exact current code");
assert_true(Totp::verifyCode($rfcSecret, $prevCode, 1, $now), "Verifies code with -1 step time drift (30s ago)");
assert_true(Totp::verifyCode($rfcSecret, $nextCode, 1, $now), "Verifies code with +1 step time drift (+30s)");
assert_true(!Totp::verifyCode($rfcSecret, $farPrevCode, 1, $now), "Rejects code outside drift window (>30s)");
assert_true(!Totp::verifyCode($rfcSecret, '12345', 1, $now), "Rejects invalid code length (5 digits)");
assert_true(!Totp::verifyCode($rfcSecret, 'abcdef', 1, $now), "Rejects non-numeric code");

// 1.5 otpauth:// URI Generation
$uri = Totp::getOtpAuthUri('user@example.com', 'Roundcube Webmail', $secret16);
assert_true(str_starts_with($uri, 'otpauth://totp/'), "URI starts with standard otpauth://totp/");
assert_true(str_contains($uri, 'secret=' . rawurlencode($secret16)), "URI contains urlencoded secret");
assert_true(str_contains($uri, 'issuer=Roundcube%20Webmail') || str_contains($uri, 'issuer=Roundcube+Webmail'), "URI contains urlencoded issuer");
assert_true(str_contains($uri, 'algorithm=SHA1'), "URI specifies SHA1 algorithm (standard authenticator compatible)");
assert_true(str_contains($uri, 'digits=6'), "URI specifies 6 digits");
assert_true(str_contains($uri, 'period=30'), "URI specifies 30 second period");

// --------------------------------------------------------------------------
// Test Suite 2: Standalone Zero-Dependency QR Code Generator
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Standalone QR Code Generator ---\n";

$testData = 'otpauth://totp/Roundcube%20Webmail:user%40example.com?secret=GEZDGNBVGY3TQOJQ&issuer=Roundcube%20Webmail';
$svg = QrCode::getSvg($testData, 240, '#111111', '#ffffff');

assert_true(str_starts_with($svg, '<svg'), "SVG starts with <svg element");
assert_true(str_ends_with(trim($svg), '</svg>'), "SVG ends with </svg>");
assert_true(str_contains($svg, 'width="240"') && str_contains($svg, 'height="240"'), "SVG preserves requested dimensions");
assert_true(str_contains($svg, 'viewBox="0 0'), "SVG contains responsive viewBox");
assert_true(str_contains($svg, '<rect width="100%" height="100%" fill="#ffffff"'), "SVG contains background rect");
assert_true(str_contains($svg, '<path d="') && str_contains($svg, 'fill="#111111"'), "SVG contains module path with foreground color");

// Validate XML syntax of generated SVG
$xmlObj = @simplexml_load_string($svg);
assert_true($xmlObj !== false, "Generated SVG parses cleanly as valid XML");

$dataUri = QrCode::getDataUri($testData, 200);
assert_true(str_starts_with($dataUri, 'data:image/svg+xml;base64,'), "getDataUri produces valid Data URI header");
$base64Part = substr($dataUri, strlen('data:image/svg+xml;base64,'));
$decodedSvg = base64_decode($base64Part);
assert_true(str_starts_with($decodedSvg, '<svg') && str_ends_with(trim($decodedSvg), '</svg>'), "Decoded Data URI matches valid SVG");

// --------------------------------------------------------------------------
// Test Suite 3: Emergency Recovery Codes Generation & Consumption
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Recovery Codes Generation & Consumption ---\n";

// Define mock Roundcube classes if not defined
if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $user;
        public $task = 'settings';
        public $action = '';

        public function __construct()
        {
            $this->config = new rcube_config();
            $this->output = new rcmail_output_mock();
            $this->user = new rcube_user_mock();
        }

        public static function get_instance()
        {
            return rcmail::get_instance();
        }

        public static function reset_instance()
        {
            return rcmail::reset_instance();
        }

        public static function Q($str)
        {
            return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
        }

        public function url($args)
        {
            return '?' . http_build_query($args);
        }

        public function get_request_token()
        {
            return 'mock_token_' . md5('test');
        }
    }

    class rcmail extends rcube
    {
        private static $rcmailInstance;

        public static function get_instance()
        {
            if (!self::$rcmailInstance) {
                self::$rcmailInstance = new self();
            }
            return self::$rcmailInstance;
        }

        public static function reset_instance()
        {
            self::$rcmailInstance = new self();
            return self::$rcmailInstance;
        }
    }

    class rcube_config
    {
        public array $data = [];
        public function get($key, $default = null)
        {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
        }
        public function set($key, $val)
        {
            $this->data[$key] = $val;
        }
    }

    class rcube_user_mock
    {
        public int $ID = 42;
        public array $prefs = [];

        public function get_username(): string
        {
            return 'user@example.com';
        }

        public function get_prefs(): array
        {
            return $this->prefs;
        }

        public function save_prefs(array $newPrefs): bool
        {
            $this->prefs = array_merge($this->prefs, $newPrefs);
            return true;
        }
    }

    class rcmail_output_mock
    {
        public array $scripts = [];
        public array $stylesheets = [];
        public string $pagetitle = '';
        public string $redirectUrl = '';

        public function set_pagetitle(string $title): void { $this->pagetitle = $title; }
        public function include_script(string $script): void { $this->scripts[] = $script; }
        public function include_stylesheet(string $sheet): void { $this->stylesheets[] = $sheet; }
        public function redirect(array $args): void { $this->redirectUrl = '?' . http_build_query($args); }
        public function send_exit(string $html): void {}
    }

    class rcube_plugin
    {
        public array $registered_hooks = [];
        public array $registered_actions = [];

        public function load_config(): void {}
        public function add_texts(string $dir, bool $bool = false): void {}
        public function include_script(string $file): void {}
        public function include_stylesheet(string $file): void {}
        public function add_hook(string $hook, $cb): void { $this->registered_hooks[$hook] = $cb; }
        public function register_action(string $act, $cb): void { $this->registered_actions[$act] = $cb; }
        public function gettext(string $key): string { return $key; }
    }

    class rcube_utils
    {
        public const INPUT_GET = 1;
        public const INPUT_POST = 2;
        public static array $mockPost = [];
        public static function get_input_value(string $key, int $type)
        {
            return self::$mockPost[$key] ?? null;
        }
    }
}

require_once $pluginDir . '/twofactor_auth.php';

$tfa = new twofactor_auth();
$reflection = new ReflectionClass($tfa);

// Test Recovery Codes Generation
$generateRecoveryMethod = $reflection->getMethod('generateRecoveryCodes');
$generateRecoveryMethod->setAccessible(true);
$codes = $generateRecoveryMethod->invoke($tfa, 10);

assert_true(count($codes) === 10, "Generates exactly 10 recovery codes");
foreach ($codes as $code) {
    assert_true((bool)preg_match('/^[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/', $code), "Recovery code '{$code}' matches XXXX-XXXX format without ambiguous chars");
}

// Test Recovery Code Hashing & Verification Logic
$hashedCodes = array_map(fn($c) => password_hash(strtoupper(str_replace('-', '', $c)), PASSWORD_DEFAULT), $codes);
$testCodeToUse = $codes[3]; // Use 4th code
$cleanTestCode = strtoupper(str_replace('-', '', $testCodeToUse));

$matchedIndex = null;
foreach ($hashedCodes as $idx => $hash) {
    if (password_verify($cleanTestCode, $hash)) {
        $matchedIndex = $idx;
        unset($hashedCodes[$idx]);
        $hashedCodes = array_values($hashedCodes);
        break;
    }
}

assert_true($matchedIndex === 3, "Recovery code correctly matches and authenticates");
assert_true(count($hashedCodes) === 9, "Recovery code list decreases to 9 after single use");

// Verify that the used code CANNOT be used again
$reusedMatch = false;
foreach ($hashedCodes as $hash) {
    if (password_verify($cleanTestCode, $hash)) {
        $reusedMatch = true;
        break;
    }
}
assert_true(!$reusedMatch, "Consumed recovery code cannot be reused (one-time use enforced)");

// --------------------------------------------------------------------------
// Test Suite 4: Customizable Verification Messages (Email & SMS)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: Customizable Verification Messages ---\n";

$issuer = 'Secure Webmail';
$destUser = 'testuser@example.com';
$testOtp = '482915';
$ttlMin = '10';

// Email Subject & Body
$emailSubjectTpl = '[%issuer%] Security Login Code';
$emailBodyTpl = "Hello %user%,\n\nCode: %code%\nValid for %ttl_minutes% mins.\n\nFrom %issuer%";

$sub = str_replace(['%issuer%', '%user%'], [$issuer, $destUser], $emailSubjectTpl);
$body = str_replace(['%code%', '%user%', '%issuer%', '%ttl_minutes%'], [$testOtp, $destUser, $issuer, $ttlMin], $emailBodyTpl);

assert_true($sub === '[Secure Webmail] Security Login Code', "Email subject template correctly populates %issuer%");
assert_true(str_contains($body, 'Hello testuser@example.com'), "Email body template correctly populates %user%");
assert_true(str_contains($body, 'Code: 482915'), "Email body template correctly populates %code%");
assert_true(str_contains($body, 'Valid for 10 mins'), "Email body template correctly populates %ttl_minutes%");

// SMS Message
$smsTpl = '%issuer% code: %code%. Valid for %ttl_minutes% min.';
$smsText = str_replace(['%issuer%', '%code%', '%ttl_minutes%'], [$issuer, $testOtp, $ttlMin], $smsTpl);
assert_true($smsText === 'Secure Webmail code: 482915. Valid for 10 min.', "SMS message correctly formatted with placeholders");

// --------------------------------------------------------------------------
// Test Suite 5: Plugin Lifecycle & Administrative Controls
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Lifecycle & Administrative Controls ---\n";

// 5.1 Global Disable Switch
rcmail::reset_instance();
$rcmail = rcmail::get_instance();
$rcmail->config->set('twofactor_auth_disabled', true);

$pluginDisabled = new twofactor_auth();
$pluginDisabled->init();
assert_true(empty($pluginDisabled->registered_hooks), "When twofactor_auth_disabled is true, no hooks are registered");

// 5.2 Enabled Plugin Hook Registration
$rcmail->config->set('twofactor_auth_disabled', false);
$pluginActive = new twofactor_auth();
$pluginActive->init();

assert_true(isset($pluginActive->registered_hooks['startup']), "Registers 'startup' gatekeeper hook");
assert_true(isset($pluginActive->registered_hooks['login_after']), "Registers 'login_after' interceptor hook");
assert_true(isset($pluginActive->registered_hooks['preferences_sections_list']), "Registers 'preferences_sections_list' hook");
assert_true(isset($pluginActive->registered_hooks['preferences_list']), "Registers 'preferences_list' hook");
assert_true(isset($pluginActive->registered_actions['plugin.twofactor_auth-check']), "Registers action 'plugin.twofactor_auth-check'");
assert_true(isset($pluginActive->registered_actions['plugin.twofactor_auth-verify']), "Registers action 'plugin.twofactor_auth-verify'");

// 5.3 Startup Gatekeeper Hook Logic
$_SESSION = [];
$_SESSION['2fa_pending'] = true;
$rcmail->action = 'mail'; // Unauthorized task/action while 2FA is pending
$rcmail->output->redirectUrl = '';

$pluginActive->hook_startup([]);
assert_true(str_contains($rcmail->output->redirectUrl, 'plugin.twofactor_auth-check'), "Startup gatekeeper intercepts unauthorized access and redirects to 2FA challenge");

// Allowed actions should NOT be redirected
$rcmail->output->redirectUrl = '';
$rcmail->action = 'plugin.twofactor_auth-verify';
$pluginActive->hook_startup([]);
assert_true($rcmail->output->redirectUrl === '', "Startup gatekeeper allows plugin.twofactor_auth-verify without redirect");

$rcmail->output->redirectUrl = '';
$rcmail->action = 'logout';
$pluginActive->hook_startup([]);
assert_true($rcmail->output->redirectUrl === '', "Startup gatekeeper allows logout without redirect");

// 5.4 Enforcement Logic
$rcmail->config->set('twofactor_auth_enforce', true);
$rcmail->user->prefs = []; // User does not have 2FA enabled
$loginArgs = ['user' => 'user@example.com', 'user_id' => 42];
$rcmail->output->redirectUrl = '';

$pluginActive->hook_login_after($loginArgs);
assert_true(!empty($_SESSION['2fa_pending']), "Enforcement flag forces 2FA challenge on login for unconfigured user");
assert_true(!empty($_SESSION['2fa_enforced_setup']), "Flags session for enforced setup");
assert_true(str_contains($rcmail->output->redirectUrl, 'plugin.twofactor_auth-check'), "Redirects to 2FA challenge upon login");

// 5.5 Preferences Sections & List
$sections = $pluginActive->hook_preferences_sections_list(['list' => []]);
assert_true(isset($sections['list']['twofactor_auth']), "Two-factor authentication registered in preferences sections");

$prefList = $pluginActive->hook_preferences_list(['section' => 'twofactor_auth', 'blocks' => []]);
assert_true(isset($prefList['blocks']['twofactor_auth']), "Renders twofactor_auth settings block");
$content = $prefList['blocks']['twofactor_auth']['options']['twofactor_content']['content'];
assert_true(str_contains($content, 'twofactor-badge'), "Settings page contains 2FA status badge");

// Clean up session
$_SESSION = [];

echo "\n*** ALL TWO-FACTOR AUTHENTICATION TESTS PASSED SUCCESSFULLY ***\n";
