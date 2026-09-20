<?php
/**
 * Roundcube Plus Signature plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . "/../xframework/common/Plugin.php";

use XFramework\Utils;
use XFramework\Response;

class xsignature extends XFramework\Plugin
{
    protected bool $hasConfig = true;
    protected string $databaseVersion = "20160601";
    protected bool $settingsHtml = false;
    protected bool $composeHtml = false;
    protected string $appUrl = "?_task=settings&_action=identities&edit_first_identity=";
    protected bool $loadAlpine = true;
    protected array $default = [
        "name" => "Sample name",
        "position" => "Sample position",
        "organization" => "Sample organization",

        "name_separator" => "",
        "position_separator" => ", ",
        "organization_separator" => "",

        "text_color" => "#000",
        "link_color" => "#0066ff",
        "font_family" => "sans-serif",
        "font_size" => "medium",

        "detail_layout" => "column",
        "detail_separator" => " | ",
        "detail_show_title" => true,
        "detail_title_color" => "#000",

        "disclaimer" => "",
        "disclaimer_color" => "#aaa",
        "disclaimer_size" => "small",
        "disclaimer_in_plain_text" => true,

        "social_icon_format" => "png",
        "social_icon_style" => "round_flat",
        "social_icon_size" => "medium",
        "social_icon_layout" => "row",
        "social_in_plain_text" => true,

        "logo" => "sample",
        "logo_width" => 150,
        "logo_resizable" => true,
        "logo_alt" => "Sample organization",

        "social_items" => [
            ["name" => "linkedin", "link" => "https://sample-url.com"],
            ["name" => "facebook", "link" => "https://sample-url.com"],
            ["name" => "x", "link" => "https://sample-url.com"],
            ["name" => "instagram", "link" => "https://sample-url.com"],
        ],
    ];
    protected array $formatItems = ["name", "position", "organization", "detail_title", "detail_value", "social"];
    protected array $availableValues = [
        "font_family" => ['serif', 'sans-serif', 'cursive', 'monospace'],
        "font_size" => ['small', 'medium', 'large'],
        "detail_layout" => ['column', 'row'],
        "disclaimer_size" => ['tiny', 'small', 'normal'],
        "social_icon_format" => ['png', 'svg'],
        "social_icon_style" => ['hand_drawn', 'logotype', 'logotype_black', 'outline', 'round_black', 'round_flat',
            'round_gloss', 'square_black', 'square_flat', 'square_gloss', 'xmas'],
        "social_icon_size" => ['small', 'medium', 'large'],
        "social_icon_layout" => ['row', 'column'],
    ];
    protected array $socialNames = [
        "behance" => "Behance",
        "dribbble" => "Dribbble",
        "facebook" => "Facebook",
        "github" => "Github",
        "instagram" => "Instagram",
        "linkedin" => "LinkedIn",
        "pinterest" => "Pinterest",
        "signal" => "Signal",
        "telegram" => "Telegram",
        "whatsapp" => "WhatsApp",
        "vimeo" => "Vimeo",
        "x" => "X",
        "youtube" => "YouTube",
    ];
    protected array $socialSizes = ['small' => "16", 'medium' => "24", 'large' => "32"];

    /**
     * Initialize the plugin.
     * @codeCoverageIgnore
     */
    public function initialize(): void
    {
        // add to $this->default (these values contain translations, so they can't be initialized above)
        $this->default["details"] = [
            ["title" => $this->gettext("xsignature.details_email"), "value" => "sample@email-address.com"],
            ["title" => $this->gettext("xsignature.details_website"), "value" => "https://sample-url.com"],
            ["title" => $this->gettext("xsignature.details_phone"), "value" => "123-456-789"],
            ["title" => $this->gettext("xsignature.details_mobile"), "value" => "987-654-321"],
            ["title" => $this->gettext("xsignature.details_address"), "value" => "This is a sample address"],
        ];

        if (rcube_utils::get_input_value("_action", rcube_utils::INPUT_POST) == "uploadSignatureLogo") {
            $this->uploadSignatureLogo();
            exit();
        }

        $this->add_hook("xais_get_plugin_documentation", [$this, "xaisGetPluginDocumentation"]);

        if ($this->rcmail->task == "mail" && $this->rcmail->action == "compose") {
            $this->add_hook('render_page', [$this, 'renderCompose']);

        } else if ($this->rcmail->task == "settings") {
            $this->add_hook('identity_form', [$this, 'identityForm']);
            $this->add_hook('identity_update', [$this, 'identityUpdate']);

            $this->includeAsset("xframework/assets/bower_components/jquery-form/jquery.form.js");
            $this->includeAsset("xframework/assets/bower_components/clipboard/dist/clipboard.min.js");
            $this->includeAsset("assets/scripts/app.min.js");
            $this->includeAsset("assets/styles/plugin.css");
            $this->includeAsset("xframework/assets/bower_components/jquery-minicolors/jquery.minicolors.min.js");
            $this->includeAsset("xframework/assets/bower_components/jquery-minicolors/jquery.minicolors.css");

            $this->rcmail->output->add_label("xsignature.left_padding", "xsignature.right_padding", "xsignature.top_padding",
                "xsignature.bottom_padding", "xsignature.bottom_border", "xsignature.left_border", "xsignature.right_border",
                "xsignature.top_border", "xsignature.bottom_border");
        }
    }

    public function xaisGetPluginDocumentation(array $arg): array
    {
        if ($arg['plugin'] != $this->ID) {
            return $arg;
        }

        $d = [];
        $d[] = 'Signature: visually build complex email signatures';
        $d[] = '';
        $d[] = 'Where: Settings → Identities → select an identity';
        $d[] = '';
        $d[] = 'Setup & use:';
        $d[] = '- Check "Enable Signature Builder" to replace the default signature textbox.';
        $d[] = '- Use the builder to pick a template/layout, add logo, user details, social links, disclaimer text, '.
            'and style.';
        $d[] = '- Live preview updates as you edit.';
        $d[] = '- Click Save to store the generated HTML signature.';
        $d[] = '- On Compose, the saved signature is inserted like a normal Roundcube signature.';
        $d[] = '';
        $d[] = 'Extras:';
        $d[] = '- Export: copy the generated signature to the clipboard for use in other email apps.';
        $d[] = '';
        $d[] = 'Notes:';
        $d[] = '- Works per identity; disable the builder to return to the standard textbox.';

        $arg['text'] = implode("\n", $d);
        return $arg;
    }


    /**
     * Override the 'signatures' environment variable with xsignature code, if the xsignature is enabled
     *
     * @param array $arg
     * @return array
     */
    public function renderCompose(array $arg): array
    {
        // replace signatures with the xsignature html
        $identities = $this->db->all(
            "SELECT identity_id AS id, xsignature_enabled AS enabled, xsignature_html AS html, xsignature_plain AS plain ".
            "FROM {identities} WHERE user_id = ? AND xsignature_enabled = 1",
            [$this->userId]
        );

        if (!empty($identities) && is_array($identities)) {
            $signatures = $this->rcmail->output->get_env("signatures");
            foreach ($identities as $identity) {
                if ($identity['html'] && $identity['plain']) {
                    $signatures[$identity['id']] = [
                        "html" => "-- <br />" . $identity['html'],
                        "text" => "-- \n" . $identity['plain'],
                    ];
                }
            }
            $this->rcmail->output->set_env("signatures", $signatures);
        }

        return $arg;
    }

    /**
     * Add items to the signature settings form, create the xsignature settings html. We can't add html to the page
     * here, so we create it and add it in renderPage(), which is called after.
     *
     * @param array $arg
     * @return array
     */
    public function identityForm(array $arg): array
    {
        // make sure all the xsignature variables are set
        (array_key_exists("record", $arg) && is_array($arg['record'])) || $arg['record'] = [];
        array_key_exists("xsignature_enabled", $arg['record']) || $arg['record']['xsignature_enabled'] = 1;
        array_key_exists("xsignature_id", $arg['record']) || $arg['record']['xsignature_id'] = 0;
        array_key_exists("xsignature_data", $arg['record']) || $arg['record']['xsignature_data'] = "";
        array_key_exists("xsignature_html", $arg['record']) || $arg['record']['xsignature_html'] = "";
        array_key_exists("xsignature_plain", $arg['record']) || $arg['record']['xsignature_plain'] = "";

        // if the signature id is not set yet, generate and set it
        if (empty($arg['record']['xsignature_id']) && isset($arg['record']['identity_id'])) {
            $arg['record']['xsignature_id'] = Utils::randomInt();
            $this->db->update(
                "identities",
                ["xsignature_id" => $arg['record']['xsignature_id']],
                ["identity_id" => $arg['record']['identity_id']]
            );
        }

        $arg['form']['signature']['content'] =
            [
                "xsignature_enabled" => [
                    "type" => "checkbox",
                    "label" => $this->gettext("xsignature.enable"),
                    "onclick" => "xsignature.enable(this)",
                ]
            ] + $arg['form']['signature']['content'];

        // get signature data for this identity
        $data = $this->getData($arg['record']['xsignature_data']);
        $data->id = $arg['record']['xsignature_id'];
        $data->assetUrl = Utils::getAssetUrl();
        $data->enabled = !empty($arg['record']['xsignature_enabled']);
        $data->customLogo = $this->rcmail->config->get("custom_signature_logo");
        $data->maxLogoSizeNote = $this->gettext([
            'name' => 'maxuploadsize',
            'vars' => ['size' => Utils::sizeToString($this->rcmail->config->get("max_logo_size", 102400))]
        ]);

        // make adjustments to the social items array
        if (!empty($data->social_items) && is_array($data->social_items)) {
            // google plus is discontinued: remove it from the data retrieved from the db
            foreach ($data->social_items as $key => $val) {
                if (isset($val->name) && $val->name == "google_plus") {
                    unset($data->social_items[$key]);
                }
            }

            // twitter got renamed to X
            foreach ($data->social_items as $key => $val) {
                if (isset($val->name) && $val->name == "twitter") {
                    $data->social_items[$key]->name = "x";
                }
            }

            // need to re-index the array, otherwise it might be considered as having length of 0 and get hidden by js
            $data->social_items = array_values($data->social_items);
        }

        $this->setJsVar("xsignature_data", $data);
        $this->setJsVar("xsignature_social_names", $this->socialNames);
        $this->setJsVar("xsignature_social_sizes", $this->socialSizes);
        $viewData = ["preview" => $this->view("elastic", "xsignature.preview", [], false)];

        foreach ($this->formatItems as $item) {
            $viewData["format_$item"] = $this->view("elastic", "xsignature.format", ["item" => $item], false);
        }

        // insert the xsignature html code into a block positioned right after the signature block
        $form = [];

        foreach ($arg['form'] as $key => $val) {
            $form[$key] = $val;
            if ($key == "signature") {
                $form['xsignature'] = [
                    "name" => null,
                    "content" => $this->view("elastic", "xsignature.settings", $viewData, false),
                ];
            }
        }

        $arg['form'] = $form;
        return $arg;
    }

    /**
     * Save the xsignature settings.
     *
     * @param array $arg
     * @return array
     */
    public function identityUpdate(array $arg): array
    {
        $arg['record']['xsignature_enabled'] = rcube_utils::get_input_value("_xsignature_enabled", rcube_utils::INPUT_POST);
        $html = rcube_utils::get_input_value("xsignature_html", rcube_utils::INPUT_POST, true);
        $plain = rcube_utils::get_input_value("xsignature_plain", rcube_utils::INPUT_POST);
        $data = json_decode(rcube_utils::get_input_value("xsignature_data", rcube_utils::INPUT_POST));

        if (!is_object($data)) {
            return $arg;   // nothing to save
        }

        if ($arg['record']['xsignature_enabled'] === null) {
            $arg['record']['xsignature_enabled'] = 0;
        }

        // collect and save the data
        $signatureId = $data->id;
        if ($signatureId !== null) {

            // validate and fix data
            $this->fixSignatureData($data);

            // if uploading a new logo, remove all unused signature file uploads (the files have the same file name but
            // could have different ext). We do this only if the logo file contains xsignature_temp, meaning it's a new
            // upload, otherwise we'd be deleting the currently set logo file.
            if (!empty($data->logo) && strpos($data->logo, "_xsignature_temp.") !== false) {
                $files = glob($this->getLogoDirectory() . $this->getLogoFileName($this->userId, $signatureId) . ".*", GLOB_ERR);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        unlink($file);
                    }
                }
            }

            // if logo just set, rename it from its temp name to its final name, and rename it on disk as well
            if ($data->logo && strpos($data->logo, "_xsignature_temp.") > -1) {
                $dir = dirname($this->getLogoDirectory() . $this->getLogoFileName($this->userId, $signatureId)) . "/";
                $array = explode("?", basename($data->logo)); // remove the random stamp from the url
                rename($dir . $array[0], $dir . str_replace("_xsignature_temp.", ".", $array[0]));
                $data->logo = str_replace("_xsignature_temp.", ".", $data->logo);
                $html = str_replace("_xsignature_temp.", ".", $html);
            }

            // remove the temp logo file if it exists (for example if it was uploaded, but later cleared, and saved)
            $files = glob(
                $this->getLogoDirectory() . $this->getLogoFileName($this->userId, $signatureId) . "_xsignature_temp.*",
                GLOB_ERR
            );

            if (!empty($files)) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }

            // assign values to the record to be saved
            $arg['record']['xsignature_data'] = json_encode($data);
            $arg['record']['xsignature_html'] = $html;
            $arg['record']['xsignature_plain'] = str_replace(["\r\n", "\r"], ["\n", "\n"], $plain);
        }

        return $arg;
    }

    /**
     * Handles the ajax request that uploads the logo image.
     */
    public function uploadSignatureLogo()
    {
        try {
            if (rcube_utils::get_input_value("_token", rcube_utils::INPUT_POST) != $this->rcmail->get_request_token()) {
                throw new Exception("Invalid token.");
            }

            if (!($signatureId = rcube_utils::get_input_value("xsignature_id", rcube_utils::INPUT_POST))) {
                throw new Exception("Invalid signature id");
            }

            $directory = $this->getLogoDirectory();

            if (!file_exists($directory) && !mkdir($directory, 0755, true)) {
                throw new Exception("Logo directory cannot be created.");
            }

            if (!is_writable($directory)) {
                throw new Exception("Logo directory is not writable.");
            }

            // get target file name (we're saving the file with _temp_ because we want to keep the old image in case
            // the user doesn't save the changes. When saving, the temp image gets renamed to a file name without temp.
            $pathInfo = pathinfo($_FILES['file']['name']);
            $ext = empty($pathInfo['extension']) ? "" : strtolower($pathInfo['extension']);
            $target = $this->getLogoFileName($this->userId, $signatureId) . "_xsignature_temp." . $ext;

            // move uploaded file
            $maxSize = $this->rcmail->config->get("max_logo_size", 1000000);
            $error = "";

            if (!$this->saveUploadedImage($_FILES['file'], $directory . $target, $maxSize, $error)) {
                throw new Exception($error);
            }

            // add random value to prevent caching
            Response::success(["url" => $this->getLogoUrlBase() . $target . "?r=" . mt_rand()]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Creates an array with all the properties for the signature. Combines the customized user
     * values and the global config values.
     *
     * @param $jsonUserData
     * @return mixed|stdClass
     */
    protected function getData($jsonUserData)
    {
        // decode the json user data string from the database
        $data = $jsonUserData ? json_decode($jsonUserData) : "";
        $useDefaults = empty($data);

        // if the settings have never been saved, create the default data object from config/default values
        if ($useDefaults) {
            $data = new stdClass();
        }

        // make sure all the settings are there
        foreach ($this->default as $name => $value) {
            if (!property_exists($data, $name)) {
                $data->$name = $this->rcmail->config->get($name, $value);
            }
        }

        // fix logo path in case protocol or config logo_url has changed
        if (!$useDefaults) {
            $data->logo = $this->fixLogoUrl((string)$data->logo);
        }

        // validate and fix data values
        foreach ($this->availableValues as $key => $options) {
            if (!in_array($data->$key, $options)) {
                $data->$key = $this->default[$key];
            }
        }

        $data->detail_show_title = (bool)$data->detail_show_title;
        $data->disclaimer_in_plain_text = (bool)$data->disclaimer_in_plain_text;
        $data->social_in_plain_text = (bool)$data->social_in_plain_text;
        $data->logo_resizable = (bool)$data->logo_resizable;

        return $data;
    }

    /**
     * Returns the root of the logo file system directory, this is where the logo files will be saved.
     *
     * @return string
     */
    protected function getLogoDirectory(): string
    {
        return Utils::addSlash($this->rcmail->config->get("logo_dir", RCUBE_INSTALL_PATH . "data/xsignature"));
    }

    /**
     * Returns the path and file name of the logo file for this user, relative to the root logo directory. Using
     * the structured directory format (000/000/filename) to limit the number of files stored in a single directory.
     *
     * @param $userId
     * @param $signatureId
     * @return string
     */
    protected function getLogoFileName($userId, $signatureId): string
    {
        return Utils::structuredDirectory($userId) . Utils::encodeId($userId) . Utils::encodeId($signatureId);
    }

    /**
     * Returns the full url base of the logo image with a trailing slash from the config. If the url is not a full
     * path, it prepends the current Roundcube url.
     *
     * @return string
     */
    protected function getLogoUrlBase(): string
    {
        return Utils::addSlash(
            Utils::getUrl((string)$this->rcmail->config->get("logo_url", "data/xsignature"))
        );
    }

    /**
     * Fixes the logo url by extracting the last three elements of the url and prepending the current logo url base.
     * We're storing the full url in the db, with the protocol and url base, but in reality we need to deal with a
     * relative url. If the user uploads an image in http and then access the webmail via https, the browser will issue
     * a warning. Or if the admin moves the data directory and specifies a different logo_url in the config, the url
     * images will not be found. So we strip the url base here and append it again according to the current settings.
     *
     * @param string $url
     * @return string
     */
    protected function fixLogoUrl(string $url): string
    {
        $array = explode("/", $url);

        if (count($array) <= 3) {
            return $url;
        }

        return $this->getLogoUrlBase() . implode("/", array_slice($array, -3));
    }

    /**
     * Validates and fixes the data before saving to database.
     *
     * @param stdClass $data
     * @return void
     */
    protected function fixSignatureData(stdClass $data): void
    {
        foreach ($this->default as $key => $value) {
            if (!property_exists($data, $key)) {
                $data->$key = $this->default[$key];
            }

            if (is_string($value))   $data->$key = trim((string)$data->$key);
            elseif (is_bool($value)) $data->$key = (bool)$data->$key;
            elseif (is_int($value))  $data->$key = (int)$data->$key;
        }


        if (!is_array($data->social_items)) {
            $data->social_items = $this->default['social_items'];
        } else {
            // convert social items from objects to arrays
            $data->social_items = array_map(fn($item) => (object)$item, $data->social_items);
        }

        // check properties that have a list of available values
        foreach ($this->availableValues as $key => $options) {
            if (!in_array($data->$key ?? '', $options, true)) {
                $data->$key = $this->default[$key];
            }
        }

        // truncate strings
        $lengths = [
            10  => ['name_separator', 'position_separator', 'organization_separator', 'detail_separator'],
            150 => ['name', 'position', 'organization', 'logo_alt'],
            2000 => ['disclaimer'],
        ];
        foreach ($lengths as $len => $keys) {
            foreach ($keys as $key) {
                $data->$key = mb_substr((string)$data->$key, 0, $len);
            }
        }

        // ensure hex colors
        $colors = ['text_color', 'link_color', 'detail_title_color', 'disclaimer_color'];
        foreach ($colors as $key) {
            if (!preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i', (string)$data->$key)) {
                $data->$key = $this->default[$key];
            }
        }

        // ensure a reasonable logo size
        $data->logo_width = max(20, min(600, (int)$data->logo_width));

        // validate social items
        foreach ($data->social_items as $key => $val) {
            $val->name = trim((string)($val->name ?? ''));
            $val->link = trim((string)($val->link ?? ''));

            if (empty($val->name) || empty($val->link) || !array_key_exists($val->name, $this->socialNames)
            ) {
                unset($data->social_items[$key]);
                continue;
            }

            if (!in_array($val->size ?? 0, $this->socialSizes)) {
                $val->size = "24";
            }

            if (strpos($val->link, 'https://') !== 0 && strpos($val->link, 'http://') !== 0) {
                $val->link = "https://" . $val->link;
            }

            $val->name = mb_substr($val->name, 0, 150);
            $val->link = mb_substr($val->link, 0, 250);

            // remove id, it's only used for alpine x-for
            unset($val->id);
        }

        // limit the length of social items to the number of available items and reindex the array in case we removed
        // some items above
        $data->social_items = array_values(array_slice($data->social_items, 0, count($this->socialNames)));
    }

}