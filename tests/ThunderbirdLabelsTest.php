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

        public function __construct()
        {
            $this->conn = new stdClass();
            $this->conn->flags = [];
        }

        public function get_folder()
        {
            return $this->folder;
        }

        public function search_once($mbox, $criteria)
        {
            $res = new stdClass();
            $res->criteria = $criteria;
            $res->count = function () { return 5; };
            return new class($res) {
                public function count() { return 5; }
            };
        }

        public function set_flag($uids, $flag, $mbox)
        {
            return true;
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
                'label1' => 'Important',
                'label2' => 'Work',
                'label3' => 'Personal',
                'label4' => 'To do',
                'label5' => 'Later',
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

// --- Test 2: Custom Labels & Color Mapping ---
echo "\n--- Test 2: Custom Labels and Color Palette Mapping ---\n";
$env_labels = $rcmail->output->env['tb_label_custom_labels'];
assert_true(is_array($env_labels), "tb_label_custom_labels exported to JS environment");
assert_true(isset($env_labels['LABEL0']) && $env_labels['LABEL0'] === 'No Label', "LABEL0 defaults to No Label");
assert_true(isset($env_labels['LABEL1']) && $env_labels['LABEL1'] === 'Important', "LABEL1 defaults to Important");
assert_true(isset($env_labels['LABEL2']) && $env_labels['LABEL2'] === 'Work', "LABEL2 defaults to Work");

$env_colors = $rcmail->output->env['tb_label_colors'];
assert_true(is_array($env_colors), "tb_label_colors exported to JS environment");
assert_true(isset($env_colors['LABEL1']) && !empty($env_colors['LABEL1']), "LABEL1 has a color code assigned");
assert_true(isset($env_colors['LABEL4']) && !empty($env_colors['LABEL4']), "LABEL4 has a color code assigned");

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
assert_true(strpos($res3['search'], 'KEYWORD $LABEL1') !== false, "label:\"Important\" resolves to LABEL1");

// Number prefix e.g. label:"1: to respond"
$res4 = $plugin->imap_search_before(['search' => 'label:"1: to respond"']);
assert_true(strpos($res4['search'], 'KEYWORD $LABEL1') !== false, "label:\"1: to respond\" resolves to LABEL1");

// Tag syntax e.g. tag:Work
$res5 = $plugin->imap_search_before(['search' => 'tag:Work']);
assert_true(strpos($res5['search'], 'KEYWORD $LABEL2') !== false, "tag:Work resolves to LABEL2");

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

echo "\n*** ALL THUNDERBIRD LABELS TESTS PASSED (100%) ***\n";
