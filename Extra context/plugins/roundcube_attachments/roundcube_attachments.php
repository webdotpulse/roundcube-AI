<?php

/**
 * Roundcube Server Attachments & Reactions Enhancements Plugin
 *
 * Allows saving attachments on the server, easily attaching them to compose emails,
 * and assigning attachments and subjects to canned responses (reactions).
 *
 * @version 1.0.0
 * @author Webdotpulse
 * @license GNU GPLv3+
 */

declare(strict_types=1);

class roundcube_attachments extends rcube_plugin
{
    public $task = 'mail|settings';

    /** @var rcmail */
    protected $rc;

    /** @var array Request-level cache */
    protected $storage_dir = null;

    /**
     * Plugin initialization
     */
    public function init(): void
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        // Register AJAX endpoints
        $this->register_action('plugin.roundcube_attachments_list', [$this, 'action_list']);
        $this->register_action('plugin.roundcube_attachments_upload', [$this, 'action_upload']);
        $this->register_action('plugin.roundcube_attachments_delete', [$this, 'action_delete']);
        $this->register_action('plugin.roundcube_attachments_download', [$this, 'action_download']);
        $this->register_action('plugin.roundcube_attachments_attach_to_compose', [$this, 'action_attach_to_compose']);
        $this->register_action('plugin.roundcube_attachments_save_meta', [$this, 'action_save_meta']);
        $this->register_action('plugin.roundcube_attachments_get_meta', [$this, 'action_get_meta']);

        // Register hooks
        $this->add_hook('get_compose_response', [$this, 'hook_get_compose_response']);
        $this->add_hook('get_compose_responses', [$this, 'hook_get_compose_responses']);
        $this->add_hook('response_create', [$this, 'hook_response_create']);
        $this->add_hook('response_update', [$this, 'hook_response_update']);
        $this->add_hook('response_delete', [$this, 'hook_response_delete']);
        $this->add_hook('render_page', [$this, 'hook_render_page']);
    }

    /**
     * Get base directory for server attachments storage.
     */
    public function get_storage_dir(): string
    {
        if ($this->storage_dir !== null) {
            return $this->storage_dir;
        }

        $config_dir = $this->rc->config->get('roundcube_attachments_dir');
        if (!empty($config_dir) && is_string($config_dir)) {
            $dir = rtrim($config_dir, '/\\');
        } elseif (defined('RCUBE_INSTALL_PATH')) {
            $dir = rtrim(RCUBE_INSTALL_PATH, '/\\') . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'roundcube_attachments' . DIRECTORY_SEPARATOR . 'data';
        } else {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
        }

        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        // Ensure directory security: .htaccess and index.php
        $htaccess = $dir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "# Deny direct web access to server attachments\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
        }

        $index_php = $dir . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($index_php)) {
            @file_put_contents($index_php, "<?php http_response_code(403); exit('Access denied.'); ?>\n");
        }

        $this->storage_dir = $dir;
        return $dir;
    }

    /**
     * Get user-isolated storage directory.
     */
    public function get_user_storage_dir(): string
    {
        $user_id = $this->rc->user ? $this->rc->user->ID : null;
        $id_str = $user_id ? 'user_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$user_id) : 'guest';
        $user_dir = $this->get_storage_dir() . DIRECTORY_SEPARATOR . $id_str;

        if (!is_dir($user_dir)) {
            @mkdir($user_dir, 0700, true);
        }

        $files_dir = $user_dir . DIRECTORY_SEPARATOR . 'files';
        if (!is_dir($files_dir)) {
            @mkdir($files_dir, 0700, true);
        }

        return $user_dir;
    }

    /**
     * Load metadata for user's stored attachments.
     */
    public function load_meta(): array
    {
        $meta_file = $this->get_user_storage_dir() . DIRECTORY_SEPARATOR . 'meta.json';
        if (file_exists($meta_file)) {
            $content = @file_get_contents($meta_file);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        return [];
    }

    /**
     * Save metadata for user's stored attachments.
     */
    public function save_meta(array $meta): bool
    {
        $meta_file = $this->get_user_storage_dir() . DIRECTORY_SEPARATOR . 'meta.json';
        return (bool) @file_put_contents($meta_file, json_encode($meta, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Sanitize and validate filename and extension.
     */
    public function sanitize_filename(string $filename): array
    {
        // Strip null bytes and control chars
        $clean = preg_replace('/[\x00-\x1f\x7f]/', '', $filename);
        $clean = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $clean));

        if (trim($clean) === '' || $clean === '.' || $clean === '..') {
            $clean = 'attachment_' . time() . '.dat';
        }

        $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));

        // Security check: blacklist dangerous extensions
        $denied = $this->rc->config->get('roundcube_attachments_denied_extensions', [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'sh', 'bash', 'zsh', 'exe', 'bat', 'cmd', 'com', 'msi', 'bin',
            'cgi', 'pl', 'py', 'rb', 'jar', 'vbs', 'ps1'
        ]);

        if (in_array($ext, $denied, true)) {
            return ['valid' => false, 'error' => 'error_file_type', 'filename' => $clean];
        }

        return ['valid' => true, 'filename' => $clean, 'extension' => $ext];
    }

    /**
     * Store an attachment file on the server.
     */
    public function save_attachment(string $filename, string $content, ?string $mimetype = null): array
    {
        $max_size = (int) $this->rc->config->get('roundcube_attachments_max_filesize', 25 * 1024 * 1024);
        if (strlen($content) > $max_size) {
            return ['status' => false, 'error' => 'error_file_size'];
        }

        $check = $this->sanitize_filename($filename);
        if (!$check['valid']) {
            return ['status' => false, 'error' => $check['error']];
        }

        $safe_name = $check['filename'];
        $user_dir = $this->get_user_storage_dir();
        $files_dir = $user_dir . DIRECTORY_SEPARATOR . 'files';

        $id = 'att_' . bin2hex(random_bytes(10)) . '_' . time();
        $storage_name = $id . '.dat'; // store with safe .dat extension
        $storage_path = $files_dir . DIRECTORY_SEPARATOR . $storage_name;

        if (@file_put_contents($storage_path, $content, LOCK_EX) === false) {
            return ['status' => false, 'error' => 'error_file_upload'];
        }

        @chmod($storage_path, 0600);

        if (empty($mimetype)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = $finfo ? finfo_buffer($finfo, $content) : 'application/octet-stream';
            if ($finfo) {
                finfo_close($finfo);
            }
        }

        $record = [
            'id' => $id,
            'name' => $safe_name,
            'storage_name' => $storage_name,
            'size' => strlen($content),
            'mimetype' => $mimetype ?: 'application/octet-stream',
            'created' => time(),
        ];

        $meta = $this->load_meta();
        $meta[$id] = $record;
        $this->save_meta($meta);

        return ['status' => true, 'record' => $record];
    }

    /**
     * List user attachments.
     */
    public function list_attachments(): array
    {
        $meta = $this->load_meta();
        uasort($meta, function ($a, $b) {
            return ($b['created'] ?? 0) <=> ($a['created'] ?? 0);
        });
        return array_values($meta);
    }

    /**
     * Get single attachment metadata and content.
     */
    public function get_attachment(string $id): ?array
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            return null;
        }

        $meta = $this->load_meta();
        if (!isset($meta[$id])) {
            return null;
        }

        $record = $meta[$id];
        $storage_path = $this->get_user_storage_dir() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $record['storage_name'];

        if (!file_exists($storage_path)) {
            return null;
        }

        $record['data'] = @file_get_contents($storage_path);
        return $record;
    }

    /**
     * Delete an attachment from server storage.
     */
    public function delete_attachment(string $id): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            return false;
        }

        $meta = $this->load_meta();
        if (!isset($meta[$id])) {
            return false;
        }

        $record = $meta[$id];
        $storage_path = $this->get_user_storage_dir() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $record['storage_name'];

        if (file_exists($storage_path)) {
            @unlink($storage_path);
        }

        unset($meta[$id]);
        $this->save_meta($meta);
        return true;
    }

    /**
     * Retrieve response metadata (subject & attachments) from user preferences.
     */
    public function get_response_meta($response_id): array
    {
        $key = (string) $response_id;
        $prefs = $this->rc->user ? $this->rc->user->get_prefs() : [];
        $all_meta = $prefs['roundcube_attachments_responses_meta'] ?? [];

        if (isset($all_meta[$key]) && is_array($all_meta[$key])) {
            return [
                'subject' => (string) ($all_meta[$key]['subject'] ?? ''),
                'attachments' => is_array($all_meta[$key]['attachments'] ?? null) ? $all_meta[$key]['attachments'] : [],
            ];
        }

        return ['subject' => '', 'attachments' => []];
    }

    /**
     * Save response metadata (subject & attachments) in user preferences.
     */
    public function save_response_meta($response_id, string $subject, array $attachment_ids): bool
    {
        if (empty($response_id)) {
            return false;
        }

        $key = (string) $response_id;
        $prefs = $this->rc->user ? $this->rc->user->get_prefs() : [];
        $all_meta = $prefs['roundcube_attachments_responses_meta'] ?? [];

        $all_meta[$key] = [
            'subject' => trim($subject),
            'attachments' => array_values(array_unique(array_filter($attachment_ids, 'is_string'))),
            'updated' => time(),
        ];

        if ($this->rc->user) {
            return (bool) $this->rc->user->save_prefs(['roundcube_attachments_responses_meta' => $all_meta]);
        }
        return false;
    }

    /**
     * Delete response metadata when a response is deleted.
     */
    public function delete_response_meta($response_id): bool
    {
        if (empty($response_id)) {
            return false;
        }

        $key = (string) $response_id;
        $prefs = $this->rc->user ? $this->rc->user->get_prefs() : [];
        $all_meta = $prefs['roundcube_attachments_responses_meta'] ?? [];

        if (isset($all_meta[$key])) {
            unset($all_meta[$key]);
            if ($this->rc->user) {
                return (bool) $this->rc->user->save_prefs(['roundcube_attachments_responses_meta' => $all_meta]);
            }
        }
        return true;
    }

    // ==========================================
    // Plugin Hooks
    // ==========================================

    /**
     * Hook: get_compose_response
     * Enriches the response record with subject and attachments metadata.
     */
    public function hook_get_compose_response(array $args): array
    {
        $id = $args['id'] ?? null;
        if (!empty($id) && !empty($args['record']) && is_array($args['record'])) {
            $meta = $this->get_response_meta($id);
            $args['record']['subject'] = $meta['subject'];
            $args['record']['attachments'] = $meta['attachments'];

            // Attach expanded file metadata for convenience
            $files_meta = [];
            $all_files = $this->load_meta();
            foreach ($meta['attachments'] as $att_id) {
                if (isset($all_files[$att_id])) {
                    $files_meta[] = [
                        'id' => $att_id,
                        'name' => $all_files[$att_id]['name'],
                        'size' => $all_files[$att_id]['size'],
                        'mimetype' => $all_files[$att_id]['mimetype'],
                    ];
                }
            }
            $args['record']['attachments_files'] = $files_meta;
        }

        return $args;
    }

    /**
     * Hook: get_compose_responses
     * Enriches the list of responses with indicators if attachments exist.
     */
    public function hook_get_compose_responses(array $args): array
    {
        if (!empty($args['list']) && is_array($args['list'])) {
            foreach ($args['list'] as &$item) {
                if (isset($item['id'])) {
                    $meta = $this->get_response_meta($item['id']);
                    $item['has_attachments'] = !empty($meta['attachments']);
                    $item['attachments_count'] = count($meta['attachments']);
                    $item['subject'] = $meta['subject'];
                }
            }
        }
        return $args;
    }

    /**
     * Hook: response_create
     * Captures submitted _subject and _attachments when a new reaction is created.
     */
    public function hook_response_create(array $args): array
    {
        $subject = rcube_utils::get_input_string('_subject', rcube_utils::INPUT_POST);
        $attachments = rcube_utils::get_input_value('_attachments', rcube_utils::INPUT_POST);

        if (!is_array($attachments) && is_string($attachments) && $attachments !== '') {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [];
        }

        $_SESSION['roundcube_attachments_pending_response_meta'] = [
            'subject' => (string)$subject,
            'attachments' => is_array($attachments) ? $attachments : [],
        ];

        return $args;
    }

    /**
     * Hook: response_update
     * Persists updated _subject and _attachments for existing reaction.
     */
    public function hook_response_update(array $args): array
    {
        $id = $args['id'] ?? rcube_utils::get_input_string('_id', rcube_utils::INPUT_POST);
        if (!empty($id)) {
            $subject = rcube_utils::get_input_string('_subject', rcube_utils::INPUT_POST);
            $attachments = rcube_utils::get_input_value('_attachments', rcube_utils::INPUT_POST);

            if (!is_array($attachments) && is_string($attachments) && $attachments !== '') {
                $decoded = json_decode($attachments, true);
                $attachments = is_array($decoded) ? $decoded : [];
            }

            $this->save_response_meta($id, (string)$subject, is_array($attachments) ? $attachments : []);
        }

        return $args;
    }

    /**
     * Hook: response_delete
     * Removes response metadata when a reaction is deleted.
     */
    public function hook_response_delete(array $args): array
    {
        $id = $args['id'] ?? rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP);
        if (!empty($id)) {
            $this->delete_response_meta($id);
        }
        return $args;
    }

    /**
     * Hook: render_page
     * Injects client JS/CSS assets and synchronizes newly created response ID.
     */
    public function hook_render_page(array $args): array
    {
        // Handle pending reaction metadata from response_create
        if (!empty($_SESSION['roundcube_attachments_pending_response_meta'])) {
            $new_id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP);
            if (empty($new_id)) {
                $new_id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GET) ?: rcube_utils::get_input_string('_id', rcube_utils::INPUT_POST);
            }
            if (!empty($new_id)) {
                $pending = $_SESSION['roundcube_attachments_pending_response_meta'];
                $this->save_response_meta($new_id, $pending['subject'] ?? '', $pending['attachments'] ?? []);
                unset($_SESSION['roundcube_attachments_pending_response_meta']);
            }
        }

        $template = $args['template'] ?? '';
        $action = $this->rc->action;
        $task = $this->rc->task;

        $is_compose = ($task === 'mail' && $action === 'compose');
        $is_responses = ($task === 'settings' && (strpos($action, 'response') !== false || in_array($action, ['responses', 'responseedit', 'response-edit', 'response-add', 'add-response', 'edit-response'], true)))
            || in_array($template, ['responses', 'responseedit'], true);

        if ($is_compose || $is_responses) {
            $this->include_script('roundcube_attachments.js');
            $this->include_stylesheet('roundcube_attachments.css');

            $current_response_id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP)
                ?: rcube_utils::get_input_string('id', rcube_utils::INPUT_GP);
            $current_meta = $current_response_id ? $this->get_response_meta($current_response_id) : ['subject' => '', 'attachments' => []];

            $all_files = $this->load_meta();
            $current_files = [];
            foreach ($current_meta['attachments'] as $att_id) {
                if (isset($all_files[$att_id])) {
                    $current_files[] = [
                        'id' => $att_id,
                        'name' => $all_files[$att_id]['name'],
                        'size' => $all_files[$att_id]['size'],
                        'mimetype' => $all_files[$att_id]['mimetype'],
                    ];
                }
            }

            $this->rc->output->set_env('roundcube_attachments', [
                'enabled' => true,
                'is_compose' => $is_compose,
                'is_responses' => $is_responses,
                'response_id' => $current_response_id,
                'response_meta' => [
                    'subject' => $current_meta['subject'],
                    'attachments' => $current_meta['attachments'],
                    'files' => $current_files,
                ],
                'max_size' => (int) $this->rc->config->get('roundcube_attachments_max_filesize', 25 * 1024 * 1024),
            ]);
        }

        return $args;
    }

    // ==========================================
    // AJAX Action Handlers
    // ==========================================

    /**
     * AJAX Action: List server attachments
     */
    public function action_list(): void
    {
        $this->rc->output->reset();
        $files = $this->list_attachments();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'files' => $files]);
        exit;
    }

    /**
     * AJAX Action: Upload a new file to server attachments
     */
    public function action_upload(): void
    {
        $this->rc->output->reset();

        if (empty($_FILES['_attachments']['tmp_name'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $this->gettext('error_file_upload')]);
            exit;
        }

        $uploaded_files = [];
        $errors = [];

        // Handle single or multiple file uploads
        $files = $_FILES['_attachments'];
        $is_array = is_array($files['tmp_name']);
        $count = $is_array ? count($files['tmp_name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $tmp_name = $is_array ? $files['tmp_name'][$i] : $files['tmp_name'];
            $orig_name = $is_array ? $files['name'][$i] : $files['name'];
            $mime = $is_array ? ($files['type'][$i] ?? null) : ($files['type'] ?? null);
            $err = $is_array ? $files['error'][$i] : $files['error'];

            if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($tmp_name)) {
                $errors[] = $this->gettext('error_file_upload') . " ({$orig_name})";
                continue;
            }

            $content = @file_get_contents($tmp_name);
            if ($content === false) {
                $errors[] = $this->gettext('error_file_upload') . " ({$orig_name})";
                continue;
            }

            $res = $this->save_attachment($orig_name, $content, $mime);
            if ($res['status']) {
                $uploaded_files[] = $res['record'];
            } else {
                $errors[] = $this->gettext($res['error']) . " ({$orig_name})";
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        if (!empty($uploaded_files)) {
            echo json_encode([
                'status' => 'success',
                'files' => $uploaded_files,
                'message' => $this->gettext('file_uploaded_success'),
                'errors' => $errors,
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => !empty($errors) ? implode(' ', $errors) : $this->gettext('error_file_upload'),
            ]);
        }
        exit;
    }

    /**
     * AJAX Action: Delete an attachment from server storage
     */
    public function action_delete(): void
    {
        $this->rc->output->reset();
        $id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_POST);

        if (empty($id) || !$this->delete_attachment($id)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $this->gettext('error_not_found')]);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'message' => $this->gettext('file_deleted_success')]);
        exit;
    }

    /**
     * Action: Download/view an attachment
     */
    public function action_download(): void
    {
        $id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GET);
        $file = $id ? $this->get_attachment($id) : null;

        if (!$file) {
            http_response_code(404);
            exit('File not found.');
        }

        $this->rc->output->reset();
        header('Content-Type: ' . $file['mimetype']);
        header('Content-Length: ' . $file['size']);
        header('Content-Disposition: inline; filename="' . rawurlencode($file['name']) . '"');
        header('Cache-Control: private, max-age=3600');
        echo $file['data'];
        exit;
    }

    /**
     * AJAX Action: Attach selected server attachment(s) to active compose session
     */
    public function action_attach_to_compose(): void
    {
        $compose_id = rcube_utils::get_input_string('composeId', rcube_utils::INPUT_POST);
        $file_ids = rcube_utils::get_input_value('fileIds', rcube_utils::INPUT_POST);
        $upload_id = rcube_utils::get_input_string('uploadId', rcube_utils::INPUT_POST);

        if (!is_array($file_ids) && is_string($file_ids) && $file_ids !== '') {
            $decoded = json_decode($file_ids, true);
            $file_ids = is_array($decoded) ? $decoded : [$file_ids];
        }

        $session_key = 'compose_data_' . $compose_id;
        $this->rc->output->reset();

        try {
            if (!$compose_id || empty($_SESSION[$session_key])) {
                throw new Exception('Invalid compose session');
            }

            if (empty($file_ids) || !is_array($file_ids)) {
                throw new Exception('No files specified');
            }

            // Initialize Roundcube's native attachment handler if available
            $_POST['_id'] = $compose_id;
            $has_native_upload = class_exists('rcmail_action_mail_attachment_upload');
            if ($has_native_upload) {
                rcmail_action_mail_attachment_upload::init();
            }

            $attached_count = 0;
            $attached_files = [];

            foreach ($file_ids as $fid) {
                if (!is_string($fid)) {
                    continue;
                }

                $file = $this->get_attachment($fid);
                if (!$file) {
                    continue;
                }

                $attachment = [
                    'path'     => false,
                    'data'     => $file['data'],
                    'size'     => $file['size'],
                    'name'     => $file['name'],
                    'mimetype' => $file['mimetype'],
                    'group'    => $compose_id,
                ];

                // Native Roundcube 1.7+
                if (method_exists($this->rc, 'insert_uploaded_file')) {
                    if (!$this->rc->insert_uploaded_file($attachment, 'attachment_save')) {
                        continue;
                    }
                }
                // Native Roundcube 1.4 - 1.6
                else {
                    if (isset($this->rc->plugins) && method_exists($this->rc->plugins, 'exec_hook')) {
                        $attachment = $this->rc->plugins->exec_hook('attachment_save', $attachment);
                        if (empty($attachment['status']) || !empty($attachment['abort'])) {
                            continue;
                        }
                        unset($attachment['status'], $attachment['abort']);
                    }
                    if (empty($attachment['id'])) {
                        $attachment['id'] = md5($attachment['name'] . microtime());
                    }
                    if (isset($this->rc->session) && method_exists($this->rc->session, 'append')) {
                        $this->rc->session->append("$session_key.attachments", $attachment['id'], $attachment);
                    } else {
                        $_SESSION[$session_key]['attachments'][$attachment['id']] = $attachment;
                    }
                }

                // Render in compose UI
                $item_upload_id = $upload_id ?: 'rcm_att_' . $attachment['id'];
                if ($has_native_upload) {
                    rcmail_action_mail_attachment_upload::attachment_success($attachment, $item_upload_id);
                } else {
                    $this->rc->output->command('add2attachment_list', "rcmfile" . $attachment['id'], [
                        'name' => $attachment['name'],
                        'size' => $attachment['size'],
                        'mimetype' => $attachment['mimetype'],
                        'classname' => rcube_utils::file2class($attachment['mimetype'], $attachment['name']),
                        'complete' => true,
                    ]);
                }

                $attached_files[] = [
                    'id' => $attachment['id'],
                    'name' => $attachment['name'],
                    'size' => $attachment['size'],
                ];
                $attached_count++;
            }

            if ($attached_count > 0) {
                $this->rc->output->command('display_message', $this->gettext('file_attached_success'), 'confirmation');
            } else {
                throw new Exception($this->gettext('error_not_found'));
            }
        } catch (Exception $e) {
            $this->rc->output->command('display_message', $e->getMessage(), 'error');
            if ($upload_id) {
                $this->rc->output->command('remove_from_attachment_list', $upload_id);
            }
        }

        $this->rc->output->send();
    }

    /**
     * AJAX Action: Save response metadata directly
     */
    public function action_save_meta(): void
    {
        $this->rc->output->reset();
        $id = rcube_utils::get_input_string('response_id', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $attachments = rcube_utils::get_input_value('attachments', rcube_utils::INPUT_POST);

        if (!is_array($attachments) && is_string($attachments) && $attachments !== '') {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [];
        }

        if (empty($id)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Missing response ID']);
            exit;
        }

        $saved = $this->save_response_meta($id, (string)$subject, is_array($attachments) ? $attachments : []);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => $saved ? 'success' : 'error']);
        exit;
    }

    /**
     * AJAX Action: Get metadata (subject & attachments) for a canned response
     */
    public function action_get_meta(): void
    {
        $this->rc->output->reset();
        $id = rcube_utils::get_input_string('response_id', rcube_utils::INPUT_GP)
            ?: rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP);

        if (empty($id)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Missing response ID']);
            exit;
        }

        $meta = $this->get_response_meta($id);
        $all_files = $this->load_meta();
        $files = [];
        foreach ($meta['attachments'] as $att_id) {
            if (isset($all_files[$att_id])) {
                $files[] = [
                    'id' => $att_id,
                    'name' => $all_files[$att_id]['name'],
                    'size' => $all_files[$att_id]['size'],
                    'mimetype' => $all_files[$att_id]['mimetype'],
                ];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'response_id' => $id,
            'subject' => $meta['subject'] ?? '',
            'attachments' => $meta['attachments'] ?? [],
            'files' => $files,
        ]);
        exit;
    }
}
