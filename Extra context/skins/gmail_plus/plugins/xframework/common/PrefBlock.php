<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2026, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

class PrefBlock
{
    private \rcube $rcmail;
    private string $blockId;

    public function __construct(private array &$arg, private string $pluginId, string $key, ?string $title = null)
    {
        $this->rcmail = xrc();
        $this->blockId = $this->pluginId . '-' . str_replace('_', '-', $key);
        $this->arg['blocks'][$this->blockId]['name'] = $title === null ? $this->gettext($key) : $title;
    }

    /**
     * Creates a checkbox preferences row. If a <key>_desc translation exists, it is displayed as a popup description.
     * The translated title and description can be overridden using $title and $desc.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param bool $default Default value used when the preference has not been saved.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @param string $additionalContent Custom HTML content appended at the end of the right preferences column.
     * @param array $attr Html attributes to add to the input element.
     * @return void
     */
    public function checkbox(string $key, bool $default = true, ?string $title = null, ?string $desc = null,
                             string $additionalContent = '', array $attr = []): void
    {
        if (empty($key) || $this->dontOverride($key)) {
            return;
        }

        $value = $this->rcmail->config->get($key, $default);
        $input = new \html_checkbox(array_merge(['id' => $key, 'name' => $key, 'value' => 1], $attr));

        $this->arg['blocks'][$this->blockId]['options'][$key] = [
            'title' => $this->getTitle($key, $title, $desc),
            'content' => $this->wrapContent($input->show($value ? 1 : 0) . $additionalContent),
        ];
    }

    /**
     * Creates a text input preferences row. If a <key>_desc translation exists, it is displayed as a popup description.
     * The translated title and description can be overridden using $title and $desc.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param string $default Default value used when the preference has not been saved.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @param string $additionalContent Custom HTML content appended at the end of the right preferences column.
     * @param array $attr Html attributes to add to the input element.
     * @return void
     */
    public function input(string $key, string $default = '', ?string $title = null, ?string $desc = null,
                          string $additionalContent = '', array $attr = []): void
    {
        if (empty($key) || $this->dontOverride($key)) {
            return;
        }

        $value = $this->rcmail->config->get($key, $default);
        $input = new \html_inputfield(array_merge(['id' => $key, 'name' => $key, 'type' => 'text'], $attr));

        $this->arg['blocks'][$this->blockId]['options'][$key] = [
            'title' => $this->getTitle($key, $title, $desc),
            'content' => $this->wrapContent($input->show($value) . $additionalContent),
        ];
    }

    /**
     * Creates a select box preferences row. $options can be an associative array of values and labels or a
     * comma-separated string when the values and labels are identical. Non-numeric labels are translated if a
     * corresponding plugin translation exists.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param array|string $options Available options as an associative array or comma-separated string.
     * @param string $default Default value used when the preference has not been saved.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @param string $additionalContent Custom HTML content appended at the end of the right preferences column.
     * @param array $attr Html attributes to add to the select element.
     * @return void
     */
    public function select(string $key, array|string $options, string $default = '', ?string $title = null,
                           ?string $desc = null, string $additionalContent = '', array $attr = []): void
    {
        if (empty($key) || $this->dontOverride($key)) {
            return;
        }

        $select = new \html_select(array_merge(['id' => $key, 'name' => $key, 'class' => 'custom-select'], $attr));
        $value = $this->rcmail->config->get($key, $default);

        if (is_string($options)) {
            $values = preg_split('/\s*,\s*/', trim($options), -1, PREG_SPLIT_NO_EMPTY);
            $options = array_combine($values, $values);
        }

        foreach ($options as $val => $label) {
            if (!is_numeric($label)) {
                $text = $this->rcmail->gettext($label, $this->pluginId);
                if (!str_starts_with($text, '[')) {
                    $label = $text;
                }
            }

            $select->add($label, $val);
        }

        $this->arg['blocks'][$this->blockId]['options'][$key] = [
            'title' => $this->getTitle($key, $title, $desc),
            'content' => $this->wrapContent($select->show($value) . $additionalContent),
        ];
    }

    /**
     * Creates a preferences row containing custom HTML in the value column.
     *
     * @param string $key Full preference key used to retrieve translations, or an empty string to hide the title.
     * @param string $content HTML displayed in the value column.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @return void
     */
    public function html(string $key, string $content, ?string $title = null, ?string $desc = null): void
    {
        $this->arg['blocks'][$this->blockId]['options'][uniqid()] = [
            'title' => $key ? $this->getTitle($key, $title, $desc, true) : '',
            'content' => $this->wrapContent($content),
        ];
    }

    /**
     * Creates the option title displayed in the left preferences' column. When $title is null, the title is retrieved
     * from the plugin's translation files. When $desc is null, a popup description is created if a <key>_desc
     * translation exists.
     *
     * @param string $key Full preference key used to retrieve the title and description translations.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @param bool $spanLabel Set to true when using html instead of an input element to use <span> instead of <label>.
     * @return string|null Rendered title HTML, or null when the title column should be hidden.
     */
    private function getTitle(string $key, ?string $title = null, ?string $desc = null,
                              bool $spanLabel = false): ?string
    {
        if ($title === '') {
            return null;
        }

        $id = substr($key, strlen($this->pluginId) + 1);

        if ($desc === null) {
            $desc = $this->gettext("{$id}_desc");
            if (str_starts_with($desc, '[')) {
                $desc = '';
            }
        } else {
            $desc = \rcube::Q($desc);
        }

        $text = $title === null ? $this->gettext($id) : \rcube::Q($title);
        $popup = $desc === '' ? '' : \html::div(['class' => 'xinfo right top'], \html::div([], $desc));
        $label = $spanLabel ? \html::span(['class' => 'col-form-label'], $text) : \html::label($key, $text);

        return \html::div(['class' => 'xpref-label-container'], $label . $popup);
    }

    /**
     * Retrieves and escapes a translation from the current plugin's translation files.
     *
     * @param string $key Translation key.
     * @return string Escaped translated text.
     */
    private function gettext(string $key): string
    {
        return \rcube::Q($this->rcmail->gettext($key, $this->pluginId));
    }

    /**
     * Wraps the value column content to ensure a consistent minimum row height.
     *
     * @param string $content Value column content.
     * @return string Wrapped HTML content.
     */
    private function wrapContent(string $content): string
    {
        return \html::div(['class' => 'xpref-content'], $content);
    }

    /**
     * Checks whether the specified preference is included in Roundcube's dont_override configuration.
     *
     * @param string $key Preference key.
     * @return bool True when the preference must not be overridden.
     */
    private function dontOverride(string $key): bool
    {
        $dontOverride = $this->rcmail->config->get('dont_override', []);

        return is_array($dontOverride) && in_array($key, $dontOverride, true);
    }
}