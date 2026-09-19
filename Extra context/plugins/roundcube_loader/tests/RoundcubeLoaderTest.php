<?php

/**
 * Unit Test Suite for Roundcube Loader Plugin
 *
 * @license GNU GPLv3+
 * @author Webdotpulse & LifePrisma
 */

declare(strict_types=1);

// Provide mock Roundcube core classes if running in standalone PHP CLI
if (!class_exists('rcube_plugin')) {
    abstract class rcube_plugin
    {
        public $api;
        public function add_hook($hook, $callback) {}
        public function load_config($fname = 'config.inc.php') {}
        public function add_texts($pdir, $add_client = false) {}
        public function include_script($fn) {}
        public function include_stylesheet($fn) {}
        public function gettext($p) { return $p; }
    }
}

if (!class_exists('rcmail')) {
    class rcmail_mock_config
    {
        private array $data = [];
        public function __construct(array $initial = []) { $this->data = $initial; }
        public function get($key, $default = null) { return $this->data[$key] ?? $default; }
        public function set($key, $val): void { $this->data[$key] = $val; }
    }

    class rcmail_mock_output
    {
        public string $type = 'html';
        public array $env = [];
        public function set_env($key, $val): void { $this->env[$key] = $val; }
    }

    class rcmail
    {
        private static ?rcmail $instance = null;
        public ?rcmail_mock_config $config = null;
        public ?rcmail_mock_output $output = null;
        public string $task = 'login';
        public string $action = '';

        public static function get_instance(): rcmail
        {
            if (self::$instance === null) {
                self::$instance = new self();
                self::$instance->config = new rcmail_mock_config();
                self::$instance->output = new rcmail_mock_output();
            }
            return self::$instance;
        }
    }
}

require_once dirname(__DIR__) . '/roundcube_loader.php';

class TestableRoundcubeLoader extends roundcube_loader
{
    public array $registeredHooks = [];
    public array $includedScripts = [];
    public array $includedStylesheets = [];

    public function __construct(rcmail $rc)
    {
        $this->rc = $rc;
    }

    public function add_hook($hook, $callback): void
    {
        $this->registeredHooks[$hook][] = $callback;
    }

    public function include_script($fn): void
    {
        $this->includedScripts[] = $fn;
    }

    public function include_stylesheet($fn): void
    {
        $this->includedStylesheets[] = $fn;
    }

    public function gettext($p): string
    {
        $translations = [
            'loading' => 'Loading...',
            'loading_user' => 'Loading %s...',
            'signing_in' => 'Signing in...',
            'standard_view' => 'Standard View',
            'taking_longer' => 'Taking longer than usual?',
            'reload' => 'Click here to reload',
        ];
        return $translations[$p] ?? $p;
    }

    public function callRenderLogo(): string
    {
        return $this->renderLogo();
    }
}

class RoundcubeLoaderTestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            echo "  \033[32m[PASS]\033[0m {$message}\n";
            $this->passed++;
        } else {
            echo "  \033[31m[FAIL]\033[0m {$message}\n";
            $this->failed++;
        }
    }

    public function run(): int
    {
        echo "=== Running Roundcube Loader Unit Tests ===\n\n";

        $rc = rcmail::get_instance();

        // Test 1: Plugin initialization and asset inclusion
        echo "--- Test 1: Initialization & Hook Registration ---\n";
        $plugin = new TestableRoundcubeLoader($rc);
        $rc->config->set('roundcube_loader_enabled', true);
        $rc->task = 'login';
        $plugin->init();

        $this->assert(in_array('assets/roundcube_loader.css', $plugin->includedStylesheets, true), 'Includes roundcube_loader.css');
        $this->assert(in_array('assets/roundcube_loader.js', $plugin->includedScripts, true), 'Includes roundcube_loader.js');
        $this->assert(isset($plugin->registeredHooks['render_page']), 'Registers render_page hook');

        // Test 2: HTML markup structure in login mode (initially hidden)
        echo "\n--- Test 2: HTML Output on Login Page ---\n";
        $htmlLogin = $plugin->buildLoaderHtml(true);
        $this->assert(strpos($htmlLogin, 'id="rc-page-loader"') !== false, 'Contains #rc-page-loader element');
        $this->assert(strpos($htmlLogin, 'rc-loader-hidden') !== false, 'Login mode has rc-loader-hidden class');
        $this->assert(strpos($htmlLogin, 'display: none;') !== false, 'Login mode starts hidden with display:none');
        $this->assert(strpos($htmlLogin, 'rc-loader-progress-track') !== false, 'Contains progress track container');
        $this->assert(strpos($htmlLogin, 'rc-loader-bar') !== false, 'Contains progress bar element');
        $this->assert(strpos($htmlLogin, 'Standard View') !== false, 'Contains Standard View subtext');

        // Test 3: HTML markup structure in application startup mode (starts hidden, unhides via sessionStorage)
        echo "\n--- Test 3: HTML Output on Mail / App Startup ---\n";
        $htmlApp = $plugin->buildLoaderHtml(false);
        $this->assert(strpos($htmlApp, 'rc-loader-hidden') !== false, 'App startup mode starts with rc-loader-hidden');
        $this->assert(strpos($htmlApp, 'display: none;') !== false, 'App startup mode starts hidden with display:none');
        $this->assert(strpos($htmlApp, 'sessionStorage.getItem(\'rc_loader_active\')===\'1\'') !== false, 'Contains inline sessionStorage login check script');

        // Test 4: Logo rendering
        echo "\n--- Test 4: Logo Rendering (Default Gmail & Roundcube) ---\n";
        $rc->config->set('roundcube_loader_logo_type', 'gmail');
        $logoGmail = $plugin->callRenderLogo();
        $this->assert(strpos($logoGmail, '<svg') !== false && strpos($logoGmail, '#4285f4') !== false, 'Default Gmail SVG logo rendered');

        $rc->config->set('roundcube_loader_logo_type', 'roundcube');
        $logoRc = $plugin->callRenderLogo();
        $this->assert(strpos($logoRc, '<svg') !== false && strpos($logoRc, '#00a0e9') !== false, 'Roundcube SVG logo rendered');

        $rc->config->set('roundcube_loader_logo_type', 'custom');
        $rc->config->set('roundcube_loader_custom_logo', 'https://example.com/custom_logo.png');
        $logoCustom = $plugin->callRenderLogo();
        $this->assert(strpos($logoCustom, 'src="https://example.com/custom_logo.png"') !== false, 'Custom image logo rendered');

        // Test 5: Themes (gmail, google_gradient, roundcube)
        echo "\n--- Test 5: Theme Variants ---\n";
        $rc->config->set('roundcube_loader_theme', 'google_gradient');
        $htmlGradient = $plugin->buildLoaderHtml(false);
        $this->assert(strpos($htmlGradient, 'rc-loader-theme-google_gradient') !== false, 'Applies google_gradient theme class');

        $rc->config->set('roundcube_loader_theme', 'roundcube');
        $htmlRoundcube = $plugin->buildLoaderHtml(false);
        $this->assert(strpos($htmlRoundcube, 'rc-loader-theme-roundcube') !== false, 'Applies roundcube theme class');

        // Test 6: Custom bar color override
        echo "\n--- Test 6: Custom Bar Color ---\n";
        $rc->config->set('roundcube_loader_bar_color', '#ff5500');
        $htmlCustomColor = $plugin->buildLoaderHtml(false);
        $this->assert(strpos($htmlCustomColor, '--rc-loader-bar-color: #ff5500;') !== false, 'Sets custom bar color variable');

        // Test 7: Page injection via render_page
        echo "\n--- Test 7: Body Injection in render_page Hook ---\n";
        $rc->config->set('roundcube_loader_theme', 'gmail');
        $rc->config->set('roundcube_loader_bar_color', null);
        $rc->task = 'mail';
        $rc->action = '';
        $sampleHtml = '<!DOCTYPE html><html><head><title>Mail</title></head><body class="elastic"><div id="layout">App Content</div></body></html>';
        $res = $plugin->render_page(['content' => $sampleHtml, 'template' => 'mail']);
        $this->assert(strpos($res['content'], '<body class="elastic">' . "\n" . '<!-- Roundcube Loader Plugin Overlay -->') !== false, 'Injected loader right after opening <body> tag');
        $this->assert(isset($rc->output->env['roundcube_loader_config']), 'Sets client config environment variable');

        // Test 8: Skip injection on print, framed dialogs, or JSON output
        echo "\n--- Test 8: Bypass on Print / Framed / JSON Requests ---\n";
        $rc->action = 'print';
        $resPrint = $plugin->render_page(['content' => $sampleHtml, 'template' => 'print']);
        $this->assert($resPrint['content'] === $sampleHtml, 'Bypasses injection on print action');
        $rc->action = '';

        $_GET['_framed'] = '1';
        $resFramed = $plugin->render_page(['content' => $sampleHtml, 'template' => 'messagepreview']);
        $this->assert($resFramed['content'] === $sampleHtml, 'Bypasses injection on framed dialogs');
        unset($_GET['_framed']);

        $rc->output->type = 'json';
        $resJson = $plugin->render_page(['content' => '{"status":"ok"}', 'template' => '']);
        $this->assert($resJson['content'] === '{"status":"ok"}', 'Bypasses injection on JSON response');
        $rc->output->type = 'html';

        echo "\n----------------------------------------\n";
        echo "Results: {$this->passed} passed, {$this->failed} failed\n";

        return ($this->failed === 0) ? 0 : 1;
    }
}

$runner = new RoundcubeLoaderTestRunner();
exit($runner->run());
