<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * Copyright 2026, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

class Pref
{
    private \rcube $rcmail;
    private string $blockId;

    /**
     * Creates a preferences block. $title can be a gettext key or literal title. When $title is an empty string, the
     * block title is hidden.
     *
     * @param array $arg
     * @param string $pluginId
     * @param string|null $title
     * @param string|null $blockId
     */
    public function __construct(private array &$arg, private string $pluginId, ?string $title = null,
                                ?string $blockId = null)
    {
        $this->rcmail = xrc();
        $this->blockId = $blockId ?? $this->getUniqueId();

        if ($title !== null) {
            $text = $this->rcmail->gettext($title, $this->pluginId);
            $text = \rcube::Q(str_starts_with($text, '[') ? $title : $text);
            $this->arg['blocks'][$this->blockId]['name'] = $text;
        }
    }

    /**
     * Creates a checkbox preferences row.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param array|null $attr Html attributes to add to the input element.
     * @param string|null $addHtml Custom HTML content appended after the input.
     * @param string|null $title Custom title.
     * @param string|null $desc Custom popup description.
     * @param string|null $default
     * @return void
     */
    public function checkbox(string $key, ?array $attr = null, ?string $addHtml = null, ?string $title = null,
                             ?string $desc = null, ?string $default = null): void
    {
        if (empty($key) || self::dontOverride($key)) {
            return;
        }

        $value = $this->rcmail->config->get($key, $default ? 1 : 0);
        $input = new \html_checkbox(array_merge(['id' => $key, 'name' => $key, 'value' => 1], $attr ?? []));

        $this->arg['blocks'][$this->blockId]['options'][$key] = [
            'title' => $this->getTitle($key, $title, $desc),
            'content' => $this->wrapContent($input->show($value ? 1 : 0) . $addHtml),
        ];
    }

    /**
     * Creates a text input preferences row.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param array|null $attr Html attributes to add to the input element.
     * @param string|null $addHtml Custom HTML content appended after the input.
     * @param string|null $title Custom title.
     * @param string|null $desc Custom popup description.
     * @param ?string $default
     * @return void
     */
    public function input(string $key, ?array $attr = null, ?string $addHtml = null, ?string $title = null,
                          ?string $desc = null, ?string $default = null): void
    {
        if (empty($key) || self::dontOverride($key)) {
            return;
        }

        $value = $this->rcmail->config->get($key, (string)$default);
        $input = new \html_inputfield(array_merge(['id' => $key, 'name' => $key, 'type' => 'text'], $attr ?? []));

        $this->arg['blocks'][$this->blockId]['options'][$key] = [
            'title' => $this->getTitle($key, $title, $desc),
            'content' => $this->wrapContent($input->show($value) . $addHtml),
        ];
    }

    /**
     * Creates a select box preferences row. $options can be an associative array of values and labels or a
     * comma-separated string when the values and labels are identical. Non-numeric labels are translated if a
     * corresponding plugin translation exists.
     *
     * @param string $key Full preference key, including the plugin ID prefix.
     * @param array|string $options Available options as an associative array or comma-separated string.
     * @param array|null $attr Html attributes to add to the input element.
     * @param string|null $addHtml Custom HTML content appended after the input.
     * @param string|null $title Custom title.
     * @param string|null $desc Custom popup description.
     * @param string|null $default
     * @return void
     */
    public function select(string $key, array|string $options, ?array $attr = null, ?string $addHtml = null,
                           ?string $title = null, ?string $desc = null, ?string $default = null): void
    {
        if (empty($key) || self::dontOverride($key)) {
            return;
        }

        $select = new \html_select(array_merge(['id' => $key, 'name' => $key, 'class' => 'custom-select'], $attr ?? []));
        $value = $this->rcmail->config->get($key, (string)$default);

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
            'content' => $this->wrapContent($select->show($value) . $addHtml),
        ];
    }

    /**
     * Creates a preferences row containing custom HTML in the value column. Set $key to null to completely remove the
     * title column (html takes the entire row). Set $key to '' to set the label to an empty string (html is shown in
     * the value column).
     *
     * @param string $content
     * @param string|null $key
     * @param string|null $title Custom title.
     * @param string|null $desc Custom popup description.
     * @return void
     */
    public function html(string $content, ?string $key = null, ?string $title = null, ?string $desc = null): void
    {
        $this->arg['blocks'][$this->blockId]['options'][$this->getUniqueId()] = [
            'title' => $this->getTitle($key, $title, $desc, true),
            'content' => $this->wrapContent($content),
        ];
    }

    /**
     * Saves the specified preferences.
     *
     * @param array $arg Preferences hook arguments.
     * @param array $schema Validation schema indexed by preference key.
     * @param array $keys Full preference keys.
     * @return bool
     */
    public static function save(array &$arg, array $schema, array $keys): bool
    {
        foreach ($keys as $key) {
            if (empty($key) || self::dontOverride($key)) {
                continue;
            }

            $validator = $schema[$key] ?? ['type' => 'string', 'default' => ''];
            $value = \rcube_utils::get_input_value($key, \rcube_utils::INPUT_POST);

            // fix the value type because all values received from POST are strings.
            switch ($validator['type']) {
                case 'string':
                    $value = is_array($value) ? '' : (string)$value;
                    break;

                case 'bool':
                    $value = (bool)$value;
                    break;

                case 'int':
                    if (!is_numeric($value)) {
                        $value = $validator['default'];
                        break;
                    }

                    $value = (int)$value;
                    break;

                case 'float':
                    if (!is_numeric($value)) {
                        $value = $validator['default'];
                        break;
                    }

                    $value = (float)$value;
                    break;

                case 'array':
                    if (!is_array($value)) {
                        $value = $validator['default'];
                    }
                    break;
            }

            // Check the value and assign the default if invalid.
            if (isset($validator['options'])) {
                if (is_string($validator['options'])) {
                    $validator['options'] = explode(',', $validator['options']);
                }
                if (!in_array($value, $validator['options'], true)) {
                    $value = $validator['default'];
                }
            }

            if (is_string($value) && isset($validator['pattern']) &&
                preg_match($validator['pattern'], $value) !== 1
            ) {
                $value = $validator['default'];
            }

            if ((is_int($value) || is_float($value)) &&
                isset($validator['min']) && $value < $validator['min']
            ) {
                $value = $validator['default'];
            }

            if ((is_int($value) || is_float($value)) &&
                isset($validator['max']) && $value > $validator['max']
            ) {
                $value = $validator['default'];
            }

            $arg['prefs'][$key] = $value;
        }

        return true;
    }

    /**
     * Creates the option title displayed in the left preferences' column. When $title is null, the title is retrieved
     * from the plugin's translation files. When $desc is null, a popup description is created if a <key>_desc
     * translation exists.
     *
     * @param string|null $key Full preference key used to retrieve the title and description translations.
     * @param string|null $title Custom title, an empty string to hide the title column, or null to use the translation.
     * @param string|null $desc Custom description, an empty string to hide it, or null to use the translation.
     * @param bool $spanLabel Set to true when using html instead of an input element to use <span> instead of <label>.
     * @return string|null Rendered title HTML, or null when the title column should be hidden.
     */
    private function getTitle(?string $key, ?string $title = null, ?string $desc = null,
                              bool $spanLabel = false): ?string
    {
        if ($key === null) {
            return null;
        }

        if ($key === '') {
            return '';
        }

        // if $key is prefixed with {plugin ID}_, remove it
        $prefix = $this->pluginId . '_';
        $id = str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;

        $title = \Rcube::Q($title === null ? $this->gettext($id) : $title);
        $desc = \Rcube::Q($desc === null ? $this->gettext("{$id}_desc", '') : $desc);

        $label = $spanLabel ? \html::span(['class' => 'col-form-label'], $title) : \html::label($key, $title);

        if ($desc) {
            $label .= \html::div(['class' => 'xinfo right top'], \html::div([], \Rcube::Q($desc)));
        }

        return \html::div(['class' => 'xpref-label-container'], $label);
    }

    /**
     * Retrieves settings translation title.
     *
     * @param string|null $key
     * @param string|null $default
     * @return string
     */
    private function gettext(?string $key, ?string $default = null): string
    {
        if (empty($key)) {
            return '';
        }

        // try setting_$key
        $text = $this->rcmail->gettext("setting_$key", $this->pluginId);

        // if doesn't exist, try just $key
        if (str_starts_with($text, '[')) {
            $text = $this->rcmail->gettext($key, $this->pluginId);
        }

        // if doesn't exist, output $key
        if (str_starts_with($text, '[')) {
            $text = $default === null ? $key : $default;
        }

        return $text;
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
    private static function dontOverride(string $key): bool
    {
        $dontOverride = xrc()->config->get('dont_override', []);

        return is_array($dontOverride) && in_array($key, $dontOverride, true);
    }

    private function getUniqueId(): string
    {
        return uniqid($this->pluginId . '-');
    }    
}