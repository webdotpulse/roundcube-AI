<?php

/**
 * Advanced Vacation Out-of-Office & Mail Forwarding Plugin for Roundcube Webmail
 *
 * Features:
 * - Vacation / Out-of-Office auto-reply with start & end scheduling.
 * - Multi-language templates with automatic sender language detection & fallback.
 * - Anti-loop suppression conforming to RFC 3834 and daemon/mailing list filters.
 * - Rate limiting / throttle interval per sender.
 * - Email forwarding and redirection with local copy retention options.
 * - Sieve script generation & export (ManageSieve compatible).
 * - Autonomous cron daemon and real-time IMAP new_messages hook.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

require_once __DIR__ . '/lib/TemplateManager.php';
require_once __DIR__ . '/lib/VacationEngine.php';
require_once __DIR__ . '/lib/ForwardEngine.php';
require_once __DIR__ . '/lib/SieveSync.php';

class vacation_forward extends rcube_plugin
{
    public $task = 'mail|settings';
    private rcmail $rcmail;
    private VacationForwardVacationEngine $vacationEngine;
    private VacationForwardForwardEngine $forwardEngine;

    public function init(): void
    {
        $this->rcmail = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        $this->vacationEngine = new VacationForwardVacationEngine($this->rcmail);
        $this->forwardEngine = new VacationForwardForwardEngine($this->rcmail);

        // Mail task hooks
        $this->add_hook('new_messages', [$this, 'hook_new_messages']);

        // Settings task hooks
        $this->add_hook('settings_actions', [$this, 'hook_settings_actions']);
        $this->add_hook('preferences_sections_list', [$this, 'hook_preferences_sections_list']);
        $this->add_hook('preferences_list', [$this, 'hook_preferences_list']);
        $this->add_hook('preferences_save', [$this, 'hook_preferences_save']);

        // Plugin actions
        $this->register_action('plugin.vacation_forward', [$this, 'action_settings']);
        $this->register_action('plugin.vacation_forward-save', [$this, 'action_save']);
        $this->register_action('plugin.vacation_forward-template-save', [$this, 'action_template_save']);
        $this->register_action('plugin.vacation_forward-template-delete', [$this, 'action_template_delete']);
        $this->register_action('plugin.vacation_forward-simulate', [$this, 'action_simulate']);
        $this->register_action('plugin.vacation_forward-clear-logs', [$this, 'action_clear_logs']);
        $this->register_action('plugin.vacation_forward-sieve-export', [$this, 'action_sieve_export']);
        $this->register_action('plugin.vacation_forward-cron', [$this, 'action_cron']);

        // Assets
        if ($this->rcmail->task === 'settings' || $this->rcmail->task === 'mail') {
            $this->include_stylesheet('vacation_forward.css');
            $this->include_script('vacation_forward.js');
        }
    }

    /**
     * Hook: Intercept new incoming messages to evaluate auto-replies and forwarding.
     */
    public function hook_new_messages(array $args): void
    {
        $mbox = $args['mailbox'] ?? 'INBOX';
        if (strtoupper($mbox) !== 'INBOX') {
            return;
        }

        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        if ($userId <= 0) {
            return;
        }

        $prefs = $this->getUserPrefs();
        [$vacActive] = $this->vacationEngine->evaluateStatus($prefs);
        [$fwdActive] = $this->forwardEngine->evaluateStatus($prefs);

        if (!$vacActive && !$fwdActive) {
            return;
        }

        $storage = $this->rcmail->get_storage();
        if (!$storage) {
            return;
        }

        $storage->set_folder($mbox);
        $uids = $storage->search($mbox, 'UNSEEN RECENT');
        if (empty($uids)) {
            $uids = $storage->search($mbox, 'UNSEEN');
        }

        if (is_object($uids) && method_exists($uids, 'get')) {
            $uids = $uids->get();
        } elseif (is_object($uids) && method_exists($uids, 'count') && count($uids) === 0) {
            $uids = [];
        }

        if (!is_array($uids) || empty($uids)) {
            return;
        }

        // Limit to max 5 unseen messages per batch
        $uids = array_slice(array_reverse($uids), 0, 5);
        $identity = $this->rcmail->user ? $this->rcmail->user->get_identity() : [];

        foreach ($uids as $uid) {
            $msgHeader = $storage->get_message_headers((int)$uid);
            if (!$msgHeader) {
                continue;
            }

            $senderEmail = (string)($msgHeader->from ?? '');
            if (preg_match('/<([^>]+)>/', $senderEmail, $m)) {
                $senderEmail = $m[1];
            }
            $senderEmail = trim($senderEmail);

            $senderFrom = (string)($msgHeader->from ?? $senderEmail);
            $subject = (string)($msgHeader->subject ?? 'No Subject');
            $msgId = (string)($msgHeader->message_id ?? '');

            // Convert message headers to array
            $headers = [];
            if (!empty($msgHeader->others)) {
                foreach ((array)$msgHeader->others as $hk => $hv) {
                    $headers[$hk] = is_array($hv) ? reset($hv) : $hv;
                }
            }

            $body = (string)$storage->get_message_body((int)$uid);

            // Execute auto-reply if active
            if ($vacActive) {
                $this->vacationEngine->processAutoReply(
                    $userId,
                    $senderEmail,
                    $senderFrom,
                    $subject,
                    $body,
                    $msgId,
                    $headers,
                    $prefs,
                    $identity
                );
            }

            // Execute forwarding if active
            if ($fwdActive) {
                $this->forwardEngine->processForward(
                    $userId,
                    $senderEmail,
                    $senderFrom,
                    $subject,
                    $body,
                    $msgId,
                    $headers,
                    $prefs,
                    $identity
                );
            }
        }
    }

    /**
     * Hook: Register Vacation & Forwarding entry in Settings side menu.
     */
    public function hook_settings_actions(array $args): array
    {
        $args['actions'][] = [
            'action' => 'plugin.vacation_forward',
            'class' => 'vacation',
            'label' => 'vacation_forward.vacation_forward_title',
            'title' => 'vacation_forward.vacation_forward_title',
            'domain' => 'vacation_forward',
        ];
        return $args;
    }

    /**
     * Hook: Register section in Preferences.
     */
    public function hook_preferences_sections_list(array $args): array
    {
        $args['list']['vacation'] = [
            'id' => 'vacation',
            'section' => 'vacation',
            'name' => $this->gettext('vacation_forward_title'),
        ];
        return $args;
    }

    /**
     * Hook: Render view inside preferences tab.
     */
    public function hook_preferences_list(array $args): array
    {
        if ($args['section'] !== 'vacation') {
            return $args;
        }

        $this->include_stylesheet('vacation_forward.css');
        $this->include_script('vacation_forward.js');

        $args['blocks']['vacation_forward_main'] = [
            'name' => '',
            'content' => $this->render_settings_view(),
        ];

        return $args;
    }

    /**
     * Hook: Standard Preferences Save.
     */
    public function hook_preferences_save(array $args): array
    {
        if ($args['section'] === 'vacation') {
            $updated = $this->collectPostPrefs();
            foreach ($updated as $k => $v) {
                $args['prefs'][$k] = $v;
            }
        }
        return $args;
    }

    /**
     * Action: Main Settings Page.
     */
    public function action_settings(): void
    {
        $this->rcmail->output->set_pagetitle($this->gettext('vacation_forward_title'));
        $this->include_stylesheet('vacation_forward.css');
        $this->include_script('vacation_forward.js');

        $prefs = $this->getUserPrefs();
        $this->rcmail->output->set_env('vacation_forward_prefs', $prefs);
        $this->rcmail->output->set_env('vacation_forward_templates', $prefs['vacation_templates'] ?? VacationForwardTemplateManager::getDefaultTemplates());

        $this->register_handler('plugin.body', [$this, 'render_settings_view']);
        $this->rcmail->output->send('plugin');
    }

    /**
     * Action: AJAX Save preferences.
     */
    public function action_save(): void
    {
        if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        }

        $newPrefs = $this->collectPostPrefs();
        $currentPrefs = $this->getUserPrefs();
        $merged = array_merge($currentPrefs, $newPrefs);

        if ($this->rcmail->user) {
            $this->rcmail->user->save_prefs($merged);
        }

        $this->rcmail->output->command('display_message', $this->gettext('saved_success'), 'confirmation');
        $this->rcmail->output->send();
    }

    /**
     * Action: AJAX Save/Edit Template.
     */
    public function action_template_save(): void
    {
        if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        }

        $tplId = trim((string)rcube_utils::get_input_value('_tpl_id', rcube_utils::INPUT_POST));
        $tplName = trim((string)rcube_utils::get_input_value('_tpl_name', rcube_utils::INPUT_POST));
        $tplLang = trim((string)rcube_utils::get_input_value('_tpl_lang', rcube_utils::INPUT_POST));
        $tplDefault = !empty(rcube_utils::get_input_value('_tpl_is_default', rcube_utils::INPUT_POST));
        $tplDomain = trim((string)rcube_utils::get_input_value('_tpl_domain_rule', rcube_utils::INPUT_POST));
        $tplSubject = trim((string)rcube_utils::get_input_value('_tpl_subject', rcube_utils::INPUT_POST));
        $tplBody = (string)rcube_utils::get_input_value('_tpl_body', rcube_utils::INPUT_POST);

        if ($tplName === '') {
            $tplName = 'Custom Template';
        }
        if ($tplLang === '') {
            $tplLang = 'en';
        }
        if ($tplId === '') {
            $tplId = 'tpl_' . uniqid();
        }

        $prefs = $this->getUserPrefs();
        $templates = $prefs['vacation_templates'] ?? VacationForwardTemplateManager::getDefaultTemplates();

        if ($tplDefault) {
            foreach ($templates as &$t) {
                $t['is_default'] = false;
            }
            unset($t);
        }

        $found = false;
        foreach ($templates as &$t) {
            if ($t['id'] === $tplId) {
                $t['name'] = $tplName;
                $t['lang'] = $tplLang;
                $t['is_default'] = $tplDefault;
                $t['domain_rule'] = $tplDomain;
                $t['subject'] = $tplSubject;
                $t['body'] = $tplBody;
                $found = true;
                break;
            }
        }
        unset($t);

        if (!$found) {
            $templates[] = [
                'id' => $tplId,
                'name' => $tplName,
                'lang' => $tplLang,
                'is_default' => $tplDefault,
                'domain_rule' => $tplDomain,
                'subject' => $tplSubject,
                'body' => $tplBody,
            ];
        }

        $prefs['vacation_templates'] = $templates;
        if ($this->rcmail->user) {
            $this->rcmail->user->save_prefs($prefs);
        }

        $this->rcmail->output->command('display_message', $this->gettext('saved_success'), 'confirmation');
        $this->rcmail->output->command('plugin.vacation_forward_templates_updated', $templates);
        $this->rcmail->output->send();
    }

    /**
     * Action: AJAX Delete Template.
     */
    public function action_template_delete(): void
    {
        if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        }

        $tplId = trim((string)rcube_utils::get_input_value('_tpl_id', rcube_utils::INPUT_POST));
        $prefs = $this->getUserPrefs();
        $templates = $prefs['vacation_templates'] ?? VacationForwardTemplateManager::getDefaultTemplates();

        $templates = array_values(array_filter($templates, fn($t) => $t['id'] !== $tplId));
        if (empty($templates)) {
            $templates = VacationForwardTemplateManager::getDefaultTemplates();
        }

        $prefs['vacation_templates'] = $templates;
        if ($this->rcmail->user) {
            $this->rcmail->user->save_prefs($prefs);
        }

        $this->rcmail->output->command('display_message', $this->gettext('saved_success'), 'confirmation');
        $this->rcmail->output->command('plugin.vacation_forward_templates_updated', $templates);
        $this->rcmail->output->send();
    }

    /**
     * Action: AJAX Run Simulation.
     */
    public function action_simulate(): void
    {
        $testSender = trim((string)rcube_utils::get_input_value('_test_sender', rcube_utils::INPUT_POST));
        $testSubject = trim((string)rcube_utils::get_input_value('_test_subject', rcube_utils::INPUT_POST));
        $testBody = (string)rcube_utils::get_input_value('_test_body', rcube_utils::INPUT_POST);

        $prefs = $this->getUserPrefs();
        $templates = $prefs['vacation_templates'] ?? VacationForwardTemplateManager::getDefaultTemplates();

        [$template, $matchReason, $detectedLang] = VacationForwardTemplateManager::resolveTemplate(
            $templates,
            $testSender,
            $testSubject,
            $testBody
        );

        $identity = $this->rcmail->user ? $this->rcmail->user->get_identity() : [];
        $userTz = $prefs['timezone'] ?? ($this->rcmail->config->get('timezone') ?: 'UTC');
        $startDateStr = !empty($prefs['vacation_start']) ? date('M j, Y', strtotime($prefs['vacation_start'])) : 'Oct 1, 2026';
        $endDateStr = !empty($prefs['vacation_end']) ? date('M j, Y', strtotime($prefs['vacation_end'])) : 'Oct 15, 2026';
        $returnDateStr = VacationForwardTemplateManager::calculateReturnDate($prefs['vacation_end'] ?? '2026-10-15', $userTz);
        $senderName = VacationForwardTemplateManager::extractSenderName($testSender, $testSender);

        $context = [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'return_date' => $returnDateStr,
            'sender_name' => $senderName,
            'sender_email' => $testSender,
            'original_subject' => $testSubject ?: 'Test Subject',
            'user_name' => $identity['name'] ?? 'User Name',
            'user_email' => $identity['email'] ?? 'user@example.com',
        ];

        $renderedSubject = VacationForwardTemplateManager::interpolate($template['subject'] ?? '', $context);
        $renderedBody = VacationForwardTemplateManager::interpolate($template['body'] ?? '', $context);

        $prefix = $prefs['vacation_subject_prefix'] ?? '';
        if ($prefix !== '' && !str_starts_with($renderedSubject, $prefix)) {
            $renderedSubject = $prefix . ' ' . $renderedSubject;
        }

        $this->rcmail->output->command('plugin.vacation_forward_sim_result', [
            'matched_template' => $template['name'] ?? 'Default',
            'match_reason' => $matchReason,
            'detected_lang' => strtoupper($detectedLang),
            'rendered_subject' => $renderedSubject,
            'rendered_body' => $renderedBody,
        ]);
        $this->rcmail->output->send();
    }

    /**
     * Action: Clear Activity Logs.
     */
    public function action_clear_logs(): void
    {
        if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
            $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
        }

        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        if ($userId > 0) {
            $db = $this->rcmail->get_dbh();
            $db->query("DELETE FROM vacation_forward_logs WHERE user_id = ?", $userId);
        }

        $this->rcmail->output->command('display_message', $this->gettext('cleared_logs_success'), 'confirmation');
        $this->rcmail->output->command('plugin.vacation_forward_logs_cleared');
        $this->rcmail->output->send();
    }

    /**
     * Action: Export Sieve Script.
     */
    public function action_sieve_export(): void
    {
        $prefs = $this->getUserPrefs();
        $identity = $this->rcmail->user ? $this->rcmail->user->get_identity() : [];
        $script = VacationForwardSieveSync::generateScript($prefs, $identity);

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vacation_forward.sieve"');
        echo $script;
        exit(0);
    }

    /**
     * Action: Web Cron Endpoint.
     */
    public function action_cron(): void
    {
        $cronSecret = (string)$this->rcmail->config->get('vacation_forward_cron_secret', '');
        $reqSecret = (string)rcube_utils::get_input_value('secret', rcube_utils::INPUT_GET);

        if ($cronSecret !== '' && $reqSecret !== $cronSecret) {
            header('HTTP/1.0 403 Forbidden');
            echo "Access Denied\n";
            exit(0);
        }

        $processed = $this->runBatchCron();
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Vacation & Forward Worker completed: {$processed} mailboxes processed.\n";
        exit(0);
    }

    /**
     * Autonomous Batch Cron Processor.
     */
    public function runBatchCron(): int
    {
        // Standalone offline processor invoked by CLI / web cron
        $db = $this->rcmail->get_dbh();
        $this->vacationEngine->ensureLogsTable($db);

        $now = date('Y-m-d H:i:s');
        // Auto-disable expired vacations where vacation_auto_disable is true
        $res = $db->query("SELECT user_id, preferences FROM users WHERE preferences LIKE '%vacation_status%'");
        $count = 0;

        while ($row = $db->fetch_assoc($res)) {
            $uid = (int)$row['user_id'];
            $prefs = @unserialize($row['preferences']);
            if (!is_array($prefs)) {
                continue;
            }

            if (($prefs['vacation_status'] ?? '') === 'scheduled' && !empty($prefs['vacation_auto_disable'])) {
                $endStr = $prefs['vacation_end'] ?? '';
                if ($endStr !== '' && strtotime($endStr) < time()) {
                    $prefs['vacation_status'] = 'disabled';
                    $db->query("UPDATE users SET preferences = ? WHERE user_id = ?", serialize($prefs), $uid);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get user preferences with defaults applied.
     */
    public function getUserPrefs(): array
    {
        $prefs = $this->rcmail->user ? (array)$this->rcmail->user->get_prefs() : [];

        $defaults = [
            'vacation_status' => 'disabled',
            'vacation_start' => '',
            'vacation_end' => '',
            'vacation_auto_disable' => true,
            'vacation_rate_interval' => '1_day',
            'vacation_subject_prefix' => 'Out of Office: ',
            'vacation_blacklist' => '',
            'vacation_templates' => VacationForwardTemplateManager::getDefaultTemplates(),
            'forward_status' => 'disabled',
            'forward_destinations' => '',
            'forward_keep_copy' => true,
            'forward_mode' => 'inline',
            'forward_condition' => 'all',
            'forward_keywords' => '',
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($prefs[$k])) {
                $prefs[$k] = $v;
            }
        }

        return $prefs;
    }

    /**
     * Collect and sanitize form inputs from POST request.
     */
    private function collectPostPrefs(): array
    {
        $fields = [
            'vacation_status' => (string)rcube_utils::get_input_value('_vacation_status', rcube_utils::INPUT_POST),
            'vacation_start' => (string)rcube_utils::get_input_value('_vacation_start', rcube_utils::INPUT_POST),
            'vacation_end' => (string)rcube_utils::get_input_value('_vacation_end', rcube_utils::INPUT_POST),
            'vacation_auto_disable' => !empty(rcube_utils::get_input_value('_vacation_auto_disable', rcube_utils::INPUT_POST)),
            'vacation_rate_interval' => (string)rcube_utils::get_input_value('_vacation_rate_interval', rcube_utils::INPUT_POST),
            'vacation_subject_prefix' => (string)rcube_utils::get_input_value('_vacation_subject_prefix', rcube_utils::INPUT_POST),
            'vacation_blacklist' => (string)rcube_utils::get_input_value('_vacation_blacklist', rcube_utils::INPUT_POST),
            'forward_status' => (string)rcube_utils::get_input_value('_forward_status', rcube_utils::INPUT_POST),
            'forward_destinations' => (string)rcube_utils::get_input_value('_forward_destinations', rcube_utils::INPUT_POST),
            'forward_keep_copy' => !empty(rcube_utils::get_input_value('_forward_keep_copy', rcube_utils::INPUT_POST)),
            'forward_mode' => (string)rcube_utils::get_input_value('_forward_mode', rcube_utils::INPUT_POST),
            'forward_condition' => (string)rcube_utils::get_input_value('_forward_condition', rcube_utils::INPUT_POST),
            'forward_keywords' => (string)rcube_utils::get_input_value('_forward_keywords', rcube_utils::INPUT_POST),
        ];

        return array_filter($fields, fn($val) => $val !== null);
    }

    /**
     * Renders modern settings dashboard HTML.
     */
    public function render_settings_view(): string
    {
        $prefs = $this->getUserPrefs();
        [$vacActive, $vacBadge, $vacMsg] = $this->vacationEngine->evaluateStatus($prefs);
        [$fwdActive, $fwdBadge, $fwdMsg] = $this->forwardEngine->evaluateStatus($prefs);

        $templates = $prefs['vacation_templates'] ?? VacationForwardTemplateManager::getDefaultTemplates();
        $logs = $this->getActivityLogs();

        // Status badge colors
        $vacBadgeClass = match ($vacBadge) {
            'active' => 'badge-success',
            'scheduled' => 'badge-warning',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };

        $fwdBadgeClass = match ($fwdBadge) {
            'active' => 'badge-success',
            'scheduled' => 'badge-warning',
            default => 'badge-secondary',
        };

        $html = '<div id="vacation-forward-settings" class="vacation-forward-container boxcontent uibox">';

        // Header Card
        $html .= '<div class="vf-header card mb-4 p-3 shadow-sm">';
        $html .= '  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">';
        $html .= '    <div>';
        $html .= '      <h2 class="vf-title mb-1"><span class="vf-icon">🏖️</span> ' . rcube::Q($this->gettext('vacation_forward_title')) . '</h2>';
        $html .= '      <p class="text-muted mb-0">' . rcube::Q($this->gettext('templates_desc')) . '</p>';
        $html .= '    </div>';
        $html .= '    <div class="vf-status-indicators d-flex align-items-center gap-2">';
        $html .= '      <span class="badge ' . $vacBadgeClass . ' px-3 py-2" id="vf-vac-badge">Auto-Reply: ' . rcube::Q(ucfirst($vacBadge)) . '</span>';
        $html .= '      <span class="badge ' . $fwdBadgeClass . ' px-3 py-2" id="vf-fwd-badge">Forwarding: ' . rcube::Q(ucfirst($fwdBadge)) . '</span>';
        $html .= '      <a href="' . $this->rcmail->url(['_action' => 'plugin.vacation_forward-sieve-export']) . '" class="btn btn-sm btn-outline-secondary ml-2" target="_blank">📜 ' . rcube::Q($this->gettext('btn_export_sieve')) . '</a>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        // Nav Tabs
        $html .= '<ul class="nav nav-tabs vf-tabs mb-4" id="vfTabs" role="tablist">';
        $html .= '  <li class="nav-item"><a class="nav-link active" id="tab-vacation-link" data-toggle="tab" href="#vf-tab-vacation" role="tab">🏖️ ' . rcube::Q($this->gettext('tab_vacation')) . '</a></li>';
        $html .= '  <li class="nav-item"><a class="nav-link" id="tab-templates-link" data-toggle="tab" href="#vf-tab-templates" role="tab">🌐 ' . rcube::Q($this->gettext('tab_templates')) . ' <span class="badge badge-light" id="vf-tpl-count">' . count($templates) . '</span></a></li>';
        $html .= '  <li class="nav-item"><a class="nav-link" id="tab-forward-link" data-toggle="tab" href="#vf-tab-forward" role="tab">📬 ' . rcube::Q($this->gettext('tab_forward')) . '</a></li>';
        $html .= '  <li class="nav-item"><a class="nav-link" id="tab-exclusions-link" data-toggle="tab" href="#vf-tab-exclusions" role="tab">🛡️ ' . rcube::Q($this->gettext('tab_exclusions')) . '</a></li>';
        $html .= '  <li class="nav-item"><a class="nav-link" id="tab-logs-link" data-toggle="tab" href="#vf-tab-logs" role="tab">📜 ' . rcube::Q($this->gettext('tab_logs')) . '</a></li>';
        $html .= '</ul>';

        $html .= '<form id="vf-settings-form" method="post" action="' . $this->rcmail->url(['_action' => 'plugin.vacation_forward-save']) . '">';
        $html .= '<input type="hidden" name="_token" value="' . $this->rcmail->get_request_token() . '">';

        $html .= '<div class="tab-content vf-tab-content">';

        // ---------------- TAB 1: Vacation & Auto-Reply ----------------
        $html .= '<div class="tab-pane fade show active" id="vf-tab-vacation" role="tabpanel">';
        $html .= '  <div class="card mb-4"><div class="card-body">';
        $html .= '    <h4 class="card-title mb-3">🏖️ ' . rcube::Q($this->gettext('tab_vacation')) . '</h4>';

        // Status Select
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label font-weight-bold">' . rcube::Q($this->gettext('vacation_status')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <select name="_vacation_status" id="rcmfd_vacation_status" class="form-control">';
        $html .= '          <option value="disabled"' . ($prefs['vacation_status'] === 'disabled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_disabled')) . '</option>';
        $html .= '          <option value="enabled"' . ($prefs['vacation_status'] === 'enabled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_enabled')) . '</option>';
        $html .= '          <option value="scheduled"' . ($prefs['vacation_status'] === 'scheduled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_scheduled')) . '</option>';
        $html .= '        </select>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Schedule Period (Dates)
        $scheduleStyle = ($prefs['vacation_status'] === 'scheduled') ? '' : 'style="display:none;"';
        $html .= '    <div id="vf-schedule-fields" ' . $scheduleStyle . '>';
        $html .= '      <div class="form-group row">';
        $html .= '        <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('vacation_start')) . '</label>';
        $html .= '        <div class="col-sm-4">';
        $html .= '          <input type="datetime-local" name="_vacation_start" id="rcmfd_vacation_start" class="form-control" value="' . rcube::Q($prefs['vacation_start']) . '">';
        $html .= '        </div>';
        $html .= '        <label class="col-sm-1 col-form-label text-sm-right">' . rcube::Q($this->gettext('vacation_end')) . '</label>';
        $html .= '        <div class="col-sm-4">';
        $html .= '          <input type="datetime-local" name="_vacation_end" id="rcmfd_vacation_end" class="form-control" value="' . rcube::Q($prefs['vacation_end']) . '">';
        $html .= '        </div>';
        $html .= '      </div>';

        // Quick Presets
        $html .= '      <div class="form-group row">';
        $html .= '        <div class="col-sm-9 offset-sm-3 d-flex gap-2 flex-wrap">';
        $html .= '          <button type="button" class="btn btn-sm btn-outline-info" onclick="vf_apply_preset(\'weekend\')">📅 ' . rcube::Q($this->gettext('preset_weekend')) . '</button>';
        $html .= '          <button type="button" class="btn btn-sm btn-outline-info" onclick="vf_apply_preset(\'next_week\')">📅 ' . rcube::Q($this->gettext('preset_next_week')) . '</button>';
        $html .= '          <button type="button" class="btn btn-sm btn-outline-info" onclick="vf_apply_preset(\'two_weeks\')">📅 ' . rcube::Q($this->gettext('preset_two_weeks')) . '</button>';
        $html .= '        </div>';
        $html .= '      </div>';

        // Auto disable checkbox
        $html .= '      <div class="form-group row">';
        $html .= '        <div class="col-sm-9 offset-sm-3">';
        $html .= '          <div class="custom-control custom-checkbox">';
        $html .= '            <input type="checkbox" name="_vacation_auto_disable" id="rcmfd_vacation_auto_disable" class="custom-control-input" value="1"' . (!empty($prefs['vacation_auto_disable']) ? ' checked' : '') . '>';
        $html .= '            <label class="custom-control-label" for="rcmfd_vacation_auto_disable">' . rcube::Q($this->gettext('vacation_auto_disable')) . '</label>';
        $html .= '          </div>';
        $html .= '        </div>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Reply Rate Interval
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label font-weight-bold">' . rcube::Q($this->gettext('rate_limit_interval')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <select name="_vacation_rate_interval" id="rcmfd_vacation_rate_interval" class="form-control">';
        $intervals = [
            '1_hour' => 'interval_1_hour',
            '6_hours' => 'interval_6_hours',
            '12_hours' => 'interval_12_hours',
            '1_day' => 'interval_1_day',
            '2_days' => 'interval_2_days',
            '3_days' => 'interval_3_days',
            '7_days' => 'interval_7_days',
            '14_days' => 'interval_14_days',
            'unlimited' => 'interval_unlimited',
        ];
        foreach ($intervals as $k => $lblKey) {
            $sel = ($prefs['vacation_rate_interval'] === $k) ? ' selected' : '';
            $html .= '<option value="' . $k . '"' . $sel . '>' . rcube::Q($this->gettext($lblKey)) . '</option>';
        }
        $html .= '        </select>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Subject Prefix
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('subject_prefix')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <input type="text" name="_vacation_subject_prefix" id="rcmfd_vacation_subject_prefix" class="form-control" value="' . rcube::Q($prefs['vacation_subject_prefix']) . '" placeholder="Out of Office: ">';
        $html .= '      </div>';
        $html .= '    </div>';

        $html .= '  </div></div>';
        $html .= '</div>';

        // ---------------- TAB 2: Multi-Language Templates ----------------
        $html .= '<div class="tab-pane fade" id="vf-tab-templates" role="tabpanel">';
        $html .= '  <div class="card mb-4"><div class="card-body">';
        $html .= '    <div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '      <div>';
        $html .= '        <h4 class="card-title mb-1">🌐 ' . rcube::Q($this->gettext('templates_title')) . '</h4>';
        $html .= '        <p class="text-muted mb-0 small">' . rcube::Q($this->gettext('templates_desc')) . '</p>';
        $html .= '      </div>';
        $html .= '      <div class="d-flex gap-2">';
        $html .= '        <button type="button" class="btn btn-outline-primary btn-sm" onclick="vf_open_simulator()">🧪 ' . rcube::Q($this->gettext('btn_preview')) . '</button>';
        $html .= '        <button type="button" class="btn btn-primary btn-sm" onclick="vf_open_template_modal()">➕ ' . rcube::Q($this->gettext('btn_add_template')) . '</button>';
        $html .= '      </div>';
        $html .= '    </div>';

        $html .= '    <div class="row" id="vf-templates-list">';
        foreach ($templates as $t) {
            $isDef = !empty($t['is_default']);
            $langCode = strtoupper($t['lang'] ?? 'EN');
            $html .= '      <div class="col-md-6 mb-3" id="vf-card-' . rcube::Q($t['id']) . '">';
            $html .= '        <div class="card h-100 shadow-sm vf-template-card' . ($isDef ? ' border-primary' : '') . '">';
            $html .= '          <div class="card-header d-flex justify-content-between align-items-center">';
            $html .= '            <div class="d-flex align-items-center gap-2">';
            $html .= '              <span class="badge badge-info">' . rcube::Q($langCode) . '</span>';
            $html .= '              <strong class="card-title mb-0">' . rcube::Q($t['name']) . '</strong>';
            if ($isDef) {
                $html .= '            <span class="badge badge-primary ml-1">Default</span>';
            }
            $html .= '            </div>';
            $html .= '            <div class="btn-group btn-group-sm">';
            $html .= '              <button type="button" class="btn btn-outline-secondary" onclick="vf_edit_template(\'' . rcube::Q($t['id']) . '\')">✏️</button>';
            $html .= '              <button type="button" class="btn btn-outline-danger" onclick="vf_delete_template(\'' . rcube::Q($t['id']) . '\')">🗑️</button>';
            $html .= '            </div>';
            $html .= '          </div>';
            $html .= '          <div class="card-body">';
            $html .= '            <p class="small text-muted mb-1"><strong>Subject:</strong> ' . rcube::Q($t['subject']) . '</p>';
            if (!empty($t['domain_rule'])) {
                $html .= '          <p class="small text-muted mb-1"><strong>Domain Filter:</strong> <code>' . rcube::Q($t['domain_rule']) . '</code></p>';
            }
            $html .= '            <div class="vf-template-preview-box p-2 bg-light rounded small text-monospace" style="max-height: 120px; overflow-y: auto;">' . nl2br(rcube::Q($t['body'])) . '</div>';
            $html .= '          </div>';
            $html .= '        </div>';
            $html .= '      </div>';
        }
        $html .= '    </div>';

        $html .= '  </div></div>';
        $html .= '</div>';

        // ---------------- TAB 3: Forwarding Rules ----------------
        $html .= '<div class="tab-pane fade" id="vf-tab-forward" role="tabpanel">';
        $html .= '  <div class="card mb-4"><div class="card-body">';
        $html .= '    <h4 class="card-title mb-3">📬 ' . rcube::Q($this->gettext('tab_forward')) . '</h4>';

        // Status
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label font-weight-bold">' . rcube::Q($this->gettext('forward_status')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <select name="_forward_status" id="rcmfd_forward_status" class="form-control">';
        $html .= '          <option value="disabled"' . ($prefs['forward_status'] === 'disabled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_disabled')) . '</option>';
        $html .= '          <option value="enabled"' . ($prefs['forward_status'] === 'enabled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_enabled')) . '</option>';
        $html .= '          <option value="scheduled"' . ($prefs['forward_status'] === 'scheduled' ? ' selected' : '') . '>' . rcube::Q($this->gettext('status_scheduled')) . '</option>';
        $html .= '        </select>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Destinations
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label font-weight-bold">' . rcube::Q($this->gettext('forward_destinations')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <textarea name="_forward_destinations" id="rcmfd_forward_destinations" rows="3" class="form-control" placeholder="colleague@example.com, manager@example.com">' . rcube::Q($prefs['forward_destinations']) . '</textarea>';
        $html .= '        <small class="form-text text-muted">' . rcube::Q($this->gettext('forward_destinations_help')) . '</small>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Keep local copy
        $html .= '    <div class="form-group row">';
        $html .= '      <div class="col-sm-9 offset-sm-3">';
        $html .= '        <div class="custom-control custom-checkbox">';
        $html .= '          <input type="checkbox" name="_forward_keep_copy" id="rcmfd_forward_keep_copy" class="custom-control-input" value="1"' . (!empty($prefs['forward_keep_copy']) ? ' checked' : '') . '>';
        $html .= '          <label class="custom-control-label" for="rcmfd_forward_keep_copy">' . rcube::Q($this->gettext('forward_keep_copy')) . '</label>';
        $html .= '        </div>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Forward Mode
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('forward_mode')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <select name="_forward_mode" id="rcmfd_forward_mode" class="form-control">';
        $html .= '          <option value="inline"' . ($prefs['forward_mode'] === 'inline' ? ' selected' : '') . '>' . rcube::Q($this->gettext('forward_mode_inline')) . '</option>';
        $html .= '          <option value="redirect"' . ($prefs['forward_mode'] === 'redirect' ? ' selected' : '') . '>' . rcube::Q($this->gettext('forward_mode_redirect')) . '</option>';
        $html .= '        </select>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Condition Filter
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('forward_condition')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <select name="_forward_condition" id="rcmfd_forward_condition" class="form-control">';
        $html .= '          <option value="all"' . ($prefs['forward_condition'] === 'all' ? ' selected' : '') . '>' . rcube::Q($this->gettext('forward_all')) . '</option>';
        $html .= '          <option value="matching"' . ($prefs['forward_condition'] === 'matching' ? ' selected' : '') . '>' . rcube::Q($this->gettext('forward_matching_only')) . '</option>';
        $html .= '        </select>';
        $html .= '      </div>';
        $html .= '    </div>';

        // Keywords
        $kwStyle = ($prefs['forward_condition'] === 'matching') ? '' : 'style="display:none;"';
        $html .= '    <div class="form-group row" id="vf-forward-keywords-row" ' . $kwStyle . '>';
        $html .= '      <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('forward_keywords')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <input type="text" name="_forward_keywords" id="rcmfd_forward_keywords" class="form-control" value="' . rcube::Q($prefs['forward_keywords']) . '" placeholder="URGENT, Invoice, Project">';
        $html .= '      </div>';
        $html .= '    </div>';

        $html .= '  </div></div>';
        $html .= '</div>';

        // ---------------- TAB 4: Anti-Loop & Exclusions ----------------
        $html .= '<div class="tab-pane fade" id="vf-tab-exclusions" role="tabpanel">';
        $html .= '  <div class="card mb-4"><div class="card-body">';
        $html .= '    <h4 class="card-title mb-3">🛡️ ' . rcube::Q($this->gettext('anti_loop_title')) . '</h4>';

        $html .= '    <div class="alert alert-info py-2 small">';
        $html .= '      ✓ ' . rcube::Q($this->gettext('ignore_auto_submitted')) . '<br>';
        $html .= '      ✓ ' . rcube::Q($this->gettext('ignore_daemons'));
        $html .= '    </div>';

        // Blacklist
        $html .= '    <div class="form-group row">';
        $html .= '      <label class="col-sm-3 col-form-label">' . rcube::Q($this->gettext('sender_blacklist')) . '</label>';
        $html .= '      <div class="col-sm-9">';
        $html .= '        <textarea name="_vacation_blacklist" id="rcmfd_vacation_blacklist" rows="4" class="form-control" placeholder="@marketing.com&#10;spammer@bad.org">' . rcube::Q($prefs['vacation_blacklist']) . '</textarea>';
        $html .= '      </div>';
        $html .= '    </div>';

        $html .= '  </div></div>';
        $html .= '</div>';

        // ---------------- TAB 5: Activity Logs ----------------
        $html .= '<div class="tab-pane fade" id="vf-tab-logs" role="tabpanel">';
        $html .= '  <div class="card mb-4"><div class="card-body">';
        $html .= '    <div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '      <h4 class="card-title mb-0">📜 ' . rcube::Q($this->gettext('tab_logs')) . '</h4>';
        $html .= '      <button type="button" class="btn btn-outline-danger btn-sm" onclick="vf_clear_logs()">🗑️ ' . rcube::Q($this->gettext('btn_clear_logs')) . '</button>';
        $html .= '    </div>';

        $html .= '    <div class="table-responsive">';
        $html .= '      <table class="table table-bordered table-striped table-hover small" id="vf-logs-table">';
        $html .= '        <thead class="thead-light"><tr>';
        $html .= '          <th>' . rcube::Q($this->gettext('col_timestamp')) . '</th>';
        $html .= '          <th>' . rcube::Q($this->gettext('col_sender')) . '</th>';
        $html .= '          <th>' . rcube::Q($this->gettext('col_recipient')) . '</th>';
        $html .= '          <th>' . rcube::Q($this->gettext('col_action')) . '</th>';
        $html .= '          <th>' . rcube::Q($this->gettext('col_status')) . '</th>';
        $html .= '        </tr></thead><tbody>';

        if (empty($logs)) {
            $html .= '      <tr id="vf-no-logs-row"><td colspan="5" class="text-center text-muted p-4">' . rcube::Q($this->gettext('no_logs_found')) . '</td></tr>';
        } else {
            foreach ($logs as $log) {
                $statusBadge = match ($log['status']) {
                    'sent' => '<span class="badge badge-success">Sent</span>',
                    'throttled' => '<span class="badge badge-warning">Throttled</span>',
                    'suppressed' => '<span class="badge badge-secondary">Suppressed</span>',
                    default => '<span class="badge badge-danger">Failed</span>',
                };
                $actionBadge = match ($log['action_type']) {
                    'auto_reply' => '<span class="badge badge-info">Auto-Reply</span>',
                    'forward' => '<span class="badge badge-primary">Forward</span>',
                    default => '<span class="badge badge-secondary">' . rcube::Q($log['action_type']) . '</span>',
                };

                $html .= '      <tr>';
                $html .= '        <td>' . rcube::Q(date('M j, Y H:i', strtotime($log['created_at']))) . '</td>';
                $html .= '        <td>' . rcube::Q($log['sender']) . '</td>';
                $html .= '        <td>' . rcube::Q($log['recipient']) . '</td>';
                $html .= '        <td>' . $actionBadge . '</td>';
                $html .= '        <td>' . $statusBadge . '</td>';
                $html .= '      </tr>';
            }
        }

        $html .= '        </tbody></table>';
        $html .= '    </div>';

        $html .= '  </div></div>';
        $html .= '</div>';

        $html .= '</div>'; // End tab-content

        // Footer Actions
        $html .= '<div class="vf-footer d-flex justify-content-end gap-2 p-3 bg-white border-top rounded shadow-sm">';
        $html .= '  <button type="submit" class="btn btn-primary px-4 font-weight-bold">💾 ' . rcube::Q($this->gettext('save')) . '</button>';
        $html .= '</div>';

        $html .= '</form>';

        // Template Edit Modal Container
        $html .= $this->render_template_modal();
        // Simulation Modal Container
        $html .= $this->render_simulation_modal();

        $html .= '</div>'; // End main container

        return $html;
    }

    /**
     * Renders the interactive template editor modal.
     */
    private function render_template_modal(): string
    {
        $html = '<div class="modal fade vf-modal" id="vfTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">';
        $html .= '  <div class="modal-dialog modal-lg" role="document">';
        $html .= '    <div class="modal-content">';
        $html .= '      <div class="modal-header">';
        $html .= '        <h5 class="modal-title" id="vfTemplateModalTitle">Template Editor</h5>';
        $html .= '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $html .= '      </div>';
        $html .= '      <div class="modal-body">';
        $html .= '        <form id="vf-modal-template-form">';
        $html .= '          <input type="hidden" id="vf_modal_tpl_id" value="">';

        $html .= '          <div class="form-row">';
        $html .= '            <div class="form-group col-md-8">';
        $html .= '              <label for="vf_modal_tpl_name" class="font-weight-bold">' . rcube::Q($this->gettext('template_name')) . '</label>';
        $html .= '              <input type="text" id="vf_modal_tpl_name" class="form-control" required placeholder="e.g. Dutch Vacation Response">';
        $html .= '            </div>';
        $html .= '            <div class="form-group col-md-4">';
        $html .= '              <label for="vf_modal_tpl_lang" class="font-weight-bold">' . rcube::Q($this->gettext('template_lang')) . '</label>';
        $html .= '              <select id="vf_modal_tpl_lang" class="form-control">';
        $html .= '                <option value="en">English (EN)</option>';
        $html .= '                <option value="nl">Nederlands (NL)</option>';
        $html .= '                <option value="de">Deutsch (DE)</option>';
        $html .= '                <option value="fr">Français (FR)</option>';
        $html .= '                <option value="es">Español (ES)</option>';
        $html .= '                <option value="it">Italiano (IT)</option>';
        $html .= '              </select>';
        $html .= '            </div>';
        $html .= '          </div>';

        $html .= '          <div class="form-group">';
        $html .= '            <div class="custom-control custom-checkbox">';
        $html .= '              <input type="checkbox" id="vf_modal_tpl_default" class="custom-control-input">';
        $html .= '              <label class="custom-control-label" for="vf_modal_tpl_default">' . rcube::Q($this->gettext('template_is_default')) . '</label>';
        $html .= '            </div>';
        $html .= '          </div>';

        $html .= '          <div class="form-group">';
        $html .= '            <label for="vf_modal_tpl_domain">' . rcube::Q($this->gettext('template_domain_rule')) . '</label>';
        $html .= '            <input type="text" id="vf_modal_tpl_domain" class="form-control" placeholder="@company.com or vip@client.com">';
        $html .= '          </div>';

        $html .= '          <div class="form-group">';
        $html .= '            <label for="vf_modal_tpl_subject" class="font-weight-bold">' . rcube::Q($this->gettext('template_subject')) . '</label>';
        $html .= '            <input type="text" id="vf_modal_tpl_subject" class="form-control" required placeholder="Out of Office: {ORIGINAL_SUBJECT}">';
        $html .= '          </div>';

        $html .= '          <div class="form-group">';
        $html .= '            <label for="vf_modal_tpl_body" class="font-weight-bold">' . rcube::Q($this->gettext('template_body')) . '</label>';
        $html .= '            <textarea id="vf_modal_tpl_body" rows="6" class="form-control font-monospace" required></textarea>';
        $html .= '          </div>';

        // Placeholders Help Box
        $html .= '          <div class="vf-placeholders-box p-3 bg-light rounded">';
        $html .= '            <h6 class="font-weight-bold mb-2">🏷️ ' . rcube::Q($this->gettext('placeholders_legend')) . '</h6>';
        $html .= '            <div class="d-flex flex-wrap gap-2">';
        $tags = [
            '{START_DATE}' => 'placeholder_start_date',
            '{END_DATE}' => 'placeholder_end_date',
            '{RETURN_DATE}' => 'placeholder_return_date',
            '{SENDER_NAME}' => 'placeholder_sender_name',
            '{SENDER_EMAIL}' => 'placeholder_sender_email',
            '{ORIGINAL_SUBJECT}' => 'placeholder_original_subject',
            '{USER_NAME}' => 'placeholder_user_name',
            '{USER_EMAIL}' => 'placeholder_user_email',
        ];
        foreach ($tags as $tag => $labelKey) {
            $html .= '<button type="button" class="btn btn-xs btn-outline-dark" onclick="vf_insert_tag(\'' . $tag . '\')" title="' . rcube::Q($this->gettext($labelKey)) . '"><code>' . $tag . '</code></button>';
        }
        $html .= '            </div>';
        $html .= '          </div>';

        $html .= '        </form>';
        $html .= '      </div>';
        $html .= '      <div class="modal-footer">';
        $html .= '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>';
        $html .= '        <button type="button" class="btn btn-primary" onclick="vf_save_template_modal()">Save Template</button>';
        $html .= '      </div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Renders the interactive simulator modal.
     */
    private function render_simulation_modal(): string
    {
        $html = '<div class="modal fade vf-modal" id="vfSimulatorModal" tabindex="-1" role="dialog" aria-hidden="true">';
        $html .= '  <div class="modal-dialog modal-lg" role="document">';
        $html .= '    <div class="modal-content">';
        $html .= '      <div class="modal-header">';
        $html .= '        <h5 class="modal-title">🧪 ' . rcube::Q($this->gettext('simulate_title')) . '</h5>';
        $html .= '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $html .= '      </div>';
        $html .= '      <div class="modal-body">';
        $html .= '        <form id="vf-simulator-form">';
        $html .= '          <div class="form-row">';
        $html .= '            <div class="form-group col-md-6">';
        $html .= '              <label for="vf_sim_sender" class="font-weight-bold">' . rcube::Q($this->gettext('simulate_sender')) . '</label>';
        $html .= '              <input type="email" id="vf_sim_sender" class="form-control" value="jan.jansen@bedrijf.nl">';
        $html .= '            </div>';
        $html .= '            <div class="form-group col-md-6">';
        $html .= '              <label for="vf_sim_subject" class="font-weight-bold">' . rcube::Q($this->gettext('simulate_subject')) . '</label>';
        $html .= '              <input type="text" id="vf_sim_subject" class="form-control" value="Vraag over de offerte van volgende week">';
        $html .= '            </div>';
        $html .= '          </div>';
        $html .= '          <div class="form-group">';
        $html .= '            <label for="vf_sim_body">' . rcube::Q($this->gettext('simulate_body')) . '</label>';
        $html .= '            <textarea id="vf_sim_body" rows="3" class="form-control">Beste, kunt u mij laten weten of we met het project kunnen starten? Alvast bedankt!</textarea>';
        $html .= '          </div>';
        $html .= '          <button type="button" class="btn btn-info btn-block" onclick="vf_run_simulation()">🚀 ' . rcube::Q($this->gettext('simulate_run')) . '</button>';
        $html .= '        </form>';

        $html .= '        <div id="vf-sim-result" class="mt-4 p-3 bg-light border rounded" style="display:none;">';
        $html .= '          <div class="d-flex justify-content-between align-items-center mb-2">';
        $html .= '            <strong>' . rcube::Q($this->gettext('simulate_matched')) . ': <span id="vf-sim-tpl-name" class="text-primary"></span></strong>';
        $html .= '            <span class="badge badge-info" id="vf-sim-detected-lang"></span>';
        $html .= '          </div>';
        $html .= '          <p class="small text-muted mb-2"><strong>Match Reason:</strong> <span id="vf-sim-reason"></span></p>';
        $html .= '          <hr>';
        $html .= '          <p class="mb-1"><strong>Subject:</strong> <span id="vf-sim-res-subject" class="font-weight-bold"></span></p>';
        $html .= '          <div class="p-3 bg-white border rounded font-monospace small" id="vf-sim-res-body"></div>';
        $html .= '        </div>';

        $html .= '      </div>';
        $html .= '      <div class="modal-footer">';
        $html .= '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
        $html .= '      </div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieve activity logs from database for current user.
     */
    private function getActivityLogs(int $limit = 25): array
    {
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        if ($userId <= 0) {
            return [];
        }

        $db = $this->rcmail->get_dbh();
        $this->vacationEngine->ensureLogsTable($db);

        $res = $db->query("SELECT * FROM vacation_forward_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?", $userId, $limit);
        $logs = [];
        while ($row = $db->fetch_assoc($res)) {
            $logs[] = $row;
        }
        return $logs;
    }
}
