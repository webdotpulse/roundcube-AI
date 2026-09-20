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
        $this->register_action('plugin.roundcube_attachments_update_file', [$this, 'action_update_file']);

        // Settings main action
        $this->register_action('plugin.roundcube_attachments', [$this, 'action_settings']);

        // Register hooks
        $this->add_hook('settings_actions', [$this, 'settings_actions']);
        $this->add_hook('get_compose_response', [$this, 'hook_get_compose_response']);
        $this->add_hook('get_compose_responses', [$this, 'hook_get_compose_responses']);
        $this->add_hook('response_create', [$this, 'hook_response_create']);
        $this->add_hook('response_update', [$this, 'hook_response_update']);
        $this->add_hook('response_delete', [$this, 'hook_response_delete']);
        $this->add_hook('render_page', [$this, 'hook_render_page']);
        $this->add_hook('message_compose', [$this, 'hook_message_compose']);
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
    public function save_attachment(string $filename, string $content, ?string $mimetype = null, ?string $description = null): array
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
            'description' => trim((string)$description),
            'storage_name' => $storage_name,
            'size' => strlen($content),
            'mimetype' => $mimetype ?: 'application/octet-stream',
            'created' => time(),
            'updated' => time(),
        ];

        $meta = $this->load_meta();
        $meta[$id] = $record;
        $this->save_meta($meta);

        return ['status' => true, 'record' => $record];
    }

    /**
     * Update an attachment's description.
     */
    public function update_attachment_description(string $id, string $description): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            return false;
        }

        $meta = $this->load_meta();
        if (!isset($meta[$id])) {
            return false;
        }

        $meta[$id]['description'] = trim($description);
        $meta[$id]['updated'] = time();

        return $this->save_meta($meta);
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
                        'description' => $all_files[$att_id]['description'] ?? '',
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

        $template = (string) ($args['template'] ?? '');
        $action = (string) ($this->rc->action ?? '');
        $task = (string) ($this->rc->task ?? '');

        $is_compose = ($task === 'mail' && ($action === 'compose' || $template === 'compose' || strpos($action, 'compose') !== false));
        $is_responses = ($task === 'settings' && (strpos($action, 'response') !== false || in_array($action, ['responses', 'responseedit', 'response-edit', 'response-add', 'add-response', 'edit-response'], true)))
            || in_array($template, ['responses', 'responseedit'], true);
        $is_settings = ($task === 'settings' && (strpos($action, 'roundcube_attachments') !== false || strpos($template, 'roundcube_attachments') !== false));

        if ($is_compose || $is_responses || $is_settings) {
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

    /**
     * Hook: message_compose
     * Ensures scripts and stylesheets are loaded on any compose session
     */
    public function hook_message_compose(array $args): array
    {
        $this->include_script('roundcube_attachments.js');
        $this->include_stylesheet('roundcube_attachments.css');
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

        $raw_desc = rcube_utils::get_input_value('description', rcube_utils::INPUT_POST);
        $descriptions = is_array($raw_desc) ? $raw_desc : [$raw_desc];

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

            $file_desc = isset($descriptions[$i]) ? (string)$descriptions[$i] : (string)($descriptions[0] ?? '');
            $res = $this->save_attachment($orig_name, $content, $mime, $file_desc);
            if ($res['status']) {
                $rec = $res['record'];
                $rec['html_row'] = $this->render_file_row($rec);
                $uploaded_files[] = $rec;
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
                    'description' => $all_files[$att_id]['description'] ?? '',
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

    /**
     * Hook: settings_actions
     * Registers Serverbijlagen (Server Attachments) in Roundcube's settings navigation menu.
     */
    public function settings_actions(array $args): array
    {
        $args['actions'][] = [
            'action' => 'plugin.roundcube_attachments',
            'class'  => 'server-attachments',
            'label'  => 'roundcube_attachments.server_attachments',
            'title'  => 'roundcube_attachments.server_attachments',
            'domain' => 'roundcube_attachments',
        ];
        return $args;
    }

    /**
     * Action: Settings page for Serverbijlagen
     */
    public function action_settings(): void
    {
        $this->rc->output->set_pagetitle($this->gettext('server_attachments'));
        $this->include_script('roundcube_attachments.js');
        $this->include_stylesheet('roundcube_attachments.css');

        $this->register_handler('plugin.body', [$this, 'render_settings_view']);
        $this->rc->output->send('plugin');
    }

    /**
     * Safe HTML quote helper compatible with both Roundcube runtime and test suites.
     */
    public static function quote(?string $str): string
    {
        if (class_exists('html') && method_exists('html', 'quote')) {
            return html::quote((string)$str);
        }
        return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Render the native settings page HTML for Serverbijlagen
     */
    public function render_settings_view(): string
    {
        $files = $this->list_attachments();
        $total_files = count($files);
        $total_bytes = 0;
        foreach ($files as $f) {
            $total_bytes += (int)($f['size'] ?? 0);
        }

        $formatted_total_size = $this->format_filesize($total_bytes);

        $html = '<div id="rc-server-att-settings" class="rc-server-att-settings-container boxcontent uibox">';

        // Header / Summary Card
        $html .= '<div class="rc-server-att-header card mb-4 p-3">';
        $html .= '  <div class="d-flex justify-content-between align-items-center flex-wrap">';
        $html .= '    <div>';
        $html .= '      <h2 class="rc-server-att-title mb-1"><span class="icon">📁</span> ' . self::quote($this->gettext('settings_title')) . '</h2>';
        $html .= '      <p class="text-muted mb-0">' . self::quote($this->gettext('settings_description')) . '</p>';
        $html .= '    </div>';
        $html .= '    <div class="rc-server-att-stats mt-2 mt-md-0">';
        $html .= '      <span class="badge badge-primary px-3 py-2 mr-2" id="rc-stat-count"><span class="stat-number">' . $total_files . '</span> ' . self::quote($this->gettext('total_files')) . '</span>';
        $html .= '      <span class="badge badge-secondary px-3 py-2" id="rc-stat-size"><span class="stat-number">' . $formatted_total_size . '</span> ' . self::quote($this->gettext('storage_used')) . '</span>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        // Action Toolbar
        $html .= '<div class="rc-server-att-toolbar d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">';
        $html .= '  <div class="rc-server-att-search-box flex-grow-1 mr-3">';
        $html .= '    <div class="input-group">';
        $html .= '      <div class="input-group-prepend"><span class="input-group-text">🔍</span></div>';
        $html .= '      <input type="text" id="rc-settings-search" class="form-control" placeholder="' . self::quote($this->gettext('search_placeholder')) . '" autocomplete="off">';
        $html .= '      <div class="input-group-append"><button type="button" id="rc-settings-search-clear" class="btn btn-outline-secondary" style="display:none;">✕</button></div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '  <div class="rc-server-att-actions mt-2 mt-sm-0">';
        $html .= '    <button type="button" id="rc-btn-toggle-upload" class="btn btn-primary"><span class="icon">⬆</span> ' . self::quote($this->gettext('upload_file')) . '</button>';
        $html .= '  </div>';
        $html .= '</div>';

        // Dropzone / Upload Panel
        $html .= '<div id="rc-server-att-dropzone-panel" class="card mb-4" style="display:none;">';
        $html .= '  <div class="card-body">';
        $html .= '    <div id="rc-server-att-dropzone" class="rc-dropzone text-center p-4 border border-dashed rounded">';
        $html .= '      <div class="rc-dropzone-icon mb-2">☁️</div>';
        $html .= '      <p class="rc-dropzone-text mb-2 font-weight-bold">' . self::quote($this->gettext('drop_files_here')) . '</p>';
        $html .= '      <input type="file" id="rc-settings-file-input" class="d-none" multiple>';
        $html .= '      <button type="button" id="rc-settings-browse-btn" class="btn btn-sm btn-outline-primary mb-3">' . self::quote($this->gettext('upload_file')) . '</button>';
        $html .= '      <div class="row justify-content-center">';
        $html .= '        <div class="col-md-6 col-sm-8">';
        $html .= '          <div class="form-group mb-2 text-left">';
        $html .= '            <label for="rc-settings-file-desc" class="small text-muted">' . self::quote($this->gettext('upload_with_description')) . '</label>';
        $html .= '            <input type="text" id="rc-settings-file-desc" class="form-control form-control-sm" placeholder="' . self::quote($this->gettext('file_description_placeholder')) . '">';
        $html .= '          </div>';
        $html .= '        </div>';
        $html .= '      </div>';
        $html .= '      <div id="rc-settings-upload-progress" class="progress mt-3" style="display:none; height: 6px;">';
        $html .= '        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>';
        $html .= '      </div>';
        $html .= '      <div id="rc-settings-upload-status" class="small text-muted mt-2"></div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        // Table / Listing Container
        $html .= '<div class="card rc-server-att-table-card">';
        $html .= '  <div class="table-responsive">';
        $html .= '    <table id="rc-server-att-table" class="table table-hover table-striped mb-0">';
        $html .= '      <thead class="thead-light">';
        $html .= '        <tr>';
        $html .= '          <th style="width: 40px;"></th>';
        $html .= '          <th style="min-width: 200px;">' . self::quote($this->gettext('file_name')) . '</th>';
        $html .= '          <th style="min-width: 260px;">' . self::quote($this->gettext('file_description')) . '</th>';
        $html .= '          <th style="width: 110px;">' . self::quote($this->gettext('file_size')) . '</th>';
        $html .= '          <th style="width: 160px;">' . self::quote($this->gettext('file_date')) . '</th>';
        $html .= '          <th style="width: 140px; text-align: right;">' . self::quote($this->gettext('actions')) . '</th>';
        $html .= '        </tr>';
        $html .= '      </thead>';
        $html .= '      <tbody id="rc-server-att-tbody">';

        foreach ($files as $file) {
            $html .= $this->render_file_row($file);
        }

        $html .= '      </tbody>';
        $html .= '    </table>';
        $html .= '  </div>';

        // Empty state
        $empty_style = empty($files) ? '' : 'style="display:none;"';
        $html .= '  <div id="rc-server-att-empty" class="text-center p-5 text-muted" ' . $empty_style . '>';
        $html .= '    <div class="mb-3" style="font-size: 3rem;">📂</div>';
        $html .= '    <h4 class="mb-2 font-weight-normal">' . self::quote($this->gettext('no_attachments_found')) . '</h4>';
        $html .= '    <p class="mb-3">' . self::quote($this->gettext('settings_description')) . '</p>';
        $html .= '    <button type="button" class="btn btn-primary rc-btn-empty-upload">' . self::quote($this->gettext('upload_file')) . '</button>';
        $html .= '  </div>';

        $html .= '</div>'; // end table card
        $html .= '</div>'; // end container

        return $html;
    }

    /**
     * Render a single file row for the settings table.
     */
    public function render_file_row(array $file): string
    {
        $id = self::quote($file['id'] ?? '');
        $name = self::quote($file['name'] ?? '');
        $desc = self::quote($file['description'] ?? '');
        $size_bytes = (int)($file['size'] ?? 0);
        $size = $this->format_filesize($size_bytes);
        $mimetype = self::quote($file['mimetype'] ?? 'application/octet-stream');
        $created = !empty($file['created']) ? date('d-m-Y H:i', (int)$file['created']) : '-';
        $icon = $this->get_file_icon($name, $mimetype);
        $download_url = './?_task=settings&_action=plugin.roundcube_attachments_download&_id=' . urlencode($file['id'] ?? '');

        $has_desc = !empty(trim($file['description'] ?? ''));
        $desc_display = $has_desc ? $desc : '<span class="text-muted font-italic">' . self::quote($this->gettext('no_description')) . '</span>';

        $row = '<tr class="rc-server-att-row" data-id="' . $id . '" data-name="' . strtolower($name) . '" data-description="' . strtolower($desc) . '" data-size="' . $size_bytes . '">';
        $row .= '  <td class="text-center"><span class="rc-file-icon">' . $icon . '</span></td>';
        $row .= '  <td class="rc-col-filename">';
        $row .= '    <a href="' . $download_url . '" target="_blank" class="font-weight-bold rc-file-link" title="' . $name . '">' . $name . '</a>';
        $row .= '    <div class="small text-muted">' . $mimetype . '</div>';
        $row .= '  </td>';
        $row .= '  <td class="rc-col-description">';
        $row .= '    <div class="rc-desc-view d-flex align-items-center justify-content-between" title="' . self::quote($this->gettext('edit_description')) . '">';
        $row .= '      <span class="rc-desc-text text-break">' . $desc_display . '</span>';
        $row .= '      <button type="button" class="btn btn-sm btn-link rc-btn-edit-desc text-muted p-0 ml-2" title="' . self::quote($this->gettext('edit_description')) . '">✏️</button>';
        $row .= '    </div>';
        $row .= '    <div class="rc-desc-edit" style="display: none;">';
        $row .= '      <div class="input-group input-group-sm">';
        $row .= '        <input type="text" class="form-control form-control-sm rc-desc-input" value="' . $desc . '" placeholder="' . self::quote($this->gettext('file_description_placeholder')) . '">';
        $row .= '        <div class="input-group-append">';
        $row .= '          <button type="button" class="btn btn-success rc-btn-save-desc" title="' . self::quote($this->gettext('save_description')) . '">✓</button>';
        $row .= '          <button type="button" class="btn btn-secondary rc-btn-cancel-desc" title="' . self::quote($this->gettext('cancel')) . '">✕</button>';
        $row .= '        </div>';
        $row .= '      </div>';
        $row .= '    </div>';
        $row .= '  </td>';
        $row .= '  <td class="rc-col-size text-nowrap">' . $size . '</td>';
        $row .= '  <td class="rc-col-date text-nowrap">' . $created . '</td>';
        $row .= '  <td class="rc-col-actions text-right text-nowrap">';
        $row .= '    <a href="' . $download_url . '" class="btn btn-sm btn-outline-secondary rc-btn-download mr-1" title="' . self::quote($this->gettext('download')) . '" download>⬇</a>';
        $row .= '    <button type="button" class="btn btn-sm btn-outline-danger rc-btn-delete" title="' . self::quote($this->gettext('delete_attachment')) . '">🗑</button>';
        $row .= '  </td>';
        $row .= '</tr>';

        return $row;
    }

    /**
     * File icon helper.
     */
    public function get_file_icon(string $filename, string $mimetype): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf'], true)) return '📄';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) return '🖼️';
        if (in_array($ext, ['doc', 'docx', 'odt', 'rtf'], true)) return '📝';
        if (in_array($ext, ['xls', 'xlsx', 'ods', 'csv'], true)) return '📊';
        if (in_array($ext, ['zip', 'rar', 'tar', 'gz', '7z'], true)) return '📦';
        if (in_array($ext, ['txt', 'md', 'log'], true)) return '📃';
        if (in_array($ext, ['mp3', 'wav', 'ogg'], true)) return '🎵';
        if (in_array($ext, ['mp4', 'webm', 'mov', 'avi'], true)) return '🎬';
        return '📎';
    }

    /**
     * Format byte count into human-readable size.
     */
    public function format_filesize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        $size = $bytes / (1024 ** $i);
        return round($size, 1) . ' ' . $units[$i];
    }

    /**
     * AJAX Action: Update an attachment's description
     */
    public function action_update_file(): void
    {
        $this->rc->output->reset();
        $id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_POST);
        $description = rcube_utils::get_input_string('description', rcube_utils::INPUT_POST);

        if (empty($id) || !$this->update_attachment_description($id, (string)$description)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $this->gettext('error_not_found')]);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'message' => $this->gettext('description_updated_success'),
            'id' => $id,
            'description' => trim((string)$description),
        ]);
        exit;
    }
}

