<?php

/**
 * Automated Test Suite for Vacation & Forwarding Plugin (vacation_forward)
 *
 * Validates:
 * - Database schema definitions (MySQL, SQLite, PostgreSQL)
 * - Vacation status scheduler & timezone evaluation
 * - Loop suppression & anti-spam heuristics (RFC 3834, daemons, mailing lists, blacklists)
 * - Multi-language template detection (stop-words, TLDs, domain rules, default fallbacks)
 * - Placeholder interpolation and return date calculation
 * - Per-sender rate-limiting throttle window
 * - Forwarding address parsing, sanitization, mode headers, and condition filters
 * - Sieve script generation
 * - Plugin hooks & settings dashboard rendering
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

echo "=== Running Vacation & Forwarding (vacation_forward) Test Suite ===\n\n";

$pluginDir = dirname(__DIR__) . '/Extra context/plugins/vacation_forward';

// --------------------------------------------------------------------------
// Test Suite 1: Database Schema Definitions (MySQL, SQLite, PostgreSQL)
// --------------------------------------------------------------------------
echo "--- Test Suite 1: Database Schema Definitions ---\n";

$mysqlSql = file_get_contents($pluginDir . '/SQL/mysql.sql');
assert_true($mysqlSql !== false && str_contains($mysqlSql, 'CREATE TABLE IF NOT EXISTS `vacation_forward_logs`'), "MySQL schema defines vacation_forward_logs table");
assert_true(str_contains($mysqlSql, '`action_type` VARCHAR(32)'), "MySQL schema defines action_type column");
assert_true(str_contains($mysqlSql, '`template_used` VARCHAR(128)'), "MySQL schema defines template_used column");
assert_true(str_contains($mysqlSql, 'ENGINE=InnoDB'), "MySQL schema uses InnoDB engine");

$sqliteSql = file_get_contents($pluginDir . '/SQL/sqlite.sql');
assert_true($sqliteSql !== false && str_contains($sqliteSql, 'CREATE TABLE IF NOT EXISTS vacation_forward_logs'), "SQLite schema defines vacation_forward_logs table");
assert_true(str_contains($sqliteSql, 'id INTEGER PRIMARY KEY AUTOINCREMENT'), "SQLite schema defines autoincrement primary key");
assert_true(str_contains($sqliteSql, 'CREATE INDEX IF NOT EXISTS vacation_forward_user_sender'), "SQLite schema defines composite index on user_id, sender, action_type");

$postgresSql = file_get_contents($pluginDir . '/SQL/postgres.sql');
assert_true($postgresSql !== false && str_contains($postgresSql, 'CREATE TABLE IF NOT EXISTS vacation_forward_logs'), "PostgreSQL schema defines vacation_forward_logs table");
assert_true(str_contains($postgresSql, 'id SERIAL PRIMARY KEY'), "PostgreSQL schema defines serial primary key");
assert_true(str_contains($postgresSql, 'created_at TIMESTAMP NOT NULL'), "PostgreSQL schema defines timestamp column");

// --------------------------------------------------------------------------
// Test Suite 2: Multi-Language Template Manager & Language Detection
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Multi-Language Template Manager ---\n";

require_once $pluginDir . '/lib/TemplateManager.php';

$defaultTemplates = VacationForwardTemplateManager::getDefaultTemplates();
assert_true(count($defaultTemplates) >= 5, "TemplateManager provides at least 5 default templates (en, nl, de, fr, es)");

$defaultEn = array_filter($defaultTemplates, fn($t) => !empty($t['is_default']));
assert_true(count($defaultEn) === 1, "Exactly one default fallback template is defined");

// Heuristic Language Detection: Dutch
$dutchText = "Beste heer Jansen, graag wil ik u vragen naar de offerte van volgende week. Met vriendelijke groet.";
$detectedNl = VacationForwardTemplateManager::detectLanguage('info@bedrijf.nl', 'Vraag over de offerte', $dutchText);
assert_true($detectedNl === 'nl', "Correctly detects Dutch from stop words and .nl TLD");

// Heuristic Language Detection: German
$germanText = "Guten Tag, vielen Dank für Ihre E-Mail. Wir bitten um Rückmeldung bezüglich der Rechnung. Mit freundlichen Grüßen.";
$detectedDe = VacationForwardTemplateManager::detectLanguage('partner@firma.de', 'Rechnung 2026', $germanText);
assert_true($detectedDe === 'de', "Correctly detects German from stop words and .de TLD");

// Heuristic Language Detection: French
$frenchText = "Bonjour, merci pour votre message concernant le projet. Cordialement.";
$detectedFr = VacationForwardTemplateManager::detectLanguage('client@societe.fr', 'Projet web', $frenchText);
assert_true($detectedFr === 'fr', "Correctly detects French from text and .fr TLD");

// Heuristic Language Detection: Spanish
$spanishText = "Hola, muchas gracias por su mensaje. Saludos cordiales.";
$detectedEs = VacationForwardTemplateManager::detectLanguage('contacto@empresa.es', 'Consulta importante', $spanishText);
assert_true($detectedEs === 'es', "Correctly detects Spanish from text and .es TLD");

// Template Resolution: Domain Rule Override
$customTemplates = [
    [
        'id' => 'vip_rule',
        'name' => 'Internal Corporate Template',
        'lang' => 'en',
        'is_default' => false,
        'domain_rule' => '@internalcorp.com',
        'subject' => 'Internal Away Notice: {ORIGINAL_SUBJECT}',
        'body' => 'Hi teammate, I am out of office.',
    ],
    [
        'id' => 'general_default',
        'name' => 'General Default',
        'lang' => 'en',
        'is_default' => true,
        'domain_rule' => '',
        'subject' => 'Out of Office',
        'body' => 'General message.',
    ],
];

[$resolvedVip, $vipReason] = VacationForwardTemplateManager::resolveTemplate(
    $customTemplates,
    'alice@internalcorp.com',
    'Quarterly planning',
    'Check this out'
);
assert_true($resolvedVip['id'] === 'vip_rule', "Domain rule template takes precedence over default");
assert_true(str_contains($vipReason, 'Domain rule match'), "Match reason explains domain rule");

// Template Resolution: Fallback to default
[$resolvedGeneral] = VacationForwardTemplateManager::resolveTemplate(
    $customTemplates,
    'stranger@external.org',
    'Inquiry',
    'General question'
);
assert_true($resolvedGeneral['id'] === 'general_default', "Falls back to default template when no rule matches");

// Placeholder Interpolation
$tplContent = "Dear {SENDER_NAME} ({SENDER_EMAIL}), I am away from {START_DATE} to {END_DATE} and will return on {RETURN_DATE}. Regards, {USER_NAME}";
$context = [
    'start_date' => 'Oct 1, 2026',
    'end_date' => 'Oct 10, 2026',
    'return_date' => 'Monday, Oct 12, 2026',
    'sender_name' => 'John Doe',
    'sender_email' => 'john@example.com',
    'original_subject' => 'Meeting',
    'user_name' => 'Koen Boss',
    'user_email' => 'koen@example.com',
];
$interpolated = VacationForwardTemplateManager::interpolate($tplContent, $context);
assert_true(str_contains($interpolated, 'Dear John Doe (john@example.com)'), "Interpolates sender name and email");
assert_true(str_contains($interpolated, 'away from Oct 1, 2026 to Oct 10, 2026'), "Interpolates start and end dates");
assert_true(str_contains($interpolated, 'return on Monday, Oct 12, 2026'), "Interpolates calculated return date");
assert_true(str_contains($interpolated, 'Regards, Koen Boss'), "Interpolates user name");

// Sender Name Extraction
$senderName1 = VacationForwardTemplateManager::extractSenderName('"Alexander Bell" <alex@bell.com>', 'alex@bell.com');
assert_true($senderName1 === 'Alexander Bell', "Extracts sender display name from From header");
$senderName2 = VacationForwardTemplateManager::extractSenderName('sarah.connor@sky.net', 'sarah.connor@sky.net');
assert_true($senderName2 === 'Sarah Connor', "Derives human-friendly name from email address username");

// --------------------------------------------------------------------------
// Test Suite 3: Mock Roundcube Runtime & Vacation Engine
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Vacation Engine & Scheduler ---\n";

if (!class_exists('rcube')) {
    class rcube
    {
        public static function Q(string $str): string
        {
            return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
}

if (!class_exists('rcube_utils')) {
    class rcube_utils
    {
        public const INPUT_POST = 1;
        public const INPUT_GET = 2;
        public static array $mockPost = [];
        public static array $mockGet = [];

        public static function get_input_value(string $name, int $source)
        {
            return ($source === self::INPUT_POST) ? (self::$mockPost[$name] ?? null) : (self::$mockGet[$name] ?? null);
        }
    }
}

class MockDb
{
    public array $logs = [];
    public int $nextId = 1;

    public function query(string $sql, ...$params)
    {
        if (str_starts_with(trim($sql), 'INSERT INTO vacation_forward_logs')) {
            $this->logs[] = [
                'id' => $this->nextId++,
                'user_id' => $params[0] ?? 0,
                'sender' => $params[1] ?? '',
                'recipient' => $params[2] ?? '',
                'subject' => $params[3] ?? '',
                'action_type' => $params[4] ?? '',
                'template_used' => $params[5] ?? '',
                'status' => $params[6] ?? '',
                'message_id' => $params[7] ?? '',
                'details' => $params[8] ?? '',
                'created_at' => $params[9] ?? date('Y-m-d H:i:s'),
            ];
            return true;
        }

        if (str_starts_with(trim($sql), 'SELECT id FROM vacation_forward_logs')) {
            // Check throttle
            $userId = $params[0] ?? 0;
            $sender = $params[1] ?? '';
            $cutoff = $params[2] ?? '';

            $found = null;
            foreach ($this->logs as $l) {
                if ($l['user_id'] == $userId && $l['sender'] === $sender && $l['action_type'] === 'auto_reply' && $l['status'] === 'sent' && $l['created_at'] >= $cutoff) {
                    $found = $l;
                    break;
                }
            }
            return new MockDbResult($found ? [$found] : []);
        }

        if (str_starts_with(trim($sql), 'SELECT * FROM vacation_forward_logs')) {
            return new MockDbResult($this->logs);
        }

        if (str_starts_with(trim($sql), 'SELECT user_id, preferences FROM users')) {
            return new MockDbResult([]);
        }

        if (str_starts_with(trim($sql), 'DELETE FROM vacation_forward_logs')) {
            $this->logs = [];
            return true;
        }

        return new MockDbResult([]);
    }

    public function fetch_assoc($res)
    {
        return ($res instanceof MockDbResult) ? $res->fetch() : null;
    }

    public function get_type(): string
    {
        return 'sqlite';
    }
}

class MockDbResult
{
    private array $rows;
    private int $idx = 0;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function fetch(): ?array
    {
        return $this->rows[$this->idx++] ?? null;
    }
}

class MockRcmailUser
{
    public int $ID = 42;
    public array $prefs = [];

    public function get_prefs(): array
    {
        return $this->prefs;
    }

    public function save_prefs(array $p): bool
    {
        $this->prefs = $p;
        return true;
    }

    public function get_identity(): array
    {
        return [
            'name' => 'Koen Tester',
            'email' => 'koen@roundcube-test.local',
        ];
    }
}

class MockRcmailOutput
{
    public array $commands = [];
    public array $env = [];

    public function set_env(string $key, $val): void
    {
        $this->env[$key] = $val;
    }

    public function command(string $cmd, ...$args): void
    {
        $this->commands[] = ['cmd' => $cmd, 'args' => $args];
    }

    public function set_pagetitle(string $t): void {}
    public function send(string $template = ''): void {}
}

class MockRcmailConfig
{
    public array $config = ['timezone' => 'UTC'];

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}

if (!class_exists('rcmail')) {
    class rcmail
    {
        private static ?rcmail $instance = null;
        public string $task = 'settings';
        public string $action = '';
        public MockDb $db;
        public ?MockRcmailUser $user;
        public MockRcmailOutput $output;
        public MockRcmailConfig $config;
        public array $delivered = [];

        public function __construct()
        {
            $this->db = new MockDb();
            $this->user = new MockRcmailUser();
            $this->output = new MockRcmailOutput();
            $this->config = new MockRcmailConfig();
        }

        public static function get_instance(): rcmail
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_dbh(): MockDb
        {
            return $this->db;
        }

        public function get_storage()
        {
            return null;
        }

        public function get_request_token(): string
        {
            return 'mock_token_123';
        }

        public function url(array $args): string
        {
            return '?' . http_build_query($args);
        }

        public function deliver_message(string $msg, string $from, string $to, &$error = null): bool
        {
            $this->delivered[] = [
                'from' => $from,
                'to' => $to,
                'message' => $msg,
            ];
            return true;
        }
    }
}

if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin
    {
        public array $hooks = [];
        public array $actions = [];
        public array $texts = [];

        public function add_hook(string $name, callable $callback): void
        {
            $this->hooks[$name] = $callback;
        }

        public function register_action(string $name, callable $callback): void
        {
            $this->actions[$name] = $callback;
        }

        public function register_handler(string $name, callable $callback): void {}
        public function load_config(): void {}
        public function add_texts(string $dir, bool $domain): void {}
        public function include_stylesheet(string $file): void {}
        public function include_script(string $file): void {}

        public function gettext(string $key): string
        {
            return $key;
        }
    }
}

require_once $pluginDir . '/lib/VacationEngine.php';
$mockRcmail = rcmail::get_instance();
$vacationEngine = new VacationForwardVacationEngine($mockRcmail);

// Test Status Evaluation: Disabled
[$vActiveDisabled, $vBadgeDisabled] = $vacationEngine->evaluateStatus(['vacation_status' => 'disabled']);
assert_true(!$vActiveDisabled && $vBadgeDisabled === 'disabled', "Disabled status evaluates to inactive");

// Test Status Evaluation: Enabled
[$vActiveEnabled, $vBadgeEnabled] = $vacationEngine->evaluateStatus(['vacation_status' => 'enabled']);
assert_true($vActiveEnabled && $vBadgeEnabled === 'active', "Enabled status evaluates to active");

// Test Status Evaluation: Scheduled - Future
$futurePrefs = [
    'vacation_status' => 'scheduled',
    'vacation_start' => date('Y-m-d H:i:s', time() + 86400),
    'vacation_end' => date('Y-m-d H:i:s', time() + 172800),
    'timezone' => 'UTC',
];
[$vActiveFuture, $vBadgeFuture] = $vacationEngine->evaluateStatus($futurePrefs);
assert_true(!$vActiveFuture && $vBadgeFuture === 'scheduled', "Future scheduled period evaluates to scheduled badge");

// Test Status Evaluation: Scheduled - Currently Active
$activePrefs = [
    'vacation_status' => 'scheduled',
    'vacation_start' => date('Y-m-d H:i:s', time() - 3600),
    'vacation_end' => date('Y-m-d H:i:s', time() + 3600),
    'timezone' => 'UTC',
];
[$vActiveCurrent, $vBadgeCurrent] = $vacationEngine->evaluateStatus($activePrefs);
assert_true($vActiveCurrent && $vBadgeCurrent === 'active', "Active scheduled period evaluates to active now");

// Test Status Evaluation: Scheduled - Expired
$expiredPrefs = [
    'vacation_status' => 'scheduled',
    'vacation_start' => date('Y-m-d H:i:s', time() - 7200),
    'vacation_end' => date('Y-m-d H:i:s', time() - 3600),
    'timezone' => 'UTC',
];
[$vActiveExpired, $vBadgeExpired] = $vacationEngine->evaluateStatus($expiredPrefs);
assert_true(!$vActiveExpired && $vBadgeExpired === 'expired', "Past scheduled period evaluates to expired");

// --------------------------------------------------------------------------
// Test Suite 4: Loop Suppression & Anti-Spam
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: Loop Suppression & Anti-Spam ---\n";

// 1. Mailer daemon suppression
[$suppressDaemon] = $vacationEngine->shouldSuppress('mailer-daemon@mx1.example.com', [], []);
assert_true($suppressDaemon, "Suppresses mailer-daemon sender");

// 2. Noreply suppression
[$suppressNoreply] = $vacationEngine->shouldSuppress('noreply@service.com', [], []);
assert_true($suppressNoreply, "Suppresses noreply@ sender");

// 3. RFC 3834 Auto-Submitted header
[$suppressAutoSubmitted] = $vacationEngine->shouldSuppress('sender@normal.com', ['Auto-Submitted' => 'auto-replied'], []);
assert_true($suppressAutoSubmitted, "Suppresses incoming email with Auto-Submitted header");

// 4. Precedence bulk / list
[$suppressBulk] = $vacationEngine->shouldSuppress('newsletter@shop.com', ['Precedence' => 'bulk'], []);
assert_true($suppressBulk, "Suppresses incoming email with Precedence: bulk header");

// 5. Mailing List headers
[$suppressList] = $vacationEngine->shouldSuppress('developer@community.org', ['List-Id' => '<roundcube-dev.lists.org>'], []);
assert_true($suppressList, "Suppresses incoming email with List-Id header");

// 6. Blacklist match
$blacklistPrefs = ['vacation_blacklist' => "@spammers.net\nbadactor@example.com"];
[$suppressBlacklistDomain] = $vacationEngine->shouldSuppress('promo@spammers.net', [], $blacklistPrefs);
assert_true($suppressBlacklistDomain, "Suppresses sender matching blacklisted @domain");
[$suppressBlacklistEmail] = $vacationEngine->shouldSuppress('badactor@example.com', [], $blacklistPrefs);
assert_true($suppressBlacklistEmail, "Suppresses sender matching blacklisted exact email");

// 7. Legitimate user allowed
[$suppressNormal, $reasonNormal] = $vacationEngine->shouldSuppress('genuine.client@partner.com', [], $blacklistPrefs);
assert_true(!$suppressNormal && $reasonNormal === 'OK', "Allows legitimate email without suppression flags");

// --------------------------------------------------------------------------
// Test Suite 5: Rate Limiting & Throttle Window
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Rate Limiting & Throttle Window ---\n";

$userId = 42;
$testSender = 'inquisitive.client@clientcorp.com';

// 1. Initial reply is allowed
assert_true($vacationEngine->checkRateLimit($userId, $testSender, '1_day'), "Initial auto-reply is permitted");

// Record successful reply in mock database
$mockRcmail->db->query(
    "INSERT INTO vacation_forward_logs (user_id, sender, recipient, subject, action_type, template_used, status, message_id, details, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    $userId,
    $testSender,
    'user@example.com',
    'Out of Office: Query',
    'auto_reply',
    'default',
    'sent',
    '<msg123@clientcorp.com>',
    'Delivered',
    date('Y-m-d H:i:s')
);

// 2. Second reply within 1 day must be throttled
assert_true(!$vacationEngine->checkRateLimit($userId, $testSender, '1_day'), "Second auto-reply to same sender within 24 hours is throttled");

// 3. Different sender is NOT throttled
assert_true($vacationEngine->checkRateLimit($userId, 'other.colleague@firm.com', '1_day'), "Different sender is not affected by another sender's throttle");

// 4. Unlimited throttle interval permits reply
assert_true($vacationEngine->checkRateLimit($userId, $testSender, 'unlimited'), "Unlimited throttle interval permits consecutive replies");

// --------------------------------------------------------------------------
// Test Suite 6: Mail Forwarding Engine
// --------------------------------------------------------------------------
echo "\n--- Test Suite 6: Mail Forwarding Engine ---\n";

require_once $pluginDir . '/lib/ForwardEngine.php';
$forwardEngine = new VacationForwardForwardEngine($mockRcmail);

// Destination Parsing & Sanitization
$rawDestinations = "colleague1@example.com, John Doe <colleague2@example.com>;\ninvalid-email, colleague3@example.com\r\nmalicious@bad.org\r\nBcc: evil@hacker.com";
$parsed = VacationForwardForwardEngine::parseDestinations($rawDestinations);
assert_true(in_array('colleague1@example.com', $parsed, true), "Parses plain email destination");
assert_true(in_array('colleague2@example.com', $parsed, true), "Extracts email from Name <email> notation");
assert_true(in_array('colleague3@example.com', $parsed, true), "Parses newline-separated email destination");
assert_true(!in_array('invalid-email', $parsed, true), "Excludes invalid email syntax");
assert_true(!str_contains(implode(',', $parsed), "\r") && !str_contains(implode(',', $parsed), "\n"), "Sanitizes carriage returns to prevent header injection");

// Forwarding Conditions
$fwdPrefsMatching = [
    'forward_condition' => 'matching',
    'forward_keywords' => 'URGENT, Invoice, Contract',
];
assert_true($forwardEngine->matchesCondition('[URGENT] Please review this server status', 'ops@domain.com', $fwdPrefsMatching), "Matches subject containing URGENT keyword");
assert_true($forwardEngine->matchesCondition('Fwd: Invoice for August', 'finance@domain.com', $fwdPrefsMatching), "Matches subject containing Invoice keyword");
assert_true(!$forwardEngine->matchesCondition('Casual chat about lunch', 'friend@domain.com', $fwdPrefsMatching), "Rejects message not matching any keywords");

$fwdPrefsAll = ['forward_condition' => 'all'];
assert_true($forwardEngine->matchesCondition('Any subject', 'anyone@domain.com', $fwdPrefsAll), "Matches all messages when condition is 'all'");

// --------------------------------------------------------------------------
// Test Suite 7: ManageSieve Script Generation
// --------------------------------------------------------------------------
echo "\n--- Test Suite 7: ManageSieve Script Generation ---\n";

require_once $pluginDir . '/lib/SieveSync.php';

$sievePrefs = [
    'vacation_status' => 'scheduled',
    'vacation_start' => '2026-10-01 08:00:00',
    'vacation_end' => '2026-10-15 18:00:00',
    'vacation_rate_interval' => '2_days',
    'forward_status' => 'enabled',
    'forward_destinations' => 'backup@company.com',
    'forward_keep_copy' => true,
    'vacation_templates' => $defaultTemplates,
];
$identity = ['name' => 'Koen Tester', 'email' => 'koen@roundcube-test.local'];

$sieveScript = VacationForwardSieveSync::generateScript($sievePrefs, $identity);
assert_true(str_contains($sieveScript, 'require ["vacation", "copy", "date", "relational"];'), "Sieve script includes required capabilities");
assert_true(str_contains($sieveScript, 'redirect :copy "backup@company.com";'), "Sieve script generates redirect :copy rule for forwarding");
assert_true(str_contains($sieveScript, 'currentdate :value "ge" "date" "2026-10-01"'), "Sieve script enforces date start condition");
assert_true(str_contains($sieveScript, 'currentdate :value "le" "date" "2026-10-15"'), "Sieve script enforces date end condition");
assert_true(str_contains($sieveScript, ':days 2'), "Sieve script sets vacation days to 2");
assert_true(str_contains($sieveScript, ':addresses ["koen@roundcube-test.local"]'), "Sieve script registers user email address");

// --------------------------------------------------------------------------
// Test Suite 8: Plugin Hooks, Actions & Settings Rendering
// --------------------------------------------------------------------------
echo "\n--- Test Suite 8: Plugin Hooks & Settings Rendering ---\n";

require_once $pluginDir . '/vacation_forward.php';
$plugin = new vacation_forward($mockRcmail);
$plugin->init();

assert_true(isset($plugin->hooks['new_messages']), "Registers new_messages hook for real-time IMAP processing");
assert_true(isset($plugin->hooks['settings_actions']), "Registers settings_actions hook for Roundcube settings menu");
assert_true(isset($plugin->hooks['preferences_sections_list']), "Registers preferences_sections_list hook");
assert_true(isset($plugin->hooks['preferences_list']), "Registers preferences_list hook");
assert_true(isset($plugin->hooks['preferences_save']), "Registers preferences_save hook");

// Test settings_actions
$settingsArgs = $plugin->hook_settings_actions(['actions' => []]);
assert_true(!empty($settingsArgs['actions']), "settings_actions hook populates actions list");
$foundAction = false;
foreach ($settingsArgs['actions'] as $act) {
    if (($act['action'] ?? '') === 'plugin.vacation_forward' && ($act['class'] ?? '') === 'vacation') {
        $foundAction = true;
        break;
    }
}
assert_true($foundAction, "settings_actions registers plugin.vacation_forward with class 'vacation' for clock icon");

// Test preferences_sections_list
$prefSections = $plugin->hook_preferences_sections_list(['list' => []]);
assert_true(isset($prefSections['list']['vacation']), "preferences_sections_list registers 'vacation' section");

// Test render_settings_view HTML
$viewHtml = $plugin->render_settings_view();
assert_true(str_contains($viewHtml, 'id="vacation-forward-settings"'), "Settings view contains main container");
assert_true(str_contains($viewHtml, 'id="vfTabs"'), "Settings view renders tabbed navigation");
assert_true(str_contains($viewHtml, 'id="vf-tab-vacation"'), "Settings view contains Vacation tab");
assert_true(str_contains($viewHtml, 'id="vf-tab-templates"'), "Settings view contains Multi-Language Templates tab");
assert_true(str_contains($viewHtml, 'id="vf-tab-forward"'), "Settings view contains Forwarding tab");
assert_true(str_contains($viewHtml, 'id="vf-tab-exclusions"'), "Settings view contains Exclusions & Anti-loop tab");
assert_true(str_contains($viewHtml, 'id="vf-tab-logs"'), "Settings view contains Activity Logs tab");
assert_true(str_contains($viewHtml, 'id="vfTemplateModal"'), "Settings view includes interactive template editor modal");
assert_true(str_contains($viewHtml, 'id="vfSimulatorModal"'), "Settings view includes test simulation modal");

// Test Cron Worker
$cronMailboxesProcessed = $plugin->runBatchCron();
assert_true($cronMailboxesProcessed >= 0, "runBatchCron completes successfully");

echo "\n*** ALL VACATION & FORWARDING TESTS PASSED SUCCESSFULLY ***\n";
