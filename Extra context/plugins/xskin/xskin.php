<?php
/**
 * Roundcube Plus Skin plugin.
 *
 * Copyright 2019, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/../xframework/common/Plugin.php';

use XFramework\Utils;
use XFramework\Pref;

class xskin extends XFramework\Plugin
{
    protected bool $settings = false;
    private string $lookAndFeelUrl = '?_task=settings&_action=preferences&_section=xskin';
    protected array $fonts = [
        'arial' => 'Arial',
        'courier' => 'Courier',
        'merienda' => 'Merienda',
        'montserrat' => 'Montserrat',
        'noto_sans' => 'Noto Sans',
        'quattrocento' => 'Quattrocento',
        'sarala' => 'Sarala',
        'roboto' => 'Roboto',
        'times' => 'Times',
        'ubuntu' => 'Ubuntu',
    ];
    protected array $fontSizes = ['xs', 's', 'n', 'l', 'xl'];
    protected array $icons = ['solid', 'traditional', 'outlined', 'material', 'cartoon'];
    protected array $colors = [];
    protected array $configSchema = [
        'preview_branding' => ['type' => 'string', 'default' => ''],
        'overwrite_css' => ['type' => 'string', 'default' => ''],
        'disable_menu_skins' => ['type' => 'bool', 'default' => false],
        'disable_menu_languages' => ['type' => 'bool', 'default' => false],
        'disable_mobile_interface' => ['type' => 'bool', 'default' => false],
        'disable_plugins_on_mobile' => ['type' => 'array', 'default' => []],
        'allow_mobile_html_composing' => ['type' => 'bool', 'default' => true],
        'disable_menu_colors' => ['type' => 'bool', 'default' => false],
        'disable_remote_skin_fonts' => ['type' => 'bool', 'default' => false],
        'fix_plugins' => ['type' => 'array', 'default' => []],
        'custom_sidebar_bg' => ['type' => 'string', 'default' => ''],
        'custom_topbar_bg' => ['type' => 'string', 'default' => ''],
        'custom_compose_bg' => ['type' => 'string', 'default' => ''],
        'custom_btn_primary_bg' => ['type' => 'string', 'default' => ''],
        'custom_btn_secondary_bg' => ['type' => 'string', 'default' => ''],
        'custom_btn_radius' => ['type' => 'string', 'default' => ''],
    ];

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        $skin = $this->rcmail->config->get('skin');
        if (($xresell = $this->api->get_plugin('xresell')) &&
            array_key_exists($skin, $this->skins) &&
            !$xresell->enabled($skin)
        ) {
            return;
        }

        $this->colors = $this->getSkinColors();

        // add skin-specific config options to config schema
        $skin = $this->skin;
        $this->configSchema = array_merge(
            $this->configSchema,
            [
                "xskin_icons_$skin" => ['type' => 'string', 'options' => $this->icons, 'default' => 'outlined'],
                "xskin_list_icons_$skin" => ['type' => 'bool', 'default' => true],
                "xskin_button_icons_$skin" => ['type' => 'bool', 'default' => false],
                "xskin_font_family_$skin" => ['type' => 'string', 'options' => array_keys($this->fonts), 'default' => 'arial'],
                "xskin_font_size_$skin" => ['type' => 'string', 'options' => $this->fontSizes, 'default' => 'n'],
                "xskin_thick_font_$skin" => ['type' => 'bool', 'default' => false],
                "xskin_color_$skin" => ['type' => 'string', 'options' => $this->colors, 'default' => ''],
            ]
        );

        // fix plugin config according to the specified schema
        $this->config->validate($this->configSchema);

        $this->addSkinInterfaceMenuItem();
        $this->addLanguageInterfaceMenuItem();

        // include scripts (doing it here so the quick skin change works in elastic/larry)
        $this->includeAsset('assets/scripts/xskin.min.js');

        // return if we're not running a Roundcube Plus skin (but add custom css and colors so it applies to all skins)
        if (!$this->rcpSkin) {
            $this->includeCustomCss();
            $this->add_hook('render_page', [$this, $this->elastic ? 'elasticRenderPage' : 'larryRenderPage']);
            if ($this->rcmail->task == 'settings') {
                $this->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
                $this->add_hook('preferences_list', [$this, 'preferencesList']);
                $this->add_hook('preferences_save', [$this, 'preferencesSave']);
            }
            return;
        }

        // add hooks
        $this->add_hook('startup', [$this, 'startup']);
        $this->add_hook('config_get', [$this, $this->elastic ? 'elasticGetConfig' : 'larryGetConfig']);
        $this->add_hook('render_page', [$this, $this->elastic ? 'elasticRenderPage' : 'larryRenderPage']);

        if ($this->rcmail->task == 'settings') {
            $this->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
            $this->add_hook('preferences_list', [$this, 'preferencesList']);
            $this->add_hook('preferences_save', [$this, 'preferencesSave']);
        }

        // include assets
        $this->includeAsset('assets/scripts/xskin.min.js');
        $this->includeAsset('assets/styles/styles.css');
        $this->includeSkinConfig();

        if ($this->skinBase == 'larry') {
            $this->larrySetSkin();
            $this->addDisableMobileInterfaceMenuItem();

            if ($this->rcmail->output->get_env('xskin_type') == 'mobile') {
                $this->includeAsset('assets/scripts/hammer.min.js');
                $this->includeAsset('assets/scripts/jquery.hammer.js');
                $this->includeAsset('assets/scripts/larry_mobile.min.js');
                $this->includeAsset('assets/styles/larry_mobile.css');
                $this->includeAsset("../../skins/$this->skin/assets/styles/mobile.css");
            } else {
                $this->includeAsset('assets/scripts/larry_desktop.min.js');
                $this->includeAsset('assets/styles/larry_desktop.css');
                $this->includeAsset("../../skins/$this->skin/assets/styles/desktop.css");
            }
        } else {
            $this->includeAsset("../../skins/$this->skin/assets/styles/styles.css");
            $this->includeAsset("../../skins/$this->skin/assets/scripts/scripts.min.js");
        }

        // removed the cairo font (included with previous versions) because of line spacing issues - fix any old font settings
        if ($this->rcmail->config->get("xskin_font_family_$this->skin") == 'cairo') {
            $this->rcmail->config->set("xskin_font_family_$this->skin", 'noto-sans');
        }

        // if remote assets are disabled, set the font to roboto (loaded from elastic) and don't load fonts from google
        if ($this->rcmail->config->get('disable_remote_skin_fonts')) {
            // set these to a value that doesn't exist in _options.scss so it won't set the font
            $this->rcmail->config->set('xskin_font_family', 'inherited-local');
            $this->rcmail->config->set("xskin_font_family_$this->skin", 'inherited-local');
        } else {
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Roboto&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Noto+Sans&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Ubuntu&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Montserrat+Alternates&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Sarala&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Quattrocento&display=block');
            $this->include_stylesheet('https://fonts.googleapis.com/css2?family=Merienda&display=block');
        }

        $this->ensureSkinLogo();
        $this->setPreviewBranding();
        $this->includeCustomCss();
    }

    public function startup(): void
    {
        if ($this->elastic) {
            // add labels to env (for creating the mobile interface in js)
            $this->rcmail->output->add_label('login');
        } else {
            // litecube is the only skin not using font icons in desktop; but it does use it on mobile
            if ($this->skin == 'litecube' && $this->rcmail->output->get_env('xmobile')) {
                $this->rcmail->config->set('xlarry_font_icons', true);
            }

            // add larry-based classes to body
            $bodyClasses = ['x' . $this->rcmail->output->get_env('xskin_type')];
            $this->rcmail->config->get('xlarry_font_icons') && ($bodyClasses[] = 'xlarry-font-icons');
            $this->rcmail->config->get('xlarry_square_ui') && ($bodyClasses[] = 'xlarry-square-ui');
            $this->rcmail->config->get('xlarry_light_ui') && ($bodyClasses[] = 'xlarry-light-ui');
            $this->rcmail->task == 'logout' && ($bodyClasses[] = 'login-page');
            $this->addBodyClass(implode(' ', $bodyClasses));

            // add labels to env (for creating the mobile interface in js)
            $this->rcmail->output->add_label('login', 'folders', 'search', 'attachment', 'section', 'options');

            // disable composing in html on mobile devices unless config option set to allow
            if ($this->rcmail->output->get_env('xmobile') && !$this->rcmail->config->get('allow_mobile_html_composing')) {
                global $CONFIG;
                $CONFIG['htmleditor'] = false;
            }
        }

        $this->rcmail->output->set_env('rcp_skin', $this->rcpSkin);
        $this->addClasses();
    }

    /**
     * Hook retrieving config options (including user settings).
     */
    public function elasticGetConfig($arg)
    {
        // Substitute the skin name retrieved from the config file with "elastic" for the plugins that treat
        // elastic-based skins as "elastic."
        if (empty($arg['name']) || 
            $arg['name'] != 'skin' || 
            !array_key_exists(str_replace('_elastic', '', $arg['result']), $this->getSkins())
        ) {
            return $arg;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        // this is a call from the rc core, let's hope they fix this
        if (!empty($trace[3]['class']) && $trace[3]['class'] == 'jqueryui') {
            $arg['result'] = 'elastic';
        }

        // check if the calling file is in the list of plugins to fix or it's a unit test and set the skin to elastic
        $fixPlugins = $this->rcmail->config->get('fix_plugins', []);
        if (!empty($trace[3]['file']) &&
            (in_array(basename(dirname($trace[3]['file'])), $fixPlugins))
        ) {
            $arg['result'] = 'elastic';
        }

        return $arg;
    }

    function larryGetConfig($arg)
    {
        if ($this->rcmail->output->get_env('xskin_type') == 'mobile') {
            // disable unwanted plugins on mobile devices
            $disablePlugins = ['preview_pane', 'google_ads', 'threecol'];

            if (!empty($this->larryDisabledPluginsConfig) && is_array($this->larryDisabledPluginsConfig)) {
                $disablePlugins = array_merge($disablePlugins, $this->larryDisabledPluginsConfig);
            }

            foreach ($disablePlugins as $val) {
                if (isset($arg['name']) && str_contains($arg['name'], $val)) {
                    $arg['result'] = false;
                    return $arg;
                }
            }

            // set the layout to list on mobile devices so it can be displayed properly
            // IMPORTANT: we have to unset $_GET['_layout'] because on RC 1.4 setting $arg here results in adding
            // the new layout value to GET, which is then picked up and saved into the database by
            // program/steps/mail/list.inc. So the 'list' value we set here for mobile is then applied to desktop
            // as well. Unsetting GET fixes the issue.
            if (isset($arg['name']) && $arg['name'] == 'layout') {
                $arg['result'] = 'list';
                unset($_GET['_layout']);
                return $arg;
            }
        }

        // Substitute the skin name retrieved from the config file with "larry" for the plugins that treat larry-based
        // skins as "classic."
        if (empty($arg['name']) || $arg['name'] != 'skin' || !$this->isRcpSkin($arg['result'])) {
            return $arg;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        // check if the calling file is in the list of plugins to fix, or it's a unit test and set the skin to larry
        $fixPlugins = $this->rcmail->config->get('fix_plugins', []);
        if (!is_array($fixPlugins)) {
            $fixPlugins = [];
        }

        if (!empty($trace[3]['file']) &&
            (in_array(basename(dirname($trace[3]['file'])), $fixPlugins))
        ) {
            $arg['result'] = "larry";
        }

        return $arg;
    }

    /**
     * @throws Exception
     */
    public function elasticRenderPage($arg)
    {
        $this->addLoginRcpBranding($arg);
        $this->injectCustomColors($arg);
        return $arg;
    }

    /**
     * @throws Exception
     */
    public function larryRenderPage($arg)
    {
        // check if it's an error page
        if (strpos($arg['content'], 'uibox centerbox errorbox')) {
            return $arg;
        }

        $this->addLoginRcpBranding($arg);

        if ($this->rcmail->task != 'login' && $this->rcmail->task != 'logout') {
            $this->larryModifyPageHtml($arg);
        }

        $this->injectCustomColors($arg);

        return $arg;
    }

    /**
     * Injects custom background colors for sidebar, topbar, and compose button.
     */
    public function injectCustomColors(array &$arg): void
    {
        if (str_contains($arg['content'], 'id="customizr-custom-colors"') || str_contains($arg['content'], 'id="xskin-custom-colors"')) {
            return;
        }

        $sidebar = $this->rcmail->config->get('custom_sidebar_bg');
        $topbar = $this->rcmail->config->get('custom_topbar_bg');
        $compose = $this->rcmail->config->get('custom_compose_bg');
        $btn_primary = $this->rcmail->config->get('custom_btn_primary_bg');
        $btn_secondary = $this->rcmail->config->get('custom_btn_secondary_bg');
        $btn_radius = $this->rcmail->config->get('custom_btn_radius');

        $color_css = '';
        if (!empty($sidebar) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $sidebar)) {
            $color_css .= "html #layout-sidebar, html.dark-mode #layout-sidebar, body #layout-sidebar, #layout-sidebar, #layout-sidebar .scroller, #xsidebar, #layout-menu, .sidebar, #folderlist-content, #mailview-left, #folderlist { background-color: " . htmlspecialchars($sidebar, ENT_QUOTES) . " !important; }\n";
        }
        if (!empty($topbar) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $topbar)) {
            $color_css .= "html #layout div > .header, html.dark-mode #layout div > .header, body #layout div > .header, #layout div > .header, #layout > .header, .header, #layout-sidebar > .header, #layout-list > .header, #layout-content > .header, #messagelist-header, #topline, #header { background-color: " . htmlspecialchars($topbar, ENT_QUOTES) . " !important; }\n";
        }
        if (!empty($compose) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $compose)) {
            $color_css .= "#compose-plus, a.button.compose, .floating-action-buttons a.button.compose, a.compose, a.button-compose, .btn.btn-compose { background-color: " . htmlspecialchars($compose, ENT_QUOTES) . " !important; }\n";
        }
        if (!empty($btn_primary) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $btn_primary)) {
            $escColor = htmlspecialchars($btn_primary, ENT_QUOTES);
            $color_css .= ":root, html, body { --md-btn-primary-bg: {$escColor} !important; }\n";
            $color_css .= ".btn-primary, button.mainaction, input[type=\"submit\"].mainaction, .formbuttons .btn-primary, .formbuttons input.mainaction, .ui-dialog .ui-dialog-buttonpane button.ui-button-primary, .btn-material-primary { background-color: {$escColor} !important; }\n";
        }
        if (!empty($btn_secondary) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $btn_secondary)) {
            $escSecColor = htmlspecialchars($btn_secondary, ENT_QUOTES);
            $color_css .= ":root, html, body { --md-btn-secondary-bg: {$escSecColor} !important; }\n";
            $color_css .= ".btn-secondary, .btn-outline-secondary, button.cancel, .formbuttons .btn-secondary, .formbuttons button.cancel, .ui-dialog .ui-dialog-buttonpane button.ui-button-secondary, .btn-material-secondary { background-color: {$escSecColor} !important; }\n";
        }
        if (!empty($btn_radius) && preg_match('/^[0-9]+px$/', $btn_radius)) {
            $escRadius = htmlspecialchars($btn_radius, ENT_QUOTES);
            $color_css .= ":root, html, body { --md-btn-radius: {$escRadius} !important; }\n";
            $color_css .= ".btn-primary, .btn-secondary, button.mainaction, .btn, .formbuttons .btn-primary, .formbuttons .btn-secondary, .btn-material-primary, .btn-material-secondary { border-radius: {$escRadius} !important; }\n";
        }

        if (!empty($color_css)) {
            $css_tag = html::tag('style', ['type' => 'text/css', 'id' => 'xskin-custom-colors'], "\n" . $color_css);
            $arg['content'] = preg_replace('!(</head>)!i', $css_tag . "\n\\1", $arg['content']);
        }
    }

    /**
     * Modifies the html of the non-login Roundcube pages.
     * Unit tested via renderPage()
     *
     * @param array $arg
     * @codeCoverageIgnore
     * @throws Exception
     */
    protected function larryModifyPageHtml(array &$arg): void
    {
        // check if it's an error page
        if (strpos($arg['content'], 'uibox centerbox errorbox')) {
            return;
        }

        // if using a desktop skin on mobile devices after clicked "use desktop skin" show a link to revert to
        // mobile skin in the top bar
        if (isset($_COOKIE['rcs_disable_mobile_skin'])) {
            $this->replace(
                '<div class="topleft">',
                '<div class="topleft">'.
                html::a(
                    [
                        'class' => 'enable-mobile-skin',
                        'href' => 'javascript:void(0)',
                        'onclick' => 'xskin.enableMobileSkin()',
                    ],
                    rcube::Q($this->rcmail->gettext('xskin.enable_mobile_skin'))
                ),
                $arg['content']
            );
        }

        // add the toolbar-bg element that is used by alpha
        $this->replace(
            '<div id="mainscreencontent',
            '<div id="toolbar-bg"></div><div id="mainscreencontent',
            $arg['content']
        );
    }

    /**
     * Adds the skin config files from <skin>/config.inc.php to the main config, if the file exists.
     */
    protected function includeSkinConfig(): void
    {
        // include the default setting values from the skin's meta.json in the config
        // values from meta.json get automatically included in the config, but at the same time they're included
        // in dontoverride, which is not good because we want admins to be able to include/exclude it from dontoverride
        // so we set the default values in meta as 'xskin_default_*' and here we translate them to 'xskin_*'
        // this way the values 'xskin_*' can be used normally in dontoverride
        foreach ($this->rcmail->config->all() as $key => $val) {
            if (str_starts_with($key, 'xskin_default')) {
                $this->rcmail->config->set('xskin' . substr($key, 13), $val);
            }
        }

        $file = RCUBE_INSTALL_PATH . "skins/$this->skin/config.inc.php";

        if (!file_exists($file)) {
            return;
        }

        $config = [];
        @include($file);

        if (is_array($config)) {
            foreach ($config as $key => $val) {
                $this->rcmail->config->set($key, $val);
            }
        }
    }

    /**
     * Sets the current skin and color and fills in the correct properties for the desktop, tablet and phone skin.
     * Larry only.
     */
    public function larrySetSkin(): void
    {
        // check if already set
        if ($this->rcmail->output->get_env('xskin')) {
            return;
        }

        if ($this->rcmail->output->get_env('xphone')) {
            $skinType = 'mobile';
        } else if ($this->rcmail->output->get_env('xtablet')) {
            $skinType = 'mobile';
        } else {
            $skinType = 'desktop';
        }

        // litecube-f doesn't support mobile, set the device to desktop to avoid errors
        // also set device to desktop if mobile interface is disabled in config
        if ($this->skin == 'litecube-f' || $this->rcmail->config->get('disable_mobile_interface')) {
            $this->setDevice(true);
            $skinType = 'desktop';
        }

        // change the skin in the environment
        if (isset($GLOBALS['OUTPUT']) && method_exists($GLOBALS['OUTPUT'], 'set_skin')) {
            $GLOBALS['OUTPUT']->set_skin($this->skin);
        }

        // if running a mobile skin, remove the apps menu before it gets added using js
        if ($skinType != 'desktop') {
            $this->setJsVar('appsMenu', '');
        }

        // sent environment variables
        $this->rcmail->output->set_env('xskin', $this->skin);
        $this->rcmail->output->set_env('xskin_type', $skinType);
        $this->rcmail->output->set_env('rcp_skin', $this->rcpSkin);
    }

    protected function addLanguageInterfaceMenuItem(): void
    {
        if ($this->getDontOverride("language") || $this->rcmail->config->get("disable_menu_languages")) {
            return;
        }

        $languages = $this->rcmail->list_languages();
        asort($languages);

        $select = new html_select([
            'onchange' => 'xskin.quickLanguageChange()',
            'class'=>'form-control',
            'name' => 'quick-language-change'
        ]);
        $select->add(array_values($languages), array_keys($languages));

        $this->addToInterfaceMenu(
            'quick-language-change',
            html::div(
                ['id' => 'quick-language-change', 'class' => 'section'],
                html::div(
                    ['class' => 'section-title'], 
                    $this->gettext('language')
                ) . $select->show($this->rcmail->user->language)
            )
        );
    }

    public function preferencesSectionsList(array $arg): array
    {
        $arg['list']['xskin'] = ['id' => 'xskin', 'section' => $this->gettext('skin_look_and_feel')];
        return $arg;
    }

    /**
     * Replaces the preference skin selection with a dialog-based selection that allows specifying separate desktop
     * table and phone skins.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesList(array $arg): array
    {
        if ($arg['section'] != 'xskin' || $this->getDontOverride("look_and_feel")) {
            return $arg;
        }

        $skin = $this->skin;

        $pref = new Pref($arg, $this->ID, 'skin_look_and_feel');



        if (!$this->getDontOverride("xskin_icons") &&
            ($this->elastic || $this->rcmail->config->get("xlarry_font_icons"))
        ) {
            $pref->select(
                "xskin_icons_$skin",
                [
                    'solid' => $this->gettext('icons_solid'),
                    'traditional' => $this->gettext('icons_traditional'),
                    'outlined' => $this->gettext('icons_outlined'),
                    'material' => $this->gettext('icons_material'),
                    'cartoon' => $this->gettext('icons_cartoon'),
                ],
                ['onchange' => "xskin.applySetting(this, 'xicons', 'html')"],
                null,
                $this->gettext('icons'),
                null,
                $this->getCurrentIcons()
            );
        }

        if (!$this->getDontOverride("xskin_list_icons") &&
            ($this->elastic || $this->rcmail->config->get("xlarry_font_icons"))
        ) {
            $pref->checkbox(
                "xskin_list_icons_$skin",
                ['onchange' => "xskin.applySetting(this, 'xlist-icons', 'body')"],
                null,
                $this->gettext('setting_list_icons'),
                null,
                $this->getCurrentListIcons(),
            );
        }

        // larry-based skins don't have icons on buttons, disabling this option for larry
        if (!$this->getDontOverride("xskin_button_icons") && $this->elastic) {
            $pref->checkbox(
                "xskin_button_icons_$skin",
                ["onchange" => "xskin.applySetting(this, 'xbutton-icons', 'body')"],
                null,
                $this->gettext('setting_button_icons'),
                null,
                $this->getCurrentButtonIcons(),
            );
        }

        // if remote assets are disabled, don't give the users the choice of a font because they load from google
        if (!$this->getDontOverride("xskin_font_family") &&
            !$this->rcmail->config->get("disable_remote_skin_fonts")
        ) {
            $pref->select(
                "xskin_font_family_$skin",
                $this->fonts,
                ["onchange" => "xskin.applySetting(this, 'xfont-family', 'html')"],
                null,
                $this->gettext('setting_font_family'),
                null,
                $this->getCurrentFontFamily()
            );
        }

        if (!$this->getDontOverride("xskin_font_size")) {
            $pref->select(
                "xskin_font_size_$skin",
                [
                    'xs' => $this->gettext('font_size_xs'),
                    's' => $this->gettext('font_size_s'),
                    'n' => $this->gettext('font_size_n'),
                    'l' => $this->gettext('font_size_l'),
                    'xl' => $this->gettext('font_size_xl'),
                ],
                ["onchange" => "xskin.applySetting(this, 'xfont-size', 'html')"],
                null,
                $this->gettext('setting_font_size'),
                null,
                $this->getCurrentFontSize()
            );
        }

        if (!$this->getDontOverride("xskin_thick_font")) {
            $pref->checkbox(
                "xskin_thick_font_$skin",
                ["onchange" => "xskin.applySetting(this, 'xthick-font', 'html')"],
                null,
                $this->gettext('setting_thick_font'),
                null,
                $this->getCurrentThickFont(),
            );
        }

        if (!$this->getDontOverride("xskin_color")) {
            $html = html::tag('input', [
                'id' => 'xcolor-input',
                'type' => 'hidden',
                'name' => "xskin_color_$skin",
                'value' => $this->getCurrentColor(),
            ]);
            foreach ($this->colors as $color) {
                $html .= html::a(
                    [
                        'class'   => 'xskin-settings-color-box',
                        'onclick' => "xskin.applySetting('#xcolor-input', 'xcolor', 'body', '" . rcube::JQ($color) . "')",
                        'style'   => "background:#$color !important",
                    ],
                    ' '
                );
            }

            $pref->html($html, 'xskin_color');
        }

        $colorFields = [
            'custom_sidebar_bg' => 'setting_custom_sidebar_bg',
            'custom_topbar_bg' => 'setting_custom_topbar_bg',
            'custom_compose_bg' => 'setting_custom_compose_bg',
            'custom_btn_primary_bg' => 'setting_custom_btn_primary_bg',
            'custom_btn_secondary_bg' => 'setting_custom_btn_secondary_bg',
        ];

        $materialSwatches = [
            '#1A73E8' => 'Blue',
            '#3F51B5' => 'Indigo',
            '#7C3AED' => 'Purple',
            '#00875A' => 'Emerald',
            '#009688' => 'Teal',
            '#F59E0B' => 'Amber',
            '#EA580C' => 'Coral',
            '#E11D48' => 'Rose',
            '#334155' => 'Slate',
        ];

        foreach ($colorFields as $field => $labelKey) {
            if (!$this->getDontOverride($field)) {
                $val = (string)$this->rcmail->config->get($field, '');
                $escapedVal = htmlspecialchars($val, ENT_QUOTES);
                $pickerVal = (!empty($val) && preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) ? $val : (($field === 'custom_btn_primary_bg') ? '#1a73e8' : (($field === 'custom_btn_secondary_bg') ? '#e8f0fe' : '#ffffff'));
                $html = html::tag('input', [
                    'type' => 'color',
                    'id' => $field . '_picker',
                    'value' => $pickerVal,
                    'class' => 'form-control form-control-color',
                    'style' => 'width: 44px; height: 38px; padding: 2px; display: inline-block; vertical-align: middle; cursor: pointer;',
                    'onchange' => "document.getElementById('{$field}').value = this.value.toUpperCase(); xskin.applyCustomColor('{$field}', this.value);",
                    'oninput' => "document.getElementById('{$field}').value = this.value.toUpperCase(); xskin.applyCustomColor('{$field}', this.value);",
                ]);
                $html .= html::tag('input', [
                    'type' => 'text',
                    'id' => $field,
                    'name' => $field,
                    'value' => $escapedVal,
                    'class' => 'form-control font-monospace',
                    'style' => 'width: 110px; display: inline-block; vertical-align: middle; margin-left: 8px; text-transform: uppercase;',
                    'placeholder' => ($field === 'custom_btn_primary_bg') ? '#1A73E8' : (($field === 'custom_btn_secondary_bg') ? '#E8F0FE' : '#RRGGBB'),
                    'pattern' => '^#[0-9A-Fa-f]{6}$',
                    'oninput' => "if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { document.getElementById('{$field}_picker').value = this.value; xskin.applyCustomColor('{$field}', this.value); } else if (this.value === '') { xskin.applyCustomColor('{$field}', ''); }",
                    'onchange' => "if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { document.getElementById('{$field}_picker').value = this.value; xskin.applyCustomColor('{$field}', this.value); } else if (this.value === '') { xskin.applyCustomColor('{$field}', ''); }",
                ]);
                $html .= html::tag('button', [
                    'type' => 'button',
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'style' => 'margin-left: 6px; vertical-align: middle;',
                    'onclick' => "document.getElementById('{$field}').value = ''; document.getElementById('{$field}_picker').value = '#ffffff'; xskin.applyCustomColor('{$field}', '');",
                ], rcube::Q($this->gettext('clear_color')));

                // Add quick Material swatches for primary button color
                if ($field === 'custom_btn_primary_bg') {
                    $swatchHtml = '<div style="margin-top: 8px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">';
                    foreach ($materialSwatches as $hex => $name) {
                        $swatchHtml .= html::tag('button', [
                            'type' => 'button',
                            'title' => $name,
                            'style' => "width: 24px; height: 24px; border-radius: 50%; background-color: {$hex}; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.3); cursor: pointer; padding: 0;",
                            'onclick' => "document.getElementById('custom_btn_primary_bg').value = '{$hex}'; document.getElementById('custom_btn_primary_bg_picker').value = '{$hex}'; xskin.applyCustomColor('custom_btn_primary_bg', '{$hex}');",
                        ], '');
                    }
                    $swatchHtml .= '</div>';
                    $html .= $swatchHtml;
                }

                $pref->html($html, $field, $this->gettext($labelKey), $this->gettext($labelKey . '_desc'));
            }
        }

        // Button corner shape / radius setting
        if (!$this->getDontOverride('custom_btn_radius')) {
            $radiusVal = (string)$this->rcmail->config->get('custom_btn_radius', '20px');
            $radiusSelect = new html_select([
                'name' => 'custom_btn_radius',
                'id' => 'custom_btn_radius',
                'class' => 'form-control',
                'style' => 'width: 180px; display: inline-block;',
                'onchange' => "xskin.applyCustomRadius(this.value);",
            ]);
            $radiusSelect->add([
                $this->gettext('btn_radius_pill'),
                $this->gettext('btn_radius_rounded'),
                $this->gettext('btn_radius_square'),
            ], [
                '20px',
                '8px',
                '4px',
            ]);
            $radiusHtml = $radiusSelect->show($radiusVal);
            $pref->html($radiusHtml, 'custom_btn_radius', $this->gettext('setting_custom_btn_radius'), $this->gettext('setting_custom_btn_radius_desc'));
        }

        // Interactive Live Button Preview
        $previewHtml = '
        <div id="material-btn-preview-card" style="padding: 16px 20px; background: #f8f9fa; border: 1px solid #e0e2ec; border-radius: 16px; margin: 12px 0 16px 0; max-width: 680px;">
            <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px; color: #535f70; text-transform: uppercase; margin-bottom: 12px;">' . rcube::Q($this->gettext('button_preview')) . '</div>
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary" id="preview-primary-btn" style="background-color: var(--md-btn-primary-bg, #1a73e8); color: #fff; border-radius: var(--md-btn-radius, 20px); border: none; padding: 8px 24px; font-weight: 500; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">Primary Action</button>
                <button type="button" class="btn btn-secondary" id="preview-secondary-btn" style="background-color: var(--md-btn-secondary-bg, #e8f0fe); color: var(--md-btn-secondary-text, #1a73e8); border-radius: var(--md-btn-radius, 20px); border: 1px solid #c4c7c5; padding: 8px 20px; font-weight: 500; cursor: pointer;">Secondary</button>
                <a class="button compose" id="preview-fab-btn" style="background-color: var(--md-btn-primary-bg, #1a73e8); color: #fff; border-radius: var(--md-btn-radius, 24px); padding: 8px 20px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 600; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-right: 6px;">edit</span> Compose
                </a>
            </div>
        </div>';
        $pref->html($previewHtml, 'button_preview', $this->gettext('button_preview'));

        $pref->html(
            html::span(['class' => 'xskin-settings-save-hint'], $this->gettext('save_hint')) .
            "<script>
            if (!window.xskin) window.xskin = {};
            xskin.applyCustomColor = function(field, color) {
                var hex = (color || '').trim();
                if (hex && !/^#[0-9A-Fa-f]{3,6}$/.test(hex)) return;
                var styleId = 'xskin-live-' + field;
                var selMap = {
                    custom_topbar_bg: 'html #layout div > .header, html.dark-mode #layout div > .header, body #layout div > .header, #layout div > .header, #layout > .header, .header, #layout-sidebar > .header, #layout-list > .header, #layout-content > .header, #messagelist-header, #topline, #header',
                    custom_sidebar_bg: 'html #layout-sidebar, html.dark-mode #layout-sidebar, body #layout-sidebar, #layout-sidebar, #layout-sidebar .scroller, #xsidebar, #layout-menu, .sidebar, #folderlist-content, #mailview-left, #folderlist',
                    custom_compose_bg: '#compose-plus, a.button.compose, .floating-action-buttons a.button.compose, a.compose, a.button-compose, .btn.btn-compose',
                    custom_btn_primary_bg: '.btn-primary, button.mainaction, input[type=\"submit\"].mainaction, .formbuttons .btn-primary, .formbuttons input.mainaction, .btn-material-primary, #preview-primary-btn, #preview-fab-btn',
                    custom_btn_secondary_bg: '.btn-secondary, .btn-outline-secondary, button.cancel, .formbuttons .btn-secondary, .formbuttons button.cancel, .btn-material-secondary, #preview-secondary-btn'
                };
                var sel = selMap[field];
                if (!sel) return;
                var cssText = '';
                if (hex) {
                    if (field === 'custom_btn_primary_bg') {
                        cssText = ':root, html, body { --md-btn-primary-bg: ' + hex + ' !important; } ' + sel + ' { background-color: ' + hex + ' !important; }';
                    } else if (field === 'custom_btn_secondary_bg') {
                        cssText = ':root, html, body { --md-btn-secondary-bg: ' + hex + ' !important; } ' + sel + ' { background-color: ' + hex + ' !important; }';
                    } else {
                        cssText = sel + ' { background-color: ' + hex + ' !important; }';
                    }
                }
                function applyToDoc(d) {
                    if (!d || !d.head) return;
                    var el = d.getElementById(styleId);
                    if (!hex) {
                        if (el) el.remove();
                        return;
                    }
                    if (!el) {
                        el = d.createElement('style');
                        el.id = styleId;
                        el.type = 'text/css';
                        d.head.appendChild(el);
                    }
                    el.textContent = cssText;
                }
                try { applyToDoc(document); } catch (e) {}
                try {
                    if (window.parent && window.parent.document && window.parent.document !== document) {
                        applyToDoc(window.parent.document);
                    }
                } catch (e) {}
                if (window.$ && $('.xskin-settings-save-hint').length) {
                    $('.xskin-settings-save-hint').fadeIn();
                }
            };
            xskin.applyCustomRadius = function(val) {
                var rad = (val || '').trim();
                var styleId = 'xskin-live-custom_btn_radius';
                var cssText = rad ? (':root, html, body { --md-btn-radius: ' + rad + ' !important; } .btn-primary, .btn-secondary, button.mainaction, #preview-primary-btn, #preview-secondary-btn, #preview-fab-btn { border-radius: ' + rad + ' !important; }') : '';
                function applyToDoc(d) {
                    if (!d || !d.head) return;
                    var el = d.getElementById(styleId);
                    if (!rad) {
                        if (el) el.remove();
                        return;
                    }
                    if (!el) {
                        el = d.createElement('style');
                        el.id = styleId;
                        el.type = 'text/css';
                        d.head.appendChild(el);
                    }
                    el.textContent = cssText;
                }
                try { applyToDoc(document); } catch (e) {}
                try {
                    if (window.parent && window.parent.document && window.parent.document !== document) {
                        applyToDoc(window.parent.document);
                    }
                } catch (e) {}
                if (window.$ && $('.xskin-settings-save-hint').length) {
                    $('.xskin-settings-save-hint').fadeIn();
                }
            };
            xskin.updateIFrameClasses();
            </script>",
            ''
        );

        return $arg;
    }

    /**
     * Saves the skin selection preferences.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesSave(array $arg): array
    {
        if ($arg['section'] !== 'xskin') {
            return $arg;
        }

        Pref::save(
            $arg, $this->configSchema,
            ["xskin_icons_$this->skin", "xskin_list_icons_$this->skin", "xskin_button_icons_$this->skin",
                "xskin_font_family_$this->skin", "xskin_font_size_$this->skin", "xskin_thick_font_$this->skin",
                "xskin_color_$this->skin", "custom_sidebar_bg", "custom_topbar_bg", "custom_compose_bg",
                "custom_btn_primary_bg", "custom_btn_secondary_bg", "custom_btn_radius"]
        );

        foreach (['custom_sidebar_bg', 'custom_topbar_bg', 'custom_compose_bg', 'custom_btn_primary_bg', 'custom_btn_secondary_bg', 'custom_btn_radius'] as $colorField) {
            $val = trim((string)\rcube_utils::get_input_value($colorField, \rcube_utils::INPUT_POST));
            if ($val === '') {
                $val = trim((string)\rcube_utils::get_input_value('_' . $colorField, \rcube_utils::INPUT_POST));
            }
            if ($colorField === 'custom_btn_radius') {
                if ($val !== '' && preg_match('/^[0-9]+px$/', $val)) {
                    $arg['prefs'][$colorField] = $val;
                } elseif ($val === '') {
                    $arg['prefs'][$colorField] = '';
                }
            } else {
                if ($val !== '' && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $val)) {
                    $arg['prefs'][$colorField] = $val;
                } elseif ($val === '') {
                    $arg['prefs'][$colorField] = '';
                }
            }
        }

        $this->addClasses();

        return $arg;
    }

    public function addSkinInterfaceMenuItem(): void
    {
        if ($this->getDontOverride('skin') || $this->rcmail->config->get('disable_menu_skins')) {
            return;
        }

        if ($html = $this->getShortcutSkinsHtml()) {
            $this->addToInterfaceMenu(
                'skin-options',
                html::div(
                    ['id' => 'xskin-options', 'class' => 'section'],
                    html::div(['class' => 'section-title'], $this->gettext('skin')) . $html
                )
            );
        }
    }

    protected function getShortcutSkinsHtml(): bool|string
    {
        if (count($this->getInstalledSkins()) <= 1 ||
            $this->getDontOverride('skin') ||
            $this->rcmail->config->get('disable_menu_skins')
        ) {
            return false;
        }

        $select = new html_select([
            'onchange' => 'xskin.quickSkinChange()', 
            'class' => 'form-control', 
            'name' => 'quick-skin-change',
        ]);
        $added = 0;

        foreach ($this->getInstalledSkins() as $installedSkin) {
            if (array_key_exists($installedSkin, $this->skins)) {
                $select->add($this->skins[$installedSkin], $installedSkin);
                $added++;
            } else if ($installedSkin == 'elastic' || $installedSkin == 'larry') {
                $select->add(ucfirst($installedSkin), $installedSkin);
                $added++;
            }
        }

        if ($added > 1) {
            if ($this->rcpSkin) {
                $lookAndFeelHtml = html::div(
                    ['id' => 'look-and-feel-shortcut'],
                    html::a(
                        ['href' => $this->lookAndFeelUrl, 'class' => 'btn btn-sm btn-success'],
                        rcube::Q($this->gettext('skin_look_and_feel_shortcut'))
                    )
                );
            } else {
                $lookAndFeelHtml = '';
            }

            return html::div(
                ['id' => 'xshortcut-skins', 'class' => 'shortcut-item'], 
                $select->show($this->skin)
            ) . $lookAndFeelHtml;
        }

        return false;
    }

    protected function getCurrentColor(): string
    {
        $default = $this->rcmail->config->get('xskin_color', '');
        $color = $this->getDontOverride('xskin_color')
            ? $default
            : $this->rcmail->config->get("xskin_color_$this->skin", $default);

        // $this->colors is pre-filtered by getSkinColors() to valid hex strings.
        // Strict in_array so "0" doesn't match "000000". strlen guards the class-name
        // output (we want exactly 6 hex chars even if Utils::isValidColor accepts #abc).
        if (!is_string($color) || strlen($color) !== 6 || !in_array($color, $this->colors, true)) {
            $color = $default;
        }

        // Final hex check protects against an invalid configured default.
        return is_string($color) && Utils::isValidColor("#$color") ? $color : '';
    }

    protected function getCurrentFontFamily(): string
    {
        $default = $this->rcmail->config->get('xskin_font_family');
        $value = $this->getDontOverride('xskin_font_family')
            ? $default
            : $this->rcmail->config->get("xskin_font_family_$this->skin", $default);

        // Allow whitelisted fonts plus "inherited-local" (set by initialize() when disable_remote_skin_fonts is on).
        $allowed = array_keys($this->fonts);
        $allowed[] = "inherited-local";

        return in_array($value, $allowed, true) ? $value : "";
    }

    protected function getCurrentFontSize(): string
    {
        $default = $this->rcmail->config->get('xskin_font_size', '');
        $value = $this->getDontOverride('xskin_font_size')
            ? $default
            : $this->rcmail->config->get("xskin_font_size_$this->skin", $default);

        return in_array($value, $this->fontSizes, true) ? $value : '';
    }

    protected function getCurrentThickFont(): bool
    {
        $value = $this->getDontOverride('xskin_thick_font')
            ? $this->rcmail->config->get('xskin_thick_font')
            : $this->rcmail->config->get(
                "xskin_thick_font_$this->skin",
                $this->rcmail->config->get('xskin_thick_font')
            );

        return (bool)$value;
    }

    protected function getCurrentIcons(): string
    {
        $default = $this->rcmail->config->get('xskin_icons', '');
        $value = $this->getDontOverride('xskin_icons')
            ? $default
            : $this->rcmail->config->get("xskin_icons_$this->skin", $default);

        return in_array($value, $this->icons, true) ? $value : '';
    }

    protected function getCurrentListIcons(): bool
    {
        $value = $this->getDontOverride('xskin_list_icons')
            ? $this->rcmail->config->get('xskin_list_icons')
            : $this->rcmail->config->get(
                "xskin_list_icons_$this->skin",
                $this->rcmail->config->get('xskin_list_icons')
            );

        return (bool)$value;
    }

    protected function getCurrentButtonIcons(): bool
    {
        $value = $this->getDontOverride('xskin_button_icons')
            ? $this->rcmail->config->get('xskin_button_icons')
            : $this->rcmail->config->get(
                "xskin_button_icons_$this->skin",
                $this->rcmail->config->get('xskin_button_icons')
            );

        return (bool)$value;
    }

    protected function addClasses(): void
    {
        // add html classes
        $classes = [
            'xfont-family-' . $this->getCurrentFontFamily(),
            'xfont-size-' . $this->getCurrentFontSize(),
            'xthick-font-' . ($this->getCurrentThickFont() ? 'yes' : 'no'),
        ];

        $this->addHtmlClass(implode(" ", $classes));

        // add body classes
        $classes = [
            "{$this->rcmail->task}-page",
            "xskin",
            "skin-$this->skin",
            'xcolor-' . $this->getCurrentColor(),
            'xlist-icons-' . ($this->getCurrentListIcons() ? 'yes' : 'no'),
            'xbutton-icons-' . ($this->getCurrentButtonIcons() ? 'yes' : 'no'),
        ];

        // add body classes from skin's meta.json
        $classes[] = $this->rcmail->config->get('xbody-classes', '');

        if ($this->rcmail->task == 'logout') {
            $classes[] = 'login-page';
        }

        $this->addBodyClass(implode(' ', $classes));

        // this needs to be added to html so the icon() scss function works properly
        $this->addHtmlClass('xicons-' . $this->getCurrentIcons());
    }

    /**
     * Adds the Roundcube Plus icon to the login page.
     *
     * @param $arg
     * @throws Exception
     */
    protected function addLoginRcpBranding(&$arg): void
    {
        if ($this->rcmail->task != 'login' && $this->rcmail->task != 'logout') {
            return;
        }

        if (!$this->rcmail->config->get('remove_vendor_branding')) {
            $this->replace(
                '</body>',
                html::a(
                    [
                        'id' => 'vendor-branding',
                        'href' => 'https://roundcubeplus.com',
                        'target' => '_blank',
                        'rel' => 'noopener',
                        'title' => 'More Roundcube skins and plugins at roundcubeplus.com',
                    ],
                    html::span([], '+')
                ).
                '</body>',
                $arg['content']
            );
        }
    }

    /**
     * Performs string replacement with error checking. If the string to search for cannot be found it exits with an
     * error message.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @param $errorNumber
     * @return int
     * @codeCoverageIgnore
     * @throws Exception
     */
    protected function replace(string $search, string $replace, string &$subject, $errorNumber = null): int
    {
        $count = 0;
        $subject = str_replace($search, $replace, $subject, $count);

        if ($errorNumber && !$count) {
            rcube::raise_error([
                'code' => 500,
                'message' => "[xskin] ERROR $errorNumber: Roundcube is not running properly or it is not compatible ".
                    "with the Roundcube Plus skin. Disable the xskin plugin in config.inc.php and refresh the page ".
                    "to check for errors.",
            ], true);
        }

        return $count;
    }

    protected function setPreviewBranding(): void
    {
        // set the preview background logo (loaded using js in [skin]/watermark.html)
        $this->rcmail->output->set_env(
            'xwatermark',
            $this->rcmail->config->get('preview_branding', '../../plugins/xskin/assets/images/watermark.png')
        );

    }

    protected function includeCustomCss(): void
    {
        // include the custom css if specified in the xskin config
        if ($overwriteCss = $this->rcmail->config->get('overwrite_css')) {
            $this->includeAsset($overwriteCss);
        }

        // include the custom css if specified in skin json
        if ($customCss = $this->rcmail->config->get('custom_css')) {
            $this->includeAsset($customCss);
        }
    }

    /**
     * Larry only.
     */
    protected function addDisableMobileInterfaceMenuItem(): void
    {
        // create the 'use mobile skin' button (added only if user switched to desktop skin on mobile)
        $skinType = $this->rcmail->output->get_env("xskin_type");

        if ($skinType == 'desktop' && isset($_COOKIE['rcs_disable_mobile_skin'])) {
            $this->addToInterfaceMenu(
                'enable-mobile-skin',
                html::div(
                    ['id' => 'enable-mobile-skin', 'class' => 'section'],
                    "<input type='button' class='button mainaction' onclick='xskin.enableMobileSkin()' value='" .
                    rcube::Q($this->rcmail->gettext('xskin.enable_mobile_skin')) . "' />"

                )
            );
        } else if ($skinType != 'desktop') {
            $this->addToInterfaceMenu(
                'disable-mobile-skin',
                html::div(
                    ['id' => 'disable-mobile-skin', 'class' => 'section'],
                    "<input type='button' class='button mainaction' onclick='xskin.disableMobileSkin()' value='" .
                    rcube::Q($this->rcmail->gettext('xskin.disable_mobile_skin')) . "' />"
                )
            );
        }
    }

    /**
     * Sets the default logo images to RC+ if they're not set up otherwise in the config.
     */
    protected function ensureSkinLogo(): void
    {
        if (empty($this->rcmail->config->get("skin_logo"))) {
            $skinLogo = [
                '*' => "skins/$this->skin/assets/images/logo_header.png",
                '[print]' => "skins/$this->skin/assets/images/logo_print.png",
            ];

            $basePath = defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH : '';
            if ($basePath && is_file($basePath . "skins/$this->skin/assets/images/logo_login.png")) {
                $skinLogo['login'] = "skins/$this->skin/assets/images/logo_login.png";
            }

            $this->rcmail->config->set('skin_logo', $skinLogo);
        }
    }

    /**
     * Gets the available skin colors (set in skin's meta.json) and validates them.
     *
     * @return array
     */
    protected function getSkinColors(): array
    {
        $colors = $this->rcmail->config->get('xskin_colors', []);
        if (!is_array($colors)) {
            return [];
        }

        $result = [];
        foreach ($colors as $color) {
            $color = trim((string)$color);
            if (!Utils::isValidColor("#$color")) {
                continue;
            }
            // expand 3-letter color codes
            if (strlen($color) === 3) {
                $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
            }
            $result[] = $color;
        }

        return $result;
    }
}