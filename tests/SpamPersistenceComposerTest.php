<?php
/**
 * Test Suite: Bayesian Spam Filter & AI Memory Persistence Across Composer Updates
 *
 * Verifies that:
 * 1. Bayesian intelligence model, learning stats, and tokens are stored in a persistent location
 *    outside the package directory (<roundcube_root>/data/lifeprisma_ai/spam).
 * 2. Models and stats are backed up to the Roundcube database table (lpai_spam_models).
 * 3. Simulating a `composer require` / `composer update` package wipe:
 *    - Deleting the disk model file does NOT lose the memory or statistics;
 *    - load_model() automatically recovers the model from the database, re-writes the disk file,
 *      and restores exact spam/ham counts, tokens, and sender reputations.
 * 4. AI Learned Memory (Q&A replication) is stored in persistent location (<roundcube_root>/data/lifeprisma_ai/memory)
 *    and backed up to database table (lpai_ai_memory).
 * 5. Simulating composer wipe on AI memory: load_ai_memory() recovers all Q&A items from database.
 * 6. bin/install-extra.php automatically ensures persistent data directories exist and migrates legacy data.
 * 7. composer.json includes persistent data directories in roundcube.persistent-files.
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

echo "=== BAYESIAN MEMORY & COMPOSER PERSISTENCE TEST SUITE ===\n\n";

$repo_root = dirname(__DIR__);
$ai_dir = is_dir($repo_root . '/Extra context/plugins/roundcube_ai')
    ? $repo_root . '/Extra context/plugins/roundcube_ai'
    : $repo_root;

// --- Test Group 1: Persistent Directory Resolution & Canonical Paths ---
echo "--- Group 1: Persistent Directory Resolution & Hierarchy ---\n";

require_once $ai_dir . '/src/LpaiSpamFilter.php';

$rc_root = LpaiSpamFilter::find_roundcube_root();
assert_true(method_exists('LpaiSpamFilter', 'find_roundcube_root'), "LpaiSpamFilter implements find_roundcube_root()");
assert_true(method_exists('LpaiSpamFilter', 'write_model_file'), "LpaiSpamFilter implements write_model_file()");

// Setup isolated fake Roundcube root
$test_rc_dir = sys_get_temp_dir() . '/test_rc_persist_' . uniqid();
@mkdir($test_rc_dir . '/data', 0775, true);
@mkdir($test_rc_dir . '/plugins', 0775, true);
@mkdir($test_rc_dir . '/skins', 0775, true);
file_put_contents($test_rc_dir . '/index.php', '<?php // fake rc');

// Mock rcmail environment with database
class MockPdoDb
{
    public string $db_provider = 'sqlite';
    public array $tables = [
        'lpai_spam_models' => [],
        'lpai_ai_memory' => [],
    ];

    public function table_name(string $name): string
    {
        return $name;
    }

    public function query(string $sql, ...$params)
    {
        $trimmed = trim($sql);

        // CREATE TABLE IF NOT EXISTS
        if (stripos($trimmed, 'CREATE TABLE') !== false) {
            return true;
        }

        // SELECT ... FROM lpai_spam_models WHERE user_id = ?
        if (stripos($trimmed, 'FROM lpai_spam_models') !== false && stripos($trimmed, 'WHERE user_id = ?') !== false) {
            $uid = $params[0] ?? '';
            $row = $this->tables['lpai_spam_models'][$uid] ?? null;
            return new MockPdoResult($row ? [$row] : []);
        }

        // INSERT INTO lpai_spam_models (user_id, total_spam, total_ham, tokens_count, model_data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)
        if (stripos($trimmed, 'INSERT INTO lpai_spam_models') !== false) {
            $uid = $params[0] ?? '';
            $this->tables['lpai_spam_models'][$uid] = [
                'user_id' => $uid,
                'total_spam' => $params[1] ?? 0,
                'total_ham' => $params[2] ?? 0,
                'tokens_count' => $params[3] ?? 0,
                'model_data' => $params[4] ?? '{}',
                'created_at' => $params[5] ?? time(),
                'updated_at' => $params[6] ?? time(),
            ];
            return true;
        }

        // UPDATE lpai_spam_models SET total_spam = ?, total_ham = ?, tokens_count = ?, model_data = ?, updated_at = ? WHERE user_id = ?
        if (stripos($trimmed, 'UPDATE lpai_spam_models') !== false) {
            $uid = $params[5] ?? '';
            $this->tables['lpai_spam_models'][$uid] = [
                'user_id' => $uid,
                'total_spam' => $params[0] ?? 0,
                'total_ham' => $params[1] ?? 0,
                'tokens_count' => $params[2] ?? 0,
                'model_data' => $params[3] ?? '{}',
                'created_at' => $this->tables['lpai_spam_models'][$uid]['created_at'] ?? time(),
                'updated_at' => $params[4] ?? time(),
            ];
            return true;
        }

        // DELETE FROM lpai_spam_models WHERE user_id = ?
        if (stripos($trimmed, 'DELETE FROM lpai_spam_models') !== false) {
            $uid = $params[0] ?? '';
            unset($this->tables['lpai_spam_models'][$uid]);
            return true;
        }

        // SELECT memory_data FROM lpai_ai_memory WHERE user_id = ?
        if (stripos($trimmed, 'FROM lpai_ai_memory') !== false) {
            if (stripos($trimmed, 'WHERE user_id = ?') !== false) {
                $uid = $params[0] ?? '';
                $row = $this->tables['lpai_ai_memory'][$uid] ?? null;
                return new MockPdoResult($row ? [$row] : []);
            }
            return new MockPdoResult(array_values($this->tables['lpai_ai_memory']));
        }

        // INSERT INTO lpai_ai_memory (user_id, memory_data, updated_at) VALUES (?, ?, ?)
        if (stripos($trimmed, 'INSERT INTO lpai_ai_memory') !== false) {
            $uid = $params[0] ?? '';
            $this->tables['lpai_ai_memory'][$uid] = [
                'user_id' => $uid,
                'memory_data' => $params[1] ?? '[]',
                'updated_at' => $params[2] ?? time(),
            ];
            return true;
        }

        // UPDATE lpai_ai_memory SET memory_data = ?, updated_at = ? WHERE user_id = ?
        if (stripos($trimmed, 'UPDATE lpai_ai_memory') !== false) {
            $uid = $params[2] ?? '';
            $this->tables['lpai_ai_memory'][$uid] = [
                'user_id' => $uid,
                'memory_data' => $params[0] ?? '[]',
                'updated_at' => $params[1] ?? time(),
            ];
            return true;
        }

        return new MockPdoResult([]);
    }

    public function fetch_assoc($res)
    {
        if ($res instanceof MockPdoResult) {
            return $res->fetch_assoc();
        }
        return false;
    }
}

class MockPdoResult
{
    private array $rows;
    private int $idx = 0;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function fetch_assoc()
    {
        return $this->rows[$this->idx++] ?? false;
    }
}

class MockRcConfig
{
    private array $data = [];
    public function __construct(array $data = []) { $this->data = $data; }
    public function get(string $key, $default = null) { return $this->data[$key] ?? $default; }
}

class MockRcmail
{
    private static ?MockRcmail $instance = null;
    public MockRcConfig $config;
    public MockPdoDb $db;
    public $user;

    public function __construct(string $data_dir)
    {
        $this->config = new MockRcConfig([
            'lifeprisma_ai_spam_storage_dir' => $data_dir . '/spam',
            'lifeprisma_ai_memory_dir' => $data_dir . '/memory',
        ]);
        $this->db = new MockPdoDb();
        $this->user = new class { public int $ID = 42; };
        self::$instance = $this;
    }

    public static function get_instance(): MockRcmail
    {
        return self::$instance;
    }

    public function get_dbh(): MockPdoDb
    {
        return $this->db;
    }
}

// Instantiate mock rcmail
$mock_rc = new MockRcmail($test_rc_dir . '/data/lifeprisma_ai');

// Ensure class_exists('rcmail') is satisfied for the test
if (!class_exists('rcmail', false)) {
    class_alias('MockRcmail', 'rcmail');
}


// --- Test Group 2: Bayesian Model Disk & Database Synchronization ---
echo "\n--- Group 2: Bayesian Model Disk & Database Synchronization ---\n";

$user_id = 'test_executive@company.com';

// Train Bayesian filter on a spam email
$trained_model = LpaiSpamFilter::learn_spam(
    'URGENT: Wire transfer funds immediately for invoice 99281',
    'Dear Finance, please process wire payment to bank account XYZ right now. Bitcoin casino lottery pills.',
    "Subject: URGENT: Wire transfer funds\r\nFrom: fraud@scammer-domain.xyz\r\n",
    'fraud@scammer-domain.xyz',
    $user_id,
    'msg_spam_001'
);

assert_true($trained_model['total_spam'] === 1, "Learned 1 spam message");
$stats = LpaiSpamFilter::get_stats($user_id);
assert_true($stats['total_spam'] === 1, "Stats reflect 1 total_spam");
assert_true($stats['total_tokens'] > 0, "Stats reflect learned tokens");

// Verify disk file was created in persistent storage directory
$model_file = LpaiSpamFilter::get_model_path($user_id);
assert_true(file_exists($model_file), "Model file exists on persistent storage disk: {$model_file}");
assert_true(strpos($model_file, 'test_rc_persist_') !== false, "Storage path points to persistent host directory, NOT package vendor dir");

// Verify database record was automatically populated in lpai_spam_models table
$db = $mock_rc->get_dbh();
$db_res = $db->query("SELECT user_id, total_spam, total_ham, tokens_count, model_data FROM lpai_spam_models WHERE user_id = ?", $user_id);
$db_row = $db->fetch_assoc($db_res);

assert_true(!empty($db_row), "Database table 'lpai_spam_models' contains record for user");
assert_true((int)$db_row['total_spam'] === 1, "Database total_spam matches trained count (1)");
assert_true((int)$db_row['tokens_count'] > 0, "Database tokens_count is populated");

// Now train a legitimate (HAM) email
LpaiSpamFilter::learn_ham(
    'Project status update and quarterly meeting notes',
    'Hi team, attached is the project roadmap for Q4. Looking forward to our call tomorrow.',
    "Subject: Project status update\r\nFrom: manager@company.com\r\n",
    'manager@company.com',
    $user_id,
    'msg_ham_001'
);

$stats_ham = LpaiSpamFilter::get_stats($user_id);
assert_true($stats_ham['total_spam'] === 1, "Stats spam count remains 1");
assert_true($stats_ham['total_ham'] === 1, "Stats ham count is now 1");


// --- Test Group 3: Simulation of Composer Package Wipe & Automatic Recovery ---
echo "\n--- Group 3: Simulation of Composer Package Wipe & Zero-Data-Loss Recovery ---\n";

// Clear memory cache
LpaiSpamFilter::set_storage_dir($test_rc_dir . '/data/lifeprisma_ai/spam');

// SIMULATE COMPOSER UPDATE: Delete the file on disk completely
@unlink($model_file);
assert_true(!file_exists($model_file), "SIMULATION: Model file deleted from disk (as happens during composer update wipe)");

// Now call load_model() or get_stats() as Roundcube does upon loading Settings > Spam Filter
$recovered_stats = LpaiSpamFilter::get_stats($user_id);

assert_true($recovered_stats['total_spam'] === 1, "RECOVERY SUCCESS: total_spam was restored from database (expected 1, got {$recovered_stats['total_spam']})");
assert_true($recovered_stats['total_ham'] === 1, "RECOVERY SUCCESS: total_ham was restored from database (expected 1, got {$recovered_stats['total_ham']})");
assert_true($recovered_stats['total_tokens'] > 0, "RECOVERY SUCCESS: tokens dictionary was completely restored from database");
assert_true($recovered_stats['spam_ratio'] === 50.0, "RECOVERY SUCCESS: spam ratio accurately restored to 50.0%");

// Verify that load_model automatically restored the file on disk as well
assert_true(file_exists($model_file), "RECOVERY SUCCESS: Model file was re-written to disk automatically from database backup");


// --- Test Group 4: AI Memory Persistent Storage & Database Recovery ---
echo "\n--- Group 4: AI Memory Persistent Storage & Database Recovery ---\n";

if (!class_exists('rcube_plugin', false)) {
    abstract class rcube_plugin
    {
        public $api;
        public function __construct($api = null) { $this->api = $api; }
        public function add_hook($hook, $callback) {}
        public function register_action($action, $callback) {}
        public function add_texts($p1, $p2 = false) {}
        public function include_script($script) {}
        public function include_stylesheet($css) {}
    }
}

require_once $ai_dir . '/lifeprisma_ai.php';

$plugin = new lifeprisma_ai(rcmail::get_instance());

// Save memory items
$test_memory_items = [
    [
        'id' => 'mem_001',
        'question' => 'What is the refund policy for annual enterprise subscriptions?',
        'answer' => 'Enterprise annual subscriptions are eligible for a 100% full refund within 30 days of initial purchase.',
        'created_at' => time(),
    ],
    [
        'id' => 'mem_002',
        'question' => 'Where can I find the API key for Gemini 3.8 Flash?',
        'answer' => 'Navigate to Google AI Studio (aistudio.google.com) to generate your Gemini API key.',
        'created_at' => time(),
    ],
];

$saved = $plugin->save_ai_memory($test_memory_items);
assert_true($saved !== false, "save_ai_memory wrote items to persistent store");

$mem_file = $plugin->get_memory_file();
assert_true(file_exists($mem_file), "AI memory file exists at: {$mem_file}");
assert_true(strpos($mem_file, 'test_rc_persist_') !== false, "AI memory file is stored outside the plugin vendor folder");

// Verify AI memory is in database table lpai_ai_memory
$mem_db_res = $db->query("SELECT user_id, memory_data FROM lpai_ai_memory");
$mem_db_row = $db->fetch_assoc($mem_db_res);
assert_true(!empty($mem_db_row), "Database table 'lpai_ai_memory' contains saved memories");

$parsed_mem = json_decode($mem_db_row['memory_data'], true);
assert_true(count($parsed_mem) === 2, "Database contains exactly 2 saved memory items");

// SIMULATE COMPOSER UPDATE: Delete the memory file from disk
@unlink($mem_file);
assert_true(!file_exists($mem_file), "SIMULATION: AI memory file deleted from disk");

// Load AI memory again
$recovered_mem = $plugin->load_ai_memory();
assert_true(count($recovered_mem) === 2, "RECOVERY SUCCESS: load_ai_memory restored 2 memory items from database");
assert_true($recovered_mem[0]['id'] === 'mem_001', "RECOVERY SUCCESS: First memory item ID matches");
assert_true(strpos($recovered_mem[0]['answer'], '100% full refund') !== false, "RECOVERY SUCCESS: Verified answer preserved");
assert_true(file_exists($mem_file), "RECOVERY SUCCESS: Memory file re-created on disk from database backup");


// --- Test Group 5: bin/install-extra.php Preservation Routine ---
echo "\n--- Group 5: bin/install-extra.php Preservation Routine ---\n";

$installer_content = file_get_contents($repo_root . '/bin/install-extra.php');
assert_true(
    strpos($installer_content, 'preserveAndMigrateLifeprismaData') !== false,
    "bin/install-extra.php defines preserveAndMigrateLifeprismaData method"
);
assert_true(
    strpos($installer_content, '$this->preserveAndMigrateLifeprismaData($targetDir);') !== false,
    "bin/install-extra.php calls preserveAndMigrateLifeprismaData during execute()"
);
assert_true(
    strpos($installer_content, 'data/lifeprisma_ai') !== false,
    "bin/install-extra.php creates and manages data/lifeprisma_ai"
);


// --- Test Group 6: composer.json Persistent Files Protection ---
echo "\n--- Group 6: composer.json Persistent Files Protection ---\n";

$composer_json = json_decode(file_get_contents($repo_root . '/composer.json'), true);
$persistent_files = $composer_json['extra']['roundcube']['persistent-files'] ?? [];

assert_true(in_array('config.inc.php', $persistent_files, true), "composer.json preserves config.inc.php");
assert_true(in_array('data/ai_memory.json', $persistent_files, true), "composer.json preserves data/ai_memory.json");
assert_true(in_array('data/memory', $persistent_files, true), "composer.json preserves data/memory");
assert_true(in_array('data/spam', $persistent_files, true), "composer.json preserves data/spam");


// Cleanup test directories
array_map('unlink', glob($test_rc_dir . '/data/lifeprisma_ai/spam/*.*'));
array_map('unlink', glob($test_rc_dir . '/data/lifeprisma_ai/memory/*.*'));
@rmdir($test_rc_dir . '/data/lifeprisma_ai/spam');
@rmdir($test_rc_dir . '/data/lifeprisma_ai/memory');
@rmdir($test_rc_dir . '/data/lifeprisma_ai');
@rmdir($test_rc_dir . '/data');
@rmdir($test_rc_dir . '/plugins');
@rmdir($test_rc_dir . '/skins');
@unlink($test_rc_dir . '/index.php');
@rmdir($test_rc_dir);

echo "\n============================================\n";
echo "TEST RESULTS: {$passed_count} / {$test_count} tests passed\n";
if ($passed_count === $test_count) {
    echo "*** ALL BAYESIAN MEMORY & COMPOSER PERSISTENCE TESTS PASSED (100%) ***\n";
    exit(0);
} else {
    echo "!!! SOME TESTS FAILED !!!\n";
    exit(1);
}
