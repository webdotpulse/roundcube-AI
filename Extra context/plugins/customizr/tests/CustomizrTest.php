<?php

/**
 * Automated test suite for Roundcube Customizr plugin
 */

// Define mock Roundcube classes if not running within a full Roundcube installation
if (!class_exists('rcube')) {
    class rcube
    {
        private static $instance;
        public $config;
        public $output;
        public $task = 'settings';

        public function __construct()
        {
            $this->config = new rcube_config();
            $this->output = new rcmail_output_html();
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
        }

        public static function Q($str)
        {
            return htmlspecialchars($str, ENT_QUOTES);
        }

        public function url($action)
        {
            return './?_action=' . $action;
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

    class rcmail_output_html
    {
        public $env = [];
        public $included_css = [];

        public function set_env($key, $val)
        {
            $this->env[$key] = $val;
        }

        public function include_css($file)
        {
            $this->included_css[] = $file;
        }

        public function get_skin_file($path)
        {
            return false;
        }
    }

    class rcube_plugin
    {
        public $hooks = [];
        public $actions = [];
        public $texts = [];

        public function add_hook($hook, $callback)
        {
            $this->hooks[$hook][] = $callback;
        }

        public function register_action($action, $callback)
        {
            $this->actions[$action] = $callback;
        }

        public function load_config() {}

        public function add_texts($dir)
        {
            $file = __DIR__ . '/../' . $dir . 'en_US.inc';
            if (file_exists($file)) {
                include $file;
                if (!empty($labels)) {
                    $this->texts = array_merge($this->texts, $labels);
                }
            }
        }

        public function gettext($key)
        {
            return $this->texts[$key] ?? $key;
        }
    }

    class rcube_utils
    {
        const INPUT_POST = 1;

        public static function get_input_value($name, $source, $allow_raw = false)
        {
            $val = $_POST[$name] ?? '';
            return $val;
        }
    }

    class html
    {
        public static function tag($name, $attrib = [], $content = '')
        {
            $attr_str = '';
            foreach ($attrib as $k => $v) {
                $attr_str .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
            }
            if (empty($content) && in_array($name, ['link', 'img', 'input', 'meta'])) {
                return '<' . $name . $attr_str . ' />';
            }
            return '<' . $name . $attr_str . '>' . $content . '</' . $name . '>';
        }

        public static function label($for, $content)
        {
            return '<label for="' . htmlspecialchars($for, ENT_QUOTES) . '">' . $content . '</label>';
        }
    }

    class html_inputfield
    {
        private $attrib;
        public function __construct($attrib = [])
        {
            $this->attrib = $attrib;
        }
        public function show($value = '')
        {
            $attr_str = '';
            foreach ($this->attrib as $k => $v) {
                $attr_str .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
            }
            return '<input' . $attr_str . ' value="' . htmlspecialchars($value, ENT_QUOTES) . '" />';
        }
    }

    class html_textarea
    {
        private $attrib;
        public function __construct($attrib = [])
        {
            $this->attrib = $attrib;
        }
        public function show($value = '')
        {
            $attr_str = '';
            foreach ($this->attrib as $k => $v) {
                $attr_str .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
            }
            return '<textarea' . $attr_str . '>' . htmlspecialchars($value, ENT_QUOTES) . '</textarea>';
        }
    }
}

// Require the plugin
require_once __DIR__ . '/../customizr.php';

// Assert helper
function assert_true($expr, $message)
{
    if (!$expr) {
        echo "FAILED: $message\n";
        exit(1);
    }
    echo "PASSED: $message\n";
}

echo "=== Running Customizr Test Suite ===\n\n";

// Test 1: Plugin Initialization and Hook Registration
echo "--- Test 1: Plugin Initialization ---\n";
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->task = 'settings';
$rc->config->set('custom_logo', '/images/my_logo.png');

$plugin = new customizr();
$plugin->init();

assert_true(isset($plugin->hooks['preferences_sections_list']), "Registers preferences_sections_list hook in settings task");
assert_true(isset($plugin->hooks['preferences_list']), "Registers preferences_list hook in settings task");
assert_true(isset($plugin->hooks['preferences_save']), "Registers preferences_save hook in settings task");
assert_true(isset($plugin->hooks['render_page']), "Registers render_page hook when customizations exist");
assert_true($rc->config->get('skin_logo') === '/images/my_logo.png', "Sets skin_logo config option from custom_logo");

// Test 2: preferences_sections_list
echo "\n--- Test 2: Preferences Sections List Hook ---\n";
$args = ['list' => []];
$res = $plugin->preferences_sections_list($args);
assert_true(isset($res['list']['customizr']), "Section 'customizr' added to sections list");
assert_true($res['list']['customizr']['section'] === 'Custom Appearance', "Section label is localized");

// Test 2b: All keys locked in dont_override
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->config->set('dont_override', [
    'custom_watermark_image',
    'custom_watermark_uri',
    'custom_favicon',
    'custom_logo',
    'custom_stylesheet',
    'custom_css',
]);
$plugin_locked = new customizr();
$plugin_locked->init();
$res_locked = $plugin_locked->preferences_sections_list(['list' => []]);
assert_true(!isset($res_locked['list']['customizr']), "Section 'customizr' hidden when all keys are in dont_override");

// Test 3: preferences_list
echo "\n--- Test 3: Preferences List Hook ---\n";
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->config->set('custom_watermark_image', './skins/watermark.png');
$rc->config->set('custom_favicon', './favicon.ico');
$rc->config->set('custom_stylesheet', './custom.css');
$rc->config->set('custom_logo', './logo.png');
$rc->config->set('custom_css', 'body { color: red; }');

$plugin = new customizr();
$plugin->init();

// Probe check (!current)
$probe = $plugin->preferences_list(['section' => 'customizr', 'current' => false, 'blocks' => []]);
assert_true($probe['blocks']['customizr']['content'] === true, "Probe check sets content = true");

// Full form render (current = true)
$form = $plugin->preferences_list(['section' => 'customizr', 'current' => true, 'blocks' => []]);
$opts = $form['blocks']['customizr']['options'];

assert_true(isset($opts['custom_watermark_image']), "Form includes custom_watermark_image field");
assert_true(isset($opts['custom_watermark_uri']), "Form includes custom_watermark_uri field");
assert_true(isset($opts['custom_favicon']), "Form includes custom_favicon field");
assert_true(isset($opts['custom_logo']), "Form includes custom_logo field");
assert_true(isset($opts['custom_stylesheet']), "Form includes custom_stylesheet field");
assert_true(isset($opts['custom_css']), "Form includes custom_css field");

// Verify values in rendered fields
assert_true(strpos($opts['custom_watermark_image']['content'], 'value="./skins/watermark.png"') !== false, "Watermark image value rendered");
assert_true(strpos($opts['custom_favicon']['content'], 'value="./favicon.ico"') !== false, "Favicon value rendered");
assert_true(strpos($opts['custom_stylesheet']['content'], 'value="./custom.css"') !== false, "Stylesheet value rendered");
assert_true(strpos($opts['custom_logo']['content'], 'value="./logo.png"') !== false, "Logo value rendered");
assert_true(strpos($opts['custom_css']['content'], 'body { color: red; }') !== false, "Inline CSS value rendered");

// Test 3b: Specific options in dont_override omitted
$rc->config->set('dont_override', ['custom_logo', 'custom_css']);
$plugin_partial = new customizr();
$plugin_partial->init();
$form_partial = $plugin_partial->preferences_list(['section' => 'customizr', 'current' => true, 'blocks' => []]);
$opts_partial = $form_partial['blocks']['customizr']['options'];
assert_true(!isset($opts_partial['custom_logo']), "custom_logo omitted when in dont_override");
assert_true(!isset($opts_partial['custom_css']), "custom_css omitted when in dont_override");
assert_true(isset($opts_partial['custom_favicon']), "custom_favicon present when not in dont_override");

// Test 4: preferences_save
echo "\n--- Test 4: Preferences Save Hook ---\n";
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->config->set('dont_override', ['custom_logo']);

$_POST = [
    '_custom_watermark_image' => '  ./new_watermark.png  ',
    '_custom_watermark_uri'   => 'https://example.com/welcome',
    '_custom_favicon'         => './new_favicon.ico',
    '_custom_logo'            => './hacked_logo.png', // in dont_override!
    '_custom_stylesheet'      => './new_styles.css',
    '_custom_css'             => 'h1 > a { font-size: 20px; }',
];

$plugin = new customizr();
$plugin->init();

$save_res = $plugin->preferences_save(['section' => 'customizr', 'prefs' => []]);
$saved = $save_res['prefs'];

assert_true($saved['custom_watermark_image'] === './new_watermark.png', "custom_watermark_image saved and trimmed");
assert_true($saved['custom_watermark_uri'] === 'https://example.com/welcome', "custom_watermark_uri saved");
assert_true($saved['custom_favicon'] === './new_favicon.ico', "custom_favicon saved");
assert_true(!isset($saved['custom_logo']), "custom_logo rejected because it is in dont_override");
assert_true($saved['custom_stylesheet'] === './new_styles.css', "custom_stylesheet saved");
assert_true($saved['custom_css'] === 'h1 > a { font-size: 20px; }', "custom_css saved with raw selectors");

// Test 5: render_page HTML modifications
echo "\n--- Test 5: Render Page Modifications ---\n";
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->config->set('custom_favicon', '/fav.png');
$rc->config->set('custom_logo', '/mybrand.svg');
$rc->config->set('custom_css', 'a.test { color: green; }');
$rc->config->set('custom_stylesheet', '/my_styles.css');
$rc->config->set('custom_watermark_uri', '/custom_empty.html');

$plugin = new customizr();
$plugin->init();

$html_sample = '<!DOCTYPE html><html><head><title>Roundcube</title><link rel="shortcut icon" href="skins/elastic/images/favicon.ico" /></head>'
    . '<body><div id="header"><img id="logo" src="skins/elastic/images/logo.svg" /></div>'
    . '<iframe src="skins/elastic/watermark.html"></iframe></body></html>';

$rendered = $plugin->render_page(['content' => $html_sample]);
$content = $rendered['content'];

assert_true(strpos($content, '<link rel="shortcut icon" href="/fav.png" />') !== false, "Favicon link correctly replaced");
assert_true(strpos($content, '<img id="logo" src="/mybrand.svg"') !== false, "Logo image src correctly replaced");
assert_true(strpos($content, '<iframe src="/custom_empty.html">') !== false, "Watermark link correctly replaced");
assert_true(strpos($content, '<style type="text/css">') !== false && strpos($content, 'a.test { color: green; }') !== false, "Inline CSS style tag correctly injected");
assert_true(in_array('/my_styles.css', $rc->output->included_css), "External CSS registered with include_css");
assert_true($rc->output->env['blankpage'] === '/custom_empty.html', "blankpage env variable set");

// Test 6: Localization integrity
echo "\n--- Test 6: Localization Verification ---\n";
$langs = ['en_US', 'nl_NL', 'de_DE', 'fr_FR'];
$required_keys = [
    'customizr',
    'custom_watermark_image',
    'custom_watermark_image_desc',
    'custom_watermark_uri',
    'custom_watermark_uri_desc',
    'custom_favicon',
    'custom_favicon_desc',
    'custom_logo',
    'custom_logo_desc',
    'custom_stylesheet',
    'custom_stylesheet_desc',
    'custom_css',
    'custom_css_desc',
];

foreach ($langs as $lang) {
    $labels = [];
    $path = __DIR__ . '/../localization/' . $lang . '.inc';
    assert_true(file_exists($path), "Localization file exists for $lang");
    include $path;
    foreach ($required_keys as $key) {
        assert_true(!empty($labels[$key]), "Key '$key' defined in $lang");
    }
}

echo "\n*** ALL TESTS PASSED SUCCESSFULLY ***\n";
