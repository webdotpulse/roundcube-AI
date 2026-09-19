<?php

/**
 * Roundcube Loader Plugin
 *
 * Gmail-style page loader and progress bar for login and application startup.
 *
 * @version 1.0.0
 * @author Webdotpulse & LifePrisma
 * @license GNU GPLv3+
 */

declare(strict_types=1);

class roundcube_loader extends rcube_plugin
{
    /** @var string Task regex matching all tasks except logout */
    public $task = '?(?!logout).*';

    /** @var rcmail */
    protected $rc;

    /**
     * Plugin initialization
     */
    public function init(): void
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        // Check global enable status
        $enabled = (bool) $this->rc->config->get('roundcube_loader_enabled', true);

        // Register user preferences hooks in Settings
        if ($this->rc->task === 'settings') {
            $this->add_hook('preferences_list', [$this, 'preferences_list']);
            $this->add_hook('preferences_save', [$this, 'preferences_save']);
        }

        if (!$enabled) {
            return;
        }

        // Include CSS and JavaScript assets
        $this->include_stylesheet('assets/roundcube_loader.css');
        $this->include_script('assets/roundcube_loader.js');

        // Hook into page rendering to inject loader markup
        $this->add_hook('render_page', [$this, 'render_page']);
    }

    /**
     * Handler for the render_page plugin hook
     */
    public function render_page(array $args): array
    {
        // Do not inject into print, framed dialogs, or non-HTML responses
        if (
            isset($_GET['_framed']) ||
            $this->rc->action === 'print' ||
            ($this->rc->output && $this->rc->output->type === 'json')
        ) {
            return $args;
        }

        // Pass client-side configuration to JavaScript environment
        $clientConfig = [
            'enabled' => (bool) $this->rc->config->get('roundcube_loader_enabled', true),
            'theme' => (string) $this->rc->config->get('roundcube_loader_theme', 'gmail'),
            'show_username' => (bool) $this->rc->config->get('roundcube_loader_show_username', true),
            'show_subtext' => (bool) $this->rc->config->get('roundcube_loader_show_subtext', true),
            'splash_on_login' => (bool) $this->rc->config->get('roundcube_loader_splash_on_login', true),
            'splash_on_startup' => (bool) $this->rc->config->get('roundcube_loader_splash_on_startup', false),
            'timeout' => (int) $this->rc->config->get('roundcube_loader_timeout', 15),
        ];

        if ($this->rc->output) {
            $this->rc->output->set_env('roundcube_loader_config', $clientConfig);
        }

        // Build the loader HTML
        $isLogin = ($this->rc->task === 'login');
        $loaderHtml = $this->buildLoaderHtml($isLogin);

        // Inject right after opening <body> tag for zero layout shift
        if (!empty($args['content']) && is_string($args['content'])) {
            if (preg_match('/(<body\b[^>]*>)/i', $args['content'])) {
                $args['content'] = preg_replace('/(<body\b[^>]*>)/i', "$1\n" . $loaderHtml, $args['content'], 1);
            } else {
                $args['content'] = $loaderHtml . "\n" . $args['content'];
            }
        }

        return $args;
    }

    /**
     * Build the loader HTML container and critical inline styles
     */
    public function buildLoaderHtml(bool $isLogin): string
    {
        $theme = (string) $this->rc->config->get('roundcube_loader_theme', 'gmail');
        $sanitizedTheme = preg_replace('/[^a-z0-9_-]/i', '', $theme);
        $themeClass = 'rc-loader-theme-' . $sanitizedTheme;

        // Overlay is initially hidden on all pages so internal transitions never flash it
        $hiddenClass = ' rc-loader-hidden';
        $initialDisplay = 'display: none;';

        // Custom bar color override if specified (defaults to modern blue)
        $customBarColor = $this->rc->config->get('roundcube_loader_bar_color', '#1a73e8');
        $colorStyle = '';
        if (!empty($customBarColor) && is_string($customBarColor)) {
            $safeColor = htmlspecialchars($customBarColor, ENT_QUOTES, 'UTF-8');
            $colorStyle = " --rc-loader-bar-color: {$safeColor};";
        }

        $logoHtml = $this->renderLogo();
        $loadingLabel = htmlspecialchars($this->gettext('loading'), ENT_QUOTES, 'UTF-8');
        $standardViewLabel = htmlspecialchars($this->gettext('standard_view'), ENT_QUOTES, 'UTF-8');
        $takingLongerLabel = htmlspecialchars($this->gettext('taking_longer'), ENT_QUOTES, 'UTF-8');
        $reloadLabel = htmlspecialchars($this->gettext('reload'), ENT_QUOTES, 'UTF-8');
        $showSubtext = (bool) $this->rc->config->get('roundcube_loader_show_subtext', true);

        $subtextHtml = '';
        if ($showSubtext) {
            $subtextHtml = <<<HTML
            <p class="rc-loader-subtext">{$standardViewLabel}</p>
HTML;
        }

        return <<<HTML
<!-- Roundcube Loader Plugin Overlay -->
<div id="rc-page-loader" class="{$themeClass}{$hiddenClass}" style="{$initialDisplay}{$colorStyle}" aria-live="polite" aria-busy="true">
  <div class="rc-loader-content">
    <div class="rc-loader-logo-wrapper">
      {$logoHtml}
    </div>
    <div class="rc-loader-progress-track">
      <div class="rc-loader-bar" style="width: 0%;"></div>
    </div>
    <div class="rc-loader-text">{$loadingLabel}</div>
    {$subtextHtml}
    <div class="rc-loader-fallback" style="display: none;">
      {$takingLongerLabel} <a href="#" class="rc-loader-reload-action">{$reloadLabel}</a>
    </div>
  </div>
</div>
<script>try{if(sessionStorage.getItem('rc_loader_active')==='1'){var _l=document.getElementById('rc-page-loader');if(_l){_l.style.display='flex';_l.classList.remove('rc-loader-hidden');}}}catch(e){}</script>
HTML;
    }

    /**
     * Render the logo element (inline SVG or image tag)
     */
    protected function renderLogo(): string
    {
        $logoType = (string) $this->rc->config->get('roundcube_loader_logo_type', 'gmail');
        $customLogo = $this->rc->config->get('roundcube_loader_custom_logo');

        if ($logoType === 'custom' && !empty($customLogo) && is_string($customLogo)) {
            $escaped = htmlspecialchars($customLogo, ENT_QUOTES, 'UTF-8');
            return '<img src="' . $escaped . '" alt="Logo" class="rc-loader-logo" />';
        }

        if ($logoType === 'roundcube') {
            $roundcubeSvg = __DIR__ . '/assets/roundcube_logo.svg';
            if (file_exists($roundcubeSvg)) {
                return file_get_contents($roundcubeSvg);
            }
        }

        // Default: Gmail envelope SVG
        $gmailSvg = __DIR__ . '/assets/gmail_logo.svg';
        if (file_exists($gmailSvg)) {
            return file_get_contents($gmailSvg);
        }

        // Fallback text mark if SVG is not readable
        return '<span class="rc-loader-logo" style="font-size: 28px; font-weight: bold; color: #1a73e8;">Webmail</span>';
    }

    /**
     * Handler for the preferences_list plugin hook
     */
    public function preferences_list(array $args): array
    {
        if ($args['section'] === 'ui') {
            $dontOverride = (array) $this->rc->config->get('dont_override', []);

            if (!in_array('roundcube_loader_enabled', $dontOverride, true)) {
                $fieldId = 'rcmfd_roundcube_loader_enabled';
                $checkbox = new html_checkbox(['name' => '_roundcube_loader_enabled', 'id' => $fieldId, 'value' => 1]);
                $enabled = (bool) $this->rc->config->get('roundcube_loader_enabled', true);

                $args['blocks']['main']['options']['roundcube_loader_enabled'] = [
                    'title'   => html::label($fieldId, rcube::Q($this->gettext('enable_loader'))),
                    'content' => $checkbox->show($enabled ? 1 : 0),
                ];
            }

            if (!in_array('roundcube_loader_theme', $dontOverride, true)) {
                $fieldId = 'rcmfd_roundcube_loader_theme';
                $select = new html_select(['name' => '_roundcube_loader_theme', 'id' => $fieldId]);
                $select->add([
                    $this->gettext('theme_gmail'),
                    $this->gettext('theme_google_gradient'),
                    $this->gettext('theme_roundcube'),
                ], ['gmail', 'google_gradient', 'roundcube']);

                $currentTheme = (string) $this->rc->config->get('roundcube_loader_theme', 'gmail');

                $args['blocks']['main']['options']['roundcube_loader_theme'] = [
                    'title'   => html::label($fieldId, rcube::Q($this->gettext('loader_theme'))),
                    'content' => $select->show($currentTheme),
                ];
            }
        }

        return $args;
    }

    /**
     * Handler for the preferences_save plugin hook
     */
    public function preferences_save(array $args): array
    {
        if ($args['section'] === 'ui') {
            $dontOverride = (array) $this->rc->config->get('dont_override', []);

            if (!in_array('roundcube_loader_enabled', $dontOverride, true)) {
                $args['prefs']['roundcube_loader_enabled'] = !empty($_POST['_roundcube_loader_enabled']);
            }

            if (!in_array('roundcube_loader_theme', $dontOverride, true) && !empty($_POST['_roundcube_loader_theme'])) {
                $theme = (string) $_POST['_roundcube_loader_theme'];
                if (in_array($theme, ['gmail', 'google_gradient', 'roundcube'], true)) {
                    $args['prefs']['roundcube_loader_theme'] = $theme;
                }
            }
        }

        return $args;
    }
}
