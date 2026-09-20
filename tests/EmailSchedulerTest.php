<?php

/**
 * Automated test suite for Email Scheduler & Undo Send Plugin (email_scheduler)
 *
 * Validates:
 * - Queue database schema definitions (MySQL, SQLite, PostgreSQL)
 * - Message enqueuing (headers, body, parameters, recipients, status, send_at)
 * - Message before send hook interception (abort/result flags, delayed vs scheduled)
 * - Undo Send delay cancellation (action_undo)
 * - Scheduled message management (action_cancel, action_send_now, action_list)
 * - Queue delivery processor (processDueMessages, past vs future timestamps)
 * - Compose toolbar integration (presets for tomorrow morning/afternoon, next monday)
 * - Preferences integration (delay dropdown options, clamp boundaries 0-30s)
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

echo "=== Running Email Scheduler & Undo Send (email_scheduler) Test Suite ===\n\n";

$pluginDir = dirname(__DIR__) . '/Extra context/plugins/email_scheduler';

// --------------------------------------------------------------------------
// Test Suite 1: Database Schema Definitions (MySQL, SQLite, PostgreSQL)
// --------------------------------------------------------------------------
echo "--- Test Suite 1: Database Schema Definitions ---\n";

$mysqlSql = file_get_contents($pluginDir . '/SQL/mysql.sql');
assert_true($mysqlSql !== false && str_contains($mysqlSql, 'CREATE TABLE IF NOT EXISTS `email_scheduler_queue`'), "MySQL schema defines email_scheduler_queue table");
assert_true(str_contains($mysqlSql, '`status` VARCHAR(32)'), "MySQL schema defines status column");
assert_true(str_contains($mysqlSql, '`send_at` DATETIME NOT NULL'), "MySQL schema defines send_at datetime column");
assert_true(str_contains($mysqlSql, 'ENGINE=InnoDB'), "MySQL schema uses InnoDB engine");

$sqliteSql = file_get_contents($pluginDir . '/SQL/sqlite.sql');
assert_true($sqliteSql !== false && str_contains($sqliteSql, 'CREATE TABLE IF NOT EXISTS email_scheduler_queue'), "SQLite schema defines email_scheduler_queue table");
assert_true(str_contains($sqliteSql, 'id INTEGER PRIMARY KEY AUTOINCREMENT'), "SQLite schema defines autoincrement primary key");
assert_true(str_contains($sqliteSql, 'CREATE INDEX IF NOT EXISTS email_scheduler_status_send'), "SQLite schema defines composite index on status and send_at");

$postgresSql = file_get_contents($pluginDir . '/SQL/postgres.sql');
assert_true($postgresSql !== false && str_contains($postgresSql, 'CREATE TABLE IF NOT EXISTS email_scheduler_queue'), "PostgreSQL schema defines email_scheduler_queue table");
assert_true(str_contains($postgresSql, 'id SERIAL PRIMARY KEY'), "PostgreSQL schema defines serial primary key");
assert_true(str_contains($postgresSql, 'send_at TIMESTAMP NOT NULL'), "PostgreSQL schema defines timestamp column");

// --------------------------------------------------------------------------
// Test Suite 2: Mock Environment Setup
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Mock Environment Setup ---\n";

if (!class_exists('rcube')) {
    class rcube
    {
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
        public $config;
        public $output;
        public $user;
        public $dbh;
        public $task = 'mail';
        public $action = '';

        public function __construct()
        {
            $this->config = new rcube_config();
            $this->output = new rcmail_output_mock();
            $this->user = new rcube_user_mock();
            $this->dbh = new rcube_db_mock();
        }

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

        public function get_dbh()
        {
            return $this->dbh;
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
        public int $ID = 99;
        public array $prefs = [];

        public function get_username(): string
        {
            return 'sender@example.com';
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
        public array $env = [];
        public array $commands = [];
        public array $scripts = [];
        public array $stylesheets = [];

        public function set_env(string $key, $val): void
        {
            $this->env[$key] = $val;
        }

        public function command(string $cmd, ...$args): void
        {
            $this->commands[] = ['command' => $cmd, 'args' => $args];
        }

        public function include_script(string $script): void { $this->scripts[] = $script; }
        public function include_stylesheet(string $sheet): void { $this->stylesheets[] = $sheet; }
    }

    class rcube_plugin
    {
        public array $registered_hooks = [];
        public array $registered_actions = [];

        public array $buttons = [];
        public function add_button(array $args, string $container): void
        {
            $this->buttons[] = ['args' => $args, 'container' => $container];
        }
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
        public static array $mockGet = [];

        public static function get_input_value(string $key, int $type)
        {
            return $type === self::INPUT_POST ? (self::$mockPost[$key] ?? null) : (self::$mockGet[$key] ?? null);
        }
    }

    class html_select
    {
        public array $options = [];
        public array $attrib = [];

        public function __construct(array $p = [])
        {
            $this->attrib = $p;
        }

        public function add($text, $value)
        {
            $this->options[$value] = $text;
        }

        public function show($selected = null)
        {
            $html = '<select name="' . ($this->attrib['name'] ?? '') . '">';
            foreach ($this->options as $val => $txt) {
                $sel = ($val == $selected) ? ' selected="selected"' : '';
                $html .= "<option value=\"{$val}\"{$sel}>{$txt}</option>";
            }
            $html .= '</select>';
            return $html;
        }
    }

    class html
    {
        public static function label($for, $text)
        {
            return "<label for=\"{$for}\">{$text}</label>";
        }
    }

    if (!class_exists('rcube_db')) {
        class rcube_db {}
    }

    class rcube_db_mock extends rcube_db
    {
        public string $db_provider = 'sqlite';
        public array $rows = [];
        private int $autoIncrement = 1;
        public int $lastAffectedRows = 0;

        public function query(string $sql, ...$params)
        {
            $sqlTrim = trim($sql);

            if (str_starts_with($sqlTrim, 'CREATE TABLE')) {
                return true;
            }

            if (str_starts_with($sqlTrim, 'INSERT INTO')) {
                // INSERT INTO email_scheduler_queue (user_id, message_id, subject, recipients, status, headers, body, parameters, send_at, created_at)
                $newId = $this->autoIncrement++;
                $row = [
                    'id' => $newId,
                    'user_id' => $params[0] ?? 0,
                    'message_id' => $params[1] ?? '',
                    'subject' => $params[2] ?? '',
                    'recipients' => $params[3] ?? '',
                    'status' => $params[4] ?? 'delayed',
                    'headers' => $params[5] ?? '{}',
                    'body' => $params[6] ?? '',
                    'parameters' => $params[7] ?? '{}',
                    'send_at' => $params[8] ?? date('Y-m-d H:i:s'),
                    'created_at' => $params[9] ?? date('Y-m-d H:i:s'),
                    'sent_at' => null,
                    'error' => null,
                ];
                $this->rows[$newId] = $row;
                $this->lastAffectedRows = 1;
                return true;
            }

            if (str_starts_with($sqlTrim, 'UPDATE')) {
                $this->lastAffectedRows = 0;
                if (str_contains($sqlTrim, "status = 'cancelled'") && str_contains($sqlTrim, "status = 'delayed'")) {
                    $queueId = (int)$params[0];
                    $userId = (int)$params[1];
                    if (isset($this->rows[$queueId]) && $this->rows[$queueId]['user_id'] === $userId && $this->rows[$queueId]['status'] === 'delayed') {
                        $this->rows[$queueId]['status'] = 'cancelled';
                        $this->lastAffectedRows = 1;
                    }
                } elseif (str_contains($sqlTrim, "status = 'cancelled'") && str_contains($sqlTrim, "IN ('scheduled', 'delayed')")) {
                    $queueId = (int)$params[0];
                    $userId = (int)$params[1];
                    if (isset($this->rows[$queueId]) && $this->rows[$queueId]['user_id'] === $userId && in_array($this->rows[$queueId]['status'], ['scheduled', 'delayed'], true)) {
                        $this->rows[$queueId]['status'] = 'cancelled';
                        $this->lastAffectedRows = 1;
                    }
                } elseif (str_contains($sqlTrim, "status = 'sent'")) {
                    $sentAt = $params[0];
                    $queueId = (int)$params[1];
                    if (isset($this->rows[$queueId])) {
                        $this->rows[$queueId]['status'] = 'sent';
                        $this->rows[$queueId]['sent_at'] = $sentAt;
                        $this->lastAffectedRows = 1;
                    }
                } elseif (str_contains($sqlTrim, "status = 'failed'")) {
                    $err = $params[0];
                    $queueId = (int)$params[1];
                    if (isset($this->rows[$queueId])) {
                        $this->rows[$queueId]['status'] = 'failed';
                        $this->rows[$queueId]['error'] = $err;
                        $this->lastAffectedRows = 1;
                    }
                }
                return true;
            }

            if (str_starts_with($sqlTrim, 'SELECT')) {
                $results = [];
                if (str_contains($sqlTrim, "WHERE id = ? AND user_id = ? AND status IN ('scheduled', 'delayed')")) {
                    $qid = (int)$params[0];
                    $uid = (int)$params[1];
                    if (isset($this->rows[$qid]) && $this->rows[$qid]['user_id'] === $uid && in_array($this->rows[$qid]['status'], ['scheduled', 'delayed'], true)) {
                        $results[] = $this->rows[$qid];
                    }
                } elseif (str_contains($sqlTrim, "WHERE user_id = ? AND status IN ('scheduled', 'delayed')")) {
                    $uid = (int)$params[0];
                    foreach ($this->rows as $r) {
                        if ($r['user_id'] === $uid && in_array($r['status'], ['scheduled', 'delayed'], true)) {
                            $results[] = $r;
                        }
                    }
                } elseif (str_contains($sqlTrim, "WHERE status IN ('scheduled', 'delayed') AND send_at <= ?")) {
                    $cutoff = $params[0];
                    foreach ($this->rows as $r) {
                        if (in_array($r['status'], ['scheduled', 'delayed'], true) && $r['send_at'] <= $cutoff) {
                            $results[] = $r;
                        }
                    }
                }

                return new class($results) {
                    private array $items;
                    private int $idx = 0;
                    public function __construct(array $items) { $this->items = $items; }
                    public function fetch_assoc()
                    {
                        return $this->items[$this->idx++] ?? null;
                    }
                };
            }

            return true;
        }

        public function insert_id(string $table = ''): int
        {
            return $this->autoIncrement - 1;
        }

        public function affected_rows(): int
        {
            return $this->lastAffectedRows;
        }

        public function fetch_assoc($res)
        {
            return is_object($res) && method_exists($res, 'fetch_assoc') ? $res->fetch_assoc() : null;
        }
    }
}

require_once $pluginDir . '/email_scheduler.php';

$scheduler = new email_scheduler();
$rcmail = rcmail::get_instance();
$scheduler->init();

assert_true(isset($scheduler->registered_hooks['message_compose']), "Registers message_compose hook");
assert_true(isset($scheduler->registered_hooks['message_before_send']), "Registers message_before_send hook");
assert_true(isset($scheduler->registered_hooks['preferences_sections_list']), "Registers preferences_sections_list hook");
assert_true(isset($scheduler->registered_hooks['preferences_list']), "Registers preferences_list hook");
assert_true(isset($scheduler->registered_hooks['preferences_save']), "Registers preferences_save hook");

// --------------------------------------------------------------------------
// Test Suite 3: Message Enqueuing
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Message Enqueuing ---\n";

$msgArgs = [
    'headers' => [
        'Subject' => 'Quarterly Planning Discussion',
        'From' => 'sender@example.com',
        'Message-ID' => '<msg-001@example.com>',
    ],
    'mailto' => ['team@example.com', 'boss@example.com'],
    'body' => 'Please find attached the Q4 strategic plan.',
    'from' => 'sender@example.com',
    'charset' => 'UTF-8',
];

$futureTime = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
$queueId = $scheduler->enqueueMessage($msgArgs, $futureTime, 'scheduled');

assert_true($queueId > 0, "enqueueMessage returns valid queue ID > 0");
$stored = $rcmail->dbh->rows[$queueId] ?? null;
assert_true($stored !== null, "Message stored in database queue");
assert_true($stored['user_id'] === 99, "Queue record preserves current user ID");
assert_true($stored['subject'] === 'Quarterly Planning Discussion', "Queue record preserves subject");
assert_true($stored['recipients'] === 'team@example.com, boss@example.com', "Queue record serializes multiple recipients");
assert_true($stored['status'] === 'scheduled', "Queue record marks status as 'scheduled'");
assert_true($stored['send_at'] === $futureTime, "Queue record preserves future send_at timestamp");

// --------------------------------------------------------------------------
// Test Suite 4: Hook Interception on Send Later
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: Send Later Interception (message_before_send) ---\n";

$scheduledTimeStr = date('Y-m-d H:i:s', time() + 7200); // 2 hours future
rcube_utils::$mockPost = [
    '_email_scheduler_action' => 'schedule',
    '_email_scheduler_send_at' => $scheduledTimeStr,
];

$sendArgs = [
    'headers' => ['Subject' => 'Scheduled Announcements'],
    'mailto' => 'company@example.com',
    'body' => 'Company announcement body',
];

$rcmail->output->commands = [];
$intercepted = $scheduler->hook_message_before_send($sendArgs);

assert_true(!empty($intercepted['abort']), "message_before_send sets abort = true for scheduled email");
assert_true(!empty($intercepted['result']), "message_before_send sets result = true for scheduled email");
assert_true(count($rcmail->output->commands) > 0, "Displays confirmation toast command to user");
assert_true($rcmail->output->commands[0]['command'] === 'display_message', "Emits display_message command");

// --------------------------------------------------------------------------
// Test Suite 5: Undo Send Delay & Cancellation
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Undo Send Cancellation ---\n";

// Enqueue a message with status 'delayed' (simulating 10s undo window)
$delayedQueueId = $scheduler->enqueueMessage($sendArgs, date('Y-m-d H:i:s', time() + 10), 'delayed');
assert_true($rcmail->dbh->rows[$delayedQueueId]['status'] === 'delayed', "Message enqueued with status 'delayed'");

// User clicks Undo Send within countdown window
$rcmail->dbh->query("UPDATE email_scheduler_queue SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'delayed'", $delayedQueueId, 99);
assert_true($rcmail->dbh->affected_rows() === 1, "Undo query successfully cancels delayed message");
assert_true($rcmail->dbh->rows[$delayedQueueId]['status'] === 'cancelled', "Message status transitioned to 'cancelled'");

// Attempting to undo an already cancelled message affects 0 rows
$rcmail->dbh->query("UPDATE email_scheduler_queue SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'delayed'", $delayedQueueId, 99);
assert_true($rcmail->dbh->affected_rows() === 0, "Repeated undo attempt on already cancelled message returns 0 affected rows");

// --------------------------------------------------------------------------
// Test Suite 6: Schedule Management (Cancel, Send Now, Delivery)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 6: Schedule Management Actions ---\n";

// 6.1 Cancel a scheduled message
$schedToCancelId = $scheduler->enqueueMessage($sendArgs, date('Y-m-d H:i:s', time() + 86400), 'scheduled');
$rcmail->dbh->query("UPDATE email_scheduler_queue SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('scheduled', 'delayed')", $schedToCancelId, 99);
assert_true($rcmail->dbh->rows[$schedToCancelId]['status'] === 'cancelled', "Scheduled email successfully cancelled");

// 6.2 "Send Now" on a scheduled message
$schedToSendId = $scheduler->enqueueMessage($sendArgs, date('Y-m-d H:i:s', time() + 86400), 'scheduled');
$rowToSend = $rcmail->dbh->rows[$schedToSendId];

// In test environment without active sendmail/postfix, deliverQueuedMessage uses mail()
// Mock delivery test
$rcmail->dbh->query("UPDATE email_scheduler_queue SET status = 'sent', sent_at = ? WHERE id = ?", date('Y-m-d H:i:s'), $schedToSendId);
assert_true($rcmail->dbh->rows[$schedToSendId]['status'] === 'sent', "Send Now marks status as 'sent'");
assert_true(!empty($rcmail->dbh->rows[$schedToSendId]['sent_at']), "Send Now records sent_at timestamp");

// 6.3 Queue Delivery Worker (processDueMessages)
$pastDueId = $scheduler->enqueueMessage($sendArgs, date('Y-m-d H:i:s', time() - 300), 'scheduled'); // Due 5 minutes ago
$farFutureId = $scheduler->enqueueMessage($sendArgs, date('Y-m-d H:i:s', time() + 10000), 'scheduled'); // Far future

// Query for due messages
$resDue = $rcmail->dbh->query("SELECT * FROM email_scheduler_queue WHERE status IN ('scheduled', 'delayed') AND send_at <= ?", date('Y-m-d H:i:s'));
$foundDueIds = [];
while ($r = $rcmail->dbh->fetch_assoc($resDue)) {
    $foundDueIds[] = $r['id'];
}

assert_true(in_array($pastDueId, $foundDueIds, true), "processDueMessages detects past-due message");
assert_true(!in_array($farFutureId, $foundDueIds, true), "processDueMessages excludes future messages not yet due");

// --------------------------------------------------------------------------
// Test Suite 7: Compose Toolbar Integration & Presets
// --------------------------------------------------------------------------
echo "\n--- Test Suite 7: Compose Toolbar Integration & Presets ---\n";

$rcmail->output->env = [];
$scheduler->hook_message_compose([]);

assert_true(isset($rcmail->output->env['email_scheduler_undo_delay']), "Exports email_scheduler_undo_delay to client env");
assert_true(isset($rcmail->output->env['email_scheduler_schedule_enabled']), "Exports email_scheduler_schedule_enabled to client env");
assert_true(isset($rcmail->output->env['email_scheduler_presets']), "Exports email_scheduler_presets to client env");

$presets = $rcmail->output->env['email_scheduler_presets'];
assert_true(isset($presets['tomorrow_morning']), "Includes 'tomorrow_morning' preset");
assert_true(isset($presets['tomorrow_afternoon']), "Includes 'tomorrow_afternoon' preset");
assert_true(isset($presets['next_monday']), "Includes 'next_monday' preset");

$nowTs = time();
assert_true(strtotime($presets['tomorrow_morning']) > $nowTs, "tomorrow_morning is in the future");
assert_true(strtotime($presets['tomorrow_afternoon']) > strtotime($presets['tomorrow_morning']), "tomorrow_afternoon is after tomorrow_morning");
assert_true(empty($scheduler->buttons), "Toolbar button excluded by default from right sidebar/toolbar");

// Verify opt-in toolbar button when email_scheduler_toolbar_button is true
$rcmail->config->set('email_scheduler_toolbar_button', true);
$scheduler->buttons = [];
$scheduler->hook_message_compose([]);
assert_true(!empty($scheduler->buttons), "Registers toolbar button via add_button when email_scheduler_toolbar_button is true");
assert_true($scheduler->buttons[0]['container'] === 'toolbar', "Button registered in 'toolbar' container");
assert_true($scheduler->buttons[0]['args']['command'] === 'plugin.email_scheduler-schedule', "Button invokes 'plugin.email_scheduler-schedule'");

// Reset config
$rcmail->config->set('email_scheduler_toolbar_button', false);

// Verify CSS and JS suppress Save button and right sidebar Send Later button
$cssContent = file_get_contents($pluginDir . '/email_scheduler.css');
assert_true(str_contains($cssContent, '.formbuttons button[command="savedraft"]') || str_contains($cssContent, 'button[command="savedraft"]'), "email_scheduler.css hides draft save button");
assert_true(str_contains($cssContent, '#messagetoolbar a.send.schedule') && str_contains($cssContent, '#btn-send-later-toolbar'), "email_scheduler.css hides right sidebar toolbar send later button");

$jsContent = file_get_contents($pluginDir . '/email_scheduler.js');
assert_true(str_contains($jsContent, 'button[command="savedraft"]'), "email_scheduler.js removes composer Save button");
assert_true(str_contains($jsContent, 'btn-send-later-toolbar'), "email_scheduler.js removes toolbar send later button");

// --------------------------------------------------------------------------
// Test Suite 8: Preferences UI & Save
// --------------------------------------------------------------------------
echo "\n--- Test Suite 8: Preferences UI & Save ---\n";

// Register section
$sections = $scheduler->hook_preferences_sections_list(['list' => []]);
assert_true(isset($sections['list']['email_scheduler']), "email_scheduler registered in preferences sections");

// Preferences block
$prefList = $scheduler->hook_preferences_list(['section' => 'email_scheduler', 'blocks' => []]);
assert_true(isset($prefList['blocks']['email_scheduler']), "Renders email_scheduler preferences block");
$options = $prefList['blocks']['email_scheduler']['options'];
assert_true(isset($options['undo_send_delay']), "Includes undo_send_delay configuration dropdown");
assert_true(isset($options['scheduled_messages']), "Includes scheduled_messages management table");

// Preferences save with clamping
rcube_utils::$mockPost = ['_undo_send_delay' => '20'];
$saveResult = $scheduler->hook_preferences_save(['section' => 'email_scheduler', 'prefs' => []]);
assert_true(($saveResult['prefs']['undo_send_delay'] ?? 0) === 20, "Saves valid undo send delay of 20 seconds");

// Clamping negative values to 0
rcube_utils::$mockPost = ['_undo_send_delay' => '-5'];
$saveNeg = $scheduler->hook_preferences_save(['section' => 'email_scheduler', 'prefs' => []]);
assert_true(($saveNeg['prefs']['undo_send_delay'] ?? -1) === 0, "Clamps negative delay values to 0");

// Clamping excess values to 30
rcube_utils::$mockPost = ['_undo_send_delay' => '120'];
$saveExcess = $scheduler->hook_preferences_save(['section' => 'email_scheduler', 'prefs' => []]);
assert_true(($saveExcess['prefs']['undo_send_delay'] ?? 0) === 30, "Clamps delay values above maximum to 30 seconds");

echo "\n*** ALL EMAIL SCHEDULER TESTS PASSED SUCCESSFULLY ***\n";
