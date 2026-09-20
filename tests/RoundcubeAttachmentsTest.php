<?php

/**
 * Automated test suite for roundcube_attachments plugin
 *
 * Tests:
 * 1. Plugin class structure, actions & hooks registration.
 * 2. Storage engine: file saving, listing, retrieval, security validation, and deletion.
 * 3. Reactions (canned responses) enhancements: setting subject and attachments per response.
 * 4. Compose integration: attaching server files to compose sessions.
 * 5. Headless Chrome UI tests: DOM injections in Settings -> Responses and Compose window.
 */

declare(strict_types=1);

$test_count = 0;
$passed_count = 0;

function assert_true(bool $cond, string $desc): void
{
    global $test_count, $passed_count;
    $test_count++;
    if ($cond) {
        $passed_count++;
        echo "PASSED: {$desc}\n";
    } else {
        echo "FAILED: {$desc}\n";
        exit(1);
    }
}

echo "=== ROUNDCUBE ATTACHMENTS & REACTIONS TEST SUITE ===\n\n";

// Setup Mock Roundcube Environment if not already loaded
if (!class_exists('html')) {
    class html
    {
        public static function quote($str)
        {
            return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
}

if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;
        public $session;
        public $task = 'mail';
        public $action = 'compose';

        public function __construct()
        {
            $this->config = new class {
                private $data = [
                    'skin' => 'elastic',
                    'roundcube_attachments_max_filesize' => 25 * 1024 * 1024,
                ];
                public function get($k, $d = null) { return $this->data[$k] ?? $d; }
                public function set($k, $v) { $this->data[$k] = $v; }
            };
            $this->output = new class {
                public $env = [];
                public $commands = [];
                public $footer = '';
                public function set_env($k, $v) { $this->env[$k] = $v; }
                public function add_footer($html) { $this->footer .= $html; }
                public function command($cmd, ...$args) { $this->commands[] = ['cmd' => $cmd, 'args' => $args]; }
                public function reset() { $this->commands = []; }
                public function send($t = null) {}
                public function include_script($s) {}
                public function include_stylesheet($s) {}
            };
            $this->user = new class {
                public $ID = 42;
                public $prefs = [];
                public function get_prefs() { return $this->prefs; }
                public function save_prefs($new_prefs) {
                    $this->prefs = array_merge($this->prefs, $new_prefs);
                    return true;
                }
            };
            $this->session = new class {
                public function append($key, $item_key, $val) {
                    // split key like compose_data_123.attachments
                    $parts = explode('.', $key);
                    if (count($parts) === 2) {
                        $_SESSION[$parts[0]][$parts[1]][$item_key] = $val;
                    }
                }
            };
        }

        public static function get_instance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public static function reset_instance()
        {
            self::$instance = new self();
            return self::$instance;
        }

        public function get_user_name() { return 'testuser'; }
    }

    class rcmail extends rcube {}

    class rcube_plugin
    {
        public $actions = [];
        public $hooks = [];

        public function load_config() {}
        public function add_texts($d, $fallback = true) {}
        public function register_action($a, $cb) { $this->actions[$a] = $cb; }
        public function add_hook($h, $cb) { $this->hooks[$h][] = $cb; }
        public function include_script($s) {}
        public function include_stylesheet($s) {}
        public function gettext($key) { return $key; }
    }

    class rcube_utils
    {
        const INPUT_GET = 1;
        const INPUT_POST = 2;
        const INPUT_GP = 3;
        const INPUT_GPC = 4;

        public static function get_input_string($name, $source)
        {
            if ($source === self::INPUT_POST) return $_POST[$name] ?? '';
            if ($source === self::INPUT_GET) return $_GET[$name] ?? '';
            if ($source === self::INPUT_GP || $source === self::INPUT_GPC) {
                return $_POST[$name] ?? $_GET[$name] ?? $_REQUEST[$name] ?? '';
            }
            return $_REQUEST[$name] ?? '';
        }

        public static function get_input_value($name, $source)
        {
            if ($source === self::INPUT_POST) return $_POST[$name] ?? null;
            if ($source === self::INPUT_GET) return $_GET[$name] ?? null;
            if ($source === self::INPUT_GP || $source === self::INPUT_GPC) {
                return $_POST[$name] ?? $_GET[$name] ?? $_REQUEST[$name] ?? null;
            }
            return $_REQUEST[$name] ?? null;
        }

        public static function file2class($mime, $filename)
        {
            return 'file-icon';
        }
    }
}

if (!session_id()) {
    @session_start();
}

require_once __DIR__ . '/../Extra context/plugins/roundcube_attachments/roundcube_attachments.php';

// Setup isolated temp data dir for testing
$testStorageDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rc_att_test_' . uniqid();
@mkdir($testStorageDir, 0777, true);

$rcmail = rcmail::get_instance();
$rcmail->config->set('roundcube_attachments_dir', $testStorageDir);

// ==========================================
// Test 1: Plugin Class & Hook Registration
// ==========================================
echo "--- Test 1: Plugin Initialization & Hooks Registration ---\n";
$plugin = new roundcube_attachments();
$plugin->init();

assert_true(isset($plugin->actions['plugin.roundcube_attachments_list']), "Action plugin.roundcube_attachments_list registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_upload']), "Action plugin.roundcube_attachments_upload registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_delete']), "Action plugin.roundcube_attachments_delete registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_download']), "Action plugin.roundcube_attachments_download registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_attach_to_compose']), "Action plugin.roundcube_attachments_attach_to_compose registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_save_meta']), "Action plugin.roundcube_attachments_save_meta registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_get_meta']), "Action plugin.roundcube_attachments_get_meta registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments_update_file']), "Action plugin.roundcube_attachments_update_file registered");
assert_true(isset($plugin->actions['plugin.roundcube_attachments']), "Action plugin.roundcube_attachments registered");

assert_true(isset($plugin->hooks['settings_actions']), "Hook settings_actions registered");
assert_true(isset($plugin->hooks['get_compose_response']), "Hook get_compose_response registered");
assert_true(isset($plugin->hooks['get_compose_responses']), "Hook get_compose_responses registered");
assert_true(isset($plugin->hooks['response_create']), "Hook response_create registered");
assert_true(isset($plugin->hooks['response_update']), "Hook response_update registered");
assert_true(isset($plugin->hooks['response_delete']), "Hook response_delete registered");
assert_true(isset($plugin->hooks['render_page']), "Hook render_page registered");

// ==========================================
// Test 2: Storage Engine & File Security
// ==========================================
echo "\n--- Test 2: Server Storage & Security Engine ---\n";

// Test directory security
$baseDir = $plugin->get_storage_dir();
assert_true(is_dir($baseDir), "Storage directory created");
assert_true(file_exists($baseDir . '/.htaccess'), ".htaccess file created in storage dir");
assert_true(file_exists($baseDir . '/index.php'), "index.php created in storage dir");

$htaccessContent = file_get_contents($baseDir . '/.htaccess');
assert_true(strpos($htaccessContent, 'Require all denied') !== false || strpos($htaccessContent, 'Deny from all') !== false, ".htaccess denies web access");

// Test saving a valid file
$pdfContent = "%PDF-1.4 sample pdf document content for testing";
$res1 = $plugin->save_attachment("Company_Brochure.pdf", $pdfContent, "application/pdf");
assert_true($res1['status'] === true, "save_attachment succeeds for valid PDF");
assert_true(!empty($res1['record']['id']), "save_attachment returns file ID");
assert_true($res1['record']['name'] === "Company_Brochure.pdf", "Original filename preserved in record");
assert_true($res1['record']['size'] === strlen($pdfContent), "File size matches accurately");
assert_true(strpos($res1['record']['storage_name'], '.dat') !== false, "Stored file uses safe .dat extension");

$attId = $res1['record']['id'];

// Test listing files
$filesList = $plugin->list_attachments();
assert_true(count($filesList) === 1, "list_attachments returns 1 file");
assert_true($filesList[0]['id'] === $attId, "list_attachments contains saved file ID");

// Test getting single file
$fetched = $plugin->get_attachment($attId);
assert_true($fetched !== null, "get_attachment retrieves stored file");
assert_true($fetched['data'] === $pdfContent, "get_attachment returns identical binary content");

// Test saving second file
$imageContent = "IMAGE_BINARY_BYTES";
$res2 = $plugin->save_attachment("Price_List.xlsx", $imageContent, "application/vnd.ms-excel");
assert_true($res2['status'] === true, "save_attachment succeeds for second file");
$attId2 = $res2['record']['id'];
assert_true(count($plugin->list_attachments()) === 2, "list_attachments returns 2 files");

// Security Tests: Rejection of dangerous files
$phpRes = $plugin->save_attachment("exploit.php", "<?php phpinfo(); ?>", "application/x-php");
assert_true($phpRes['status'] === false, "Rejects dangerous .php extension");
assert_true($phpRes['error'] === 'error_file_type', "Returns error_file_type on .php");

$shRes = $plugin->save_attachment("script.sh", "#!/bin/bash\nrm -rf /", "text/x-shellscript");
assert_true($shRes['status'] === false, "Rejects dangerous .sh extension");

$exeRes = $plugin->save_attachment("trojan.exe", "MZ...", "application/x-msdownload");
assert_true($exeRes['status'] === false, "Rejects dangerous .exe extension");

// Security Tests: Path Traversal
$checkTraversal = $plugin->sanitize_filename("../../etc/passwd");
assert_true($checkTraversal['filename'] === 'passwd', "Sanitizes directory traversal from filename");

// Test file deletion
$delResult = $plugin->delete_attachment($attId2);
assert_true($delResult === true, "delete_attachment returns true");
assert_true(count($plugin->list_attachments()) === 1, "Attachment count decreases after delete");
assert_true($plugin->get_attachment($attId2) === null, "Deleted attachment can no longer be retrieved");

// ==========================================
// Test 3: Reactions (Responses) Enhancements
// ==========================================
echo "\n--- Test 3: Reaction Subject & Attachments Metadata ---\n";

// Save response metadata
$savedMeta = $plugin->save_response_meta(101, "Thank you for contacting our sales team", [$attId]);
assert_true($savedMeta === true, "save_response_meta succeeds for response 101");

$meta = $plugin->get_response_meta(101);
assert_true($meta['subject'] === "Thank you for contacting our sales team", "Retrieved subject matches saved value");
assert_true($meta['attachments'] === [$attId], "Retrieved attachments array matches saved value");

// Test hook_get_compose_response
$rec = [
    'id' => 101,
    'name' => 'Sales Response',
    'data' => '<p>Hello, please find our brochure attached.</p>',
    'is_html' => true,
];
$hookRes = $plugin->hook_get_compose_response(['id' => 101, 'record' => $rec]);
assert_true($hookRes['record']['subject'] === "Thank you for contacting our sales team", "hook_get_compose_response injects subject into record");
assert_true($hookRes['record']['attachments'] === [$attId], "hook_get_compose_response injects attachments into record");
assert_true(!empty($hookRes['record']['attachments_files']), "hook_get_compose_response injects expanded file metadata");
assert_true($hookRes['record']['attachments_files'][0]['name'] === "Company_Brochure.pdf", "Expanded file name matches");

// Test hook_get_compose_responses (list view)
$listHook = $plugin->hook_get_compose_responses([
    'list' => [
        ['id' => 101, 'name' => 'Sales Response'],
        ['id' => 102, 'name' => 'Support Response'],
    ]
]);
assert_true($listHook['list'][0]['has_attachments'] === true, "hook_get_compose_responses flags has_attachments for response 101");
assert_true($listHook['list'][0]['attachments_count'] === 1, "hook_get_compose_responses counts attachments");
assert_true($listHook['list'][1]['has_attachments'] === false, "hook_get_compose_responses flags false for response without attachments");

// Test hook_response_update
$_POST['_subject'] = "Updated Response Subject";
$_POST['_attachments'] = json_encode([$attId]);
$plugin->hook_response_update(['id' => 101]);
$updatedMeta = $plugin->get_response_meta(101);
assert_true($updatedMeta['subject'] === "Updated Response Subject", "hook_response_update persists modified subject");

// Test hook_response_create & hook_render_page synchronization
$_POST['_subject'] = "Brand New Response Subject";
$_POST['_attachments'] = json_encode([$attId]);
$plugin->hook_response_create(['record' => ['name' => 'Brand New']]);
assert_true(!empty($_SESSION['roundcube_attachments_pending_response_meta']), "hook_response_create stashes pending metadata");

// Simulate Roundcube finishing insert and rendering responseedit with new ID 105
$_GET['_id'] = 105;
$plugin->hook_render_page(['template' => 'responseedit']);
$createdMeta = $plugin->get_response_meta(105);
assert_true($createdMeta['subject'] === "Brand New Response Subject", "hook_render_page commits pending metadata to new response ID 105");
assert_true(empty($_SESSION['roundcube_attachments_pending_response_meta']), "Pending metadata cleared after commit");

// Test hook_response_delete
$plugin->hook_response_delete(['id' => 101]);
$afterDelMeta = $plugin->get_response_meta(101);
assert_true($afterDelMeta['subject'] === '', "Subject cleared after hook_response_delete");
assert_true(empty($afterDelMeta['attachments']), "Attachments cleared after hook_response_delete");

// ==========================================
// Test 4: Compose Integration (Attaching)
// ==========================================
echo "\n--- Test 4: Compose Session Attachment Injection ---\n";

$composeId = 'test_compose_session_' . uniqid();
$_SESSION['compose_data_' . $composeId] = ['attachments' => []];

$_POST['composeId'] = $composeId;
$_POST['fileIds'] = [$attId];
$_POST['uploadId'] = 'upload_test_1';

$rcmail->output->reset();

// Execute action_attach_to_compose
$refClass = new ReflectionClass('roundcube_attachments');
$attachMethod = $refClass->getMethod('action_attach_to_compose');
$attachMethod->invoke($plugin);

// Verify file was added to compose session
$sessionAttachments = $_SESSION['compose_data_' . $composeId]['attachments'];
assert_true(!empty($sessionAttachments), "File successfully attached to compose session");
$attachedItem = reset($sessionAttachments);
assert_true($attachedItem['name'] === "Company_Brochure.pdf", "Attached item name matches");
assert_true($attachedItem['data'] === $pdfContent, "Attached item content matches");

// Verify Roundcube command was issued to client
$cmds = $rcmail->output->commands;
$hasDisplayMsg = false;
$hasAdd2List = false;
foreach ($cmds as $c) {
    if ($c['cmd'] === 'display_message') $hasDisplayMsg = true;
    if ($c['cmd'] === 'add2attachment_list') $hasAdd2List = true;
}
assert_true($hasDisplayMsg, "display_message confirmation command dispatched");
assert_true($hasAdd2List, "add2attachment_list UI update command dispatched");

// ==========================================
// Test 5: Native Settings Page & File Descriptions
// ==========================================
echo "\n--- Test 5: Native Settings Page & File Descriptions ---\n";

// Test settings_actions hook
$settings_args = $plugin->settings_actions(['actions' => []]);
assert_true(!empty($settings_args['actions']), "settings_actions hook returns actions array");
$found_action = false;
foreach ($settings_args['actions'] as $act) {
    if (($act['action'] ?? '') === 'plugin.roundcube_attachments') {
        $found_action = true;
        assert_true(($act['class'] ?? '') === 'server-attachments', "settings action class is server-attachments");
        assert_true(($act['label'] ?? '') === 'roundcube_attachments.server_attachments', "settings action label matches");
    }
}
assert_true($found_action, "settings_actions hook includes plugin.roundcube_attachments");

// Test saving attachment with description
$desc_res = $plugin->save_attachment('rates_2026.pdf', '%PDF-sample-rates', 'application/pdf', 'Tarievenoverzicht 2026 voor beheer');
assert_true($desc_res['status'], "save_attachment with description succeeds");
$desc_att_id = $desc_res['record']['id'];
assert_true($desc_res['record']['description'] === 'Tarievenoverzicht 2026 voor beheer', "Stored record contains description");

// Test listing attachments includes description
$all_list = $plugin->list_attachments();
$found_item = null;
foreach ($all_list as $item) {
    if ($item['id'] === $desc_att_id) {
        $found_item = $item;
        break;
    }
}
assert_true($found_item !== null, "Attachment with description found in list_attachments");
assert_true(($found_item['description'] ?? '') === 'Tarievenoverzicht 2026 voor beheer', "list_attachments preserves description");

// Test updating attachment description
$update_ok = $plugin->update_attachment_description($desc_att_id, 'Bijgewerkt tarievenoverzicht 2026');
assert_true($update_ok, "update_attachment_description returns true");

$meta_after = $plugin->load_meta();
assert_true(($meta_after[$desc_att_id]['description'] ?? '') === 'Bijgewerkt tarievenoverzicht 2026', "Metadata persistence confirmed for updated description");

// Test rendering settings view HTML
$settings_html = $plugin->render_settings_view();
assert_true(strpos($settings_html, 'id="rc-server-att-settings"') !== false, "Settings view contains #rc-server-att-settings container");
assert_true(strpos($settings_html, 'id="rc-settings-search"') !== false, "Settings view contains search bar");
assert_true(strpos($settings_html, 'id="rc-server-att-dropzone"') !== false, "Settings view contains upload dropzone");
assert_true(strpos($settings_html, 'id="rc-server-att-table"') !== false, "Settings view contains file table");
assert_true(strpos($settings_html, 'Bijgewerkt tarievenoverzicht 2026') !== false, "Settings view renders file description");
assert_true(strpos($settings_html, 'rc-btn-edit-desc') !== false, "Settings view renders edit description button");
assert_true(strpos($settings_html, 'rc-btn-delete') !== false, "Settings view renders delete button");

// Test helper methods
assert_true($plugin->format_filesize(1024) === '1 KB', "format_filesize formats KB correctly");
assert_true($plugin->format_filesize(1024 * 1024 * 3) === '3 MB', "format_filesize formats MB correctly");
assert_true($plugin->get_file_icon('document.pdf', 'application/pdf') === '📄', "get_file_icon identifies PDF");

// ==========================================
// Test 6: Headless Chrome Browser Integration Test
// ==========================================
echo "\n--- Test 6: Headless Chrome UI Validation ---\n";

$chrome = exec('which google-chrome-stable 2>/dev/null') ?: exec('which google-chrome 2>/dev/null') ?: exec('which chromium 2>/dev/null');

if ($chrome) {
    // Generate an integration HTML test harness that tests:
    // 1. Settings -> Responses form injection (#ffsubject, #rc-reaction-attachments-container).
    // 2. Compose window button (#rc-server-att-compose-btn) and modal.
    // 3. rcmail.insert_response interceptor auto-setting subject and attaching files.
    $testHtmlFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rc_att_ui_test_' . uniqid() . '.html';

    $jsPath = realpath(__DIR__ . '/../Extra context/plugins/roundcube_attachments/roundcube_attachments.js');
    $cssPath = realpath(__DIR__ . '/../Extra context/plugins/roundcube_attachments/roundcube_attachments.css');
    $jqueryPath = realpath(__DIR__ . '/../Extra context/plugins/xframework/assets/bower_components/jquery/dist/jquery.min.js');

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Roundcube Attachments UI Test</title>
<link rel="stylesheet" href="file://{$cssPath}">
<script src="file://{$jqueryPath}"></script>
<script>
// Mock Roundcube client environment
window.rcmail = {
    env: {
        action: 'compose',
        compose_id: 'comp123',
        request_token: 'test_token_123',
        roundcube_attachments: {
            enabled: true,
            is_compose: true,
            is_responses: true,
            response_meta: {
                subject: 'Initial Reaction Subject',
                attachments: ['att_1'],
                files: [{ id: 'att_1', name: 'Brochure.pdf', size: 45000, mimetype: 'application/pdf', description: 'Bedrijfspresentatie' }]
            }
        }
    },
    gettext: function(k) { return k; },
    url: function(a, p) { return './?' + a + (p ? '&' + $.param(p) : ''); },
    addEventListener: function(evt, cb) {
        if (evt === 'init') { window.__init_cb = cb; }
    },
    set_busy: function(busy, msg, lock) { return 'lock_123'; },
    http_post: function(action, data) {
        window.__last_http_post = { action: action, data: data };
    },
    insert_response: function(res) {
        window.__orig_insert_called = true;
    },
    display_message: function(msg, type) {
        window.__last_msg = { msg: msg, type: type };
    }
};
</script>
<script src="file://{$jsPath}"></script>
</head>
<body>
<!-- Sidebar Menu with About Button -->
<div id="layout-menu">
    <div class="special-buttons">
        <a class="button-theme-toggle" href="#">Dark</a>
        <a class="about button-about" href="#about" onclick="UI.about_dialog(this)">About</a>
        <a class="button-logout logout" href="#">Logout</a>
    </div>
</div>

<!-- Compose Window Elements matching Elastic & Gmail Plus skin -->
<div id="compose-toolbar">
    <a href="#" class="button attach">Attach</a>
</div>
<div id="composeform">
    <input type="text" id="_subject" name="_subject" value="">
    <div id="compose-attachments">
        <div class="header">Opties en bijlages</div>
        <div class="file-upload">
            <div class="hint">Maximum toegestane bestandsgrootte is 75 MB</div>
            <div class="buttons">
                <button type="button" class="btn btn-secondary attach">Bijlage toevoegen</button>
                <button type="button" class="btn btn-secondary attach vcard">vCard toevoegen</button>
            </div>
        </div>
    </div>
</div>

<!-- Settings -> Responses Form Elements (Roundcube native <form id="form">, NOT responseform) -->
<div id="settings-responses-area" style="margin-top:50px;">
    <form id="form" class="propform" method="post">
        <div class="form-group row">
            <label for="ffname" class="col-sm-2 col-form-label">Naam</label>
            <div class="col-sm-10">
                <input type="text" id="ffname" name="_name" value="Tarieven beheer laadpunt">
            </div>
        </div>
        <div class="form-group row">
            <label for="fftext" class="col-sm-2 col-form-label">Tekst</label>
            <div class="col-sm-10">
                <textarea id="fftext" name="_text">Beste, hieronder vindt u een overzicht van de kosten.</textarea>
            </div>
        </div>
        <div class="formbuttons">
            <button type="submit" class="btn btn-primary mainaction">Opslaan</button>
        </div>
    </form>
</div>

<!-- Settings -> Serverbijlagen Native Settings Page Content -->
<div id="settings-serverbijlagen-area" style="margin-top:50px;">
{$settings_html}
</div>

<script>
window.onload = function() {
    if (window.__init_cb) {
        window.__init_cb();
    }

    // 1. Check Compose Button Deduplication
    var composeBtns = document.querySelectorAll('#rc-server-att-compose-btn');
    console.log("CHROME_COMPOSE_BTNS_COUNT:" + composeBtns.length);

    var headerBtns = document.querySelectorAll('#compose-attachments .header button');
    console.log("CHROME_HEADER_BTNS_COUNT:" + headerBtns.length);

    var vcardBtns = document.querySelectorAll('#compose-attachments button.vcard');
    console.log("CHROME_VCARD_BTNS_COUNT:" + vcardBtns.length);

    // 2. Open Modal via Click
    if (composeBtns.length) composeBtns[0].click();
    var modal = document.getElementById('rc-server-att-modal');
    console.log("CHROME_MODAL_EXISTS:" + (modal ? "yes" : "no"));

    var search = document.getElementById('rc-server-att-search');
    console.log("CHROME_MODAL_SEARCH:" + (search ? "yes" : "no"));

    // 3. Check Response Edit Form Injections on native <form id="form">
    var subjectEl = document.getElementById('ffsubject');
    console.log("CHROME_SUBJECT_EXISTS:" + (subjectEl ? "yes" : "no"));
    console.log("CHROME_SUBJECT_VAL:" + (subjectEl ? subjectEl.value : ""));

    var attContainer = document.getElementById('rc-reaction-attachments-container');
    console.log("CHROME_ATT_CONTAINER:" + (attContainer ? "yes" : "no"));

    var addServerBtn = document.getElementById('rc-btn-reaction-add-server');
    console.log("CHROME_ADD_SERVER_BTN:" + (addServerBtn ? "yes" : "no"));

    var uploadBtn = document.getElementById('rc-btn-reaction-upload');
    console.log("CHROME_UPLOAD_BTN:" + (uploadBtn ? "yes" : "no"));

    var chips = document.querySelectorAll('#rc-reaction-attached-list .rc-reaction-att-chip');
    console.log("CHROME_CHIPS_COUNT:" + chips.length);

    var chipNameEl = document.querySelector('#rc-reaction-attached-list .rc-chip-name');
    console.log("CHROME_CHIP_NAME:" + (chipNameEl ? chipNameEl.textContent : ""));

    // Check DOM Order: ffname precedes ffsubject precedes attachments precedes fftext editor
    var nameEl = document.getElementById('ffname');
    var textEl = document.getElementById('fftext');
    var nameBeforeSub = nameEl && subjectEl && !!(nameEl.compareDocumentPosition(subjectEl) & Node.DOCUMENT_POSITION_FOLLOWING);
    var subBeforeAtt = subjectEl && attContainer && !!(subjectEl.compareDocumentPosition(attContainer) & Node.DOCUMENT_POSITION_FOLLOWING);
    var attBeforeEditor = attContainer && textEl && !!(attContainer.compareDocumentPosition(textEl) & Node.DOCUMENT_POSITION_FOLLOWING);
    console.log("CHROME_NAME_BEFORE_SUB:" + (nameBeforeSub ? "yes" : "no"));
    console.log("CHROME_SUB_BEFORE_ATT:" + (subBeforeAtt ? "yes" : "no"));
    console.log("CHROME_ATT_BEFORE_EDITOR:" + (attBeforeEditor ? "yes" : "no"));

    // 4. Check About Button Removal
    var aboutLink = document.querySelector('#layout-menu a.about, a.button-about');
    console.log("CHROME_ABOUT_EXISTS:" + (aboutLink ? "yes" : "no"));

    // 5. Test rcmail.insert_response interceptor
    var subInput = document.getElementById('_subject');
    if (subInput) subInput.value = '';

    window.rcmail.insert_response({
        subject: 'Automatic Intercepted Subject',
        attachments: ['att_auto_1', 'att_auto_2'],
        data: 'Some reaction text',
        is_html: false
    });

    var newSubVal = subInput ? subInput.value : '';
    console.log("CHROME_INTERCEPTED_SUBJECT:" + newSubVal);
    console.log("CHROME_ORIG_INSERT_CALLED:" + (window.__orig_insert_called ? "yes" : "no"));

    var httpAction = window.__last_http_post ? window.__last_http_post.action : '';
    console.log("CHROME_HTTP_ACTION:" + httpAction);

    // 6. Test Settings Page DOM & Interactivity
    var settingsContainer = document.querySelector('#rc-server-att-settings');
    console.log("CHROME_SETTINGS_EXISTS:" + (settingsContainer ? "yes" : "no"));

    var settingRows = document.querySelectorAll('#rc-server-att-table tbody tr.rc-server-att-row');
    console.log("CHROME_SETTINGS_ROWS:" + settingRows.length);

    // Toggle description edit
    var editBtn = document.querySelector('.rc-btn-edit-desc');
    if (editBtn) editBtn.click();
    var editBox = document.querySelector('.rc-desc-edit');
    console.log("CHROME_DESC_EDIT_VISIBLE:" + (editBox && editBox.style.display !== 'none' ? "yes" : "no"));

    var cancelBtn = document.querySelector('.rc-btn-cancel-desc');
    if (cancelBtn) cancelBtn.click();
    var descView = document.querySelector('.rc-desc-view');
    console.log("CHROME_DESC_VIEW_VISIBLE:" + (descView && descView.style.display !== 'none' ? "yes" : "no"));

    // Search filter test
    var searchInput = document.querySelector('#rc-settings-search');
    if (searchInput) {
        searchInput.value = 'tarieven';
        $(searchInput).trigger('input');
    }
    var visibleRowsMatching = $('#rc-server-att-table tbody tr.rc-server-att-row:visible').length;
    console.log("CHROME_SETTINGS_MATCHING_ROWS:" + visibleRowsMatching);

    if (searchInput) {
        searchInput.value = 'nonexistentxyz123';
        $(searchInput).trigger('input');
    }
    var visibleRowsNone = $('#rc-server-att-table tbody tr.rc-server-att-row:visible').length;
    console.log("CHROME_SETTINGS_NONE_ROWS:" + visibleRowsNone);

    var emptyStateVisible = $('#rc-server-att-empty').is(':visible');
    console.log("CHROME_EMPTY_STATE_VISIBLE:" + (emptyStateVisible ? "yes" : "no"));

    // Reset search
    if (searchInput) {
        searchInput.value = '';
        $(searchInput).trigger('input');
    }

    // Toggle upload dropzone
    var toggleUploadBtn = document.querySelector('#rc-btn-toggle-upload');
    if (toggleUploadBtn) toggleUploadBtn.click();
    var dropzonePanel = document.querySelector('#rc-server-att-dropzone-panel');
    console.log("CHROME_DROPZONE_TOGGLED:" + (dropzonePanel && dropzonePanel.style.display !== 'none' ? "yes" : "no"));
};
</script>
</body>
</html>
HTML;

    file_put_contents($testHtmlFile, $html);

    $cmd = "{$chrome} --headless --disable-gpu --no-sandbox --disable-background-networking --window-size=1280,800 --run-all-compositor-stages-before-draw --virtual-time-budget=2000 --enable-logging=stderr 'file://{$testHtmlFile}' 2>&1";
    $output = shell_exec($cmd);
    @unlink($testHtmlFile);

    $compose_btns_count = 0;
    $header_btns_count = 0;
    $vcard_btns_count = 0;
    $modal_exists = false;
    $modal_search = false;
    $subject_exists = false;
    $subject_val = '';
    $att_container = false;
    $add_server_btn = false;
    $upload_btn = false;
    $chips_count = 0;
    $chip_name = '';
    $about_exists = true;
    $intercepted_subject = '';
    $orig_insert_called = false;
    $http_action = '';
    $settings_exists = false;
    $settings_rows = 0;
    $desc_edit_visible = false;
    $desc_view_visible = false;
    $settings_matching_rows = 0;
    $settings_none_rows = 0;
    $empty_state_visible = false;
    $dropzone_toggled = false;

    if ($output) {
        foreach (explode("\n", $output) as $line) {
            if (preg_match('/CHROME_COMPOSE_BTNS_COUNT:(.*?)"/', $line, $m)) $compose_btns_count = (int) trim($m[1]);
            if (preg_match('/CHROME_HEADER_BTNS_COUNT:(.*?)"/', $line, $m)) $header_btns_count = (int) trim($m[1]);
            if (preg_match('/CHROME_VCARD_BTNS_COUNT:(.*?)"/', $line, $m)) $vcard_btns_count = (int) trim($m[1]);
            if (preg_match('/CHROME_MODAL_EXISTS:(.*?)"/', $line, $m)) $modal_exists = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_MODAL_SEARCH:(.*?)"/', $line, $m)) $modal_search = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_SUBJECT_EXISTS:(.*?)"/', $line, $m)) $subject_exists = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_SUBJECT_VAL:(.*?)"/', $line, $m)) $subject_val = trim($m[1]);
            if (preg_match('/CHROME_ATT_CONTAINER:(.*?)"/', $line, $m)) $att_container = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_ADD_SERVER_BTN:(.*?)"/', $line, $m)) $add_server_btn = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_UPLOAD_BTN:(.*?)"/', $line, $m)) $upload_btn = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_CHIPS_COUNT:(.*?)"/', $line, $m)) $chips_count = (int) trim($m[1]);
            if (preg_match('/CHROME_CHIP_NAME:(.*?)"/', $line, $m)) $chip_name = trim($m[1]);
            if (preg_match('/CHROME_NAME_BEFORE_SUB:(.*?)"/', $line, $m)) $name_before_sub = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_SUB_BEFORE_ATT:(.*?)"/', $line, $m)) $sub_before_att = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_ATT_BEFORE_EDITOR:(.*?)"/', $line, $m)) $att_before_editor = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_ABOUT_EXISTS:(.*?)"/', $line, $m)) $about_exists = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_INTERCEPTED_SUBJECT:(.*?)"/', $line, $m)) $intercepted_subject = trim($m[1]);
            if (preg_match('/CHROME_ORIG_INSERT_CALLED:(.*?)"/', $line, $m)) $orig_insert_called = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_HTTP_ACTION:(.*?)"/', $line, $m)) $http_action = trim($m[1]);

            if (preg_match('/CHROME_SETTINGS_EXISTS:(.*?)"/', $line, $m)) $settings_exists = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_SETTINGS_ROWS:(.*?)"/', $line, $m)) $settings_rows = (int) trim($m[1]);
            if (preg_match('/CHROME_DESC_EDIT_VISIBLE:(.*?)"/', $line, $m)) $desc_edit_visible = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_DESC_VIEW_VISIBLE:(.*?)"/', $line, $m)) $desc_view_visible = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_SETTINGS_MATCHING_ROWS:(.*?)"/', $line, $m)) $settings_matching_rows = (int) trim($m[1]);
            if (preg_match('/CHROME_SETTINGS_NONE_ROWS:(.*?)"/', $line, $m)) $settings_none_rows = (int) trim($m[1]);
            if (preg_match('/CHROME_EMPTY_STATE_VISIBLE:(.*?)"/', $line, $m)) $empty_state_visible = (trim($m[1]) === 'yes');
            if (preg_match('/CHROME_DROPZONE_TOGGLED:(.*?)"/', $line, $m)) $dropzone_toggled = (trim($m[1]) === 'yes');
        }
    }

    assert_true($compose_btns_count === 1, "Browser DOM: Exactly 1 Server Attachments button rendered (no duplicates)");
    assert_true($header_btns_count === 0, "Browser DOM: No buttons erroneously injected into attachments header");
    assert_true($vcard_btns_count === 1, "Browser DOM: Exactly 1 vCard button present (no duplicates)");
    assert_true($modal_exists, "Browser DOM: Server Attachments modal container rendered");
    assert_true($modal_search, "Browser DOM: Search field rendered in modal");
    assert_true($subject_exists, "Browser DOM: #ffsubject field rendered in reaction form");
    assert_true($subject_val === 'Initial Reaction Subject', "Browser DOM: #ffsubject populated with reaction subject");
    assert_true($att_container, "Browser DOM: Attachments container rendered in reaction form");
    assert_true($add_server_btn, "Browser DOM: 'Attach from Server' button rendered");
    assert_true($upload_btn, "Browser DOM: 'Upload & Attach' button rendered");
    assert_true($chips_count === 1, "Browser DOM: Attached file chip rendered");
    assert_true($chip_name === 'Brochure.pdf', "Browser DOM: Attached file chip displays correct filename");
    assert_true($name_before_sub, "Browser DOM: Subject field is positioned directly below Name field");
    assert_true($sub_before_att, "Browser DOM: Attachments container is positioned directly below Subject field");
    assert_true($att_before_editor, "Browser DOM: Attachments container is positioned before the body text editor");
    assert_true(!$about_exists, "Browser DOM: About button successfully removed from sidebar");
    assert_true($intercepted_subject === 'Automatic Intercepted Subject', "Browser JS: insert_response auto-populates #_subject");
    assert_true($orig_insert_called, "Browser JS: original insert_response invoked");
    assert_true($http_action === 'plugin.roundcube_attachments_attach_to_compose', "Browser JS: auto-attaches reaction files via AJAX");

    assert_true($settings_exists, "Browser DOM: #rc-server-att-settings native settings page rendered");
    assert_true($settings_rows >= 1, "Browser DOM: Settings file table rendered rows");
    assert_true($desc_edit_visible, "Browser DOM: Clicking edit button displays inline description input");
    assert_true($desc_view_visible, "Browser DOM: Clicking cancel button restores description view");
    assert_true($settings_matching_rows >= 1, "Browser JS: Live search filters rows correctly for matching query");
    assert_true($settings_none_rows === 0, "Browser JS: Live search hides all rows for non-matching query");
    assert_true($empty_state_visible, "Browser JS: Empty state shown when 0 rows match search");
    assert_true($dropzone_toggled, "Browser JS: Toggle upload button reveals dropzone panel");
} else {
    echo "Notice: Chrome binary not found, skipping headless browser test.\n";
}

// Clean up temp test storage
function delete_dir($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir) ?: [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $p = $dir . DIRECTORY_SEPARATOR . $f;
        is_dir($p) ? delete_dir($p) : @unlink($p);
    }
    @rmdir($dir);
}
delete_dir($testStorageDir);

echo "\n==================================================\n";
echo "TEST RESULTS: {$passed_count}/{$test_count} tests passed successfully!\n";
echo "==================================================\n";

if ($passed_count < $test_count) {
    exit(1);
}
exit(0);
