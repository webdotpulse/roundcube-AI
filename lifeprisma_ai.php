<?php

class lifeprisma_ai extends rcube_plugin
{
    public $task = 'mail|settings';

    public function init()
    {
        $this->load_config();
        $this->add_texts('localization/', true);
        $this->include_stylesheet($this->local_skin_path() . '/style.min.css');
        $this->include_script('lifeprisma_ai.min.js');

        $this->register_action('plugin.lifeprisma_ai_request', [$this, 'handle_request']);
        $this->register_action('plugin.lifeprisma_ai_stream', [$this, 'handle_stream']);
        $this->register_action('plugin.lifeprisma_ai_templates', [$this, 'handle_templates']);
        $this->register_action('plugin.lifeprisma_ai_admin', [$this, 'handle_admin']);
        $this->register_action('plugin.lifeprisma_ai_admin_save', [$this, 'handle_admin_save']);
        $this->register_action('plugin.lifeprisma_ai_autodraft', [$this, 'handle_autodraft']);

        $this->add_hook('render_page', [$this, 'render_page']);
        $this->add_hook('preferences_sections_list', [$this, 'preferences_sections']);
        $this->add_hook('preferences_list', [$this, 'preferences_list']);
        $this->add_hook('preferences_save', [$this, 'preferences_save']);
        $this->add_hook('new_messages', [$this, 'handle_new_messages']);
    }

    public function render_page($args)
    {
        if ($args['template'] === 'compose' || $args['template'] === 'message') {
            $rcmail = rcmail::get_instance();

            // Use admin-configured providers if available
            $providers = $this->get_providers_with_admin();
            $js_providers = [];
            foreach ($providers as $id => $p) {
                $ptype = $p['api_type'] ?? 'responses';
                $is_local = $ptype === 'chat_completions' && strpos($p['api_url'] ?? '', 'localhost') !== false;
                if (empty($p['api_key']) && !$is_local) continue;
                $js_providers[$id] = [
                    'label' => $p['label'],
                    'models' => $p['models'] ?? [$p['model']],
                    'default_model' => $p['model'],
                    'supports_reasoning' => $p['supports_reasoning'] ?? true,
                    'pricing' => $p['pricing'] ?? [],
                ];
            }
            $rcmail->output->set_env('lpai_providers', $js_providers);

            // Pass default provider from admin config
            $admin_config = $this->get_admin_config();
            $default_provider = $admin_config['default_provider'] ?? '';
            if ($default_provider && isset($js_providers[$default_provider])) {
                $rcmail->output->set_env('lpai_default_provider', $default_provider);
            }

            // Pass follow-up detection provider/model
            $fu_provider = $admin_config['followup_provider'] ?? '';
            $fu_model = $admin_config['followup_model'] ?? '';
            if ($fu_provider && isset($js_providers[$fu_provider])) {
                $rcmail->output->set_env('lpai_followup_provider', $fu_provider);
                if ($fu_model) $rcmail->output->set_env('lpai_followup_model', $fu_model);
            }

            // Pass user preferences to JS
            $prefs = $rcmail->user->get_prefs();
            $rcmail->output->set_env('lpai_user_prefs', [
                'language' => $prefs['genia_language'] ?? '',
                'tone' => $prefs['genia_tone'] ?? '',
                'auto_draft' => $prefs['genia_auto_draft'] ?? 0,
                'auto_draft_mode' => $prefs['genia_auto_draft_mode'] ?? 'disabled',
                'auto_draft_filter' => $prefs['genia_auto_draft_filter'] ?? 1,
                'followup_check' => $prefs['genia_followup_check'] ?? 1,
            ]);

            // Pass enabled features to JS
            $features = $this->get_enabled_features();
            if ($features) {
                $rcmail->output->set_env('lpai_features', $features);
            }

            // Pass admin status to JS
            $rcmail->output->set_env('lpai_is_admin', $this->is_admin());

            // Pass attachment info and message context for read view
            if ($args['template'] === 'message') {
                $uid = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET);
                $mbox = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GET);
                if ($uid) {
                    $attachments = $this->get_attachment_info((int) $uid, $mbox);
                    if ($attachments) {
                        $rcmail->output->set_env('lpai_attachments', $attachments);
                    }
                    // Pass message context for follow-up detection
                    $ctx = $this->fetch_message_context((int) $uid, $mbox);
                    if ($ctx) {
                        $rcmail->output->set_env('lpai_msg_context', [
                            'from' => $ctx['from'] ?? '',
                            'date' => $ctx['date'] ?? '',
                            'subject' => $ctx['subject'] ?? '',
                            'spam_score' => $ctx['spam_score'],
                        ]);
                    }
                }
            }

            // Pass smart compose preference
            $rcmail->output->set_env('lpai_smart_compose', $prefs['genia_smart_compose'] ?? 1);

            $rcmail->output->add_footer($this->get_ai_panel_html($js_providers));
        }
        return $args;
    }

    /**
     * Get attachment info from IMAP message
     */
    private function get_attachment_info($uid, $mbox = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            if (!empty($mbox)) $storage->set_folder($mbox);

            $msg = new rcube_message($uid);
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
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get configured providers — supports both old flat config and new multi-provider format
     */
    private function get_providers()
    {
        $rcmail = rcmail::get_instance();
        $providers = $rcmail->config->get('lifeprisma_ai_providers');

        if (!empty($providers) && is_array($providers)) {
            return $providers;
        }

        // Fallback: build single provider from old flat config
        $api_key = $rcmail->config->get('lifeprisma_ai_api_key', '');
        $model = $rcmail->config->get('lifeprisma_ai_model', 'gpt-4o');
        $api_url = $rcmail->config->get('lifeprisma_ai_api_url', 'https://api.openai.com/v1/responses');

        return [
            'openai' => [
                'label' => 'OpenAI',
                'api_url' => $api_url,
                'api_key' => $api_key,
                'model' => $model,
                'models' => [$model],
            ],
        ];
    }

    /**
     * Resolve provider config by ID (checks admin config first)
     */
    private function get_provider_config($provider_id = '')
    {
        $providers = $this->get_providers_with_admin();

        if (!empty($provider_id) && isset($providers[$provider_id])) {
            return $providers[$provider_id];
        }

        // Return first provider as default
        return reset($providers);
    }

    private function get_ai_panel_html($js_providers)
    {
        // SVG icons per provider — OpenAI hexagon/sparkle, xAI X logo
        $icons = [
            'openai' => '<svg class="lpai-provider-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22.28 9.37a5.93 5.93 0 00-.51-4.88 6.01 6.01 0 00-6.47-2.91A5.93 5.93 0 0010.84.02a6.01 6.01 0 00-5.73 3.93 5.93 5.93 0 00-3.97 2.88 6.01 6.01 0 00.74 7.05 5.93 5.93 0 00.51 4.88 6.01 6.01 0 006.47 2.91 5.93 5.93 0 004.46 1.56 6.01 6.01 0 005.73-3.93 5.93 5.93 0 003.97-2.88 6.01 6.01 0 00-.74-7.05zM13.3 21.54a4.5 4.5 0 01-2.89-1.05l.14-.08 4.8-2.77a.78.78 0 00.39-.68v-6.77l2.03 1.17a.07.07 0 01.04.06v5.6a4.51 4.51 0 01-4.51 4.52zM3.6 17.6a4.49 4.49 0 01-.54-3.02l.14.09 4.8 2.77a.78.78 0 00.78 0l5.86-3.38v2.34a.07.07 0 01-.03.06l-4.85 2.8A4.51 4.51 0 013.6 17.6zM2.34 7.87A4.49 4.49 0 014.7 5.9v5.7a.78.78 0 00.39.68l5.86 3.38-2.03 1.17a.07.07 0 01-.07 0L4 14.03a4.51 4.51 0 01-1.66-6.16zm17.17 4l-5.86-3.38 2.03-1.17a.07.07 0 01.07 0l4.85 2.8a4.51 4.51 0 01-.7 8.13v-5.7a.78.78 0 00-.39-.68zm2.02-3.03l-.14-.09-4.8-2.77a.78.78 0 00-.78 0L9.95 9.36V7.02a.07.07 0 01.03-.06l4.85-2.8a4.51 4.51 0 016.7 4.68zM8.83 12.68L6.8 11.51a.07.07 0 01-.04-.06V5.85a4.51 4.51 0 017.4-3.47l-.14.08-4.8 2.77a.78.78 0 00-.39.68v6.77zm1.1-2.37L12 9.06l2.07 1.19v2.38L12 13.82l-2.07-1.19v-2.38z"/></svg>',
            'xai' => '<svg class="lpai-provider-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M13.98 10.93L21.39 2h-1.75l-6.43 7.76L7.95 2H2l7.77 11.72L2 23h1.75l6.8-8.2L17.05 23H23l-9.02-12.07zM11.54 13.6l-.79-1.17L4.45 3.41h2.7l5.07 7.53.79 1.17 6.59 9.78h-2.7l-5.36-7.29z"/></svg>',
            'anthropic' => '<svg class="lpai-provider-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M13.83 2 22 22h-4.2l-1.67-4.2H9.55L14 6.5l2.91 7.3H13.1L11.44 18h5.73L18.83 22H22L13.83 2ZM8.6 2H4.43L2 8.25 6.17 22h4.17L2 2h6.6Z"/></svg>',
            'gemini' => '<svg class="lpai-provider-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/></svg>',
        ];

        // Build provider card buttons
        $provider_buttons = '';
        $first = true;
        foreach ($js_providers as $id => $p) {
            $active = $first ? ' active' : '';
            $icon = $icons[$id] ?? '<svg class="lpai-provider-icon" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>';
            $provider_buttons .= '<button type="button" class="lpai-provider-btn' . $active . '" data-group="provider" data-value="' . htmlspecialchars($id) . '" data-provider-id="' . htmlspecialchars($id) . '">' . $icon . '<span class="lpai-provider-name">' . htmlspecialchars($p['label']) . '</span></button>';
            $first = false;
        }

        // Build model buttons for all providers (JS will show/hide)
        $model_buttons = '';
        foreach ($js_providers as $id => $p) {
            foreach ($p['models'] as $m) {
                $is_default = ($m === $p['default_model']) ? ' active' : '';
                $model_buttons .= '<button type="button" class="lpai-opt-btn lpai-model-btn' . $is_default . '" data-group="model" data-value="' . htmlspecialchars($m) . '" data-provider="' . htmlspecialchars($id) . '">' . htmlspecialchars($m) . '</button>';
            }
        }

        return '
<div id="lpai-overlay" style="display:none"></div>
<div id="lpai-panel" style="display:none">
    <div id="lpai-header">
        <span id="lpai-title">GenIA Assistant</span>
        <button id="lpai-close" type="button">&times;</button>
    </div>
    <div id="lpai-body">
        <div id="lpai-provider-row">
            ' . $provider_buttons . '
        </div>
        <div id="lpai-actions">
            <button type="button" class="lpai-action-btn" data-action="compose">Compose</button>
            <button type="button" class="lpai-action-btn" data-action="rewrite">Rewrite</button>
            <button type="button" class="lpai-action-btn" data-action="reply">Reply</button>
            <button type="button" class="lpai-action-btn" data-action="translate">Translate</button>
            <button type="button" class="lpai-action-btn" data-action="summarize">Summarize</button>
            <button type="button" class="lpai-action-btn" data-action="fix">Fix Grammar</button>
            <button type="button" class="lpai-action-btn lpai-action-scam" data-action="scam">Check Scam</button>
            <button type="button" class="lpai-action-btn" data-action="suggest_subject">Subject Line</button>
            <button type="button" class="lpai-action-btn" data-action="thread_summarize">Thread Summary</button>
        </div>
        <div id="lpai-model-row" class="lpai-btn-group">
            <span class="lpai-group-label">Model</span>
            ' . $model_buttons . '
        </div>
        <div id="lpai-lang-row" class="lpai-btn-group" style="display:none">
            <span class="lpai-group-label">Language</span>
            <button type="button" class="lpai-opt-btn active" data-group="language" data-value="Portuguese">PT</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="English">EN</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="Spanish">ES</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="French">FR</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="German">DE</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="Italian">IT</button>
            <button type="button" class="lpai-opt-btn" data-group="language" data-value="Dutch">NL</button>
        </div>
        <div id="lpai-tone-row" class="lpai-btn-group" style="display:none">
            <span class="lpai-group-label">Tone</span>
            <button type="button" class="lpai-opt-btn active" data-group="tone" data-value="professional">Professional</button>
            <button type="button" class="lpai-opt-btn" data-group="tone" data-value="casual">Casual</button>
            <button type="button" class="lpai-opt-btn" data-group="tone" data-value="friendly">Friendly</button>
            <button type="button" class="lpai-opt-btn" data-group="tone" data-value="formal">Formal</button>
            <button type="button" class="lpai-opt-btn" data-group="tone" data-value="urgent">Urgent</button>
        </div>
        <div id="lpai-reasoning-row" class="lpai-btn-group">
            <span class="lpai-group-label">Reasoning</span>
            <button type="button" class="lpai-opt-btn active" data-group="reasoning" data-value="none">None</button>
            <button type="button" class="lpai-opt-btn" data-group="reasoning" data-value="low">Low</button>
            <button type="button" class="lpai-opt-btn" data-group="reasoning" data-value="medium">Med</button>
            <button type="button" class="lpai-opt-btn" data-group="reasoning" data-value="high">High</button>
        </div>
        <div id="lpai-verbosity-row" class="lpai-btn-group">
            <span class="lpai-group-label">Verbosity</span>
            <button type="button" class="lpai-opt-btn" data-group="verbosity" data-value="low">Concise</button>
            <button type="button" class="lpai-opt-btn active" data-group="verbosity" data-value="medium">Balanced</button>
            <button type="button" class="lpai-opt-btn" data-group="verbosity" data-value="high">Detailed</button>
        </div>
        <div id="lpai-templates-row" class="lpai-btn-group" style="display:none">
            <span class="lpai-group-label">Templates</span>
            <select id="lpai-template-select" class="lpai-template-select"><option value="">Select template...</option></select>
            <button type="button" id="lpai-template-save" class="lpai-opt-btn" title="Save current as template">Save</button>
            <button type="button" id="lpai-template-delete" class="lpai-opt-btn" title="Delete selected template" style="display:none">Del</button>
        </div>
        <div id="lpai-context-preview" style="display:none">
            <div id="lpai-context-toggle" class="lpai-context-toggle">Context preview <span id="lpai-context-arrow">&#9654;</span></div>
            <div id="lpai-context-body" class="lpai-context-body" style="display:none"></div>
        </div>
        <textarea id="lpai-input" placeholder="What do you want GenIA to do?" rows="3"></textarea>
        <div id="lpai-buttons">
            <button id="lpai-submit" type="button">Generate</button>
            <button id="lpai-apply" type="button" style="display:none">Apply to Email</button>
            <button id="lpai-copy" type="button" style="display:none">Copy</button>
            <button id="lpai-undo" type="button" style="display:none">Undo</button>
            <button id="lpai-draft" type="button" style="display:none">Save Draft</button>
        </div>
        <div id="lpai-preview" style="display:none">
            <div id="lpai-preview-label">Preview:</div>
            <div id="lpai-preview-text"></div>
        </div>
        <div id="lpai-loading" style="display:none">
            <div class="lpai-spinner"></div>
            <span id="lpai-loading-text">Thinking...</span>
        </div>
        <div id="lpai-footer">
            <a href="https://lifeprisma.ai" target="_blank" id="lpai-powered">
                <span id="lpai-powered-heart">&#9829;</span> Powered by <strong>LifePrisma.ai</strong>
            </a>
        </div>
    </div>
</div>';
    }

    /**
     * User preferences — add GenIA section
     */
    public function preferences_sections($args)
    {
        $args['list']['genia'] = [
            'id' => 'genia',
            'section' => 'GenIA AI Assistant',
        ];
        if ($this->is_admin()) {
            $args['list']['genia_admin'] = [
                'id' => 'genia_admin',
                'section' => 'GenIA Admin Panel',
            ];
        }
        return $args;
    }

    public function preferences_list($args)
    {
        if ($args['section'] === 'genia_admin') {
            return $this->admin_preferences_list($args);
        }
        if ($args['section'] !== 'genia') return $args;

        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user->get_prefs();

        $languages = ['Portuguese' => 'Portuguese', 'English' => 'English', 'Spanish' => 'Spanish', 'French' => 'French', 'German' => 'German', 'Italian' => 'Italian', 'Dutch' => 'Dutch'];
        $tones = ['professional' => 'Professional', 'casual' => 'Casual', 'friendly' => 'Friendly', 'formal' => 'Formal', 'urgent' => 'Urgent'];

        $lang_select = new html_select(['name' => '_genia_language', 'id' => 'genia_language']);
        foreach ($languages as $k => $v) $lang_select->add($v, $k);

        $tone_select = new html_select(['name' => '_genia_tone', 'id' => 'genia_tone']);
        foreach ($tones as $k => $v) $tone_select->add($v, $k);

        $draft_checkbox = new html_checkbox(['name' => '_genia_auto_draft', 'id' => 'genia_auto_draft', 'value' => 1]);
        $smart_compose_checkbox = new html_checkbox(['name' => '_genia_smart_compose', 'id' => 'genia_smart_compose', 'value' => 1]);
        $followup_checkbox = new html_checkbox(['name' => '_genia_followup_check', 'id' => 'genia_followup_check', 'value' => 1]);

        $auto_draft_mode_select = new html_select(['name' => '_genia_auto_draft_mode', 'id' => 'genia_auto_draft_mode']);
        $auto_draft_mode_select->add('Disabled', 'disabled');
        $auto_draft_mode_select->add('When opening/reading an email', 'open');
        $auto_draft_mode_select->add('On new incoming email (background check)', 'receive');

        $auto_draft_filter_checkbox = new html_checkbox(['name' => '_genia_auto_draft_filter', 'id' => 'genia_auto_draft_filter', 'value' => 1]);

        $args['blocks']['genia_general'] = [
            'name' => 'General Settings',
            'options' => [
                'genia_language' => [
                    'title' => 'Default language',
                    'content' => $lang_select->show($prefs['genia_language'] ?? 'English'),
                ],
                'genia_tone' => [
                    'title' => 'Default tone',
                    'content' => $tone_select->show($prefs['genia_tone'] ?? 'professional'),
                ],
                'genia_auto_draft_mode' => [
                    'title' => 'Auto-generate draft replies for incoming emails',
                    'content' => $auto_draft_mode_select->show($prefs['genia_auto_draft_mode'] ?? 'disabled'),
                ],
                'genia_auto_draft_filter' => [
                    'title' => 'Smart filter (only draft if email asks questions or expects a reply)',
                    'content' => $auto_draft_filter_checkbox->show($prefs['genia_auto_draft_filter'] ?? 1),
                ],
                'genia_auto_draft' => [
                    'title' => 'Auto-save AI content as draft (in Compose)',
                    'content' => $draft_checkbox->show($prefs['genia_auto_draft'] ?? 0),
                ],
                'genia_smart_compose' => [
                    'title' => 'Smart Compose (AI autocomplete while typing)',
                    'content' => $smart_compose_checkbox->show($prefs['genia_smart_compose'] ?? 1),
                ],
                'genia_followup_check' => [
                    'title' => 'Auto-detect follow-up reminders when reading emails',
                    'content' => $followup_checkbox->show($prefs['genia_followup_check'] ?? 1),
                ],
            ],
        ];

        return $args;
    }

    public function preferences_save($args)
    {
        if ($args['section'] === 'genia_admin') {
            // Admin saves are handled via AJAX, not form submit
            return $args;
        }
        if ($args['section'] !== 'genia') return $args;

        $args['prefs']['genia_language'] = rcube_utils::get_input_string('_genia_language', rcube_utils::INPUT_POST);
        $args['prefs']['genia_tone'] = rcube_utils::get_input_string('_genia_tone', rcube_utils::INPUT_POST);
        $args['prefs']['genia_auto_draft'] = rcube_utils::get_input_string('_genia_auto_draft', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['genia_auto_draft_mode'] = rcube_utils::get_input_string('_genia_auto_draft_mode', rcube_utils::INPUT_POST) ?: 'disabled';
        $args['prefs']['genia_auto_draft_filter'] = rcube_utils::get_input_string('_genia_auto_draft_filter', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['genia_smart_compose'] = rcube_utils::get_input_string('_genia_smart_compose', rcube_utils::INPUT_POST) ? 1 : 0;
        $args['prefs']['genia_followup_check'] = rcube_utils::get_input_string('_genia_followup_check', rcube_utils::INPUT_POST) ? 1 : 0;

        return $args;
    }

    private function admin_preferences_list($args)
    {
        if (!$this->is_admin()) return $args;

        $args['blocks']['genia_admin_panel'] = [
            'name' => 'GenIA Administration',
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
     * Check if current user is an admin
     */
    private function is_admin()
    {
        $rcmail = rcmail::get_instance();
        $admins = $rcmail->config->get('lifeprisma_ai_admins', []);
        if (empty($admins)) return false;
        if (empty($rcmail->user)) return false;
        $identity = $rcmail->user->get_identity();
        $email = $identity['email'] ?? '';
        $username = $rcmail->user->get_username();
        return in_array($email, $admins) || in_array($username, $admins);
    }

    /**
     * Check CSRF request token
     */
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

    /**
     * Validate external API URL to prevent SSRF and internal network exposure
     */
    private function validate_api_url($url, $allow_local = false)
    {
        if (empty($url) || !is_string($url)) return false;
        $parts = parse_url($url);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) return false;
        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') return false;

        $host = strtolower($parts['host']);
        if ($allow_local && ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1')) {
            return true;
        }

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            $records = @dns_get_record($host, DNS_A + DNS_AAAA);
            if (!empty($records)) {
                foreach ($records as $r) {
                    if (isset($r['ip'])) $ips[] = $r['ip'];
                    if (isset($r['ipv6'])) $ips[] = $r['ipv6'];
                }
            } else {
                $resolved = @gethostbyname($host);
                if ($resolved && $resolved !== $host) {
                    $ips[] = $resolved;
                }
            }
        }

        if (empty($ips)) return false;

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get unsupported params for a specific model.
     * Supports both per-model map and legacy flat array format.
     */
    private function get_unsupported_params($provider, $model)
    {
        $raw = $provider['unsupported_params'] ?? [];
        if (empty($raw)) return [];
        $is_list = function_exists('array_is_list')
            ? array_is_list($raw)
            : (array_keys($raw) === range(0, count($raw) - 1));
        if (is_array($raw) && !$is_list) {
            return $raw[$model] ?? [];
        }
        return $raw;
    }

    /**
     * Admin page — render or API
     */
    public function handle_admin()
    {
        if (!$this->is_admin()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $op = rcube_utils::get_input_string('op', rcube_utils::INPUT_POST) ?: rcube_utils::get_input_string('op', rcube_utils::INPUT_GET);

        if ($op === 'get_config') {
            // Return current admin settings from DB prefs (not file config)
            $db_config = $this->get_admin_config();
            $file_providers = $rcmail->config->get('lifeprisma_ai_providers', []);

            // Merge: DB overrides take priority
            $providers = $db_config['providers'] ?? $file_providers;

            // Mask API keys for display
            $masked = [];
            foreach ($providers as $id => $p) {
                $key = $p['api_key'] ?? '';
                $masked[$id] = $p;
                $masked[$id]['api_key_masked'] = $key ? (substr($key, 0, 8) . '...' . substr($key, -4)) : '';
                $masked[$id]['has_key'] = !empty($key);
            }

            echo json_encode([
                'status' => 'success',
                'providers' => $masked,
                'settings' => [
                    'max_tokens' => $db_config['max_tokens'] ?? $rcmail->config->get('lifeprisma_ai_max_tokens', 2000),
                    'temperature' => $db_config['temperature'] ?? $rcmail->config->get('lifeprisma_ai_temperature', 0.5),
                    'rate_limit' => $db_config['rate_limit'] ?? $rcmail->config->get('lifeprisma_ai_rate_limit', 3),
                    'default_language' => $db_config['default_language'] ?? $rcmail->config->get('lifeprisma_ai_default_language', 'English'),
                    'default_provider' => $db_config['default_provider'] ?? '',
                    'followup_provider' => $db_config['followup_provider'] ?? '',
                    'followup_model' => $db_config['followup_model'] ?? '',
                ],
                'features' => $db_config['features'] ?? [
                    'compose' => true, 'rewrite' => true, 'reply' => true,
                    'translate' => true, 'summarize' => true, 'fix' => true,
                    'scam' => true, 'suggest_subject' => true, 'thread_summarize' => true,
                    'snippet_extract' => true,
                ],
                'usage' => $this->get_usage_stats(),
            ]);
            exit;
        }

        if ($op === 'get_users') {
            echo json_encode([
                'status' => 'success',
                'users' => $this->get_user_usage(),
            ]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
        exit;
    }

    public function handle_admin_save()
    {
        if (!$this->is_admin()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

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

        if (isset($data['providers']) && is_array($data['providers'])) {
            $existing = $config['providers'] ?? $rcmail->config->get('lifeprisma_ai_providers', []);
            $clean_providers = [];
            foreach ($data['providers'] as $id => $p) {
                if (!is_array($p)) continue;
                $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $id);
                if (empty($id)) continue;

                $url = trim($p['api_url'] ?? '');
                $api_type = $p['api_type'] ?? 'responses';
                $is_local = $api_type === 'chat_completions' && (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false);
                if (!empty($url) && !$this->validate_api_url($url, $is_local)) {
                    echo json_encode(['status' => 'error', 'message' => "Invalid or prohibited API URL for provider '{$id}'"]);
                    exit;
                }

                if (empty($p['api_key']) && isset($existing[$id])) {
                    $p['api_key'] = $existing[$id]['api_key'] ?? '';
                }

                $p['label'] = htmlspecialchars(strip_tags((string) ($p['label'] ?? $id)), ENT_QUOTES, 'UTF-8');
                $p['model'] = strip_tags((string) ($p['model'] ?? ''));

                $clean_providers[$id] = $p;
            }
            $config['providers'] = $clean_providers;
        }

        if (isset($data['settings'])) {
            $s = $data['settings'];
            if (isset($s['max_tokens'])) $config['max_tokens'] = (int) $s['max_tokens'];
            if (isset($s['temperature'])) $config['temperature'] = (float) $s['temperature'];
            if (isset($s['rate_limit'])) $config['rate_limit'] = (int) $s['rate_limit'];
            if (isset($s['default_language'])) $config['default_language'] = $s['default_language'];
            if (isset($s['default_provider'])) $config['default_provider'] = $s['default_provider'];
            if (isset($s['followup_provider'])) $config['followup_provider'] = $s['followup_provider'];
            if (isset($s['followup_model'])) $config['followup_model'] = $s['followup_model'];
        }

        if (isset($data['features'])) {
            $config['features'] = $data['features'];
        }

        $this->save_admin_config($config);

        echo json_encode(['status' => 'success', 'message' => 'Settings saved']);
        exit;
    }

    /**
     * Admin config stored in DB via a special system preference key
     */
    private function get_admin_config()
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();
        $result = $db->query("SELECT preferences FROM users WHERE username = ?", '__genia_admin__');
        $row = $db->fetch_assoc($result);
        if ($row && !empty($row['preferences'])) {
            $data = @unserialize($row['preferences'], ['allowed_classes' => false]);
            return is_array($data) ? ($data['genia_admin'] ?? []) : [];
        }
        return [];
    }

    private function save_admin_config($config)
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        $result = $db->query("SELECT user_id FROM users WHERE username = ?", '__genia_admin__');
        $row = $db->fetch_assoc($result);

        $prefs = serialize(['genia_admin' => $config]);

        if ($row) {
            $db->query("UPDATE users SET preferences = ? WHERE username = ?", $prefs, '__genia_admin__');
        } else {
            $now_expr = method_exists($db, 'now') ? $db->now() : 'CURRENT_TIMESTAMP';
            $db->query("INSERT INTO users (username, mail_host, preferences, created) VALUES (?, ?, ?, " . $now_expr . ")",
                '__genia_admin__', 'localhost', $prefs);
        }
    }

    /**
     * Get usage stats for admin dashboard
     */
    private function get_usage_stats()
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        $result = $db->query(
            "SELECT COUNT(*) as total_users FROM users WHERE username != '__genia_admin__'"
        );
        $row = $db->fetch_assoc($result);
        $total_users = $row['total_users'] ?? 0;

        // Count users who have GenIA preferences set (active users)
        $result = $db->query(
            "SELECT COUNT(*) as active_users FROM users WHERE username != '__genia_admin__' AND preferences LIKE '%genia_%'"
        );
        $row = $db->fetch_assoc($result);
        $active_users = $row['active_users'] ?? 0;

        return [
            'total_users' => (int) $total_users,
            'active_users' => (int) $active_users,
        ];
    }

    private function get_user_usage()
    {
        $rcmail = rcmail::get_instance();
        $db = $rcmail->get_dbh();

        $result = $db->query(
            "SELECT username, preferences FROM users WHERE username != '__genia_admin__' AND preferences LIKE '%genia_%' ORDER BY username LIMIT 100"
        );

        $users = [];
        while ($row = $db->fetch_assoc($result)) {
            $prefs = @unserialize($row['preferences'], ['allowed_classes' => false]);
            if (!is_array($prefs)) $prefs = [];
            $users[] = [
                'username' => $row['username'],
                'language' => $prefs['genia_language'] ?? 'default',
                'tone' => $prefs['genia_tone'] ?? 'default',
                'templates' => count($prefs['genia_templates'] ?? []),
            ];
        }
        return $users;
    }

    /**
     * Override get_providers to check DB admin config first
     */
    private function get_providers_with_admin()
    {
        $admin_config = $this->get_admin_config();
        if (!empty($admin_config['providers'])) {
            return $admin_config['providers'];
        }
        return $this->get_providers();
    }

    /**
     * Get enabled features from admin config
     */
    private function get_enabled_features()
    {
        $admin_config = $this->get_admin_config();
        return $admin_config['features'] ?? null;
    }

    /**
     * Handle email templates (save/load/delete)
     */
    public function handle_templates()
    {
        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $op = rcube_utils::get_input_string('op', rcube_utils::INPUT_POST) ?: rcube_utils::get_input_string('op', rcube_utils::INPUT_GET);
        $prefs = $rcmail->user->get_prefs();
        $templates = $prefs['genia_templates'] ?? [];

        if ($op === 'list') {
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        if ($op === 'save') {
            if (!$this->check_csrf()) {
                header('Content-Type: application/json; charset=utf-8', true, 403);
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                exit;
            }

            $name = trim(rcube_utils::get_input_string('name', rcube_utils::INPUT_POST));
            $action = rcube_utils::get_input_string('tpl_action', rcube_utils::INPUT_POST);
            $instruction = trim(rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST));

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Template name is required']);
                exit;
            }

            if (count($templates) >= 50) {
                echo json_encode(['status' => 'error', 'message' => 'Template limit reached (maximum 50)']);
                exit;
            }

            $name = mb_substr($name, 0, 100);
            $instruction = mb_substr($instruction, 0, 2000);

            $templates[] = [
                'id' => uniqid('tpl_'),
                'name' => $name,
                'action' => $action,
                'instruction' => $instruction,
            ];

            $rcmail->user->save_prefs(['genia_templates' => $templates]);
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        if ($op === 'delete') {
            if (!$this->check_csrf()) {
                header('Content-Type: application/json; charset=utf-8', true, 403);
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                exit;
            }

            $id = rcube_utils::get_input_string('id', rcube_utils::INPUT_POST);
            $templates = array_values(array_filter($templates, function ($t) use ($id) {
                return $t['id'] !== $id;
            }));
            $rcmail->user->save_prefs(['genia_templates' => $templates]);
            echo json_encode(['status' => 'success', 'templates' => $templates]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
        exit;
    }

    /**
     * Log AI-related events to dedicated genia.log file
     */
    private function ai_log($message)
    {
        $log_dir = RCUBE_INSTALL_PATH . 'logs/';
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        @file_put_contents($log_dir . 'genia.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rate limiting — per-user cooldown and sliding window
     */
    private function check_rate_limit($action = '')
    {
        $rcmail = rcmail::get_instance();

        // Check if admin has configured rate_limit in DB config
        $admin_config = $this->get_admin_config();
        $admin_settings = $admin_config['settings'] ?? $admin_config;
        $cooldown = isset($admin_settings['rate_limit'])
            ? (int) $admin_settings['rate_limit']
            : (int) $rcmail->config->get('lifeprisma_ai_rate_limit', 3);

        $max_per_min = (int) $rcmail->config->get('lifeprisma_ai_rate_limit_per_min', 60);

        if ($cooldown <= 0 && $max_per_min <= 0) {
            return true;
        }

        $now = microtime(true);

        // Passive background analysis tasks do not block user manual actions and have negligible cooldown
        $is_background = in_array($action, ['detect_followup', 'detect_tone', 'autocomplete']);
        $session_key = $is_background ? 'lpai_last_bg_request' : 'lpai_last_request';

        // Background tasks use a minimal 0.5s debounce, not the user-facing cooldown
        $effective_cooldown = $is_background ? 0.5 : $cooldown;

        if ($effective_cooldown > 0) {
            $last = isset($_SESSION[$session_key]) ? (float) $_SESSION[$session_key] : 0.0;
            if ($last > 0) {
                $elapsed = $now - $last;
                // Only block if strictly non-negative and within cooldown window.
                // If elapsed < 0 (clock drift, NTP step, or multi-worker race), do not block.
                if ($elapsed >= 0 && $elapsed < $effective_cooldown) {
                    return false;
                }
            }
        }

        // Sliding window quota (only track user-initiated requests against the per-minute quota)
        if (!$is_background && $max_per_min > 0) {
            $window = 60.0;
            $history = $_SESSION['lpai_req_history'] ?? [];
            if (!is_array($history)) $history = [];

            // Strictly retain timestamps within [now - window, now]
            $history = array_values(array_filter($history, function ($t) use ($now, $window) {
                $age = $now - (float) $t;
                return $age >= 0 && $age < $window;
            }));

            if (count($history) >= $max_per_min) {
                return false;
            }

            $history[] = $now;
            $_SESSION['lpai_req_history'] = $history;
        }

        $_SESSION[$session_key] = $now;
        return true;
    }

    /**
     * Redis cache helpers
     */
    private function redis_connect()
    {
        static $redis = null;
        if ($redis !== null) return $redis;
        try {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->setOption(Redis::OPT_PREFIX, 'lpai:');
            return $redis;
        } catch (\Exception $e) {
            $redis = false;
            return false;
        }
    }

    private function cache_user_prefix()
    {
        $user = rcmail::get_instance()->user;
        return $user ? md5($user->get_username()) . ':' : '';
    }

    private function cache_get($key)
    {
        $r = $this->redis_connect();
        if (!$r) return null;
        try {
            $val = $r->get($key);
            return $val !== false ? json_decode($val, true) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cache_set($key, $data, $ttl = 86400)
    {
        $r = $this->redis_connect();
        if (!$r) return;
        try {
            $r->setex($key, $ttl, json_encode($data));
        } catch (\Exception $e) {}
    }

    /**
     * Streaming endpoint — sends Server-Sent Events
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
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Please wait a few seconds between requests.']) . "\n\n";
            exit;
        }

        $rcmail = rcmail::get_instance();

        $instruction = rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST);
        $email_body = rcube_utils::get_input_string('email_body', rcube_utils::INPUT_POST);
        $reply_text = rcube_utils::get_input_string('reply_text', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $language = rcube_utils::get_input_string('language', rcube_utils::INPUT_POST);
        $tone = rcube_utils::get_input_string('tone', rcube_utils::INPUT_POST);
        $sender_name = rcube_utils::get_input_string('sender_name', rcube_utils::INPUT_POST);
        $reasoning = rcube_utils::get_input_string('reasoning', rcube_utils::INPUT_POST) ?: 'none';
        $verbosity = rcube_utils::get_input_string('verbosity', rcube_utils::INPUT_POST) ?: 'medium';
        $history = rcube_utils::get_input_string('history', rcube_utils::INPUT_POST);
        $msg_uid = rcube_utils::get_input_string('msg_uid', rcube_utils::INPUT_POST);
        $mbox = rcube_utils::get_input_string('mbox', rcube_utils::INPUT_POST);
        $provider_id = rcube_utils::get_input_string('provider', rcube_utils::INPUT_POST);
        $model_override = rcube_utils::get_input_string('model', rcube_utils::INPUT_POST);
        $view_context = rcube_utils::get_input_string('view_context', rcube_utils::INPUT_POST);

        $provider = $this->get_provider_config($provider_id);
        $api_key = $provider['api_key'] ?? '';
        $model = $model_override ?: ($provider['model'] ?? 'gpt-4o');
        $api_url = $provider['api_url'] ?? 'https://api.openai.com/v1/responses';
        $api_type = $provider['api_type'] ?? 'responses';
        $max_tokens = (int) $rcmail->config->get('lifeprisma_ai_max_tokens', 2000);
        $temperature = (float) $rcmail->config->get('lifeprisma_ai_temperature', 0.5);

        // Redis cache for read-view actions (first request only, no follow-ups)
        $cacheable_actions = ['summarize', 'thread_summarize', 'translate', 'scam'];
        $stream_cache_key = null;
        if ($view_context === 'read' && in_array($action, $cacheable_actions)
            && !empty($msg_uid) && !empty($mbox) && empty($instruction) && empty($history)) {
            $stream_cache_key = $this->cache_user_prefix() . "stream:{$action}:{$mbox}:{$msg_uid}:{$language}:{$model}";
            $cached = $this->cache_get($stream_cache_key);
            if ($cached !== null) {
                $this->ai_log("[STREAM CACHE HIT] action=$action key=$stream_cache_key");
                header('Content-Type: text/event-stream');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('X-Accel-Buffering: no');
                echo "data: " . json_encode(['type' => 'delta', 'text' => $cached['result']]) . "\n\n";
                echo "data: " . json_encode(['type' => 'done', 'model' => $cached['model'], 'cached' => true, 'usage' => $cached['tokens'] ?? []]) . "\n\n";
                flush();
                exit;
            }
        }

        $is_local = $api_type === 'chat_completions' && (strpos($api_url, 'localhost') !== false || strpos($api_url, '127.0.0.1') !== false);
        if (empty($api_key) && !$is_local) {
            header('Content-Type: text/event-stream');
            echo "data: " . json_encode(['type' => 'error', 'message' => 'API key not configured. Your server admin needs to edit plugins/lifeprisma_ai/config.inc.php — see github.com/eduardostern/roundcube-genia#configuration']) . "\n\n";
            exit;
        }

        if (!$this->validate_api_url($api_url, $is_local)) {
            header('Content-Type: text/event-stream', true, 400);
            echo "data: " . json_encode(['type' => 'error', 'message' => 'Invalid or prohibited API endpoint URL.']) . "\n\n";
            exit;
        }

        // Get user's own identity (for "I am:" context)
        $user_identity = '';
        $identity = $rcmail->user->get_identity();
        if (!empty($identity)) {
            $user_identity = trim(($identity['name'] ?? '') . ' <' . ($identity['email'] ?? '') . '>');
        }

        // Enrich context from IMAP only in read view — fill gaps, don't overwrite JS data
        $raw_headers = '';
        $original_sender = '';
        if (!empty($msg_uid) && $view_context === 'read') {
            $ctx = $this->fetch_message_context($msg_uid, $mbox);
            if (!empty($ctx)) {
                if (empty($subject)) $subject = $ctx['subject'] ?? '';
                if (empty($reply_text)) $reply_text = $ctx['body'] ?? '';
                $original_sender = $ctx['from'] ?? '';
            }
        }
        if (!empty($msg_uid) && $action === 'scam') {
            $raw_headers = $this->fetch_raw_headers($msg_uid, $mbox);
        }

        // In read view, sender_name should be the user, not the email sender
        if ($view_context === 'read') {
            $sender_name = $user_identity;
        }

        // Get attachment info from POST
        $attachments_json = rcube_utils::get_input_string('attachments', rcube_utils::INPUT_POST);
        $attachments_text = '';
        if (!empty($attachments_json)) {
            $atts = json_decode($attachments_json, true);
            if (is_array($atts) && count($atts) > 0) {
                $parts = [];
                foreach ($atts as $a) {
                    $size = isset($a['size']) ? round($a['size'] / 1024, 1) . 'KB' : '';
                    $parts[] = ($a['name'] ?? 'unknown') . ' (' . ($a['type'] ?? '') . ($size ? ", {$size}" : '') . ')';
                }
                $attachments_text = "Attachments: " . implode(', ', $parts);
            }
        }

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, $raw_headers, $original_sender, $attachments_text);

        // Build messages/input with conversation history
        $input = [];
        if (!empty($history)) {
            $hist = json_decode($history, true);
            if (is_array($hist)) {
                foreach ($hist as $msg) {
                    $input[] = $msg;
                }
            }
            // Follow-up: use raw instruction instead of rebuilding the full prompt
            if (!empty($instruction) && !empty($hist)) {
                $input[] = ['role' => 'user', 'content' => $instruction];
            } else {
                $input[] = ['role' => 'user', 'content' => $user_prompt];
            }
        } else {
            $input[] = ['role' => 'user', 'content' => $user_prompt];
        }

        $supports_reasoning = $provider['supports_reasoning'] ?? ($api_type === 'responses');

        // Build payload based on API type
        if ($api_type === 'anthropic') {
            $payload = [
                'model' => $model,
                'system' => $system_prompt,
                'messages' => $input,
                'max_tokens' => $max_tokens,
                'stream' => true,
                'temperature' => $temperature,
            ];
        } elseif ($api_type === 'chat_completions') {
            $messages = array_merge(
                [['role' => 'system', 'content' => $system_prompt]],
                $input
            );
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $max_tokens,
                'stream' => true,
                'temperature' => $temperature,
            ];
        } else {
            $payload = [
                'model' => $model,
                'instructions' => $system_prompt,
                'input' => $input,
                'max_output_tokens' => $max_tokens,
                'stream' => true,
            ];
            $unsupported = $this->get_unsupported_params($provider, $model);
            if ($supports_reasoning) {
                $effort = $reasoning;
                if ($reasoning === 'none') {
                    // Model can't receive reasoning=none → use minimal
                    $effort = in_array('reasoning_none', $unsupported) ? 'minimal' : 'none';
                }
                if ($effort !== 'none') {
                    $payload['reasoning'] = ['effort' => $effort];
                    if ($reasoning !== 'none') {
                        $payload['text'] = ['verbosity' => $verbosity];
                    }
                }
            }
            if (!in_array('temperature', $unsupported) && (!isset($payload['reasoning']) || !$supports_reasoning)) {
                $payload['temperature'] = $temperature;
            }
        }

        // SSE headers — no-store prevents Cloudflare caching/buffering
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Close session early — prevents session lock during streaming
        session_write_close();

        // Disable output buffering
        while (ob_get_level()) {
            ob_end_flush();
        }

        $this->ai_log("[STREAM] action=$action model=$model provider=$provider_id api_type=$api_type user=" . ($rcmail->user->get_username() ?? 'unknown'));

        $ch = curl_init($api_url);

        $headers = ['Content-Type: application/json'];
        if ($api_type === 'anthropic') {
            $headers[] = 'x-api-key: ' . $api_key;
            $headers[] = 'anthropic-version: 2023-06-01';
        } elseif (!empty($api_key)) {
            $headers[] = 'Authorization: Bearer ' . $api_key;
        }

        $stream_api_type = $api_type;
        $stream_buffer = '';
        $stream_model = $model;
        $stream_action = $action;
        $stream_first_chunk = true;
        $stream_full_text = '';
        $stream_tokens = ['input' => 0, 'output' => 0];
        $stream_error = false;
        $log_fn = function($msg) { rcube::write_log('genia', $msg); };
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => !$is_local,
            CURLOPT_PROTOCOLS_ALLOWED => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($stream_api_type, &$stream_buffer, &$stream_first_chunk, $stream_model, $stream_action, $log_fn, &$stream_full_text, &$stream_tokens, &$stream_error) {
                // Detect non-SSE error response (e.g. API returns plain JSON error)
                if ($stream_first_chunk) {
                    $stream_first_chunk = false;
                    $trimmed = trim($data);
                    if (!empty($trimmed) && $trimmed[0] === '{') {
                        $err = json_decode($trimmed, true);
                        if (isset($err['error'])) {
                            $stream_error = true;
                            $msg = $err['error']['message'] ?? 'Unknown API error';
                            $log_fn("[STREAM API ERROR] model=$stream_model action=$stream_action error=$msg");
                            echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
                            flush();
                            return 0; // Abort cURL transfer immediately
                        }
                    }
                }
                $stream_buffer .= $data;
                // Bounded buffer check to prevent memory exhaustion from non-SSE payloads
                if (strlen($stream_buffer) > 262144) {
                    $stream_error = true;
                    return 0;
                }

                $lines = explode("\n", $stream_buffer);
                // Keep the last (possibly incomplete) line in the buffer
                $stream_buffer = array_pop($lines);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (strpos($line, 'data: ') !== 0) continue;
                    $json = substr($line, 6);
                    if ($json === '[DONE]') continue;

                    $event = json_decode($json, true);
                    if (!$event) continue;

                    if ($stream_api_type === 'anthropic') {
                        // Anthropic Messages API format
                        $type = $event['type'] ?? '';
                        if ($type === 'content_block_delta') {
                            $delta = $event['delta']['text'] ?? '';
                            if ($delta !== '') {
                                $stream_full_text .= $delta;
                                echo "data: " . json_encode(['type' => 'delta', 'text' => $delta]) . "\n\n";
                                flush();
                            }
                        } elseif ($type === 'message_stop') {
                            echo "data: " . json_encode(['type' => 'done', 'tokens' => ['input' => 0, 'output' => 0]]) . "\n\n";
                            flush();
                        } elseif ($type === 'message_delta') {
                            $usage = $event['usage'] ?? [];
                            if (!empty($usage)) {
                                $stream_tokens = ['input' => $usage['input_tokens'] ?? 0, 'output' => $usage['output_tokens'] ?? 0];
                                echo "data: " . json_encode([
                                    'type' => 'done',
                                    'tokens' => $stream_tokens,
                                ]) . "\n\n";
                                flush();
                            }
                        } elseif ($type === 'error') {
                            $stream_error = true;
                            $msg = $event['error']['message'] ?? 'Unknown error';
                            $log_fn("[STREAM API ERROR] anthropic model=$stream_model error=$msg");
                            echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
                            flush();
                        }
                    } elseif ($stream_api_type === 'chat_completions') {
                        // OpenAI Chat Completions / Ollama format
                        $delta = $event['choices'][0]['delta']['content'] ?? '';
                        if ($delta !== '') {
                            $stream_full_text .= $delta;
                            echo "data: " . json_encode(['type' => 'delta', 'text' => $delta]) . "\n\n";
                            flush();
                        }
                        if (isset($event['error'])) {
                            $stream_error = true;
                            $msg = $event['error']['message'] ?? 'Unknown error';
                            $log_fn("[STREAM API ERROR] chat_completions model=$stream_model error=$msg");
                            echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
                            flush();
                        }
                        $finish = isset($event['choices'][0]['finish_reason']) ? strtolower($event['choices'][0]['finish_reason']) : null;
                        if ($finish === 'stop' || $finish === 'length' || (!empty($event['usage']) && $delta === '')) {
                            $usage = $event['usage'] ?? [];
                            $stream_tokens = ['input' => $usage['prompt_tokens'] ?? 0, 'output' => $usage['completion_tokens'] ?? 0];
                            echo "data: " . json_encode([
                                'type' => 'done',
                                'tokens' => $stream_tokens,
                            ]) . "\n\n";
                            flush();
                        }
                    } else {
                        // OpenAI Responses API format
                        $type = $event['type'] ?? '';
                        if ($type === 'response.output_text.delta') {
                            $delta = $event['delta'] ?? '';
                            $stream_full_text .= $delta;
                            echo "data: " . json_encode(['type' => 'delta', 'text' => $delta]) . "\n\n";
                            flush();
                        } elseif ($type === 'response.completed') {
                            $usage = $event['response']['usage'] ?? [];
                            $stream_tokens = ['input' => $usage['input_tokens'] ?? 0, 'output' => $usage['output_tokens'] ?? 0];
                            echo "data: " . json_encode([
                                'type' => 'done',
                                'tokens' => $stream_tokens,
                            ]) . "\n\n";
                            flush();
                        } elseif ($type === 'error') {
                            $stream_error = true;
                            $msg = $event['message'] ?? 'Unknown error';
                            $log_fn("[STREAM API ERROR] responses model=$stream_model error=$msg");
                            echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
                            flush();
                        }
                    }
                }
                return strlen($data);
            },
        ]);

        curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_error($ch)) {
            $stream_error = true;
            $err = curl_error($ch);
            if (curl_errno($ch) !== CURLE_WRITE_ERROR) {
                $this->ai_log("[STREAM ERROR] curl_error=$err http=$http_code model=$model");
                echo "data: " . json_encode(['type' => 'error', 'message' => $err]) . "\n\n";
                flush();
            }
        } elseif ($http_code !== 200) {
            $stream_error = true;
            $this->ai_log("[STREAM ERROR] http=$http_code model=$model action=$action");
        }

        curl_close($ch);

        // Cache streaming result in Redis (1h for read-view actions)
        if (!$stream_error && $stream_cache_key && !empty($stream_full_text)) {
            $this->cache_set($stream_cache_key, [
                'result' => $stream_full_text,
                'model' => $model,
                'tokens' => $stream_tokens,
            ], 3600);
        }

        if (!$stream_error) {
            echo "data: [DONE]\n\n";
            flush();
        }
        exit;
    }

    /**
     * Non-streaming fallback endpoint
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
            echo json_encode(['status' => 'error', 'message' => 'Please wait a few seconds between requests.']);
            exit;
        }

        $rcmail = rcmail::get_instance();
        header('Content-Type: application/json; charset=utf-8');

        $instruction = rcube_utils::get_input_string('instruction', rcube_utils::INPUT_POST);
        $email_body = rcube_utils::get_input_string('email_body', rcube_utils::INPUT_POST);
        $reply_text = rcube_utils::get_input_string('reply_text', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_string('subject', rcube_utils::INPUT_POST);
        $language = rcube_utils::get_input_string('language', rcube_utils::INPUT_POST);
        $tone = rcube_utils::get_input_string('tone', rcube_utils::INPUT_POST);
        $sender_name = rcube_utils::get_input_string('sender_name', rcube_utils::INPUT_POST);
        $reasoning = rcube_utils::get_input_string('reasoning', rcube_utils::INPUT_POST) ?: 'none';
        $verbosity = rcube_utils::get_input_string('verbosity', rcube_utils::INPUT_POST) ?: 'medium';
        $msg_uid = rcube_utils::get_input_string('msg_uid', rcube_utils::INPUT_POST);
        $mbox = rcube_utils::get_input_string('mbox', rcube_utils::INPUT_POST);
        $provider_id = rcube_utils::get_input_string('provider', rcube_utils::INPUT_POST);
        $model_override = rcube_utils::get_input_string('model', rcube_utils::INPUT_POST);
        $view_context = rcube_utils::get_input_string('view_context', rcube_utils::INPUT_POST);

        $provider = $this->get_provider_config($provider_id);
        $api_key = $provider['api_key'] ?? '';
        $model = $model_override ?: ($provider['model'] ?? 'gpt-4o');
        $api_url = $provider['api_url'] ?? 'https://api.openai.com/v1/responses';
        $api_type = $provider['api_type'] ?? 'responses';
        $max_tokens = (int) $rcmail->config->get('lifeprisma_ai_max_tokens', 2000);
        $temperature = (float) $rcmail->config->get('lifeprisma_ai_temperature', 0.5);

        // Redis cache for detect_followup
        if ($action === 'detect_followup' && !empty($msg_uid) && !empty($mbox)) {
            $cache_key = $this->cache_user_prefix() . "fu:{$mbox}:{$msg_uid}";
            $cached = $this->cache_get($cache_key);
            if ($cached !== null) {
                $this->ai_log("[EMAIL ANALYSIS] CACHE HIT key=$cache_key");
                $cached['cached'] = true;
                echo json_encode($cached);
                exit;
            }
        }

        $is_local = $api_type === 'chat_completions' && (strpos($api_url, 'localhost') !== false || strpos($api_url, '127.0.0.1') !== false);
        if (empty($api_key) && !$is_local) {
            echo json_encode(['status' => 'error', 'message' => 'API key not configured. Your server admin needs to edit plugins/lifeprisma_ai/config.inc.php — see github.com/eduardostern/roundcube-genia#configuration']);
            exit;
        }

        if (!$this->validate_api_url($api_url, $is_local)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or prohibited API endpoint URL.']);
            exit;
        }

        $user_identity = '';
        $identity = $rcmail->user->get_identity();
        if (!empty($identity)) {
            $user_identity = trim(($identity['name'] ?? '') . ' <' . ($identity['email'] ?? '') . '>');
        }

        $raw_headers = '';
        $original_sender = '';
        if (!empty($msg_uid) && $view_context === 'read') {
            $ctx = $this->fetch_message_context($msg_uid, $mbox);
            if (!empty($ctx)) {
                if (empty($subject)) $subject = $ctx['subject'] ?? '';
                if (empty($reply_text)) $reply_text = $ctx['body'] ?? '';
                $original_sender = $ctx['from'] ?? '';
            }
        }
        if (!empty($msg_uid) && $action === 'scam') {
            $raw_headers = $this->fetch_raw_headers($msg_uid, $mbox);
        }

        if ($view_context === 'read') {
            $sender_name = $user_identity;
        }

        $attachments_json = rcube_utils::get_input_string('attachments', rcube_utils::INPUT_POST);
        $attachments_text = '';
        if (!empty($attachments_json)) {
            $atts = json_decode($attachments_json, true);
            if (is_array($atts) && count($atts) > 0) {
                $parts = [];
                foreach ($atts as $a) {
                    $size = isset($a['size']) ? round($a['size'] / 1024, 1) . 'KB' : '';
                    $parts[] = ($a['name'] ?? 'unknown') . ' (' . ($a['type'] ?? '') . ($size ? ", {$size}" : '') . ')';
                }
                $attachments_text = "Attachments: " . implode(', ', $parts);
            }
        }

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, $raw_headers, $original_sender, $attachments_text);

        $supports_reasoning = $provider['supports_reasoning'] ?? ($api_type === 'responses');

        // Build payload based on API type
        if ($api_type === 'anthropic') {
            $payload = [
                'model' => $model,
                'system' => $system_prompt,
                'messages' => [['role' => 'user', 'content' => $user_prompt]],
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
            ];
        } elseif ($api_type === 'chat_completions') {
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
        } else {
            $payload = [
                'model' => $model,
                'instructions' => $system_prompt,
                'input' => $user_prompt,
                'max_output_tokens' => $max_tokens,
            ];
            $unsupported = $this->get_unsupported_params($provider, $model);
            if ($supports_reasoning) {
                $effort = $reasoning;
                if ($reasoning === 'none') {
                    $effort = in_array('reasoning_none', $unsupported) ? 'minimal' : 'none';
                }
                if ($effort !== 'none') {
                    $payload['reasoning'] = ['effort' => $effort];
                    if ($reasoning !== 'none') {
                        $payload['text'] = ['verbosity' => $verbosity];
                    }
                }
            }
            if (!in_array('temperature', $unsupported)) {
                if (!isset($payload['reasoning']) || !$supports_reasoning) {
                    $payload['temperature'] = $temperature;
                }
            }
        }

        $curl_headers = ['Content-Type: application/json'];
        if ($api_type === 'anthropic') {
            $curl_headers[] = 'x-api-key: ' . $api_key;
            $curl_headers[] = 'anthropic-version: 2023-06-01';
        } elseif (!empty($api_key)) {
            $curl_headers[] = 'Authorization: Bearer ' . $api_key;
        }

        $this->ai_log("[REQUEST] action=$action model=$model provider=$provider_id api_type=$api_type user=" . ($rcmail->user->get_username() ?? 'unknown'));

        // Close session before long synchronous cURL request to prevent session locking
        session_write_close();

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $curl_headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => !$is_local,
            CURLOPT_PROTOCOLS_ALLOWED => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->ai_log("[REQUEST ERROR] curl_error=$error model=$model action=$action");
            echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $error]);
            exit;
        }

        $data = json_decode($response, true);

        if ($http_code !== 200) {
            $msg = $data['error']['message'] ?? 'API error (HTTP ' . $http_code . ')';
            $this->ai_log("[REQUEST ERROR] http=$http_code model=$model action=$action error=$msg");
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }

        $content = '';
        if ($api_type === 'anthropic') {
            foreach ($data['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $content .= $block['text'];
                }
            }
        } elseif ($api_type === 'chat_completions') {
            $content = $data['choices'][0]['message']['content'] ?? '';
        } else {
            if (!empty($data['output'])) {
                foreach ($data['output'] as $item) {
                    if (($item['type'] ?? '') === 'message' && !empty($item['content'])) {
                        foreach ($item['content'] as $block) {
                            if (($block['type'] ?? '') === 'output_text') {
                                $content .= $block['text'];
                            }
                        }
                    }
                }
            }
        }

        if (empty($content)) {
            echo json_encode(['status' => 'error', 'message' => 'Empty response from AI']);
            exit;
        }

        if ($action === 'detect_followup') {
            $this->ai_log("[EMAIL ANALYSIS] model=$model result=" . substr(trim($content), 0, 500));
        }

        $usage = $data['usage'] ?? [];
        $input_tokens = $usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0;
        $output_tokens = $usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0;
        $response = [
            'status' => 'success',
            'result' => trim($content),
            'model' => $model,
            'tokens' => [
                'input' => $input_tokens,
                'output' => $output_tokens,
            ],
        ];

        // Cache detect_followup results in Redis (24h)
        if ($action === 'detect_followup' && !empty($msg_uid) && !empty($mbox)) {
            $cache_key = $this->cache_user_prefix() . "fu:{$mbox}:{$msg_uid}";
            $this->cache_set($cache_key, $response, 86400);
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Fetch full message context from IMAP (subject, from, to, body, headers)
     */
    private function fetch_message_context($uid, $mbox = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();

            if (!empty($mbox)) {
                $storage->set_folder($mbox);
            }

            $uid = (int) $uid;
            $msg = new rcube_message($uid);
            if (empty($msg->headers)) {
                return [];
            }

            // Get message body as plain text
            $body = '';
            $text_body = $msg->first_text_part($part);
            if (!empty($text_body)) {
                $body = $text_body;
            } else {
                // Fallback: try HTML part and convert
                $html_body = $msg->first_html_part($part);
                if (!empty($html_body)) {
                    $h2t = new rcube_html2text($html_body);
                    $body = $h2t->get_text();
                }
            }

            // Extract spam score from Rspamd headers
            $spam_score = null;
            $spam_header = $msg->headers->others['x-spam-status'] ?? '';
            if (is_array($spam_header)) $spam_header = end($spam_header);
            if ($spam_header && preg_match('/\bscore=(-?[0-9]+(?:\.[0-9]+)?)/i', $spam_header, $m)) {
                $spam_score = (float) $m[1];
            }
            // Fallback: X-Spamd-Bar (+ = positive, - = negative)
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
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Fetch raw email headers from IMAP for scam analysis
     */
    private function fetch_raw_headers($uid, $mbox = '')
    {
        $rcmail = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        if (!empty($mbox)) {
            $storage->set_folder($mbox);
        }

        $raw = $storage->get_raw_headers((int) $uid);
        if (empty($raw)) {
            return '';
        }

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
    }

    private function build_system_prompt($action = '')
    {
        if ($action === 'suggest_subject') {
            return "You are an email subject line expert. Generate clear, concise, professional subject lines. " .
                "Return ONLY a numbered list of 5 subject lines, nothing else.";
        }

        if ($action === 'thread_summarize') {
            return "You are an expert email thread analyst. Summarize threads clearly and concisely. " .
                "Use markdown formatting (bold for key points, bullet lists for action items). " .
                "Structure: Overview, Key Points, Action Items, Current Status.";
        }

        if ($action === 'extract_actions') {
            return "You are an expert at extracting action items from emails. " .
                "Extract ALL action items, tasks, requests, and things that need to be done. " .
                "For each item, note who is responsible (if mentioned) and any deadline. " .
                "Use markdown formatting with checkboxes (- [ ] item). Be thorough.";
        }

        if ($action === 'extract_dates') {
            return "You are an expert at extracting dates, deadlines, and time-sensitive information from emails. " .
                "Extract ALL dates, deadlines, meetings, appointments, and time references. " .
                "Format as a clear list with the date/time and what it refers to. " .
                "Use markdown formatting. Sort chronologically.";
        }

        if ($action === 'extract_contacts') {
            return "You are an expert at extracting contact information from emails. " .
                "Extract ALL names, email addresses, phone numbers, companies, titles, and any other contact details. " .
                "Format as a structured list. Use markdown formatting with bold for names.";
        }

        if ($action === 'autocomplete') {
            return "You are an email autocomplete engine. The user is composing an email and paused typing. " .
                "Complete the current sentence or thought naturally. Rules:\n" .
                "1. Return ONLY the completion text (the part that comes AFTER what the user typed).\n" .
                "2. Keep it short — one sentence or at most two.\n" .
                "3. Match the tone and language of what's already written.\n" .
                "4. Be natural and contextually appropriate.\n" .
                "5. Do NOT repeat what the user already typed.\n" .
                "6. Do NOT add greetings or closings unless the user clearly started one.\n" .
                "7. If the text seems complete, return an empty string.";
        }

        if ($action === 'suggest_send_time') {
            return "You are an email timing expert. Based on the recipient information and email context, " .
                "suggest the best time to send this email for maximum engagement. " .
                "Return ONLY a JSON object (no code block): " .
                "{\"suggestion\": \"brief one-line suggestion\", \"time\": \"HH:MM\", \"day\": \"today/tomorrow/weekday\", \"reason\": \"brief reason\"}. " .
                "Consider business hours, timezone hints from the email domain, and email urgency. " .
                "Return ONLY the JSON, nothing else.";
        }

        if ($action === 'detect_followup') {
            return "You are an email security and productivity analyst. Analyze this email for three things: " .
                "1) SPAM: Is this unsolicited bulk/marketing email? " .
                "2) SCAM: Is this a phishing, fraud, or social engineering attempt? " .
                "3) FOLLOW-UP: Does the reader need to respond or take action? " .
                "Return ONLY a JSON object (no code block): " .
                "{\"is_spam\": true/false, \"is_scam\": true/false, \"scam_reason\": \"brief reason or null\", " .
                "\"needs_followup\": true/false, \"urgency\": \"high/medium/low/none\", \"reason\": \"brief reason\", " .
                "\"suggested_action\": \"brief suggestion\", \"deadline\": \"date or null\"}. " .
                "For follow-up, look for: direct questions, action requests, promises to respond, meeting proposals, deadlines, pending decisions. " .
                "For scam, look for: suspicious links, urgency pressure, impersonation, too-good-to-be-true offers, credential requests. " .
                "For spam, look for: bulk marketing, newsletters the user didn't opt into, unsolicited promotions. " .
                "Return ONLY the JSON, nothing else.";
        }

        if ($action === 'detect_tone') {
            return "You are an expert at analyzing email tone and sentiment. " .
                "Analyze the tone of the email and respond with a JSON object (no code block): " .
                "{\"tone\": \"detected_tone\", \"confidence\": \"high/medium/low\", \"language\": \"detected_language_code\"}. " .
                "Possible tones: professional, casual, friendly, formal, urgent, angry, apologetic, grateful, neutral. " .
                "For language, use ISO 639-1 codes (en, pt, es, fr, de, it, etc.). " .
                "Return ONLY the JSON, nothing else.";
        }

        if ($action === 'scam') {
            return "You are a cybersecurity expert specialized in email fraud detection. " .
                "Analyze the email for signs of scam, phishing, fraud, social engineering, or suspicious content.\n\n" .
                "Check for:\n" .
                "- Urgency tactics and pressure to act fast\n" .
                "- Requests for money, gift cards, wire transfers, or crypto\n" .
                "- Requests for personal information, passwords, or credentials\n" .
                "- Suspicious links or domain impersonation\n" .
                "- Impersonation of known entities (banks, government, tech companies)\n" .
                "- Grammar/spelling patterns common in scam emails\n" .
                "- Too-good-to-be-true offers\n" .
                "- Mismatched sender identity\n" .
                "- Emotional manipulation (fear, greed, curiosity)\n\n" .
                "When raw email headers are provided, also check:\n" .
                "- SPF, DKIM, and DMARC authentication results\n" .
                "- Mismatched From vs Return-Path or Reply-To addresses\n" .
                "- Suspicious Received headers or originating IPs\n" .
                "- Unusual X-Mailer or sending infrastructure\n\n" .
                "Provide a clear verdict: SAFE, SUSPICIOUS, or DANGEROUS.\n" .
                "Then explain your reasoning with specific evidence from the email.\n" .
                "Format your response with clear structure. Use bold for key findings and bullet points for evidence.";
        }

        return "You are an expert email writing assistant embedded in a webmail client. Your rules:\n\n" .
            "1. EXECUTE instructions, never write emails ABOUT instructions.\n" .
            "2. When asked to translate, translate the text. When asked to rewrite, rewrite it.\n" .
            "3. Return ONLY the email body text. No subject lines, no code blocks, no explanations.\n" .
            "4. Preserve the email structure (greeting, body, closing) unless told otherwise.\n" .
            "5. Match the requested tone and language precisely.\n" .
            "6. Be natural and human-sounding, not robotic.\n" .
            "7. When composing replies, be contextually aware of the conversation.\n" .
            "8. Use markdown formatting (bold, lists, tables). For tables use markdown pipe syntax (| col | col |), never ASCII art (+---+). Always put a line break after the greeting (e.g. 'Olá,\\n\\n' not 'Olá,Texto').\n" .
            "9. NEVER include email signatures, sign-offs, or signature blocks in your responses. Do NOT add lines like '--', 'Best regards', 'Sincerely', name/title/company blocks, or contact details. The email client automatically inserts the user's signature.\n" .
            "10. If the user gives a follow-up instruction like 'make it shorter' or 'now translate it', " .
            "apply it to the previously generated text.";
    }

    private function build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, $raw_headers = '', $original_sender = '', $attachments = '')
    {
        switch ($action) {
            case 'compose':
                return "Compose a new {$tone} email in {$language}.\n" .
                    ($subject ? "Subject context: {$subject}\n" : '') .
                    ($sender_name ? "From: {$sender_name}\n" : '') .
                    ($attachments ? "{$attachments}\n" : '') .
                    "Instructions: {$instruction}\n\n" .
                    "Write the email body only. No subject line." .
                    ($attachments ? " Mention the attachments naturally in the email body." : '');

            case 'rewrite':
                return "Here is the current email draft:\n\n{$email_body}\n\n" .
                    "Rewrite this email with a {$tone} tone in {$language}.\n" .
                    ($instruction ? "Additional instructions: {$instruction}\n" : '') .
                    "Return only the rewritten email body.";

            case 'reply':
                $prompt = "Here is the email to reply to:\n\n";
                if (!empty($original_sender)) {
                    $prompt .= "From: {$original_sender}\n";
                }
                if (!empty($subject)) {
                    $prompt .= "Subject: {$subject}\n";
                }
                $prompt .= "\n{$reply_text}\n\n";
                if (!empty($email_body)) {
                    $prompt .= "Current draft reply:\n\n{$email_body}\n\n";
                }
                if (!empty($sender_name)) {
                    $prompt .= "I am: {$sender_name}\n";
                }
                if (!empty($attachments)) {
                    $prompt .= "{$attachments}\n";
                }
                $prompt .= "Write a {$tone} reply in {$language}.\n" .
                    "Instructions: {$instruction}\n\n" .
                    "Return only the reply body." .
                    ($attachments ? " Acknowledge or reference the attachments if relevant." : '');
                return $prompt;

            case 'translate':
                return "Translate the following email to {$language}. " .
                    "Keep the same tone, structure, and meaning. Return only the translated text.\n\n{$email_body}";

            case 'summarize':
                $text = $reply_text ?: $email_body;
                return "Summarize this email thread concisely in {$language}. " .
                    "Include key points, action items, and decisions.\n\n{$text}";

            case 'fix':
                return "Fix all grammar, spelling, and punctuation errors in this email. " .
                    "Keep the same tone, style, and language. Make minimal changes. " .
                    "Return only the corrected email body.\n\n{$email_body}";

            case 'scam':
                $text = $reply_text ?: $email_body;
                $prompt = "Analyze this email for scam, phishing, or fraud indicators.\n\n";
                if (!empty($raw_headers)) {
                    $prompt .= "=== RAW EMAIL HEADERS ===\n{$raw_headers}\n\n";
                }
                if (!empty($subject)) {
                    $prompt .= "=== SUBJECT ===\n{$subject}\n\n";
                }
                $prompt .= "=== EMAIL BODY ===\n{$text}";
                return $prompt;

            case 'suggest_subject':
                return "Based on this email body, suggest 5 concise, professional subject lines in {$language}. " .
                    "Format as a numbered list. Each should be clear and specific.\n\n" .
                    "Email body:\n{$email_body}";

            case 'thread_summarize':
                $text = $reply_text ?: $email_body;
                return "Summarize this entire email thread in {$language}. Include:\n" .
                    "- Key discussion points\n" .
                    "- Decisions made\n" .
                    "- Action items and who is responsible\n" .
                    "- Current status\n\n" .
                    "Thread:\n{$text}";

            case 'extract_actions':
                $text = $reply_text ?: $email_body;
                $prompt = "Extract all action items and tasks from this email";
                if (!empty($subject)) $prompt .= " (Subject: {$subject})";
                $prompt .= ".\nRespond in {$language}.\n\n{$text}";
                return $prompt;

            case 'extract_dates':
                $text = $reply_text ?: $email_body;
                $prompt = "Extract all dates, deadlines, and time references from this email";
                if (!empty($subject)) $prompt .= " (Subject: {$subject})";
                $prompt .= ".\nRespond in {$language}.\n\n{$text}";
                return $prompt;

            case 'extract_contacts':
                $text = $reply_text ?: $email_body;
                $prompt = "Extract all contact information from this email";
                if (!empty($subject)) $prompt .= " (Subject: {$subject})";
                $prompt .= ".\nRespond in {$language}.\n\n{$text}";
                return $prompt;

            case 'autocomplete':
                $lines = explode("\n", $email_body);
                $last_lines = array_slice($lines, -5);
                $context = implode("\n", $last_lines);
                $prompt = "Complete this email text naturally";
                if (!empty($subject)) $prompt .= " (Subject: {$subject})";
                if (!empty($language)) $prompt .= " in {$language}";
                $prompt .= ":\n\n{$context}";
                return $prompt;

            case 'suggest_send_time':
                $prompt = "Suggest the best time to send this email.\n";
                if (!empty($subject)) $prompt .= "Subject: {$subject}\n";
                $prompt .= "Recipients: " . ($instruction ?: 'unknown') . "\n";
                if (!empty($email_body)) $prompt .= "Email preview: " . substr($email_body, 0, 300) . "\n";
                return $prompt;

            case 'detect_followup':
                $text = $reply_text ?: $email_body;
                $prompt = "Analyze if this email needs a follow-up response:\n\n";
                if (!empty($original_sender)) $prompt .= "From: {$original_sender}\n";
                if (!empty($subject)) $prompt .= "Subject: {$subject}\n\n";
                $prompt .= $text;
                return $prompt;

            case 'detect_tone':
                $text = $reply_text ?: $email_body;
                return "Analyze the tone and language of this email:\n\n{$text}";

            default:
                return "Help with this email in {$language} with a {$tone} tone.\n" .
                    ($email_body ? "Current text:\n{$email_body}\n\n" : '') .
                    "Instructions: {$instruction}";
        }
    }

    /**
     * Hook callback for incoming mail detection
     */
    public function handle_new_messages($args)
    {
        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user->get_prefs();
        $mode = $prefs['genia_auto_draft_mode'] ?? 'disabled';

        if ($mode !== 'receive') {
            return;
        }

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

        if (is_object($uids) && method_exists($uids, 'get')) {
            $uids = $uids->get();
        }

        if (!is_array($uids) || empty($uids)) {
            return;
        }

        // Limit to 2 most recent messages per check
        $uids = array_slice(array_reverse($uids), 0, 2);
        $created_subjects = [];

        foreach ($uids as $uid) {
            $subj = $this->generate_autodraft_for_message((int) $uid, $mbox, $prefs);
            if ($subj) {
                $created_subjects[] = $subj;
            }
        }

        if (!empty($created_subjects)) {
            $count = count($created_subjects);
            $msg = ($count === 1)
                ? "GenIA created an AI draft reply for '{$created_subjects[0]}' in Drafts"
                : "GenIA created {$count} AI draft replies in Drafts";
            $rcmail->output->command('display_message', $msg, 'confirmation');
        }
    }

    /**
     * Action handler for on-demand autodraft generation (e.g. read view)
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

        $prefs = $rcmail->user->get_prefs();
        $mode = $prefs['genia_auto_draft_mode'] ?? 'disabled';

        if ($mode === 'disabled') {
            echo json_encode(['status' => 'skipped', 'message' => 'Auto-draft disabled']);
            exit;
        }

        // Close session before triggering AI generation to prevent webmail lockup
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
     * Cache & offline state deduplication helpers for auto-drafting
     */
    private function is_autodraft_done($uid, $mbox)
    {
        $cache_key = $this->cache_user_prefix() . "autodraft:done:{$mbox}:{$uid}";
        $cached = $this->cache_get($cache_key);
        if ($cached !== null) {
            return true;
        }

        // Fallback when Redis is not available: check user preferences log
        $rcmail = rcmail::get_instance();
        if ($rcmail->user) {
            $prefs = $rcmail->user->get_prefs();
            $log = $prefs['genia_autodraft_log'] ?? [];
            $item_key = "{$mbox}:{$uid}";
            if (isset($log[$item_key])) {
                if (time() - ($log[$item_key]['time'] ?? 0) < 86400 * 7) {
                    return true;
                }
            }
        }
        return false;
    }

    private function mark_autodraft_done($uid, $mbox, $status, $extra = [])
    {
        $cache_key = $this->cache_user_prefix() . "autodraft:done:{$mbox}:{$uid}";
        $data = array_merge(['status' => $status, 'time' => time()], $extra);
        $this->cache_set($cache_key, $data, 86400 * 7);

        // Fallback storage in user preferences
        $rcmail = rcmail::get_instance();
        if ($rcmail->user) {
            $prefs = $rcmail->user->get_prefs();
            $log = $prefs['genia_autodraft_log'] ?? [];
            $item_key = "{$mbox}:{$uid}";
            $log[$item_key] = ['status' => $status, 'time' => time()];

            if (count($log) > 200) {
                $cutoff = time() - (86400 * 7);
                $log = array_filter($log, function ($entry) use ($cutoff) {
                    return ($entry['time'] ?? 0) > $cutoff;
                });
                if (count($log) > 150) {
                    $log = array_slice($log, -150, null, true);
                }
            }
            $rcmail->user->save_prefs(['genia_autodraft_log' => $log]);
        }
    }

    /**
     * Check if a message is suitable for auto-drafting and not already processed
     */
    private function should_auto_draft($uid, $mbox, $prefs)
    {
        $rcmail = rcmail::get_instance();

        // Check if already processed (via Redis or user preferences fallback)
        if ($this->is_autodraft_done($uid, $mbox)) {
            return false;
        }

        $raw_headers = $this->fetch_raw_headers($uid, $mbox);
        if (!empty($raw_headers)) {
            // Check for bulk / newsletter / automated headers
            if (preg_match('/\b(List-Unsubscribe|List-Id|List-Post):/i', $raw_headers)) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_bulk');
                return false;
            }
            if (preg_match('/\bPrecedence:\s*(bulk|list|junk)/i', $raw_headers)) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_bulk');
                return false;
            }
            if (preg_match('/\bAuto-Submitted:\s*(auto-generated|auto-replied)/i', $raw_headers)) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_auto');
                return false;
            }
        }

        $ctx = $this->fetch_message_context($uid, $mbox);
        if (empty($ctx) || empty($ctx['body'])) {
            return false;
        }

        // Skip messages sent by user themselves
        $user_emails = [];
        $identities = $rcmail->user->list_identities();
        foreach ($identities as $ident) {
            if (!empty($ident['email'])) {
                $user_emails[] = strtolower(trim($ident['email']));
            }
        }
        $from_email = strtolower($ctx['from']);
        foreach ($user_emails as $ue) {
            if ($ue && strpos($from_email, $ue) !== false) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_self');
                return false;
            }
        }

        // Smart filter: check if the email expects a reply or asks questions
        $use_filter = !empty($prefs['genia_auto_draft_filter']);
        if ($use_filter) {
            $body = $ctx['body'];
            $subject = $ctx['subject'];
            $text = $subject . ' ' . $body;

            $has_question = strpos($text, '?') !== false;
            $has_request = (bool) preg_match('/\b(please|could you|can you|let me know|what do you think|your thoughts|confirm|feedback|reply|respond|waiting for|deadline|meeting|schedule|availability|asap|update me)\b/i', $text);

            if (!$has_question && !$has_request) {
                $this->mark_autodraft_done($uid, $mbox, 'skipped_no_action');
                return false;
            }
        }

        return true;
    }

    /**
     * Generate an AI draft for a message and save to Drafts
     */
    public function generate_autodraft_for_message($uid, $mbox, $prefs)
    {
        $rcmail = rcmail::get_instance();

        if (!$this->should_auto_draft($uid, $mbox, $prefs)) {
            return false;
        }

        $ctx = $this->fetch_message_context($uid, $mbox);
        if (empty($ctx)) return false;

        $subject = $ctx['subject'] ?? '';
        $from = $ctx['from'] ?? '';
        $body = $ctx['body'] ?? '';
        $date = $ctx['date'] ?? '';

        $identity = $rcmail->user->get_identity();
        $sender_name = trim(($identity['name'] ?? '') . ' <' . ($identity['email'] ?? '') . '>');

        $language = $prefs['genia_language'] ?? 'English';
        $tone = $prefs['genia_tone'] ?? 'professional';

        // Select provider: use default_provider from admin or first available
        $admin_config = $this->get_admin_config();
        $provider_id = $admin_config['default_provider'] ?? '';
        $provider = $this->get_provider_config($provider_id);
        if (empty($provider)) {
            $this->ai_log("[AUTODRAFT] No provider available for autodraft");
            return false;
        }

        $instruction = "Draft a polite and helpful response addressing the points in this email.";
        $reply_result = $this->call_ai_direct('reply', $instruction, '', $body, $subject, $language, $tone, $sender_name, $from, $provider);

        if (empty($reply_result)) {
            $this->ai_log("[AUTODRAFT] Empty AI response generated for uid=$uid");
            return false;
        }

        // Extract Message-ID for threading headers
        $orig_msg_id = '';
        $raw_headers = $this->fetch_raw_headers($uid, $mbox);
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

    /**
     * Direct non-streaming AI call for background auto-drafting
     */
    private function call_ai_direct($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, $original_sender, $provider)
    {
        $rcmail = rcmail::get_instance();
        $api_key = $provider['api_key'] ?? '';
        $model = $provider['model'] ?? 'gemini-3.6-flash';
        $api_url = $provider['api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';
        $api_type = $provider['api_type'] ?? 'chat_completions';
        $max_tokens = (int) $rcmail->config->get('lifeprisma_ai_max_tokens', 2000);
        $temperature = (float) $rcmail->config->get('lifeprisma_ai_temperature', 0.5);

        $is_local = $api_type === 'chat_completions' && (strpos($api_url, 'localhost') !== false || strpos($api_url, '127.0.0.1') !== false);
        if (empty($api_key) && !$is_local) {
            return false;
        }

        if (!$this->validate_api_url($api_url, $is_local)) {
            $this->ai_log("[AUTODRAFT] Prohibited or invalid API URL: $api_url");
            return false;
        }

        $system_prompt = $this->build_system_prompt($action);
        $user_prompt = $this->build_user_prompt($action, $instruction, $email_body, $reply_text, $subject, $language, $tone, $sender_name, '', $original_sender, '');

        if ($api_type === 'anthropic') {
            $payload = [
                'model' => $model,
                'system' => $system_prompt,
                'messages' => [['role' => 'user', 'content' => $user_prompt]],
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
            ];
        } elseif ($api_type === 'chat_completions') {
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
        } else {
            $payload = [
                'model' => $model,
                'instructions' => $system_prompt,
                'input' => $user_prompt,
                'max_output_tokens' => $max_tokens,
                'temperature' => $temperature,
            ];
        }

        $curl_headers = ['Content-Type: application/json'];
        if ($api_type === 'anthropic') {
            $curl_headers[] = 'x-api-key: ' . $api_key;
            $curl_headers[] = 'anthropic-version: 2023-06-01';
        } elseif (!empty($api_key)) {
            $curl_headers[] = 'Authorization: Bearer ' . $api_key;
        }

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $curl_headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => !$is_local,
            CURLOPT_PROTOCOLS_ALLOWED => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || empty($response)) {
            return false;
        }

        $data = json_decode($response, true);
        if (!$data) return false;

        $content = '';
        if ($api_type === 'anthropic') {
            foreach ($data['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text') $content .= $block['text'];
            }
        } elseif ($api_type === 'chat_completions') {
            $content = $data['choices'][0]['message']['content'] ?? '';
        } else {
            if (!empty($data['output'])) {
                foreach ($data['output'] as $item) {
                    if (($item['type'] ?? '') === 'message' && !empty($item['content'])) {
                        foreach ($item['content'] as $block) {
                            if (($block['type'] ?? '') === 'output_text') $content .= $block['text'];
                        }
                    }
                }
            }
        }

        return trim($content);
    }

    /**
     * Save message to Drafts IMAP mailbox with RFC 2047 encoding and CRLF protection
     */
    private function create_imap_draft($to, $subject, $reply_body, $orig_msg_id = '', $orig_date = '', $orig_from = '', $orig_body = '')
    {
        try {
            $rcmail = rcmail::get_instance();
            $storage = $rcmail->get_storage();
            $drafts_mbox = $rcmail->config->get('drafts_mbox', 'Drafts');

            if (!$storage->folder_exists($drafts_mbox)) {
                $storage->folder_create($drafts_mbox, true);
            }

            $identity = $rcmail->user->get_identity();
            $from_name = $identity['name'] ?? '';
            $from_email = $identity['email'] ?? '';
            $from_str = $from_name ? "\"$from_name\" <$from_email>" : $from_email;

            $re_subject = (stripos($subject, 'Re:') === 0) ? $subject : 'Re: ' . $subject;

            // Quote original message if present
            $quoted = '';
            if (!empty($orig_body)) {
                $date_str = $orig_date ? "On $orig_date, " : "On earlier message, ";
                $from_info = $orig_from ? "$orig_from wrote:" : "sender wrote:";
                $quote_header = $date_str . $from_info;
                $quoted = "\n\n" . $quote_header . "\n" . preg_replace('/^/m', '> ', trim($orig_body));
            }

            $full_body = trim($reply_body) . $quoted;
            $domain = $rcmail->config->mail_domain() ?: 'localhost';
            $msg_id = '<' . md5(uniqid(microtime(), true)) . '@' . $domain . '>';

            // Sanitize headers against CRLF injection
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
                'X-Mailer: GenIA AI Assistant',
                'X-GenIA-AutoDraft: 1',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];

            if (!empty($clean_orig_id)) {
                $headers[] = 'In-Reply-To: ' . $clean_orig_id;
                $headers[] = 'References: ' . $clean_orig_id;
            }

            $raw_message = implode("\r\n", $headers) . "\r\n\r\n" . $full_body;

            $saved = $storage->save_message($drafts_mbox, $raw_message, '', false, ['SEEN', 'DRAFT']);
            if ($saved) {
                $this->ai_log("[AUTODRAFT] Successfully saved draft to $drafts_mbox for '$subject'");
                return true;
            } else {
                $this->ai_log("[AUTODRAFT ERROR] Failed to save draft to $drafts_mbox");
                return false;
            }
        } catch (\Exception $e) {
            $this->ai_log("[AUTODRAFT EXCEPTION] " . $e->getMessage());
            return false;
        }
    }
}
