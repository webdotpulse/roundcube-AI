<?php

/**
 * Gemini Executive Assistant for Roundcube
 *
 * An autonomous, reliable, and secure email assistant powered exclusively by Google Gemini.
 * Provides executive email triage, briefings, action item extraction, and pre-crafted draft replies.
 *
 * @license MIT
 * @author LifePrisma & Contributors
 */
require_once __DIR__ . '/src/LpaiSpamFilter.php';

class lifeprisma_ai extends rcube_plugin
{
    public $task = '?(?!logout).*';

    public function init()
    {
        $this->load_config();
        $rcmail = rcmail::get_instance();
        $active_skin = $rcmail->config->get('skin', 'elastic');
        $skin_path = $this->local_skin_path();
        if (($active_skin === 'gmail_plus' || strpos($active_skin, 'gmail') !== false) && is_dir($this->home . '/skins/gmail_plus')) {
            $skin_path = 'skins/gmail_plus';
        }
        if (file_exists($this->home . "/{$skin_path}/style.min.css")) {
            $this->include_stylesheet("{$skin_path}/style.min.css");
        } else {
            $this->include_stylesheet("{$skin_path}/style.css");
        }

        if (file_exists($this->home . '/lifeprisma_ai.min.js')) {
            $this->include_script('lifeprisma_ai.min.js');
        } else {
            $this->include_script('src/lifeprisma_ai.js');
        }

        // Register action endpoints
        $this->register_action('plugin.lifeprisma_ai_request', [$this, 'handle_request']);
        $this->register_action('plugin.lifeprisma_ai_stream', [$this, 'handle_stream']);
        $this->register_action('plugin.lifeprisma_ai_templates', [$this, 'handle_templates']);
        $this->register_action('plugin.lifeprisma_ai_admin', [$this, 'handle_admin']);
        $this->register_action('plugin.lifeprisma_ai_admin_save', [$this, 'handle_admin_save']);
        $this->register_action('plugin.lifeprisma_ai_autodraft', [$this, 'handle_autodraft']);
        $this->register_action('plugin.lifeprisma_ai_triage', [$this, 'handle_triage']);
        $this->register_action('plugin.lifeprisma_ai_memory', [$this, 'handle_memory']);
        $this->register_action('plugin.lifeprisma_ai_prepare_compose', [$this, 'handle_prepare_compose']);
        $this->register_action('plugin.lifeprisma_ai_spam_tag', [$this, 'handle_spam_tag']);
        $this->register_action('plugin.lifeprisma_ai_spam_untag', [$this, 'handle_spam_untag']);
        $this->register_action('plugin.lifeprisma_ai_spam_stats', [$this, 'handle_spam_stats']);
        $this->register_action('plugin.lifeprisma_ai_spam_reset', [$this, 'handle_spam_reset']);
        $this->register_action('plugin.lifeprisma_ai_spam_batch_train', [$this, 'handle_spam_batch_train']);

        // Register hooks
        $this->add_hook('render_page', [$this, 'render_page']);
        $this->add_hook('preferences_sections_list', [$this, 'preferences_sections']);
        $this->add_hook('preferences_list', [$this, 'preferences_list']);
        $this->add_hook('preferences_save', [$this, 'preferences_save']);
        $this->add_hook('new_messages', [$this, 'handle_new_messages']);
        $this->add_hook('messages_list', [$this, 'handle_messages_list']);
        $this->add_hook('messages_move', [$this, 'handle_messages_move']);
        $this->add_hook('message_compose', [$this, 'handle_message_compose']);
        $this->add_hook('message_sent', [$this, 'handle_message_sent']);

        // Add taskbar button for sidebar menu (purple Gemini icon)
        $this->add_button([
            'type' => 'link',
            'label' => 'Gemini',
            'title' => 'Gemini Assistant (Alt+A)',
            'class' => 'button-gemini-ai',
            'id' => 'taskmenu-gemini-btn',
            'href' => '#gemini',
            'onclick' => 'if(window.lpai_open_panel){lpai_open_panel(); return false;}',
            'innerclass' => 'inner',
        ], 'taskbar');
    }

    public function render_page($args)
    {
        $rcmail = rcmail::get_instance();
        $template = $args['template'] ?? '';
        $is_compose = ($template === 'compose');
        $is_read = ($template === 'message' || $template === 'messagepreview' || $template === 'mail');
        $action = $rcmail->action ?? '';
        $task = $rcmail->task ?? '';
        $is_response = ($template === 'responses' || $template === 'responseedit' || in_array($action, ['responses', 'response-edit', 'response-add', 'add-response', 'edit-response'], true));
        $is_newsletter = ($task === 'newsletter' || strpos((string)$action, 'newsletter') !== false || ($template === 'plugin' && $task === 'newsletter'));

        if ($is_compose || $is_read || $is_response || $is_newsletter) {
            $gemini = $this->get_gemini_config();
            $active_skin = $rcmail->config->get('skin', 'elastic');

            // Pass active skin environment
            $rcmail->output->set_env('lpai_skin', $active_skin);

            // Pass Gemini configuration and pricing
            $rcmail->output->set_env('lpai_gemini', [
                'has_key' => !empty($gemini['api_key']),
                'model' => $gemini['model'],
                'models' => $gemini['models'],
                'endpoint' => $gemini['api_url'],
                'pricing' => $gemini['pricing'],
            ]);

            // Backward-compatible provider object for existing components
            $rcmail->output->set_env('lpai_providers', [
                'gemini' => [
                    'label' => 'Gemini',
                    'models' => $gemini['models'],
                    'default_model' => $gemini['model'],
                    'supports_reasoning' => false,
                    'pricing' => $gemini['pricing'],
                ]
            ]);
            $rcmail->output->set_env('lpai_default_provider', 'gemini');

            // Pass user preferences
            $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
            $auto_draft_mode = $prefs['genia_auto_draft_mode'] ?? $rcmail->config->get('lifeprisma_ai_auto_draft_mode', 'open');
            $rcmail->output->set_env('lpai_user_prefs', [
                'language' => $prefs['genia_language'] ?? $rcmail->config->get('lifeprisma_ai_default_language', 'English'),
                'tone' => $prefs['genia_tone'] ?? $rcmail->config->get('lifeprisma_ai_default_tone', 'professional'),
                'auto_draft' => $prefs['genia_auto_draft'] ?? 0,
                'auto_draft_mode' => $auto_draft_mode,
                'auto_draft_filter' => $prefs['genia_auto_draft_filter'] ?? 1,
                'followup_check' => $prefs['genia_followup_check'] ?? 1,
                'memory_enabled' => (bool) $rcmail->config->get('lifeprisma_ai_memory_enabled', true),
                'triage_labels_enabled' => (bool) $rcmail->config->get('lifeprisma_ai_triage_labels_enabled', true),
                'triage_label_map' => $rcmail->config->get('lifeprisma_ai_triage_label_map', [
                    'to_respond'            => '$Label1',
                    'fyi'                   => '$Label2',
                    'important'             => '$Label3',
                    'marketing_newsletters' => '$Label4',
                    'todo'                  => '$Label5',
                    'action_required'       => '$Label1',
                    'action_required_high'  => '$Label3',
                    'meeting'               => '$Label1',
                    'follow_up'             => '$Label1',
                    'newsletter'            => '$Label4',
                    'scam'                  => '$Label3',
                ]),
            ]);

            // Pass admin status and feature toggles
            $rcmail->output->set_env('lpai_is_admin', $this->is_admin());
            $features = $this->get_enabled_features();
            if ($features) {
                $rcmail->output->set_env('lpai_features', $features);
            }

            // Pass spam environment settings
            $junk_folder = $this->get_junk_folder();
            $rcmail->output->set_env('lpai_junk_mbox', $junk_folder);
            $rcmail->output->set_env('lpai_spam_enabled', (bool) ($prefs['lifeprisma_ai_spam_filter_enabled'] ?? $rcmail->config->get('lifeprisma_ai_spam_filter_enabled', true)));
            $rcmail->output->set_env('lpai_spam_action', $prefs['lifeprisma_ai_spam_action'] ?? $rcmail->config->get('lifeprisma_ai_spam_action', 'move_and_label'));

            // Pass message context for read/preview view
            if ($is_read) {
                $uid = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);
                $mbox = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GET);
                if ($uid) {
                    $attachments = $this->get_attachment_info((int) $uid, $mbox);
                    if ($attachments) {
                        $rcmail->output->set_env('lpai_attachments', $attachments);
                    }
                    $ctx = $this->fetch_message_context((int) $uid, $mbox);
                    if ($ctx) {
                        $is_msg_spam = (strcasecmp((string)$mbox, $junk_folder) === 0);
                        $storage = $rcmail->get_storage();
                        if ($storage && !$is_msg_spam) {
                            $flags = $storage->get_message_flags((int) $uid);
                            if (is_array($flags)) {
                                $is_msg_spam = !empty($flags['Junk']) || !empty($flags['$Junk']);
                            }
                        }
                        $rcmail->output->set_env('lpai_msg_context', [
                            'from' => $ctx['from'] ?? '',
                            'date' => $ctx['date'] ?? '',
                            'subject' => $ctx['subject'] ?? '',
                            'spam_score' => $ctx['spam_score'],
                            'is_spam' => $is_msg_spam,
                        ]);
                    }
                }
            }

            $rcmail->output->set_env('lpai_smart_compose', $prefs['genia_smart_compose'] ?? 1);

            // Add Assistant modal in footer
            $rcmail->output->add_footer($this->get_ai_panel_html($gemini));
        }

        // Replace sidebar button text with exact purple Gemini SVG icon
        $svg_sidebar = '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"></path></svg>';
        if (isset($args['content'])) {
            $args['content'] = preg_replace(
                '#<a([^>]*class="[^"]*button-gemini-ai[^"]*"[^>]*)>.*?<span class="inner">.*?</span>.*?</a>#is',
                '<a$1>' . $svg_sidebar . '</a>',
                $args['content']
            );
            $args['content'] = preg_replace(
                '#<span class="inner">\s*\[?(?:gemini)\]?\s*</span>#is',
                $svg_sidebar,
                $args['content']
            );
            $args['content'] = str_ireplace('<span class="inner">[Gemini]</span>', $svg_sidebar, $args['content']);
            $args['content'] = str_ireplace('<span class="inner">Gemini</span>', $svg_sidebar, $args['content']);
        }

        return $args;
    }

    /**
     * Resolve Google Gemini Configuration
     */
    public function get_gemini_config()
    {
        $rcmail = rcmail::get_instance();
        $admin = $this->get_admin_config();

        // 1. Admin overrides in DB take highest precedence
        $api_key = $admin['gemini_api_key'] ?? '';
        $model = $admin['gemini_model'] ?? '';
        $api_url = $admin['api_url'] ?? '';

        // 2. Config file fallbacks
        if (empty($api_key)) {
            $api_key = $rcmail->config->get('lifeprisma_ai_gemini_api_key', '');
        }
        if (empty($api_key)) {
            // Check legacy multi-provider config if present
            $providers = $rcmail->config->get('lifeprisma_ai_providers', []);
            if (!empty($providers['gemini']['api_key'])) {
                $api_key = $providers['gemini']['api_key'];
            } elseif ($rcmail->config->get('lifeprisma_ai_api_key')) {
                $api_key = $rcmail->config->get('lifeprisma_ai_api_key');
            }
        }

        if (empty($model)) {
            $model = $rcmail->config->get('lifeprisma_ai_gemini_model', 'gemini-3.8-flash');
        }

        if (empty($api_url)) {
            $api_url = $rcmail->config->get('lifeprisma_ai_api_url', 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions');
        }

        $models = $rcmail->config->get('lifeprisma_ai_gemini_models', [
            'gemini-3.8-flash',
            'gemini-3.8-flash-cyber',
            'gemini-3.7-flash',
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
        ]);

        return [
            'api_key' => trim($api_key),
            'model' => trim($model),
            'models' => $models,
            'api_url' => trim($api_url),
            'has_key' => !empty($api_key),
            'pricing' => [
                'gemini-3.8-flash'       => ['input' => 0.30, 'output' => 2.50],
                'gemini-3.8-flash-cyber' => ['input' => 0.30, 'output' => 2.50],
                'gemini-3.7-flash'       => ['input' => 0.30, 'output' => 2.50],
                'gemini-3.6-flash'       => ['input' => 0.30, 'output' => 2.50],
                'gemini-3.5-flash'       => ['input' => 0.30, 'output' => 2.50],
                'gemini-3.5-flash-lite'  => ['input' => 0.075, 'output' => 0.30],
            ],
        ];
    }

    /**
     * Unified Autonomous Executive Assistant triage endpoint
     * Analyzes email in a single shot: classification, summary, action items, deadlines,
     * security check, and pre-crafted draft reply.
     */
    public function handle_triage()
    {
        try {
            if (!$this->check_csrf()) {
                header('Content-Type: application/json; charset=utf-8', true, 403);
                echo json_encode(['status' => 'error', 'message' => 'Invalid or expired CSRF token']);
                exit;
            }

            $action = 'triage';
            if (!$this->check_rate_limit($action)) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Please wait a moment between requests.']);
                exit;
            }

            $rcmail = rcmail::get_instance();
            header('Content-Type: application/json; charset=utf-8');

            $uid = rcube_utils::get_input_string('msg_uid', rcube_utils::INPUT_POST);
            $mbox = rcube_utils::get_input_string('mbox', rcube_utils::INPUT_POST) ?: 'INBOX';
            $force = (bool) rcube_utils::get_input_string('force', rcube_utils::INPUT_POST);

            if (empty($uid)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing msg_uid']);
                exit;
            }

            $gemini = $this->get_gemini_config();
            if (empty($gemini['api_key'])) {
                echo json_encode([
                    'status' => 'error',
                    'code' => 'no_api_key',
                    'message' => 'Google Gemini API key not configured. Please add your key in Settings -> Gemini Assistant Admin or config.inc.php.'
                ]);
                exit;
            }

            $cache_key = $this->cache_user_prefix() . "triage:{$mbox}:{$uid}";
            if (!$force) {
                $cached = $this->cache_get($cache_key);
                if ($cached !== null) {
                    $cached['cached'] = 'server';
                    echo json_encode($cached);
                    exit;
                }

                // Check user preferences log
                if ($rcmail->user) {
                    try {
                        $prefs = $rcmail->user->get_prefs();
                        $triage_log = $prefs['genia_triage_log'] ?? [];
                        $item_key = "{$mbox}:{$uid}";
                        if (isset($triage_log[$item_key]) && (time() - ($triage_log[$item_key]['time'] ?? 0) < 86400 * 7)) {
                            $item = $triage_log[$item_key];
                            $item['status'] = 'success';
                            $item['cached'] = 'local';
                            echo json_encode($item);
                            exit;
                        }
                    } catch (\Throwable $e) {
                        $this->ai_log("[TRIAGE PREFS CHECK ERROR] " . $e->getMessage());
                    }
                }
            }

            $ctx = $this->fetch_message_context($uid, $mbox);
            if (empty($ctx) || empty($ctx['body'])) {
                echo json_encode(['status' => 'error', 'message' => 'Empty or unreadable message body']);
                exit;
            }

            $raw_headers = $this->fetch_raw_headers($uid, $mbox);
            $is_bulk = false;
            if (!empty($raw_headers)) {
                if (preg_match('/\b(List-Unsubscribe|List-Id|List-Post):/i', $raw_headers) ||
                    preg_match('/\bPrecedence:\s*(bulk|list|junk)/i', $raw_headers) ||
                    preg_match('/\bAuto-Submitted:\s*(auto-generated|auto-replied)/i', $raw_headers)) {
                    $is_bulk = true;
                }
            }

            // Check if sent by user themselves
            $user_emails = [];
            $identities = $rcmail->user ? $rcmail->user->list_identities() : [];
            if (is_array($identities)) {
                foreach ($identities as $ident) {
                    if (!empty($ident['email'])) $user_emails[] = strtolower(trim($ident['email']));
                }
            }
            $is_self = false;
            $from_email = strtolower($ctx['from'] ?? '');
            foreach ($user_emails as $ue) {
                if ($ue && strpos($from_email, $ue) !== false) {
                    $is_self = true;
                    break;
                }
            }

            $identity = $rcmail->user ? $rcmail->user->get_identity() : [];
            if (!is_array($identity)) $identity = [];
            $user_name = trim(($identity['name'] ?? '') . ' <' . ($identity['email'] ?? '') . '>');
            $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
            if (!is_array($prefs)) $prefs = [];
            $language = $prefs['genia_language'] ?? 'English';
            $tone = $prefs['genia_tone'] ?? 'professional';

            // Close session early to prevent webmail lockup during external AI generation
            session_write_close();

            // Single-shot executive triage via Google Gemini
            $result = $this->call_gemini_triage($ctx, $raw_headers, $user_name, $language, $tone, $gemini, $is_bulk, $is_self);

            if (!$result || empty($result['analysis'])) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to generate executive analysis from Gemini']);
                exit;
            }

            $analysis = $result['analysis'];
            $model = $result['model'];
            $tokens = $result['tokens'];

            // Assign IMAP label flag if triage labels are enabled (roundcube-labels integration)
            if ($rcmail->config->get('lifeprisma_ai_triage_labels_enabled', true)) {
                try {
                    $label_map = $rcmail->config->get('lifeprisma_ai_triage_label_map', [
                        'to_respond'            => '$Label1',
                        'fyi'                   => '$Label2',
                        'important'             => '$Label3',
                        'marketing_newsletters' => '$Label4',
                        'todo'                  => '$Label5',
                        'action_required'       => '$Label1',
                        'action_required_high'  => '$Label3',
                        'meeting'               => '$Label1',
                        'follow_up'             => '$Label1',
                        'newsletter'            => '$Label4',
                        'scam'                  => '$Label3',
                    ]);
                    $cat = $analysis['category'] ?? 'fyi';
                    $urgency = $analysis['urgency'] ?? 'low';
                    $mapKey = ($cat === 'action_required' && $urgency === 'high') ? 'action_required_high' : $cat;
                    $flag = $label_map[$cat] ?? ($label_map[$mapKey] ?? ($label_map['fyi'] ?? '$Label2'));
                    if ($flag) {
                        $storage = $rcmail->get_storage();
                        if ($storage) {
                            $storage->set_flag($uid, $flag, $mbox);
                        }
                        $analysis['assigned_label'] = $flag;
                    }
                } catch (\Throwable $e) {
                    $this->ai_log("[TRIAGE LABEL ERROR] " . $e->getMessage());
                }
            }

            // Save to cache (multi-tier resilient cache)
            $cache_payload = [
                'status' => 'success',
                'analysis' => $analysis,
                'model' => $model,
                'tokens' => $tokens,
                'time' => time(),
            ];
            $this->cache_set($cache_key, $cache_payload, 86400 * 7);

            // Save in user preferences log (FIFO capped at 200 items, using no_session = true post session_write_close)
            if ($rcmail->user) {
                try {
                    $tlog = $prefs['genia_triage_log'] ?? [];
                    if (!is_array($tlog)) $tlog = [];
                    $item_key = "{$mbox}:{$uid}";
                    $tlog[$item_key] = $cache_payload;
                    if (count($tlog) > 200) {
                        $cutoff = time() - (86400 * 7);
                        $tlog = array_filter($tlog, function ($e) use ($cutoff) { return ($e['time'] ?? 0) > $cutoff; });
                        if (count($tlog) > 150) $tlog = array_slice($tlog, -150, null, true);
                    }
                    $rcmail->user->save_prefs(['genia_triage_log' => $tlog], true);
                } catch (\Throwable $e) {
                    $this->ai_log("[TRIAGE SAVE PREFS ERROR] " . $e->getMessage());
                }
            }

            // Auto-save draft to IMAP Drafts if user preference enables background creation
            try {
                $auto_draft_mode = $prefs['genia_auto_draft_mode'] ?? 'open';
                if ($auto_draft_mode === 'open' && !empty($analysis['needs_reply']) && !empty($analysis['draft_reply']) && !empty($prefs['genia_auto_draft'])) {
                    $orig_msg_id = '';
                    if (preg_match('/^Message-ID:\s*(<[^>]+>)/im', $raw_headers, $m)) {
                        $orig_msg_id = trim($m[1]);
                    }
                    $this->create_imap_draft(
                        $ctx['from'],
                        $ctx['subject'],
                        $analysis['draft_reply'],
                        $orig_msg_id,
                        $ctx['date'],
                        $ctx['from'],
                        $ctx['body']
                    );
                }
            } catch (\Throwable $e) {
                $this->ai_log("[TRIAGE AUTODRAFT ERROR] " . $e->getMessage());
            }

            echo json_encode([
                'status' => 'success',
                'analysis' => $analysis,
                'model' => $model,
                'tokens' => $tokens,
                'cached' => false,
            ]);
            exit;
        } catch (\Throwable $e) {
            $this->ai_log("[TRIAGE FATAL ERROR] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            echo json_encode([
                'status' => 'error',
                'message' => 'Executive triage error: ' . $e->getMessage(),
            ]);
            exit;
        }
    }

    /**
     * Execute Gemini Executive Triage Prompt
     */
    private function call_gemini_triage($ctx, $raw_headers, $user_name, $language, $tone, $gemini, $is_bulk = false, $is_self = false)
    {
        $subject = $ctx['subject'] ?? '';
        $from = $ctx['from'] ?? '';
        $date = $ctx['date'] ?? '';
        $body = mb_substr($ctx['body'] ?? '', 0, 4000);

        $system_prompt = "You are an executive Chief of Staff and AI email assistant.
Your goal is to provide an instant, high-level briefing of incoming emails, classify them strictly according to the defined 5-label taxonomy, extract concrete action items, and prepare a polished, contextual draft reply when appropriate.

Return ONLY a JSON object with this exact structure (no markdown formatting, no code fences):
{
  \"category\": \"to_respond\" | \"fyi\" | \"important\" | \"marketing_newsletters\" | \"todo\",
  \"urgency\": \"high\" | \"medium\" | \"low\",
  \"category_label\": \"To Respond\" | \"FYI\" | \"Important\" | \"Marketing & Newsletters\" | \"ToDo\",
  \"summary\": \"1-2 concise sentence executive briefing focusing on what the email is about and key implications for the recipient.\",
  \"action_items\": [\"Specific action item, question to answer, or next step\", ...],
  \"meeting_details\": \"Date, time, timezone, topic, or null if no meeting mentioned\",
  \"needs_reply\": true | false,
  \"draft_reply\": \"Polite, professional, complete reply addressing all questions and next steps in the email. Never include email sign-off signature.\",
  \"is_scam\": true | false,
  \"scam_reason\": \"Explanation if suspicious, or null\"
}

Rules:
1. Category definitions (classify into EXACTLY ONE of these 5 categories):
   - \"to_respond\" (1: To Respond): Direct conversational obligation. Requires you to draft a reply, give an explicit approval, or answer questions directly within the thread.
   - \"fyi\" (2: FYI): Passive knowledge. Updates, company announcements, receipts, and project summaries where you are CC’d or kept in the loop, requiring no action or response.
   - \"important\" (3: Important): Critical urgency and high stakes. High-impact updates or alerts from VIPs, clients, or security teams that must be seen immediately.
   - \"marketing_newsletters\" (4: Marketing & Newsletters): Low-priority machine-generated mail. Vendor outreach, industry newsletters, webinars, product updates, and routine SaaS platform digests.
   - \"todo\" (5: ToDo): External work execution. The email assigns you work to be done outside the inbox (e.g., \"please sign this contract\" or \"fix this bug\"), closing the loop only after the external task is finished.
2. If the email is sent by the user themself, category should be \"fyi\" and needs_reply should be false.
3. If the email is \"marketing_newsletters\", needs_reply should be false and draft_reply should be null.
4. If the email is \"fyi\", needs_reply should be false and draft_reply should be null.
5. If the email is \"to_respond\", needs_reply should be true and a draft_reply should be prepared.
6. If the email is a suspicious phishing/scam, set category to \"important\", is_scam to true, and explain in scam_reason.
7. Keep summary under 50 words. Be objective and direct.
8. Action items should be clear and actionable. If no action items, return an empty array [].
9. Draft reply must be contextually appropriate in {$language} with a {$tone} tone. Do NOT include sign-offs like '--' or 'Best regards, [Name]' (Roundcube handles signatures).
10. If needs_reply is false, set draft_reply to null.";

        // Inject learned AI memory if enabled (answer replication)
        $rcmail = rcmail::get_instance();
        $memory_prompt = '';
        if ($rcmail->config->get('lifeprisma_ai_memory_enabled', true)) {
            $memories = $this->load_ai_memory();
            if (!empty($memories)) {
                $matches = $this->find_matching_memory($subject . ' ' . $body, $memories, 3);
                if (!empty($matches)) {
                    $memory_prompt = "\nVERIFIED PREVIOUS CLIENT ANSWERS (REPLICATE IF SIMILAR INQUIRY):\n";
                    foreach ($matches as $m_item) {
                        $q = $m_item['question'] ?? '';
                        $a = $m_item['answer'] ?? '';
                        $memory_prompt .= "- Previous Client Inquiry: {$q}\n  Verified Official Answer: {$a}\n";
                    }
                    $memory_prompt .= "\nCRITICAL MEMORY INSTRUCTION: If the incoming email asks a question similar to any of the verified Q&A pairs above, you MUST replicate the verified answer accurately and maintain the exact factual guidance, pricing, policies, or instructions provided previously.\n";
                }
            }
        }

        $user_prompt = "Email to analyze:
From: {$from}
Date: {$date}
Subject: {$subject}
User (Recipient): {$user_name}
Language requested: {$language}
Tone requested: {$tone}
Bulk/Newsletter indicator: " . ($is_bulk ? 'YES' : 'NO') . "
Self-sent indicator: " . ($is_self ? 'YES' : 'NO') . "
{$memory_prompt}
Body:
{$body}";

        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt],
        ];

        $payload = [
            'model' => $gemini['model'],
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 2048,
            'response_format' => ['type' => 'json_object'],
        ];

        $api_url = $gemini['api_url'];
        $api_key = $gemini['api_key'];

        if (!$this->validate_api_url($api_url)) {
            $this->ai_log("[TRIAGE ERROR] Prohibited or invalid API URL: $api_url");
            return null;
        }

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || empty($response)) {
            $this->ai_log("[TRIAGE ERROR] HTTP $http_code: " . substr((string)$response, 0, 300));
            return null;
        }

        $res = json_decode($response, true);
        if (!$res) return null;

        $content = $res['choices'][0]['message']['content'] ?? '';
        if (empty($content)) return null;

        $clean_json = trim($content);
        if (strpos($clean_json, '```') !== false) {
            $clean_json = preg_replace('/^```(?:json)?\s*/i', '', $clean_json);
            $clean_json = preg_replace('/\s*```$/', '', $clean_json);
        }

        $analysis = json_decode(trim($clean_json), true);
        if (!is_array($analysis) || !isset($analysis['category'])) {
            return null;
        }

        $tokens = [
            'input' => $res['usage']['prompt_tokens'] ?? 0,
            'output' => $res['usage']['completion_tokens'] ?? 0,
        ];

        return [
            'analysis' => $analysis,
            'model' => $gemini['model'],
            'tokens' => $tokens,
        ];
    }

    /**
     * Streaming endpoint for Assistant dialogs — sends Server-Sent Events
     */
    public function handle_stream()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: text/event-stream', true, 403);
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Invalid or expired CSRF token']) . "\n\n";
            exit;
        }

        $action = rcube_utils::get_input_string('ai_action', rcube_utils::INPUT_POST);
        if (!$this->check_rate_limit($action)) {
            header('Content-Type: text/event-stream');
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Please wait a moment between requests.']) . "\n\n";
            exit;
        }

        $rcmail = rcmail::get_instance();
        $gemini = $this->get_gemini_config();

        if (empty($gemini['api_key'])) {
            header('Content-Type: text/event-stream');
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Gemini API key not configured. Please add your key in Settings -> Gemini Assistant Admin.']) . "\n\n";
            exit;
        }

        $instruction = rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST);
        $email_body = rcube_utils::get_input_string('email_body', rcube_utils::INPUT_POST);
        $reply_text = rcube_utils::get_input_string('reply_text', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $language = rcube_utils::get_input_string('language', rcube_utils::INPUT_POST);
        $tone = rcube_utils::get_input_string('tone', rcube_utils::INPUT_POST);
        $sender_name = rcube_utils::get_input_string('sender_name', rcube_utils::INPUT_POST);
        $history = rcube_utils::get_input_string('history', rcube_utils::INPUT_POST);
        $model_override = rcube_utils::get_input_string('model', rcube_utils::INPUT_POST);

        $model = $model_override ?: $gemini['model'];
        $api_url = $gemini['api_url'];
        $max_tokens = (int) $rcmail->config->get('lifeprisma_ai_max_tokens', 2048);
        $temperature = (float) $rcmail->config->get('lifeprisma_ai_temperature', 0.4);

        if (!$this->validate_api_url($api_url)) {
            header('Content-Type: text/event-stream', true, 400);
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Prohibited API endpoint URL.']) . "\n\n";
            exit;
        }

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name);

        $messages = [['role' => 'system', 'content' => $system_prompt]];

        if (!empty($history)) {
            $hist = json_decode($history, true);
            if (is_array($hist)) {
                foreach ($hist as $m) $messages[] = $m;
            }
            if (!empty($instruction) && !empty($hist)) {
                $messages[] = ['role' => 'user', 'content' => $instruction];
            } else {
                $messages[] = ['role' => 'user', 'content' => $user_prompt];
            }
        } else {
            $messages[] = ['role' => 'user', 'content' => $user_prompt];
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
            'stream_options' => ['include_usage' => true],
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ];

        // Headers for SSE streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Close session to prevent UI lockup
        session_write_close();

        while (ob_get_level()) {
            ob_end_flush();
        }

        $ch = curl_init($api_url);
        $stream_buffer = '';
        $stream_first_chunk = true;
        $stream_error = false;
        $stream_tokens = ['input' => 0, 'output' => 0];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $gemini['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$stream_buffer, &$stream_first_chunk, &$stream_error, &$stream_tokens, $model, $action) {
                if ($stream_first_chunk) {
                    $stream_first_chunk = false;
                    $trimmed = trim($data);
                    if (!empty($trimmed) && $trimmed[0] === '{') {
                        $err = json_decode($trimmed, true);
                        if (isset($err['error'])) {
                            $stream_error = true;
                            $msg = $err['error']['message'] ?? 'Unknown Gemini API error';
                            echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
                            flush();
                            return 0; // Abort transfer immediately
                        }
                    }
                }

                $stream_buffer .= $data;
                // Protect against unbounded memory consumption if upstream omits newlines
                if (strlen($stream_buffer) > 65536) {
                    $stream_error = true;
                    echo "data: " . json_encode(['type' => 'error', 'message' => 'Stream buffer overflow']) . "\n\n";
                    flush();
                    return 0;
                }

                $lines = explode("\n", $stream_buffer);
                $stream_buffer = array_pop($lines); // Retain incomplete line

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, 'data: ') !== 0) continue;
                    $json_str = substr($line, 6);
                    if ($json_str === '[DONE]') {
                        echo "data: [DONE]\n\n";
                        flush();
                        continue;
                    }
                    $chunk = json_decode($json_str, true);
                    if (isset($chunk['usage'])) {
                        $stream_tokens['input'] = (int) ($chunk['usage']['prompt_tokens'] ?? $chunk['usage']['promptTokens'] ?? $stream_tokens['input']);
                        $stream_tokens['output'] = (int) ($chunk['usage']['completion_tokens'] ?? $chunk['usage']['completionTokens'] ?? $stream_tokens['output']);
                    }
                    if (isset($chunk['choices'][0]['delta']['content'])) {
                        $delta = $chunk['choices'][0]['delta']['content'];
                        echo "data: " . json_encode(['type' => 'delta', 'text' => $delta]) . "\n\n";
                        flush();
                    }
                }

                return strlen($data);
            }
        ]);

        curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err && !$stream_error) {
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Connection error: ' . $curl_err]) . "\n\n";
            flush();
        } else {
            echo "data: " . json_encode(['type' => 'done', 'model' => $model, 'usage' => $stream_tokens]) . "\n\n";
            flush();
        }
        exit;
    }

    /**
     * Synchronous JSON request handler (e.g. autocomplete, suggest subject)
     */
    public function handle_request()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired CSRF token']);
            exit;
        }

        $action = rcube_utils::get_input_string('ai_action', rcube_utils::INPUT_POST);
        if (!$this->check_rate_limit($action)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Please wait a moment between requests.']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $gemini = $this->get_gemini_config();
        if (empty($gemini['api_key'])) {
            echo json_encode(['status' => 'error', 'message' => 'Gemini API key not configured.']);
            exit;
        }

        $instruction = rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST);
        $email_body = rcube_utils::get_input_string('email_body', rcube_utils::INPUT_POST);
        $reply_text = rcube_utils::get_input_string('reply_text', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $language = rcube_utils::get_input_string('language', rcube_utils::INPUT_POST);
        $tone = rcube_utils::get_input_string('tone', rcube_utils::INPUT_POST);
        $sender_name = rcube_utils::get_input_string('sender_name', rcube_utils::INPUT_POST);
        $model_override = rcube_utils::get_input_string('model', rcube_utils::INPUT_POST);

        $model = $model_override ?: $gemini['model'];
        $api_url = $gemini['api_url'];
        $max_tokens = (int) $rcmail->config->get('lifeprisma_ai_max_tokens', 2048);
        $temperature = (float) $rcmail->config->get('lifeprisma_ai_temperature', 0.4);

        if (!$this->validate_api_url($api_url)) {
            echo json_encode(['status' => 'error', 'message' => 'Prohibited API endpoint URL.']);
            exit;
        }

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name);

        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt],
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ];

        // Close session before long synchronous request
        session_write_close();

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $gemini['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err || $http_code !== 200 || empty($response)) {
            echo json_encode(['status' => 'error', 'message' => 'API request failed: ' . ($curl_err ?: "HTTP $http_code")]);
            exit;
        }

        $res = json_decode($response, true);
        $content = $res['choices'][0]['message']['content'] ?? '';

        echo json_encode([
            'status' => 'success',
            'result' => trim($content),
            'model' => $model,
            'tokens' => [
                'input' => $res['usage']['prompt_tokens'] ?? 0,
                'output' => $res['usage']['completion_tokens'] ?? 0,
            ],
        ]);
        exit;
    }

    /**
     * Background autodraft handler
     */
    public function handle_autodraft()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired CSRF token']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $uid = rcube_utils::get_input_string('msg_uid', rcube_utils::INPUT_POST);
        $mbox = rcube_utils::get_input_string('mbox', rcube_utils::INPUT_POST) ?: 'INBOX';

        if (empty($uid)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing msg_uid']);
            exit;
        }

        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
        $mode = $prefs['genia_auto_draft_mode'] ?? 'open';
        if ($mode === 'disabled') {
            echo json_encode(['status' => 'skipped', 'message' => 'Auto-draft disabled']);
            exit;
        }

        session_write_close();

        $subj = $this->generate_autodraft_for_message((int) $uid, $mbox, $prefs);
        if ($subj) {
            echo json_encode(['status' => 'success', 'created' => true, 'subject' => $subj]);
        } else {
            echo json_encode(['status' => 'skipped', 'created' => false]);
        }
        exit;
    }

    /**
     * Hook triggered when new messages arrive in mailbox.
     * Evaluates incoming emails for spam on the server side:
     * - Adds SPAM label flags (Junk, $Junk, $Label1)
     * - Automatically moves spam emails to the Junk/Spam folder
     * - Continues to auto-draft only for verified non-spam emails
     */
    public function handle_new_messages($args)
    {
        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];

        $mbox = $args['mailbox'] ?? 'INBOX';
        if (strtoupper($mbox) !== 'INBOX') {
            return;
        }

        $storage = $rcmail->get_storage();
        $storage->set_folder($mbox);

        $uids = $storage->search($mbox, 'UNSEEN RECENT');
        if (empty($uids)) {
            $uids = $storage->search($mbox, 'UNSEEN');
        }

        // Safe extraction from rcube_result_set to prevent PHP 8 TypeError
        if (is_object($uids) && method_exists($uids, 'get')) {
            $uids = $uids->get();
        } elseif (is_object($uids) && method_exists($uids, 'count') && count($uids) === 0) {
            $uids = [];
        }

        if (!is_array($uids) || empty($uids)) {
            return;
        }

        // 1. Server-Side Automatic Spam Filter Check
        $spam_enabled = (bool) ($prefs['lifeprisma_ai_spam_filter_enabled'] ?? $rcmail->config->get('lifeprisma_ai_spam_filter_enabled', true));
        $spam_uids = [];
        $non_spam_uids = [];

        if ($spam_enabled) {
            $junk_mbox = $this->get_junk_folder();
            $user_id = $this->get_user_identifier();
            $spam_action = $prefs['lifeprisma_ai_spam_action'] ?? $rcmail->config->get('lifeprisma_ai_spam_action', 'move_and_label');
            $auto_learn = (bool) ($prefs['lifeprisma_ai_spam_auto_learn'] ?? $rcmail->config->get('lifeprisma_ai_spam_auto_learn', true));

            foreach ($uids as $uid) {
                $ctx = $this->fetch_message_context($uid, $mbox);
                $raw_headers = $this->fetch_raw_headers($uid, $mbox);
                if (empty($ctx)) {
                    $non_spam_uids[] = $uid;
                    continue;
                }

                $decision = LpaiSpamFilter::check_message(
                    $ctx['subject'] ?? '',
                    $ctx['body'] ?? '',
                    $raw_headers,
                    $ctx['from'] ?? '',
                    $user_id,
                    $prefs
                );

                if ($decision['is_spam']) {
                    $spam_uids[] = $uid;

                    // Add SPAM label flags: Junk, $Junk, and $Label1 (thunderbird / roundcube-labels red badge)
                    if ($spam_action === 'move_and_label' || $spam_action === 'label_only') {
                        $storage->set_flag($uid, 'Junk', $mbox);
                        $storage->set_flag($uid, '$Junk', $mbox);
                        $storage->set_flag($uid, '$Label1', $mbox);
                    }

                    // Auto-train Bayesian learning model on arrival
                    if ($auto_learn) {
                        $msg_id = $this->extract_message_id($raw_headers);
                        LpaiSpamFilter::learn_spam($ctx['subject'] ?? '', $ctx['body'] ?? '', $raw_headers, $ctx['from'] ?? '', $user_id, $msg_id);
                    }

                    // Move to Spam folder automatically
                    if ($spam_action === 'move_and_label' || $spam_action === 'move_only') {
                        $storage->move_message($uid, $junk_mbox, $mbox);
                    }
                } else {
                    $non_spam_uids[] = $uid;
                }
            }

            if (!empty($spam_uids)) {
                $scount = count($spam_uids);
                $smsg = ($scount === 1)
                    ? "1 spam email detected and moved to {$junk_mbox} folder"
                    : "{$scount} spam emails detected and moved to {$junk_mbox} folder";
                $rcmail->output->command('display_message', $smsg, 'warning');
            }
        } else {
            $non_spam_uids = $uids;
        }

        // 2. Auto-Draft Mode for Remaining Non-Spam Messages
        $mode = $prefs['genia_auto_draft_mode'] ?? 'disabled';
        if ($mode !== 'receive' || empty($non_spam_uids)) {
            return;
        }

        // Process at most 2 most recent non-spam messages per check
        $candidate_uids = array_slice(array_reverse($non_spam_uids), 0, 2);
        $created_subjects = [];

        foreach ($candidate_uids as $uid) {
            $subj = $this->generate_autodraft_for_message((int) $uid, $mbox, $prefs);
            if ($subj) {
                $created_subjects[] = $subj;
            }
        }

        if (!empty($created_subjects)) {
            $count = count($created_subjects);
            $msg = ($count === 1)
                ? "Gemini prepared an AI draft reply for '{$created_subjects[0]}' in Drafts"
                : "Gemini prepared {$count} AI draft replies in Drafts";
            $rcmail->output->command('display_message', $msg, 'confirmation');
        }
    }

    /**
     * Hook triggered when rendering the mailbox message list table.
     * Detects IMAP label flags ($Label1 - $Label5) and SPAM flags (Junk, $Junk),
     * passing them to the frontend so colored label & SPAM badges are rendered directly on rows.
     */
    public function handle_messages_list($args)
    {
        if (empty($args['messages']) || !is_array($args['messages'])) {
            return $args;
        }

        $row_labels = [];
        $row_spams = [];
        $junk_mbox = $this->get_junk_folder();
        $rcmail = rcmail::get_instance();
        $curr_mbox = (string) ($rcmail->storage ? $rcmail->storage->get_folder() : '');
        $is_junk_folder = (strcasecmp($curr_mbox, $junk_mbox) === 0);

        foreach ($args['messages'] as $header) {
            if (empty($header) || empty($header->uid)) continue;

            $uid_str = (string) $header->uid;
            $has_junk_flag = false;

            if (!empty($header->flags) && is_array($header->flags)) {
                foreach ($header->flags as $flag_name => $val) {
                    $flag_lower = strtolower((string) $flag_name);

                    if ($flag_lower === 'junk' || $flag_lower === '$junk' || $flag_lower === 'spam') {
                        $has_junk_flag = true;
                    }

                    $idx = null;
                    if (preg_match('/^\$label([0-9]+)$/i', $flag_lower, $m)) {
                        $idx = $m[1];
                    } elseif (preg_match('/^label([0-9]+)$/i', $flag_lower, $m)) {
                        $idx = $m[1];
                    }

                    if ($idx !== null) {
                        $canonical = '$Label' . $idx;
                        if (!isset($row_labels[$uid_str])) {
                            $row_labels[$uid_str] = [];
                        }
                        if (!in_array($canonical, $row_labels[$uid_str], true)) {
                            $row_labels[$uid_str][] = $canonical;
                        }

                        // Ensure row receives CSS classes in standard Roundcube rendering
                        if (!is_array($header->list_flags)) {
                            $header->list_flags = [];
                        }
                        $header->list_flags['label-' . $idx] = 1;
                    }
                }
            }

            if ($is_junk_folder || $has_junk_flag) {
                if (!is_array($header->list_flags)) {
                    $header->list_flags = [];
                }
                $header->list_flags['spam'] = 1;
                $row_spams[$uid_str] = true;
            }
        }

        if (!empty($row_labels)) {
            $rcmail->output->set_env('lpai_row_labels', $row_labels);
            $rcmail->output->command('plugin.lifeprisma_ai_sync_labels', $row_labels);
        }

        if (!empty($row_spams)) {
            $rcmail->output->set_env('lpai_row_spams', $row_spams);
            $rcmail->output->command('plugin.lifeprisma_ai_sync_spams', $row_spams);
        }

        return $args;
    }

    /**
     * Generate an AI draft for a message and save to Drafts
     */
    public function generate_autodraft_for_message($uid, $mbox, $prefs)
    {
        $rcmail = rcmail::get_instance();
        $cache_key = $this->cache_user_prefix() . "autodraft:done:{$mbox}:{$uid}";

        if ($this->cache_get($cache_key) !== null) {
            return false;
        }

        // Preference log check
        $log = $prefs['genia_autodraft_log'] ?? [];
        $item_key = "{$mbox}:{$uid}";
        if (isset($log[$item_key]) && (time() - ($log[$item_key]['time'] ?? 0) < 86400 * 7)) {
            return false;
        }

        $ctx = $this->fetch_message_context($uid, $mbox);
        if (empty($ctx) || empty($ctx['body'])) return false;

        $subject = $ctx['subject'] ?? '';
        $from = $ctx['from'] ?? '';
        $body = $ctx['body'] ?? '';
        $date = $ctx['date'] ?? '';

        // Filter bulk messages
        $raw_headers = $this->fetch_raw_headers($uid, $mbox);
        if (!empty($raw_headers)) {
            if (preg_match('/\b(List-Unsubscribe|List-Id|Precedence:\s*(bulk|list|junk)|Auto-Submitted:\s*auto)/i', $raw_headers)) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_bulk');
                return false;
            }
        }

        // Filter self messages
        $identities = $rcmail->user ? $rcmail->user->list_identities() : [];
        foreach ($identities as $ident) {
            if (!empty($ident['email']) && stripos($from, $ident['email']) !== false) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_self');
                return false;
            }
        }

        // Smart filter: check question or actionable trigger
        if (!empty($prefs['genia_auto_draft_filter'])) {
            $text = $subject . ' ' . $body;
            $has_action = (strpos($text, '?') !== false) ||
                preg_match('/\b(please|could you|can you|let me know|what do you think|confirm|feedback|reply|respond|waiting for|deadline|meeting|schedule|availability|asap)\b/i', $text);
            if (!$has_action) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_no_action');
                return false;
            }
        }

        $gemini = $this->get_gemini_config();
        if (empty($gemini['api_key'])) return false;

        $identity = $rcmail->user ? $rcmail->user->get_identity() : [];
        $sender_name = trim(($identity['name'] ?? '') . ' <' . ($identity['email'] ?? '') . '>');
        $language = $prefs['genia_language'] ?? 'English';
        $tone = $prefs['genia_tone'] ?? 'professional';

        $instruction = "Draft a polite and helpful executive response addressing all points in this email.";
        $reply_result = $this->call_gemini_direct('reply', $instruction, '', $body, $subject, $language, $tone, $sender_name, $from, $gemini);

        if (empty($reply_result)) return false;

        $orig_msg_id = '';
        if (preg_match('/^Message-ID:\s*(<[^>]+>)/im', $raw_headers, $m)) {
            $orig_msg_id = trim($m[1]);
        }

        $saved = $this->create_imap_draft($from, $subject, $reply_result, $orig_msg_id, $date, $from, $body);
        if ($saved) {
            $this->mark_autodraft_done($uid, $mbox, 'draft_created', ['subject' => $subject]);
            return $subject;
        }

        return false;
    }

    private function mark_autodraft_done($uid, $mbox, $status, $extra = [])
    {
        $cache_key = $this->cache_user_prefix() . "autodraft:done:{$mbox}:{$uid}";
        $data = array_merge(['status' => $status, 'time' => time()], $extra);
        $this->cache_set($cache_key, $data, 86400 * 7);

        $rcmail = rcmail::get_instance();
        if ($rcmail->user) {
            $prefs = $rcmail->user->get_prefs();
            $log = $prefs['genia_autodraft_log'] ?? [];
            $item_key = "{$mbox}:{$uid}";
            $log[$item_key] = ['status' => $status, 'time' => time()];
            if (count($log) > 200) {
                $cutoff = time() - (86400 * 7);
                $log = array_filter($log, function ($e) use ($cutoff) { return ($e['time'] ?? 0) > $cutoff; });
                if (count($log) > 150) $log = array_slice($log, -150, null, true);
            }
            $rcmail->user->save_prefs(['genia_autodraft_log' => $log]);
        }
    }

    /**
     * Direct non-streaming Gemini call
     */
    private function call_gemini_direct($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, $original_sender, $gemini)
    {
        $api_key = $gemini['api_key'];
        $model = $gemini['model'];
        $api_url = $gemini['api_url'];

        if (empty($api_key) || !$this->validate_api_url($api_url)) return false;

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, '', $original_sender, '');

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $user_prompt],
            ],
            'max_tokens' => 2048,
            'temperature' => 0.4,
        ];

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || empty($response)) return false;

        $data = json_decode($response, true);
        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    /**
     * Save draft to IMAP mailbox with RFC 2047 encoding & CRLF injection protection
     */
    private function create_imap_draft($to, $subject, $reply_body, $orig_msg_id = '', $orig_date = '', $orig_from = '', $orig_body = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            if (!$storage) {
                $this->ai_log("[AUTODRAFT ERROR] Storage connection unavailable");
                return false;
            }

            $drafts_mbox = $rcmail->config->get('drafts_mbox', 'Drafts');

            if (!$storage->folder_exists($drafts_mbox)) {
                $storage->folder_create($drafts_mbox, true);
            }

            $identity = $rcmail->user ? $rcmail->user->get_identity() : [];
            if (!is_array($identity)) $identity = [];
            $from_name = $identity['name'] ?? '';
            $from_email = $identity['email'] ?? '';
            $from_str = $from_name ? "\"$from_name\" <$from_email>" : $from_email;

            $re_subject = (stripos($subject, 'Re:') === 0) ? $subject : 'Re: ' . $subject;

            $quoted = '';
            if (!empty($orig_body)) {
                $date_str = $orig_date ? "On $orig_date, " : "On earlier message, ";
                $from_info = $orig_from ? "$orig_from wrote:" : "sender wrote:";
                $quoted = "\n\n" . $date_str . $from_info . "\n" . preg_replace('/^/m', '> ', trim($orig_body));
            }

            $full_body = trim($reply_body) . $quoted;
            $domain = $rcmail->config->mail_domain() ?: 'localhost';
            $msg_id = '<' . md5(uniqid((string) microtime(), true)) . '@' . $domain . '>';

            $clean_to = preg_replace('/[\r\n]+/', ' ', trim($to));
            $clean_subject = preg_replace('/[\r\n]+/', ' ', trim($re_subject));
            $clean_from = preg_replace('/[\r\n]+/', ' ', trim($from_str));
            $clean_msg_id = preg_replace('/[\r\n]+/', '', trim($msg_id));
            $clean_orig_id = preg_replace('/[\r\n]+/', '', trim($orig_msg_id));

            $encoded_subject = class_exists('rcube_mime')
                ? rcube_mime::encode_header('Subject', $clean_subject)
                : 'Subject: ' . $clean_subject;

            $headers = [
                'Date: ' . date('r'),
                'From: ' . $clean_from,
                'To: ' . $clean_to,
                $encoded_subject,
                'Message-ID: ' . $clean_msg_id,
                'X-Mailer: Gemini Executive Assistant',
                'X-Gemini-AutoDraft: 1',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];

            if (!empty($clean_orig_id)) {
                $headers[] = 'In-Reply-To: ' . $clean_orig_id;
                $headers[] = 'References: ' . $clean_orig_id;
            }

            $raw_message = implode("\r\n", $headers) . "\r\n\r\n" . $full_body;
            return (bool) $storage->save_message($drafts_mbox, $raw_message, '', false, ['SEEN', 'DRAFT']);
        } catch (\Throwable $e) {
            $this->ai_log("[AUTODRAFT EXCEPTION] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to fetch message plain context
     */
    private function fetch_message_context($uid, $mbox = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            if (!$storage) {
                return [];
            }
            if (!empty($mbox)) {
                $storage->set_folder($mbox);
            }

            $msg = new rcube_message((int) $uid, $mbox ?: null);
            if (empty($msg->headers)) return [];

            $body = '';
            $part = null;
            $text_body = $msg->first_text_part($part);
            if (!empty($text_body)) {
                $body = $text_body;
            } else {
                $part = null;
                $html_body = $msg->first_html_part($part);
                if (!empty($html_body)) {
                    if (class_exists('rcube_html2text')) {
                        $h2t = new rcube_html2text($html_body);
                        $body = $h2t->get_text();
                    } else {
                        $body = strip_tags($html_body);
                    }
                }
            }

            // Extract spam score
            $spam_score = null;
            $spam_header = $msg->headers->others['x-spam-status'] ?? '';
            if (is_array($spam_header)) $spam_header = end($spam_header);
            if ($spam_header && preg_match('/\bscore=(-?[0-9]+(?:\.[0-9]+)?)/i', $spam_header, $m)) {
                $spam_score = (float) $m[1];
            }
            if ($spam_score === null) {
                $bar = $msg->headers->others['x-spamd-bar'] ?? '';
                if (is_array($bar)) $bar = end($bar);
                if ($bar) {
                    $plus = substr_count($bar, '+');
                    $minus = substr_count($bar, '-');
                    if ($plus > 0 || $minus > 0) {
                        $spam_score = (float) ($plus - $minus);
                    }
                }
            }

            return [
                'subject' => $msg->headers->subject ?? '',
                'from' => $msg->headers->from ?? '',
                'to' => $msg->headers->to ?? '',
                'date' => $msg->headers->date ?? '',
                'body' => trim($body),
                'spam_score' => $spam_score,
            ];
        } catch (\Throwable $e) {
            $this->ai_log("[FETCH CONTEXT ERROR] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch raw headers for security check
     */
    private function fetch_raw_headers($uid, $mbox = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            if (!$storage) {
                return '';
            }
            if (!empty($mbox)) {
                $storage->set_folder($mbox);
            }

            if (!method_exists($storage, 'get_raw_headers')) {
                return '';
            }

            $raw = $storage->get_raw_headers((int) $uid);
            if (empty($raw) || !is_string($raw)) return '';

            $relevant_headers = [
                'From', 'To', 'Reply-To', 'Return-Path', 'Subject', 'Date',
                'Message-ID', 'X-Mailer', 'X-Originating-IP',
                'Received-SPF', 'Authentication-Results', 'DKIM-Signature',
                'ARC-Authentication-Results', 'X-Spam-Status', 'X-Spam-Score',
                'Content-Type', 'MIME-Version', 'Received',
                'List-Unsubscribe', 'List-Id', 'Precedence', 'Auto-Submitted'
            ];

            $lines = explode("\n", $raw);
            $filtered = [];
            $capturing = false;

            foreach ($lines as $line) {
                if (preg_match('/^([A-Za-z\-]+):\s*(.*)$/', $line, $m)) {
                    $capturing = false;
                    foreach ($relevant_headers as $h) {
                        if (strcasecmp($m[1], $h) === 0) {
                            $filtered[] = $line;
                            $capturing = true;
                            break;
                        }
                    }
                } elseif ($capturing && preg_match('/^\s+/', $line)) {
                    $filtered[] = $line;
                } else {
                    $capturing = false;
                }
            }

            return implode("\n", $filtered);
        } catch (\Throwable $e) {
            $this->ai_log("[FETCH RAW HEADERS ERROR] " . $e->getMessage());
            return '';
        }
    }

    private function get_attachment_info($uid, $mbox = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            if (!$storage) {
                return [];
            }
            if (!empty($mbox)) {
                $storage->set_folder($mbox);
            }

            $msg = new rcube_message((int) $uid, $mbox ?: null);
            if (empty($msg->headers) || empty($msg->attachments)) return [];

            $attachments = [];
            foreach ($msg->attachments as $part) {
                $attachments[] = [
                    'name' => $part->filename ?: ('part-' . $part->mime_id),
                    'type' => $part->mimetype,
                    'size' => $part->size,
                ];
            }
            return $attachments;
        } catch (\Throwable $e) {
            $this->ai_log("[FETCH ATTACHMENT ERROR] " . $e->getMessage());
            return [];
        }
    }

    /**
     * User Preferences Section Setup
     */
    public function preferences_sections($args)
    {
        $args['list']['genia'] = [
            'id' => 'genia',
            'section' => 'Gemini Assistant',
        ];
        $args['list']['lpai_spam'] = [
            'id' => 'lpai_spam',
            'section' => 'Spam Filter',
        ];
        if ($this->is_admin()) {
            $args['list']['genia_admin'] = [
                'id' => 'genia_admin',
                'section' => 'Gemini Assistant Admin',
            ];
        }
        return $args;
    }

    public function preferences_list($args)
    {
        if ($args['section'] === 'genia_admin') {
            return $this->admin_preferences_list($args);
        }
        if ($args['section'] === 'lpai_spam') {
            return $this->spam_preferences_list($args);
        }
        if ($args['section'] !== 'genia') return $args;

        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];

        $languages = ['English' => 'English', 'Portuguese' => 'Portuguese', 'Spanish' => 'Spanish', 'French' => 'French', 'German' => 'German', 'Italian' => 'Italian', 'Dutch' => 'Dutch'];
        $tones = ['professional' => 'Professional', 'concise' => 'Concise', 'friendly' => 'Friendly', 'formal' => 'Formal', 'direct' => 'Direct'];

        $lang_select = new html_select(['name' => '_genia_language', 'id' => 'genia_language']);
        foreach ($languages as $k => $v) $lang_select->add($v, $k);

        $tone_select = new html_select(['name' => '_genia_tone', 'id' => 'genia_tone']);
        foreach ($tones as $k => $v) $tone_select->add($v, $k);

        $auto_draft_mode_select = new html_select(['name' => '_genia_auto_draft_mode', 'id' => 'genia_auto_draft_mode']);
        $auto_draft_mode_select->add('When opening/reading an email (Instant Triage & Draft)', 'open');
        $auto_draft_mode_select->add('On new incoming email (background triage)', 'receive');
        $auto_draft_mode_select->add('Disabled (manual trigger only)', 'disabled');

        $auto_draft_filter_checkbox = new html_checkbox(['name' => '_genia_auto_draft_filter', 'id' => 'genia_auto_draft_filter', 'value' => 1]);
        $smart_compose_checkbox = new html_checkbox(['name' => '_genia_smart_compose', 'id' => 'genia_smart_compose', 'value' => 1]);
        $auto_draft_checkbox = new html_checkbox(['name' => '_genia_auto_draft', 'id' => 'genia_auto_draft', 'value' => 1]);

        $args['blocks']['genia_general'] = [
            'name' => 'Gemini Executive Assistant Preferences',
            'options' => [
                'genia_auto_draft_mode' => [
                    'title' => 'Autonomous Assistant Mode',
                    'content' => $auto_draft_mode_select->show($prefs['genia_auto_draft_mode'] ?? 'open'),
                ],
                'genia_language' => [
                    'title' => 'Default language for briefings & replies',
                    'content' => $lang_select->show($prefs['genia_language'] ?? 'English'),
                ],
                'genia_tone' => [
                    'title' => 'Default executive tone',
                    'content' => $tone_select->show($prefs['genia_tone'] ?? 'professional'),
                ],
                'genia_auto_draft_filter' => [
                    'title' => 'Smart Filter (skip marketing/newsletters)',
                    'content' => $auto_draft_filter_checkbox->show($prefs['genia_auto_draft_filter'] ?? 1),
                ],
                'genia_auto_draft' => [
                    'title' => 'Automatically save prepared reply to Drafts folder',
                    'content' => $auto_draft_checkbox->show($prefs['genia_auto_draft'] ?? 0),
                ],
                'genia_smart_compose' => [
                    'title' => 'Smart Compose (inline AI autocomplete while typing)',
                    'content' => $smart_compose_checkbox->show($prefs['genia_smart_compose'] ?? 1),
                ],
            ],
        ];

        return $args;
    }

    public function preferences_save($args)
    {
        if ($args['section'] === 'genia_admin') return $args;
        if ($args['section'] === 'lpai_spam') {
            return $this->spam_preferences_save($args);
        }
        if ($args['section'] !== 'genia') return $args;

        $args['prefs']['genia_language'] = rcube_utils::get_input_string('_genia_language', rcube_utils::INPUT_POST);
        $args['prefs']['genia_tone'] = rcube_utils::get_input_string('_genia_tone', rcube_utils::INPUT_POST);
        $args['prefs']['genia_auto_draft_mode'] = rcube_utils::get_input_string('_genia_auto_draft_mode', rcube_utils::INPUT_POST) ?: 'open';
        $args['prefs']['genia_auto_draft_filter'] = rcube_utils::get_input_string('_genia_auto_draft_filter', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['genia_auto_draft'] = rcube_utils::get_input_string('_genia_auto_draft', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['genia_smart_compose'] = rcube_utils::get_input_string('_genia_smart_compose', rcube_utils::INPUT_POST) ? 1 : 0;

        return $args;
    }

    private function admin_preferences_list($args)
    {
        if (!$this->is_admin()) return $args;

        $args['blocks']['genia_admin_panel'] = [
            'name' => 'Google Gemini Configuration',
            'options' => [
                'genia_admin_app' => [
                    'content' => '<div id="lpai-admin-root" data-url-config="' . htmlspecialchars(rcmail::get_instance()->url('plugin.lifeprisma_ai_admin')) . '" data-url-save="' . htmlspecialchars(rcmail::get_instance()->url('plugin.lifeprisma_ai_admin_save')) . '" data-token="' . htmlspecialchars(rcmail::get_instance()->get_request_token()) . '"></div>' .
                    '<script>if(window.lpai_init_admin)lpai_init_admin();</script>',
                ],
            ],
        ];

        return $args;
    }

    /**
     * Admin endpoints
     */
    public function handle_admin()
    {
        if (!$this->is_admin()) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $op = rcube_utils::get_input_string('op', rcube_utils::INPUT_POST) ?: rcube_utils::get_input_string('op', rcube_utils::INPUT_GET);

        if ($op === 'get_config') {
            $gemini = $this->get_gemini_config();
            $admin = $this->get_admin_config();
            $apiKey = $gemini['api_key'];

            $maskedKey = $apiKey ? (substr($apiKey, 0, 6) . '...' . substr($apiKey, -4)) : '';

            echo json_encode([
                'status' => 'success',
                'gemini' => [
                    'api_key_masked' => $maskedKey,
                    'has_key' => !empty($apiKey),
                    'model' => $gemini['model'],
                    'models' => $gemini['models'],
                    'api_url' => $gemini['api_url'],
                ],
                'settings' => [
                    'max_tokens' => $admin['max_tokens'] ?? $rcmail->config->get('lifeprisma_ai_max_tokens', 2048),
                    'temperature' => $admin['temperature'] ?? $rcmail->config->get('lifeprisma_ai_temperature', 0.4),
                    'rate_limit' => $admin['rate_limit'] ?? $rcmail->config->get('lifeprisma_ai_rate_limit', 2),
                    'default_language' => $admin['default_language'] ?? $rcmail->config->get('lifeprisma_ai_default_language', 'English'),
                    'default_tone' => $admin['default_tone'] ?? $rcmail->config->get('lifeprisma_ai_default_tone', 'professional'),
                    'auto_draft_mode' => $admin['auto_draft_mode'] ?? $rcmail->config->get('lifeprisma_ai_auto_draft_mode', 'open'),
                ],
                'usage' => $this->get_usage_stats(),
            ]);
            exit;
        }

        if ($op === 'test_connection') {
            $test_key = rcube_utils::get_input_string('api_key', rcube_utils::INPUT_POST);
            $gemini = $this->get_gemini_config();
            $key = !empty($test_key) ? trim($test_key) : $gemini['api_key'];

            if (empty($key)) {
                echo json_encode(['status' => 'error', 'message' => 'No API key provided']);
                exit;
            }

            session_write_close();

            $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/openai/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gemini-3.8-flash',
                    'messages' => [['role' => 'user', 'content' => 'Ping']],
                    'max_tokens' => 5,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($code === 200) {
                echo json_encode(['status' => 'success', 'message' => 'Google Gemini API connection verified successfully!']);
            } else {
                $msg = $err ?: "HTTP $code";
                if ($resp) {
                    $json = json_decode($resp, true);
                    if (!empty($json['error']['message'])) $msg .= ": " . $json['error']['message'];
                }
                echo json_encode(['status' => 'error', 'message' => 'Connection test failed: ' . $msg]);
            }
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
        exit;
    }

    public function handle_admin_save()
    {
        if (!$this->is_admin()) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
            exit;
        }

        $token = $data['_token'] ?? rcube_utils::get_input_string('_token', rcube_utils::INPUT_GET) ?? rcube_utils::get_input_string('_token', rcube_utils::INPUT_POST);
        if (!$this->check_csrf($token)) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
            exit;
        }

        $config = $this->get_admin_config();

        if (isset($data['gemini'])) {
            $g = $data['gemini'];
            if (!empty($g['api_key'])) {
                $config['gemini_api_key'] = trim($g['api_key']);
            }
            if (!empty($g['model'])) {
                $config['gemini_model'] = trim($g['model']);
            }
            if (!empty($g['api_url'])) {
                $url = trim($g['api_url']);
                if ($this->validate_api_url($url)) {
                    $config['api_url'] = $url;
                }
            }
        }

        if (isset($data['settings'])) {
            $s = $data['settings'];
            if (isset($s['max_tokens'])) $config['max_tokens'] = (int) $s['max_tokens'];
            if (isset($s['temperature'])) $config['temperature'] = (float) $s['temperature'];
            if (isset($s['rate_limit'])) $config['rate_limit'] = (int) $s['rate_limit'];
            if (isset($s['default_language'])) $config['default_language'] = $s['default_language'];
            if (isset($s['default_tone'])) $config['default_tone'] = $s['default_tone'];
            if (isset($s['auto_draft_mode'])) $config['auto_draft_mode'] = $s['auto_draft_mode'];
        }

        $this->save_admin_config($config);

        echo json_encode(['status' => 'success', 'message' => 'Gemini settings saved successfully']);
        exit;
    }

    /**
     * DB Config helpers
     */
    private function get_admin_config()
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $table = method_exists($db, 'table_name') ? $db->table_name('users') : 'users';

        $result = $db->query("SELECT preferences FROM {$table} WHERE username = ?", '__genia_admin__');
        if (!$result || is_bool($result)) return [];

        $row = $db->fetch_assoc($result);
        if ($row && !empty($row['preferences'])) {
            $data = @unserialize($row['preferences'], ['allowed_classes' => false]);
            return $data['genia_admin'] ?? [];
        }
        return [];
    }

    private function save_admin_config($config)
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $table = method_exists($db, 'table_name') ? $db->table_name('users') : 'users';

        $result = $db->query("SELECT user_id FROM {$table} WHERE username = ?", '__genia_admin__');
        $row = ($result && !is_bool($result)) ? $db->fetch_assoc($result) : null;

        $prefs = serialize(['genia_admin' => $config]);

        if ($row) {
            $db->query("UPDATE {$table} SET preferences = ? WHERE username = ?", $prefs, '__genia_admin__');
        } else {
            $now_expr = method_exists($db, 'now') ? $db->now() : 'CURRENT_TIMESTAMP';
            $db->query(
                "INSERT INTO {$table} (username, mail_host, preferences, created) VALUES (?, ?, ?, " . $now_expr . ")",
                '__genia_admin__', 'localhost', $prefs
            );
        }
    }

    private function get_usage_stats()
    {
        $cached = $this->cache_get('admin_usage_stats');
        if (is_array($cached) && isset($cached['total_users'])) {
            return $cached;
        }

        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $table = method_exists($db, 'table_name') ? $db->table_name('users') : 'users';

        $result = $db->query("SELECT COUNT(*) as total_users FROM {$table} WHERE username != '__genia_admin__'");
        $row = ($result && !is_bool($result)) ? $db->fetch_assoc($result) : null;
        $total_users = $row['total_users'] ?? 0;

        $result = $db->query("SELECT COUNT(*) as active_users FROM {$table} WHERE username != '__genia_admin__' AND preferences LIKE '%genia_%'");
        $row = ($result && !is_bool($result)) ? $db->fetch_assoc($result) : null;
        $active_users = $row['active_users'] ?? 0;

        $stats = [
            'total_users' => (int) $total_users,
            'active_users' => (int) $active_users,
        ];
        $this->cache_set('admin_usage_stats', $stats, 3600);
        return $stats;
    }

    private function is_admin()
    {
        $rcmail = rcmail::get_instance();
        $admins = $rcmail->config->get('lifeprisma_ai_admins', []);
        if (empty($admins)) return false;
        if (empty($rcmail->user)) return false;

        $identity = $rcmail->user->get_identity();
        $email = $identity['email'] ?? '';
        $username = $rcmail->user->get_username();

        return in_array($email, $admins, true) || in_array($username, $admins, true);
    }

    private function check_csrf($token_override = null)
    {
        $rcmail = rcmail::get_instance();
        if ($token_override !== null && method_exists($rcmail, 'get_request_token')) {
            $valid_token = $rcmail->get_request_token();
            return hash_equals((string) $valid_token, (string) $token_override);
        }
        if (method_exists($rcmail, 'check_request_token')) {
            return (bool) ($rcmail->check_request_token(rcube_utils::INPUT_POST) || $rcmail->check_request_token(rcube_utils::INPUT_GET));
        }
        return true;
    }

    private function validate_api_url($url)
    {
        if (empty($url) || !is_string($url)) return false;
        $parts = parse_url($url);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) return false;

        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'https') return false;

        $host = strtolower($parts['host']);
        // Strictly allow Google generative AI host or custom subdomains
        if ($host === 'generativelanguage.googleapis.com' || str_ends_with($host, '.googleapis.com')) {
            return true;
        }

        // Validate IP or resolve hostname to check against private/reserved IP ranges (SSRF protection)
        $resolved_ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (empty($resolved_ip) || ($resolved_ip === $host && !filter_var($host, FILTER_VALIDATE_IP))) {
            return false;
        }

        if (filter_var($resolved_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $this->ai_log("[SECURITY] Blocked SSRF attempt resolving to private/reserved IP: " . $resolved_ip . " (host: {$host})");
            return false;
        }

        return true;
    }

    private function check_rate_limit($action = '')
    {
        $rcmail = rcmail::get_instance();
        $cooldown = (int) $rcmail->config->get('lifeprisma_ai_rate_limit', 2);
        $max_per_min = (int) $rcmail->config->get('lifeprisma_ai_rate_limit_per_min', 60);

        if ($cooldown <= 0 && $max_per_min <= 0) return true;

        $now = microtime(true);
        $is_bg = in_array($action, ['triage', 'autocomplete', 'detect_tone'], true);
        $session_key = $is_bg ? 'lpai_last_bg_req' : 'lpai_last_req';
        $effective_cooldown = $is_bg ? 0.3 : $cooldown;

        if ($effective_cooldown > 0) {
            $last = isset($_SESSION[$session_key]) ? (float) $_SESSION[$session_key] : 0.0;
            if ($last > 0 && ($now - $last) >= 0 && ($now - $last) < $effective_cooldown) {
                return false;
            }
        }

        $hist_key = $is_bg ? 'lpai_bg_hist' : 'lpai_req_hist';
        $effective_max = $is_bg ? max(10, $max_per_min * 2) : $max_per_min;

        if ($effective_max > 0) {
            $history = $_SESSION[$hist_key] ?? [];
            if (!is_array($history)) $history = [];
            $history = array_values(array_filter($history, function ($t) use ($now) {
                return ($now - (float) $t) < 60.0;
            }));
            if (count($history) >= $effective_max) return false;
            $history[] = $now;
            $_SESSION[$hist_key] = $history;
        }

        $_SESSION[$session_key] = $now;
        return true;
    }

    /**
     * Cache helpers
     */
    /**
     * Cache helpers with resilient multi-tier fallback:
     * Tier 1: Static in-memory request-level cache
     * Tier 2: Redis (checked with class_exists, connect timeout, and Throwable guard)
     * Tier 3: Roundcube core database cache via $rcmail->get_cache()
     * Tier 4: Local JSON file cache fallback in plugin data directory or temp dir
     */
    private static $in_memory_cache = [];

    private function redis_connect()
    {
        static $redis = null;
        if ($redis !== null) return $redis;

        if (!class_exists('Redis')) {
            $redis = false;
            return false;
        }

        try {
            $r = new Redis();
            $connected = @$r->connect('127.0.0.1', 6379, 0.5);
            if ($connected) {
                @$r->setOption(Redis::OPT_PREFIX, 'gemini:');
                $redis = $r;
                return $redis;
            }
        } catch (\Throwable $e) {
            // Redis connection or configuration failed
        }

        $redis = false;
        return false;
    }

    private function get_rcube_cache($ttl = 86400)
    {
        try {
            $rcmail = rcmail::get_instance();
            if (method_exists($rcmail, 'get_cache')) {
                return $rcmail->get_cache('lifeprisma_ai', 'db', $ttl);
            }
        } catch (\Throwable $e) {}
        return null;
    }

    private function get_file_cache_dir()
    {
        $dir = $this->home . '/data/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            return $dir;
        }
        $sys_temp = sys_get_temp_dir() . '/lifeprisma_cache';
        if (!is_dir($sys_temp)) {
            @mkdir($sys_temp, 0755, true);
        }
        return is_dir($sys_temp) && is_writable($sys_temp) ? $sys_temp : sys_get_temp_dir();
    }

    private function cache_user_prefix()
    {
        $user = rcmail::get_instance()->user;
        return $user ? md5($user->get_username()) . ':' : '';
    }

    private function cache_get($key)
    {
        // 1. In-memory check
        if (array_key_exists($key, self::$in_memory_cache)) {
            return self::$in_memory_cache[$key];
        }

        // 2. Redis check
        $r = $this->redis_connect();
        if ($r) {
            try {
                $val = $r->get($key);
                if ($val !== false) {
                    $decoded = json_decode($val, true);
                    self::$in_memory_cache[$key] = $decoded;
                    return $decoded;
                }
            } catch (\Throwable $e) {}
        }

        // 3. Roundcube core cache check
        $rc_cache = $this->get_rcube_cache();
        if ($rc_cache && method_exists($rc_cache, 'get')) {
            try {
                $val = $rc_cache->get($key);
                if ($val !== null && $val !== false) {
                    $decoded = is_array($val) ? $val : json_decode((string)$val, true);
                    if ($decoded !== null) {
                        self::$in_memory_cache[$key] = $decoded;
                        return $decoded;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 4. File cache fallback
        try {
            $file = $this->get_file_cache_dir() . '/lpai_' . md5($key) . '.json';
            if (file_exists($file)) {
                $raw = @file_get_contents($file);
                if ($raw) {
                    $wrapper = json_decode($raw, true);
                    if (is_array($wrapper) && isset($wrapper['exp'], $wrapper['data'])) {
                        if ($wrapper['exp'] >= time()) {
                            self::$in_memory_cache[$key] = $wrapper['data'];
                            return $wrapper['data'];
                        } else {
                            @unlink($file);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    private function cache_set($key, $data, $ttl = 86400)
    {
        self::$in_memory_cache[$key] = $data;

        // 1. Redis
        $r = $this->redis_connect();
        if ($r) {
            try {
                $r->setex($key, $ttl, json_encode($data));
                return;
            } catch (\Throwable $e) {}
        }

        // 2. Roundcube core cache
        $rc_cache = $this->get_rcube_cache($ttl);
        if ($rc_cache && method_exists($rc_cache, 'set')) {
            try {
                $rc_cache->set($key, $data);
                return;
            } catch (\Throwable $e) {}
        }

        // 3. File cache fallback
        try {
            $file = $this->get_file_cache_dir() . '/lpai_' . md5($key) . '.json';
            $wrapper = [
                'exp' => time() + $ttl,
                'data' => $data,
            ];
            @file_put_contents($file, json_encode($wrapper), LOCK_EX);
        } catch (\Throwable $e) {}
    }

    private function ai_log($message)
    {
        $log_dir = defined('RCUBE_INSTALL_PATH') ? RCUBE_INSTALL_PATH . 'logs/' : __DIR__ . '/logs/';
        if (!is_dir($log_dir)) {
            $log_dir = sys_get_temp_dir() . '/';
        }
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        @file_put_contents($log_dir . 'gemini.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function get_enabled_features()
    {
        $config = $this->get_admin_config();
        return $config['features'] ?? [
            'compose' => true, 'rewrite' => true, 'reply' => true,
            'translate' => true, 'summarize' => true, 'fix' => true,
            'scam' => true, 'suggest_subject' => true, 'thread_summarize' => true,
        ];
    }

    public function handle_templates()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $op = rcube_utils::get_input_string('op', rcube_utils::INPUT_POST);
        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
        $templates = $prefs['genia_templates'] ?? [];

        if ($op === 'list') {
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        if ($op === 'save') {
            $id = trim(rcube_utils::get_input_string('id', rcube_utils::INPUT_POST));
            $name = trim(rcube_utils::get_input_string('name', rcube_utils::INPUT_POST));
            $instruction = trim(rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST));
            $action = rcube_utils::get_input_string('tpl_action', rcube_utils::INPUT_POST) ?: 'compose';

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Template name is required']);
                exit;
            }

            if (empty($id) && count($templates) >= 50) {
                echo json_encode(['status' => 'error', 'message' => 'Template limit reached (maximum 50)']);
                exit;
            }

            $instruction = mb_substr($instruction, 0, 2000);

            if (!empty($id)) {
                $found = false;
                foreach ($templates as &$t) {
                    if (($t['id'] ?? '') === $id) {
                        $t['name'] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                        $t['action'] = $action;
                        $t['instruction'] = $instruction;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $templates[] = [
                        'id' => $id,
                        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                        'action' => $action,
                        'instruction' => $instruction,
                        'attachments' => [],
                    ];
                }
            } else {
                $templates[] = [
                    'id' => uniqid('tpl_'),
                    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    'action' => $action,
                    'instruction' => $instruction,
                    'attachments' => [],
                ];
            }

            if ($rcmail->user) $rcmail->user->save_prefs(['genia_templates' => $templates]);
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        if ($op === 'upload_attachment') {
            $tpl_id = rcube_utils::get_input_string('tpl_id', rcube_utils::INPUT_POST);
            if (empty($tpl_id) || empty($_FILES['file'])) {
                echo json_encode(['status' => 'error', 'message' => 'Missing file or template ID']);
                exit;
            }

            $file = $_FILES['file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['status' => 'error', 'message' => 'File upload error: ' . $file['error']]);
                exit;
            }

            $clean_tpl_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $tpl_id);
            $orig_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $disallowed_exts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'inc', 'sh', 'cgi', 'pl', 'py', 'exe', 'htaccess', 'svg'];
            if (in_array($orig_ext, $disallowed_exts, true) || empty($orig_ext)) {
                echo json_encode(['status' => 'error', 'message' => 'Disallowed or dangerous file extension']);
                exit;
            }

            $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'zip'];
            if (!in_array($orig_ext, $allowed_exts, true)) {
                echo json_encode(['status' => 'error', 'message' => 'File type not permitted']);
                exit;
            }

            $upload_dir = $this->home . '/data/attachments/templates/' . $clean_tpl_id;
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0750, true);
            }

            $safe_name = md5(uniqid((string) microtime(true), true)) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
            $target_path = $upload_dir . '/' . $safe_name;

            if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
                exit;
            }

            $att_item = [
                'id' => uniqid('att_'),
                'name' => $file['name'],
                'size' => filesize($target_path),
                'mimetype' => mime_content_type($target_path) ?: 'application/octet-stream',
                'path' => 'data/attachments/templates/' . $clean_tpl_id . '/' . $safe_name,
            ];

            foreach ($templates as &$t) {
                if (($t['id'] ?? '') === $tpl_id) {
                    if (!isset($t['attachments']) || !is_array($t['attachments'])) {
                        $t['attachments'] = [];
                    }
                    $t['attachments'][] = $att_item;
                    break;
                }
            }
            if ($rcmail->user) $rcmail->user->save_prefs(['genia_templates' => $templates]);

            echo json_encode(['status' => 'success', 'attachment' => $att_item, 'templates' => $templates]);
            exit;
        }

        if ($op === 'delete_attachment') {
            $tpl_id = rcube_utils::get_input_string('tpl_id', rcube_utils::INPUT_POST);
            $att_id = rcube_utils::get_input_string('att_id', rcube_utils::INPUT_POST);

            foreach ($templates as &$t) {
                if (($t['id'] ?? '') === $tpl_id && !empty($t['attachments'])) {
                    $t['attachments'] = array_values(array_filter($t['attachments'], function ($a) use ($att_id) {
                        if (($a['id'] ?? '') === $att_id) {
                            $path = $this->home . '/' . ($a['path'] ?? '');
                            if (file_exists($path)) @unlink($path);
                            return false;
                        }
                        return true;
                    }));
                    break;
                }
            }
            if ($rcmail->user) $rcmail->user->save_prefs(['genia_templates' => $templates]);

            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        if ($op === 'delete') {
            $id = rcube_utils::get_input_string('id', rcube_utils::INPUT_POST);
            $templates = array_values(array_filter($templates, function ($t) use ($id) {
                return ($t['id'] ?? '') !== $id;
            }));

            if ($rcmail->user) $rcmail->user->save_prefs(['genia_templates' => $templates]);
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
        exit;
    }

    /**
     * System Prompts
     */
    private function build_system_prompt($action = '')
    {
        if ($action === 'suggest_subject') {
            return "You are an email subject line expert. Generate clear, concise, high-open-rate professional subject lines. Return ONLY a numbered list of 5 subject lines.";
        }

        if ($action === 'suggest_response_name') {
            return "You are an expert at creating concise, professional titles for email canned responses and message templates. Return ONLY a clear 2-4 word title in the requested language. Do not include quotes, markdown, bullet points, or any extra text.";
        }

        if ($action === 'thread_summarize') {
            return "You are an executive email analyst. Provide a structured summary of this conversation thread: Overview, Key Decisions, Action Items with assignees, and Current Status. Use markdown formatting.";
        }

        if ($action === 'autocomplete') {
            return "You are an email autocomplete engine. Return ONLY the completion text (1-2 sentences) naturally continuing what the user typed. Do not repeat existing text.";
        }

        if ($action === 'scam') {
            return "You are a cybersecurity expert specialized in email fraud detection. Analyze the email for phishing, social engineering, spoofing, and malicious intent. Provide a clear verdict (SAFE, SUSPICIOUS, or DANGEROUS) followed by specific evidence points.";
        }

        if ($action === 'newsletter_draft' || $action === 'newsletter') {
            return "You are an elite email marketing copywriter and deliverability specialist powered by Google Gemini.
Create high-converting, engaging newsletter content formatted in clean, modern email HTML with inline CSS styling.
Rules:
1. Generate structured, beautiful email layout: clean header, compelling hero title, engaging intro paragraph, 2-3 content sections or article highlights with clear headings, a prominent call-to-action button, and a polite sign-off.
2. Incorporate dynamic personalization tokens where appropriate ({first_name}, {name}, {unsubscribe_url}).
3. Write crisp, persuasive copy that avoids spam trigger words (no all-caps screaming, no excessive punctuation, no spam clichés).
4. Ensure clean HTML semantics compatible with all modern email clients and dual MIME rendering.
5. Return ONLY the newsletter HTML body content. No meta-commentary, markdown wrapping backticks, or conversational preamble.";
        }

        if ($action === 'newsletter_optimize_spam') {
            return "You are an email deliverability and anti-spam heuristic expert.
Review the provided newsletter subject and body. Identify deliverability risks, spam trigger patterns, and formatting traps.
Rewrite the content to maximize deliverability, readability, and inbox placement while preserving marketing impact.
Return ONLY the deliverability-optimized newsletter HTML.";
        }

        return "You are Google Gemini, an elite executive email assistant embedded in Roundcube webmail. Rules:
1. Return ONLY the final email text. No meta-commentary, no conversational filler.
2. Match requested tone and language precisely.
3. Natural, crisp, professional prose.
4. NEVER include email signatures or sign-off blocks (e.g. '--', 'Sincerely', name/title). The webmail client inserts user signatures automatically.";
    }

    private function build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name)
    {
        $prompt = "Task: {$action}\nLanguage: {$language}\nTone: {$tone}\n";
        if (!empty($subject)) $prompt .= "Subject/Title: {$subject}\n";
        if (!empty($sender_name)) $prompt .= "User: {$sender_name}\n";
        if (!empty($instruction)) $prompt .= "Instruction: {$instruction}\n";

        if (!empty($reply_text)) {
            $prompt .= "\nOriginal Email Content:\n{$reply_text}\n";
        }
        if (!empty($email_body)) {
            $prompt .= "\nCurrent Text/Template:\n{$email_body}\n";
        }

        return $prompt;
    }

    /**
     * Modal Assistant Panel HTML (Google Gemini Branding)
     */
    private function get_ai_panel_html($gemini)
    {
        $model_options = '';
        $models = !empty($gemini['models']) && is_array($gemini['models']) ? $gemini['models'] : [
            'gemini-3.8-flash',
            'gemini-3.8-flash-cyber',
            'gemini-3.7-flash',
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
        ];
        $current_model = $gemini['model'] ?? 'gemini-3.8-flash';
        foreach ($models as $m) {
            $selected = ($m === $current_model) ? ' selected' : '';
            $model_options .= '<option value="' . htmlspecialchars($m) . '"' . $selected . '>' . htmlspecialchars($m) . '</option>';
        }

        return '
<div id="lpai-overlay" style="display:none"></div>
<div id="lpai-panel" style="display:none">
    <div id="lpai-header">
        <div class="lpai-title-wrapper">
            <span class="lpai-gemini-sparkle">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/>
                </svg>
            </span>
            <span id="lpai-title">Gemini Assistant</span>
            <span class="lpai-model-tag">' . htmlspecialchars($gemini['model']) . '</span>
        </div>
        <button type="button" id="lpai-close" title="Close (Esc)">&times;</button>
    </div>

    <div id="lpai-body">
        <div id="lpai-actions">
            <button type="button" class="lpai-action-btn active" data-action="compose">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                <span>Compose</span>
            </button>
            <button type="button" class="lpai-action-btn" data-action="rewrite">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Rewrite</span>
            </button>
            <button type="button" class="lpai-action-btn" data-action="fix">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Fix Grammar</span>
            </button>
            <button type="button" class="lpai-action-btn" data-action="translate">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                <span>Translate</span>
            </button>
            <button type="button" class="lpai-action-btn" data-action="summarize">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="15" x2="3" y1="12" y2="12"/><line x1="17" x2="3" y1="18" y2="18"/></svg>
                <span>Summarize</span>
            </button>
            <button type="button" class="lpai-action-btn" data-action="suggest_subject">
                <svg class="lpai-btn-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
                <span>Subject Lines</span>
            </button>
        </div>

        <div id="lpai-controls">
            <div class="lpai-control-group">
                <label for="lpai-model-select">Model</label>
                <div class="lpai-custom-select" data-select-id="lpai-model-select">
                    <select id="lpai-model-select" class="lpai-select">' . $model_options . '</select>
                </div>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-tone-select">Tone</label>
                <div class="lpai-custom-select" data-select-id="lpai-tone-select">
                    <select id="lpai-tone-select" class="lpai-select">
                        <option value="professional">Professional</option>
                        <option value="concise">Concise</option>
                        <option value="friendly">Friendly</option>
                        <option value="formal">Formal</option>
                        <option value="direct">Direct</option>
                    </select>
                </div>
            </div>
            <div class="lpai-control-group">
                <label for="lpai-lang-select">Language</label>
                <div class="lpai-custom-select" data-select-id="lpai-lang-select">
                    <select id="lpai-lang-select" class="lpai-select">
                        <option value="English">English</option>
                        <option value="Dutch">Dutch</option>
                        <option value="Spanish">Spanish</option>
                        <option value="French">French</option>
                        <option value="German">German</option>
                        <option value="Portuguese">Portuguese</option>
                        <option value="Italian">Italian</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="lpai-input-wrapper">
            <textarea id="lpai-input" rows="4" placeholder="What should Gemini write or change?"></textarea>
            <div class="lpai-input-hint"><span>💡 Press <strong>Enter</strong> to generate &bull; <strong>Shift+Enter</strong> for a new line</span></div>
        </div>

        <div id="lpai-preview" style="display:none">
            <div id="lpai-preview-header">
                <span class="lpai-preview-title">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/></svg>
                    Result Preview
                </span>
                <span id="lpai-word-count"></span>
            </div>
            <div id="lpai-preview-content"></div>
        </div>

        <div id="lpai-loading" style="display:none">
            <div class="lpai-spinner"></div>
            <span>Gemini is generating response...</span>
        </div>
    </div>

    <div id="lpai-footer">
        <div id="lpai-footer-left">
            <span id="lpai-token-cost" class="lpai-cost-tag"></span>
        </div>
        <div id="lpai-footer-right">
            <button type="button" id="lpai-cancel" class="lpai-btn-secondary" style="display:none">Stop</button>
            <button type="button" id="lpai-copy" class="lpai-btn-secondary" style="display:none">Copy</button>
            <button type="button" id="lpai-apply" class="lpai-btn-apply" style="display:none">Insert into Email</button>
            <button type="button" id="lpai-generate" class="lpai-btn-generate">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
                    <path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/>
                </svg>
                <span>Generate</span>
            </button>
        </div>
    </div>
</div>';
    }

    /**
     * AI Memory Helpers (Knowledge Base & Verified Answer Replication)
     */
    public function get_memory_file()
    {
        $rcmail = rcmail::get_instance();
        $user_id = ($rcmail->user && isset($rcmail->user->ID)) ? (int) $rcmail->user->ID : 0;

        $dir = $this->home . '/data/memory';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        if ($user_id > 0) {
            $user_hash = md5("lpai_user_mem_" . $user_id);
            return $dir . '/user_' . $user_hash . '.json';
        }

        return $this->home . '/data/ai_memory.json';
    }

    public function load_ai_memory()
    {
        $file = $this->get_memory_file();
        if (!file_exists($file)) {
            return [];
        }
        $raw = @file_get_contents($file);
        $data = json_decode((string)$raw, true);
        return is_array($data) ? $data : [];
    }

    public function save_ai_memory(array $items)
    {
        $file = $this->get_memory_file();
        $rcmail = rcmail::get_instance();
        $max = (int) $rcmail->config->get('lifeprisma_ai_memory_max_items', 500);
        if (count($items) > $max) {
            $items = array_slice($items, -$max);
        }
        return @file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function find_matching_memory($query, array $memories, $limit = 3)
    {
        if (empty($memories) || empty($query)) return [];
        $words = preg_split('/[\s,\.\?\!\:\;]+/', mb_strtolower($query));
        $words = array_filter($words, function ($w) {
            return mb_strlen($w) >= 3 && !in_array($w, ['the','and','for','with','this','that','from','have','your','will','what','when','where','how','can','you','our','are','het','een','van','voor','met','dat','die','wat','wie','hoe','zou','kun']);
        });
        if (empty($words)) return [];

        $scored = [];
        foreach ($memories as $item) {
            $text = mb_strtolower(($item['question'] ?? '') . ' ' . ($item['subject'] ?? '') . ' ' . ($item['answer'] ?? ''));
            $score = 0;
            foreach ($words as $w) {
                if (mb_strpos($text, $w) !== false) {
                    $score += 2;
                }
            }
            if ($score > 0) {
                $scored[] = ['score' => $score, 'item' => $item];
            }
        }

        if (empty($scored)) {
            return [];
        }

        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $results = [];
        foreach (array_slice($scored, 0, $limit) as $s) {
            $results[] = $s['item'];
        }
        return $results;
    }

    public function add_ai_memory_item($question, $answer, $meta = [])
    {
        $question = trim($question);
        $answer = trim($answer);
        if (empty($question) || empty($answer)) return false;

        $memories = $this->load_ai_memory();
        $id = uniqid('mem_');
        $item = [
            'id' => $id,
            'question' => $question,
            'answer' => $answer,
            'subject' => $meta['subject'] ?? '',
            'client' => $meta['client'] ?? '',
            'source' => $meta['source'] ?? 'user_action',
            'created_at' => time(),
            'updated_at' => time(),
            'use_count' => 0,
        ];

        $found = false;
        foreach ($memories as &$m) {
            if (mb_strtolower(trim($m['question'])) === mb_strtolower($question)) {
                $m['answer'] = $answer;
                $m['updated_at'] = time();
                $item = $m;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $memories[] = $item;
        }

        $this->save_ai_memory($memories);
        return $item;
    }

    public function handle_memory()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired CSRF token']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        $op = rcube_utils::get_input_string('op', rcube_utils::INPUT_POST);

        if ($op === 'list') {
            $memories = $this->load_ai_memory();
            echo json_encode(['status' => 'success', 'memories' => $memories]);
            exit;
        }

        if ($op === 'add') {
            $question = rcube_utils::get_input_string('question', rcube_utils::INPUT_POST);
            $answer = rcube_utils::get_input_string('answer', rcube_utils::INPUT_POST);
            $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
            $client = rcube_utils::get_input_string('client', rcube_utils::INPUT_POST);

            $item = $this->add_ai_memory_item($question, $answer, [
                'subject' => $subject,
                'client' => $client,
                'source' => 'manual',
            ]);

            if ($item) {
                echo json_encode(['status' => 'success', 'item' => $item]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Question and answer cannot be empty']);
            }
            exit;
        }

        if ($op === 'delete') {
            $id = rcube_utils::get_input_string('id', rcube_utils::INPUT_POST);
            $memories = $this->load_ai_memory();
            $memories = array_values(array_filter($memories, function ($m) use ($id) {
                return ($m['id'] ?? '') !== $id;
            }));
            $this->save_ai_memory($memories);
            echo json_encode(['status' => 'success', 'deleted' => $id]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid memory operation']);
        exit;
    }

    public function handle_message_sent($args)
    {
        $rcmail = rcmail::get_instance();
        if (!$rcmail->config->get('lifeprisma_ai_memory_enabled', true) ||
            !$rcmail->config->get('lifeprisma_ai_memory_auto_learn', true)) {
            return;
        }

        $body = $args['body'] ?? '';
        $subject = $args['subject'] ?? '';
        if (empty($body) || empty($subject)) return;

        $lines = explode("\n", $body);
        $reply_lines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '>') === 0) continue;
            if (preg_match('/^(On\s+.+wrote:|Op\s+.+schreef:)/i', $trimmed)) break;
            if (preg_match('/^--\s*$/', $trimmed)) break;
            $reply_lines[] = $line;
        }
        $clean_reply = trim(implode("\n", $reply_lines));
        if (mb_strlen($clean_reply) < 20) return;

        $clean_subj = preg_replace('/^(Re|Fwd|Aw|Antw):\s*/i', '', $subject);
        $this->add_ai_memory_item(
            $clean_subj,
            $clean_reply,
            [
                'subject' => $subject,
                'source' => 'auto_sent',
            ]
        );
    }

    /**
     * Compose & Attachment helpers
     */
    public function handle_prepare_compose()
    {
        if (!$this->check_csrf()) {
            header('Content-Type: application/json; charset=utf-8', true, 403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
            exit;
        }

        $reply = rcube_utils::get_input_string('reply', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $attachments_raw = rcube_utils::get_input_string('attachments', rcube_utils::INPUT_POST);
        $attachments = !empty($attachments_raw) ? json_decode($attachments_raw, true) : [];

        $_SESSION['lpai_pending_compose'] = [
            'reply' => $reply,
            'subject' => $subject,
            'attachments' => is_array($attachments) ? $attachments : [],
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success']);
        exit;
    }

    public function handle_message_compose($args)
    {
        if (!empty($_SESSION['lpai_pending_compose'])) {
            $data = $_SESSION['lpai_pending_compose'];
            unset($_SESSION['lpai_pending_compose']);

            if (!empty($data['reply']) && empty($args['param']['body'])) {
                $args['param']['body'] = $data['reply'];
            }
            if (!empty($data['subject']) && empty($args['param']['subject'])) {
                $args['param']['subject'] = $data['subject'];
            }
            if (!empty($data['attachments']) && is_array($data['attachments'])) {
                if (!isset($args['attachments']) || !is_array($args['attachments'])) {
                    $args['attachments'] = [];
                }
                $rcmail = rcmail::get_instance();
                $att_dir = $this->home . '/data/attachments';
                if (!is_dir($att_dir)) {
                    @mkdir($att_dir, 0750, true);
                }
                $base_allowed = realpath($att_dir);
                $temp_dir = realpath($rcmail->config->get('temp_dir', sys_get_temp_dir()));

                foreach ($data['attachments'] as $att) {
                    $full_path = $att['path'] ?? '';
                    if (empty($full_path)) continue;

                    $target = (strpos($full_path, '/') === 0) ? $full_path : ($this->home . '/' . $full_path);
                    $resolved = realpath($target);

                    // Strictly confine attachments to data/attachments or Roundcube's temp_dir
                    $is_in_base = ($base_allowed && $resolved && strpos($resolved, $base_allowed) === 0);
                    $is_in_temp = ($temp_dir && $resolved && strpos($resolved, $temp_dir) === 0);

                    if (!$resolved || (!$is_in_base && !$is_in_temp)) {
                        $this->ai_log("[SECURITY] Blocked unauthorized compose attachment path traversal: " . $full_path);
                        continue;
                    }

                    if (file_exists($resolved)) {
                        $args['attachments'][] = [
                            'path' => $resolved,
                            'name' => $att['name'] ?? basename($resolved),
                            'mimetype' => $att['mimetype'] ?? 'application/octet-stream',
                        ];
                    }
                }
            }
        }
        return $args;
    }

    // =========================================================================
    // Advanced Spam Filter & Self-Learning Engine Integration
    // =========================================================================

    /**
     * Resolves the current user's unique identifier for model storage.
     */
    public function get_user_identifier(): string
    {
        $rcmail = rcmail::get_instance();
        return ($rcmail->user && method_exists($rcmail->user, 'get_username'))
            ? (string) $rcmail->user->get_username()
            : 'default_user';
    }

    /**
     * Resolves the configured or special-use Junk/Spam mailbox folder name.
     */
    public function get_junk_folder(): string
    {
        $rcmail = rcmail::get_instance();
        $junk = $rcmail->config->get('junk_mbox');
        if (!empty($junk)) {
            return $junk;
        }
        $storage = $rcmail->get_storage();
        if ($storage && method_exists($storage, 'get_special_folder')) {
            $sp = $storage->get_special_folder('junk');
            if (!empty($sp)) return $sp;
        }
        return 'Junk';
    }

    /**
     * Extracts Message-ID header from raw email headers.
     */
    private function extract_message_id(string $raw_headers): string
    {
        if (preg_match('/^Message-ID:\s*(<[^>]+>|[^\r\n]+)/mi', $raw_headers, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /**
     * AJAX Action: Tag selected message(s) as SPAM.
     * - Adds SPAM label flags: Junk, $Junk, and $Label1 (Important/Warning red label)
     * - Feeds the message to the statistical Bayesian learning engine (SPAM)
     * - Moves the email automatically to the Junk/Spam folder
     */
    public function handle_spam_tag()
    {
        $rcmail = rcmail::get_instance();
        $uids = rcube_utils::get_input_value('_uids', rcube_utils::INPUT_POST);
        if (empty($uids)) {
            $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
            if ($uid) $uids = [$uid];
        }
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST) ?: 'INBOX';

        if (empty($uids)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No messages specified']);
            exit;
        }

        $storage = $rcmail->get_storage();
        $storage->set_folder($mbox);
        $junk_mbox = $this->get_junk_folder();
        $user_id = $this->get_user_identifier();

        $count = 0;
        foreach ((array) $uids as $uid) {
            $ctx = $this->fetch_message_context($uid, $mbox);
            $raw_headers = $this->fetch_raw_headers($uid, $mbox);
            $msg_id = $raw_headers ? $this->extract_message_id($raw_headers) : '';

            // 1. Add SPAM flags (standard Junk flags + red Label1)
            $storage->set_flag($uid, 'Junk', $mbox);
            $storage->set_flag($uid, '$Junk', $mbox);
            $storage->set_flag($uid, '$Label1', $mbox);

            // 2. Train Bayesian engine & update sender reputation
            if (!empty($ctx)) {
                LpaiSpamFilter::learn_spam(
                    $ctx['subject'] ?? '',
                    $ctx['body'] ?? '',
                    $raw_headers,
                    $ctx['from'] ?? '',
                    $user_id,
                    $msg_id
                );
            }

            // 3. Move message to Junk folder
            if (strcasecmp($mbox, $junk_mbox) !== 0) {
                $storage->move_message($uid, $junk_mbox, $mbox);
            }
            $count++;
        }

        header('Content-Type: application/json');
        $msg = ($count === 1)
            ? 'Message tagged as spam and moved to Junk folder. Spam filter learned.'
            : "{$count} messages tagged as spam and moved to Junk folder. Spam filter learned.";
        echo json_encode(['status' => 'success', 'message' => $msg, 'count' => $count, 'stats' => LpaiSpamFilter::get_stats($user_id)]);
        exit;
    }

    /**
     * AJAX Action: Untag selected message(s) (Mark as NOT SPAM / HAM).
     * - Removes SPAM label flags: Junk, $Junk, $Label1
     * - Feeds the message to the statistical Bayesian learning engine (HAM, reversing spam count)
     * - Moves the email back to the INBOX
     */
    public function handle_spam_untag()
    {
        $rcmail = rcmail::get_instance();
        $uids = rcube_utils::get_input_value('_uids', rcube_utils::INPUT_POST);
        if (empty($uids)) {
            $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
            if ($uid) $uids = [$uid];
        }
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST) ?: $this->get_junk_folder();

        if (empty($uids)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No messages specified']);
            exit;
        }

        $storage = $rcmail->get_storage();
        $storage->set_folder($mbox);
        $inbox = 'INBOX';
        $user_id = $this->get_user_identifier();

        $count = 0;
        foreach ((array) $uids as $uid) {
            $ctx = $this->fetch_message_context($uid, $mbox);
            $raw_headers = $this->fetch_raw_headers($uid, $mbox);
            $msg_id = $raw_headers ? $this->extract_message_id($raw_headers) : '';

            // 1. Remove SPAM flags and set NonJunk
            $storage->unset_flag($uid, 'Junk', $mbox);
            $storage->unset_flag($uid, '$Junk', $mbox);
            $storage->unset_flag($uid, '$Label1', $mbox);
            $storage->set_flag($uid, 'NonJunk', $mbox);
            $storage->set_flag($uid, '$NotJunk', $mbox);

            // 2. Train Bayesian engine as HAM with automatic correction
            if (!empty($ctx)) {
                LpaiSpamFilter::learn_ham(
                    $ctx['subject'] ?? '',
                    $ctx['body'] ?? '',
                    $raw_headers,
                    $ctx['from'] ?? '',
                    $user_id,
                    $msg_id,
                    true
                );
            }

            // 3. Move message back to INBOX
            if (strcasecmp($mbox, $inbox) !== 0) {
                $storage->move_message($uid, $inbox, $mbox);
            }
            $count++;
        }

        header('Content-Type: application/json');
        $msg = ($count === 1)
            ? 'Message untagged and moved to Inbox. Learning updated.'
            : "{$count} messages untagged and moved to Inbox. Learning updated.";
        echo json_encode(['status' => 'success', 'message' => $msg, 'count' => $count, 'stats' => LpaiSpamFilter::get_stats($user_id)]);
        exit;
    }

    /**
     * Hook triggered when messages are moved between folders in Roundcube.
     * Auto-trains the Bayesian spam filter if continuous learning is enabled.
     */
    public function handle_messages_move($args)
    {
        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
        $auto_learn = (bool) ($prefs['lifeprisma_ai_spam_auto_learn'] ?? $rcmail->config->get('lifeprisma_ai_spam_auto_learn', true));
        if (!$auto_learn) {
            return $args;
        }

        $junk_mbox = $this->get_junk_folder();
        $target = $args['target'] ?? '';
        $source = $args['folder'] ?? '';
        $uids = $args['uids'] ?? [];

        if (empty($uids)) return $args;

        $user_id = $this->get_user_identifier();

        // Case A: Moving messages INTO Junk -> Auto-Learn as SPAM
        if (strcasecmp($target, $junk_mbox) === 0 && strcasecmp($source, $junk_mbox) !== 0) {
            foreach ((array) $uids as $uid) {
                $ctx = $this->fetch_message_context($uid, $source);
                $raw_headers = $this->fetch_raw_headers($uid, $source);
                $msg_id = $raw_headers ? $this->extract_message_id($raw_headers) : '';
                if (!empty($ctx)) {
                    LpaiSpamFilter::learn_spam($ctx['subject'] ?? '', $ctx['body'] ?? '', $raw_headers, $ctx['from'] ?? '', $user_id, $msg_id);
                }
            }
        }
        // Case B: Moving messages OUT OF Junk -> Auto-Learn as HAM
        elseif (strcasecmp($source, $junk_mbox) === 0 && strcasecmp($target, $junk_mbox) !== 0) {
            foreach ((array) $uids as $uid) {
                $ctx = $this->fetch_message_context($uid, $source);
                $raw_headers = $this->fetch_raw_headers($uid, $source);
                $msg_id = $raw_headers ? $this->extract_message_id($raw_headers) : '';
                if (!empty($ctx)) {
                    LpaiSpamFilter::learn_ham($ctx['subject'] ?? '', $ctx['body'] ?? '', $raw_headers, $ctx['from'] ?? '', $user_id, $msg_id, true);
                }
            }
        }

        return $args;
    }

    /**
     * AJAX Action: Returns live Bayesian learning statistics.
     */
    public function handle_spam_stats()
    {
        $user_id = $this->get_user_identifier();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'stats' => LpaiSpamFilter::get_stats($user_id),
        ]);
        exit;
    }

    /**
     * AJAX Action: Resets the learned Bayesian database for the current user.
     */
    public function handle_spam_reset()
    {
        $user_id = $this->get_user_identifier();
        LpaiSpamFilter::reset_model($user_id);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Learned spam database reset to initial state.',
            'stats' => LpaiSpamFilter::get_stats($user_id),
        ]);
        exit;
    }

    /**
     * AJAX Action: Batch train spam filter from an entire mailbox folder.
     */
    public function handle_spam_batch_train()
    {
        $rcmail = rcmail::get_instance();
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST) ?: $this->get_junk_folder();
        $train_as = rcube_utils::get_input_value('_train_as', rcube_utils::INPUT_POST) ?: 'spam';
        $storage = $rcmail->get_storage();
        $storage->set_folder($mbox);
        $uids = $storage->search($mbox, 'ALL');
        if (is_object($uids) && method_exists($uids, 'get')) {
            $uids = $uids->get();
        }

        $user_id = $this->get_user_identifier();
        $count = 0;
        if (is_array($uids)) {
            // Limit to 50 messages per batch to prevent gateway timeout
            $uids = array_slice($uids, 0, 50);
            foreach ($uids as $uid) {
                $ctx = $this->fetch_message_context($uid, $mbox);
                $raw_headers = $this->fetch_raw_headers($uid, $mbox);
                $msg_id = $raw_headers ? $this->extract_message_id($raw_headers) : '';
                if (!empty($ctx)) {
                    if ($train_as === 'spam') {
                        LpaiSpamFilter::learn_spam($ctx['subject'] ?? '', $ctx['body'] ?? '', $raw_headers, $ctx['from'] ?? '', $user_id, $msg_id);
                    } else {
                        LpaiSpamFilter::learn_ham($ctx['subject'] ?? '', $ctx['body'] ?? '', $raw_headers, $ctx['from'] ?? '', $user_id, $msg_id, true);
                    }
                    $count++;
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => "Successfully trained {$count} messages as {$train_as}.",
            'count' => $count,
            'stats' => LpaiSpamFilter::get_stats($user_id),
        ]);
        exit;
    }

    /**
     * Render the Spam Filter section in Roundcube Preferences.
     */
    private function spam_preferences_list($args)
    {
        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user ? $rcmail->user->get_prefs() : [];
        $user_id = $this->get_user_identifier();
        $stats = LpaiSpamFilter::get_stats($user_id);

        $spam_filter_checkbox = new html_checkbox(['name' => '_lpai_spam_enabled', 'id' => 'lpai_spam_enabled', 'value' => 1]);
        $auto_learn_checkbox = new html_checkbox(['name' => '_lpai_spam_auto_learn', 'id' => 'lpai_spam_auto_learn', 'value' => 1]);
        $ai_deep_scan_checkbox = new html_checkbox(['name' => '_lpai_spam_ai_deep_scan', 'id' => 'lpai_spam_ai_deep_scan', 'value' => 1]);

        $action_select = new html_select(['name' => '_lpai_spam_action', 'id' => 'lpai_spam_action']);
        $action_select->add('Move to Spam folder and add SPAM label (Recommended)', 'move_and_label');
        $action_select->add('Add SPAM label only (Keep in Inbox)', 'label_only');
        $action_select->add('Move to Spam folder only', 'move_only');

        $threshold_select = new html_select(['name' => '_lpai_spam_threshold', 'id' => 'lpai_spam_threshold']);
        $threshold_select->add('Aggressive (Score >= 60) — Strict protection', '60');
        $threshold_select->add('Balanced (Score >= 75) — Standard recommended', '75');
        $threshold_select->add('Conservative (Score >= 85) — Minimal false positives', '85');
        $threshold_select->add('Custom High Sensitivity (Score >= 50)', '50');
        $threshold_select->add('Custom Relaxed (Score >= 90)', '90');

        $whitelist_arr = $prefs['lifeprisma_ai_spam_whitelist'] ?? $rcmail->config->get('lifeprisma_ai_spam_whitelist', []);
        $whitelist_text = is_array($whitelist_arr) ? implode("\n", $whitelist_arr) : (string)$whitelist_arr;

        $blacklist_arr = $prefs['lifeprisma_ai_spam_blacklist'] ?? $rcmail->config->get('lifeprisma_ai_spam_blacklist', []);
        $blacklist_text = is_array($blacklist_arr) ? implode("\n", $blacklist_arr) : (string)$blacklist_arr;

        $keywords_arr = $prefs['lifeprisma_ai_spam_keywords'] ?? $rcmail->config->get('lifeprisma_ai_spam_keywords', []);
        $keywords_text = is_array($keywords_arr) ? implode("\n", $keywords_arr) : (string)$keywords_arr;

        $whitelist_textarea = new html_textarea(['name' => '_lpai_spam_whitelist', 'id' => 'lpai_spam_whitelist', 'rows' => 4, 'cols' => 50, 'class' => 'form-control', 'placeholder' => "@trustedcorp.com\npartner@company.org"]);
        $blacklist_textarea = new html_textarea(['name' => '_lpai_spam_blacklist', 'id' => 'lpai_spam_blacklist', 'rows' => 4, 'cols' => 50, 'class' => 'form-control', 'placeholder' => "@badactor.xyz\nspammer@phishing.net"]);
        $keywords_textarea = new html_textarea(['name' => '_lpai_spam_keywords', 'id' => 'lpai_spam_keywords', 'rows' => 3, 'cols' => 50, 'class' => 'form-control', 'placeholder' => "wire transfer, bitcoin giveaway, inheritance funds, verify credentials"]);

        $token_val = htmlspecialchars($rcmail->get_request_token());
        $reset_url = htmlspecialchars($rcmail->url('plugin.lifeprisma_ai_spam_reset'));
        $stats_url = htmlspecialchars($rcmail->url('plugin.lifeprisma_ai_spam_stats'));
        $batch_url = htmlspecialchars($rcmail->url('plugin.lifeprisma_ai_spam_batch_train'));

        $stats_html = '
        <div class="lpai-spam-dashboard-wrap" data-reset-url="' . $reset_url . '" data-stats-url="' . $stats_url . '" data-batch-url="' . $batch_url . '" data-token="' . $token_val . '">
            <div class="lpai-spam-stats-container">
                <div class="lpai-spam-stat-card lpai-stat-spam">
                    <div class="lpai-stat-number" id="lpai-stat-spam">' . (int)$stats['total_spam'] . '</div>
                    <div class="lpai-stat-label">Spam Learned</div>
                </div>
                <div class="lpai-spam-stat-card lpai-stat-ham">
                    <div class="lpai-stat-number" id="lpai-stat-ham">' . (int)$stats['total_ham'] . '</div>
                    <div class="lpai-stat-label">Ham (Legitimate) Learned</div>
                </div>
                <div class="lpai-spam-stat-card lpai-stat-tokens">
                    <div class="lpai-stat-number" id="lpai-stat-tokens">' . (int)$stats['total_tokens'] . '</div>
                    <div class="lpai-stat-label">Learned Dictionary Tokens</div>
                </div>
                <div class="lpai-spam-stat-card lpai-stat-ratio">
                    <div class="lpai-stat-number" id="lpai-stat-ratio">' . $stats['spam_ratio'] . '%</div>
                    <div class="lpai-stat-label">Spam Ratio</div>
                </div>
            </div>
            <div class="lpai-spam-actions-row" style="margin-top: 14px; display: flex; gap: 10px; align-items: center;">
                <button type="button" class="btn btn-secondary lpai-btn-reset-db" onclick="lpai_reset_spam_db(this)"><i class="icon"></i> Reset Learned Database</button>
                <button type="button" class="btn btn-secondary lpai-btn-train-junk" onclick="lpai_batch_train_folder(\'Junk\', \'spam\', this)"><i class="icon"></i> Train from Junk Folder</button>
                <span id="lpai-spam-feedback" class="lpai-spam-feedback" style="display:none; font-size: 13px; color: #16a34a; font-weight: 500;"></span>
            </div>
        </div>';

        $args['blocks']['lpai_spam_general'] = [
            'name' => 'Spam Filter Automation & Thresholds',
            'options' => [
                'lpai_spam_enabled' => [
                    'title' => 'Enable Automatic Server-Side Spam Filter',
                    'content' => $spam_filter_checkbox->show($prefs['lifeprisma_ai_spam_filter_enabled'] ?? 1),
                ],
                'lpai_spam_action' => [
                    'title' => 'Automated Action when Spam Detected',
                    'content' => $action_select->show($prefs['lifeprisma_ai_spam_action'] ?? 'move_and_label'),
                ],
                'lpai_spam_threshold' => [
                    'title' => 'Sensitivity Threshold',
                    'content' => $threshold_select->show((string)($prefs['lifeprisma_ai_spam_threshold'] ?? '75')),
                ],
                'lpai_spam_ai_deep_scan' => [
                    'title' => 'Gemini 3.8 Flash Deep Phishing & Scam Inspection',
                    'content' => $ai_deep_scan_checkbox->show($prefs['lifeprisma_ai_spam_ai_deep_scan'] ?? 1),
                ],
                'lpai_spam_auto_learn' => [
                    'title' => 'Continuous Self-Learning (Train on Tag / Drag to Junk)',
                    'content' => $auto_learn_checkbox->show($prefs['lifeprisma_ai_spam_auto_learn'] ?? 1),
                ],
            ],
        ];

        $args['blocks']['lpai_spam_rules'] = [
            'name' => 'Sender Rules & Custom Keywords',
            'options' => [
                'lpai_spam_whitelist' => [
                    'title' => 'Whitelist (Allowed Senders & Domains)<br><small style="color: #64748b;">One per line. Never flagged as spam.</small>',
                    'content' => $whitelist_textarea->show($whitelist_text),
                ],
                'lpai_spam_blacklist' => [
                    'title' => 'Blacklist (Blocked Senders & Domains)<br><small style="color: #64748b;">One per line. Always moved to Spam.</small>',
                    'content' => $blacklist_textarea->show($blacklist_text),
                ],
                'lpai_spam_keywords' => [
                    'title' => 'Custom Trigger Keywords<br><small style="color: #64748b;">Keywords or phrases that boost spam probability.</small>',
                    'content' => $keywords_textarea->show($keywords_text),
                ],
            ],
        ];

        $args['blocks']['lpai_spam_dashboard'] = [
            'name' => 'Self-Learning Bayesian Intelligence',
            'options' => [
                'lpai_spam_stats' => [
                    'title' => 'Learning Statistics & Memory',
                    'content' => $stats_html,
                ],
            ],
        ];

        return $args;
    }

    /**
     * Saves user preferences for the Spam Filter section.
     */
    private function spam_preferences_save($args)
    {
        $args['prefs']['lifeprisma_ai_spam_filter_enabled'] = rcube_utils::get_input_string('_lpai_spam_enabled', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['lifeprisma_ai_spam_action'] = rcube_utils::get_input_string('_lpai_spam_action', rcube_utils::INPUT_POST) ?: 'move_and_label';
        $args['prefs']['lifeprisma_ai_spam_threshold'] = (int) (rcube_utils::get_input_string('_lpai_spam_threshold', rcube_utils::INPUT_POST) ?: 75);
        $args['prefs']['lifeprisma_ai_spam_ai_deep_scan'] = rcube_utils::get_input_string('_lpai_spam_ai_deep_scan', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['lifeprisma_ai_spam_auto_learn'] = rcube_utils::get_input_string('_lpai_spam_auto_learn', rcube_utils::INPUT_POST) ? 1 : 0;

        $wl_raw = rcube_utils::get_input_string('_lpai_spam_whitelist', rcube_utils::INPUT_POST);
        $wl_lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $wl_raw))));
        $args['prefs']['lifeprisma_ai_spam_whitelist'] = array_values($wl_lines);

        $bl_raw = rcube_utils::get_input_string('_lpai_spam_blacklist', rcube_utils::INPUT_POST);
        $bl_lines = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $bl_raw))));
        $args['prefs']['lifeprisma_ai_spam_blacklist'] = array_values($bl_lines);

        $kw_raw = rcube_utils::get_input_string('_lpai_spam_keywords', rcube_utils::INPUT_POST);
        $kw_lines = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $kw_raw)));
        $args['prefs']['lifeprisma_ai_spam_keywords'] = array_values($kw_lines);

        return $args;
    }

    /**
     * Resolves the configured LpaiSpamFilter instance for the active user.
     */
    public function get_spam_filter(): LpaiSpamFilter
    {
        return new LpaiSpamFilter($this->get_user_identifier());
    }
}


