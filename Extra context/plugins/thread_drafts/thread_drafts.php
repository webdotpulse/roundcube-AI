<?php

/**
 * Thread Drafts Plugin for Roundcube Webmail
 *
 * Includes active unsent drafts from the Drafts folder into conversation threads.
 *
 * @version 1.0.0
 * @author Webdotpulse
 * @license GNU GPLv3+
 */
class thread_drafts extends rcube_plugin
{
    public $task = 'mail|settings';

    /** @var rcmail */
    protected $rc;

    /** @var array Request-level cache for drafts headers */
    protected $drafts_cache = null;

    /**
     * Plugin initialization
     */
    public function init()
    {
        $this->rc = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        // Ensure storage fetches necessary threading headers
        $this->add_hook('storage_init', [$this, 'storage_init']);

        if ($this->rc->storage) {
            $this->ensure_fetch_headers();
        }

        if ($this->rc->task === 'mail') {
            if ($this->rc->action === 'print') {
                return;
            }

            if (!$this->is_enabled()) {
                return;
            }

            // Hook into message list construction
            $this->add_hook('messages_list', [$this, 'messages_list']);

            // Hook into message composition to sanitize draft UID parameters
            $this->add_hook('message_compose', [$this, 'message_compose']);

            // Hook into message viewing to show draft notice if applicable
            $this->add_hook('message_objects', [$this, 'message_objects']);

            // Include UI assets
            $this->include_script('thread_drafts.js');
            $this->include_stylesheet('thread_drafts.css');

            $skin = $this->rc->config->get('skin');
            if (file_exists(__DIR__ . '/skins/' . $skin . '/thread_drafts.css')) {
                $this->include_stylesheet('skins/' . $skin . '/thread_drafts.css');
            }

            // Export settings to client environment
            if (!empty($this->rc->output) && method_exists($this->rc->output, 'set_env')) {
                $this->rc->output->set_env('thread_drafts_show_root_badge', (bool) $this->get_config('thread_drafts_show_root_badge', true));
            }
        } elseif ($this->rc->task === 'settings') {
            $this->add_hook('preferences_list', [$this, 'preferences_list']);
            $this->add_hook('preferences_save', [$this, 'preferences_save']);
        }
    }

    /**
     * Check if the plugin is enabled in config and user prefs
     *
     * @return bool
     */
    public function is_enabled()
    {
        $dont_override = (array) $this->rc->config->get('dont_override', []);
        if (in_array('thread_drafts_enabled', $dont_override)) {
            return (bool) $this->rc->config->get('thread_drafts_enabled', true);
        }

        return (bool) $this->rc->config->get('thread_drafts_enabled', true);
    }

    /**
     * Helper to read plugin configuration with fallback to default
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get_config($name, $default = null)
    {
        return $this->rc->config->get($name, $default);
    }

    /**
     * Ensure storage options include MESSAGE-ID, IN-REPLY-TO, and REFERENCES
     */
    protected function ensure_fetch_headers()
    {
        if (!$this->rc->storage) {
            return;
        }

        $headers = ['MESSAGE-ID', 'IN-REPLY-TO', 'REFERENCES'];
        $opt = method_exists($this->rc->storage, 'get_option')
            ? $this->rc->storage->get_option('fetch_headers')
            : null;
        $existing = !empty($opt) ? explode(' ', (string) $opt) : [];
        $merged = array_unique(array_merge($existing, $headers));

        if (method_exists($this->rc->storage, 'set_options')) {
            $this->rc->storage->set_options(['fetch_headers' => implode(' ', $merged)]);
        }
    }

    /**
     * Handler for storage_init hook
     *
     * @param array $p Hook arguments
     *
     * @return array
     */
    public function storage_init($p)
    {
        $headers = ['MESSAGE-ID', 'IN-REPLY-TO', 'REFERENCES'];
        $existing = !empty($p['fetch_headers']) ? explode(' ', $p['fetch_headers']) : [];
        $merged = array_unique(array_merge($existing, $headers));
        $p['fetch_headers'] = implode(' ', $merged);

        return $p;
    }

    /**
     * Handler for messages_list hook.
     * Injects matching active drafts into conversation threads.
     *
     * @param array $args ['messages' => array, 'cols' => array]
     *
     * @return array
     */
    public function messages_list($args)
    {
        try {
            if (empty($args['messages']) || !is_array($args['messages'])) {
                return $args;
            }

            // Storage and threading checks
            if (!$this->rc->storage || !$this->rc->storage->get_threading()) {
                return $args;
            }

            $current_folder = $this->rc->storage->get_folder();
            $drafts_mbox = $this->rc->config->get('drafts_mbox');
            $trash_mbox = $this->rc->config->get('trash_mbox');
            $junk_mbox = $this->rc->config->get('junk_mbox');

            // Do not inject drafts into the Drafts folder itself or Trash/Junk
            if (empty($drafts_mbox) || $current_folder === $drafts_mbox
                || (!empty($trash_mbox) && $current_folder === $trash_mbox)
                || (!empty($junk_mbox) && $current_folder === $junk_mbox)
            ) {
                return $args;
            }

            // Fetch undeleted drafts from Drafts folder
            $draft_headers = $this->get_active_drafts($drafts_mbox);
            if (empty($draft_headers)) {
                return $args;
            }

            // Inject matching drafts into the thread hierarchy
            $args['messages'] = $this->insert_drafts_into_threads(
                $args['messages'],
                $draft_headers,
                $drafts_mbox,
                (bool) $this->get_config('thread_drafts_subject_fallback', false)
            );
        } catch (\Throwable $e) {
            rcube::raise_error([
                'code'    => 500,
                'type'    => 'php',
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'thread_drafts messages_list error: ' . $e->getMessage(),
            ], true, false);
        }

        return $args;
    }

    /**
     * Retrieve active undeleted draft message headers from the Drafts mailbox
     *
     * @param string $drafts_mbox
     *
     * @return array<rcube_message_header>
     */
    public function get_active_drafts($drafts_mbox)
    {
        if ($this->drafts_cache !== null) {
            return $this->drafts_cache;
        }

        $this->drafts_cache = [];

        if (empty($drafts_mbox) || !$this->rc->storage) {
            return $this->drafts_cache;
        }

        try {
            // Check if Drafts mailbox has any messages
            $count = $this->rc->storage->count($drafts_mbox, 'EXISTS');
            if (empty($count)) {
                return $this->drafts_cache;
            }

            $current_folder = $this->rc->storage->get_folder();

            // Search undeleted drafts
            $search_res = $this->rc->storage->search_once($drafts_mbox, 'UNDELETED');
            if (!$search_res || !method_exists($search_res, 'is_empty') || $search_res->is_empty()) {
                return $this->drafts_cache;
            }

            $uids = $search_res->get();
            if (empty($uids) || !is_array($uids)) {
                return $this->drafts_cache;
            }

            // Limit drafts to most recent N drafts for high performance
            $max_drafts = (int) $this->get_config('thread_drafts_max_drafts', 50);
            if ($max_drafts > 0 && count($uids) > $max_drafts) {
                $uids = array_slice($uids, -$max_drafts);
            }

            // Fetch draft headers
            $this->ensure_fetch_headers();
            $headers = $this->rc->storage->fetch_headers($drafts_mbox, $uids, false);

            // Ensure IMAP connection is restored to current folder
            if ($current_folder && $current_folder !== $drafts_mbox) {
                $this->rc->storage->set_folder($current_folder);
                if (!empty($this->rc->storage->conn) && method_exists($this->rc->storage->conn, 'select')) {
                    $this->rc->storage->conn->select($current_folder);
                }
            }

            if (is_array($headers)) {
                $this->drafts_cache = $headers;
            }
        } catch (\Throwable $e) {
            rcube::raise_error([
                'code'    => 500,
                'type'    => 'php',
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'thread_drafts error fetching drafts: ' . $e->getMessage(),
            ], true, false);
        }

        return $this->drafts_cache;
    }

    /**
     * Clean and normalize a Message-ID string
     *
     * @param string|null $id
     *
     * @return string
     */
    public static function clean_message_id($id)
    {
        if (!is_string($id) || !strlen($id)) {
            return '';
        }

        if (preg_match('/<([^>]+)>/', $id, $matches)) {
            return trim($matches[1]);
        }

        return trim($id, "<> \t\n\r\0\x0B");
    }

    /**
     * Parse References header into an array of clean Message-IDs
     *
     * @param string|array|null $refs
     *
     * @return array<string>
     */
    public static function parse_references($refs)
    {
        if (empty($refs)) {
            return [];
        }

        if (is_array($refs)) {
            $refs = implode(' ', $refs);
        }

        if (preg_match_all('/<([^>]+)>/', $refs, $matches)) {
            return array_map('trim', $matches[1]);
        }

        $parts = preg_split('/\s+/', trim($refs));

        return array_values(array_filter(array_map([self::class, 'clean_message_id'], $parts)));
    }

    /**
     * Normalize subject line by stripping Re:, Fwd:, brackets, and trimming
     *
     * @param string|null $subject
     *
     * @return string
     */
    public static function normalize_subject($subject)
    {
        if (!is_string($subject)) {
            return '';
        }

        $subject = trim(rcube_mime::decode_header($subject));

        // Remove prefixes like Re:, Fwd:, [Ticket #123], etc. iteratively
        while (preg_match('/^\s*(\[[^\]]*\]|\((?:re|fwd|fw)\)|\b(?:re|fwd|fw|aw|antw|wg)\b\s*:\s*)/i', $subject, $m)) {
            $subject = substr($subject, strlen($m[0]));
        }

        return strtolower(trim($subject));
    }

    /**
     * Insert matching drafts into the thread hierarchy of messages
     *
     * @param array<rcube_message_header> $messages Current message headers
     * @param array<rcube_message_header> $drafts   Draft message headers
     * @param string                      $drafts_mbox
     * @param bool                        $subject_fallback
     *
     * @return array<rcube_message_header>
     */
    public function insert_drafts_into_threads($messages, $drafts, $drafts_mbox, $subject_fallback = false)
    {
        if (empty($messages) || empty($drafts)) {
            return $messages;
        }

        // Build index of current message list by Message-ID and UID
        $by_msgid = [];
        $by_uid = [];
        $by_subject = [];

        foreach ($messages as $idx => $msg) {
            if (!empty($msg->messageID)) {
                $clean_id = self::clean_message_id($msg->messageID);
                if (strlen($clean_id)) {
                    $by_msgid[$clean_id] = $msg->uid;
                }
            }

            $by_uid[$msg->uid] = [
                'header' => $msg,
                'index'  => $idx,
            ];

            if ($subject_fallback && !empty($msg->subject)) {
                $norm_subj = self::normalize_subject($msg->subject);
                if (strlen($norm_subj) > 3) {
                    $by_subject[$norm_subj][] = $msg->uid;
                }
            }
        }

        // Identify matched drafts and determine their parent UID
        $matched_drafts = []; // parent_uid => array of draft headers

        foreach ($drafts as $draft) {
            $parent_uid = $this->find_matching_parent(
                $draft,
                $by_msgid,
                $by_subject,
                $subject_fallback
            );

            if ($parent_uid !== null && isset($by_uid[$parent_uid])) {
                $matched_drafts[$parent_uid][] = $draft;
            }
        }

        if (empty($matched_drafts)) {
            return $messages;
        }

        // Prepare and insert drafts into messages array
        $result = [];
        $len = count($messages);

        for ($i = 0; $i < $len; $i++) {
            $msg = $messages[$i];
            $result[] = $msg;
            $current_uid = $msg->uid;

            // If this message has matching drafts, insert them after this message and its descendants
            if (!empty($matched_drafts[$current_uid])) {
                $msg->has_children = true;

                // Mark thread root
                $root_header = $this->find_thread_root_header($result, count($result) - 1);
                if ($root_header) {
                    $root_header->has_children = true;
                    if (!isset($root_header->list_flags) || !is_array($root_header->list_flags)) {
                        $root_header->list_flags = [];
                    }
                    $root_header->list_flags['has_draft'] = 1;
                }

                // Collect any existing descendants of this message
                $parent_depth = (int) ($msg->depth ?? 0);
                while ($i + 1 < $len && isset($messages[$i + 1]->depth) && $messages[$i + 1]->depth > $parent_depth) {
                    $i++;
                    $result[] = $messages[$i];
                }

                // Now insert the drafts at the end of this branch
                foreach ($matched_drafts[$current_uid] as $draft) {
                    $prepared_draft = $this->prepare_draft_header($draft, $current_uid, $parent_depth + 1, $drafts_mbox);
                    $result[] = $prepared_draft;
                }
            }
        }

        return $result;
    }

    /**
     * Find parent message UID matching the given draft
     *
     * @param rcube_message_header $draft
     * @param array<string, mixed> $by_msgid
     * @param array<string, array> $by_subject
     * @param bool                 $subject_fallback
     *
     * @return mixed|null Parent UID or null
     */
    public function find_matching_parent($draft, $by_msgid, $by_subject, $subject_fallback = false)
    {
        // 1. Direct match on In-Reply-To
        if (!empty($draft->in_reply_to)) {
            $clean_in_reply_to = self::clean_message_id($draft->in_reply_to);
            if (strlen($clean_in_reply_to) && isset($by_msgid[$clean_in_reply_to])) {
                return $by_msgid[$clean_in_reply_to];
            }
        }

        // 2. Match on References list (from newest/closest to oldest)
        if (!empty($draft->references)) {
            $refs = self::parse_references($draft->references);
            if (!empty($refs)) {
                $rev_refs = array_reverse($refs);
                foreach ($rev_refs as $ref) {
                    if (isset($by_msgid[$ref])) {
                        return $by_msgid[$ref];
                    }
                }
            }
        }

        // 3. Fallback to normalized subject match
        if ($subject_fallback && !empty($draft->subject)) {
            $norm = self::normalize_subject($draft->subject);
            if (strlen($norm) > 3 && !empty($by_subject[$norm])) {
                // Return the latest message with this subject
                return end($by_subject[$norm]);
            }
        }

        return null;
    }

    /**
     * Find the root message header of a thread given an index in the array
     *
     * @param array<rcube_message_header> $list
     * @param int                         $idx
     *
     * @return rcube_message_header|null
     */
    protected function find_thread_root_header($list, $idx)
    {
        while ($idx >= 0) {
            if (empty($list[$idx]->depth)) {
                return $list[$idx];
            }
            $idx--;
        }

        return null;
    }

    /**
     * Prepare a draft header object for insertion into the message list
     *
     * @param rcube_message_header $draft
     * @param mixed                $parent_uid
     * @param int                  $depth
     * @param string               $drafts_mbox
     *
     * @return rcube_message_header
     */
    public function prepare_draft_header($draft, $parent_uid, $depth, $drafts_mbox)
    {
        $h = clone $draft;

        // Native Roundcube multi-folder format: <UID>-<MBOX>
        $numeric_uid = is_string($h->uid) && strpos($h->uid, '-') !== false
            ? explode('-', $h->uid)[0]
            : $h->uid;

        $h->uid = $numeric_uid . '-' . $drafts_mbox;
        $h->folder = $drafts_mbox;
        $h->parent_uid = $parent_uid;
        $h->depth = $depth;
        $h->has_children = false;
        $h->size = max(1, (int) $h->size);

        if (!is_array($h->flags)) {
            $h->flags = [];
        }
        $h->flags['skip_mbox_check'] = true;
        $h->flags['draft'] = true;
        $h->flags['seen'] = true;

        if (!is_array($h->list_flags)) {
            $h->list_flags = [];
        }
        $h->list_flags['skip_mbox_check'] = true;
        $h->list_flags['is_draft'] = 1;
        $h->list_flags['draft'] = 1;
        $h->list_flags['mbox'] = $drafts_mbox;

        if (!is_array($h->list_cols)) {
            $h->list_cols = [];
        }

        // Format smart From/To column for draft
        $recipient = !empty($h->to) ? $h->to : $h->get('to');
        if (!empty($recipient)) {
            $to_formatted = '';
            if (class_exists('rcmail_action_mail_index') && defined('INTL_IDNA_VARIANT_UTS46')) {
                try {
                    $to_formatted = rcmail_action_mail_index::address_string($recipient, 3, false, null, $h->charset, null, false);
                } catch (\Throwable $e) {
                    $to_formatted = '';
                }
            }
            if (empty($to_formatted)) {
                $to_formatted = class_exists('rcube') ? rcube::SQ($recipient) : htmlspecialchars($recipient, ENT_QUOTES);
            }
            $h->list_cols['fromto'] = $to_formatted;
        } else {
            $draft_label = method_exists($this, 'gettext') ? $this->gettext('draft') : 'Draft';
            $h->list_cols['fromto'] = '<em>' . (class_exists('rcube') ? rcube::Q($draft_label) : htmlspecialchars($draft_label)) . '</em>';
        }

        return $h;
    }

    /**
     * Handler for message_compose hook.
     * Sanitizes draft_uid parameter to strip any multi-folder suffix.
     *
     * @param array $args Compose parameters
     *
     * @return array
     */
    public function message_compose($args)
    {
        if (!empty($args['param']['draft_uid'])) {
            $uid = (string) $args['param']['draft_uid'];
            if (strpos($uid, '-') !== false) {
                [$clean_uid, ] = explode('-', $uid, 2);
                $args['param']['draft_uid'] = $clean_uid;
            }
        }

        return $args;
    }

    /**
     * Handler for message_objects hook.
     * Displays a notice banner when viewing a message that has an active draft reply.
     *
     * @param array $args ['content' => array, 'message' => rcube_message]
     *
     * @return array
     */
    public function message_objects($args)
    {
        try {
            if (!$this->get_config('thread_drafts_show_message_banner', true)) {
                return $args;
            }

            /** @var rcube_message $message */
            $message = $args['message'] ?? null;
            if (!$message || empty($message->headers->messageID)) {
                return $args;
            }

            $drafts_mbox = $this->rc->config->get('drafts_mbox');
            if (empty($drafts_mbox) || $message->folder === $drafts_mbox) {
                return $args;
            }

            $drafts = $this->get_active_drafts($drafts_mbox);
            if (empty($drafts)) {
                return $args;
            }

            $clean_msg_id = self::clean_message_id($message->headers->messageID);
            $found_draft = null;

            foreach ($drafts as $draft) {
                $in_reply_to = self::clean_message_id($draft->in_reply_to);
                if ($in_reply_to === $clean_msg_id) {
                    $found_draft = $draft;
                    break;
                }

                $refs = self::parse_references($draft->references);
                if (in_array($clean_msg_id, $refs, true)) {
                    $found_draft = $draft;
                    break;
                }
            }

            if ($found_draft) {
                $numeric_uid = is_string($found_draft->uid) && strpos($found_draft->uid, '-') !== false
                    ? explode('-', $found_draft->uid)[0]
                    : $found_draft->uid;

                $draft_url = $this->rc->url([
                    '_task'      => 'mail',
                    '_action'    => 'compose',
                    '_draft_uid' => $numeric_uid,
                    '_mbox'      => $drafts_mbox,
                ]);

                $notice_html = html::div(
                    ['class' => 'notice information draft-reply-notice'],
                    html::span(['class' => 'draft-reply-icon'], '&#9998;') . ' '
                    . html::span(null, rcube::Q($this->gettext('draft_notice')))
                    . ' &nbsp;'
                    . html::a(
                        [
                            'href'    => $draft_url,
                            'class'   => 'btn btn-sm btn-primary draft-resume-btn',
                            'onclick' => "if (window.rcmail) { rcmail.open_compose_step({_draft_uid: '{$numeric_uid}', _mbox: " . json_encode($drafts_mbox) . "}); return false; }",
                        ],
                        rcube::Q($this->gettext('resume_draft'))
                    )
                );

                array_unshift($args['content'], $notice_html);
            }
        } catch (\Throwable $e) {
            rcube::raise_error([
                'code'    => 500,
                'type'    => 'php',
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'thread_drafts message_objects error: ' . $e->getMessage(),
            ], true, false);
        }

        return $args;
    }

    /**
     * Handler for preferences_list hook
     *
     * @param array $args
     *
     * @return array
     */
    public function preferences_list($args)
    {
        if ($args['section'] === 'mailview') {
            $dont_override = (array) $this->rc->config->get('dont_override', []);

            if (!in_array('thread_drafts_enabled', $dont_override)) {
                $field_id = 'rcmfd_thread_drafts_enabled';
                $checkbox = new html_checkbox(['name' => '_thread_drafts_enabled', 'id' => $field_id, 'value' => 1]);

                $args['blocks']['main']['options']['thread_drafts_enabled'] = [
                    'title'   => html::label($field_id, rcube::Q($this->gettext('thread_drafts_option'))),
                    'content' => $checkbox->show($this->is_enabled() ? 1 : 0),
                ];
            }

            if (!in_array('thread_drafts_show_message_banner', $dont_override)) {
                $field_id = 'rcmfd_thread_drafts_banner';
                $checkbox = new html_checkbox(['name' => '_thread_drafts_banner', 'id' => $field_id, 'value' => 1]);

                $args['blocks']['main']['options']['thread_drafts_banner'] = [
                    'title'   => html::label($field_id, rcube::Q($this->gettext('thread_drafts_banner_option'))),
                    'content' => $checkbox->show($this->get_config('thread_drafts_show_message_banner', true) ? 1 : 0),
                ];
            }
        }

        return $args;
    }

    /**
     * Handler for preferences_save hook
     *
     * @param array $args
     *
     * @return array
     */
    public function preferences_save($args)
    {
        if ($args['section'] === 'mailview') {
            $dont_override = (array) $this->rc->config->get('dont_override', []);

            if (!in_array('thread_drafts_enabled', $dont_override)) {
                $args['prefs']['thread_drafts_enabled'] = !empty($_POST['_thread_drafts_enabled']);
            }

            if (!in_array('thread_drafts_show_message_banner', $dont_override)) {
                $args['prefs']['thread_drafts_show_message_banner'] = !empty($_POST['_thread_drafts_banner']);
            }
        }

        return $args;
    }
}
