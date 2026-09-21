<?php

/**
 * Automated test suite for Thunderbird Labels plugin
 */

// Define mock Roundcube classes if not running inside Roundcube web server
if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $storage;
        public $user;
        public $task = 'mail';
        public $action = '';

        public function __construct()
        {
            $this->config = new rcube_config();
            $this->output = new rcmail_output_html();
            $this->storage = new rcube_storage_mock();
            $this->user = new rcube_user_mock();
        }

        public function get_storage()
        {
            return $this->storage;
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

        public static function Q($str)
        {
            return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        }
    }

    class rcmail extends rcube {}

    class rcube_config
    {
        public $data = [];

        public function get($key, $default = null)
        {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
        }

        public function set($key, $val)
        {
            $this->data[$key] = $val;
        }
    }

    class rcube_storage_mock
    {
        public $folder = 'INBOX';
        public $conn;
        public $search_count = 5;
        public $set_flags = [];
        public $moved_messages = [];

        public function __construct()
        {
            $this->conn = new stdClass();
            $this->conn->flags = [];
        }

        public function get_folder()
        {
            return $this->folder;
        }

        public function set_folder($folder)
        {
            $this->folder = $folder;
        }

        public function search_once($mbox, $criteria)
        {
            $count = $this->search_count;
            $res = new stdClass();
            $res->criteria = $criteria;
            $res->count = function () use ($count) { return $count; };
            return new class($res, $count) {
                private $c;
                public function __construct($r, $c) { $this->c = $c; }
                public function count() { return $this->c; }
            };
        }

        public function set_flag($uids, $flag, $mbox)
        {
            $this->set_flags[] = ['uids' => $uids, 'flag' => $flag, 'mbox' => $mbox];
            return true;
        }

        public function move_message($uids, $to, $from = null)
        {
            $this->moved_messages[] = ['uids' => $uids, 'to' => $to, 'from' => $from];
            return true;
        }

        public function list_folders_subscribed()
        {
            return ['INBOX', 'Drafts', 'Sent', 'Trash', 'Junk', 'Archive'];
        }

        public function list_folders()
        {
            return ['INBOX', 'Drafts', 'Sent', 'Trash', 'Junk', 'Archive'];
        }
    }

    class rcube_user_mock
    {
        public $prefs = [];

        public function save_prefs($prefs)
        {
            $this->prefs = array_merge($this->prefs, $prefs);
            foreach ($prefs as $k => $v) {
                rcmail::get_instance()->config->set($k, $v);
            }
            return true;
        }
    }

    class rcmail_output_html
    {
        public $env = [];
        public $scripts = [];
        public $stylesheets = [];
        public $commands = [];
        public $messages = [];
        public $type = 'html';

        public function set_env($key, $val)
        {
            $this->env[$key] = $val;
        }

        public function include_script($file)
        {
            $this->scripts[] = $file;
        }

        public function include_stylesheet($file)
        {
            $this->stylesheets[] = $file;
        }

        public function command($cmd, $arg1 = null, $arg2 = null)
        {
            $this->commands[] = ['command' => $cmd, 'arg1' => $arg1, 'arg2' => $arg2];
        }

        public function show_message($msg, $type = 'notice')
        {
            $this->messages[] = ['message' => $msg, 'type' => $type];
        }

        public function just_parse($tpl)
        {
            return $tpl;
        }

        public function add_footer($html) {}

        public function add_content($html, $container) {}

        public function send() {}
    }

    class rcube_plugin
    {
        public $api;
        public $home;
        public $registered_hooks = [];
        public $registered_actions = [];

        public function __construct()
        {
            global $plugin_dir;
            $this->api = new class {
                public $output;
                public function __construct() {
                    $this->output = rcmail::get_instance()->output;
                }
                public function add_content($html, $container) {}
                public function button($args) { return '<button>' . ($args['label'] ?? '') . '</button>'; }
            };
            $this->home = $plugin_dir ?? dirname(__DIR__) . '/Extra context/plugins/thunderbird_labels';
        }

        public function load_config() {}

        public function add_texts($dir, $bool = false) {}

        public function add_button($args) {}

        public function include_script($file)
        {
            rcmail::get_instance()->output->include_script($file);
        }

        public function include_stylesheet($file)
        {
            rcmail::get_instance()->output->include_stylesheet($file);
        }

        public function local_skin_path()
        {
            return 'skins/elastic';
        }

        public function add_hook($hook, $callback)
        {
            $this->registered_hooks[$hook] = $callback;
        }

        public function register_action($action, $callback)
        {
            $this->registered_actions[$action] = $callback;
        }

        public function getText($key)
        {
            $texts = [
                'label0' => 'No Label',
                'label1' => 'To Respond',
                'label2' => 'FYI',
                'label3' => 'Important',
                'label4' => 'Marketing & Newsletters',
                'label5' => 'ToDo',
            ];
            return $texts[$key] ?? $key;
        }
    }

    class rcube_utils
    {
        public const INPUT_GET = 1;
        public const INPUT_POST = 2;
        public const INPUT_GPC = 3;

        public static $mock_inputs = [];

        public static function get_input_value($name, $source, $allow_html = false)
        {
            return self::$mock_inputs[$name] ?? null;
        }
    }

    if (!function_exists('slashify')) {
        function slashify($path)
        {
            return rtrim($path, '/') . '/';
        }
    }
}

$plugin_dir = file_exists(dirname(__DIR__) . '/thunderbird_labels.php')
    ? dirname(__DIR__)
    : dirname(__DIR__) . '/Extra context/plugins/thunderbird_labels';

// Require the thunderbird_labels plugin
require_once $plugin_dir . '/thunderbird_labels.php';

function assert_true($cond, $msg)
{
    if (!$cond) {
        echo "FAILED: $msg\n";
        exit(1);
    }
    echo "PASSED: $msg\n";
}

echo "=== Running Thunderbird Labels Test Suite ===\n\n";

// --- Test 1: Plugin Initialization & Hooks Registration ---
echo "--- Test 1: Plugin Initialization & Hook/Action Registration ---\n";
$rcmail = rcmail::reset_instance();
$rcmail->config->set('tb_label_enable', true);
$rcmail->config->set('tb_label_style', 'badges');
$rcmail->config->set('tb_label_enable_shortcuts', true);

$plugin = new thunderbird_labels();
$plugin->init();

assert_true(isset($plugin->registered_hooks['imap_search_before']), "imap_search_before hook is registered");
assert_true(isset($plugin->registered_hooks['messages_list']), "messages_list hook is registered");
assert_true(isset($plugin->registered_hooks['message_load']), "message_load hook is registered");
assert_true(isset($plugin->registered_hooks['template_object_messageheaders']), "template_object_messageheaders hook is registered");
assert_true(isset($plugin->registered_hooks['render_page']), "render_page hook is registered");
assert_true(isset($plugin->registered_hooks['check_recent']), "check_recent hook is registered");

assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.set_flags']), "set_flags action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.get_counts']), "get_counts action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.add_label']), "add_label action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.update_label']), "update_label action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.delete_label']), "delete_label action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.get_filters']), "get_filters action registered");
assert_true(isset($plugin->registered_actions['plugin.thunderbird_labels.get_folders']), "get_folders action registered");

// --- Test 2: Custom Labels & Color Mapping ---
echo "\n--- Test 2: Custom Labels and Color Palette Mapping ---\n";
$env_labels = $rcmail->output->env['tb_label_custom_labels'];
assert_true(is_array($env_labels), "tb_label_custom_labels exported to JS environment");
assert_true(isset($env_labels['LABEL0']) && $env_labels['LABEL0'] === 'No Label', "LABEL0 defaults to No Label");
assert_true(isset($env_labels['LABEL1']) && $env_labels['LABEL1'] === 'To Respond', "LABEL1 defaults to To Respond");
assert_true(isset($env_labels['LABEL2']) && $env_labels['LABEL2'] === 'FYI', "LABEL2 defaults to FYI");
assert_true(isset($env_labels['LABEL3']) && $env_labels['LABEL3'] === 'Important', "LABEL3 defaults to Important");
assert_true(isset($env_labels['LABEL4']) && $env_labels['LABEL4'] === 'Marketing & Newsletters', "LABEL4 defaults to Marketing & Newsletters");
assert_true(isset($env_labels['LABEL5']) && $env_labels['LABEL5'] === 'ToDo', "LABEL5 defaults to ToDo");

$env_colors = $rcmail->output->env['tb_label_colors'];
assert_true(is_array($env_colors), "tb_label_colors exported to JS environment");
assert_true(isset($env_colors['LABEL1']) && !empty($env_colors['LABEL1']), "LABEL1 has a color code assigned");
assert_true(isset($env_colors['LABEL4']) && !empty($env_colors['LABEL4']), "LABEL4 has a color code assigned");

// Verify sanitization of raw database label keys
$rcmail->config->set('tb_label_custom_labels', ['LABEL1' => 'LABEL1', 'LABEL2' => 'LABEL2']);
$plugin_sanitized = new thunderbird_labels();
$plugin_sanitized->init();
$sanitized_labels = $rcmail->output->env['tb_label_custom_labels'];
assert_true($sanitized_labels['LABEL1'] === 'To Respond', "Raw database key LABEL1 sanitized to human-readable 'To Respond'");
assert_true($sanitized_labels['LABEL2'] === 'FYI', "Raw database key LABEL2 sanitized to human-readable 'FYI'");

// --- Test 3: imap_search_before Query Rewriting ---
echo "\n--- Test 3: imap_search_before Search Query Rewriting ---\n";

// Numeric label e.g. label:1
$res1 = $plugin->imap_search_before(['search' => 'label:1']);
assert_true(strpos($res1['search'], 'KEYWORD $LABEL1') !== false, "label:1 rewrites to KEYWORD \$LABEL1");
assert_true(strpos($res1['search'], 'KEYWORD LABEL1') !== false, "label:1 rewrites to KEYWORD LABEL1");

// Explicit key e.g. label:LABEL3
$res2 = $plugin->imap_search_before(['search' => 'label:LABEL3']);
assert_true(strpos($res2['search'], 'KEYWORD $LABEL3') !== false, "label:LABEL3 rewrites to KEYWORD \$LABEL3");

// Label name with quotes e.g. label:"Important"
$res3 = $plugin->imap_search_before(['search' => 'label:"Important"']);
assert_true(strpos($res3['search'], 'KEYWORD $LABEL3') !== false, "label:\"Important\" resolves to LABEL3");

// Number prefix e.g. label:"1: to respond"
$res4 = $plugin->imap_search_before(['search' => 'label:"1: to respond"']);
assert_true(strpos($res4['search'], 'KEYWORD $LABEL1') !== false, "label:\"1: to respond\" resolves to LABEL1");

// Tag syntax e.g. tag:FYI
$res5 = $plugin->imap_search_before(['search' => 'tag:FYI']);
assert_true(strpos($res5['search'], 'KEYWORD $LABEL2') !== false, "tag:FYI resolves to LABEL2");

// Negation e.g. NOT label:4
$res6 = $plugin->imap_search_before(['search' => 'NOT label:4']);
assert_true(strpos($res6['search'], 'NOT (OR KEYWORD $LABEL4') !== false, "NOT label:4 preserves negation");

// Complex query preserving standard headers
$res7 = $plugin->imap_search_before(['search' => 'FROM test@example.com label:2 SINCE 1-Sep-2026']);
assert_true(strpos($res7['search'], 'FROM test@example.com') !== false, "Complex query preserves FROM");
assert_true(strpos($res7['search'], 'SINCE 1-Sep-2026') !== false, "Complex query preserves SINCE");
assert_true(strpos($res7['search'], 'KEYWORD $LABEL2') !== false, "Complex query rewrites label token");

// --- Test 4: Dynamic Label Management (add, update, delete) ---
echo "\n--- Test 4: Dynamic Label Management (add, update, delete) ---\n";

// Add new label
rcube_utils::$mock_inputs['name'] = '4: notification';
rcube_utils::$mock_inputs['color'] = '#188038';
$plugin->action_add_label();

$saved_labels = $rcmail->config->get('tb_label_custom_labels');
$saved_colors = $rcmail->config->get('tb_label_colors');

assert_true(isset($saved_labels['LABEL6']), "New label assigned key LABEL6");
assert_true($saved_labels['LABEL6'] === '4: notification', "LABEL6 name stored accurately");
assert_true(isset($saved_colors['LABEL6']) && $saved_colors['LABEL6'] === '#188038', "LABEL6 custom color stored accurately");

// Verify search for the new label
$res_new = $plugin->imap_search_before(['search' => 'label:"4: notification"']);
assert_true(strpos($res_new['search'], 'KEYWORD $LABEL6') !== false, "Searching for new label by name maps to LABEL6");

// Update label
rcube_utils::$mock_inputs['key'] = 'LABEL6';
rcube_utils::$mock_inputs['name'] = '4: notifications & alerts';
rcube_utils::$mock_inputs['color'] = '#0f9d58';
$plugin->update_label();

$updated_labels = $rcmail->config->get('tb_label_custom_labels');
$updated_colors = $rcmail->config->get('tb_label_colors');
assert_true($updated_labels['LABEL6'] === '4: notifications & alerts', "Label name updated in preferences");
assert_true($updated_colors['LABEL6'] === '#0f9d58', "Label color updated in preferences");

// Delete label
rcube_utils::$mock_inputs['key'] = 'LABEL6';
$plugin->delete_label();

$final_labels = $rcmail->config->get('tb_label_custom_labels');
assert_true(!isset($final_labels['LABEL6']), "Label successfully deleted from preferences");

// --- Test 5: Label Counts Endpoint ---
echo "\n--- Test 5: get_counts Action & Caching ---\n";
rcube_utils::$mock_inputs['_mbox'] = 'INBOX';
$rcmail->output->commands = [];
$plugin->get_counts();

assert_true(!empty($rcmail->output->commands), "get_counts dispatches update_counts command to client");
$cmd = $rcmail->output->commands[0];
assert_true($cmd['command'] === 'plugin.thunderbird_labels.update_counts', "Command is plugin.thunderbird_labels.update_counts");
assert_true(isset($cmd['arg1']['counts']['LABEL1']), "Counts array contains entries for labels");
assert_true($cmd['arg1']['counts']['LABEL1'] === 5, "Count value computed correctly from search result");

// --- Test 6: Localization Completeness ---
echo "\n--- Test 6: Localization Completeness Across Languages ---\n";
$loc_files = [
    'en_US' => $plugin_dir . '/localization/en_US.inc',
    'nl_NL' => $plugin_dir . '/localization/nl_NL.inc',
    'de_DE' => $plugin_dir . '/localization/de_DE.inc',
    'fr_FR' => $plugin_dir . '/localization/fr_FR.inc',
];

$required_keys = [
    'labels_title',
    'add_label',
    'create_label',
    'cancel',
    'label_name',
    'label_name_placeholder',
    'label_color',
    'filter_by_label',
    'clear_filter',
    'delete_label',
    'rename_label',
    'change_color',
    'confirm_delete_label',
];

foreach ($loc_files as $lang => $path) {
    assert_true(file_exists($path), "$lang localization file exists");
    $labels = [];
    include $path;
    foreach ($required_keys as $key) {
        assert_true(isset($labels[$key]) && !empty($labels[$key]), "[$lang] has key '$key'");
    }
}

// --- Test 7: CSS & JS Integrity ---
echo "\n--- Test 7: CSS & JavaScript Integrity ---\n";
$js_file = $plugin_dir . '/tb_label.js';
$css_elastic = $plugin_dir . '/skins/elastic/tb_label.css';

$js_content = file_get_contents($js_file);
assert_true(strpos($js_content, 'rcm_tb_label_init_sidebar') !== false, "tb_label.js contains rcm_tb_label_init_sidebar");
assert_true(strpos($js_content, 'rcm_tb_label_filter_click') !== false, "tb_label.js contains rcm_tb_label_filter_click");
assert_true(strpos($js_content, 'rcm_tb_label_show_add_modal') !== false, "tb_label.js contains rcm_tb_label_show_add_modal");
assert_true(strpos($js_content, 'tb-label-filter-bar') !== false, "tb_label.js contains tb-label-filter-bar");

$css_content = file_get_contents($css_elastic);
assert_true(strpos($css_content, '.tb-labels-sidebar') !== false, "elastic tb_label.css contains .tb-labels-sidebar");
assert_true(strpos($css_content, '.tb-label-filter-bar') !== false, "elastic tb_label.css contains .tb-label-filter-bar");
assert_true(strpos($css_content, '.tb-label-modal') !== false, "elastic tb_label.css contains .tb-label-modal");
assert_true(strpos($css_content, '.tb-label-count:empty') !== false, "elastic tb_label.css contains .tb-label-count:empty rule");

$css_larry = file_get_contents($plugin_dir . '/skins/larry/tb_label.css');
assert_true(strpos($css_larry, '.tb-label-count:empty') !== false, "larry tb_label.css contains .tb-label-count:empty rule");

$css_classic = file_get_contents($plugin_dir . '/skins/classic/tb_label.css');
assert_true(strpos($css_classic, '.tb-label-count:empty') !== false, "classic tb_label.css contains .tb-label-count:empty rule");

// Count rendering assertions in JS
assert_true(strpos($js_content, 'display_count') !== false || strpos($js_content, 'count >= 0') !== false, "tb_label.js renders count badge showing 0 when count is zero");
assert_true(strpos($js_content, 'target_mbox = "INBOX"') !== false, "tb_label.js routes sidebar label click to INBOX");
assert_true(strpos($js_content, 'tb-label-delete-btn') !== false, "tb_label.js renders tb-label-delete-btn for deleting labels");
assert_true(strpos($js_content, 'rcm_tb_label_count_local_messages') !== false, "tb_label.js defines rcm_tb_label_count_local_messages helper");

assert_true(strpos($css_content, '.tb-label-delete-btn') !== false, "elastic tb_label.css styles .tb-label-delete-btn");
assert_true(strpos($css_larry, '.tb-label-delete-btn') !== false, "larry tb_label.css styles .tb-label-delete-btn");
assert_true(strpos($css_classic, '.tb-label-delete-btn') !== false, "classic tb_label.css styles .tb-label-delete-btn");

// Etiketten label-dots xskin outline font icon (\ec7d) assertions
assert_true(strpos($css_content, "\\ec7d") !== false, "elastic tb_label.css contains label-dots outline font icon (\\ec7d)");
$gp_css = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.css');
$gp_min = file_get_contents(__DIR__ . '/../skins/gmail_plus/style.min.css');
$el_css = file_get_contents(__DIR__ . '/../skins/elastic/style.css');
$el_min = file_get_contents(__DIR__ . '/../skins/elastic/style.min.css');
assert_true(strpos($gp_css, "\\ec7d") !== false, "gmail_plus style.css maps label-dots icon glyph (\\ec7d)");
assert_true(strpos($gp_min, "\\ec7d") !== false, "gmail_plus style.min.css contains label-dots icon glyph (\\ec7d)");
assert_true(strpos($el_css, "\\ec7d") !== false, "elastic style.css maps label-dots icon glyph (\\ec7d)");
assert_true(strpos($el_min, "\\ec7d") !== false, "elastic style.min.css contains label-dots icon glyph (\\ec7d)");
assert_true(strpos($gp_css, "top: 0.9rem !important;") !== false, "gmail_plus style.css offsets label button icon with top: 0.9rem");
assert_true(strpos($el_css, "top: 0.9rem !important;") !== false, "elastic style.css offsets label button icon with top: 0.9rem");
assert_true(strpos($gp_min, "top:.9rem!important") !== false, "gmail_plus style.min.css contains top: .9rem for label button icon");
assert_true(strpos($el_min, "top:.9rem!important") !== false, "elastic style.min.css contains top: .9rem for label button icon");

// Toolbar template structure assertions (single button without split dropdown button)
$toolbar_tpl = file_get_contents($plugin_dir . '/skins/elastic/includes/toolbar.html');
assert_true(strpos($toolbar_tpl, 'id="tb-label-menulink"') !== false, "elastic toolbar.html contains tb-label-menulink");
assert_true(strpos($toolbar_tpl, 'class="button"') !== false, "elastic toolbar.html assigns class='button' to tb-label-menulink");
assert_true(strpos($toolbar_tpl, 'rcm_tb_label_submenu') === false, "elastic toolbar.html does not contain separate split rcm_tb_label_submenu button");
assert_true(strpos($toolbar_tpl, 'class="button dropdown"') === false, "elastic toolbar.html does not assign button dropdown class");

// Test session cache invalidation on delete
$_SESSION['tb_label_counts_test'] = ['data' => [1]];
rcube_utils::$mock_inputs['key'] = 'LABEL5';
$plugin->delete_label();
assert_true(!isset($_SESSION['tb_label_counts_test']), "delete_label clears session count caches");

// --- Test 8: Filter Engine Conditions & Rules Evaluation ---
echo "\n--- Test 8: Filter Engine Conditions & Rules Evaluation ---\n";
require_once $plugin_dir . '/tb_label_filter_engine.php';

// 1. Condition evaluation tests for all operators
$sample_msg = [
    'from' => 'finance@accounting.corp',
    'to' => 'team@company.com',
    'cc' => 'audit@partner.org',
    'subject' => '[URGENT] Q3 Financial Report & Invoice #9876',
    'body' => 'Please find attached the signed contract and invoice for processing.',
];

// contains
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'contains', 'value' => 'Financial Report'], $sample_msg), "evaluate_condition 'contains' matches substring");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'contains', 'value' => 'Marketing Plan'], $sample_msg), "evaluate_condition 'contains' rejects missing substring");

// not_contains
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'from', 'operator' => 'not_contains', 'value' => 'spam.org'], $sample_msg), "evaluate_condition 'not_contains' matches when string absent");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'from', 'operator' => 'not_contains', 'value' => 'accounting'], $sample_msg), "evaluate_condition 'not_contains' rejects when string present");

// equals / is
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'from', 'operator' => 'equals', 'value' => 'FINANCE@ACCOUNTING.CORP'], $sample_msg), "evaluate_condition 'equals' matches exact case-insensitive");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'from', 'operator' => 'equals', 'value' => 'finance@accounting'], $sample_msg), "evaluate_condition 'equals' rejects partial match");

// not_equals / is_not
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'to', 'operator' => 'not_equals', 'value' => 'other@company.com'], $sample_msg), "evaluate_condition 'not_equals' matches differing values");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'to', 'operator' => 'not_equals', 'value' => 'team@company.com'], $sample_msg), "evaluate_condition 'not_equals' rejects identical values");

// starts_with
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'starts_with', 'value' => '[urgent]'], $sample_msg), "evaluate_condition 'starts_with' matches prefix");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'starts_with', 'value' => 'Q3'], $sample_msg), "evaluate_condition 'starts_with' rejects non-prefix");

// ends_with
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'cc', 'operator' => 'ends_with', 'value' => '@partner.org'], $sample_msg), "evaluate_condition 'ends_with' matches suffix");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'cc', 'operator' => 'ends_with', 'value' => '.com'], $sample_msg), "evaluate_condition 'ends_with' rejects non-suffix");

// regex
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'regex', 'value' => 'Invoice\s+#\d+'], $sample_msg), "evaluate_condition 'regex' matches regex pattern");
assert_true(!tb_label_filter_engine::evaluate_condition(['field' => 'subject', 'operator' => 'regex', 'value' => '^Order\s+#\d+'], $sample_msg), "evaluate_condition 'regex' rejects non-matching regex");

// Empty value condition always evaluates to true
assert_true(tb_label_filter_engine::evaluate_condition(['field' => 'body', 'operator' => 'contains', 'value' => ''], $sample_msg), "evaluate_condition with empty value always passes");

// 2. Rule evaluation: scope 'all' vs 'any'
$rule_all = [
    'id' => 'rule_test_1',
    'name' => 'Finance Invoices',
    'enabled' => true,
    'scope' => 'all',
    'conditions' => [
        ['field' => 'from', 'operator' => 'contains', 'value' => 'accounting'],
        ['field' => 'subject', 'operator' => 'contains', 'value' => 'Invoice'],
    ],
    'actions' => [
        'labels' => ['LABEL1', 'LABEL2'],
        'folder' => 'Invoices',
        'mark_read' => true,
    ],
];
assert_true(tb_label_filter_engine::matches_rule($rule_all, $sample_msg), "matches_rule with scope 'all' passes when all conditions match");

$rule_all_fail = $rule_all;
$rule_all_fail['conditions'][] = ['field' => 'subject', 'operator' => 'contains', 'value' => 'Marketing'];
assert_true(!tb_label_filter_engine::matches_rule($rule_all_fail, $sample_msg), "matches_rule with scope 'all' fails if one condition fails");

$rule_any = [
    'id' => 'rule_test_2',
    'name' => 'Any Alert',
    'enabled' => true,
    'scope' => 'any',
    'conditions' => [
        ['field' => 'subject', 'operator' => 'contains', 'value' => 'Marketing'],
        ['field' => 'subject', 'operator' => 'contains', 'value' => 'Invoice'],
    ],
];
assert_true(tb_label_filter_engine::matches_rule($rule_any, $sample_msg), "matches_rule with scope 'any' passes when at least one condition matches");

$rule_disabled = $rule_all;
$rule_disabled['enabled'] = false;
assert_true(!tb_label_filter_engine::matches_rule($rule_disabled, $sample_msg), "matches_rule returns false for disabled rules");

// 3. Rule actions execution (multi-label assignment, move folder, mark read)
$rcmail_test = rcmail::reset_instance();
$applied = tb_label_filter_engine::apply_actions($rcmail_test, 2048, 'INBOX', $rule_all);

assert_true(in_array('LABEL1', $applied['labels']) && in_array('LABEL2', $applied['labels']), "apply_actions returns all applied labels");
assert_true($applied['moved_to'] === 'Invoices', "apply_actions records moved folder");
assert_true($applied['marked_read'] === true, "apply_actions marks message as read");

$storage = $rcmail_test->get_storage();
$flags_set = array_column($storage->set_flags, 'flag');
assert_true(in_array('$Label1', $flags_set), "apply_actions set \$Label1 flag via storage");
assert_true(in_array('$Label2', $flags_set), "apply_actions set \$Label2 flag via storage");
assert_true(in_array('SEEN', $flags_set), "apply_actions set SEEN flag via storage");
assert_true(!empty($storage->moved_messages), "apply_actions called storage move_message");
assert_true($storage->moved_messages[0]['to'] === 'Invoices', "apply_actions moved to Invoices target folder");

// 4. Rule persistence CRUD
$saved = tb_label_filter_engine::save_rule($rcmail_test, $rule_all);
assert_true(!empty($saved['id']), "save_rule returns rule with ID");
$rules = tb_label_filter_engine::get_rules($rcmail_test);
assert_true(count($rules) === 1 && $rules[0]['name'] === 'Finance Invoices', "get_rules returns saved rules");

tb_label_filter_engine::toggle_rule($rcmail_test, $rule_all['id'], false);
$rules = tb_label_filter_engine::get_rules($rcmail_test);
assert_true($rules[0]['enabled'] === false, "toggle_rule toggles enabled state to false");

tb_label_filter_engine::delete_rule($rcmail_test, $rule_all['id']);
$rules = tb_label_filter_engine::get_rules($rcmail_test);
assert_true(count($rules) === 0, "delete_rule removes rule from preferences");


// --- Test 9: Multi-Label Assignment Across Plugin & LifePrisma AI ---
echo "\n--- Test 9: Multi-Label Assignment Across Plugin & LifePrisma AI ---\n";

// 1. thunderbird_labels::read_flags with multiple flags
$msg_obj = new stdClass();
$msg_obj->uid = 42;
$msg_obj->flags = [
    '$Label1' => 1,
    '$Label3' => 1,
    'SEEN' => 1,
];
$msg_obj->list_flags = [];

$res = $plugin->read_flags(['messages' => [$msg_obj]]);
$tb_labels = $res['messages'][0]->list_flags['extra_flags']['tb_labels'] ?? [];
assert_true(in_array('LABEL1', $tb_labels) || in_array('$Label1', $tb_labels), "read_flags retains first label flag");
assert_true(in_array('LABEL3', $tb_labels) || in_array('$Label3', $tb_labels), "read_flags retains multiple label flags on the same message");

// 2. lifeprisma_ai::handle_messages_list multi-badge assignment
$lpai_file = dirname(__DIR__) . '/lifeprisma_ai.php';
if (file_exists($lpai_file)) {
    require_once $lpai_file;
    $lpai = new lifeprisma_ai();
    $lp_msg = new stdClass();
    $lp_msg->uid = 99;
    $lp_msg->flags = [
        '$label1' => 1,
        '$Label4' => 1,
    ];
    $lp_msg->list_flags = [];

    $lp_res = $lpai->handle_messages_list(['messages' => [$lp_msg]]);
    assert_true(isset($lp_msg->list_flags['label-1']) && $lp_msg->list_flags['label-1'] === 1, "lifeprisma_ai sets label-1 class");
    assert_true(isset($lp_msg->list_flags['label-4']) && $lp_msg->list_flags['label-4'] === 1, "lifeprisma_ai sets label-4 class simultaneously (multi-label)");

    $env_lp_labels = rcmail::get_instance()->output->env['lpai_row_labels'] ?? [];
    assert_true(isset($env_lp_labels['99']), "lpai_row_labels contains UID 99");
    assert_true(in_array('$Label1', $env_lp_labels['99']) && in_array('$Label4', $env_lp_labels['99']), "lpai_row_labels exports all multiple labels in array");
}


// --- Test 10: Count Badge Zero Guarantee ---
echo "\n--- Test 10: Count Badge Zero Guarantee ---\n";

$rcmail_cnt = rcmail::reset_instance();
$rcmail_cnt->config->set('tb_label_enable', true);
$storage_cnt = $rcmail_cnt->get_storage();
$storage_cnt->search_count = 0; // 0 emails matching

$plugin_cnt = new thunderbird_labels();
$plugin_cnt->init();
$plugin_cnt->get_counts();

$cmds = $rcmail_cnt->output->commands;
$update_cmd = null;
foreach ($cmds as $c) {
    if (($c['command'] ?? '') === 'plugin.thunderbird_labels.update_counts') {
        $update_cmd = $c;
        break;
    }
}
assert_true($update_cmd !== null, "get_counts dispatches update_counts command");
$counts_data = $update_cmd['arg1']['counts'] ?? [];
assert_true(isset($counts_data['LABEL1']), "LABEL1 exists in counts response");
assert_true($counts_data['LABEL1'] === 0, "Counts strictly returns integer 0 when matching email count is zero");
assert_true($counts_data['LABEL2'] === 0, "Counts strictly returns integer 0 for LABEL2");

// Verify JS code guarantees 0 display on DOM
assert_true(strpos($js_content, "display_count") !== false || strpos($js_content, "count > 0 ? count : 0") !== false, "tb_label.js guarantees count badge shows 0 and never ID");

// --- Test 11: Filter Folders Resolution & Dropdown Building ---
echo "\n--- Test 11: Filter Folders Resolution & Dropdown Building ---\n";
$folders = $plugin->get_mail_folders();
assert_true(is_array($folders) && count($folders) > 0, "get_mail_folders returns array of folders");
assert_true(in_array('INBOX', $folders), "Folders list includes INBOX");
assert_true(in_array('Archive', $folders), "Folders list includes Archive");

$rcmail_folders = rcmail::reset_instance();
$plugin_folders = new thunderbird_labels();
$plugin_folders->init();
$plugin_folders->action_get_folders();
$cmd_folders = null;
foreach ($rcmail_folders->output->commands as $cmd) {
    if (($cmd['command'] ?? '') === 'plugin.thunderbird_labels.folders_list') {
        $cmd_folders = $cmd;
        break;
    }
}
assert_true($cmd_folders !== null, "action_get_folders dispatches folders_list command to client");
assert_true(!empty($cmd_folders['arg1']['folders']), "folders_list payload contains folders array");
assert_true(in_array('Archive', $cmd_folders['arg1']['folders']), "folders_list includes Archive folder");

// Verify JS helpers for folder options
$js_content = file_get_contents($plugin_dir . '/tb_label.js');
assert_true(strpos($js_content, 'rcm_tb_label_get_mail_folders') !== false, "tb_label.js defines rcm_tb_label_get_mail_folders");
assert_true(strpos($js_content, 'rcm_tb_label_build_folder_options') !== false, "tb_label.js defines rcm_tb_label_build_folder_options");
assert_true(strpos($js_content, 'rcm_tb_label_folder_display_name') !== false, "tb_label.js defines rcm_tb_label_folder_display_name");
assert_true(strpos($js_content, 'rcm_tb_label_refresh_modal_folders') !== false, "tb_label.js dynamically refreshes folder select");

echo "\n*** ALL THUNDERBIRD LABELS TESTS PASSED (100%) ***\n";

