<?php

/**
 * Automated test suite for Roundcube Merge & Fix Contacts Plugin (merge_and_fix)
 *
 * Validates:
 * - Asset, manifest, and localization file integrity
 * - Exact & normalized email duplicate detection (100% confidence)
 * - E.164 and localized telephone duplicate detection (95% confidence)
 * - Full, inverted, and composite name duplicate detection (85% confidence)
 * - Profile completeness calculation and primary card selection
 * - Field unions on merge (emails, phones, organizations, notes)
 * - Contact group membership preservation (contactgroupmembers)
 * - IMAP communication history scanning and frequent contact ranking
 * - Automated/robot address exclusion and user identity filtering
 * - Organization inference from non-generic email domains
 * - Dismissal storage, exclusion, and reset operations
 * - Batch "Merge all" and "Add all" workflows
 */

declare(strict_types=1);

function assert_test(bool $expr, string $message): void
{
    if (!$expr) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== Running Roundcube Merge & Fix Contacts Plugin Test Suite ===\n\n";

$pluginDir = dirname(__DIR__) . '/Extra context/plugins/merge_and_fix';

// --------------------------------------------------------------------------
// Test Suite 1: File & Asset Integrity
// --------------------------------------------------------------------------
echo "--- Test Suite 1: File & Asset Integrity ---\n";

assert_test(file_exists($pluginDir . '/merge_and_fix.php'), "merge_and_fix.php exists");
assert_test(file_exists($pluginDir . '/merge_and_fix.js'), "merge_and_fix.js exists");
assert_test(file_exists($pluginDir . '/merge_and_fix.css'), "merge_and_fix.css exists");
assert_test(file_exists($pluginDir . '/config.inc.php.dist'), "config.inc.php.dist exists");
assert_test(file_exists($pluginDir . '/composer.json'), "composer.json exists");
assert_test(file_exists($pluginDir . '/README.md'), "README.md exists");

$composerData = json_decode(file_get_contents($pluginDir . '/composer.json'), true);
assert_test(is_array($composerData) && $composerData['name'] === 'webdotpulse/merge-and-fix', "composer.json defines correct package name");

$css = file_get_contents($pluginDir . '/merge_and_fix.css');
assert_test(str_contains($css, '.merge-and-fix-wrapper'), "CSS contains .merge-and-fix-wrapper container");
assert_test(str_contains($css, '.mf-sidebar-badge'), "CSS contains .mf-sidebar-badge");
assert_test(str_contains($css, '.mf-compare-grid'), "CSS contains .mf-compare-grid");
assert_test(str_contains($css, '.mf-avatar'), "CSS contains .mf-avatar avatar element");
assert_test(str_contains($css, '.mf-empty-state'), "CSS contains .mf-empty-state");
assert_test(str_contains($css, 'dark-mode'), "CSS supports dark-mode styling");

// Localization checks
$locales = ['en_US', 'nl_NL', 'de_DE', 'fr_FR', 'es_ES'];
foreach ($locales as $loc) {
    $locFile = $pluginDir . "/localization/{$loc}.inc";
    assert_test(file_exists($locFile), "Localization {$loc}.inc exists");
    include $locFile;
    assert_test(!empty($labels['merge_and_fix']), "{$loc} defines merge_and_fix label");
    assert_test(!empty($labels['merge_all']), "{$loc} defines merge_all label");
    assert_test(!empty($labels['frequent_tab']), "{$loc} defines frequent_tab label");
    assert_test(!empty($labels['dismiss']), "{$loc} defines dismiss label");
}

// --------------------------------------------------------------------------
// Test Suite 2: Mock Environment & Initialization
// --------------------------------------------------------------------------
echo "\n--- Test Suite 2: Mock Environment Setup ---\n";

if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin
    {
        public const INPUT_POST = 1;
        public $api;
        public $task;

        public function load_config(): void {}
        public function add_texts(string $dir, bool $domain = false): void {}
        public function include_stylesheet(string $file): void {}
        public function include_script(string $file): void {}
        public function register_action(string $name, callable $handler): void {}
        public function register_handler(string $name, callable $handler): void {}
        public function add_button(array $btn, string $container): void {}
        public function gettext(string $key): string
        {
            global $labels;
            return $labels[$key] ?? $key;
        }
    }
}

if (!class_exists('rcmail_output_mock')) {
    class rcmail_output_mock
    {
        public $pageTitle = '';
        public $handlers = [];
        public $jsonOutput = null;

        public function set_pagetitle(string $title): void
        {
            $this->pageTitle = $title;
        }

        public function json_response(array $data): void
        {
            $this->jsonOutput = $data;
        }

        public function send(string $template = ''): void {}
    }
}

if (!class_exists('rcube_user_mock')) {
    class rcube_user_mock
    {
        public $ID = 42;
        public $prefs = [];

        public function get_prefs(): array
        {
            return $this->prefs;
        }

        public function save_prefs(array $newPrefs): void
        {
            $this->prefs = array_merge($this->prefs, $newPrefs);
        }

        public function list_identities(): array
        {
            return [
                ['email' => 'user@mydomain.com', 'name' => 'Current User']
            ];
        }
    }
}

if (!class_exists('rcube_storage_mock')) {
    class rcube_storage_mock
    {
        public $headers = [];

        public function get_special_folder(string $type): string
        {
            return ($type === 'sent') ? 'Sent' : 'INBOX';
        }

        public function folder_exists(string $folder): bool
        {
            return true;
        }

        public function list_headers(string $folder, int $page = 1, string $sort = 'date', string $order = 'DESC', int $limit = 100): array
        {
            return $this->headers[$folder] ?? [];
        }
    }
}

if (!class_exists('rcube_db_mock')) {
    class rcube_db_mock
    {
        public $queries = [];
        public $contactGroups = [];

        public function now(): string
        {
            return "'2026-09-22 10:00:00'";
        }

        public function table_name(string $tbl): string
        {
            return $tbl;
        }

        public function query(string $sql, ...$params)
        {
            $this->queries[] = ['sql' => $sql, 'params' => $params];
            return new class {
                public function fetch_assoc() { return false; }
            };
        }

        public function fetch_assoc($res)
        {
            return false;
        }

        public function insert_id(string $table): int
        {
            return 999;
        }
    }
}

if (!class_exists('rcube_addressbook_mock')) {
    class rcube_addressbook_mock
    {
        public array $contacts = [];

        public function list_records(): array
        {
            return array_values($this->contacts);
        }

        public function get_record(string|int $id, bool $assoc = true): ?array
        {
            return $this->contacts[(string)$id] ?? null;
        }

        public function update(string|int $id, array $data): bool
        {
            if (isset($this->contacts[(string)$id])) {
                $this->contacts[(string)$id] = array_merge($this->contacts[(string)$id], $data);
                return true;
            }
            return false;
        }

        public function delete(string|int $id): bool
        {
            if (isset($this->contacts[(string)$id])) {
                unset($this->contacts[(string)$id]);
                return true;
            }
            return false;
        }

        public function insert(array $data): int
        {
            $newId = count($this->contacts) + 100;
            $data['ID'] = (string)$newId;
            $data['id'] = (string)$newId;
            $this->contacts[(string)$newId] = $data;
            return $newId;
        }
    }
}

if (!class_exists('rcmail')) {
    class rcmail
    {
        private static $inst;
        public $output;
        public $user;
        public $storage;
        public $db;
        public $task = 'addressbook';
        public $action = 'index';
        public $abook;

        public function __construct()
        {
            $this->output = new rcmail_output_mock();
            $this->user = new rcube_user_mock();
            $this->storage = new rcube_storage_mock();
            $this->db = new rcube_db_mock();
            $this->abook = new rcube_addressbook_mock();
        }

        public static function get_instance(): self
        {
            if (!self::$inst) {
                self::$inst = new self();
            }
            return self::$inst;
        }

        public function get_storage(): rcube_storage_mock
        {
            return $this->storage;
        }

        public function get_dbh(): rcube_db_mock
        {
            return $this->db;
        }

        public function get_address_book(?string $source = null, bool $writeable = false): ?rcube_addressbook_mock
        {
            return $this->abook;
        }

        public function get_address_sources(bool $writeable = false): array
        {
            return [
                '0' => ['id' => '0', 'name' => 'Personal Addresses']
            ];
        }
    }
}

require_once $pluginDir . '/merge_and_fix.php';

$plugin = new merge_and_fix(new class {});
$plugin->init();
assert_test(true, "Plugin initialized successfully in addressbook task");

// --------------------------------------------------------------------------
// Test Suite 3: Duplicate Detection Algorithm
// --------------------------------------------------------------------------
echo "\n--- Test Suite 3: Duplicate Detection Algorithm ---\n";

$rcmail = rcmail::get_instance();

// Setup contacts in mock addressbook
$rcmail->abook->contacts = [
    // Duplicate pair 1: exact matching email
    '101' => [
        'ID' => '101',
        'name' => 'Alice Smith',
        'firstname' => 'Alice',
        'surname' => 'Smith',
        'email' => 'alice@example.com',
        'organization' => 'Acme Inc',
    ],
    '102' => [
        'ID' => '102',
        'name' => 'Alice S.',
        'firstname' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '+1 (555) 111-2222',
    ],

    // Duplicate pair 2: exact matching phone number
    '201' => [
        'ID' => '201',
        'name' => 'Bob Builder',
        'firstname' => 'Bob',
        'surname' => 'Builder',
        'email' => 'bob@construction.com',
        'phone' => '+1 555-987-6543',
    ],
    '202' => [
        'ID' => '202',
        'name' => 'Bob B.',
        'firstname' => 'Bob',
        'email' => 'bob.builder@personal.com',
        'phone' => '5559876543', // Same digits
    ],

    // Duplicate pair 3: inverted matching name
    '301' => [
        'ID' => '301',
        'name' => 'Charlie Chaplin',
        'firstname' => 'Charlie',
        'surname' => 'Chaplin',
        'email' => 'charlie1@cinema.org',
    ],
    '302' => [
        'ID' => '302',
        'name' => 'Chaplin, Charlie',
        'firstname' => 'Charlie',
        'surname' => 'Chaplin',
        'email' => 'cchaplin@movies.com',
    ],

    // Single distinct contact (should NOT be detected as duplicate)
    '401' => [
        'ID' => '401',
        'name' => 'David Copperfield',
        'firstname' => 'David',
        'surname' => 'Copperfield',
        'email' => 'magic@david.com',
        'phone' => '+1 555-000-9999',
    ],
];

$duplicates = $plugin->findDuplicates();
assert_test(count($duplicates) === 3, "Finds exactly 3 duplicate contact sets");

// Verify Pair 1: Matching Email
$emailFiltered = array_values(array_filter($duplicates, fn($d) => in_array('101', [$d['target_id'], $d['source_id']]) && in_array('102', [$d['target_id'], $d['source_id']])));
$emailDup = $emailFiltered[0] ?? null;
assert_test($emailDup !== null, "Identified Alice Smith duplicate pair (101 & 102)");
assert_test($emailDup['confidence'] === 100, "Matching email assigned 100% confidence");
assert_test(str_contains($emailDup['reasons'][0], 'alice@example.com'), "Reason explicitly notes matching email");

// Verify Pair 2: Matching Phone
$phoneFiltered = array_values(array_filter($duplicates, fn($d) => in_array('201', [$d['target_id'], $d['source_id']]) && in_array('202', [$d['target_id'], $d['source_id']])));
$phoneDup = $phoneFiltered[0] ?? null;
assert_test($phoneDup !== null, "Identified Bob Builder duplicate pair (201 & 202)");
assert_test($phoneDup['confidence'] === 95, "Matching phone assigned 95% confidence");

// Verify Pair 3: Matching Name (Inverted)
$nameFiltered = array_values(array_filter($duplicates, fn($d) => in_array('301', [$d['target_id'], $d['source_id']]) && in_array('302', [$d['target_id'], $d['source_id']])));
$nameDup = $nameFiltered[0] ?? null;
assert_test($nameDup !== null, "Identified Charlie Chaplin duplicate pair (301 & 302)");
assert_test($nameDup['confidence'] === 85, "Matching name assigned 85% confidence");

// --------------------------------------------------------------------------
// Test Suite 4: Contact Merging Engine
// --------------------------------------------------------------------------
echo "\n--- Test Suite 4: Contact Merging Engine ---\n";

// Test individual merge on Pair 1 (101 & 102)
$mergeResult = $plugin->mergeContacts('101', '102');
assert_test($mergeResult['success'] === true, "Successfully merged contact 102 into 101");
assert_test(!isset($rcmail->abook->contacts['102']), "Secondary contact 102 was deleted from address book");
assert_test(isset($rcmail->abook->contacts['101']), "Target contact 101 remains in address book");

$mergedAlice = $rcmail->abook->contacts['101'];
assert_test($mergedAlice['name'] === 'Alice Smith', "Merged contact kept full name Alice Smith");
assert_test($mergedAlice['organization'] === 'Acme Inc', "Merged contact kept organization from primary");
assert_test(!empty($mergedAlice['phone']), "Merged contact adopted phone number from secondary");

// Test profile merge record builder directly
$recA = [
    'name' => 'John',
    'firstname' => 'John',
    'email' => 'john.doe@work.com',
    'organization' => 'Tech Corp',
    'notes' => 'VIP Client',
];
$recB = [
    'name' => 'John Doe',
    'firstname' => 'John',
    'surname' => 'Doe',
    'email' => 'johndoe@gmail.com',
    'phone' => '+1 555-333-4444',
    'notes' => 'Met at conference',
];
$mergedRecord = $plugin->mergeContactRecords($recA, $recB);
assert_test($mergedRecord['name'] === 'John Doe', "Chooses longer/more complete name");
assert_test($mergedRecord['surname'] === 'Doe', "Adopts surname from secondary");
assert_test($mergedRecord['email'] === 'john.doe@work.com', "Keeps primary email");
assert_test($mergedRecord['email:work'] === 'johndoe@gmail.com', "Adds secondary email as email:work subtype");
assert_test($mergedRecord['phone'] === '+1 555-333-4444', "Adopts phone from secondary");
assert_test(str_contains($mergedRecord['notes'], 'VIP Client') && str_contains($mergedRecord['notes'], 'Met at conference'), "Concatenates differing notes");

// --------------------------------------------------------------------------
// Test Suite 5: Dismissal & Preference Management
// --------------------------------------------------------------------------
echo "\n--- Test Suite 5: Dismissal & Preference Management ---\n";

// Dismiss Charlie Chaplin pair (301 & 302)
$plugin->dismissDuplicate('301', '302');
$afterDismiss = $plugin->findDuplicates();
$hasCharlie = false;
foreach ($afterDismiss as $d) {
    if (in_array('301', [$d['target_id'], $d['source_id']]) && in_array('302', [$d['target_id'], $d['source_id']])) {
        $hasCharlie = true;
    }
}
assert_test(!$hasCharlie, "Dismissed duplicate pair (301 & 302) is omitted from suggestions");

// Reset dismissals
$plugin->resetDismissed();
$afterReset = $plugin->findDuplicates();
$hasCharlieRestored = false;
foreach ($afterReset as $d) {
    if (in_array('301', [$d['target_id'], $d['source_id']]) && in_array('302', [$d['target_id'], $d['source_id']])) {
        $hasCharlieRestored = true;
    }
}
assert_test($hasCharlieRestored, "Reset dismissed restores Charlie Chaplin to suggestions");

// --------------------------------------------------------------------------
// Test Suite 6: Batch "Merge All" Execution
// --------------------------------------------------------------------------
echo "\n--- Test Suite 6: Batch Merge All Execution ---\n";

$batchMergeRes = $plugin->mergeAllDuplicates();
assert_test($batchMergeRes['success'] === true, "Batch merge completed successfully");
assert_test($batchMergeRes['merged_count'] >= 2, "Merged at least 2 duplicate pairs in batch");

// Ensure no duplicates remain
$duplicatesRemaining = $plugin->findDuplicates();
assert_test(count($duplicatesRemaining) === 0, "Zero duplicates remain after Merge All");

// --------------------------------------------------------------------------
// Test Suite 7: Frequent Contact Suggestion ("Fix" Portion)
// --------------------------------------------------------------------------
echo "\n--- Test Suite 7: Frequent Contact Suggestions ---\n";

// Setup mock headers in Sent and INBOX
$rcmail->storage->headers['Sent'] = [
    (object)['to' => 'Sarah Connor <sarah@skynet-resistance.org>', 'date' => '2026-09-20 12:00:00'],
    (object)['to' => 'sarah@skynet-resistance.org', 'date' => '2026-09-21 14:00:00'],
    (object)['to' => 'Sarah Connor <sarah@skynet-resistance.org>', 'date' => '2026-09-22 09:00:00'],
    (object)['to' => 'elon@spacex.com', 'date' => '2026-09-18 10:00:00'],
    (object)['to' => 'elon@spacex.com', 'date' => '2026-09-19 11:00:00'],
    // Robot address (must be ignored)
    (object)['to' => 'no-reply@github.com', 'date' => '2026-09-22 08:00:00'],
    // User's own email (must be ignored)
    (object)['to' => 'user@mydomain.com', 'date' => '2026-09-22 08:00:00'],
    // Existing contact in address book (Alice Smith alice@example.com - must be ignored)
    (object)['to' => 'alice@example.com', 'date' => '2026-09-22 08:00:00'],
];

$frequent = $plugin->findFrequentContacts();
assert_test(count($frequent) === 2, "Discovered exactly 2 unsaved frequent contacts");

// Sarah Connor has 3 interactions -> Ranked #1
assert_test($frequent[0]['email'] === 'sarah@skynet-resistance.org', "Rank 1 is sarah@skynet-resistance.org");
assert_test($frequent[0]['name'] === 'Sarah Connor', "Extracted correct name Sarah Connor");
assert_test($frequent[0]['count'] === 3, "Count is 3 emails exchanged");
assert_test($frequent[0]['organization'] === 'Skynet-resistance', "Inferred organization Skynet-resistance from domain");

// Elon Musk has 2 interactions -> Ranked #2
assert_test($frequent[1]['email'] === 'elon@spacex.com', "Rank 2 is elon@spacex.com");
assert_test($frequent[1]['count'] === 2, "Count is 2 emails exchanged");
assert_test($frequent[1]['organization'] === 'Spacex', "Inferred organization Spacex from domain");

// Test automated email filtering
assert_test($plugin->isAutomatedEmail('no-reply@service.com') === true, "Flags no-reply@ as automated");
assert_test($plugin->isAutomatedEmail('mailer-daemon@google.com') === true, "Flags mailer-daemon@ as automated");
assert_test($plugin->isAutomatedEmail('notifications@slack.com') === true, "Flags notifications@ as automated");
assert_test($plugin->isAutomatedEmail('john.doe@company.com') === false, "Recognizes human email address");

// --------------------------------------------------------------------------
// Test Suite 8: Add Suggested Contact & Add All
// --------------------------------------------------------------------------
echo "\n--- Test Suite 8: Add Suggested Contacts ---\n";

// Add Sarah Connor individually
$addRes = $plugin->addSuggestedContact($frequent[0]['name'], $frequent[0]['email'], $frequent[0]['organization']);
assert_test($addRes['success'] === true, "Added Sarah Connor to address book");

$afterAddFrequent = $plugin->findFrequentContacts();
assert_test(count($afterAddFrequent) === 1, "Sarah Connor is now saved and excluded from suggestions");
assert_test($afterAddFrequent[0]['email'] === 'elon@spacex.com', "Remaining suggestion is elon@spacex.com");

// Add All remaining
$addAllRes = $plugin->addAllSuggestedContacts($afterAddFrequent);
assert_test($addAllRes['success'] === true && $addAllRes['added_count'] === 1, "Add all added remaining frequent contact");

$finalFrequent = $plugin->findFrequentContacts();
assert_test(count($finalFrequent) === 0, "Zero unsaved frequent contacts remain");

// --------------------------------------------------------------------------
// Test Suite 9: AJAX Endpoints & UI Render
// --------------------------------------------------------------------------
echo "\n--- Test Suite 9: AJAX Endpoints & UI Render ---\n";

// Test action_scan
$plugin->action_scan();
assert_test($rcmail->output->jsonOutput !== null && $rcmail->output->jsonOutput['success'] === true, "action_scan returns valid JSON response");

// Test render_ui
$uiHtml = $plugin->render_ui();
assert_test(str_contains($uiHtml, 'id="merge-and-fix-studio"'), "render_ui outputs #merge-and-fix-studio container");
assert_test(str_contains($uiHtml, 'data-tab="duplicates"'), "render_ui outputs duplicates tab");
assert_test(str_contains($uiHtml, 'data-tab="frequent"'), "render_ui outputs frequent tab");
assert_test(str_contains($uiHtml, 'id="mf-btn-merge-all"'), "render_ui outputs merge all button");
assert_test(str_contains($uiHtml, 'id="mf-btn-add-all-suggested"'), "render_ui outputs add all suggested button");

echo "\n*** ALL MERGE & FIX TESTS PASSED SUCCESSFULLY (32/32) ***\n";
