<?php

/**
 * Unit Test Suite for Grid Mail Standard Logo and icon.png Favicon
 *
 * Verifies:
 * 1. "Grid Mail" SVG is standard Mailbox Logo Image & Login Page Logo Image
 * 2. "Grid Mail" SVG is standard Logo on the roundcube_loader
 * 3. "Grid Mail" SVG is standard in the gmail_plus skin
 * 4. icon.png is standard Favicon across customizr, xskin, core config, and skin assets
 *
 * @license GNU GPLv3+
 */

declare(strict_types=1);

echo "=================================================\n";
echo "  Grid Mail Logo & Favicon Verification Test Suite\n";
echo "=================================================\n";

function assert_true(bool $condition, string $message): void
{
    if ($condition) {
        echo "PASSED: {$message}\n";
    } else {
        echo "FAILED: {$message}\n";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        exit(1);
    }
}

$repoRoot = dirname(__DIR__);

// --- Test Suite 1: Asset Existence & Integrity ---
echo "\n--- Test Suite 1: Asset Existence & Integrity ---\n";

$assets = [
    'assets/grid_mail.svg' => 'Project assets grid_mail.svg',
    'assets/icon.png' => 'Project assets icon.png',
    'assets/favicon.png' => 'Project assets favicon.png',
    'assets/favicon.ico' => 'Project assets favicon.ico',
    'Extra context/skins/gmail_plus/assets/images/grid_mail.svg' => 'gmail_plus grid_mail.svg',
    'Extra context/skins/gmail_plus/assets/images/logo_header.svg' => 'gmail_plus logo_header.svg',
    'Extra context/skins/gmail_plus/assets/images/logo_login.svg' => 'gmail_plus logo_login.svg',
    'Extra context/skins/gmail_plus/assets/images/logo_header.png' => 'gmail_plus logo_header.png',
    'Extra context/skins/gmail_plus/assets/images/logo_login.png' => 'gmail_plus logo_login.png',
    'Extra context/skins/gmail_plus/assets/images/icon.png' => 'gmail_plus icon.png',
    'Extra context/skins/gmail_plus/assets/images/favicon.png' => 'gmail_plus favicon.png',
    'Extra context/skins/gmail_plus/assets/images/favicon.ico' => 'gmail_plus favicon.ico',
    'Extra context/plugins/roundcube_loader/assets/gmail_logo.svg' => 'roundcube_loader gmail_logo.svg',
    'Extra context/plugins/roundcube_loader/assets/grid_mail.svg' => 'roundcube_loader grid_mail.svg',
];

foreach ($assets as $relPath => $desc) {
    $fullPath = $repoRoot . '/' . $relPath;
    assert_true(file_exists($fullPath), "{$desc} exists at {$relPath}");
    assert_true(filesize($fullPath) > 0, "{$desc} has non-zero size (" . filesize($fullPath) . " bytes)");
}

// Verify SVG content contains Grid Mail signature paths & colors (#289FF2 & #5ABBFF)
$gridSvg = file_get_contents($repoRoot . '/assets/grid_mail.svg');
assert_true(strpos($gridSvg, '<svg') !== false, 'assets/grid_mail.svg is valid SVG');
assert_true(stripos($gridSvg, '#289FF2') !== false, 'assets/grid_mail.svg contains envelope color #289FF2');
assert_true(stripos($gridSvg, '#5ABBFF') !== false, 'assets/grid_mail.svg contains envelope flap color #5ABBFF');
assert_true(strpos($gridSvg, 'filter="url(#filter0_i_149_10)"') !== false, 'assets/grid_mail.svg contains lightning bolt filter');

// Verify favicon.ico is a valid ICO format (magic bytes: 00 00 01 00)
$icoData = file_get_contents($repoRoot . '/assets/favicon.ico');
assert_true(substr($icoData, 0, 4) === "\x00\x00\x01\x00", 'favicon.ico has valid ICO format header');

// --- Setup Mock Roundcube Core Classes ---
if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin {
        public $api;
        public function add_hook($h, $cb) {}
        public function load_config($fn = 'config.inc.php') {}
        public function add_texts($d, $c = false) {}
        public function include_script($fn) {}
        public function include_stylesheet($fn) {}
        public function gettext($p) { return $p; }
        public function register_action($a, $cb) {}
    }
}

if (!class_exists('rcube')) {
    class rcube {
        public static function Q($str) { return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8'); }
    }
}

if (!class_exists('rcube_utils')) {
    class rcube_utils {
        const INPUT_POST = 1;
        const INPUT_GET = 2;
        public static function get_input_value($name, $type) { return $_POST[$name] ?? ''; }
    }
}

if (!class_exists('html')) {
    class html {
        public static function label($for, $content) { return "<label for=\"$for\">$content</label>"; }
        public static function tag($tag, $attrs = [], $content = '') {
            $attrStr = '';
            foreach ($attrs as $k => $v) {
                $attrStr .= " $k=\"" . htmlspecialchars((string)$v, ENT_QUOTES) . "\"";
            }
            return "<$tag$attrStr>$content</$tag>";
        }
    }
}

if (!class_exists('html_checkbox')) {
    class html_checkbox {
        private array $attrib = [];
        public function __construct(array $attrib = []) { $this->attrib = $attrib; }
        public function show($val = 0) { return '<input type="checkbox" ' . ($val ? 'checked ' : '') . '/>'; }
    }
}

if (!class_exists('html_select')) {
    class html_select {
        private array $attrib = [];
        private array $options = [];
        public function __construct(array $attrib = []) { $this->attrib = $attrib; }
        public function add($names, $values) {
            foreach ($names as $idx => $name) {
                $this->options[] = ['name' => $name, 'val' => $values[$idx] ?? $name];
            }
        }
        public function show($selected = '') {
            $h = '<select>';
            foreach ($this->options as $opt) {
                $sel = ($opt['val'] == $selected) ? ' selected' : '';
                $h .= '<option value="' . $opt['val'] . '"' . $sel . '>' . $opt['name'] . '</option>';
            }
            return $h . '</select>';
        }
    }
}

if (!class_exists('html_inputfield')) {
    class html_inputfield {
        private array $attrib = [];
        public function __construct(array $attrib = []) { $this->attrib = $attrib; }
        public function show($val = '') { return '<input type="text" value="' . htmlspecialchars((string)$val, ENT_QUOTES) . '"/>'; }
    }
}

if (!class_exists('rcmail_test_config')) {
    class rcmail_test_config {
        private array $data = [];
        public function get($key, $default = null) { return $this->data[$key] ?? $default; }
        public function set($key, $val): void { $this->data[$key] = $val; }
    }
    class rcmail_test_app extends rcube {
        public $config;
        public $output;
        public string $task = 'mail';
        public function __construct() {
            $this->config = new rcmail_test_config();
        }
    }
}

if (!class_exists('rcmail')) {
    class rcmail extends rcmail_test_app {
        private static ?rcmail $instance = null;
        public static function get_instance(): rcmail {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public static function set_instance($inst): void {
            self::$instance = $inst;
        }
    }
}

// --- Test Suite 2: roundcube_loader Logo Rendering ---
echo "\n--- Test Suite 2: roundcube_loader Logo Rendering ---\n";

require_once $repoRoot . '/Extra context/plugins/roundcube_loader/roundcube_loader.php';

class TestableLoaderPlugin extends roundcube_loader {
    public function __construct($rc) {
        $this->rc = $rc;
    }
    public function getLogoHtml(): string {
        return $this->renderLogo();
    }
}

$mockRc = new rcmail_test_app();
$loaderPlugin = new TestableLoaderPlugin($mockRc);

// Default logo render (no config set)
$defaultLogo = $loaderPlugin->getLogoHtml();
assert_true(strpos($defaultLogo, '<svg') !== false, 'Default loader logo renders SVG');
assert_true(stripos($defaultLogo, '#289FF2') !== false, 'Default loader logo is Grid Mail SVG (#289FF2)');
assert_true(stripos($defaultLogo, '#5ABBFF') !== false, 'Default loader logo contains flap color #5ABBFF');

// Explicit 'gmail' logo type
$mockRc->config->set('roundcube_loader_logo_type', 'gmail');
$gmailTypeLogo = $loaderPlugin->getLogoHtml();
assert_true(stripos($gmailTypeLogo, '#289FF2') !== false, "roundcube_loader_logo_type='gmail' renders Grid Mail SVG");

// --- Test Suite 3: xskin ensureSkinLogo() in gmail_plus Skin ---
echo "\n--- Test Suite 3: xskin ensureSkinLogo() in gmail_plus Skin ---\n";

require_once $repoRoot . '/Extra context/plugins/xframework/xframework.php';
require_once $repoRoot . '/Extra context/plugins/xskin/xskin.php';

class TestableXskinPlugin extends xskin {
    public function __construct($rc, string $skin) {
        $this->rcmail = $rc;
        $this->skin = $skin;
    }
    public function callEnsureSkinLogo(): void {
        $this->ensureSkinLogo();
    }
}

$mockRcSkin = new rcmail_test_app();
$xskinPlugin = new TestableXskinPlugin($mockRcSkin, 'gmail_plus');
$xskinPlugin->callEnsureSkinLogo();

$skinLogo = $mockRcSkin->config->get('skin_logo');
assert_true(is_array($skinLogo), 'skin_logo configured as array in xskin');
assert_true(isset($skinLogo['*']), "skin_logo has '*' (Mailbox Logo)");
assert_true(strpos($skinLogo['*'], 'logo_header.svg') !== false || strpos($skinLogo['*'], 'grid_mail.svg') !== false, "Mailbox Logo is Grid Mail SVG (got: {$skinLogo['*']})");

assert_true(isset($skinLogo['login']), "skin_logo has 'login' (Login Page Logo)");
assert_true(strpos($skinLogo['login'], 'logo_login.svg') !== false || strpos($skinLogo['login'], 'grid_mail.svg') !== false, "Login Page Logo is Grid Mail SVG (got: {$skinLogo['login']})");

assert_true(isset($skinLogo['[favicon]']), "skin_logo has '[favicon]'");
assert_true(strpos($skinLogo['[favicon]'], 'favicon.png') !== false || strpos($skinLogo['[favicon]'], 'icon.png') !== false, "Standard favicon configured in skin_logo (got: {$skinLogo['[favicon]']})");

$faviconCore = $mockRcSkin->config->get('favicon');
assert_true(strpos($faviconCore, 'favicon.png') !== false || strpos($faviconCore, 'icon.png') !== false, "Standard favicon configured in core 'favicon' config (got: {$faviconCore})");

// --- Test Suite 4: customizr Plugin Standard Logo & Favicon Integration ---
echo "\n--- Test Suite 4: customizr Plugin Standard Logo & Favicon Integration ---\n";

require_once $repoRoot . '/Extra context/plugins/customizr/customizr.php';

class TestableCustomizrPlugin extends customizr {
    public function __construct($rc) {
        $this->rcmail = $rc;
        $this->settings_section = 'customizr';
    }
    public function testRenderPage(string $html): string {
        $res = $this->render_page(['content' => $html]);
        return $res['content'];
    }
}

// Case A: Mailbox page with empty/default config -> should render Grid Mail SVG and standard favicon
$mockRcCustMail = new rcmail_test_app();
$mockRcCustMail->task = 'mail';
$mockRcCustMail->output = new class {
    public $env = [];
    public function set_env($k, $v) { $this->env[$k] = $v; }
    public function include_css($c) {}
};
$customizrMail = new TestableCustomizrPlugin($mockRcCustMail);

$htmlTemplate = '<!DOCTYPE html><html><head><link rel="shortcut icon" href="skins/elastic/images/favicon.ico" /></head><body><div id="topline"><img id="logo" src="old_logo.png" /></div></body></html>';
$renderedMail = $customizrMail->testRenderPage($htmlTemplate);

assert_true(strpos($renderedMail, 'skins/gmail_plus/assets/images/logo_header.svg') !== false || strpos($renderedMail, 'skins/gmail_plus/assets/images/grid_mail.svg') !== false, "Customizr render_page injects standard Grid Mail SVG into mailbox logo");
assert_true(strpos($renderedMail, 'skins/gmail_plus/assets/images/favicon.png') !== false, "Customizr render_page injects standard icon.png (favicon.png) into favicon link");

// Case B: Login page with empty/default config -> should render Login Logo (Grid Mail SVG)
$mockRcCustLogin = new rcmail_test_app();
$mockRcCustLogin->task = 'login';
$mockRcCustLogin->output = new class {
    public $env = [];
    public function set_env($k, $v) { $this->env[$k] = $v; }
    public function include_css($c) {}
};
$customizrLogin = new TestableCustomizrPlugin($mockRcCustLogin);
$renderedLogin = $customizrLogin->testRenderPage($htmlTemplate);

assert_true(strpos($renderedLogin, 'skins/gmail_plus/assets/images/logo_login.svg') !== false || strpos($renderedLogin, 'skins/gmail_plus/assets/images/logo_header.svg') !== false, "Customizr render_page injects standard Grid Mail SVG into login logo");

// --- Test Suite 5: Dist Config Files Standard Values ---
echo "\n--- Test Suite 5: Dist Config Files Standard Values ---\n";

$customizrDist = file_get_contents($repoRoot . '/Extra context/plugins/customizr/config.inc.php.dist');
assert_true(strpos($customizrDist, "'custom_logo'] = 'skins/gmail_plus/assets/images/logo_header.svg'") !== false, "customizr config.inc.php.dist standard custom_logo is logo_header.svg");
assert_true(strpos($customizrDist, "'custom_logo_login'] = 'skins/gmail_plus/assets/images/logo_login.svg'") !== false, "customizr config.inc.php.dist standard custom_logo_login is logo_login.svg");
assert_true(strpos($customizrDist, "'custom_favicon'] = 'skins/gmail_plus/assets/images/favicon.png'") !== false, "customizr config.inc.php.dist standard custom_favicon is favicon.png");

echo "\n*** ALL GRID MAIL LOGO & FAVICON TESTS PASSED SUCCESSFULLY (100%) ***\n";
