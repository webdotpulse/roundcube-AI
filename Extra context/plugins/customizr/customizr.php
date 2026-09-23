<?php

/**
 * Custom styling plugin
 *
 * Displays a custom page where usually the watermark image is shown,
 * allows custom logo (distinct for mailbox and login view), favicon,
 * watermark image with file uploads and instant live preview,
 * stylesheets and inline CSS rules, configurable either by config.inc.php
 * or in the webmail Settings interface.
 *
 * Configuration options (to be added to main config.inc.php):
 *
 *   // section under Settings > Preferences ('customizr' or 'general')
 *   $config['customizr_settings_section'] = 'customizr';
 *
 *   // define a custom watermark image (relative or absolute URL, or uploaded path)
 *   $config['custom_watermark_image'] = './skins/custom_watermark.png';
 *
 *   // define a custom URI to be displayed instead of the empty watermark page
 *   $config['custom_watermark_uri'] = '';
 *
 *   // define a custom favicon image (empty string to remove it)
 *   $config['custom_favicon'] = './skins/custom_favicon.ico';
 *
 *   // define a custom logo image for the mailbox / application view
 *   $config['custom_logo'] = './skins/custom_logo.svg';
 *
 *   // define a custom logo image specifically for the login page (empty falls back to custom_logo)
 *   $config['custom_logo_login'] = './skins/custom_logo_login.svg';
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
    public $task = 'login|mail|settings';
    protected $rcmail;
    private $custom_css;
    private $custom_css_inline;
    private $custom_favicon;
    private $custom_logo;
    private $custom_logo_login;
    private $watermark_uri;
    private $watermark_image;
    private $settings_section;
    private $custom_sidebar_bg;
    private $custom_topbar_bg;
    private $custom_compose_bg;
    private $compose_button_bg_color;
    private $compose_button_text_color;

    /**
     * Resolves an image path: if it is a local upload path (e.g. plugins/customizr/uploads/custom_...),
     * converts it to a base64 data URI so it works even when plugins/ is outside webroot or blocked.
     */
    public static function resolve_image_url(?string $url): string
    {
        if (empty($url)) {
            return '';
        }
        $url = trim($url);
        // Already a data URI or external URL
        if (str_starts_with($url, 'data:') || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        // Check if pointing to plugins/customizr/uploads/
        if (strpos($url, 'plugins/customizr/uploads/') !== false) {
            $filename = basename($url);
            $upload_dir = __DIR__ . '/uploads';
            $local_path = $upload_dir . '/' . $filename;
            if (!file_exists($local_path) && is_dir($upload_dir)) {
                $matches = glob($upload_dir . '/' . $filename . '*');
                if (!empty($matches)) {
                    $local_path = $matches[0];
                }
            }
            if (file_exists($local_path) && !is_dir($local_path)) {
                $ext = strtolower(pathinfo($local_path, PATHINFO_EXTENSION));
                $data = @file_get_contents($local_path);
                if ($data !== false) {
                    $mime = '';
                    if (function_exists('mime_content_type')) {
                        $detected = @mime_content_type($local_path);
                        if ($detected && str_starts_with($detected, 'image/')) {
                            $mime = $detected;
                        }
                    }
                    if (!$mime) {
                        $mime = 'image/' . ($ext === 'svg' ? 'svg+xml' : ($ext === 'ico' ? 'x-icon' : ($ext === 'jpg' ? 'jpeg' : ($ext ?: 'png'))));
                    }
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            }
        }

        // Check if pointing to a local skin or image asset (e.g. ./skins/watermark.png or skins/watermark.png)
        if (strpos($url, 'watermark.png') !== false || str_starts_with($url, './skins/') || str_starts_with($url, 'skins/')) {
            $rel = ltrim(preg_replace('#^\./#', '', $url), '/');
            $candidates = [
                $url,
                (defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH . '/' . $rel : ''),
                dirname(__DIR__, 2) . '/' . $rel,
                dirname(__DIR__, 3) . '/' . $rel,
                dirname(__DIR__, 4) . '/' . $rel,
                __DIR__ . '/../../skins/' . basename($url),
                __DIR__ . '/../xframework/assets/images/' . basename($url),
                __DIR__ . '/../xskin/assets/images/' . basename($url),
            ];
            foreach ($candidates as $cand) {
                if (!empty($cand) && is_file($cand) && filesize($cand) > 0) {
                    $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                    $data = @file_get_contents($cand);
                    if ($data !== false) {
                        $mime = 'image/' . ($ext === 'svg' ? 'svg+xml' : ($ext === 'ico' ? 'x-icon' : ($ext === 'jpg' ? 'jpeg' : ($ext ?: 'png'))));
                        return 'data:' . $mime . ';base64,' . base64_encode($data);
                    }
                }
            }
        }

        return $url;
    }

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
        $this->custom_logo_login = $this->rcmail->config->get('custom_logo_login');
        $this->watermark_uri = $this->rcmail->config->get('custom_watermark_uri');
        $this->watermark_image = $this->rcmail->config->get('custom_watermark_image');
        $this->custom_sidebar_bg = $this->rcmail->config->get('custom_sidebar_bg');
        $this->custom_topbar_bg = $this->rcmail->config->get('custom_topbar_bg');
        $this->custom_compose_bg = $this->rcmail->config->get('compose_button_bg_color', $this->rcmail->config->get('custom_compose_bg'));
        $this->compose_button_bg_color = $this->custom_compose_bg;
        $this->compose_button_text_color = $this->rcmail->config->get('compose_button_text_color');

        // Resolve images to data URIs if they reference local uploads
        $this->custom_logo = self::resolve_image_url($this->custom_logo);
        $this->custom_logo_login = self::resolve_image_url($this->custom_logo_login);
        $this->custom_favicon = !is_null($this->custom_favicon) ? self::resolve_image_url($this->custom_favicon) : null;
        $this->watermark_image = self::resolve_image_url($this->watermark_image);

        // Apply custom logo to core Roundcube config if set
        // Use login logo when on login task; otherwise use mailbox logo
        $is_login = ($this->rcmail->task === 'login');
        $active_logo = ($is_login && !empty($this->custom_logo_login)) ? $this->custom_logo_login : $this->custom_logo;
        if (!empty($active_logo)) {
            $this->rcmail->config->set('skin_logo', $active_logo);
        }

        // Register settings hooks if on settings task
        if ($this->rcmail->task == 'settings') {
            if ($this->settings_section === 'customizr') {
                $this->add_hook('preferences_sections_list', array($this, 'preferences_sections_list'));
            }
            $this->add_hook('preferences_list', array($this, 'preferences_list'));
            $this->add_hook('preferences_save', array($this, 'preferences_save'));
            $this->register_action('plugin.customizr_upload', array($this, 'ajax_upload'));
        }

        // Hook render_page if any customizations are active
        if (!empty($this->custom_css)
            || !empty($this->custom_css_inline)
            || !is_null($this->custom_favicon)
            || !empty($this->custom_logo)
            || !empty($this->custom_logo_login)
            || !empty($this->watermark_uri)
            || !empty($this->watermark_image)
            || !empty($this->custom_sidebar_bg)
            || !empty($this->custom_topbar_bg)
            || !empty($this->custom_compose_bg)
            || !empty($this->compose_button_bg_color)
            || !empty($this->compose_button_text_color)
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
            'custom_logo_login',
            'custom_stylesheet',
            'custom_css',
            'custom_sidebar_bg',
            'custom_topbar_bg',
            'custom_compose_bg',
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
     * Helper to render an image setting field with upload button, preview box, and clear action.
     */
    private function render_image_field($field_name, $field_id, $value, $title_label, $desc_text, $accept = 'image/*,.ico,.svg')
    {
        $preview_src = self::resolve_image_url($value);
        $has_value = !empty($value);
        $escaped_preview = htmlspecialchars((string) $preview_src, ENT_QUOTES);

        $input = new html_inputfield([
            'name' => '_' . $field_name,
            'id' => $field_id,
            'size' => 45,
            'class' => 'form-control customizr-url-input',
            'style' => 'display: inline-block; width: calc(100% - 210px); min-width: 180px; vertical-align: middle;',
            'placeholder' => './skins/... or https://...',
        ]);

        $file_input = html::tag('input', [
            'type' => 'file',
            'id' => $field_id . '_file',
            'name' => '_' . $field_name . '_file',
            'class' => 'customizr-file-input',
            'accept' => $accept,
            'style' => 'position: absolute !important; width: 0 !important; height: 0 !important; opacity: 0 !important; pointer-events: none !important; overflow: hidden !important;',
            'onchange' => "customizr_handle_file_select(this, '{$field_id}')",
        ]);

        $upload_btn = html::tag('button', [
            'type' => 'button',
            'class' => 'btn btn-secondary btn-sm customizr-upload-btn',
            'style' => 'margin-left: 6px; vertical-align: middle;',
            'onclick' => "document.getElementById('{$field_id}_file').click();",
        ], rcube::Q($this->gettext('upload_image')));

        $clear_btn = html::tag('button', [
            'type' => 'button',
            'id' => $field_id . '_clear',
            'class' => 'btn btn-outline-danger btn-sm customizr-clear-btn',
            'style' => 'margin-left: 6px; vertical-align: middle;' . ($has_value ? '' : ' display:none;'),
            'onclick' => "customizr_clear_field('{$field_id}')",
        ], rcube::Q($this->gettext('remove_image')));

        $preview_img = html::tag('img', [
            'id' => $field_id . '_preview',
            'src' => $has_value ? $escaped_preview : '',
            'alt' => rcube::Q($title_label),
            'style' => 'max-width: 220px; max-height: 70px; object-fit: contain;' . ($has_value ? '' : ' display:none;'),
        ]);

        $empty_note = html::tag('span', [
            'id' => $field_id . '_empty',
            'class' => 'text-muted small',
            'style' => 'font-style: italic;' . ($has_value ? ' display:none;' : ''),
        ], rcube::Q($this->gettext('no_image')));

        $preview_box = html::tag('div', [
            'id' => $field_id . '_preview_box',
            'class' => 'customizr-preview-box',
            'style' => 'margin-top: 8px; padding: 6px 12px; border: 1px dashed #bbb; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; min-width: 140px; min-height: 48px; max-width: 320px; background: repeating-conic-gradient(#f3f3f3 0% 25%, #ffffff 0% 50%) 50% / 16px 16px;',
        ], $preview_img . $empty_note);

        $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($desc_text));

        return html::tag('div', ['class' => 'customizr-field-wrapper'],
            html::tag('div', ['class' => 'customizr-controls-row'],
                $input->show($value) . $file_input . $upload_btn . $clear_btn
            ) .
            $preview_box .
            $desc
        );
    }

    /**
     * Helper to render a color setting field with color picker, text input, and clear button.
     */
    private function render_color_field($field_name, $field_id, $value, $title_label, $desc_text)
    {
        $value = trim((string)$value);
        $escaped_val = htmlspecialchars($value, ENT_QUOTES);
        $picker_val = (!empty($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) ? $value : '#ffffff';

        $color_picker = html::tag('input', [
            'type' => 'color',
            'id' => $field_id . '_picker',
            'value' => $picker_val,
            'class' => 'form-control form-control-color customizr-color-picker',
            'style' => 'width: 44px; height: 38px; padding: 2px; display: inline-block; vertical-align: middle; cursor: pointer;',
            'onchange' => "customizr_handle_color_picker_change(this, '{$field_id}')",
            'oninput' => "customizr_handle_color_picker_change(this, '{$field_id}')",
        ]);

        $input = new html_inputfield([
            'name' => '_' . $field_name,
            'id' => $field_id,
            'size' => 12,
            'class' => 'form-control font-monospace customizr-color-input',
            'style' => 'width: 110px; display: inline-block; vertical-align: middle; margin-left: 8px; text-transform: uppercase;',
            'placeholder' => '#RRGGBB',
            'pattern' => '^#[0-9A-Fa-f]{6}$',
            'oninput' => "customizr_handle_color_text_change(this, '{$field_id}')",
        ]);

        $clear_btn = html::tag('button', [
            'type' => 'button',
            'id' => $field_id . '_clear',
            'class' => 'btn btn-outline-secondary btn-sm customizr-color-clear-btn',
            'style' => 'margin-left: 6px; vertical-align: middle;',
            'onclick' => "customizr_clear_color('{$field_id}')",
        ], rcube::Q($this->gettext('clear_color')));

        $desc = html::tag('div', ['class' => 'form-text text-muted small mt-1'], rcube::Q($desc_text));

        return html::tag('div', ['class' => 'customizr-field-wrapper'],
            html::tag('div', ['class' => 'customizr-controls-row'],
                $color_picker . $input->show($value) . $clear_btn
            ) .
            $desc
        );
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

        // 1. Watermark Image
        if (!in_array('custom_watermark_image', $dont_override)) {
            $field_id = 'rcmfd_custom_watermark_image';
            $value = $this->rcmail->config->get('custom_watermark_image', '');
            $title = $this->gettext('custom_watermark_image');
            $desc = $this->gettext('custom_watermark_image_desc');
            $args['blocks']['customizr']['options']['custom_watermark_image'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_image_field('custom_watermark_image', $field_id, $value, $title, $desc),
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

        // 3. Favicon
        if (!in_array('custom_favicon', $dont_override)) {
            $field_id = 'rcmfd_custom_favicon';
            $value = $this->rcmail->config->get('custom_favicon', '');
            $title = $this->gettext('custom_favicon');
            $desc = $this->gettext('custom_favicon_desc');
            $args['blocks']['customizr']['options']['custom_favicon'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_image_field('custom_favicon', $field_id, $value, $title, $desc, 'image/*,.ico,.svg'),
            ];
        }

        // 4. Mailbox Logo (App View)
        if (!in_array('custom_logo', $dont_override)) {
            $field_id = 'rcmfd_custom_logo';
            $value = $this->rcmail->config->get('custom_logo', '');
            $title = $this->gettext('custom_logo');
            $desc = $this->gettext('custom_logo_desc');
            $args['blocks']['customizr']['options']['custom_logo'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_image_field('custom_logo', $field_id, $value, $title, $desc),
            ];
        }

        // 5. Login Page Logo
        if (!in_array('custom_logo_login', $dont_override)) {
            $field_id = 'rcmfd_custom_logo_login';
            $value = $this->rcmail->config->get('custom_logo_login', '');
            $title = $this->gettext('custom_logo_login');
            $desc = $this->gettext('custom_logo_login_desc');
            $args['blocks']['customizr']['options']['custom_logo_login'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_image_field('custom_logo_login', $field_id, $value, $title, $desc),
            ];
        }

        // 6. Custom Stylesheet URL
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

        // 7. Custom Inline CSS
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

        // 8. Custom Sidebar Background
        if (!in_array('custom_sidebar_bg', $dont_override)) {
            $field_id = 'rcmfd_custom_sidebar_bg';
            $value = $this->rcmail->config->get('custom_sidebar_bg', '');
            $title = $this->gettext('custom_sidebar_bg');
            $desc = $this->gettext('custom_sidebar_bg_desc');
            $args['blocks']['customizr']['options']['custom_sidebar_bg'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_color_field('custom_sidebar_bg', $field_id, $value, $title, $desc),
            ];
        }

        // 9. Custom Topbar Background
        if (!in_array('custom_topbar_bg', $dont_override)) {
            $field_id = 'rcmfd_custom_topbar_bg';
            $value = $this->rcmail->config->get('custom_topbar_bg', '');
            $title = $this->gettext('custom_topbar_bg');
            $desc = $this->gettext('custom_topbar_bg_desc');
            $args['blocks']['customizr']['options']['custom_topbar_bg'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_color_field('custom_topbar_bg', $field_id, $value, $title, $desc),
            ];
        }

        // 10. Custom Compose Button Background
        if (!in_array('compose_button_bg_color', $dont_override) && !in_array('custom_compose_bg', $dont_override)) {
            $field_id = 'rcmfd_compose_button_bg_color';
            $value = $this->rcmail->config->get('compose_button_bg_color', $this->rcmail->config->get('custom_compose_bg', ''));
            $title = $this->gettext('compose_button_bg_color') ?: $this->gettext('custom_compose_bg');
            $desc = $this->gettext('compose_button_bg_color_desc') ?: $this->gettext('custom_compose_bg_desc');
            $args['blocks']['customizr']['options']['compose_button_bg_color'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_color_field('compose_button_bg_color', $field_id, $value, $title, $desc),
            ];
            $args['blocks']['customizr']['options']['custom_compose_bg'] = &$args['blocks']['customizr']['options']['compose_button_bg_color'];
        }

        // 11. Custom Compose Button Text / Icon Color
        if (!in_array('compose_button_text_color', $dont_override)) {
            $field_id = 'rcmfd_compose_button_text_color';
            $value = $this->rcmail->config->get('compose_button_text_color', '');
            $title = $this->gettext('compose_button_text_color');
            $desc = $this->gettext('compose_button_text_color_desc');
            $args['blocks']['customizr']['options']['compose_button_text_color'] = [
                'title' => html::label($field_id, rcube::Q($title)),
                'content' => $this->render_color_field('compose_button_text_color', $field_id, $value, $title, $desc),
            ];
        }

        // Inject client-side preview, color picker sync, and upload script
        $script = <<<JS
function customizr_handle_color_picker_change(picker, textId) {
    var textInput = document.getElementById(textId);
    if (textInput && picker) {
        textInput.value = picker.value.toUpperCase();
        textInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function customizr_handle_color_text_change(textInput, textId) {
    var picker = document.getElementById(textId + '_picker');
    if (picker && textInput) {
        var val = textInput.value.trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            picker.value = val;
        }
    }
}

function customizr_clear_color(textId) {
    var textInput = document.getElementById(textId);
    var picker = document.getElementById(textId + '_picker');
    if (textInput) {
        textInput.value = '';
        textInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (picker) {
        picker.value = '#ffffff';
    }
}

function customizr_update_preview(fieldId, url) {
    var img = document.getElementById(fieldId + '_preview');
    var empty = document.getElementById(fieldId + '_empty');
    var clearBtn = document.getElementById(fieldId + '_clear');
    if (url && url.trim() !== '') {
        if (img) { img.src = url; img.style.display = 'inline-block'; }
        if (empty) { empty.style.display = 'none'; }
        if (clearBtn) { clearBtn.style.display = 'inline-block'; }
    } else {
        if (img) { img.src = ''; img.style.display = 'none'; }
        if (empty) { empty.style.display = 'inline'; }
        if (clearBtn) { clearBtn.style.display = 'none'; }
    }
}

function customizr_clear_field(fieldId) {
    var input = document.getElementById(fieldId);
    var fileInput = document.getElementById(fieldId + '_file');
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (fileInput) { fileInput.value = ''; }
    customizr_update_preview(fieldId, '');
}

function customizr_handle_file_select(fileInput, fieldId) {
    if (!fileInput.files || !fileInput.files[0]) return;
    var file = fileInput.files[0];

    // Max 2.5MB check
    if (file.size > 2.5 * 1024 * 1024) {
        alert('Selected image exceeds 2.5MB limit. Please choose a smaller image.');
        fileInput.value = '';
        return;
    }

    // 1. Instant client-side preview via FileReader
    var reader = new FileReader();
    reader.onload = function(e) {
        var dataUrl = e.target.result;
        var input = document.getElementById(fieldId);
        if (input) {
            input.value = dataUrl;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        customizr_update_preview(fieldId, dataUrl);
    };
    reader.readAsDataURL(file);

    // 2. Upload to server via AJAX if rcmail is available
    if (typeof rcmail !== 'undefined' && rcmail.url) {
        var formData = new FormData();
        formData.append('file', file);
        formData.append('_token', rcmail.env.request_token || '');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', rcmail.url('plugin.customizr_upload'));
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res && res.status === 'success' && res.url) {
                        var input = document.getElementById(fieldId);
                        if (input) {
                            input.value = res.url;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        customizr_update_preview(fieldId, res.url);
                        if (rcmail.display_message) {
                            rcmail.display_message(file.name + ' uploaded successfully', 'confirmation');
                        }
                        return;
                    }
                } catch(err) {}
            }
        };
        xhr.send(formData);
    }

    // Ensure form has multipart enctype
    var form = fileInput.closest('form');
    if (form) { form.setAttribute('enctype', 'multipart/form-data'); }
}

document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('.customizr-url-input');
    inputs.forEach(function(inp) {
        inp.addEventListener('input', function() {
            customizr_update_preview(inp.id, inp.value);
        });
        inp.addEventListener('change', function() {
            customizr_update_preview(inp.id, inp.value);
        });
    });
    var firstInp = document.querySelector('.customizr-url-input');
    if (firstInp) {
        var form = firstInp.closest('form');
        if (form) { form.setAttribute('enctype', 'multipart/form-data'); }
    }
});
JS;

        if ($this->rcmail->output && method_exists($this->rcmail->output, 'add_script')) {
            $this->rcmail->output->add_script($script, 'foot');
        }

        return $args;
    }

    /**
     * Store an uploaded file safely and return its relative URL or data URI.
     */
    public function save_uploaded_file(array $file): ?string
    {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowed_exts = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'webp'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            return null;
        }

        $data = @file_get_contents($file['tmp_name']);
        if ($data === false) {
            return null;
        }

        if ($ext === 'svg') {
            $sanitized = self::sanitize_svg($data);
            if ($sanitized === null) {
                return null;
            }
            $data = $sanitized;
        }

        $mime = 'image/' . ($ext === 'svg' ? 'svg+xml' : ($ext === 'ico' ? 'x-icon' : ($ext === 'jpg' ? 'jpeg' : $ext)));
        $dataUri = 'data:' . $mime . ';base64,' . base64_encode($data);

        // Also save to disk backup if uploads directory is writable
        $upload_dir = __DIR__ . '/uploads';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }
        $htaccess = $upload_dir . '/.htaccess';
        if (!file_exists($htaccess) && is_dir($upload_dir)) {
            @file_put_contents($htaccess, "# Disable script execution\n<FilesMatch \"\.(php|phtml|php3|php4|php5|php7|phps|inc|cgi|pl|sh)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\nOptions -Indexes\n");
        }
        $random_bytes = function_exists('random_bytes') ? bin2hex(random_bytes(4)) : substr(md5((string) mt_rand()), 0, 8);
        $filename = 'custom_' . time() . '_' . $random_bytes . '.' . $ext;
        if (is_writable($upload_dir)) {
            @file_put_contents($upload_dir . '/' . $filename, $data);
            if (!empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
                @unlink($file['tmp_name']);
            }
        }

        return $dataUri;
    }

    /**
     * Sanitize SVG markup to prevent XSS.
     */
    public static function sanitize_svg(string $content): ?string
    {
        if (!preg_match('/<svg\b[^>]*>/i', $content)) {
            return null;
        }

        $disallowed_tags = ['script', 'iframe', 'object', 'embed', 'link', 'meta', 'applet', 'foreignObject'];
        foreach ($disallowed_tags as $tag) {
            $content = preg_replace("/<{$tag}\b[^>]*>.*?<\/{$tag}>/is", '', $content);
            $content = preg_replace("/<{$tag}\b[^>]*\/?>/is", '', $content);
        }

        $content = preg_replace('/\son[a-z]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $content);
        $content = preg_replace('/\s(href|xlink:href)\s*=\s*["\']\s*(javascript|vbscript|data):[^"\']*["\']/i', '', $content);

        return $content;
    }

    /**
     * AJAX action: plugin.customizr_upload
     */
    public function ajax_upload()
    {
        if (!$this->rcmail->user) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        if (method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        } elseif (method_exists($this->rcmail, 'check_request_token') && !$this->rcmail->check_request_token(rcube_utils::INPUT_POST)) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Invalid request token']);
            exit;
        }

        $file = !empty($_FILES['file']) ? $_FILES['file'] : (!empty($_FILES['_file']) ? $_FILES['_file'] : null);
        $url = $this->save_uploaded_file($file);

        header('Content-Type: application/json; charset=UTF-8');
        if ($url) {
            echo json_encode(['status' => 'success', 'url' => $url]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed or invalid image format']);
        }
        exit;
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
            'custom_logo_login',
            'custom_stylesheet',
        ];

        foreach ($text_fields as $field) {
            if (!in_array($field, $dont_override)) {
                // Check if a direct file was uploaded for this field via multipart form
                $file_key = '_' . $field . '_file';
                if (!empty($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $saved_url = $this->save_uploaded_file($_FILES[$file_key]);
                    if ($saved_url) {
                        $args['prefs'][$field] = $saved_url;
                        continue;
                    }
                }

                $val = trim((string) rcube_utils::get_input_value('_' . $field, rcube_utils::INPUT_POST));
                $args['prefs'][$field] = self::resolve_image_url($val);
            }
        }

        if (!in_array('custom_css', $dont_override)) {
            // Allow raw CSS characters without entity encoding
            $css_val = trim((string) rcube_utils::get_input_value('_custom_css', rcube_utils::INPUT_POST, true));
            $args['prefs']['custom_css'] = $css_val;
        }

        $color_fields = [
            'custom_sidebar_bg',
            'custom_topbar_bg',
            'custom_compose_bg',
            'compose_button_bg_color',
            'compose_button_text_color',
        ];

        foreach ($color_fields as $field) {
            if (!in_array($field, $dont_override)) {
                $val = trim((string) rcube_utils::get_input_value('_' . $field, rcube_utils::INPUT_POST));
                if ($val === '') {
                    $val = trim((string) rcube_utils::get_input_value($field, rcube_utils::INPUT_POST));
                }
                if ($field === 'compose_button_bg_color' && $val === '') {
                    $legacy = trim((string) rcube_utils::get_input_value('_custom_compose_bg', rcube_utils::INPUT_POST));
                    if ($legacy === '') $legacy = trim((string) rcube_utils::get_input_value('custom_compose_bg', rcube_utils::INPUT_POST));
                    if ($legacy !== '') $val = $legacy;
                }
                if ($val !== '' && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $val)) {
                    $args['prefs'][$field] = strtoupper($val);
                    if ($field === 'compose_button_bg_color') {
                        $args['prefs']['custom_compose_bg'] = strtoupper($val);
                    }
                } else {
                    $args['prefs'][$field] = '';
                    if ($field === 'compose_button_bg_color') {
                        $args['prefs']['custom_compose_bg'] = '';
                    }
                }
            }
        }

        return $args;
    }

    /**
     * Handler for the 'render_page' plugin hook
     */
    public function render_page($args)
    {
        $active_watermark = self::resolve_image_url($this->watermark_image);
        // replace static links to <skin>/watermark.html and set blankpage / xwatermark env
        if (!empty($this->watermark_uri) || !empty($active_watermark)) {
            $skin = $this->rcmail->config->get('skin', 'gmail_plus');
            $watermark_html = "skins/{$skin}/watermark.html";
            $has_watermark_html = (defined('RCUBE_INSTALL_PATH') && is_file(RCUBE_INSTALL_PATH . '/' . $watermark_html))
                || is_file(__DIR__ . "/../../{$watermark_html}");

            if (!empty($this->watermark_uri)) {
                $url = $this->watermark_uri;
            } elseif ($has_watermark_html) {
                $url = $watermark_html;
            } else {
                $url = $this->rcmail->url('plugin.watermark');
            }

            if (strpos($args['content'], 'watermark.html') !== false && !empty($this->watermark_uri)) {
                $args['content'] = preg_replace('!(src)="([^"]+/watermark.html)"!', '\\1="' . $url . '"', $args['content']);
            }
            $this->rcmail->output->set_env('blankpage', $url);
            if (!empty($active_watermark)) {
                $this->rcmail->output->set_env('xwatermark', $active_watermark);
                $this->rcmail->config->set('preview_branding', $active_watermark);
            }
        }

        // replace or inject favicon
        $active_fav = !is_null($this->custom_favicon) ? self::resolve_image_url($this->custom_favicon) : null;
        if (empty($active_fav) && is_null($this->custom_favicon)) {
            // standard default favicon (icon.png / favicon.png)
            $skin = $this->rcmail->config->get('skin', 'gmail_plus');
            $std_fav = "skins/{$skin}/assets/images/favicon.png";
            $basePath = defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH : '';
            if (($basePath && is_file($basePath . $std_fav)) || is_file(__DIR__ . "/../../{$std_fav}")) {
                $active_fav = $std_fav;
            }
        }

        if (!empty($active_fav)) {
            $favicon = html::tag('link', array('rel' => 'shortcut icon', 'href' => $active_fav));

            $args['content'] = preg_replace('!<link\s[^>]*rel="(shortcut )?icon"[^>]*>!i', $favicon, $args['content'], -1, $count);

            // append favicon if not found in template
            if (!$count && $favicon) {
                $args['content'] = preg_replace('!(</head>)!i', $favicon . "\n\\1", $args['content']);
            }
        }

        // replace logo image in template:
        // on login page, use custom_logo_login if configured; otherwise use custom_logo
        $is_login = ($this->rcmail->task === 'login');
        $active_logo = ($is_login && !empty($this->custom_logo_login)) ? $this->custom_logo_login : $this->custom_logo;
        $active_logo = self::resolve_image_url($active_logo);
        if (empty($active_logo)) {
            // standard default: Grid Mail SVG
            $skin = $this->rcmail->config->get('skin', 'gmail_plus');
            $std_logo = "skins/{$skin}/assets/images/logo_header.svg";
            $std_login = "skins/{$skin}/assets/images/logo_login.svg";
            $std_grid = "skins/{$skin}/assets/images/grid_mail.svg";
            $basePath = defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH : '';
            if ($is_login && (($basePath && is_file($basePath . $std_login)) || is_file(__DIR__ . "/../../{$std_login}"))) {
                $active_logo = $std_login;
            } elseif (($basePath && is_file($basePath . $std_logo)) || is_file(__DIR__ . "/../../{$std_logo}")) {
                $active_logo = $std_logo;
            } elseif (($basePath && is_file($basePath . $std_grid)) || is_file(__DIR__ . "/../../{$std_grid}")) {
                $active_logo = $std_grid;
            }
        }

        if (!empty($active_logo)) {
            $escaped = htmlspecialchars($active_logo, ENT_QUOTES);
            $args['content'] = preg_replace_callback(
                '/<img\b([^>]*?)>/i',
                function ($m) use ($escaped) {
                    $tag = $m[0];
                    $attrs = $m[1];
                    if (preg_match('/\b(?:id=["\']?(?:logo|toplogo)["\']?|class=["\'][^"\']*\blogo\b[^"\']*["\'])/i', $attrs)) {
                        return preg_replace('/\bsrc=["\'][^"\']*["\']/i', 'src="' . $escaped . '"', $tag);
                    }
                    return $tag;
                },
                $args['content']
            );
        }

        // append custom stylesheet file
        if (!empty($this->custom_css)) {
            $css_url = $this->custom_css;
            $is_remote = str_starts_with($css_url, 'http://') || str_starts_with($css_url, 'https://') || str_starts_with($css_url, '//');
            $should_include = true;
            if (!$is_remote) {
                // If it's a relative path on disk (e.g. ./skins/custom.css or skins/custom.css), check if file exists on disk
                if (str_starts_with($css_url, './') || str_starts_with($css_url, 'skins/') || !str_starts_with($css_url, '/')) {
                    $rel = ltrim(preg_replace('#^\./#', '', $css_url), '/');
                    $candidates = [
                        $css_url,
                        (defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH . '/' . $rel : ''),
                        dirname(__DIR__, 2) . '/' . $rel,
                        dirname(__DIR__, 3) . '/' . $rel,
                        dirname(__DIR__, 4) . '/' . $rel,
                        __DIR__ . '/../../skins/' . basename($css_url),
                        __DIR__ . '/../xskin/assets/css/' . basename($css_url),
                    ];
                    $found = false;
                    foreach ($candidates as $c) {
                        if (!empty($c) && is_file($c) && filesize($c) > 0) {
                            $found = true;
                            break;
                        }
                    }
                    $should_include = $found;
                }
            }
            if ($should_include) {
                $this->rcmail->output->include_css($this->custom_css);
            }
        }

        // inject custom inline CSS rules before </head>
        if (!empty($this->custom_css_inline)) {
            $css_tag = html::tag('style', array('type' => 'text/css'), "\n" . $this->custom_css_inline . "\n");
            $args['content'] = preg_replace('!(</head>)!i', $css_tag . "\n\\1", $args['content']);
        }

        // inject custom colors (sidebar, topbar, compose button)
        $color_css = '';
        if (!empty($this->custom_sidebar_bg) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $this->custom_sidebar_bg)) {
            $color_css .= "html #layout-sidebar, html.dark-mode #layout-sidebar, body #layout-sidebar, #layout-sidebar, #layout-sidebar .scroller, #xsidebar, #layout-menu, .sidebar, #folderlist-content, #mailview-left, #folderlist { background-color: " . htmlspecialchars($this->custom_sidebar_bg, ENT_QUOTES) . " !important; }\n";
        }
        if (!empty($this->custom_topbar_bg) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $this->custom_topbar_bg)) {
            $color_css .= "html #layout div > .header, html.dark-mode #layout div > .header, body #layout div > .header, #layout div > .header, #layout > .header, .header, #layout-sidebar > .header, #layout-list > .header, #layout-content > .header, #messagelist-header, #topline, #header { background-color: " . htmlspecialchars($this->custom_topbar_bg, ENT_QUOTES) . " !important; }\n";
        }
        $compose_bg = !empty($this->compose_button_bg_color) ? $this->compose_button_bg_color : $this->custom_compose_bg;
        if (!empty($compose_bg) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $compose_bg)) {
            $escComposeBg = htmlspecialchars($compose_bg, ENT_QUOTES);
            $color_css .= ":root, html, body { --compose-btn-bg: {$escComposeBg} !important; }\n";
            $color_css .= "#compose-plus, #compose-plus a, a.button.compose, .floating-action-buttons a.button.compose, a.compose, a.button-compose, .btn.compose, .btn.btn-compose { background-color: {$escComposeBg} !important; border-color: {$escComposeBg} !important; }\n";
            $color_css .= "#compose-plus:hover, #compose-plus a:hover, a.button.compose:hover, .floating-action-buttons a.button.compose:hover, a.compose:hover, a.button-compose:hover, .btn.compose:hover, .btn.btn-compose:hover { filter: brightness(0.92) !important; }\n";
            $color_css .= "#compose-plus:active, #compose-plus a:active, a.button.compose:active, .floating-action-buttons a.button.compose:active, a.compose:active, a.button-compose:active, .btn.compose:active, .btn.btn-compose:active { filter: brightness(0.85) !important; }\n";
            $color_css .= "#compose-plus:focus, #compose-plus:focus-visible, a.button.compose:focus, a.button.compose:focus-visible, .floating-action-buttons a.button.compose:focus, .floating-action-buttons a.button.compose:focus-visible, .btn.compose:focus, .btn.compose:focus-visible { outline: 2px solid {$escComposeBg} !important; outline-offset: 2px !important; }\n";
        }
        if (!empty($this->compose_button_text_color) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $this->compose_button_text_color)) {
            $escComposeText = htmlspecialchars($this->compose_button_text_color, ENT_QUOTES);
            $color_css .= ":root, html, body { --compose-btn-color: {$escComposeText} !important; }\n";
            $color_css .= "#compose-plus, #compose-plus a, #compose-plus span, a.button.compose, a.button.compose span, a.button.compose .button-inner, .floating-action-buttons a.button.compose, a.compose, a.button-compose, .btn.compose, .btn.btn-compose { color: {$escComposeText} !important; }\n";
            $color_css .= "#compose-plus svg, #compose-plus a svg, a.button.compose svg, .floating-action-buttons a.button.compose svg, a.compose svg, a.button-compose svg, .btn.compose svg, .btn.btn-compose svg { fill: {$escComposeText} !important; color: {$escComposeText} !important; }\n";
        }
        if (!empty($color_css)) {
            $css_tag = html::tag('style', ['type' => 'text/css', 'id' => 'customizr-custom-colors'], "\n" . $color_css);
            $args['content'] = preg_replace('!(</head>)!i', $css_tag . "\n\\1", $args['content']);
        }

        return $args;
    }

    /**
     * Handler for plugin.watermark request actions
     */
    public function watermark_page()
    {
        $watermark = self::resolve_image_url($this->watermark_image);
        // Search and replace watermark background image if template exists
        if ($templ = $this->rcmail->output->get_skin_file('/watermark.html')) {
            echo preg_replace('!url\(.+watermark.+\)!U', "url('$watermark')", file_get_contents($templ));
        } elseif (!empty($watermark)) {
            // Fallback for modern skins without watermark.html (e.g. Elastic)
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                . 'body { margin:0; height:100vh; display:flex; align-items:center; justify-content:center; background-color:transparent; }'
                . 'img { max-width:80%; max-height:80%; opacity:0.18; }'
                . '</style></head><body>'
                . '<img src="' . htmlspecialchars($watermark, ENT_QUOTES) . '" alt="" />'
                . '</body></html>';
        }
        exit;
    }
}
