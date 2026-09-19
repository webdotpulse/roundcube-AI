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
        public $scripts = [];

        public function set_env($key, $val)
        {
            $this->env[$key] = $val;
        }

        public function include_css($file)
        {
            $this->included_css[] = $file;
        }

        public function add_script($script, $pos = 'head')
        {
            $this->scripts[] = ['script' => $script, 'pos' => $pos];
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
    'custom_logo_login',
    'custom_stylesheet',
    'custom_css',
    'custom_sidebar_bg',
    'custom_topbar_bg',
    'custom_compose_bg',
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
$rc->config->set('custom_logo_login', './logo_login.png');
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
assert_true(isset($opts['custom_logo_login']), "Form includes custom_logo_login field");
assert_true(isset($opts['custom_stylesheet']), "Form includes custom_stylesheet field");
assert_true(isset($opts['custom_css']), "Form includes custom_css field");
assert_true(isset($opts['custom_sidebar_bg']), "Form includes custom_sidebar_bg field");
assert_true(isset($opts['custom_topbar_bg']), "Form includes custom_topbar_bg field");
assert_true(isset($opts['custom_compose_bg']), "Form includes custom_compose_bg field");

// Verify values and preview components in rendered fields
assert_true(strpos($opts['custom_watermark_image']['content'], 'value="./skins/watermark.png"') !== false, "Watermark image value rendered");
assert_true(strpos($opts['custom_favicon']['content'], 'value="./favicon.ico"') !== false, "Favicon value rendered");
assert_true(strpos($opts['custom_stylesheet']['content'], 'value="./custom.css"') !== false, "Stylesheet value rendered");
assert_true(strpos($opts['custom_logo']['content'], 'value="./logo.png"') !== false, "Logo value rendered");
assert_true(strpos($opts['custom_logo_login']['content'], 'value="./logo_login.png"') !== false, "Logo login value rendered");
assert_true(strpos($opts['custom_logo']['content'], 'customizr-preview-box') !== false, "Logo field has image preview box");
assert_true(strpos($opts['custom_logo_login']['content'], 'customizr-file-input') !== false, "Logo login field has file upload button");
assert_true(strpos($opts['custom_logo']['content'], 'customizr-clear-btn') !== false, "Logo field has clear button");
assert_true(strpos($opts['custom_sidebar_bg']['content'], 'customizr-color-picker') !== false, "Sidebar color field has color picker");
assert_true(strpos($opts['custom_topbar_bg']['content'], 'customizr-color-picker') !== false, "Topbar color field has color picker");
assert_true(strpos($opts['custom_compose_bg']['content'], 'customizr-color-picker') !== false, "Compose color field has color picker");
assert_true(strpos($opts['custom_css']['content'], 'body { color: red; }') !== false, "Inline CSS value rendered");
assert_true(!empty($rc->output->scripts), "Client-side preview JavaScript added to page output");

// Test 3b: Specific options in dont_override omitted
$rc->config->set('dont_override', ['custom_logo', 'custom_css']);
$plugin_partial = new customizr();
$plugin_partial->init();
$form_partial = $plugin_partial->preferences_list(['section' => 'customizr', 'current' => true, 'blocks' => []]);
$opts_partial = $form_partial['blocks']['customizr']['options'];
assert_true(!isset($opts_partial['custom_logo']), "custom_logo omitted when in dont_override");
assert_true(!isset($opts_partial['custom_css']), "custom_css omitted when in dont_override");
assert_true(isset($opts_partial['custom_favicon']), "custom_favicon present when not in dont_override");
assert_true(isset($opts_partial['custom_logo_login']), "custom_logo_login present when not in dont_override");

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
    '_custom_logo_login'      => './new_login_logo.png',
    '_custom_stylesheet'      => './new_styles.css',
    '_custom_css'             => 'h1 > a { font-size: 20px; }',
    '_custom_sidebar_bg'      => '#2C3E50',
    '_custom_topbar_bg'       => '#1A73E8',
    '_custom_compose_bg'      => '#FF5722',
];

$plugin = new customizr();
$plugin->init();

$save_res = $plugin->preferences_save(['section' => 'customizr', 'prefs' => []]);
$saved = $save_res['prefs'];

assert_true($saved['custom_watermark_image'] === './new_watermark.png', "custom_watermark_image saved and trimmed");
assert_true($saved['custom_watermark_uri'] === 'https://example.com/welcome', "custom_watermark_uri saved");
assert_true($saved['custom_favicon'] === './new_favicon.ico', "custom_favicon saved");
assert_true(!isset($saved['custom_logo']), "custom_logo rejected because it is in dont_override");
assert_true($saved['custom_logo_login'] === './new_login_logo.png', "custom_logo_login saved");
assert_true($saved['custom_stylesheet'] === './new_styles.css', "custom_stylesheet saved");
assert_true($saved['custom_css'] === 'h1 > a { font-size: 20px; }', "custom_css saved with raw selectors");
assert_true($saved['custom_sidebar_bg'] === '#2C3E50', "custom_sidebar_bg saved with valid hex");
assert_true($saved['custom_topbar_bg'] === '#1A73E8', "custom_topbar_bg saved with valid hex");
assert_true($saved['custom_compose_bg'] === '#FF5722', "custom_compose_bg saved with valid hex");

// Test invalid color hex values sanitized to empty string
$_POST['custom_sidebar_bg'] = 'invalid_not_a_hex';
$_POST['_custom_sidebar_bg'] = 'invalid_not_a_hex';
$save_invalid = $plugin->preferences_save(['section' => 'customizr', 'prefs' => []]);
assert_true($save_invalid['prefs']['custom_sidebar_bg'] === '', "invalid color hex reset to empty string");

// Test 4b: File upload handler in preferences_save
echo "\n--- Test 4b: File Upload and Fallback ---\n";
// Create a temporary mock image file
$tmp_img = tempnam(sys_get_temp_dir(), 'test_img');
file_put_contents($tmp_img, "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR");

$mock_file = [
    'name'     => 'test_logo.png',
    'type'     => 'image/png',
    'tmp_name' => $tmp_img,
    'error'    => UPLOAD_ERR_OK,
    'size'     => 16,
];

// Test save_uploaded_file with valid image
$saved_url = $plugin->save_uploaded_file($mock_file);
assert_true(!empty($saved_url), "save_uploaded_file returns valid URL or data URI");
assert_true(
    strpos($saved_url, 'plugins/customizr/uploads/') !== false || strpos($saved_url, 'data:image/png;base64,') === 0,
    "Uploaded file saved to uploads directory or returned as base64 data URI"
);

// Test disallowing dangerous files
$tmp_bad = tempnam(sys_get_temp_dir(), 'test_bad');
file_put_contents($tmp_bad, "<?php echo 'bad'; ?>");
$bad_file = [
    'name'     => 'exploit.php',
    'type'     => 'application/x-php',
    'tmp_name' => $tmp_bad,
    'error'    => UPLOAD_ERR_OK,
    'size'     => 21,
];
$bad_res = $plugin->save_uploaded_file($bad_file);
assert_true($bad_res === null, "save_uploaded_file rejects disallowed file extension (.php)");
@unlink($tmp_bad);
@unlink($tmp_img);

// Test 5: render_page HTML modifications and Dual Logo
echo "\n--- Test 5: Render Page Modifications & Dual Logo ---\n";
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->task = 'mail'; // normal mailbox view
$rc->config->set('custom_favicon', '/fav.png');
$rc->config->set('custom_logo', '/mail_logo.svg');
$rc->config->set('custom_logo_login', '/login_only_logo.svg');
$rc->config->set('custom_css', 'a.test { color: green; }');
$rc->config->set('custom_stylesheet', '/my_styles.css');
$rc->config->set('custom_watermark_uri', '/custom_empty.html');
$rc->config->set('custom_sidebar_bg', '#123456');
$rc->config->set('custom_topbar_bg', '#abcdef');
$rc->config->set('custom_compose_bg', '#987654');

$plugin_mail = new customizr();
$plugin_mail->init();

$html_sample = '<!DOCTYPE html><html><head><title>Roundcube</title><link rel="shortcut icon" href="skins/elastic/images/favicon.ico" /></head>'
    . '<body><div id="header"><img id="logo" src="skins/elastic/images/logo.svg" /></div>'
    . '<iframe src="skins/elastic/watermark.html"></iframe></body></html>';

// On mailbox view (task = mail), logo should be $custom_logo (/mail_logo.svg)
$rendered_mail = $plugin_mail->render_page(['content' => $html_sample]);
$content_mail = $rendered_mail['content'];

assert_true(strpos($content_mail, '<link rel="shortcut icon" href="/fav.png" />') !== false, "Favicon link correctly replaced");
assert_true(strpos($content_mail, '<img id="logo" src="/mail_logo.svg"') !== false, "Mailbox view uses custom_logo");
assert_true(strpos($content_mail, '/login_only_logo.svg') === false, "Mailbox view does NOT use login logo");
assert_true(strpos($content_mail, '<iframe src="/custom_empty.html">') !== false, "Watermark link correctly replaced");
assert_true(strpos($content_mail, '<style type="text/css">') !== false && strpos($content_mail, 'a.test { color: green; }') !== false, "Inline CSS style tag correctly injected");
assert_true(strpos($content_mail, 'id="customizr-custom-colors"') !== false, "Custom colors style tag correctly injected");
assert_true(strpos($content_mail, '#123456') !== false && strpos($content_mail, '#layout-sidebar') !== false, "Custom sidebar color injected in CSS");
assert_true(strpos($content_mail, '#abcdef') !== false && strpos($content_mail, '.header') !== false, "Custom topbar color injected in CSS");
assert_true(strpos($content_mail, '#987654') !== false && strpos($content_mail, '#compose-plus') !== false, "Custom compose color injected in CSS");
assert_true(in_array('/my_styles.css', $rc->output->included_css), "External CSS registered with include_css");
assert_true($rc->output->env['blankpage'] === '/custom_empty.html', "blankpage env variable set");

// On login page (task = login), logo should be $custom_logo_login (/login_only_logo.svg)
$rc->task = 'login';
$plugin_login = new customizr();
$plugin_login->init();

$rendered_login = $plugin_login->render_page(['content' => $html_sample]);
$content_login = $rendered_login['content'];
assert_true(strpos($content_login, '<img id="logo" src="/login_only_logo.svg"') !== false, "Login view uses custom_logo_login");

// On login page when custom_logo_login is empty, should fall back to custom_logo
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->task = 'login';
$rc->config->set('custom_logo', '/mail_logo.svg');
$rc->config->set('custom_logo_login', '');
$plugin_fallback = new customizr();
$plugin_fallback->init();

$rendered_fallback = $plugin_fallback->render_page(['content' => $html_sample]);
$content_fallback = $rendered_fallback['content'];
assert_true(strpos($content_fallback, '<img id="logo" src="/mail_logo.svg"') !== false, "Login view falls back to custom_logo when custom_logo_login is empty");

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
    'custom_logo_login',
    'custom_logo_login_desc',
    'upload_image',
    'choose_file',
    'remove_image',
    'image_preview',
    'no_image',
    'upload_error',
    'custom_stylesheet',
    'custom_stylesheet_desc',
    'custom_css',
    'custom_css_desc',
    'custom_sidebar_bg',
    'custom_sidebar_bg_desc',
    'custom_topbar_bg',
    'custom_topbar_bg_desc',
    'custom_compose_bg',
    'custom_compose_bg_desc',
    'clear_color',
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

// Test 7: resolve_image_url & Elastic Logo Matching
echo "\n--- Test 7: resolve_image_url & Elastic Logo Matching ---\n";
$upload_dir = __DIR__ . '/../uploads';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}
$test_img_name = 'custom_test_' . time() . '_unit.png';
$test_img_path = $upload_dir . '/' . $test_img_name;
file_put_contents($test_img_path, "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15c4\x00\x00\x00\nIDATx\x9cc\x00\x01\x00\x00\x05\x00\x01\r\n-\xb4\x00\x00\x00\x00IEND\xaeB`\x82");

// Exact path resolve
$resolved_exact = customizr::resolve_image_url('./plugins/customizr/uploads/' . $test_img_name);
assert_true(str_starts_with($resolved_exact, 'data:image/'), "resolve_image_url converts uploads path to data URI");

// Glob prefix resolve (when extension is omitted or path is truncated)
$prefix_name = explode('.', $test_img_name)[0];
$resolved_prefix = customizr::resolve_image_url('./plugins/customizr/uploads/' . $prefix_name);
assert_true($resolved_prefix === $resolved_exact, "resolve_image_url resolves via glob prefix match when extension is omitted");

// Preserves external URL and data URI
assert_true(customizr::resolve_image_url('https://example.com/logo.png') === 'https://example.com/logo.png', "Preserves external HTTPS URL");
assert_true(customizr::resolve_image_url('data:image/svg+xml;base64,PHN2Zz4=') === 'data:image/svg+xml;base64,PHN2Zz4=', "Preserves existing data URI");

// Test Elastic skin toplogo and class logo replacement in render_page
rcube::reset_instance();
$rc = rcube::get_instance();
$rc->task = 'mail';
$rc->config->set('custom_logo', '/custom_roundcube_logo.svg');
$rc->config->set('custom_watermark_image', '/custom_watermark.png');
$plugin_elastic = new customizr();
$plugin_elastic->init();

$elastic_html = '<div id="layout-sidebar"><a href="./"><img src="skins/elastic/images/logo.svg" id="toplogo" alt="Logo"></a>'
    . '<div class="mobile-logo"><img class="logo" src="skins/elastic/images/logo.svg"></div></div>';
$rendered_elastic = $plugin_elastic->render_page(['content' => $elastic_html]);
$content_elastic = $rendered_elastic['content'];

assert_true(strpos($content_elastic, 'src="/custom_roundcube_logo.svg" id="toplogo"') !== false, "Replaces src in id=toplogo tag");
assert_true(strpos($content_elastic, 'class="logo" src="/custom_roundcube_logo.svg"') !== false, "Replaces src in class=logo tag");
assert_true($rc->output->env['xwatermark'] === '/custom_watermark.png', "Sets rcmail env xwatermark for gmail_plus / elastic");
assert_true($rc->config->get('preview_branding') === '/custom_watermark.png', "Sets preview_branding config");

@unlink($test_img_path);

echo "\n*** ALL TESTS PASSED SUCCESSFULLY ***\n";

