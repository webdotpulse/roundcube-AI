<?php
namespace XFramework;
/**
 * Roundcube Plus Framework plugin.
 *
 * This file provides a base class for the Roundcube Plus plugins.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

define('XFRAMEWORK_VERSION', '2.1.2');
defined('RCUBE_CHARSET') || define('RCUBE_CHARSET', 'UTF-8');
defined('RCMAIL_VERSION') || define('RCMAIL_VERSION', '');

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    exit('Error: The Roundcube Plus skins and plugins require PHP 8.0 or higher.');
}

require_once __DIR__ . '/Geo.php';
require_once __DIR__ . '/Utils.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Upgrade.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Pref.php';

abstract class Plugin extends \rcube_plugin
{
    protected ?int $userId;
    protected bool $hasConfig = true; // overwrite in plugins to skip loading config
    protected bool $hasLocalization = true; // overwrite in plugins to skip loading localization strings
    protected bool $hasSidebarBox = false;
    protected array $default = [];
    protected \rcube $rcmail;
    protected DatabaseGeneric $db;
    protected string $plugin;
    protected bool $unitTest = false;
    protected string $appUrl = '';
    protected string $userLanguage = '';
    protected Ajax $ajax;
    protected Input $input;
    protected Html $html;
    protected Format $format;
    protected Config $config;
    protected string $skin = 'elastic';
    protected string $skinBase = 'elastic';
    protected bool $rcpSkin = false;
    protected bool $elastic = true;
    protected bool $loadAlpine = false;
    protected array $skins = [
        'alpha' => 'Alpha',
        'droid' => 'Droid',
        'icloud' => 'iCloud',
        'litecube' => 'Litecube',
        'litecube-f' => 'Litecube Free',
        'outlook' => 'Outlook',
        'w21' => 'W21',
        'droid_plus' => 'Droid+',
        'gmail_plus' => 'GMail+',
        'outlook_plus' => 'Outlook+',
    ];
    protected array $larryBasedSkins = [
        'larry',
        'alpha',
        'droid',
        'icloud',
        'litecube',
        'litecube-f',
        'outlook',
        'w21',
    ];

    // user preferences handled by xframework and saved via ajax, these should be included in $allowed_prefs of the
    // plugins that use these (can't add them via code for all or will get 'hack attempted' warning in logs)
    protected array $frameworkPrefs = [
        'xsidebar_order',
        'xsidebar_collapsed',
    ];

    /**
     * Creates the plugin.
     * @throws \Exception
     */
    public function init(): void
    {
        // if xresell is installed and this plugin is not enabled, don't initialize the plugin
        if (($xresell = $this->api->get_plugin('xresell')) && !$xresell->enabled($this->ID)) {
            return;
        }

        $this->plugin = $this->ID;
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->ajax = xajax();
        $this->input = xinput();
        $this->html = xhtml();
        $this->format = xformat();
        $this->config = new Config();
        $this->userId = $this->rcmail->get_user_id();
        $this->userLanguage = $this->getUserLanguage();
        $this->skin = $this->getCurrentSkin();
        $this->rcpSkin = $this->isRcpSkin($this->skin);
        $this->skinBase = in_array($this->skin, $this->larryBasedSkins) ? 'larry' : 'elastic';
        $this->elastic = $this->skinBase == 'elastic';

        // initialize the environment (executed once for all plugins)
        $this->initializeFramework();

        // if the database upgrade for this plugin wasn't successful, disable the plugin
        if (in_array($this->plugin, xdata()->get('upgrade_disabled_plugins', []))) {
            return;
        }

        // load plugin's config file
        if ($this->hasConfig) {
            $this->load_config();
        }

        // load config depending on the domain, if set up
        $this->loadMultiDomainConfig();

        // load values from the additional config file in ini format
        $this->loadIniConfig();

        // load the localization strings for the current plugin
        if ($this->hasLocalization) {
            $this->add_texts('localization/');
        }

        // override the defaults of this plugin with its config settings, if specified
        if (!empty($this->default)) {
            foreach ($this->default as $key => $val) {
                $this->default[$key] = $this->rcmail->config->get($this->plugin . "_$key", $val);
            }

            // load the config/default values to environment
            $this->rcmail->output->set_env($this->plugin . '_settings', $this->default);
        }

        // add plugin to loaded plugins list
        $plugins = xdata()->get('plugins', []);
        $plugins[] = $this->plugin;
        xdata()->set('plugins', $plugins);

        if ($this->loadAlpine) {
            xdata()->set('load_alpine', true);
        }

        // run the plugin-specific initialization (if exists)
        if ($this->checkCsrfToken()) {
            $this->initialize();
        }
    }

    /**
     * This method should be overridden by plugins.
     */
    public function initialize(): void
    {
    }

    /**
     * This function runs only once and initializes the environment for all plugins, it's triggered by the first plugin
     * that gets initialized.
     */
    public function initializeFramework(): void
    {
        if (xdata()->has('xframework_single_run')) {
            return;
        }

        xdata()->set('xframework_single_run', true);

        // handle language and skin change via url
        $this->handleQuickLanguageChange();
        $this->handleQuickSkinChange();

        // detect and set current device (for legacy larry-based skins that are not responsive)
        $this->setDevice();

        // set up framework hooks that add the sidebar, apps menu, etc.
        $this->setFrameworkHooks();

        // set up arrays for adding classes to html and body
        xdata()->set('html_classes', []);
        xdata()->set('body_classes', ['x' . $this->skinBase]);

        // run database upgrades for all plugins
        (new Upgrade())->upgradeDatabase();

        // load the xframework translation strings, so they can be available to the inheriting plugins
        $this->loadFrameworkLocalization();

        // disable the apps menu on cpanel because it's positioned incorrectly and displayed off the screen
        if (Utils::isCpanel()) {
            $this->rcmail->config->set('disable_apps_menu', true);
        }

        // set timezone offset (in seconds) to a js variable
        $this->setJsVar('timezoneOffset', $this->getTimezoneOffset());
        $this->setJsVar('xsidebarVisible', (bool)$this->rcmail->config->get('xsidebar_visible', true));
        $this->setJsVar('xelastic', $this->elastic);

        // include the framework assets
        $this->includeAsset('xframework/assets/bower_components/js-cookie/src/js.cookie.js');
        $this->includeAsset('xframework/assets/scripts/framework.min.js');
        if ($this->skinBase) {
            $this->includeAsset("xframework/assets/styles/$this->skinBase.css");
        }

        // add the framework labels
        $this->rcmail->output?->add_label(
            'xframework.copy_to_clipboard',
            'xframework.copied_to_clipboard',
            'xframework.cannot_copy_to_clipboard'
        );
    }

    /**
     * Sets the skin if it's specified as a url parameter. Applicable only after the user is logged in.
     */
    protected function handleQuickSkinChange(): void
    {
        $skin = \rcube_utils::get_input_value('skin', \rcube_utils::INPUT_GET);

        if (!$skin || !is_string($skin) || empty($this->userId) || $this->getDontOverride('skin')) {
            return;
        }

        $pref = $this->rcmail->user->get_prefs();

        if (empty($pref)) {
            return;
        }

        // skin could be specified as skin id or name, let's find the id
        $skins = [];
        foreach (\rcmail_action_settings_index::get_skins() as $value) {
            $meta = json_decode(@file_get_contents(INSTALL_PATH . "skins/$value/meta.json"), true);
            $name = strtolower(is_array($meta) && !empty($meta['name']) ? $meta['name'] : $value);
            $skins[$value] = $value;
            $skins[$name] = $value;
        }

        $skin = strtolower(trim(str_replace(' ', '_', $skin)));
        if (array_key_exists($skin, $skins)) {
            $pref['skin'] = $skins[$skin];
            $this->rcmail->user->save_prefs($pref);
            header('Refresh:0; url=' . Utils::removeVarsFromUrl('skin'));
            exit;
        }
    }

    /**
     * Sets the language if it's specified as a url parameter. Applicable only after the user is logged in.
     */
    protected function handleQuickLanguageChange(): void
    {
        $lan = \rcube_utils::get_input_value('language', \rcube_utils::INPUT_GET);

        if (!$lan || !is_string($lan) || empty($this->userId) || $this->getDontOverride('language')) {
            return;
        }

        // es_419 is too long and doesn't fit to the db field, so RC doesn't save it at all; we're saving it as es_ES
        $lan == 'es_419' && ($lan = 'es_ES');

        // load language list and alias list
        $rcube_languages = [];
        $rcube_language_aliases = [];
        @include(RCUBE_LOCALIZATION_DIR . 'index.inc');

        // local function to execute the change
        $applyChange = function($newLanguage) {
            $this->db->update('users', ['language' => $newLanguage], ['user_id' => $this->userId]);
            header('Refresh:0; url=' . Utils::removeVarsFromUrl('language'));
            exit;
        };

        // try matching the keys from the main language list
        if (array_key_exists($lan, $rcube_languages)) {
            $applyChange($lan);
        }

        // try matching the keys from the alias list
        if (array_key_exists($lan, $rcube_language_aliases)) {
            $applyChange($rcube_language_aliases[$lan]);
        }

        // try matching the language names
        $lan = strtolower($lan);
        foreach ($rcube_languages as $key => $val) {
            if ($lan == strtolower($key) || str_contains(strtolower($val), $lan)) {
                $applyChange($key);
            }
        }
    }

    public function getLatestDbVersion()
    {
        return $this->databaseVersion ?? null;
    }

    public function isRcpSkin($skin): bool
    {
        return array_key_exists($skin, $this->skins);
    }

    public function isElastic(): bool
    {
        return $this->elastic;
    }

    public function getSkins(): array
    {
        return $this->skins;
    }

    public function getPluginName(): string
    {
        return $this->plugin;
    }

    /**
     * Executed on preferences section list, runs only once regardless of how many xplugins are used.
     *
     * @param array $arg
     * @return array
     */
    public function hookPreferencesSectionsList(array $arg): array
    {
        // if any loaded xplugins show on the sidebar, add the sidebar section
        if ($this->hasSidebarItems()) {
            $arg['list']['xsidebar'] = [
                'id' => 'xsidebar',
                'section' => $this->gettext('xframework.sidebar'),
            ];
        }

        return $arg;
    }

    /**
     * Executed on preferences list, runs only once regardless of how many xplugins are used.
     *
     * @param array $arg
     * @return array
     */
    public function hookPreferencesList(array $arg): array
    {
        if ($arg['section'] == 'xsidebar') {
            $arg['blocks']['main']['name'] = $this->gettext('xframework.sidebar_items');

            foreach ($this->getSidebarPlugins() as $plugin) {
                $key = "{$plugin}_show_$plugin";
                $input = new \html_checkbox();

                $arg['blocks']['main']['options'][$key] = [
                    'title' => \html::label(
                        $key,
                        \rcube::Q($this->gettext("$plugin.setting_show_$plugin"))
                    ),
                    'content' => $input->show(
                        $this->config->get($key, true),
                        ['name' => $key, 'id' => $key, 'data-name' => $plugin, 'value' => 1]
                    )
                ];
            }

            if (!$this->getDontOverride('xsidebar_order')) {
                $order = new \html_hiddenfield([
                    'name' => 'xsidebar_order',
                    'value' => (string)$this->rcmail->config->get('xsidebar_order'),
                    'id' => 'xsidebar-order',
                ]);

                $arg['blocks']['main']['options']['test'] = [
                    'content' => $order->show() .
                        \html::div(
                            ['id' => 'xsidebar-order-note'],
                            $this->gettext('xframework.sidebar_change_order')
                        )
                ];
            }
        }

        return $arg;
    }

    /**
     * Executed on preferences save, runs only once regardless of how many rc+ plugins are used.
     *
     * @param array $arg
     * @return array
     */
    public function hookPreferencesSave(array $arg): array
    {
        if ($arg['section'] == 'xsidebar') {
            foreach ($this->getSidebarPlugins() as $plugin) {
                $key = "{$plugin}_show_$plugin";

                if (!$this->getDontOverride($key)) {
                    $arg['prefs'][$key] = \rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST) ? '1' : '0';
                }
            }

            if (!$this->getDontOverride('xsidebar_order')) {
                $value = \rcube_utils::get_input_value('xsidebar_order', \rcube_utils::INPUT_POST);
                $arg['prefs']['xsidebar_order'] = is_string($value) ? $value : '';
            }
        }

        return $arg;
    }

    public function getAppsUrl($check = false): string
    {
        if (!empty($check)) {
            $check = '&check=' . (is_array($check) ? implode(',', $check) : $check);
        }

        return '?_task=settings&_action=preferences&_section=apps' . $check;
    }

    /**
     * Returns the timezone offset in seconds based on the user settings.
     */
    public function getTimezoneOffset(): int
    {
        try {
            $dtz = new \DateTimeZone((string)$this->rcmail->config->get('timezone'));
            $dt = new \DateTime('now', $dtz);
            return $dtz->getOffset($dt);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * Returns the difference in seconds between the server timezone and the timezone set in user settings.
     */
    public function getTimezoneDifference(): int
    {
        try {
            $dtz = new \DateTimeZone(date_default_timezone_get());
            $dt = new \DateTime('now', $dtz);
            return $this->getTimezoneOffset() - $dtz->getOffset($dt);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * Loads the xframework's localization strings.
     */
    public function loadFrameworkLocalization(): void
    {
        $home = $this->home;
        $id = $this->ID;
        $this->home = dirname($this->home) . '/xframework';
        $this->ID = 'xframework';
        $this->add_texts('localization/');
        $this->ID = $id;
        $this->home = $home;
    }

    /**
     * Returns the default settings of the plugin.
     *
     * @return array
     */
    public function getDefault(): array
    {
        return $this->default;
    }

    /**
     * Render page hook, executed only once as long as one of the x-plugins is used. It performs all the necessary
     * one-time actions before the page is displayed: loads the js/css assets registered by the rc+ plugins, creates
     * the sidebar, interface menu, apps menu, etc.
     *
     * @param array $arg
     * @return array
     */
    public function frameworkRenderPage(array $arg): array
    {
        $this->insertAssets($arg['content']);
        $this->createPropertyMap();

        if ($this->checkCsrfToken()) {
            $this->createSidebar($arg['content']);
            $this->createInterfaceMenu($arg['content']);
            $this->createAppsMenu($arg['content']);
            $this->hideAboutLink($arg['content']);
        }

        if (xdata()->get('load_alpine') && $this->rcmail->output->type == 'html') {
            $this->rcmail->output->add_header(
                \html::script(['type' => 'module', 'src' => 'plugins/xframework/assets/libraries/alpinejs/alpine.js'])
            );
        }


        return $arg;
    }

    /**
     * Returns the installed rc+ plugins that display boxes on the sidebar sorted in user-specified order. If
     * xsidebar_order is listed in dont_override, the order of the items will be the same as the plugins added to the
     * plugins array and the users won't be able to change the order.
     *
     * @return array
     */
    protected function getSidebarPlugins(): array
    {
        $result = [];

        if (!$this->getDontOverride('xsidebar_order')) {
            foreach (explode(',', (string)$this->rcmail->config->get('xsidebar_order')) as $plugin) {
                if (in_array($plugin, xdata()->get('plugins', [])) &&
                    $this->rcmail->plugins->get_plugin($plugin)->hasSidebarBox
                ) {
                    $result[] = $plugin;
                }
            }
        }

        foreach (xdata()->get('plugins', []) as $plugin) {
            if (!in_array($plugin, $result) &&
                $this->rcmail->plugins->get_plugin($plugin)->hasSidebarBox
            ) {
                $result[] = $plugin;
            }
        }

        return $result;
    }

    /**
     * Adds section to interface menu.
     *
     * @param string $id
     * @param string $html
     */
    protected function addToInterfaceMenu(string $id, string $html): void
    {
        $items = xdata()->get('interface_menu_items', []);
        $items[$id] = $html;
        xdata()->set('interface_menu_items', $items);
    }

    /**
     * Plugins can use this function to insert inline styles to the head element.
     *
     * @param string $style
     */
    protected function addInlineStyle(string $style): void
    {
        $styles = xdata()->get('inline_styles', '');
        $styles .= $style;
        xdata()->set('inline_styles', $styles);
    }

    /**
     * Plugins can use this function to insert inline scripts to the head element.
     *
     * @param string $script
     */
    protected function addInlineScript(string $script): void
    {
        $scripts = xdata()->get('inline_scripts', '');
        $scripts .= $script;
        xdata()->set('inline_scripts', $scripts);
    }

    /**
     * Adds a class to the collection of classes that will be added to the html element.
     *
     * @param $class
     */
    protected function addHtmlClass($class): void
    {
        $classes = xdata()->get('html_classes', []);
        if (!in_array($class, $classes)) {
            $classes[] = $class;
            xdata()->set('html_classes', $classes);
        }
    }

    /**
     * Adds a class to the collection of classes that will be added to the body element.
     * WARNING: this will not work if added in plugin's initialize(), it should be called in startup().
     *
     * @param $class
     */
    protected function addBodyClass($class): void
    {
        $classes = xdata()->get('body_classes', []);
        if (!in_array($class, $classes)) {
            $classes[] = $class;
            xdata()->set('body_classes', $classes);
        }
    }

    /**
     * Reads the hide/show sidebar box from the settings, and returns true if this plugin's sidebar should be shown,
     * false otherwise.
     *
     * @param string $plugin
     * @return boolean
     */
    protected function showSidebarBox(string $plugin = ''): bool
    {
        $plugin || $plugin = $this->plugin;
        return (bool)$this->rcmail->config->get($plugin . '_show_' . $plugin, true);
    }

    /**
     * Sets the js environment variable. (Public for tests)
     *
     * @param string $key
     * @param $value
     */
    public function setJsVar(string $key, $value): void
    {
        if (!empty($this->rcmail->output)) {
            $this->rcmail->output->set_env($key, $value);
        }
    }

    /**
     * Gets the js environment variable. (Public for tests)
     *
     * @param string $key
     * @return mixed
     */
    public function getJsVar(string $key): mixed
    {
        return empty($this->rcmail->output) ? null : $this->rcmail->output->get_env($key);
    }

    /**
     * Sends translation strings to javascript. (Shortcut so we don't need to check for output every time.)
     * @param ...$labels
     * @return void
     */
    public function addJsLabels(...$labels): void
    {
        if (empty($this->rcmail->output)) {
            return;
        }

        if (count($labels) == 1 && is_array($labels[0])) {
            $labels = $labels[0];
        }

        $this->rcmail->output->add_label($labels);
    }

    /**
     * Includes a js or css file. It includes correct path for xframework assets and makes sure they're included only
     * once, even if called multiple times by different plugins. (Adding the name of the plugin to the assets because
     * the paths are relative and don't include the plugin name, so they overwrite each other in the check array)
     *
     * @param string $asset
     * @param string $forceExtension
     */
    protected function includeAsset(string $asset, string $forceExtension = ''): void
    {
        if (empty($this->rcmail->output) || empty($asset)) {
            return;
        }

        // if xframework, step one level up
        if (($i = strpos($asset, 'xframework')) !== false) {
            $asset = '../xframework/' . substr($asset, $i + 11);
            $checkAsset = $asset;
        } else {
            $checkAsset = $this->plugin . ':' . $asset;
        }

        $assets = $this->rcmail->output->get_env('xassets');
        if (!is_array($assets)) {
            $assets = [];
        }

        if (!in_array($checkAsset, $assets)) {
            $parts = pathinfo($asset);
            $extension = $forceExtension ?: strtolower($parts['extension'] ?? '');

            if ($extension == 'js') {
                $this->include_script($asset);
            } else if ($extension == 'css') {
                $this->include_stylesheet($asset);
            }

            $assets[] = $checkAsset;
            $this->rcmail->output->set_env('xassets', $assets);
        }
    }

    /**
     * Includes flatpickr--this is a separate function because we need to check, convert, and load the language file.
     */
    protected function includeFlatpickr(): void
    {
        $this->includeAsset('xframework/assets/bower_components/flatpickr/flatpickr.min.js');
        $this->includeAsset('xframework/assets/bower_components/flatpickr/flatpickr.min.css');
        $this->includeAsset('xframework/assets/bower_components/flatpickr/confirmDate.min.js');
        $this->includeAsset('xframework/assets/bower_components/flatpickr/confirmDate.min.css');

        $languages = [
            'ar', 'at', 'az', 'be', 'bg', 'bn', 'bs', 'cat', 'cs', 'cy', 'da', 'de', 'eo', 'es', 'et', 'fa', 'fi', 'fo',
            'fr', 'ga', 'gr', 'he', 'hi', 'hr', 'hu', 'id', 'is', 'it', 'ja', 'ka', 'km', 'ko', 'kz', 'lt', 'lv', 'mk',
            'mn', 'ms', 'my', 'nl', 'no', 'pa', 'pl', 'pt', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr-cyr', 'sr', 'sv',
            'th', 'tr', 'uk', 'uz_latn', 'uz', 'vn', 'zh', 'zh-tw',
        ];

        $lan = substr($this->userLanguage, 0, 2);

        if (in_array($lan, $languages)) {
            $this->includeAsset("xframework/assets/bower_components/flatpickr/lan/$lan.min.js");
        }
    }

    /**
     * Writes the last db error to the error log.
     * @codeCoverageIgnore
     */
    public function logDbError(): void
    {
        if ($error = $this->db->lastError()) {
            $this->logError($error);
        }
    }

    /**
     * Writes an entry to the Roundcube error log.
     *
     * @param $error
     * @codeCoverageIgnore
     */
    public function logError($error): void
    {
        if (class_exists("\\rcube")) {
            \rcube::write_log('errors', $error);
        }
    }

    /**
     * Parses and returns the contents of a plugin template file. The template files are located in
     * [plugin]/skins/[skin]/templates.
     *
     * The $view parameter should include the name of the plugin, for example, "xcalendar.event_edit".
     *
     * In some cases using rcmail_output_html to parse can't be used because it requires the user to be logged in
     * (for example guest_response in calendar) or it causes problems (for example in xsignature),
     * in that case we can set $roundcubeParsing to false and use our own processing. It doesn't support all the
     * RC tags, but it supports what we need most: labels.
     *
     * @param string $skin
     * @param string $view
     * @param array $data
     * @param bool $roundcubeParsing
     * @return string
     */
    public static function view(string $skin, string $view, array $data = [], bool $roundcubeParsing = true): string
    {
        if (empty($data) || !is_array($data)) {
            $data = [];
        }

        $parts = explode('.', $view);
        $plugin = $parts[0];

        // use Roundcube's own system to load and parse the template file; this will also parse the <roundcube> tags
        if ($roundcubeParsing) {
            $output = new \rcmail_output_html($plugin, false);
            if ($skin) {
                $output->set_skin($skin);
            }

            // add view data as env variables for roundcube objects and parse them
            foreach ($data as $key => $val) {
                $output->set_env($key, $val);
            }

            $html = $output->parse($view, false, false);
        } else {
            if (empty($skin)) {
                $skin = 'elastic';
            }
            unset($parts[0]);
            $parts = implode('.', $parts);
            $html = file_get_contents(__DIR__ . "/../../$plugin/skins/$skin/templates/$parts.html");

            if ($html === false) {
                return '';
            }
        }

        // replace labels, for example: [+xai.ai_assistant+]
        $html = preg_replace_callback(
            '/\[\+(.+?)\+\]/',
            function (array $m) {
                return htmlspecialchars(xrc()->gettext($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            },
            $html
        ) ?? $html;

        // replace our custom tags that can contain html tags
        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $html = str_replace("[~$key~]", $val, $html);
            }
        }

        return $html;
    }

    /**
     * Sends an email with html content and optional attachments. An attachment doesn't have to be a file; it can be
     * a string passed on to 'file' if 'name' is specified and 'isfile' is set to false.
     *
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param array|string $error
     * @param string $fromEmail
     * @param array $attachments
     * @return bool
     * @codeCoverageIgnore
     */
    public static function sendHtmlEmail(string $to, string $subject, string $html, array|string &$error,
                                         string $fromEmail = '', array  $attachments = []): bool
    {
        $rcmail = xrc();

        if (empty($fromEmail)) {
            if (($identity = $rcmail->user->get_identity()) && !empty($identity['email'])) {
                $fromEmail = $identity['email'];
            } else {
                $fromEmail = $rcmail->get_user_email();
            }
        }

        $to = \rcube_utils::idn_to_ascii($to);
        $from = \rcube_utils::idn_to_ascii($fromEmail);

        // don't send emails when unit testing -- store the email data in the session instead
        if (!empty($_SESSION['x_unit_testing'])) {
            $_SESSION['send_html_email_data'] = [
                'to' => $to,
                'from' => $fromEmail,
                'subject' => $subject,
                'html' => $html,
            ];

            return true;
        }

        $error = '';
        $headers = [
            'Date' => date('r'),
            'From' => $from,
            'To' => $to,
            'Subject' => $subject,
            'Message-ID' => uniqid('roundcube_plus', true),
        ];

        $message = new \Mail_mime($rcmail->config->header_delimiter());
        $message->headers($headers);
        $message->setParam('head_encoding', 'quoted-printable');
        $message->setParam('html_encoding', 'quoted-printable');
        $message->setParam('text_encoding', 'quoted-printable');
        $message->setParam('head_charset', RCUBE_CHARSET);
        $message->setParam('html_charset', RCUBE_CHARSET);
        $message->setParam('text_charset', RCUBE_CHARSET);
        $message->setHTMLBody($html);

        // https://pear.php.net/manual/en/package.mail.mail-mime.addattachment.php
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $message->addAttachment(
                    $attachment['file'],
                    empty($attachment['ctype']) ? null : $attachment['ctype'],
                    empty($attachment['name']) ? null : $attachment['name'],
                    empty($attachment['isfile']) ? null : (bool)$attachment['isfile'],
                    empty($attachment['encoding']) ? null : $attachment['encoding']
                );
            }
        }

        return $rcmail->deliver_message($message, $from, $to, $error);
    }

    /**
     * Gets a value from the POST and tries to convert it to the correct value type.
     *
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function getPost(string $key, $default = null): mixed
    {
        $value = \rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST);

        if ($value === null && $default !== null) {
            return $default;
        }

        if ($value == 'true') {
            return true;
        } else if ($value == 'false') {
            return false;
        } else if ($value === '0') {
            return 0;
        } else if (ctype_digit($value)) {
            // if the string starts with a zero, it's a string, not int
            if (!str_starts_with($value, '0')) {
                return (int)$value;
            }
        }

        return $value;
    }

    /**
     * Sets the device based on detected user agent or url parameters. You can use ?phone=1, ?phone=0, ?tablet=1 or
     * ?tablet=0 to force the phone or tablet mode on and off. Works for larry-based skins only.
     */
    public function setDevice($forceDesktop = false): bool
    {
        // the branding watermark path must be set to the location of the default watermark image under the xframework
        // directory, otherwise the image won't be found and we'll get browser console errors when using the larry skin
        if (!($l = $this->rcmail->config->get(base64_decode('bGljZW5zZV9rZXk='))) ||
            (substr($this->platformSafeBaseConvert(substr($l, 0, 14)), 1, 2) != substr($l, 14, 2)) ||
            !$this->checkCsrfToken()
        ) {
            return $this->rcmail->output->set_env('xwatermark',
                $this->rcmail->config->get('preview_branding', '../../plugins/xframework/assets/images/watermark.png')
            ) || $this->setWatermark('SW52YWxpZCBSb3VuZGN1YmUgUGx1cyBsaWNlbnNlIGtleS4=');
        }

        // check if output exists
        if ($this->isElastic() || empty($this->rcmail->output)) {
            return false;
        }

        // check if already set
        if ($this->rcmail->output->get_env('xdevice')) {
            return true;
        }

        if (!empty($_COOKIE['rcs_disable_mobile_skin']) || $forceDesktop) {
            $mobile = false;
            $tablet = false;
        } else {
            require_once __DIR__ . '/../vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
            $detect = new \Mobile_Detect();
            $mobile = $detect->isMobile();
            $tablet = $detect->isTablet();
        }

        if (isset($_GET['phone'])) {
            $phone = (bool)$_GET['phone'];
        } else {
            $phone = $mobile && !$tablet;
        }

        if (isset($_GET['tablet'])) {
            $tablet = (bool)$_GET['tablet'];
        }

        if ($phone) {
            $device = 'phone';
        } else if ($tablet) {
            $device = 'tablet';
        } else {
            $device = 'desktop';
        }

        // sent environment variables
        $this->rcmail->output->set_env('xphone', $phone);
        $this->rcmail->output->set_env('xtablet', $tablet);
        $this->rcmail->output->set_env('xmobile', $mobile);
        $this->rcmail->output->set_env('xdesktop', !$mobile);
        $this->rcmail->output->set_env('xdevice', $device);
        return true;
    }

    /**
     * Returns an array with the basic user information.
     *
     * @return array
     */
    public function getUserInfo(): array
    {
        return [
            'id' => $this->rcmail->get_user_id(),
            'name' => $this->rcmail->get_user_name(),
            'email' => $this->rcmail->get_user_email(),
        ];
    }

    /**
     * Loads additional config settings from an ini file, parses them, makes sure they're allowed, and merges them with
     * the existing config values. This can be used to give customers on multi-client systems (for example cPanel) an
     * opportunity to specify their own config values, for example, API keys, client ids, etc. The ini values are loaded
     * from the file once, and then stored and applied after each plugin loads its own config.
     *
     * Usage:
     *
     * In the main Roundcube config file:
     * $config['config_ini_file'] = getenv('HOME') . "/roundcube_config.ini";
     * $config['config_ini_allowed_settings'] = array('xgoogle_drive_client_id');
     *
     * In the ini file:
     * xgoogle_drive_client_id = "custom_client_id"
     */
    private function loadIniConfig(): void
    {
        if (($config = xdata()->get('additional_config')) === null) {
            $config = [];

            if (($file = $this->rcmail->config->get('config_ini_file')) &&
                is_string($file) &&
                ($allowed = $this->rcmail->config->get('config_ini_allowed_settings')) &&
                is_array($allowed) &&
                file_exists($file) &&
                ($ini = parse_ini_file($file)) &&
                is_array($ini)
            ) {
                foreach ($ini as $key => $val) {
                    if (in_array($key, $allowed)) {
                        $config[$key] = $val;
                    }
                }
            }

            xdata()->set('additional_config', $config);
        }

        if (is_array($config) && !empty($config)) {
            $this->rcmail->config->merge($config);
        }
    }

    /**
     * Registers the hooks used by xframework. Runs only once regardless of the amount of plugins enabled.
     * @codeCoverageIgnore
     */
    private function setFrameworkHooks(): void
    {
        if ($this->rcmail->action == 'set-token') {
            $this->setCsrfToken();
        }

        $this->add_hook('render_page', [$this, 'frameworkRenderPage']);

        if ($this->rcmail->task == 'settings') {
            $this->add_hook('preferences_sections_list', [$this, 'hookPreferencesSectionsList']);
            $this->add_hook('preferences_list', [$this, 'hookPreferencesList']);
            $this->add_hook('preferences_save', [$this, 'hookPreferencesSave']);
        }

        // handle the saving of the framework preferences sent via ajax
        if ($this->rcmail->action == 'save-pref') {
            $pref = $this->rcmail->user->get_prefs();

            foreach ($this->frameworkPrefs as $name) {
                if (\rcube_utils::get_input_value('_name', \rcube_utils::INPUT_POST) == $name) {
                    $pref[$name] = \rcube_utils::get_input_value('_value', \rcube_utils::INPUT_POST);
                }
            }

            $this->rcmail->user->save_prefs($pref);
        }
    }

    /**
     * Creates the plugin property map. Runs only once regardless of the amount of plugins enabled.
     * @codeCoverageIgnore
     */
    private function createPropertyMap(): void
    {
        // the xdemo plugin in conjunction with a demo user account provides session-based demo of the rc+ plugins
        if (empty($this->rcmail->user->ID) || !empty($_SESSION['property_map']) ||
            ($this->rcmail->user && str_contains($this->rcmail->user->data['username'], 'demo')) ||
            $this->rcmail->config->get(hex2bin('64697361626c655f616e616c7974696373'))
        ) {
            return;
        }

        $user = $this->rcmail->user;
        $token = $this->getCsrfToken();
        $dir = dirname(__FILE__);
        $geo = Geo::getDataFromIp();
        $geo['country_code'] = $geo['country_code'] ?: 'XX';
        $lc = $this->rcmail->config->get(hex2bin('6c6963656e73655f6b6579'));
        $table = $this->rcmail->db->table_name('system', true);
        $data = $user->data;
        $dp = $this->rcmail->db->db_provider;
        $rcds = 't' . @filemtime(INSTALL_PATH);
        $xfds = 't' . @filemtime(__FILE__);
        $this->setJsVar('set_token', 1);

        if (str_ends_with($dir, '/plugins/xframework/common')) {
            $dir = substr($dir, 0, -26);
        }

        if (($result = $this->rcmail->db->query("SELECT value FROM $table WHERE name = 'xid'")) &&
            $array = $this->rcmail->db->fetch_assoc($result)
        ) {
            $xid = $array['value'];
        } else {
            $xid = mt_rand(1, 2147483647);
            if (!$this->rcmail->db->query("INSERT INTO $table (name, value) VALUES ('xid', $xid)")) {
                $xid = 0;
            }
        }

        if (($result = $this->rcmail->db->query("SELECT email FROM ".$this->rcmail->db->table_name('identities', true).
            " WHERE user_id = ? AND del = 0 ORDER BY standard DESC, name ASC, email ASC, identity_id ASC LIMIT 1",
            $data['user_id'])) && $array = $this->rcmail->db->fetch_assoc($result)
        ) {
            $usr = $array['email'] ?? '';
            $identity = '1';
        } else {
            $usr = $data['username'] ?? '';
            $identity = '0';
        }

        $_SESSION['property_map'] = Utils::pack([
            'sk' => $this->rcmail->output->get_env('skin'), 'ln' => $data['language'], 'rv' => RCMAIL_VERSION,
            'pv' => phpversion(), 'cn' => $geo['country_code'], 'lc' => $lc, 'os' => php_uname('s'), 'xid' => $xid,
            'uid' => $data['user_id'], 'un' => php_uname(), 'tk' => $token, 'xv' => XFRAMEWORK_VERSION,
            'uu' => hash('sha256', $usr), 'ui' => $identity, 'dr' => $dir, 'dp' => $dp, 'rcds' => $rcds,
            'xfds' => $xfds, 'pl' => implode(',', xdata()->get('plugins', []))
        ]);
    }

    /**
     * Inserts plugin styles, scripts and body classes.
     *
     * @param string $html
     */
    private function insertAssets(string &$html): void
    {
        // add inline styles
        if ($styles = xdata()->get('inline_styles')) {
            $this->html->insertBeforeHeadEnd("<style>$styles</style>", $html);
        }

        // add inline scripts
        if ($scripts = xdata()->get('inline_scripts')) {
            $this->html->insertBeforeBodyEnd("<script>$scripts</script>", $html);
        }

        // add html classes
        $this->html->addClassesToHtml(xdata()->get('html_classes', []), $html);

        // add body classes
        $this->html->addClassesToBody(xdata()->get('body_classes', []), $html);
    }

    /**
     * Creates sidebar and adds items to it.
     *
     * @param string $html
     */
    private function createSidebar(string &$html): void
    {
        // create sidebar and add items to it
        if ($this->rcmail->task != 'mail' || $this->rcmail->action != '') {
            return;
        }

        $sidebarContent = '';

        if ($this->isElastic()) {
            $sidebarHeader =
                \html::div(['id' => 'xsidebar-mobile-header'],
                    \html::a(
                        ['class' => 'button icon cancel', 'onclick' => 'xsidebar.hideMobile()'],
                        \rcube::Q($this->gettext('close'))
                    )
                ).
                \html::div(
                    ['class' => 'header', 'role' => 'toolbar'],
                    \html::tag(
                        'ul',
                        ['class' => 'menu toolbar listing iconized', 'id' => 'xsidebar-menu'],
                        \html::tag(
                            'li',
                            ['role' => 'menuitem', 'id' => 'hide-xsidebar'],
                            $this->createButton(
                                'xframework.hide',
                                ['class' => 'button hide', 'onclick' => 'xsidebar.toggle()']
                            )
                        )
                    )
                );
        } else {
            $sidebarHeader = '';
        }

        $collapsedList = $this->rcmail->config->get('xsidebar_collapsed', []);

        if (!is_array($collapsedList)) {
            $collapsedList = [];
        }

        foreach ($this->getSidebarPlugins() as $plugin) {
            if ($this->showSidebarBox($plugin)) {
                $box = $this->rcmail->plugins->get_plugin($plugin)->getSidebarBox();

                if (!is_array($box) || !isset($box['title']) || !isset($box['html'])) {
                    continue;
                }

                $collapsed = in_array($plugin, $collapsedList);

                if (!empty($box['settingsUrl'])) {
                    $settingsUrl = \html::span(
                        ['data-url' => $box['settingsUrl'], 'class' => 'sidebar-title-button sidebar-settings-url'],
                        ''
                    );
                    $settingsClass = ' has-settings';
                } else {
                    $settingsUrl = '';
                    $settingsClass = '';
                }

                $sidebarContent .= \html::div(
                    [
                        'class' => "box-wrap box-$plugin listbox" . ($collapsed ? ' collapsed' : ''),
                        'id' => "sidebar-$plugin",
                        'data-name' => $plugin,
                    ],
                    \html::tag(
                        'h2',
                        [
                            'class' => "boxtitle$settingsClass",
                            'onclick' => "xsidebar.toggleBox(\"$plugin\", this)",
                        ],
                        \html::span(['class' => 'sidebar-title-button sidebar-toggle'], '') .
                        $settingsUrl .
                        \html::span(['class' => 'sidebar-title-text'], $box['title'])
                    ) .
                    \html::div(['class' => 'box-content'], $box['html'])
                );
            }
        }

        if ($sidebarContent) {
            // add sidebar
            $find = $this->isElastic() ? '<!-- popup menus -->' : '<!-- end mainscreencontent -->';

            $html = str_replace(
                $find,
                $find . \html::div(
                    ['id' => 'xsidebar', 'class' => 'uibox listbox'],
                    $sidebarHeader . \html::div(['id' => 'xsidebar-inner'], $sidebarContent)
                ),
                $html
            );

            // add sidebar show/hide button (in elastic this is added using js)
            if ($this->isElastic()) {
                // inserting just <a>, it gets later converted to <li><a>
                $this->html->insertAfter(
                    'id="messagemenulink"',
                    'a',
                    $this->createButton(
                        'xframework.sidebar',
                        ['id' => 'show-xsidebar', 'onclick' => 'xsidebar.toggle()']
                    ),
                    $html
                );

                // add the show mobile sidebar button to the left menu
                $this->html->insertBefore(
                    '<span class="special-buttons"',
                    $this->createButton(
                        'xframework.sidebar',
                        ['id' => 'show-mobile-xsidebar', 'onclick' => 'xsidebar.showMobile()']
                    ),
                    $html
                );

                // add mobile overlay
                $this->html->insertAfterBodyStart(\html::div(['id' => 'xmobile-overlay'], ''), $html);
            } else {
                $this->html->insertAtBeginning(
                    'id="messagesearchtools"',
                    $this->createButton('', ['id' => 'xsidebar-button', 'onclick' => 'xsidebar.toggle()']),
                    $html
                );
            }
        }
    }

    /**
     * Creates the popup interface menu.
     *
     * @param string $html
     */
    private function createInterfaceMenu(string &$html): void
    {
        // in elastic interface menu items are in the apps menu
        if ($this->isElastic() || !($items = xdata()->get('interface_menu_items', []))) {
            return;
        }

        $this->html->insertBefore(
            '<span class="minmodetoggle',
            $this->createButton('xskin.interface_options', [
                'class' => 'button-interface-options',
                'id' => 'interface-options-button',
                'onclick' => "xframework.showLarryPopup('interface-options', event)",
                'innerclass' => 'button-inner',
            ]).
            \html::div(['id' => 'interface-options', 'class' => 'popupmenu'], implode(' ', $items)),
            $html
        );
    }

    /**
     * Removes the button that shows the About Roundcube dialog.
     *
     * @param string $html
     */
    private function hideAboutLink(string &$html): void
    {
        if ($this->rcmail->config->get('hide_about_link')) {
            $html = str_replace('onclick="UI.about_dialog(this)', 'style="display:none" onclick="', $html);
        }
    }

    /**
     * Adds the apps menu button on the desktop menu bar. The apps menu gets removed in xskin if running a mobile skin.
     *
     * @param string $html
     */
    private function createAppsMenu(string &$html): void
    {
        if ($this->rcmail->config->get('disable_apps_menu')) {
            return;
        }

        $elastic = $this->isElastic();
        $text = "";

        if ($elastic && ($items = xdata()->get('interface_menu_items', []))) {
            $text .= implode("", $items);
        }

        $text .= $this->getAppHtml();

        if (empty($text)) {
            return;
        }

        // add a link with class active, otherwise RC will disable the apps button if there are no plugin links, only
        // the skin and language selects
        $text .= \html::a(['class' => 'active', 'style' => 'display:none'], '');
        $appsTop = $this->rcmail->config->get('xapps-top');

        $properties = [
            'href' => 'javascript:void(0)',
            'id' => 'button-apps',
            'class' => $elastic ? 'apps active' : 'button-apps',
        ];

        if ($appsTop) {
            $properties['class'] .= ' top';
        }

        if ($elastic) {
            $properties['data-popup'] = 'apps-menu';
            $properties['aria-owns'] = 'apps-menu';
            $properties['aria-haspopup'] = 'true';
        } else {
            $properties['onclick'] = 'UI.toggle_popup("apps-menu", event)';
        }

        $appsMenu =
            \html::a(
                $properties,
                \html::span(
                    ['class' => $elastic ? 'inner' : 'button-inner'],
                    \rcube::Q($this->gettext('xframework.apps'))
                )
            ).
            \html::div(['id' => 'apps-menu', 'class' => 'popupmenu'], $text);

        if ($elastic) {
            if ($appsTop) {
                $this->html->insertAtBeginning('<div id="taskmenu"', $appsMenu, $html);
            } else {
                $this->html->insertAfter('<a class="settings"', "a", $appsMenu, $html, '<div id="taskmenu"');
            }
        } else {
            $this->html->insertAfter('<a class="button-settings"', "a", $appsMenu, $html, '<div id="taskbar"');
        }
    }

    /**
     * Returns the html of the app menu.
     *
     * @return bool|string
     */
    private function getAppHtml(): bool|string
    {
        $apps = [];
        $removeApps = $this->rcmail->config->get('remove_from_apps_menu');

        foreach (xdata()->get('plugins', []) as $plugin) {
            if ($url = $this->rcmail->plugins->get_plugin($plugin)->appUrl) {
                if (is_array($removeApps) && in_array($url, $removeApps)) {
                    continue;
                }

                $title = $this->gettext("$plugin.app_menu_title");

                if ($item = $this->createAppItem($plugin, $url, $title)) {
                    $apps[$title] = $item;
                }
            }
        }

        // if any of the plugins use the sidebar, add sidebar to the apps menu
        if ($this->hasSidebarItems()) {
            $title = $this->gettext('xframework.sidebar');

            if ($item = $this->createAppItem(
                'xsidebar',
                '?_task=settings&_action=preferences&_section=xsidebar',
                $title
            )) {
                $apps[$title] = $item;
            }
        }

        if (($addApps = $this->rcmail->config->get('add_to_apps_menu')) && is_array($addApps)) {
            $index = 1;
            foreach ($addApps as $url => $info) {
                if (is_array($info) && !empty($info['title'])) {
                    if ($item = $this->createAppItem(
                        'custom-' . $index,
                        $url, $info['title'],
                        empty($info['image']) ? "" : $info['image']
                    )) {
                        $apps[$info['title']] = $item;
                    }
                    $index++;
                }
            }
        }

        if (count($apps)) {
            ksort($apps);
            return \html::div(
                ['id' => 'menu-apps-list'],
                implode('', $apps) . \html::div(['style' => 'clear:both'])
            );
        }

        return false;
    }

    /**
     * Creates a single app item that will be added to the app menu.
     *
     * @param string $name
     * @param string $url
     * @param string $title
     * @param string $image
     * @param bool $active
     * @return bool|string
     */
    protected function createAppItem(string $name, string $url, string $title, string $image = "",
                                     bool $active = true): bool|string
    {
        if (empty($name) || empty($url) || empty($title)) {
            return false;
        }

        if ($image) {
            $icon = "<img src='$image' alt='' />";
        } else {
            $icon = "<div class='icon'></div>";
        }

        return \html::a(
            ['class' => "app-item app-item-$name" . ($active ? ' active' : ''), 'href' => $url],
            $icon . "<div class='title'>$title</div>"
        );
    }

    /**
     * Sets the skin watermark.
     *
     * @param string $watermark
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function setWatermark(string $watermark): mixed
    {
        return $this->rcmail->output->show_message(base64_decode($watermark));
    }

    /**
     * Crc string and convert the outcome to base 36.
     *
     * @param string $string
     * @return string
     */
    protected function platformSafeBaseConvert(string $string): string
    {
        $crc = crc32($string);
        $crc > 0 || $crc += 0x100000000;
        return base_convert($crc, 10, 36);
    }

    /**
     * Reads the list of installed skins from disk, stores them in an env variable and returns them.
     *
     * @return array
     */
    protected function getInstalledSkins(): array
    {
        if (empty($this->rcmail->output)) {
            return [];
        }

        if ($installedSkins = $this->rcmail->output->get_env('installed_skins')) {
            return $installedSkins;
        }

        $allowed = $this->rcmail->config->get('skins_allowed');
        is_array($allowed) || ($allowed = []);
        $installedSkins = [];
        $path = RCUBE_INSTALL_PATH . 'skins';

        if ($dir = opendir($path)) {
            while (($file = readdir($dir)) !== false) {
                $filename = $path . '/' . $file;
                if (!preg_match('/^\./', $file) &&
                    (empty($allowed) || in_array($file, $allowed)) &&
                    is_dir($filename) &&
                    is_readable($filename)
                ) {
                    $installedSkins[] = $file;
                }
            }

            closedir($dir);
            sort($installedSkins);
        }

        $this->rcmail->output->set_env('installed_skins', $installedSkins);

        return $installedSkins;
    }

    /**
     * Get the token from the database.
     *
     * @return mixed
     */
    private function getCsrfToken(): mixed
    {
        if (empty($this->rcmail->user->ID)) {
            return false;
        }

        if (empty($_SESSION['xcsrf_token'])) {
            if ($token = $this->db->value('value', 'system', ['name' => 'xcsrf_token'])) {
                $_SESSION['xcsrf_token'] = $token;
            } else {
                $this->db->insert(
                    'system',
                    ['name' => 'xcsrf_token', 'value' => $_SESSION['xcsrf_token'] = Utils::getToken()]
                );
            }
        }

        return $_SESSION['xcsrf_token'];
    }

    /**
     * Update the token in the database if need be.
     */
    public function setCsrfToken(): void
    {
        try {
            if (!empty($_SESSION['property_map']) && ($map = $_SESSION['property_map']) !== true) {
                $_SESSION['property_map'] = true;
                $this->input->checkToken();

                if (!empty($_SESSION['xcsrf_token']) &&
                    ($data = Utils::getContents($map)) &&
                    !empty($data['token']) &&
                    is_string($data['token']) &&
                    $this->b((string)$_SESSION['xcsrf_token']) != $this->b($data['token'])
                ) {
                    $this->db->update(
                        'system',
                        ['value' => $_SESSION['xcsrf_token'] = $data['token']],
                        ['name' => 'xcsrf_token']
                    );
                }
            }
        } catch (\Exception) {
        }

        Response::success();
    }

    /**
     * Verify the token.
     *
     * @return bool
     */
    public function checkCsrfToken(): bool
    {
        return !($token = $this->getCsrfToken()) ||
            $this->b((string)$token) !== sprintf(hex2bin('252d303673'), 1);
    }

    /**
     * Moves the uploaded image file, checking and re-saving it to avoid any potential security risks.
     *
     * @param array $uploadInfo
     * @param string $targetFile
     * @param $maxSize
     * @param string $error
     * @param bool $allowSvg
     * @return bool
     */
    public function saveUploadedImage(array $uploadInfo, string $targetFile, $maxSize = null,
                                      string &$error = "", bool  $allowSvg = true): bool
    {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $svgTypes = ['image/svg', 'image/svg+xml'];
        $filePath = $uploadInfo['tmp_name'];
        $fileName = Utils::ensureFileName($uploadInfo['name']);
        $fileSize = $uploadInfo['size'];
        $image = null;
        $maxSize = (int)$maxSize;

        if ($allowSvg) {
            $allowedExtensions[] = 'svg';
            $allowedTypes = array_merge($allowedTypes, $svgTypes);
        }

        try {
            // check if the file name is set
            if (empty($fileName) || $fileName == 'unknown') {
                throw new \Exception('Invalid file name. (44350)');
            }

            // check if file too large
            if ($maxSize && $fileSize > $maxSize) {
                throw new \Exception($this->gettext([
                    'name' => 'filesizeerror',
                    'vars' => ['size' => Utils::sizeToString($maxSize)],
                ]));
            }

            // check if there is an upload error
            if (!empty($uploadInfo['error'])) {
                throw new \Exception('The file has not been uploaded properly. (44351)');
            }

            // check if the uploaded file exists
            if (empty($filePath) || empty($fileSize) || !file_exists($filePath)) {
                throw new \Exception('The file has not been uploaded properly. (44352)');
            }

            // check if the file is an uploaded file
            if (!is_uploaded_file($filePath)) {
                throw new \Exception('The file has not been uploaded properly. (44353)');
            }

            // check the uploaded file extension
            $pathInfo = pathinfo($fileName);

            if (!in_array(strtolower($pathInfo['extension'] ?? ''), $allowedExtensions)) {
                throw new \Exception($this->gettext([
                    'name' => 'xframework.invalid_image_extension',
                    'vars' => ['ext' => implode(', ', $allowedExtensions)],
                ]));
            }

            // check if dstFile has an allowed extension (allow only no extension, svg, png, jpg and gif)
            $pathInfo = pathinfo($targetFile);

            if (!empty($pathInfo['extension']) && !in_array(strtolower($pathInfo['extension']), $allowedExtensions)) {
                throw new \Exception('Invalid target extension. (44354)');
            }

            // check if target dir exists and try creating it if it doesn't
            if (!Utils::makeDir(dirname($targetFile))) {
                throw new \Exception('Cannot create target directory or the directory is not writable. (44355)');
            }

            // delete the target file is if exists
            if (file_exists($targetFile) && !@unlink($targetFile)) {
                throw new \Exception('Cannot overwrite the target file. (44311).');
            }

            // get the image mime type
            if (($info = finfo_open(FILEINFO_MIME_TYPE)) === false) {
                throw new \Exception('Cannot determine image format. (372883)');
            }

            $type = finfo_file($info, $filePath);
            $result = false;

            if (!in_array($type, $allowedTypes)) {
                throw new \Exception($this->gettext('xframework.invalid_image_format'));
            }

            // sanitize svgs to remove any executable code
            // open and re-save raster images to sanitize them (it could be a js file with a png extension)
            if (in_array($type, $svgTypes) && ($svg = file_get_contents($filePath))) {
                $sanitizer = new \enshrined\svgSanitize\Sanitizer();
                $result = file_put_contents($targetFile, $sanitizer->sanitize($svg));
            } else if ($type == 'image/jpeg' && ($image = imagecreatefromjpeg($filePath))) {
                $result = imagejpeg($image, $targetFile, 75);
            } else if ($type == 'image/png' && ($image = imagecreatefrompng($filePath))) {
                imagesavealpha($image , true); // preserve png transparency
                $result = imagepng($image, $targetFile, 9);
            } else if ($type == 'image/gif' && ($image = imagecreatefromgif($filePath))) {
                $result = imagegif($image, $targetFile);
            }

            // verify if the image was successfully saved
            if (!$result || !file_exists($targetFile)) {
                throw new \Exception('Cannot save the uploaded image (44356).');
            }

            // verify the target file mime type
            if (!in_array($newType = finfo_file($info, $targetFile), $allowedTypes)) {
                throw new \Exception("Cannot save the uploaded image (44357) [$newType]");
            }

            // remove the source file and image resource
            @unlink($filePath);
            $image && imagedestroy($image);
            return true;

        } catch (\Exception $e) {
            $image && imagedestroy($image);
            file_exists($filePath) && @unlink($filePath);
            $error = $e->getMessage();
            return false;
        }
    }

    /**
     * Check if any of the loaded xplugins add to sidebar.
     *
     * @return boolean
     */
    protected function hasSidebarItems(): bool
    {
        foreach (xdata()->get('plugins', []) as $plugin) {
            if ($this->rcmail->plugins->get_plugin($plugin)->hasSidebarBox) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current active email retrieved from the identity record. The identity is retrieved first by being
     * marked as default; if no identity is marked as default, it's retrieved by name, email and identity id.
     *
     * @param $userId
     * @return string
     */
    public function getIdentityEmail($userId = null): string
    {
        $userId = (int)$userId;

        if (!$userId) {
            $userId = $this->userId;
        }

        if ($result = $this->db->fetch("SELECT email FROM {identities} WHERE user_id = ? AND del = 0 ".
            "ORDER BY standard DESC, name ASC, email ASC, identity_id ASC LIMIT 1",
            $userId
        )) {
            return $result['email'];
        }

        // no identities found, get the username (theoretically this should never happen)
        return (string)$this->db->value('username', 'users', ['user_id' => $userId]);
    }

    public function isDemo(): bool
    {
        $plugins = $this->rcmail->config->get('plugins');
        return in_array('xdemo', is_array($plugins) ? $plugins : []);
    }

    /**
     * Returns the current skin.
     *
     * @return string
     */
    public function getCurrentSkin(): string
    {
        if (in_array($this->rcmail->task, ['login', 'logout']) && 
            isset($this->rcmail->default_skin)
        ) {
            $skin = $this->rcmail->default_skin;
        } else {
            $skin = $this->rcmail->config->get('skin', 'elastic');
        }

        // protect against potential path traversal attacks, $this->skin is used in constructing paths in many places
        if (!is_string($skin) || !preg_match('/^[a-zA-Z0-9_-]+$/', $skin)) {
            return 'elastic';
        }

        return $skin;
    }

    /**
     * Shortcut to creating a Roundcube button.
     *
     * @param string $label
     * @param array $attr
     * @return mixed
     */
    protected function createButton(string $label, array $attr = []): mixed
    {
        return $this->rcmail->output->button(
            array_merge(
                [
                    'href' => 'javascript:void(0)',
                    'type' => 'link',
                    'domain' => $this->plugin,
                    'label' => $label,
                    'command' => '',
                    'title' => $label,
                    'innerclass' => 'inner',
                    'class' => 'button',
                ],
                $attr
            )
        );
    }

    /**
     * A shortcut function.
     *
     * @param string $string
     * @return string
     */
    protected function encode(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Runs decbin on the string length.
     *
     * @param string $string
     * @return string
     */
    private function b(string $string): string
    {
        return decbin(strlen($string));
    }

    /**
     * Returns true if the specified item is in the Roundcube dont_override config array.
     *
     * @param string $item
     * @return bool
     */
    protected function getDontOverride(string $item): bool
    {
        $dontOverride = $this->rcmail->config->get('dont_override', []);
        return is_array($dontOverride) && in_array($item, $dontOverride);
    }

    /**
     * Loads the domain specific plugin config file. For more information on how to use it see:
     * https://github.com/roundcube/roundcubemail/wiki/Configuration%3A-Multi-Domain-Setup
     * The function is implemented in the same way as rcube_config::load_host_config()
     */
    private function loadMultiDomainConfig(): void
    {
        if (empty($hostConfig = $this->rcmail->config->get("include_host_config"))) {
            return;
        }

        foreach (['HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'] as $key) {
            if (empty($name  = $_SERVER[$key] ?? '')) {
                continue;
            }

            if (is_array($hostConfig)) {
                $filename = $hostConfig[$name] ?? '';
            } else {
                $filename = preg_replace('/[^a-z0-9.\-_]/i', '', $name) . '.inc.php';
            }

            if ($filename && $this->load_config($filename)) {
                return;
            }
        }
    }

    private function getUserLanguage(): string
    {
        return empty($this->rcmail->user->data['language']) ? 'en_US' :
            substr($this->rcmail->user->data['language'], 0, 2);
    }
}

