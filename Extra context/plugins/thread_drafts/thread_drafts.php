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

    /** @var array Request-level cache for sent replies headers */
    protected $replies_cache = null;

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

            if ($this->is_auto_collapse_inbox_enabled()) {
                $mbox = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GPC, true);
                if (!strlen((string)$mbox)) {
                    $mbox = isset($_SESSION['mbox']) && strlen((string)$_SESSION['mbox']) ? $_SESSION['mbox'] : 'INBOX';
                }
                $is_inbox = empty($mbox) || strtoupper((string)$mbox) === 'INBOX';
                if ($is_inbox) {
                    $this->rc->config->set('autoexpand_threads', 0);
                    if (!empty($this->rc->output) && method_exists($this->rc->output, 'set_env')) {
                        $this->rc->output->set_env('autoexpand_threads', 0);
                    }
                }
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
                $this->rc->output->set_env('thread_drafts_show_reply_badge', (bool) $this->get_config('thread_drafts_show_reply_badge', true));
                $this->rc->output->set_env('thread_drafts_auto_collapse_inbox', (bool) $this->is_auto_collapse_inbox_enabled());
                if ($drafts_mbox = $this->rc->config->get('drafts_mbox')) {
                    $this->rc->output->set_env('drafts_mailbox', $drafts_mbox);
                }
                if ($sent_mbox = $this->rc->config->get('sent_mbox')) {
                    $this->rc->output->set_env('sent_mailbox', $sent_mbox);
                }
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
        if ($this->rc && $this->rc->config) {
            $dont_override = (array) $this->rc->config->get('dont_override', []);
            if (in_array('thread_drafts_enabled', $dont_override)) {
                return (bool) $this->rc->config->get('thread_drafts_enabled', true);
            }
            return (bool) $this->rc->config->get('thread_drafts_enabled', true);
        }

        return true;
    }

    /**
     * Check if drafts threading is enabled
     *
     * @return bool
     */
    public function is_drafts_enabled()
    {
        if ($this->rc && $this->rc->config) {
            $dont_override = (array) $this->rc->config->get('dont_override', []);
            if (in_array('thread_drafts_include_drafts', $dont_override)) {
                return (bool) $this->rc->config->get('thread_drafts_include_drafts', true);
            }
            return (bool) $this->rc->config->get('thread_drafts_include_drafts', true);
        }

        return true;
    }

    /**
     * Check if sent replies threading is enabled
     *
     * @return bool
     */
    public function is_replies_enabled()
    {
        if ($this->rc && $this->rc->config) {
            $dont_override = (array) $this->rc->config->get('dont_override', []);
            if (in_array('thread_drafts_include_replies', $dont_override)) {
                return (bool) $this->rc->config->get('thread_drafts_include_replies', true);
            }
            return (bool) $this->rc->config->get('thread_drafts_include_replies', true);
        }

        return true;
    }

    /**
     * Check if inbox threads should automatically collapse on opening
     *
     * @return bool
     */
    public function is_auto_collapse_inbox_enabled()
    {
        if ($this->rc && $this->rc->config) {
            $dont_override = (array) $this->rc->config->get('dont_override', []);
            if (in_array('thread_drafts_auto_collapse_inbox', $dont_override)) {
                return (bool) $this->rc->config->get('thread_drafts_auto_collapse_inbox', true);
            }
            return (bool) $this->rc->config->get('thread_drafts_auto_collapse_inbox', true);
        }

        return true;
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
        if ($this->rc && $this->rc->config) {
            return $this->rc->config->get($name, $default);
        }

        return $default;
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
     * Injects matching active drafts and sent replies into conversation threads.
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

            $current_folder = $this->rc->storage ? $this->rc->storage->get_folder() : '';
            if (empty($current_folder)) {
                $current_folder = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GPC, true) ?: ($_SESSION['mbox'] ?? 'INBOX');
            }
            $is_inbox = empty($current_folder) || strtoupper((string)$current_folder) === 'INBOX';

            // When opening the Inbox, standard the conversation threads should be collapsed and not expanded
            if ($is_inbox && $this->is_auto_collapse_inbox_enabled()) {
                $this->rc->config->set('autoexpand_threads', 0);
                if (!empty($this->rc->output) && method_exists($this->rc->output, 'set_env')) {
                    $this->rc->output->set_env('autoexpand_threads', 0);
                }
            }

            $drafts_mbox = $this->rc->config->get('drafts_mbox');
            $sent_mbox = $this->rc->config->get('sent_mbox');

            $trash_mbox = $this->rc->config->get('trash_mbox');
            $junk_mbox = $this->rc->config->get('junk_mbox');

            // Do not inject drafts or replies into Trash or Junk
            if ((!empty($trash_mbox) && $current_folder === $trash_mbox)
                || (!empty($junk_mbox) && $current_folder === $junk_mbox)
            ) {
                return $args;
            }

            $include_drafts = $this->is_drafts_enabled() && !empty($drafts_mbox) && $current_folder !== $drafts_mbox;
            $include_replies = $this->is_replies_enabled() && !empty($sent_mbox) && $current_folder !== $sent_mbox;

            if (!$include_drafts && !$include_replies) {
                return $args;
            }

            // Fetch undeleted drafts from Drafts folder
            $draft_headers = $include_drafts ? $this->get_active_drafts($drafts_mbox) : [];

            // Fetch undeleted sent replies from Sent folder
            $reply_headers = $include_replies ? $this->get_sent_replies($sent_mbox) : [];

            if (empty($draft_headers) && empty($reply_headers)) {
                return $args;
            }

            // Inject matching replies and drafts into conversation threads
            $args['messages'] = $this->insert_conversation_items(
                $args['messages'],
                $reply_headers,
                $draft_headers,
                $sent_mbox,
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
     * Retrieve undeleted sent message headers from the Sent mailbox
     *
     * @param string $sent_mbox
     *
     * @return array<rcube_message_header>
     */
    public function get_sent_replies($sent_mbox)
    {
        if ($this->replies_cache !== null) {
            return $this->replies_cache;
        }

        $this->replies_cache = [];

        if (empty($sent_mbox) || !$this->rc->storage) {
            return $this->replies_cache;
        }

        try {
            // Check if Sent mailbox has any messages
            $count = $this->rc->storage->count($sent_mbox, 'EXISTS');
            if (empty($count)) {
                return $this->replies_cache;
            }

            $current_folder = $this->rc->storage->get_folder();

            // Search undeleted sent messages
            $search_res = $this->rc->storage->search_once($sent_mbox, 'UNDELETED');
            if (!$search_res || !method_exists($search_res, 'is_empty') || $search_res->is_empty()) {
                return $this->replies_cache;
            }

            $uids = $search_res->get();
            if (empty($uids) || !is_array($uids)) {
                return $this->replies_cache;
            }

            // Limit sent replies to most recent N messages for high performance
            $max_replies = (int) $this->get_config('thread_drafts_max_replies', 100);
            if ($max_replies > 0 && count($uids) > $max_replies) {
                $uids = array_slice($uids, -$max_replies);
            }

            // Fetch sent message headers
            $this->ensure_fetch_headers();
            $headers = $this->rc->storage->fetch_headers($sent_mbox, $uids, false);

            // Ensure IMAP connection is restored to current folder
            if ($current_folder && $current_folder !== $sent_mbox) {
                $this->rc->storage->set_folder($current_folder);
                if (!empty($this->rc->storage->conn) && method_exists($this->rc->storage->conn, 'select')) {
                    $this->rc->storage->conn->select($current_folder);
                }
            }

            if (is_array($headers)) {
                $this->replies_cache = $headers;
            }
        } catch (\Throwable $e) {
            rcube::raise_error([
                'code'    => 500,
                'type'    => 'php',
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => 'thread_drafts error fetching replies: ' . $e->getMessage(),
            ], true, false);
        }

        return $this->replies_cache;
    }

    /**
     * Helper to extract an integer Unix timestamp from a message header
     *
     * @param rcube_message_header $msg
     *
     * @return int
     */
    public static function get_message_timestamp($msg)
    {
        if (isset($msg->timestamp) && is_numeric($msg->timestamp)) {
            return (int) $msg->timestamp;
        }
        if (!empty($msg->date)) {
            $ts = strtotime($msg->date);
            if ($ts !== false) {
                return $ts;
            }
        }
        if (!empty($msg->internaldate)) {
            $ts = strtotime($msg->internaldate);
            if ($ts !== false) {
                return $ts;
            }
        }
        return 0;
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

        if (class_exists('rcube_mime') && method_exists('rcube_mime', 'decode_header')) {
            $subject = trim(rcube_mime::decode_header($subject));
        } else {
            $subject = trim($subject);
        }

        // Remove prefixes like Re:, Fwd:, [Ticket #123], etc. iteratively
        while (preg_match('/^\s*(\[[^\]]*\]|\((?:re|fwd|fw)\)|\b(?:re|fwd|fw|aw|antw|wg)\b\s*:\s*)/i', $subject, $m)) {
            $subject = substr($subject, strlen($m[0]));
        }

        return strtolower(trim($subject));
    }

    /**
     * Insert matching sent replies and active drafts into conversation threads
     *
     * @param array<rcube_message_header> $messages     Current message headers
     * @param array<rcube_message_header> $replies      Sent reply headers
     * @param array<rcube_message_header> $drafts       Active draft headers
     * @param string                      $sent_mbox    Sent mailbox name
     * @param string                      $drafts_mbox  Drafts mailbox name
     * @param bool                        $subject_fallback
     *
     * @return array<rcube_message_header>
     */
    public function insert_conversation_items($messages, $replies, $drafts, $sent_mbox, $drafts_mbox, $subject_fallback = false)
    {
        if (empty($messages) || !is_array($messages)) {
            return $messages;
        }

        $has_replies = !empty($replies) && !empty($sent_mbox);
        $has_drafts = !empty($drafts) && !empty($drafts_mbox);

        if (!$has_replies && !$has_drafts) {
            return $messages;
        }

        // Build indexes of current message list by Message-ID, UID, and Subject
        $by_msgid = [];
        $by_uid = [];
        $by_subject = [];
        $depth_ancestors = [];

        foreach ($messages as $idx => $msg) {
            $msg->_seq = $idx;
            $d = (int) ($msg->depth ?? 0);
            $depth_ancestors[$d] = $msg->uid;

            if ($d > 0 && empty($msg->parent_uid) && isset($depth_ancestors[$d - 1])) {
                $msg->parent_uid = $depth_ancestors[$d - 1];
            }

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

        // 1. Identify and resolve matched sent replies (handling multi-turn reply chains)
        $matched_replies = []; // parent_uid => array of prepared reply headers
        if ($has_replies) {
            $unresolved_replies = $replies;
            $progress = true;

            while ($progress && !empty($unresolved_replies)) {
                $progress = false;
                $remaining = [];

                foreach ($unresolved_replies as $reply) {
                    $parent_uid = $this->find_matching_parent(
                        $reply,
                        $by_msgid,
                        $by_subject,
                        $subject_fallback
                    );

                    if ($parent_uid !== null && isset($by_uid[$parent_uid])) {
                        $parent_depth = (int) ($by_uid[$parent_uid]['header']->depth ?? 0);
                        $prepared = $this->prepare_reply_header($reply, $parent_uid, $parent_depth + 1, $sent_mbox);
                        $prepared->_seq = 10000 + count($matched_replies, COUNT_RECURSIVE);
                        $matched_replies[$parent_uid][] = $prepared;

                        $rep_id = self::clean_message_id($reply->messageID ?? $reply->msgid ?? null);
                        if (strlen($rep_id)) {
                            $by_msgid[$rep_id] = $prepared->uid;
                        }
                        $by_uid[$prepared->uid] = [
                            'header' => $prepared,
                            'index'  => count($by_uid),
                        ];
                        $progress = true;
                    } else {
                        $remaining[] = $reply;
                    }
                }
                $unresolved_replies = $remaining;
            }
        }

        // 2. Identify and resolve matched active drafts
        $matched_drafts = []; // parent_uid => array of prepared draft headers
        if ($has_drafts) {
            foreach ($drafts as $draft) {
                $parent_uid = $this->find_matching_parent(
                    $draft,
                    $by_msgid,
                    $by_subject,
                    $subject_fallback
                );

                if ($parent_uid !== null && isset($by_uid[$parent_uid])) {
                    $parent_depth = (int) ($by_uid[$parent_uid]['header']->depth ?? 0);
                    $prepared = $this->prepare_draft_header($draft, $parent_uid, $parent_depth + 1, $drafts_mbox);
                    $prepared->_seq = 20000 + count($matched_drafts, COUNT_RECURSIVE);
                    $matched_drafts[$parent_uid][] = $prepared;
                }
            }
        }

        if (empty($matched_replies) && empty($matched_drafts)) {
            return $messages;
        }

        // 3. Re-parent incoming messages whose In-Reply-To points to a newly spliced sent reply
        if (!empty($matched_replies)) {
            $reply_msgid_to_uid = [];
            foreach ($matched_replies as $p_uid => $reps) {
                foreach ($reps as $rep) {
                    $clean_id = self::clean_message_id($rep->messageID ?? $rep->msgid ?? null);
                    if (strlen($clean_id)) {
                        $reply_msgid_to_uid[$clean_id] = $rep->uid;
                    }
                }
            }

            if (!empty($reply_msgid_to_uid)) {
                foreach ($messages as $msg) {
                    if (!empty($msg->in_reply_to)) {
                        $clean_in_reply_to = self::clean_message_id($msg->in_reply_to);
                        if (isset($reply_msgid_to_uid[$clean_in_reply_to])) {
                            $msg->parent_uid = $reply_msgid_to_uid[$clean_in_reply_to];
                        }
                    }
                }
            }
        }

        // 4. Partition messages into independent threads by root message (depth == 0)
        $threads = [];
        $current_thread_index = -1;

        foreach ($messages as $idx => $msg) {
            $d = (int) ($msg->depth ?? 0);
            if ($d === 0 || $current_thread_index === -1) {
                $current_thread_index++;
                $threads[$current_thread_index] = [];
            }
            $threads[$current_thread_index][] = $msg;
        }

        // 5. Reassemble, chronologically order, and flatten each thread
        $result = [];

        foreach ($threads as $thread_messages) {
            $root = $thread_messages[0];
            $thread_uids = [];
            foreach ($thread_messages as $tm) {
                $thread_uids[$tm->uid] = true;
            }

            // Expand thread_uids with replies attached in this thread
            $added = true;
            while ($added) {
                $added = false;
                foreach ($matched_replies as $p_uid => $reps) {
                    if (isset($thread_uids[$p_uid])) {
                        foreach ($reps as $rep) {
                            if (!isset($thread_uids[$rep->uid])) {
                                $thread_uids[$rep->uid] = true;
                                $added = true;
                            }
                        }
                    }
                }
            }

            // Check if this thread has any external items
            $has_external = false;
            foreach ($thread_uids as $uid => $_) {
                if (!empty($matched_replies[$uid]) || !empty($matched_drafts[$uid])) {
                    $has_external = true;
                    break;
                }
            }

            if (!$has_external) {
                foreach ($thread_messages as $tm) {
                    $result[] = $tm;
                }
                continue;
            }

            // Build parent -> children map for this thread
            $children_by_parent = [];

            // Add existing thread messages (skipping root)
            for ($i = 1, $len = count($thread_messages); $i < $len; $i++) {
                $tm = $thread_messages[$i];
                $p_uid = $tm->parent_uid;
                if (empty($p_uid) || !isset($thread_uids[$p_uid])) {
                    $p_uid = $root->uid;
                }
                $children_by_parent[$p_uid][] = $tm;
            }

            // Add matched replies and drafts to children map
            $thread_has_draft = false;
            $thread_has_reply = false;

            foreach ($thread_uids as $uid => $_) {
                if (!empty($matched_replies[$uid])) {
                    $thread_has_reply = true;
                    foreach ($matched_replies[$uid] as $rep) {
                        $children_by_parent[$uid][] = $rep;
                    }
                }
                if (!empty($matched_drafts[$uid])) {
                    $thread_has_draft = true;
                    foreach ($matched_drafts[$uid] as $drf) {
                        $children_by_parent[$uid][] = $drf;
                    }
                }
            }

            // Sort children for each parent (chronologically, with drafts at the end)
            foreach ($children_by_parent as $p_uid => &$children) {
                usort($children, function ($a, $b) {
                    $a_is_draft = !empty($a->list_flags['is_draft']);
                    $b_is_draft = !empty($b->list_flags['is_draft']);
                    if ($a_is_draft !== $b_is_draft) {
                        return $a_is_draft ? 1 : -1; // Drafts always at the end
                    }
                    $t_a = self::get_message_timestamp($a);
                    $t_b = self::get_message_timestamp($b);
                    if ($t_a !== $t_b && $t_a > 0 && $t_b > 0) {
                        return $t_a <=> $t_b;
                    }
                    $seq_a = $a->_seq ?? 999999;
                    $seq_b = $b->_seq ?? 999999;
                    return $seq_a <=> $seq_b;
                });
            }
            unset($children);

            // Flatten tree via pre-order traversal
            $flatten = function ($node, $depth) use (&$flatten, &$children_by_parent, &$result) {
                $node->depth = $depth;
                $node_uid = $node->uid;
                $children = $children_by_parent[$node_uid] ?? [];
                $node->has_children = !empty($children);

                $result[] = $node;

                foreach ($children as $child) {
                    $child->parent_uid = $node_uid;
                    $flatten($child, $depth + 1);
                }
            };

            $flatten($root, 0);

            // Set flags on thread root message
            if ($thread_has_draft) {
                if (!isset($root->list_flags) || !is_array($root->list_flags)) {
                    $root->list_flags = [];
                }
                $root->list_flags['has_draft'] = 1;
            }
            if ($thread_has_reply) {
                if (!isset($root->list_flags) || !is_array($root->list_flags)) {
                    $root->list_flags = [];
                }
                $root->list_flags['has_replies'] = 1;
            }
        }

        return $result;
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
        return $this->insert_conversation_items($messages, [], $drafts, '', $drafts_mbox, $subject_fallback);
    }

    /**
     * Insert matching sent replies into the thread hierarchy of messages
     *
     * @param array<rcube_message_header> $messages Current message headers
     * @param array<rcube_message_header> $replies  Sent reply message headers
     * @param string                      $sent_mbox
     * @param bool                        $subject_fallback
     *
     * @return array<rcube_message_header>
     */
    public function insert_replies_into_threads($messages, $replies, $sent_mbox, $subject_fallback = false)
    {
        return $this->insert_conversation_items($messages, $replies, [], $sent_mbox, '', $subject_fallback);
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
        $recipient = !empty($h->to) ? $h->to : (method_exists($h, 'get') ? $h->get('to') : '');
        if (!empty($recipient)) {
            $to_formatted = '';
            if (class_exists('rcmail_action_mail_index') && defined('INTL_IDNA_VARIANT_UTS46')) {
                try {
                    $to_formatted = rcmail_action_mail_index::address_string($recipient, 3, false, null, $h->charset ?? 'UTF-8', null, false);
                } catch (\Throwable $e) {
                    $to_formatted = '';
                }
            }
            if (empty($to_formatted)) {
                if (class_exists('rcube') && method_exists('rcube', 'SQ')) {
                    $to_formatted = rcube::SQ($recipient);
                } elseif (class_exists('rcube') && method_exists('rcube', 'Q')) {
                    $to_formatted = rcube::Q($recipient);
                } else {
                    $to_formatted = htmlspecialchars((string)$recipient, ENT_QUOTES, 'UTF-8');
                }
            }
            $h->list_cols['fromto'] = $to_formatted;
        } else {
            $draft_label = method_exists($this, 'gettext') ? $this->gettext('draft') : 'Draft';
            $escaped_label = (class_exists('rcube') && method_exists('rcube', 'Q')) ? rcube::Q($draft_label) : htmlspecialchars($draft_label, ENT_QUOTES, 'UTF-8');
            $h->list_cols['fromto'] = '<em>' . $escaped_label . '</em>';
        }

        return $h;
    }

    /**
     * Prepare a sent reply header object for insertion into the message list
     *
     * @param rcube_message_header $reply
     * @param mixed                $parent_uid
     * @param int                  $depth
     * @param string               $sent_mbox
     *
     * @return rcube_message_header
     */
    public function prepare_reply_header($reply, $parent_uid, $depth, $sent_mbox)
    {
        $h = clone $reply;

        // Native Roundcube multi-folder format: <UID>-<MBOX>
        $numeric_uid = is_string($h->uid) && strpos($h->uid, '-') !== false
            ? explode('-', $h->uid)[0]
            : $h->uid;

        $h->uid = $numeric_uid . '-' . $sent_mbox;
        $h->folder = $sent_mbox;
        $h->parent_uid = $parent_uid;
        $h->depth = $depth;
        $h->has_children = false;
        $h->size = max(1, (int) $h->size);

        if (!is_array($h->flags)) {
            $h->flags = [];
        }
        $h->flags['skip_mbox_check'] = true;
        $h->flags['seen'] = true;

        if (!is_array($h->list_flags)) {
            $h->list_flags = [];
        }
        $h->list_flags['skip_mbox_check'] = true;
        $h->list_flags['is_reply'] = 1;
        $h->list_flags['is_sent'] = 1;
        $h->list_flags['mbox'] = $sent_mbox;

        if (!is_array($h->list_cols)) {
            $h->list_cols = [];
        }

        // Format smart From/To column for sent reply: To: <recipient>
        $recipient = !empty($h->to) ? $h->to : (method_exists($h, 'get') ? $h->get('to') : '');
        if (!empty($recipient)) {
            $to_formatted = '';
            if (class_exists('rcmail_action_mail_index') && defined('INTL_IDNA_VARIANT_UTS46')) {
                try {
                    $to_formatted = rcmail_action_mail_index::address_string($recipient, 3, false, null, $h->charset ?? 'UTF-8', null, false);
                } catch (\Throwable $e) {
                    $to_formatted = '';
                }
            }
            if (empty($to_formatted)) {
                if (class_exists('rcube') && method_exists('rcube', 'SQ')) {
                    $to_formatted = rcube::SQ($recipient);
                } elseif (class_exists('rcube') && method_exists('rcube', 'Q')) {
                    $to_formatted = rcube::Q($recipient);
                } else {
                    $to_formatted = htmlspecialchars((string)$recipient, ENT_QUOTES, 'UTF-8');
                }
            }
            $to_label = method_exists($this, 'gettext') ? $this->gettext('to') : 'To';
            if ($to_label === 'to' || empty($to_label)) {
                $to_label = 'To';
            }
            $h->list_cols['fromto'] = '<span class="reply-to-prefix">' . htmlspecialchars($to_label) . ':</span> ' . $to_formatted;
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

            if (!in_array('thread_drafts_include_replies', $dont_override)) {
                $field_id = 'rcmfd_thread_drafts_replies';
                $checkbox = new html_checkbox(['name' => '_thread_drafts_include_replies', 'id' => $field_id, 'value' => 1]);

                $args['blocks']['main']['options']['thread_drafts_include_replies'] = [
                    'title'   => html::label($field_id, rcube::Q($this->gettext('thread_replies_option'))),
                    'content' => $checkbox->show($this->is_replies_enabled() ? 1 : 0),
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

            if (!in_array('thread_drafts_auto_collapse_inbox', $dont_override)) {
                $field_id = 'rcmfd_thread_drafts_auto_collapse_inbox';
                $checkbox = new html_checkbox(['name' => '_thread_drafts_auto_collapse_inbox', 'id' => $field_id, 'value' => 1]);

                $args['blocks']['main']['options']['thread_drafts_auto_collapse_inbox'] = [
                    'title'   => html::label($field_id, rcube::Q($this->gettext('thread_drafts_auto_collapse_inbox_option'))),
                    'content' => $checkbox->show($this->is_auto_collapse_inbox_enabled() ? 1 : 0),
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

            if (!in_array('thread_drafts_include_replies', $dont_override)) {
                $args['prefs']['thread_drafts_include_replies'] = !empty($_POST['_thread_drafts_include_replies']);
            }

            if (!in_array('thread_drafts_show_message_banner', $dont_override)) {
                $args['prefs']['thread_drafts_show_message_banner'] = !empty($_POST['_thread_drafts_banner']);
            }

            if (!in_array('thread_drafts_auto_collapse_inbox', $dont_override)) {
                $args['prefs']['thread_drafts_auto_collapse_inbox'] = !empty($_POST['_thread_drafts_auto_collapse_inbox']);
            }
        }

        return $args;
    }
}
