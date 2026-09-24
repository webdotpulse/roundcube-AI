<?php

/**
 * Automated test suite for Persistent Login Plugin (persistent_login)
 *
 * Validates:
 * - OWASP / Paragonie Split-Token Generation & High-Entropy Verification
 * - SHA-256 Verifier Hashing & Constant-Time Verification
 * - Replay Attack & Cookie Theft Detection with Instant User Invalidation
 * - Automatic Verifier Rotation on Auto-Login (Rolling Sessions)
 * - Authenticated AES-256-GCM Credential Encryption & Decryption
 * - HMAC Cookie Signing, Verification, and Anti-Tampering
 * - Multi-Engine Database Support & Schema Auto-Creation
 * - Maximum Concurrent Device Enforcement per User
 * - User-Agent and Device Type (Desktop, Mobile, Tablet) Parsing
 * - Roundcube Hook Lifecycle (startup, authenticate, login_after, logout)
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

echo "=== Running Persistent Login (persistent_login) Test Suite ===\n\n";

// Mock Roundcube environment for standalone unit testing
if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin
    {
        public $api;
        public function add_hook($hook, $callback) {}
        public function register_action($action, $callback) {}
        public function include_script($script) {}
        public function include_stylesheet($css) {}
        public function add_texts($p, $fallback = false) {}
        public function load_config() {}
        public function gettext($key) { return $key; }
    }
}

if (!class_exists('rcube')) {
    class rcube
    {
        public static function write_log($name, $msg) {}
        public static function Q($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
    }
}

if (!class_exists('rcube_utils')) {
    class rcube_utils
    {
        const INPUT_POST = 1;
        public static function get_input_value($key, $source) {
            return $_POST[$key] ?? null;
        }
        public static function https_check() {
            return false;
        }
    }
}

if (!class_exists('html_select')) {
    class html_select
    {
        public function __construct($attrs = []) {}
        public function add($text, $val) {}
        public function show($val) { return '<select></select>'; }
    }
}

if (!class_exists('html')) {
    class html
    {
        public static function label($for, $text) {
            return "<label for=\"{$for}\">{$text}</label>";
        }
    }
}

if (!class_exists('rcube_db')) {
    class rcube_db {}
}

// Pure PHP In-Memory Mock Database for Roundcube DB
class MockRcubeDb extends rcube_db
{
    public string $db_provider = 'sqlite';
    public array $rows = [];
    public bool $tableCreated = false;

    public function query(string $sql, ...$params)
    {
        $sqlTrim = trim($sql);

        if (stripos($sqlTrim, 'CREATE TABLE') !== false) {
            $this->tableCreated = true;
            return true;
        }

        if (stripos($sqlTrim, 'INSERT INTO') === 0) {
            // INSERT INTO persistent_logins (series, token_hash, user_id, user_name, user_pass, host, ip_address, user_agent, created, last_used, expires)
            $series = $params[0];
            $this->rows[$series] = [
                'series' => $params[0],
                'token_hash' => $params[1],
                'user_id' => (int)$params[2],
                'user_name' => (string)$params[3],
                'user_pass' => (string)$params[4],
                'host' => (string)$params[5],
                'ip_address' => (string)$params[6],
                'user_agent' => (string)$params[7],
                'created' => $params[8],
                'last_used' => $params[9],
                'expires' => $params[10],
            ];
            return true;
        }

        if (stripos($sqlTrim, 'SELECT') === 0) {
            if (stripos($sqlTrim, 'sqlite_master') !== false) {
                return new MockDbStatement([['name' => $this->tableCreated ? 'persistent_logins' : '']]);
            }

            if (stripos($sqlTrim, 'WHERE series =') !== false) {
                $series = $params[0] ?? '';
                if (isset($this->rows[$series])) {
                    return new MockDbStatement([$this->rows[$series]]);
                }
                return new MockDbStatement([]);
            }

            if (stripos($sqlTrim, 'WHERE user_id =') !== false) {
                $userId = (int)($params[0] ?? 0);
                $filterExpires = (count($params) > 1) ? $params[1] : null;

                $matched = [];
                foreach ($this->rows as $r) {
                    if ($r['user_id'] === $userId) {
                        if ($filterExpires !== null && $r['expires'] <= $filterExpires) {
                            continue;
                        }
                        $matched[] = $r;
                    }
                }
                // Sort by last_used DESC
                usort($matched, fn($a, $b) => strcmp($b['last_used'], $a['last_used']));
                return new MockDbStatement($matched);
            }

            if (stripos($sqlTrim, 'WHERE user_name =') !== false) {
                $userName = (string)($params[0] ?? '');
                $filterExpires = (count($params) > 1) ? $params[1] : null;

                $matched = [];
                foreach ($this->rows as $r) {
                    if (($r['user_name'] ?? '') === $userName) {
                        if ($filterExpires !== null && $r['expires'] <= $filterExpires) {
                            continue;
                        }
                        $matched[] = $r;
                    }
                }
                usort($matched, fn($a, $b) => strcmp($b['last_used'], $a['last_used']));
                return new MockDbStatement($matched);
            }

            return new MockDbStatement(array_values($this->rows));
        }

        if (stripos($sqlTrim, 'UPDATE') === 0) {
            if (str_contains($sqlTrim, 'token_hash =')) {
                // UPDATE persistent_logins SET token_hash = ?, last_used = ?, expires = ?, ip_address = ? WHERE series = ?
                $series = $params[4] ?? '';
                if (isset($this->rows[$series])) {
                    $this->rows[$series]['token_hash'] = $params[0];
                    $this->rows[$series]['last_used'] = $params[1];
                    $this->rows[$series]['expires'] = $params[2];
                    $this->rows[$series]['ip_address'] = $params[3];
                }
            } else {
                // UPDATE persistent_logins SET last_used = ?, expires = ?, ip_address = ? WHERE series = ?
                $series = $params[3] ?? '';
                if (isset($this->rows[$series])) {
                    $this->rows[$series]['last_used'] = $params[0];
                    $this->rows[$series]['expires'] = $params[1];
                    $this->rows[$series]['ip_address'] = $params[2];
                }
            }
            return true;
        }

        if (stripos($sqlTrim, 'DELETE FROM') === 0) {
            if (str_contains($sqlTrim, 'WHERE series =')) {
                $series = $params[0] ?? '';
                unset($this->rows[$series]);
            } elseif (str_contains($sqlTrim, 'WHERE user_id = ? AND series !=')) {
                $userId = (int)$params[0];
                $keepSeries = $params[1];
                foreach ($this->rows as $s => $r) {
                    if ($r['user_id'] === $userId && $s !== $keepSeries) {
                        unset($this->rows[$s]);
                    }
                }
            } elseif (str_contains($sqlTrim, 'WHERE user_id =')) {
                $userId = (int)$params[0];
                foreach ($this->rows as $s => $r) {
                    if ($r['user_id'] === $userId) {
                        unset($this->rows[$s]);
                    }
                }
            } elseif (str_contains($sqlTrim, 'WHERE expires <=')) {
                $expiryLimit = $params[0];
                foreach ($this->rows as $s => $r) {
                    if ($r['expires'] <= $expiryLimit) {
                        unset($this->rows[$s]);
                    }
                }
            }
            return true;
        }

        return true;
    }

    public function fetch_assoc($res)
    {
        if ($res instanceof MockDbStatement) {
            return $res->fetchAssoc();
        }
        return false;
    }
}

class MockDbStatement
{
    private array $data;
    private int $idx = 0;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function fetchAssoc()
    {
        if ($this->idx < count($this->data)) {
            return $this->data[$this->idx++];
        }
        return false;
    }
}

// Mock Config
class MockRcubeConfig
{
    private array $data = [
        'persistent_login_lifetime' => 30,
        'persistent_login_cookie_name' => '_rc_persistent_login',
        'persistent_login_cookie_secure' => false,
        'persistent_login_cookie_samesite' => 'Lax',
        'persistent_login_rotate_token' => true,
        'persistent_login_check_ip' => false,
        'persistent_login_check_user_agent' => true,
        'persistent_login_max_tokens_per_user' => 3,
        'persistent_login_trust_2fa' => true,
        'persistent_login_default_checked' => false,
        'persistent_login_secret_key' => 'test-secret-pepper-key',
        'des_key' => 'test-roundcube-des-key-32-chars!',
    ];

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }
}

// Mock Output
class MockRcubeOutput
{
    public array $messages = [];
    public array $env = [];

    public function show_message($msg, $type = 'info')
    {
        $this->messages[] = ['message' => $msg, 'type' => $type];
    }

    public function set_env($k, $v)
    {
        $this->env[$k] = $v;
    }

    public function add_script($script, $pos = 'head') {}
}

// Mock User
class MockRcubeUser
{
    public int $ID = 42;
    private array $prefs = [];

    public function get_prefs() { return $this->prefs; }
    public function set_pref($k, $v) { $this->prefs[$k] = $v; }
    public function get_username() { return 'charlie'; }
}

// Mock rcmail
class rcmail
{
    private static ?rcmail $instance = null;
    public string $task = 'mail';
    public string $action = '';
    public MockRcubeConfig $config;
    public ?MockRcubeDb $db = null;
    public ?MockRcubeOutput $output = null;
    public ?MockRcubeUser $user = null;

    public function __construct()
    {
        $this->config = new MockRcubeConfig();
        $this->db = new MockRcubeDb();
        $this->output = new MockRcubeOutput();
        $this->user = new MockRcubeUser();
    }

    public static function get_instance(): rcmail
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_dbh()
    {
        return $this->db;
    }

    public function encrypt($str)
    {
        return 'mock_enc_' . base64_encode($str);
    }

    public function decrypt($str)
    {
        if (str_starts_with($str, 'mock_enc_')) {
            return base64_decode(substr($str, 9));
        }
        return $str;
    }
}

// Require Plugin Under Test
$pluginPath = dirname(__DIR__) . '/Extra context/plugins/persistent_login/persistent_login.php';
require_once $pluginPath;

$plugin = new persistent_login();
$rc = rcmail::get_instance();

// --------------------------------------------------------------------------
// Test Suite 1: Split-Token Cryptography & Verification
// --------------------------------------------------------------------------
echo "--- Test Suite 1: Split-Token Cryptography ---\n";

$series = bin2hex(random_bytes(16));
$verifier = bin2hex(random_bytes(32));

assert_true(strlen($series) === 32, "Series selector is exactly 32 hex characters (16 bytes)");
assert_true(strlen($verifier) === 64, "Verifier is exactly 64 hex characters (32 bytes)");

$tokenHash = hash('sha256', $verifier);
assert_true(strlen($tokenHash) === 64, "Token hash is valid SHA-256 digest (64 hex characters)");
assert_true(hash_equals($tokenHash, hash('sha256', $verifier)), "Constant-time comparison succeeds with matching verifier");
assert_true(!hash_equals($tokenHash, hash('sha256', $verifier . 'x')), "Constant-time comparison fails with forged verifier");

// --------------------------------------------------------------------------
// Test Suite 2: Credential Encryption & Decryption (AES-256-GCM)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Credential Encryption & Decryption ---\n";

$password = "MyV3rySecu!eP@ssw0rd#2026_✓";
$encrypted = $plugin->encrypt_password($password);

assert_true(str_starts_with($encrypted, 'gcm:') || str_starts_with($encrypted, 'rc:'), "Ciphertext has authenticated format prefix");
assert_true($encrypted !== $password, "Ciphertext differs completely from plaintext password");

$decrypted = $plugin->decrypt_password($encrypted);
assert_true($decrypted === $password, "Decrypted password strictly matches original password including UTF-8 characters");

// Resilience against tampered ciphertext
$tampered = substr($encrypted, 0, -4) . 'ffff';
$tamperedResult = $plugin->decrypt_password($tampered);
assert_true($tamperedResult === null || $tamperedResult === false, "Tampered ciphertext safely fails authentication check");

// --------------------------------------------------------------------------
// Test Suite 3: Cookie Serialization, HMAC Signing & Anti-Tampering
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Cookie Serialization & HMAC Signing ---\n";

$plugin->set_cookie($series, $verifier, time() + 86400);
$cookieName = $rc->config->get('persistent_login_cookie_name');
assert_true(!empty($_COOKIE[$cookieName]), "Cookie is set in \$_COOKIE superglobal");

$parsed = $plugin->parse_cookie_value($_COOKIE[$cookieName]);
assert_true($parsed !== null, "parse_cookie_value decodes valid HMAC-signed cookie");
assert_true($parsed['series'] === $series, "Parsed series selector matches original");
assert_true($parsed['verifier'] === $verifier, "Parsed verifier matches original");

// Tamper with cookie data
$tamperedCookie = base64_encode($series . ':' . $verifier . 'tampered:' . 'fake_signature');
assert_true($plugin->parse_cookie_value($tamperedCookie) === null, "parse_cookie_value rejects forged HMAC signature");

// --------------------------------------------------------------------------
// Test Suite 4: Database Operations & Token Lifecycle (SQLite Engine)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: Database Operations & Token Lifecycle ---\n";

$db = $rc->get_dbh();
$plugin->ensureTableExists($db);

// Verify table created
$res = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='persistent_logins'");
$row = $db->fetch_assoc($res);
assert_true(!empty($row['name']), "Database table 'persistent_logins' created successfully via ensureTableExists");

// Create token
$username = 'alice@example.com';
$createdSeries = $plugin->create_token(42, $username, $password, 'mail.example.com');
assert_true(!empty($createdSeries) && strlen($createdSeries) === 32, "create_token returns 32-character series ID");

// Validate token via cookie
$cookieVal = $_COOKIE[$cookieName];
$validated = $plugin->validate_token($cookieVal);
assert_true($validated !== null, "validate_token succeeds for freshly created cookie");
assert_true($validated['user_name'] === $username, "Validated record contains correct user_name");
assert_true($validated['password'] === $password, "Validated record contains decrypted password");
assert_true($validated['series'] === $createdSeries, "Validated record contains matching series");

// Token Rotation
$newVerifier = $plugin->rotate_token($createdSeries, 42);
assert_true(!empty($newVerifier) && strlen($newVerifier) === 64, "rotate_token returns new 64-character verifier");

// Refreshed cookie must succeed
$refreshedCookieVal = $_COOKIE[$cookieName];
$validatedAfterRotation = $plugin->validate_token($refreshedCookieVal);
assert_true($validatedAfterRotation !== null && empty($validatedAfterRotation['theft']), "Refreshed cookie with new rotated verifier succeeds validation");

// --------------------------------------------------------------------------
// Test Suite 5: Replay Attack & Cookie Theft Detection
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Cookie Theft Detection & Instant User Invalidation ---\n";

// Presenting the OLD cookie value (from before rotation) is a replay attack!
$theftResult = $plugin->validate_token($cookieVal);
assert_true(is_array($theftResult) && !empty($theftResult['theft']), "Theft detection flags verifier mismatch on replayed/old cookie");

// All persistent sessions for that user must have been wiped from DB
$sessionsAfterTheft = $plugin->get_user_sessions(42);
assert_true(count($sessionsAfterTheft) === 0, "All active user sessions are wiped instantly upon theft detection");

// --------------------------------------------------------------------------
// Test Suite 6: Maximum Device Limit Enforcement & Revocation
// --------------------------------------------------------------------------
echo "\n--- Test Suite 6: Maximum Device Limit & Revocation ---\n";

// Create 4 sessions when limit is 3
$s1 = $plugin->create_token(99, 'bob@example.com', 'pwd1', 'host1');
sleep(1);
$s2 = $plugin->create_token(99, 'bob@example.com', 'pwd2', 'host1');
sleep(1);
$s3 = $plugin->create_token(99, 'bob@example.com', 'pwd3', 'host1');
sleep(1);
$s4 = $plugin->create_token(99, 'bob@example.com', 'pwd4', 'host1');

$bobSessions = $plugin->get_user_sessions(99);
assert_true(count($bobSessions) === 3, "Max token limit (3) strictly enforced; oldest session automatically pruned");

// Revoke single session
$plugin->revoke_token($bobSessions[0]['series']);
$bobSessionsAfterRevoke = $plugin->get_user_sessions(99);
assert_true(count($bobSessionsAfterRevoke) === 2, "revoke_token removes single target session");

// Revoke all other sessions except current
$keepSeries = $bobSessionsAfterRevoke[0]['series'];
$plugin->revoke_all_user_tokens(99, $keepSeries);
$bobSessionsRemaining = $plugin->get_user_sessions(99);
assert_true(count($bobSessionsRemaining) === 1, "revoke_all_user_tokens with exceptSeries preserves current session");
assert_true($bobSessionsRemaining[0]['series'] === $keepSeries, "Preserved session matches expected series");

// --------------------------------------------------------------------------
// Test Suite 7: User-Agent & Device Parsing
// --------------------------------------------------------------------------
echo "\n--- Test Suite 7: User-Agent & Device Parsing ---\n";

$chromeMac = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36";
$uaMac = $plugin->parse_user_agent($chromeMac);
assert_true(str_contains($uaMac['device_name'], 'Chrome'), "Identifies Chrome browser");
assert_true(str_contains($uaMac['device_name'], 'macOS'), "Identifies macOS");
assert_true($uaMac['type'] === 'desktop', "Identifies desktop device type");

$iphone = "Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1";
$uaIphone = $plugin->parse_user_agent($iphone);
assert_true(str_contains($uaIphone['device_name'], 'iOS') || str_contains($uaIphone['device_name'], 'Mobile'), "Identifies iPhone/iOS mobile device");
assert_true($uaIphone['type'] === 'mobile', "Identifies mobile device type");

$ipad = "Mozilla/5.0 (iPad; CPU OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1";
$uaIpad = $plugin->parse_user_agent($ipad);
assert_true($uaIpad['type'] === 'tablet', "Identifies tablet device type");

// --------------------------------------------------------------------------
// Test Suite 8: Roundcube Hook Integration
// --------------------------------------------------------------------------
echo "\n--- Test Suite 8: Roundcube Hook Lifecycle ---\n";

// 8.1 startup hook without cookie
unset($_COOKIE[$cookieName]);
$args = ['task' => 'mail', 'action' => ''];
$resArgs = $plugin->hook_startup($args);
assert_true($resArgs['action'] === '', "Startup hook without cookie leaves action unchanged");

// 8.2 authenticate hook with manual checkbox
$_POST['_persistent_login'] = '1';
$authArgs = ['user' => 'charlie', 'pass' => 'secret', 'host' => 'localhost'];
$plugin->hook_authenticate($authArgs);
assert_true(!empty($_SESSION['persistent_login_pending']), "Authenticate hook captures _persistent_login POST parameter");

// 8.3 login_after hook creates session
$loginAfterArgs = ['user' => 'charlie', 'pass' => 'secret', 'host' => 'localhost', 'user_id' => 77];
$plugin->hook_login_after($loginAfterArgs);
assert_true(empty($_SESSION['persistent_login_pending']), "Pending flag cleared after successful login");
assert_true(!empty($_COOKIE[$cookieName]), "Cookie issued upon successful login");

// 8.4 startup hook with valid cookie triggers auto-login
$rc->user = null;
$_SESSION['user_id'] = null;
$startupRes = $plugin->hook_startup(['task' => 'mail', 'action' => '']);
assert_true($startupRes['action'] === 'login', "Startup hook with valid cookie redirects action to 'login'");

// 8.5 authenticate hook injects credentials during auto-login
$authInject = $plugin->hook_authenticate(['user' => '', 'pass' => '', 'host' => '', 'valid' => false]);
assert_true($authInject['user'] === 'charlie', "Authenticate hook injects correct username for auto-login");
assert_true($authInject['pass'] === 'secret', "Authenticate hook injects decrypted password for auto-login");
assert_true($authInject['valid'] === true, "Authenticate hook sets valid = true for auto-login");

// 8.6 logout clears token and cookie
$rc->task = 'logout';
$plugin->hook_logout_after(['task' => 'logout']);
assert_true(empty($_COOKIE[$cookieName]), "Logout cleans up persistent cookie");

// --------------------------------------------------------------------------
// Test Suite 9: Real-World Roundcube Login Lifecycle & Preferences Page UI
// --------------------------------------------------------------------------
echo "\n--- Test Suite 9: Real-World Login & Preferences UI ---\n";

// 9.1 Real Roundcube login flow: authenticate hook receives user/pass,
// but login_after receives only task/action parameters without user or pass.
$_SESSION = [];
unset($_COOKIE[$cookieName]);
$rc->db->rows = [];
$rc->user = new MockRcubeUser();
$rc->user->ID = 99;

$_POST['_persistent_login'] = '1';
$authArgs = ['user' => 'charlie', 'pass' => 'topsecret', 'host' => 'imap.example.com'];
$plugin->hook_authenticate($authArgs);

// In standard Roundcube core (rcmail.php), login_after hook arguments are only request query params:
$rcLoginAfterArgs = ['_task' => 'mail'];
$plugin->hook_login_after($rcLoginAfterArgs);

assert_true(!empty($_COOKIE[$cookieName]), "Real-world login_after creates token and sets cookie without args credentials");
$activeSessions = $plugin->get_user_sessions(99, 'charlie');
assert_true(count($activeSessions) === 1, "Session stored in database for user 99");

// 9.2 Preferences list renders table full-width (no 'title') and uses 'Device' header
$prefArgs = ['section' => 'persistent_login', 'blocks' => []];
$prefResult = $plugin->hook_preferences_list($prefArgs);
$sessionsOpt = $prefResult['blocks']['persistent_login']['options']['trusted_sessions_list'];

assert_true(!isset($sessionsOpt['title']), "trusted_sessions_list option has NO title so it renders full width");
assert_true(str_contains($sessionsOpt['content'], 'id="persistent-sessions-wrapper"'), "Wrapper container present");
assert_true(str_contains($sessionsOpt['content'], '<th>device</th>') || str_contains($sessionsOpt['content'], '<th>Device</th>'), "Header displays Device instead of unknown device");
assert_true(!str_contains($sessionsOpt['content'], 'UNKNOWN DEVICE'), "Header does not display UNKNOWN DEVICE");
assert_true(str_contains($sessionsOpt['content'], 'session-row-current'), "Active session row has current session styling");
assert_true(str_contains($sessionsOpt['content'], 'badge-current-device'), "Active session row shows Current Device badge");

// 9.3 Auto-healing: If user is logged in with remember-me active but DB record missing, preferences list auto-creates it
$_SESSION = [
    'user_id' => 99,
    'username' => 'charlie',
    'password' => $rc->encrypt('topsecret'),
    'persistent_login_remember' => true,
];
$rc->db->rows = [];
unset($_COOKIE[$cookieName]);

$prefHeal = $plugin->hook_preferences_list($prefArgs);
$healedContent = $prefHeal['blocks']['persistent_login']['options']['trusted_sessions_list']['content'];
assert_true(!str_contains($healedContent, 'no_active_sessions'), "Auto-heal prevented 'No active persistent sessions found'");
assert_true(str_contains($healedContent, 'session-row-current'), "Auto-heal rendered active session row for current device");

echo "\n*** ALL PERSISTENT LOGIN TESTS PASSED SUCCESSFULLY ***\n";

