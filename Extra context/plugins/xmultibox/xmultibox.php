<?php
/**
 * Roundcube Plus Multibox plugin.
 *
 * Copyright 2024, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . "/../xframework/common/Plugin.php";

use XFramework\Response;

class xmultibox extends XFramework\Plugin
{
    const MAX_TEXT_LENGTH = 100;
    const PASSWORD_PLACEHOLDER = '************';
    protected bool $hasConfig = false;
    protected string $databaseVersion = "20240911";
    private array $menuList = [];
    private array $identityErrorData = [];
    protected string $appUrl = "?_task=settings&_action=identities&edit_first_identity=";

    // values stored directly in the session that need to get overwritten to enable a new identity
    private array $sessionKeys = [
        'storage_host',
        'storage_port',
        'storage_ssl',
        'imap_namespace',
        'imap_delimiter',
        'imap_list_conf',
        'username',
        'password',
    ];

    // values that need to be overwritten in the config to enable a new identity (these values are stored in the session
    // but then get served as config values in configGet())
    private array $configKeys = [
        'smtp_host',
        'smtp_port',
        'smtp_ssl_mode',
        'smtp_user',
        'smtp_pass',
        'smtp_auth',
        'lock_special_folders',
        'show_real_foldernames',
        'drafts_mbox',
        'sent_mbox',
        'junk_mbox',
        'trash_mbox',
    ];

    /**
     * Initializes the plugin.
     */
    public function initialize(): void
    {
        $this->add_hook("ready", [$this, "ready"]);
        $this->add_hook("xemail_schedule_before_save", [$this, "emailScheduleBeforeSend"]);
        $this->add_hook("xemail_schedule_after_deliver", [$this, "emailScheduleAfterDeliver"]);
        $this->add_hook("xais_get_plugin_documentation", [$this, "xaisGetPluginDocumentation"]);
        // must be before the switch clause below since some of the functions depend on the modified config values
        $this->add_hook("config_get", [$this, "configGet"]);
        $this->register_action('xmultibox-change-identity', [$this, 'changeIdentityAction']);

        switch ($this->rcmail->action) {
            // handle imap test button click on the identity edit page
            case "xmultibox-test-imap-connection":
                $this->testImapConnection();
                break;

            // handle smtp test button click on the identity edit page
            case "xmultibox-test-smtp-connection":
                $this->testSmtpConnection();
                break;

            // handle the identity select box change on the compose page
            case "xmultibox-get-compose-folder-data":
                $this->getComposeFolderData();
                break;
        }

        if ($this->rcmail->task == "mail") {
            if ($this->rcmail->action == "compose") {
                // send the current identity id to the frontend, so we can set the "from" field
                $this->setJsVar("xmultiboxIdentityId", $this->getCurrentIdentityId());
            } else if ($this->rcmail->action == "send" || $this->rcmail->action == "bounce") {
                // when sending messages, set the smtp options and credentials
                $this->add_hook("smtp_connect", [$this, "smtpConnect"]);
            } else {
                // on the mail page, add the identity select box, but only if there are multiple identities and any of
                // them have multibox data
                if ($this->menuList = $this->createMenuList()) {
                    $this->add_hook("template_object_username", [$this, "templateObjectUsername"]);
                    $this->add_hook("render_page", [$this, "renderPage"]);
                }
            }
        } else if ($this->rcmail->task == "settings") {
            $this->add_hook('identity_form', [$this, 'identityForm']);
            $this->add_hook('identity_create', [$this, 'identityUpdate']);
            $this->add_hook('identity_update', [$this, 'identityUpdate']);
            $this->add_hook('preferences_list', [$this, 'preferencesList']);
            $this->add_hook('preferences_save', [$this, 'preferencesSave']);

            if ($this->rcmail->output) {
                $this->rcmail->output->add_label("xmultibox.cannot_login_to_imap_server",
                    "xmultibox.cannot_login_to_smtp_server");
            }
        }

        $this->includeAsset("assets/scripts/plugin.min.js");
        $this->includeAsset("assets/styles/plugin.css");
    }

    public function xaisGetPluginDocumentation(array $arg): array
    {
        if ($arg['plugin'] != $this->ID) {
            return $arg;
        }

        $arg['text'] = <<<PROMPT
            Multibox: use different IMAP/SMTP servers per identity
            
            Where: Settings > Identities; Mail > top bar identity dropdown; Compose > From
            
            Setup:
            - Edit or create an Identity > enable "Use custom mail servers".
            - Incoming (IMAP): Server, Port (default 993), Connection security (e.g., TLS), Username, Password. [Test connection]
            - Outgoing (SMTP): Server, Port (default 465), Connection security (e.g., TLS), Authentication: "Use IMAP username and password" or specify Username/Password. [Test connection]
            
            Using it:
            - Mail: Use the identity dropdown in the top bar to load that account’s mailbox (one account at a time; no aggregation).
            - Compose: Change the From identity to send via the SMTP server linked to that identity.
            
            Notes:
            - No OAuth support; cannot connect to providers requiring OAuth (e.g., Google/Microsoft).
            PROMPT;
        return $arg;
    }


    /**
     * Replaces config values specific to the current identity server settings. This is executed every time a function
     * requests a config value. The new config values are stored in the session.
     *
     * @param array $arg
     * @return array
     */
    public function configGet(array $arg): array
    {
        if ($config = $this->getConfigData()) {
            if ($arg['name'] == '*') {
                foreach ($config as $key => $value) {
                    $arg['result'][$key] = $value;
                }
            } else if (array_key_exists($arg['name'], $config)) {
                $arg['result'] = $config[$arg['name']];
            }
        }

        return $arg;
    }

    /**
     * Hook handler for the ready event.
     *
     * @param array $arg
     * @return array
     */
    public function ready(array $arg): array
    {
        // if multibox data is not stored in the session yet (right after login, or when the plugin was enabled by admin
        // while users are already logged in), set all the needed session values including storing the default session
        // values taken from the current session into default_session, so it can be used to switch to identities that
        // don't use multibox
        if (!isset($_SESSION['xmultibox'])) {
            // get the default identity record
            $identity = $this->rcmail->user->get_identity();

            if (!is_array($identity)) {
                rcmail::write_log('errors', '[xmultibox] Unable to retrieve user identity record (728559)');
                return $arg;
            }

            // create the multibox session array
            $_SESSION['xmultibox'] = [
                'identity_id' => $identity['identity_id'],
                'email' => $identity['email'],
                'config' => [],
                'enabled' => false,
                'default_session' => $this->getSessionData(),
            ];

            // if the default identity has multibox enabled, switch to it
            if ($data = $this->getMultiboxData($identity)) {
                $_SESSION['xmultibox']['enabled'] = true;
                $this->setConfigData($data);
                $this->setSessionData($data);
                $this->resetSession();
                $storage = $this->rcmail->get_storage();
                if (method_exists($storage, 'close')) {
                    $storage->close();
                }
                $storage->clear_cache('mailboxes', true);
            }
        }

        // if we're editing the scheduled message (when the user clicks edit on the scheduled message), and if the
        // scheduled message was created using xmultibox, and belongs to a different imap server than default, switch
        // the identity, so the message can be properly saved to drafts and edited
        if (!empty($_GET['xesid']) &&
            ($record = $this->db->row("xemail_schedule_queue", ["message_id" => $_GET['xesid'], "user_id" => $this->userId])) &&
            ($serverConfig = json_decode((string)($record['server_config'] ?? ''), true)) &&
            is_array($serverConfig) &&
            !empty($serverConfig['identity_id'])
        ) {
           $this->changeIdentity($serverConfig['identity_id']);
        }

        return $arg;
    }

    /**
     * Hook handler for the xemail_schedule before send event. Sets the server data to be stored in the database along
     * with the scheduled email, so the plugin knows how to send and save the scheduled message.
     *
     * @param array $arg
     * @return array
     */
    public function emailScheduleBeforeSend(array $arg): array
    {
        if ($arg['user_id'] == $this->userId && $this->currentMultiboxEnabled()) {
            // get all the relevant data
            $data = array_merge($this->getConfigData(), $this->getSessionData());

            // add identity id to the data
            $data['identity_id'] = $this->getCurrentIdentityId();

            // overwrite sent_mbox and set it to what it's set when composing the message
            if (isset($arg['server_config']['sent_mbox'])) {
                $data['sent_mbox'] = $arg['server_config']['sent_mbox'];
            }

            // return the data in server_config
            $arg['server_config'] = $data;
        }

        return $arg;
    }

    /**
     * Hook handler for the xemail_schedule after deliver event. After the message is sent, we check if it was scheduled
     * using a multibox and we save the message in the sent folder of that server, instead of the default server as
     * xemail_scheduler would do.
     *
     * @param array $arg
     * @return array
     */
    public function emailScheduleAfterDeliver(array $arg): array
    {
        if (!empty($arg['server_config']['storage_host']) &&
            !empty($arg['server_config']['sent_mbox']) &&
            ($imap = $this->connectToImap($arg['server_config'], true))
        ) {
            // save message in the sent folder
            // $msg is passed to save_message() by reference; we need to make it a variable to prevent php warnings
            $msg = $arg['message']->getMessage();
            $arg['success'] = $imap->save_message($arg['server_config']['sent_mbox'], $msg, '', false, ['SEEN']);

            // set sent_mbox to an empty string to force xemail_schedule to just delete the message without saving it
            // in the sent folder
            $arg['server_config']['sent_mbox'] = "";
        }

        return $arg;
    }

    /**
     * Hook handler for smtp connection. It the smtp connection settings with the settings specified in the identity
     * when Roundcube connects to the smtp server. This needs to be done explicitly, overwriting the config values won't
     * be enough for smtp connection as it is for imap connection.
     *
     * @param array $arg
     * @return array
     */
    public function smtpConnect(array $arg): array
    {
        if ($config = $_SESSION['xmultibox']['config']) {
            $arg['smtp_host'] = (empty($config['smtp_ssl_mode']) ? "" : $config['smtp_ssl_mode'] . "://") .
                ($config['smtp_host'] ?: "") . ":" .
                ($config['smtp_port'] ?: "25");
            $arg['smtp_helo_host'] = $config['smtp_host'] ?: "";

            $arg['smtp_user'] = $config['smtp_user'] ?: "";
            $arg['smtp_pass'] = $config['smtp_pass'] ? $this->rcmail->decrypt($config['smtp_pass']) : "";

            // allow using empty username and password by specifying [none]
            $arg['smtp_user'] == '[none]' && ($arg['smtp_user'] = '');
            $arg['smtp_pass'] == '[none]' && ($arg['smtp_pass'] = '');
        }

        return $arg;
    }

    /**
     * Hook handler for the username display on the mail page. It replaces the username text with an anchor element
     * containing the currently selected identity username. The anchor triggers the select popup box created in
     * renderPage().
     *
     * @param array $arg
     * @return array
     */
    public function templateObjectUsername(array $arg): array
    {
        if (empty($_SESSION['xmultibox']['email'])) {
            return $arg;
        }

        // wrap the contents of the span that contains the username in an anchor that will trigger the popup
        $element = [
            'href' => '#',
            'id' => 'xmultibox-menu-link',
            'class' => 'button icon active',
            'role' => 'button',
            'data-popup' => 'xmultibox-menu',
        ];

        if ($this->skinBase == "larry") {
            $element['onclick'] = "UI.toggle_popup('xmultibox-menu', event); return false";
        }

        $arg['content'] = html::a($element, html::span([], rcube::Q($_SESSION['xmultibox']['email'])));
        return $arg;
    }

    /**
     * Hook handler for page rendering. It adds the identity select popup on the mail page.
     *
     * @param array $arg
     * @return array
     */
    public function renderPage(array $arg): array
    {
        // create a list of the identities to display in the popup
        $items = [];
        $identityId = $this->getCurrentIdentityId();

        foreach ($this->menuList as $id => $name) {
            $items[] = html::tag("li", ["role" => "menuitem"], html::a(
                [
                    "class" => ($identityId == $id ? "checked " : "") . "identity active",
                    "role" => "button",
                    "tabindex" => "0",
                    "aria-disabled" => "false",
                    "href" => "javascript:void(0)",
                    "data-id" => $id,
                ],
                html::span([], rcube::Q($name))
            ));
        }

        // insert the popup code to the html
        $this->html->insertAfterBodyStart(
            html::div(['id' => 'xmultibox-menu', 'class' => 'popupmenu'],
                html::tag(
                    "ul",
                    [
                        'class' => 'menu ' . ($this->skinBase == "larry" ? "toolbarmenu" : "listing"),
                        'role' => 'menu',
                    ],
                    implode("", $items)
                )
            ),
            $arg['content']
        );

        return $arg;
    }

    /**
     * Hook handler for displaying preferences pages. It adds the username of the selected identity to the title of the
     * special folders page, to make it clearer which account the folders belong to.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesList(array $arg): array
    {
        // page: Settings / Special Folders
        if ($arg['section'] == 'folders' && $_SESSION['username']) {
            $arg['blocks']['main']['name'] .= " " . $this->rcmail->gettext([
                'name' => "xmultibox.text_in_parentheses",
                'vars' => ['s' => $_SESSION['username']]
            ]);
        }

        return $arg;
    }

    /**
     * Hook handler for saving the values from the preferences pages. If the currently selected identity has a multibox
     * record, it takes the special folder information and saves it in the identity db record instead of the main
     * config.
     * Keep in mind that if you set a folder as a special folder, it'll disappear from the list of folders on the mail
     * page and the folders settings page because it'll replace the special folder (for example, Sent). If you check
     * 'Show real names for special folders', then you'll see that new folders name in place of 'Sent'.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesSave(array $arg): array
    {
        // page: Settings / Special Folders
        if ($arg['section'] == 'folders' &&
            !empty($_SESSION['xmultibox']['enabled']) &&
            ($identityId = $this->getCurrentIdentityId()) &&
            !empty($this->getIdentityServerData($identityId))
        ) {
            $data = [
                'drafts_mbox' => $arg['prefs']['drafts_mbox'],
                'sent_mbox' => $arg['prefs']['sent_mbox'],
                'junk_mbox' => $arg['prefs']['junk_mbox'],
                'trash_mbox' => $arg['prefs']['trash_mbox'],
                'show_real_foldernames' => $arg['prefs']['show_real_foldernames'],
                'lock_special_folders' => true, // indicates that the user has edited and saved the special folders
            ];

            if ($this->saveIdentityServerData($identityId, $data)) {
                // saved successfully: let RC know that everything went ok, even though we're aborting
                $arg['result'] = true;
                // update the current config data in session
                $this->setConfigData(array_merge($this->getConfigData(), $data));
            } else {
                $arg['result'] = false;
                $arg['message'] = $this->gettext("xmultibox.cannot_save_multibox_data") . " (481182)";
            }

            // don't run the RC function that saves the values
            $arg['abort'] = true;
        }

        return $arg;
    }
    /**
     * Hook handler for the identity form. Adds the imap and smtp settings to the identity edit page.
     *
     * @param array $arg
     * @return array
     */
    public function identityForm(array $arg): array
    {
        // if there was an error while saving identity, load the preserved data
        if (!empty($this->identityErrorData['data'])) {
            foreach ($this->identityErrorData['data'] as $key => $val) {
                $arg['record']["xmultibox_$key"] = $val;
            }
        } else {
            // decode the xmultibox data
            if ($data = json_decode($arg['record']['xmultibox_data'] ?? "[]", true)) {
                foreach ($data as $key => $val) {
                    if (in_array($key, ["password", "smtp_pass"]) && $val) {
                        $arg['record']["xmultibox_$key"] = self::PASSWORD_PLACEHOLDER;
                    } else {
                        $arg['record']["xmultibox_$key"] = $val;
                    }
                }
            }
        }

        isset($arg['record']['xmultibox_storage_port']) || ($arg['record']['xmultibox_storage_port'] = "993");
        isset($arg['record']['xmultibox_storage_ssl']) || ($arg['record']['xmultibox_storage_ssl'] = "ssl");
        isset($arg['record']['xmultibox_smtp_ssl_mode']) || ($arg['record']['xmultibox_smtp_ssl_mode'] = "ssl");
        isset($arg['record']['xmultibox_smtp_port']) || ($arg['record']['xmultibox_smtp_port'] = "465");

        // create the forms: we're using the custom section for the enable checkbox instead of inserting it into the top
        // (addressing) form, because we want this checkbox to be right above the imap/smtp sections, and we can't be
        // sure that some other plugin will not add some more controls to the addressing form
        $enableForm = [
            'name' => " ",
            'attrs' => ['class' => 'xmultibox-enable'],
            'content' => [
                'xmultibox_enabled' => [
                    "type" => "checkbox",
                    "label" => $this->gettext("xmultibox.use_custom_mail_servers"),
                ]
            ]
        ];

        $imapForm = [
            'name' => $this->gettext("xmultibox.incoming_server"),
            'attrs' => ['class' => 'xmultibox-imap'],
            'content' => [
                "xmultibox_storage_host" => [
                    "type" => "text",
                    "label" => $this->gettext("xmultibox.server"),
                    "class" => ($this->identityErrorData['errors']['storage_host'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['storage_host'] ?? "",
                ],
                "xmultibox_storage_port" => [
                    "type" => "text",
                    "label" => $this->gettext("xmultibox.port"),
                    "class" => ($this->identityErrorData['errors']['storage_port'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['storage_port'] ?? "",
                ],
                "xmultibox_storage_ssl" => [
                    "type" => "select",
                    "label" => $this->gettext("xmultibox.connection_security"),
                    "skip-empty" => true,
                    "options" => ["" => $this->gettext("none"), "ssl" => "TLS", "tls" => "STARTTLS"],
                ],
                "xmultibox_username" => [
                    "type" => "text",
                    "label" => $this->gettext("username"),
                    "class" => ($this->identityErrorData['errors']['username'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['username'] ?? "",
                ],
                "xmultibox_password" => [
                    "type" => "password",
                    "label" => $this->gettext("password"),
                    "class" => ($this->identityErrorData['errors']['password'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['password'] ?? "",
                ],
                "xmultibox_imap_test" => [
                    "label" => " ",
                    "value" => "<div id='xmultibox-imap-test'>".
                        "<button type='button' class='btn btn-primary' onclick='xmultibox.testImapConnection()'>" .
                        "<span class='button-text'>" . $this->gettext("xmultibox.test_connection") . "</span>".
                        "<span class='xspinner'></span>".
                        "</button>".
                        "<span class='message'></span></div>",
                ],
            ],
        ];

        $smtpForm = [
            'name' => $this->gettext("xmultibox.outgoing_server"),
            'attrs' => ['class' => 'xmultibox-smtp'],
            'content' => [
                "xmultibox_smtp_host" => [
                    "type" => "text",
                    "label" => $this->gettext("xmultibox.server"),
                    "class" => ($this->identityErrorData['errors']['smtp_host'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['smtp_host'] ?? "",
                ],
                "xmultibox_smtp_port" => [
                    "type" => "text",
                    "label" => $this->gettext("xmultibox.port"),
                    "class" => ($this->identityErrorData['errors']['smtp_port'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['smtp_port'] ?? "",
                ],
                "xmultibox_smtp_ssl_mode" => [
                    "type" => "select",
                    "label" => $this->gettext("xmultibox.connection_security"),
                    "skip-empty" => true,
                    "options" => ["" => $this->gettext("none"), "ssl" => "TLS", "tls" => "STARTTLS"],
                ],
                "xmultibox_smtp_auth" => [
                    "type" => "select",
                    "label" => $this->gettext("xmultibox.authentication"),
                    "skip-empty" => true,
                    "onchange" => "xmultibox.onSettingsSmtpAuthChange()",
                    "options" => [
                        "imap" => $this->gettext("xmultibox.use_imap_user_pass"),
                        "custom" => $this->gettext("xmultibox.specify_user_pass"),
                    ]
                ],
                "xmultibox_smtp_user" => [
                    "type" => "text",
                    "label" => $this->gettext("username"),
                    "class" => ($this->identityErrorData['errors']['smtp_user'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['smtp_user'] ?? "",
                ],
                "xmultibox_smtp_pass" => [
                    "type" => "password",
                    "label" => $this->gettext("password"),
                    "class" => ($this->identityErrorData['errors']['smtp_pass'] ?? null) ? "error" : "",
                    "title" => $this->identityErrorData['errors']['smtp_pass'] ?? "",
                ],
                "xmultibox_smtp_test" => [
                    "label" => " ",
                    "value" => "<div id='xmultibox-smtp-test'>".
                        "<button type='button' class='btn btn-primary' onclick='xmultibox.testSmtpConnection()'>" .
                        "<span class='button-text'>" . $this->gettext("xmultibox.test_connection") . "</span>".
                        "<span class='xspinner'></span>".
                        "</button>".
                        "<span class='message'></span></div>",
                ],
            ]
        ];

        // insert the xmultibox sections right below the top (addressing) section
        $arg['form'] =
            array_slice($arg['form'], 0, 1, true) +
            ['xmultibox_enable' => $enableForm] +
            ['xmultibox_imap' => $imapForm] +
            ['xmultibox_smtp' => $smtpForm] +
            array_slice($arg['form'], 1, null, true);

        // reset the identity error data
        $this->identityErrorData = [];

        return $arg;
    }

    /**
     * Hook handler for the identity form saving. It extracts, validates, encrypts, and saves the multibox data in the
     * xmultibox_data db column.
     *
     * @param array $arg
     * @return array
     */
    public function identityUpdate(array $arg): array
    {
        $data = [];
        $allowedKeys = ['enabled', 'storage_host', 'storage_port', 'storage_ssl', 'username', 'password', 'smtp_host',
            'smtp_port', 'smtp_ssl_mode', 'smtp_auth', 'smtp_user', 'smtp_pass'];

        // get the data from the post and unset the multibox variables
        foreach ($_POST as $postKey => $val) {
            if (str_starts_with($postKey, "_xmultibox_")) {
                $key = substr($postKey, 11);
                if (in_array($key, $allowedKeys)) {
                    $data[$key] = trim($val);
                }
                unset($_POST[$postKey]);
            }
        }

        if (empty($data)) {
            return $arg;
        }

        if ($serverData = $this->getIdentityServerData($arg['id'] ?? 0)) {
            if ($data['password'] == self::PASSWORD_PLACEHOLDER) {
                $data['password'] = $this->rcmail->decrypt($serverData['password']);
            }

            if ($data['smtp_pass'] == self::PASSWORD_PLACEHOLDER) {
                $data['smtp_pass'] = $this->rcmail->decrypt($serverData['smtp_pass']);
            }
        }

        // set enabled to be saved in its own column and remove it from the data
        $multiboxEnabled = (int)!empty($data['enabled']);
        unset($data['enabled']);

        if ($multiboxEnabled) {
            $imap = false;
            try {
                // copy smtp credentials from imap if needed
                if ($data['smtp_auth'] == "imap") {
                    $data['smtp_user'] = $data['username'];
                    $data['smtp_pass'] = $data['password'];
                }

                in_array($data['storage_ssl'], ['ssl', 'tls']) || ($data['storage_ssl'] = "");
                in_array($data['smtp_ssl_mode'], ['ssl', 'tls']) || ($data['smtp_ssl_mode'] = "");

                // validate the data
                $errors = [];
                if (!$this->validateSettings($data, $errors)) {
                    $this->identityErrorData['errors'] = $errors;
                    throw new Exception(reset($errors)); // get the first value from errors
                }

                // try logging in to the imap server
                if (!($imap = $this->connectToImap($data))) {
                    throw new Exception($this->gettext("xmultibox.cannot_login_to_imap_server"));
                }

                // try logging in to the smtp server
                if (!$this->connectToSmtp($data)) {
                    throw new Exception($this->gettext("xmultibox.cannot_login_to_smtp_server"));
                }

                // if it's a new record, or new server settings, or if the user hasn't saved special folders for
                // this record (lock_special_folders config setting), get special folders from the server and save them
                if (empty($arg['id']) ||
                    !$serverData ||
                    $serverData['storage_host'] != $data['storage_host'] ||
                    $serverData['username'] != $data['username'] ||
                    empty($data['lock_special_folders'])
                ) {
                    $data['lock_special_folders'] = false;
                    $data['show_real_foldernames'] = false;

                    // need to set lock_special_folders to false, otherwise if the user has saved special folders
                    // for the default account, it'll return those settings
                    $lock = $this->rcmail->config->get('lock_special_folders');
                    $this->rcmail->config->set('lock_special_folders', false);
                    foreach ($imap->get_special_folders(true) as $key => $value) {
                        $data[$key . '_mbox'] = $value;
                    }
                    $this->rcmail->config->set('lock_special_folders', $lock);
                }
            } catch (Exception $e) {
                // on validation or connection error, preserve the data the user already typed into the controls
                // since RC doesn't do this for identities
                $this->identityErrorData['data'] = $data;
                $this->identityErrorData['data']['enabled'] = $multiboxEnabled;

                $arg['abort'] = true;
                $arg['result'] = false;
                $arg['message'] = $e->getMessage() . " " . $this->gettext("xmultibox.settings_not_saved");
                return $arg;
            } finally {
                $imap && $imap->close();
            }
        }

        // encrypt the server passwords in the db
        if (!empty($data['password'])) {
            $data['password'] = $this->rcmail->encrypt($data['password']);
        }
        if (!empty($data['smtp_pass'])) {
            $data['smtp_pass'] = $this->rcmail->encrypt($data['smtp_pass']);
        }

        // encode the data and set it to be saved
        $arg['record']['xmultibox_data'] = json_encode($data);
        $arg['record']['xmultibox_enabled'] = $multiboxEnabled;

        // if editing the identity that is currently set, update it in the session, or if disabled, restore default
        if (!empty($arg['id']) && $arg['id'] == $this->getCurrentIdentityId()) {
            $this->resetSession();
            $_SESSION['xmultibox']['email'] = $arg['record']['email'];
            $_SESSION['xmultibox']['enabled'] = (bool)$multiboxEnabled;

            if ($multiboxEnabled) {
                $this->setConfigData($data);
                $this->setSessionData($data);
            } else {
                $this->setConfigData([]);
                $this->setSessionData($this->getDefaultSessionData());
            }
        }

        return $arg;
    }

    /**
     * Handles identity change invoked through the drop-down box on the mail page via the 'xmultibox-change-identity'
     * action.
     *
     * @return void
     */
    public function changeIdentityAction(): void
    {
        if (method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        } elseif (method_exists($this->rcmail, 'check_request_token') && !$this->rcmail->check_request_token(rcube_utils::INPUT_POST)) {
            $this->rcmail->output->show_message("Invalid request token", "error");
            return;
        }

        if ($this->changeIdentity(rcube_utils::get_input_value('id', rcube_utils::INPUT_POST))) {
            $this->rcmail->output->redirect(['_task' => 'mail', '_mbox' => 'INBOX']);
        } else {
            $this->rcmail->output->show_message("ERROR: Cannot change identity. (281994)", "error");
        }
    }

    /**
     * Changes identity. Loads the multibox data from the specified identity and loads it to the session. The multibox
     * data is stored as json in identity's xmultibox_data field. It is loaded into different places in the session
     * according to the $this->sessionKeys and $this->configKeys.
     *
     * @param $identityId
     * @return bool
     */
    protected function changeIdentity($identityId): bool
    {
        // get the identity record
        if (!($identity = $this->rcmail->user->get_identity($identityId))) {
            return false;
        }

        // clear and set the basics
        $this->resetSession();
        $_SESSION['xmultibox']['identity_id'] = $identity['identity_id'];
        $_SESSION['xmultibox']['email'] = $identity['email'];

        if ($data = $this->getMultiboxData($identity)) {
            // if this identity record has multibox data, set the data in session
            $_SESSION['xmultibox']['enabled'] = true;
            $this->setConfigData($data);
            $this->setSessionData($data);
        } else {
            // if this identity record doesn't have multibox data, restore the default (original) data into session
            $_SESSION['xmultibox']['enabled'] = false;
            $this->setConfigData([]);
            $this->setSessionData($this->getDefaultSessionData());
        }

        // clear the cache to make sure the folders and messages reload properly
        $this->rcmail->get_storage()->clear_cache('mailboxes', true);

        return true;
    }

    /**
     * Resets and clears the session values that need to be cleared before changing to a different identity.
     *
     * @return void
     */
    private function resetSession(): void
    {
        // reset _mbox in url because the new server might not have that same mailbox
        $_SESSION['mbox'] = '';

        // set the mail page to 1 because the new mailbox might not have that many pages
        $_SESSION['page'] = 1;

        // remove imap-related session variables
        $this->rcmail->session->remove('imap_host');
        $this->rcmail->session->remove('imap_namespace');
        $this->rcmail->session->remove('imap_delimiter');
        $this->rcmail->session->remove('imap_list_conf');
        $this->rcmail->session->remove("unseen_count");
        $this->rcmail->session->remove("folders");

        // force rc to re-check thread capabilities of the server it connects to, without this, if the server specified
        // in config supports threading, but the server from identity doesn't, rc will throw an error when trying to
        // use thread commands: unknown command 'THREAD'
        $this->rcmail->session->remove("STORAGE_THREAD");
    }

    /**
     * Ajax handler for the 'Test connection' button in the imap section of the identity edit page.
     *
     * @return void
     */
    public function testImapConnection(): void
    {
        if (method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        } elseif (method_exists($this->rcmail, 'check_request_token') && !$this->rcmail->check_request_token(rcube_utils::INPUT_POST)) {
            Response::error("Invalid request token");
            return;
        }

        $ssl = trim($this->input->get("storage_ssl"));

        $data = [
            "storage_host" => trim($this->input->get("storage_host")),
            "storage_port" => trim($this->input->get("storage_port")),
            "storage_ssl"  => in_array($ssl, ['ssl', 'tls']) ? $ssl : "",
            "username"     => trim($this->input->get("username")),
            "password"     => trim($this->input->get("password")),
        ];

        // if editing the identity record, the password will be a placeholder, need to replace it with real password
        // from the db
        if ($serverData = $this->getIdentityServerData($this->input->get("identity_id"))) {
            if ($data['password'] == self::PASSWORD_PLACEHOLDER) {
                $data['password'] = $this->rcmail->decrypt($serverData['password']);
            }
        }

        $errors = [];
        if (!$this->validateSettings($data, $errors, ['imap'])) {
            Response::error(reset($errors));
        }

        if ($imap = $this->connectToImap($data)) {
            $imap->close();
            Response::success([], $this->gettext("xmultibox.login_successful"));
        }

        Response::error($this->gettext("xmultibox.cannot_login_to_imap_server"));
    }

    /**
     * Ajax handler for the 'Test connection' button in the smtp section of the identity edit page.
     * @return void
     */
    public function testSmtpConnection(): void
    {
        if (method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        } elseif (method_exists($this->rcmail, 'check_request_token') && !$this->rcmail->check_request_token(rcube_utils::INPUT_POST)) {
            Response::error("Invalid request token");
            return;
        }

        $ssl = trim($this->input->get("smtp_ssl_mode"));

        $data = [
            "smtp_host" => trim($this->input->get("smtp_host")),
            "smtp_port" => trim($this->input->get("smtp_port")),
            "smtp_ssl_mode" => in_array($ssl, ['ssl', 'tls']) ? $ssl : "",
            "smtp_auth" => trim($this->input->get("smtp_auth")),
            "smtp_user" => trim($this->input->get("smtp_user")),
            "smtp_pass" => trim($this->input->get("smtp_pass")),
            "username" => trim($this->input->get("username")),
            "password" => trim($this->input->get("password")),
        ];

        // if editing the identity record, the passwords will be placeholders, need to replace them with real password
        // from the db
        if ($serverData = $this->getIdentityServerData($this->input->get("identity_id"))) {
            if ($data['password'] == self::PASSWORD_PLACEHOLDER) {
                $data['password'] = $this->rcmail->decrypt($serverData['password']);
            }
            if ($data['smtp_pass'] == self::PASSWORD_PLACEHOLDER) {
                $data['smtp_pass'] = $this->rcmail->decrypt($serverData['smtp_pass']);
            }
        }

        // if smtp auth is set to use imap, set the smtp credentials to imap credentials
        if ($data['smtp_auth'] == 'imap') {
            $data['smtp_user'] = $data['username'];
            $data['smtp_pass'] = $data['password'];
        }

        $errors = [];
        if (!$this->validateSettings($data, $errors, ['smtp'])) {
            Response::error(reset($errors)); // get the first value from errors
        }

        if ($this->connectToSmtp($data)) {
            Response::success([], $this->gettext("xmultibox.login_successful"));
        }

        Response::error($this->gettext("xmultibox.cannot_login_to_smtp_server"));
    }

    /**
     * SSRF guard: Validates that a target host does not resolve to private, loopback, or cloud metadata IP ranges
     * unless explicitly permitted by configuration or matching the server's default mail server.
     *
     * @param string $host
     * @return bool
     */
    private function isSafeHost(string $host): bool
    {
        $host = trim($host);
        if (empty($host)) {
            return false;
        }

        if ($this->rcmail->config->get('xmultibox_allow_local_hosts', false)) {
            return true;
        }

        if (preg_match('#^[a-z0-9+.-]+://#i', $host)) {
            $parsed = parse_url($host, PHP_URL_HOST);
            if (!empty($parsed)) {
                $host = $parsed;
            }
        }

        $default_host = (string)$this->rcmail->config->get('default_host', '');
        $smtp_server = (string)$this->rcmail->config->get('smtp_server', '');
        if ($host === $default_host || $host === $smtp_server) {
            return true;
        }

        $resolved_ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (empty($resolved_ip) || ($resolved_ip === $host && !filter_var($host, FILTER_VALIDATE_IP))) {
            return false;
        }

        if (filter_var($resolved_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return true;
    }

    /**
     * Validates the imap/smtp server settings specified on the identity edit page.
     *
     * @param array $data
     * @param array $errors
     * @param array $sections
     * @return bool
     */
    private function validateSettings(array $data, array &$errors = [], array $sections = ["imap", "smtp"]): bool
    {
        empty($sections) && ($sections = ["imap", "smtp"]);
        $keys = [];
        $errors = [];

        // create an array of fields that should be validated and specify the validation rules
        if (in_array("imap", $sections)) {
            $keys = array_merge($keys, [
                "storage_host" => ["label" => "xmultibox.server", "validation" => ["required", "maxlen", "host"]],
                "storage_port" => ["label" => "xmultibox.port", "validation" => ["required", "port"]],
                "username" => ["label" => "username", "validation" => ["required", "maxlen"]],
                "password" => ["label" => "password", "validation" => ["required", "maxlen"]],
            ]);
        }

        if (in_array("smtp", $sections)) {
            $keys = array_merge($keys, [
                "smtp_host" => ["label" => "xmultibox.server", "validation" => ["required", "maxlen", "host"]],
                "smtp_port" => ["label" => "xmultibox.port", "validation" => ["required", "port"]],
            ]);

            if ($data['smtp_auth'] == 'custom') {
                $keys["smtp_user"] = ["label" => "username", "validation" => ["required", "maxlen"]];
                $keys["smtp_pass"] = ["label" => "username", "validation" => ["required", "maxlen"]];
            }
        }

        // go over the fields and validate them according to their rules
        foreach ($keys as $key => $val) {
            if (in_array("required", $val['validation']) &&
                empty($data[$key])
            ) {
                $errors[$key] = $this->getValidationErrorMessage($key, $val['label'], "required");
                continue;
            }

            if (in_array("maxlen", $val['validation']) &&
                strlen($data[$key]) > self::MAX_TEXT_LENGTH
            ) {
                $errors[$key] = $this->getValidationErrorMessage($key, $val['label'], "maxlen");
                continue;
            }

            if (in_array("port", $val['validation']) &&
                (!is_numeric($data[$key]) || $data[$key] < 1 || $data[$key] > 65535)
            ) {
                $errors[$key] = $this->getValidationErrorMessage($key, $val['label'], "port");
                continue;
            }

            if (in_array("host", $val['validation']) &&
                !empty($data[$key]) &&
                !$this->isSafeHost($data[$key])
            ) {
                $errors[$key] = $this->getValidationErrorMessage($key, $val['label'], "host");
                continue;
            }
        }

        return empty($errors);
    }

    /**
     * Throws a validation exception with the text in this format "$section / $field is required"
     *
     * @param string $key
     * @param string $label
     * @param string $type
     * @return string
     */
    private function getValidationErrorMessage(string $key, string $label, string $type): string
    {
        $text = $this->gettext([
            "name" => "xmultibox.validation_$type",
            "vars" => [
                "n" => self::MAX_TEXT_LENGTH,
                "f" => $this->gettext(
                    "xmultibox." . (str_starts_with($key, "smtp") ? "outgoing_server" : "incoming_server")
                ) . " / " . $this->gettext($label),
            ]
        ]);

        if (empty($text) || $text === "xmultibox.validation_$type") {
            return $this->gettext(
                "xmultibox." . (str_starts_with($key, "smtp") ? "outgoing_server" : "incoming_server")
            ) . " / " . $this->gettext($label) . ($type === 'host' ? ' is invalid or restricted.' : ' is invalid.');
        }

        return $text;
    }

    /**
     * Returns an array of identity_id => identity_name if there are multiple identities and if any of those identities
     * have multibox enabled. This is used to render the identity selection on the mail page.
     *
     * @return array
     */
    private function createMenuList(): array
    {
        $list = [];
        $enabled = false;

        foreach ($this->rcmail->user->list_identities() as $record) {
            if (!empty($record['xmultibox_enabled'])) {
                $enabled = true;
            }

            $list[$record['identity_id']] =
                $record['name'] ? $record['name'] . " <{$record['email']}>" : $record['email'];
        }

        if (!$enabled || count($list) <= 1) {
            return [];
        }

        // case-insensitive sort by value, preserving keys
        uasort($list, 'strcasecmp');

        return $list;
    }

    /**
     * Returns the multibox data for the specified identity.
     *
     * @param $identityId
     * @return array
     */
    private function getIdentityServerData($identityId): array
    {
        if ($identityId &&
            ($record = $this->rcmail->user->get_identity($identityId)) &&
            ($data = json_decode($record['xmultibox_data'] ?? "", true))
        ) {
            return $data;
        }

        return [];
    }

    /**
     * Saves the imap/smtp server data for the specified identity to database. It first retrieves the data and then
     * merges the new data with it, in case $newData doesn't include all the needed fields.
     *
     * @param $identityId
     * @param array $newData
     * @return bool
     */
    private function saveIdentityServerData($identityId, array $newData): bool
    {
        if ($data = $this->getIdentityServerData($identityId)) {
            return $this->rcmail->user->update_identity(
                $identityId,
                ['xmultibox_data' => json_encode(array_merge($data, $newData))]
            );
        }

        return false;
    }

    /**
     * Handles the xmultibox-get-compose-folder-data ajax request. Responds with the list of folders (key/value)
     * for the requested identity and the selected option.
     */
    private function getComposeFolderData(): void
    {
        // check if any identity has multibox enabled
        if (!array_filter($this->rcmail->user->list_identities(), [$this, 'recordMultiboxEnabled'])) {
            Response::success(['enabled' => false]);
        }

        // change identity globally: the reason is that we not only want to replace the 'Save sent message in' select
        // options, but also drafts_mbox, and this is retrieved in the background from the config when saving drafts;
        // we could create our own imap object to retrieve the folders and find a way to provide drafts_mbox just for
        // this compose session, but it's complicated and error-prone, especially because there could be multiple
        // browser tabs open (for code, see repo b63aff2); if switching identity globally is a problem, we can try
        // restoring it after compose is done
        if (!$this->changeIdentity($this->input->get("identity_id"))) {
            Response::error("ERROR: Cannot retrieve server data. (189430)");
        }

        // create an array of folder options from the select box object returned by Roundcube
        // since the $options variable in the html_select object is protected, we need to use reflection to access it
        $select = rcmail_action::folder_selector();
        $mailboxes = ['' => '- ' . html::quote($this->rcmail->gettext('dontsave')) . ' -'];

        try {
            $reflection = new ReflectionClass($select);
            $property = $reflection->getProperty('options');
            $property->setAccessible(true);

            foreach ($property->getValue($select) as $option) {
                $mailboxes[$option['value']] = $option['text'];
            }
        } catch (ReflectionException) {}

        // if storage target is empty ("don't save" is selected), let's keep the "don't save" option selected
        Response::success([
            'enabled' => true,
            'options' => $mailboxes,
            'selected' => $this->input->get("storage_target") ? $this->rcmail->config->get('sent_mbox') : "",
        ]);
    }

    /**
     * Creates a new imap object and connects to server. Returns the object on success or false on failure.
     *
     * @param array $data
     * @param bool $decryptPassword
     * @return false|rcube_imap
     */
    private function connectToImap(array $data, bool $decryptPassword = false): rcube_imap|bool
    {
        // check if all the values required for the connection exist
        foreach (['storage_host', 'storage_port', 'storage_ssl', 'username', 'password'] as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        if (!$this->isSafeHost($data['storage_host'])) {
            return false;
        }

        // when creating rcube_imap, some session variables are used; let's back them up and remove them to create
        // a completely clean version of rcube_imap
        $keys = ['imap_namespace', 'imap_delimiter', 'imap_list_conf'];
        $sessionBk = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $sessionBk[$key] = $_SESSION[$key];
                $this->rcmail->session->remove($key);
            }
        }

        try {
            $imap = new rcube_imap();
            $imap->set_options(["debug" => false, "timeout" => 4]);

            if (!$imap->connect(
                rcube_utils::idn_to_ascii($data['storage_host']),
                rcube_utils::idn_to_ascii($data['username']),
                $decryptPassword ? $this->rcmail->decrypt($data['password']) : $data['password'],
                $data['storage_port'],
                $data['storage_ssl']
            )) {
                return false;
            }

            return $imap;
        } finally {
            // restore the removed session values
            foreach ($sessionBk as $key => $val) {
                $_SESSION[$key] = $val;
            }
        }
    }

    /**
     * Creates a new smtp object and connects to server. Used for connection testing. Returns true on success or false
     * on error.
     *
     * @param array $data
     * @return bool
     */
    private function connectToSmtp(array $data): bool
    {
        // check if all the values required for the connection exist
        foreach (['smtp_host', 'smtp_port', 'smtp_ssl_mode', 'smtp_user', 'smtp_pass'] as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        if (!$this->isSafeHost($data['smtp_host'])) {
            return false;
        }

        // allow using empty username and password by specifying [none]
        $data['smtp_user'] == '[none]' && ($data['smtp_user'] = '');
        $data['smtp_pass'] == '[none]' && ($data['smtp_pass'] = '');

        $this->rcmail->config->set('smtp_timeout', 15);

        return (new rcube_smtp())->connect(
            (empty($data['smtp_ssl_mode']) ? "" : $data['smtp_ssl_mode'] . "://") .
                ($data['smtp_host'] ?: "") . ":" .
                ($data['smtp_port'] ?: "25"),
            $data['smtp_port'],
            $data['smtp_user'],
            $data['smtp_pass'],
        );
    }

    private function recordMultiboxEnabled(array $identityRecord): bool
    {
        return !empty($identityRecord['xmultibox_enabled']);
    }

    /**
     * Returns the multibox data from the identity database record array. This data contains all the values needed to
     * use a different imap/smtp server, and it's used to overwrite session and config values.
     *
     * @param array $identityRecord
     * @return array
     */
    private function getMultiboxData(array $identityRecord): array
    {
        if ($this->recordMultiboxEnabled($identityRecord)) {
            $result = json_decode($identityRecord['xmultibox_data'] ?? "", true);
            return is_array($result) ? $result : [];
        }

        return [];
    }

    /**
     * Returns the currently selected multibox identity.
     *
     * @return mixed|string
     */
    private function getCurrentIdentityId(): mixed
    {
        return $_SESSION['xmultibox']['identity_id'] ?? 0;
    }

    /**
     * Returns the current multibox config data.
     *
     * @return array
     */
    private function getConfigData(): array
    {
        return $_SESSION['xmultibox']['config'] ?? [];
    }

    /**
     * Overwrites the multibox-related data in the config. This is used in the configGet hook to serve the config
     * values when requested by Roundcube.
     *
     * @param array $data
     * @return void
     */
    private function setConfigData(array $data): void
    {
        $_SESSION['xmultibox']['config'] = [];

        foreach ($this->configKeys as $key) {
            if (isset($data[$key])) {
                $_SESSION['xmultibox']['config'][$key] = $data[$key];
            }
        }
    }

    /**
     * Returns an array composed of the multibox-related values from the current session.
     *
     * @return array
     */
    private function getSessionData(): array
    {
        $result = [];

        foreach ($this->sessionKeys as $key) {
            if (isset($_SESSION[$key])) {
                $result[$key] = $_SESSION[$key];
            }
        }

        return $result;
    }

    /**
     * Overwrites the multibox-related data in the session.
     *
     * @param array $data
     * @return void
     */
    private function setSessionData(array $data): void
    {
        foreach ($this->sessionKeys as $key) {
            if (isset($data[$key])) {
                $_SESSION[$key] = $data[$key];
            }
        }
    }

    /**
     * Returns the default session data set right after login.
     *
     * @return array
     */
    private function getDefaultSessionData(): array
    {
        return $_SESSION['xmultibox']['default_session'] ?? [];
    }

    /**
     * Returns true if the currently selected identity has multibox enabled, false otherwise.
     *
     * @return bool
     */
    private function currentMultiboxEnabled(): bool
    {
        return !empty($_SESSION['xmultibox']['enabled']);
    }
}