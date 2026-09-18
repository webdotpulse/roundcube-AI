<?php
namespace XFramework;

/**
 * Roundcube Plus Framework plugin.
 *
 * This file provides the base class for all the cloud-storage-related plugins.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the LICENSE file for details.
 */

require_once __DIR__ . '/Plugin.php';

abstract class Cloud extends Plugin
{
    protected bool $enableComposeInsert = false;
    protected bool $enableComposeAttach = false;
    protected bool $enableAttachmentSave = false;
    protected string $providerName = '';

    abstract protected function downloadFile(array $file): array;

    /**
     * Initializes the plugin.
     */
    public function commonCloudInit(array $pages = ['mail', 'settings', 'xcalendar']): bool
    {
        // make the AI documentation always available
        $this->add_hook("xais_get_plugin_documentation", [$this, "xaisGetPluginDocumentation"]);

        if (!in_array($this->rcmail->task, $pages)) {
            return false;
        }

        $this->load_config();

        if ($this->enableComposeAttach || $this->enableComposeInsert) {
            $this->register_action($this->plugin . '_attach', [$this, 'attachFiles']);

            // run render page on mail and compose (_task=mail && _task=mail&_action=compose)
            if ($this->rcmail->task == 'mail') {
                $this->add_hook('render_page', [$this, 'renderPage']);
            }
        }

        if ($this->enableAttachmentSave) {
            // output attachment files requested by the cloud service, notice that the request from the server
            // is not logged in, so we need to serve it outside the normal roundcube routing
            if (\rcube_utils::get_input_value('xcloud_save', \rcube_utils::INPUT_GET)) {
                $this->deployAttachment();
            }

            if ($this->rcmail->action == 'SaveAttachmentDeployFile') {
                $this->saveAttachmentDeployFile();
            }

            if ($this->rcmail->action == 'RemoveAttachmentDeployFile') {
                $this->removeAttachmentDeployFile();
            }
        }

        $this->includeAsset('xframework/assets/scripts/xcloud.min.js');
        $this->includeAsset('xframework/assets/styles/xcloud.css');

        $this->rcmail->output->add_label('errorsaving', 'save', 'successfullysaved');

        // add plugin to the list of cloud plugins
        $cloudPlugins = xdata()->get('cloud_plugins', []);
        $cloudPlugins[$this->plugin] = [
            'enableComposeAttach' => $this->enableComposeAttach,
            'enableComposeInsert' => $this->enableComposeInsert,
            'enableAttachmentSave' => $this->enableAttachmentSave,
        ];
        xdata()->set('cloud_plugins', $cloudPlugins);

        // send the list of cloud-related plugins to frontend
        $this->setJsVar('xcloud_plugins', xdata()->get('cloud_plugins', []));

        return true;
    }

    /**
     * [Hook for xai] Returns documentation that teaches the AI model about the plugin.
     *
     * @param array $arg
     * @return array
     */
    public function xaisGetPluginDocumentation(array $arg): array
    {
        if ($arg['plugin'] != $this->ID) {
            return $arg;
        }

        $name = $this->providerName;
        $insert = $this->enableComposeInsert;
        $attach = $this->enableComposeAttach;
        $save = $this->enableAttachmentSave;

        $d = [];
        $d[] = "Plugin: $name";
        $d[] = "Description: Provides cloud integration with $name. By design, AI cannot perform any cloud actions or ".
            "change this plugin's settings. AI can only guide, and explain functionality and settings.";
        $d[] = '';
        $d[] = 'Functions:';
        $d[] = '- Saving files to cloud from emails on mail page: ' . ($save ? 'Enabled' : 'Disabled by admin');
        $d[] = '- Downloading files from cloud and attaching to outgoing emails on compose page: ' . 
            ($attach ? 'Enabled' : 'Disabled by admin');
        $d[] = '- Inserting links to cloud files into outgoing emails on compose page: ' . 
            ($insert ? 'Enabled' : 'Disabled by admin');
        $d[] = '';
        $d[] = 'Where:';
        ($insert || $attach) && ($d[] = 'Compose > Options & attachments');
        $save && ($d[] = 'Mail > message preview > attachment dropdown');
        $d[] = '';
        $d[] = 'Actions:';
        if ($insert || $attach) {
            if ($insert && $attach) {
                $d[] = "- Compose: Use the [$name] button to pick files from $name and either attach them to the ".
                    "message or insert a $name link into the message body.";
            } elseif ($attach) {
                $d[] = "- Compose: Use the [$name] button to pick files from $name and attach them to the message.";
            } else { // insert only
                $d[] = "- Compose: Use the [$name] button to pick files from $name and insert a $name link into the ".
                    "message body.";
            }
        }
        if ($save) {
            $d[] = "- Mail: For each attachment, a 'Save to $name' option appears alongside Open/Download to save ".
                "the file to $name.";
        }

        if ($insert || $attach) {
            $d[] = '';
            $d[] = 'Notes:';
            $this->enableComposeAttach && ($d[] = '- Attaching downloads a copy into the email.');
            $this->enableComposeInsert && ($d[] = '- Inserting a link does not attach the file.');
        }

        $arg['text'] = implode("\n", $d);

        return $arg;
    }

    /**
     * This gets called once for each cloud plugin (dropbox, google drive, webdav)
     *
     * @param array $arg
     * @return array
     */
    /**
     * This gets called once for each cloud plugin (dropbox, google drive, webdav)
     *
     * @param array $arg
     * @return array
     */
    public function renderPage(array $arg): array
    {
        // add the 'attach' buttons to the compose area
        if (($this->enableComposeAttach || $this->enableComposeInsert) &&
            ($i = strpos($arg['content'], 'compose-attachments')) !== false
        ) {
            $insert = false;
            $button = "";

            $label = \rcube::Q(
                $this->config->get($this->plugin . '_name', $this->gettext("xframework.{$this->plugin}_name"))
            );

            // Elastic
            if ($this->isElastic() && ($j = strpos($arg['content'], 'btn btn-secondary attach', $i)) !== false) {
                if (($insert = strpos($arg['content'], "</button>", $j)) !== false) {
                    $insert += 9;
                    $button = "<button class='btn btn-secondary $this->plugin-icon-button' ".
                        "data-popup='$this->plugin-compose-menu'>" . \rcmail::Q($label) . "</button>";
                }
                // Larry
            } else if (($j = strpos($arg['content'], "rcmail.upload_input('uploadform')", $i)) !== false) {
                if (($insert = strpos($arg['content'], "</a>", $j)) !== false) {
                    $insert += 4;
                    $button = "<a href='javascript:void(0)' class='button' ".
                        "onclick='UI.toggle_popup(\"$this->plugin-compose-menu\", event)'>$label</a>";
                }
            }

            if ($insert !== false) {
                $view = $this->view(
                    'elastic',
                    'xframework.cloud_attach_button',
                    [
                        'plugin' => $this->plugin,
                        'button' => $button,
                        'insertLabel' => $this->enableComposeInsert ?
                            \rcube::Q($this->gettext('xframework.insert_link')) : '',
                        'attachLabel' => $this->enableComposeAttach ?
                            \rcube::Q($this->gettext('xframework.download_and_attach')) : '',
                    ]
                );
                $arg['content'] = substr_replace($arg['content'], $view, $insert, 0);
            }
        }

        return $arg;
    }

    /**
     * Perform native Roundcube message size check.
     *
     * @param $size
     * @param string $mime
     * @throws \Exception
     */
    public function checkMessageSize($size, string $mime = 'application/octet-stream'): void
    {
        if ($error = \rcmail_action_mail_attachment_upload::check_message_size($size, $mime)) {
            throw new \Exception($error);
        }
    }

    /**
     * Downloads the file from cloud service and attach it to the message. The classes inheriting from this class must
     * provide the downloadFile() method
     */
    public function attachFiles(): void
    {
        $uploadId = \rcube_utils::get_input_value('uploadId', \rcube_utils::INPUT_POST);
        $composeId = \rcube_utils::get_input_value('composeId', \rcube_utils::INPUT_POST);
        $files = \rcube_utils::get_input_value('files', \rcube_utils::INPUT_POST);
        $sessionKey = 'compose_data_' . $composeId;
        $this->rcmail->output->reset();

        try {
            if (!$composeId || empty($_SESSION[$sessionKey])) {
                throw new \Exception('Invalid session variables');
            }

            if (empty($uploadId) || empty($files) || !is_array($files)) {
                throw new \Exception('Invalid upload data');
            }

            // initialize Roundcube's native attachment handler, it expects the compose ID in _id in post
            $_POST['_id'] = $composeId;
            \rcmail_action_mail_attachment_upload::init();

            foreach ($files as $file) {
                if (!is_array($file)) {
                    throw new \Exception('Invalid file data');
                }

                // download file(s) from the cloud (throws exception on error)
                $result = $this->downloadFile($file);

                // let Roundcube perform its native message-size check
                $this->checkMessageSize($result['size'], $result['mime']);

                $attachment = [
                    'path'     => false,
                    'data'     => $result['data'],
                    'size'     => $result['size'],
                    'name'     => $result['name'],
                    'mimetype' => $result['mime'],
                    'group'    => $composeId,
                ];

                // Roundcube 1.7+
                if (method_exists($this->rcmail, 'insert_uploaded_file')) {
                    if (!$this->rcmail->insert_uploaded_file($attachment, 'attachment_save')) {
                        throw new \Exception($attachment['error'] ?? $this->gettext('fileuploaderror'));
                    }
                }
                // Roundcube 1.6
                else {
                    $attachment = $this->rcmail->plugins->exec_hook('attachment_save', $attachment);

                    if (empty($attachment['status']) || !empty($attachment['abort'])) {
                        throw new \Exception($attachment['error'] ?? $this->gettext('fileuploaderror'));
                    }

                    unset($attachment['status'], $attachment['abort']);
                    $this->rcmail->session->append("$sessionKey.attachments", $attachment['id'], $attachment);
                }

                // let Roundcube build the attachment links/buttons/UI.
                \rcmail_action_mail_attachment_upload::attachment_success($attachment, $uploadId);
            }
        }
        catch (\Exception $e) {
            $message = $e->getMessage() ?: $this->gettext('fileuploaderror');
            $this->rcmail->output->command('display_message', $message, 'error');
            $this->rcmail->output->command('remove_from_attachment_list', $uploadId);
        }

        $this->rcmail->output->send();
    }

    /**
     * Clears the files from the temporary directory that have never been uploaded to cloud and have not been removed.
     */
    public function cleanUpOldAttachments(): void
    {
        $time = time();
        foreach (glob(Utils::addSlash($this->rcmail->config->get('temp_dir')) . 'xcloud_save_*') ?: [] as $file) {
            if ($time - filemtime($file) > 3600) {
                unlink($file);
            }
        }
    }

    /**
     * Send the temporary attachment file to the browser. Cloud requests this file directly from its server in order
     * to save it in the user's cloud folder.
     */
    public function deployAttachment(): void
    {
        try {
            if (!($code = \rcube_utils::get_input_value('xcloud_save', \rcube_utils::INPUT_GET))) {
                throw new \Exception();
            }

            if (!$this->verifyCode($code)) {
                throw new \Exception();
            }

            $file = Utils::addSlash($this->rcmail->config->get('temp_dir')) . 'xcloud_save_' . $code;

            if (!file_exists($file) || !($size = filesize($file))) {
                throw new \Exception();
            }

            header("Content-Length: $size");
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="xcloud_attachment"');
            header('Content-Transfer-Encoding: binary');

            if (ob_get_contents()) {
                @ob_end_clean();
            }

            if (!($fp = fopen($file, 'rb'))) {
                throw new \Exception();
            }

            while(!feof($fp)) {
                set_time_limit(30);
                echo fread($fp, 8192);
                flush();
                @ob_flush();
            }
            fclose($fp);
            unlink($file);
            exit();

        } catch (\Exception $e) {
            header('HTTP/1.0 404');
            exit();
        }
    }

    /**
     * Saves the attachment to a temporary dir with a name based on the random code generated in js. This is currently
     * only used by Dropbox. Dropbox will fetch this file via a url.
     *
     * Cloud savers save files by connecting to a server and fetching files from the given urls. We can't simply pass it
     * the attachment download url, because those urls only work when the user is logged in, while Cloud won't be logged
     * in when it requests the file. So we save the file to a temporary directory and enable direct access to it via a
     * url (?xcloud_save=[id]). After Cloud fetches the file, we remove the temp file.
     */
    public function saveAttachmentDeployFile(): void
    {
        $handle = false;

        try {
            $messageId = $this->input->get('messageId');
            $mbox = $this->input->get('mbox');
            $mimeId = $this->input->get('mimeId');
            $code = $this->input->get('code');

            if (!$messageId || !$mbox || !$mimeId || !$code) {
                throw new \Exception();
            }

            if (!$this->verifyCode($code)) {
                throw new \Exception('582994');
            }

            if (!($message = new \rcube_message($messageId, $mbox))) {
                throw new \Exception('381933');
            }

            if (empty($message->mime_parts[$mimeId])) {
                throw new \Exception('194881');
            }

            // save attachment to a temporary file
            $dir = Utils::addSlash($this->rcmail->config->get('temp_dir'));
            $handle = @fopen($dir . 'xcloud_save_' . $code, 'w');

            if ($handle === false) {
                throw new \Exception('183229');
            }

            if ($message->get_part_body($mimeId, false, 0, $handle) === false) {
                throw new \Exception('910043');
            }

            fclose($handle);

            // use the opportunity to delete the old attachments
            $this->cleanUpOldAttachments();

            Response::success();

        } catch (\Exception $e) {
            $handle && fclose($handle);
            Response::error('Cannot save attachment (' . ($e->getMessage() ?: '4811934') . ').');
        }
    }

    /**
     * Removes the saved attachment file from the temporary directory. This is called if the user cancels the dropbox
     * save dialog.
     */
    public function removeAttachmentDeployFile(): void
    {
        if (($code = $this->input->get('code')) &&
            $this->verifyCode($code)
        ) {
            @unlink(Utils::addSlash($this->rcmail->config->get('temp_dir')) . 'xcloud_save_' . $code);
            Response::success();
        }

        Response::error();
    }

    private function verifyCode($code): bool
    {
        return preg_match('/^[a-z0-9]{32}$/', $code);
    }
}