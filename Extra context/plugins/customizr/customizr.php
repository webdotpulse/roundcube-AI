<?php

/**
 * Custom styling plugin
 *
 * Displays a custom page where usually the watermark image is shown,
 * allows custom logo, favicon, stylesheets and inline CSS rules,
 * configurable either by config.inc.php or in the webmail Settings interface.
 *
 * Configuration options (to be added to main config.inc.php):
 *
 *   // section under Settings > Preferences ('customizr' or 'general')
 *   $config['customizr_settings_section'] = 'customizr';
 *
 *   // define a custom watermark image (relative or absolute URL)
 *   $config['custom_watermark_image'] = './skins/custom_watermark.png';
 *
 *   // define a custom URI to be displayed instead of the empty watermark page
 *   $config['custom_watermark_uri'] = '';
 *
 *   // define a custom favicon image (empty string to remove it)
 *   $config['custom_favicon'] = './skins/custom_favicon.ico';
 *
 *   // define a custom logo image
 *   $config['custom_logo'] = './skins/custom_logo.svg';
 *
 *   // defines a custom CSS file which is added to every page
 *   $config['custom_stylesheet'] = './skins/custom_stylez.css';
 *
 *   // defines custom CSS rules which are injected into every page
 *   $config['custom_css'] = '';
 *
 *
 * @author Thomas Bruederli <thomas@roundcube.net>
 * @license GNU GPLv3+
 */
class customizr extends rcube_plugin
{
    public $noajax = true;
    public $task = '?(?!logout).*';

    private $rcmail;
    private $custom_css;
    private $custom_css_inline;
    private $custom_favicon;
    private $custom_logo;
    private $watermark_uri;
    private $watermark_image;
    private $settings_section;

    /**
     * Initialize the plugin
     */
    public function init()
    {
        $this->rcmail = rcube::get_instance();
        $this->load_config();

        $this->settings_section = $this->rcmail->config->get('customizr_settings_section', 'customizr');
        $this->custom_css = $this->rcmail->config->get('custom_stylesheet');
        $this->custom_css_inline = $this->rcmail->config->get('custom_css');
        $this->custom_favicon = $this->rcmail->config->get('custom_favicon', null);
        $this->custom_logo = $this->rcmail->config->get('custom_logo');
        $this->watermark_uri = $this->rcmail->config->get('custom_watermark_uri');
        $this->watermark_image = $this->rcmail->config->get('custom_watermark_image');

        // Apply custom logo to core Roundcube config if set
        if (!empty($this->custom_logo)) {
            $this->rcmail->config->set('skin_logo', $this->custom_logo);
        }

        // Register settings hooks if on settings task
        if ($this->rcmail->task == 'settings') {
            if ($this->settings_section === 'customizr') {
                $this->add_hook('preferences_sections_list', array($this, 'preferences_sections_list'));
            }
            $this->add_hook('preferences_list', array($this, 'preferences_list'));
            $this->add_hook('preferences_save', array($this, 'preferences_save'));
        }

        // Hook render_page if any customizations are active
        if (!empty($this->custom_css)
            || !empty($this->custom_css_inline)
            || !is_null($this->custom_favicon)
            || !empty($this->custom_logo)
            || !empty($this->watermark_uri)
            || !empty($this->watermark_image)
        ) {
            $this->add_hook('render_page', array($this, 'render_page'));
            $this->register_action('plugin.watermark', array($this, 'watermark_page'));
        }
    }

    /**
     * Handler for the 'preferences_sections_list' plugin hook.
     * Adds "Custom Appearance" section into Preferences sections list.
     */
    public function preferences_sections_list($args)
    {
        $this->add_texts('localization/');
        $dont_override = (array) $this->rcmail->config->get('dont_override', []);
        $keys = [
            'custom_watermark_image',
            'custom_watermark_uri',
            'custom_favicon',
            'custom_logo',
            'custom_stylesheet',
            'custom_css',
        ];

        // Only register section if at least one option is not locked via dont_override
        if (count(array_diff($keys, $dont_override)) > 0) {
            $args['list']['customizr'] = [
                'id' => 'customizr',
                'section' => $this->gettext('customizr'),
            ];
        }

        return $args;
    }

    /**
     * Handler for the 'preferences_list' plugin hook.
     * Renders customization options into settings form.
     */
    public function preferences_list($args)
    {
        if ($args['section'] !== $this->settings_section) {
            return $args;
        }

        $this->add_texts('localization/');
        $dont_override = (array) $this->rcmail->config->get('dont_override', []);

        // Roundcube checks if the section has options when generating sections menu
        if (!$args['current']) {
            $args['blocks']['customizr']['content'] = true;
            return $args;
        }

        $args['blocks']['customizr']['name'] = $this->gettext('customizr');

        // 1. Watermark Image URL
        if (!in_array('custom_watermark_image', $dont_override)) {
            $field_id = 'rcmfd_custom_watermark_image';
            $input = new html_inputfield([
                'name' => '_custom_watermark_image',
                'id' => $field_id,
                'size' => 50,
                'class' => 'form-control',
            ]);
            $value = $this->rcmail->config->get('custom_watermark_image', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_watermark_image_desc')));
            $args['blocks']['customizr']['options']['custom_watermark_image'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_watermark_image'))),
                'content' => $input->show($value) . $desc,
            ];
        }

        // 2. Watermark Page URI
        if (!in_array('custom_watermark_uri', $dont_override)) {
            $field_id = 'rcmfd_custom_watermark_uri';
            $input = new html_inputfield([
                'name' => '_custom_watermark_uri',
                'id' => $field_id,
                'size' => 50,
                'class' => 'form-control',
            ]);
            $value = $this->rcmail->config->get('custom_watermark_uri', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_watermark_uri_desc')));
            $args['blocks']['customizr']['options']['custom_watermark_uri'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_watermark_uri'))),
                'content' => $input->show($value) . $desc,
            ];
        }

        // 3. Favicon URL
        if (!in_array('custom_favicon', $dont_override)) {
            $field_id = 'rcmfd_custom_favicon';
            $input = new html_inputfield([
                'name' => '_custom_favicon',
                'id' => $field_id,
                'size' => 50,
                'class' => 'form-control',
            ]);
            $value = $this->rcmail->config->get('custom_favicon', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_favicon_desc')));
            $args['blocks']['customizr']['options']['custom_favicon'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_favicon'))),
                'content' => $input->show($value) . $desc,
            ];
        }

        // 4. Logo URL
        if (!in_array('custom_logo', $dont_override)) {
            $field_id = 'rcmfd_custom_logo';
            $input = new html_inputfield([
                'name' => '_custom_logo',
                'id' => $field_id,
                'size' => 50,
                'class' => 'form-control',
            ]);
            $value = $this->rcmail->config->get('custom_logo', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_logo_desc')));
            $args['blocks']['customizr']['options']['custom_logo'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_logo'))),
                'content' => $input->show($value) . $desc,
            ];
        }

        // 5. Custom Stylesheet URL
        if (!in_array('custom_stylesheet', $dont_override)) {
            $field_id = 'rcmfd_custom_stylesheet';
            $input = new html_inputfield([
                'name' => '_custom_stylesheet',
                'id' => $field_id,
                'size' => 50,
                'class' => 'form-control',
            ]);
            $value = $this->rcmail->config->get('custom_stylesheet', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_stylesheet_desc')));
            $args['blocks']['customizr']['options']['custom_stylesheet'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_stylesheet'))),
                'content' => $input->show($value) . $desc,
            ];
        }

        // 6. Custom Inline CSS
        if (!in_array('custom_css', $dont_override)) {
            $field_id = 'rcmfd_custom_css';
            $textarea = new html_textarea([
                'name' => '_custom_css',
                'id' => $field_id,
                'cols' => 60,
                'rows' => 6,
                'class' => 'form-control font-monospace',
                'style' => 'font-family: monospace; font-size: 0.9em;',
            ]);
            $value = $this->rcmail->config->get('custom_css', '');
            $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($this->gettext('custom_css_desc')));
            $args['blocks']['customizr']['options']['custom_css'] = [
                'title' => html::label($field_id, rcube::Q($this->gettext('custom_css'))),
                'content' => $textarea->show($value) . $desc,
            ];
        }

        return $args;
    }

    /**
     * Handler for the 'preferences_save' plugin hook.
     * Sanitizes and saves user settings into user preferences.
     */
    public function preferences_save($args)
    {
        if ($args['section'] !== $this->settings_section) {
            return $args;
        }

        $dont_override = (array) $this->rcmail->config->get('dont_override', []);

        $text_fields = [
            'custom_watermark_image',
            'custom_watermark_uri',
            'custom_favicon',
            'custom_logo',
            'custom_stylesheet',
        ];

        foreach ($text_fields as $field) {
            if (!in_array($field, $dont_override)) {
                $val = trim((string) rcube_utils::get_input_value('_' . $field, rcube_utils::INPUT_POST));
                $args['prefs'][$field] = $val;
            }
        }

        if (!in_array('custom_css', $dont_override)) {
            // Allow raw CSS characters without entity encoding
            $css_val = trim((string) rcube_utils::get_input_value('_custom_css', rcube_utils::INPUT_POST, true));
            $args['prefs']['custom_css'] = $css_val;
        }

        return $args;
    }

    /**
     * Handler for the 'render_page' plugin hook
     */
    public function render_page($args)
    {
        // replace static links to <skin>/watermark.html and set blankpage env
        if (!empty($this->watermark_uri) || !empty($this->watermark_image)) {
            $url = $this->watermark_uri ?: $this->rcmail->url('plugin.watermark');
            if (strpos($args['content'], 'watermark.html') !== false) {
                $args['content'] = preg_replace('!(src)="([^"]+/watermark.html)"!', '\\1="' . $url . '"', $args['content']);
            }
            $this->rcmail->output->set_env('blankpage', $url);
        }

        // replace or inject favicon
        if (!is_null($this->custom_favicon)) {
            $favicon = !empty($this->custom_favicon) ?
                html::tag('link', array('rel' => 'shortcut icon', 'href' => $this->custom_favicon)) :
                '';

            $args['content'] = preg_replace('!<link\s[^>]*rel="(shortcut )?icon"[^>]*>!i', $favicon, $args['content'], -1, $count);

            // append favicon if not found in template
            if (!$count && $favicon) {
                $args['content'] = preg_replace('!(</head>)!i', $favicon . "\n\\1", $args['content']);
            }
        }

        // replace logo image in template if custom logo is set
        if (!empty($this->custom_logo)) {
            $args['content'] = preg_replace(
                '!(<img\b[^>]*\bid="logo"[^>]*\bsrc=)["\'][^"\']*["\']!i',
                '${1}"' . htmlspecialchars($this->custom_logo, ENT_QUOTES) . '"',
                $args['content']
            );
        }

        // append custom stylesheet file
        if (!empty($this->custom_css)) {
            $this->rcmail->output->include_css($this->custom_css);
        }

        // inject custom inline CSS rules before </head>
        if (!empty($this->custom_css_inline)) {
            $css_tag = html::tag('style', array('type' => 'text/css'), "\n" . $this->custom_css_inline . "\n");
            $args['content'] = preg_replace('!(</head>)!i', $css_tag . "\n\\1", $args['content']);
        }

        return $args;
    }

    /**
     * Handler for plugin.watermark request actions
     */
    public function watermark_page()
    {
        // Search and replace watermark background image if template exists
        if ($templ = $this->rcmail->output->get_skin_file('/watermark.html')) {
            echo preg_replace('!url\(.+watermark.+\)!U', "url('$this->watermark_image')", file_get_contents($templ));
        } elseif (!empty($this->watermark_image)) {
            // Fallback for modern skins without watermark.html (e.g. Elastic)
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                . 'body { margin:0; height:100vh; display:flex; align-items:center; justify-content:center; background-color:transparent; }'
                . 'img { max-width:80%; max-height:80%; opacity:0.18; }'
                . '</style></head><body>'
                . '<img src="' . htmlspecialchars($this->watermark_image, ENT_QUOTES) . '" alt="" />'
                . '</body></html>';
        }
        exit;
    }
}
