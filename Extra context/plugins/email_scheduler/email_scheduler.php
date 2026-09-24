<?php

/**
 * Email Scheduler & Undo Send Plugin for Roundcube Webmail
 *
 * Provides:
 * - Undo Send: configurable cancellation delay window (5s, 10s, 20s, 30s)
 *   with an animated countdown toast and instant one-click abort.
 * - Scheduled Send ("Send Later"): schedule emails for delivery at future dates/times
 *   with presets (Tomorrow morning, Tomorrow afternoon, Monday, or custom date/time).
 * - Outbox / Queue Management: inspect, reschedule, cancel, or immediately send
 *   queued messages from Settings > Email Scheduler & Undo Send.
 * - Autonomous delivery engine via background cron and opportunistic web-dispatch.
 *
 * @license MIT
 * @author Webdotpulse & Contributors
 */

declare(strict_types=1);

class email_scheduler extends rcube_plugin
{
    public $task = 'mail|settings';
    private rcmail $rcmail;
    private string $table = 'email_scheduler_queue';

    public function init(): void
    {
        $this->rcmail = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        $this->table = (string)$this->rcmail->config->get('email_scheduler_table', 'email_scheduler_queue');

        // Opportunistic queue processor on background web requests
        if ($this->rcmail->config->get('email_scheduler_web_dispatch', true) && mt_rand(1, 10) === 1) {
            $this->processDueMessages();
        }

        // Compose hooks
        $this->add_hook('message_compose', [$this, 'hook_message_compose']);
        $this->add_hook('message_before_send', [$this, 'hook_message_before_send']);

        // Settings / Preferences hooks
        $this->add_hook('preferences_sections_list', [$this, 'hook_preferences_sections_list']);
        $this->add_hook('preferences_list', [$this, 'hook_preferences_list']);
        $this->add_hook('preferences_save', [$this, 'hook_preferences_save']);

        // Plugin actions
        $this->register_action('plugin.email_scheduler-undo', [$this, 'action_undo']);
        $this->register_action('plugin.email_scheduler-cancel', [$this, 'action_cancel']);
        $this->register_action('plugin.email_scheduler-send-now', [$this, 'action_send_now']);
        $this->register_action('plugin.email_scheduler-cron', [$this, 'action_cron']);
        $this->register_action('plugin.email_scheduler-list', [$this, 'action_list']);

        // Client asset inclusion
        if ($this->rcmail->task === 'mail' || $this->rcmail->task === 'settings') {
            $this->include_stylesheet('email_scheduler.css');
            $this->include_script('email_scheduler.js');
        }
    }

    /**
     * Hook: Inject compose controls (Send Later split button, schedule modal, and undo toast).
     */
    public function hook_message_compose(array $args): array
    {
        $undoDelay = (int)$this->rcmail->config->get('undo_send_delay', $this->rcmail->config->get('email_scheduler_default_delay', 10));
        $scheduleEnabled = (bool)$this->rcmail->config->get('email_scheduler_schedule_enabled', true);

        // Export config variables to client JavaScript environment
        $this->rcmail->output->set_env('email_scheduler_undo_delay', $undoDelay);
        $this->rcmail->output->set_env('email_scheduler_schedule_enabled', $scheduleEnabled);

        $now = new DateTime('now', new DateTimeZone($this->rcmail->config->get('timezone', 'UTC') ?: 'UTC'));

        // Preset 1: Tomorrow morning (8:00 AM)
        $tomorrowMorning = (clone $now)->modify('+1 day')->setTime(8, 0, 0);
        // Preset 2: Tomorrow afternoon (1:00 PM)
        $tomorrowAfternoon = (clone $now)->modify('+1 day')->setTime(13, 0, 0);
        // Preset 3: Next Monday morning (8:00 AM)
        $nextMonday = (clone $now)->modify('next monday')->setTime(8, 0, 0);

        $this->rcmail->output->set_env('email_scheduler_presets', [
            'tomorrow_morning' => $tomorrowMorning->format('Y-m-d H:i:s'),
            'tomorrow_morning_label' => $tomorrowMorning->format('D, M j, g:i A'),
            'tomorrow_afternoon' => $tomorrowAfternoon->format('Y-m-d H:i:s'),
            'tomorrow_afternoon_label' => $tomorrowAfternoon->format('D, M j, g:i A'),
            'next_monday' => $nextMonday->format('Y-m-d H:i:s'),
            'next_monday_label' => $nextMonday->format('D, M j, g:i A'),
        ]);

        $toolbarBtnEnabled = (bool)$this->rcmail->config->get('email_scheduler_toolbar_button', false);
        if ($scheduleEnabled && $toolbarBtnEnabled) {
            $this->add_button([
                'command' => 'plugin.email_scheduler-schedule',
                'id' => 'btn-send-later-toolbar',
                'class' => 'button send schedule',
                'classact' => 'button send schedule active',
                'innerclass' => 'inner',
                'label' => 'email_scheduler.send_later_btn',
                'title' => 'email_scheduler.send_later_btn',
                'type' => 'link',
            ], 'toolbar');
        }

        return $args;
    }

    /**
     * Hook: Intercept message before SMTP delivery to enforce Undo Send or Schedule Delivery.
     */
    public function hook_message_before_send(array $args): array
    {
        $action = trim((string)rcube_utils::get_input_value('_email_scheduler_action', rcube_utils::INPUT_POST));
        $scheduleTime = trim((string)rcube_utils::get_input_value('_email_scheduler_send_at', rcube_utils::INPUT_POST));

        if ($action === 'schedule' && !empty($scheduleTime)) {
            $parsedDate = strtotime($scheduleTime);
            if ($parsedDate && $parsedDate > time()) {
                $queueId = $this->enqueueMessage($args, date('Y-m-d H:i:s', $parsedDate), 'scheduled');
                if ($queueId > 0) {
                    $formattedTime = date('M j, Y g:i A', $parsedDate);
                    $msg = sprintf($this->gettext('message_scheduled_toast'), $formattedTime);

                    // Abort direct delivery so message waits in queue
                    $args['abort'] = true;
                    $args['result'] = true;
                    $this->rcmail->output->command('display_message', $msg, 'confirmation');
                }
            }
        }

        return $args;
    }

    /**
     * Enqueue message in email_scheduler_queue database table.
     */
    public function enqueueMessage(array $args, string $sendAt, string $status = 'scheduled'): int
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $headers = [];
        $body = '';
        $rawHeaders = '';

        // 1. Extract from Roundcube Mail_mime / rcube_mime object
        if (!empty($args['message']) && is_object($args['message'])) {
            $mime = $args['message'];

            // If delay_file_io was enabled, body may be in a temp file
            if (method_exists($mime, 'getParam') && $mime->getParam('delay_file_io') && method_exists($mime, 'saveMessageBody')) {
                $tempFile = rcube_utils::temp_filename('msg_sched');
                $res = $mime->saveMessageBody($tempFile);
                if (!is_a($res, 'PEAR_Error') && file_exists($tempFile)) {
                    $body = (string)file_get_contents($tempFile);
                    @unlink($tempFile);
                }
            }

            // Standard Mail_mime: get() MUST be called before headers() to compile MIME boundaries
            if ($body === '' && method_exists($mime, 'get')) {
                $mimeBody = $mime->get();
                if (is_string($mimeBody) && $mimeBody !== '') {
                    $body = $mimeBody;
                }
            }

            if ($body === '' && method_exists($mime, 'getTXTBody')) {
                $txt = $mime->getTXTBody();
                if (is_string($txt) && $txt !== '') {
                    $body = $txt;
                }
            }

            if (method_exists($mime, 'headers')) {
                $headers = (array)$mime->headers();
            }

            if (method_exists($mime, 'txtHeaders')) {
                $rawHeaders = (string)$mime->txtHeaders(['Bcc' => null], true);
            }
        }

        // 2. Merge explicit headers/body if provided in $args (e.g. tests or custom callers)
        if (!empty($args['headers']) && is_array($args['headers'])) {
            $headers = array_merge($headers, $args['headers']);
        }
        if ($body === '' && !empty($args['body'])) {
            $body = (string)$args['body'];
        }

        // 3. Subject extraction with POST fallback
        $subject = '';
        if (!empty($headers['Subject'])) {
            $subject = (string)$headers['Subject'];
        } elseif (!empty($headers['subject'])) {
            $subject = (string)$headers['subject'];
        }

        if ($subject === '') {
            $postSubject = rcube_utils::get_input_value('_subject', rcube_utils::INPUT_POST);
            if ($postSubject !== null && $postSubject !== '') {
                $subject = trim((string)$postSubject);
                $headers['Subject'] = $subject;
            }
        }

        // 4. Body extraction with POST fallback
        if ($body === '') {
            $postBody = rcube_utils::get_input_value('_message', rcube_utils::INPUT_POST);
            if ($postBody !== null && $postBody !== '') {
                $body = (string)$postBody;
                $isHtml = (bool)rcube_utils::get_input_value('_is_html', rcube_utils::INPUT_POST);
                if ($isHtml && empty($headers['Content-Type'])) {
                    $headers['Content-Type'] = 'text/html; charset=UTF-8';
                }
            }
        }

        // 5. From extraction with POST fallback
        $from = (string)($args['from'] ?? ($headers['From'] ?? ''));
        if ($from === '') {
            $postFrom = rcube_utils::get_input_value('_from', rcube_utils::INPUT_POST);
            if ($postFrom !== null && $postFrom !== '') {
                $from = (string)$postFrom;
                $headers['From'] = $from;
            }
        }

        // 6. Recipients extraction with POST fallback
        $mailto = $args['mailto'] ?? ($headers['To'] ?? '');
        if (is_array($mailto)) {
            $recipients = implode(', ', $mailto);
        } else {
            $recipients = (string)$mailto;
        }

        if ($recipients === '') {
            $postTo = rcube_utils::get_input_value('_to', rcube_utils::INPUT_POST);
            if ($postTo !== null && $postTo !== '') {
                $recipients = (string)$postTo;
                $headers['To'] = $recipients;
            }
        }

        // 7. Message-ID
        $messageId = $headers['Message-ID'] ?? ($args['headers']['Message-ID'] ?? sprintf('<%s@%s>', md5(uniqid((string)mt_rand(), true)), $_SERVER['SERVER_NAME'] ?? 'localhost'));
        $headers['Message-ID'] = $messageId;

        // 8. Default Date and MIME-Version if missing
        if (empty($headers['Date'])) {
            $headers['Date'] = date('r');
        }
        if (empty($headers['MIME-Version'])) {
            $headers['MIME-Version'] = '1.0';
        }

        $serializedHeaders = json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $serializedParams = json_encode([
            'from' => $from,
            'mailto' => $recipients,
            'options' => $args['options'] ?? [],
            'raw_headers' => $rawHeaders,
            'charset' => $args['charset'] ?? 'UTF-8',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $query = "INSERT INTO {$this->table}
            (user_id, message_id, subject, recipients, status, headers, body, parameters, send_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $db->query(
            $query,
            $userId,
            $messageId,
            $subject,
            $recipients,
            $status,
            $serializedHeaders,
            $body,
            $serializedParams,
            $sendAt,
            date('Y-m-d H:i:s')
        );

        return (int)$db->insert_id($this->table);
    }

    /**
     * Action: Undo / Cancel a pending delayed delivery.
     */
    public function action_undo(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $queueId = (int)rcube_utils::get_input_value('queue_id', rcube_utils::INPUT_POST);
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $db = $this->rcmail->get_dbh();

        $query = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'delayed'";
        $db->query($query, $queueId, $userId);

        $success = ($db->affected_rows() > 0);

        $this->jsonResponse([
            'success' => $success,
            'message' => $success ? $this->gettext('sending_undone') : 'Message could not be undone.',
        ]);
    }

    /**
     * Action: Cancel a scheduled email.
     */
    public function action_cancel(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $queueId = (int)rcube_utils::get_input_value('id', rcube_utils::INPUT_POST);
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $db = $this->rcmail->get_dbh();

        $query = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('scheduled', 'delayed')";
        $db->query($query, $queueId, $userId);

        $this->jsonResponse([
            'success' => ($db->affected_rows() > 0),
            'message' => $this->gettext('msg_cancelled'),
        ]);
    }

    /**
     * Action: Immediately send a scheduled email.
     */
    public function action_send_now(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $queueId = (int)rcube_utils::get_input_value('id', rcube_utils::INPUT_POST);
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $db = $this->rcmail->get_dbh();

        $res = $db->query("SELECT * FROM {$this->table} WHERE id = ? AND user_id = ? AND status IN ('scheduled', 'delayed')", $queueId, $userId);
        $row = $db->fetch_assoc($res);

        if ($row) {
            $db->query("UPDATE {$this->table} SET status = 'processing' WHERE id = ? AND user_id = ? AND status IN ('scheduled', 'delayed')", $queueId, $userId);
            if ($db->affected_rows() > 0) {
                $sent = $this->deliverQueuedMessage($row);
                $this->jsonResponse([
                    'success' => $sent,
                    'message' => $sent ? $this->gettext('msg_sent_now') : 'Delivery error occurred.',
                ]);
                return;
            }
        }

        $this->jsonResponse(['success' => false, 'message' => 'Message not found.']);
    }

    /**
     * Action: Return list of user's pending scheduled messages.
     */
    public function action_list(): void
    {
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $res = $db->query("SELECT id, subject, recipients, send_at, status FROM {$this->table} WHERE user_id = ? AND status IN ('scheduled', 'delayed') ORDER BY send_at ASC", $userId);
        $list = [];

        while ($row = $db->fetch_assoc($res)) {
            $list[] = $row;
        }

        $this->jsonResponse(['success' => true, 'data' => $list]);
    }

    /**
     * Action: Web cron endpoint.
     */
    public function action_cron(): void
    {
        $isCli = (php_sapi_name() === 'cli');
        $token = (string)$this->rcmail->config->get('email_scheduler_cron_token');

        if (!$isCli) {
            if ($token !== '') {
                $reqToken = (string)rcube_utils::get_input_value('token', rcube_utils::INPUT_GET);
                if (!hash_equals($token, $reqToken)) {
                    header('HTTP/1.1 403 Forbidden', true, 403);
                    exit('Forbidden');
                }
            } elseif (!$this->rcmail->user) {
                header('HTTP/1.1 403 Forbidden', true, 403);
                exit('Forbidden: cron token required for unauthenticated web dispatch');
            }
        }

        $processed = $this->processDueMessages();
        $this->jsonResponse(['success' => true, 'processed' => $processed]);
    }

    /**
     * Process due messages from queue where send_at <= NOW().
     */
    public function processDueMessages(): int
    {
        if (!isset($this->rcmail)) {
            $this->rcmail = rcmail::get_instance();
        }
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $nowStr = date('Y-m-d H:i:s');
        $query = "SELECT * FROM {$this->table} WHERE status IN ('scheduled', 'delayed') AND send_at <= ? ORDER BY send_at ASC LIMIT 50";
        $res = $db->query($query, $nowStr);
        $count = 0;

        while ($row = $db->fetch_assoc($res)) {
            // Atomically claim the row to prevent concurrent workers from double-sending
            $db->query(
                "UPDATE {$this->table} SET status = 'processing' WHERE id = ? AND status IN ('scheduled', 'delayed')",
                $row['id']
            );
            if ($db->affected_rows() <= 0) {
                // Another worker already claimed this message
                continue;
            }

            if ($this->deliverQueuedMessage($row)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Delivers a single queued message record.
     */
    public function deliverQueuedMessage(array $row): bool
    {
        $db = $this->rcmail->get_dbh();
        $headers = json_decode($row['headers'] ?? '{}', true) ?: [];
        $params = json_decode($row['parameters'] ?? '{}', true) ?: [];
        $body = (string)($row['body'] ?? '');
        $recipients = (string)($row['recipients'] ?? '');
        $subject = (string)($row['subject'] ?? ($headers['Subject'] ?? ''));
        $from = (string)($params['from'] ?? ($headers['From'] ?? ''));

        // Restore missing properties from headers or row
        if ($subject === '' && !empty($headers['Subject'])) {
            $subject = (string)$headers['Subject'];
        }
        if ($from === '' && !empty($headers['From'])) {
            $from = (string)$headers['From'];
        }
        if ($recipients === '' && !empty($headers['To'])) {
            $recipients = (string)$headers['To'];
        }

        // Standardize required MIME headers
        if (empty($headers['Date'])) {
            $headers['Date'] = date('r');
        }
        if (empty($headers['MIME-Version'])) {
            $headers['MIME-Version'] = '1.0';
        }
        if ($subject !== '' && empty($headers['Subject'])) {
            $headers['Subject'] = $subject;
        }
        if ($from !== '' && empty($headers['From'])) {
            $headers['From'] = $from;
        }
        if ($recipients !== '' && empty($headers['To'])) {
            $headers['To'] = $recipients;
        }
        if (empty($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
        }

        $cleanSubject = preg_replace('/[\r\n]+/', ' ', trim($subject));
        $cleanFrom = preg_replace('/[\r\n]+/', '', trim($from));
        $cleanRecipients = preg_replace('/[\r\n]+/', '', trim($recipients));
        $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($cleanSubject, 'UTF-8') : $cleanSubject;

        // Build recipient list
        $recipientsList = [];
        foreach (explode(',', $cleanRecipients) as $rcpt) {
            $rcpt = trim($rcpt);
            if ($rcpt !== '') {
                $recipientsList[] = $rcpt;
            }
        }
        if (!empty($headers['Cc'])) {
            foreach (explode(',', (string)$headers['Cc']) as $rcpt) {
                $rcpt = trim($rcpt);
                if ($rcpt !== '' && !in_array($rcpt, $recipientsList, true)) {
                    $recipientsList[] = $rcpt;
                }
            }
        }
        if (!empty($headers['Bcc'])) {
            foreach (explode(',', (string)$headers['Bcc']) as $rcpt) {
                $rcpt = trim($rcpt);
                if ($rcpt !== '' && !in_array($rcpt, $recipientsList, true)) {
                    $recipientsList[] = $rcpt;
                }
            }
        }

        // Build SMTP headers string
        $rawHeadersStr = (string)($params['raw_headers'] ?? '');
        if ($rawHeadersStr === '') {
            $headerLines = [];
            foreach ($headers as $k => $v) {
                if (strcasecmp($k, 'Bcc') === 0) {
                    continue; // Exclude Bcc from message payload
                }
                if (is_array($v)) {
                    foreach ($v as $subV) {
                        $headerLines[] = "{$k}: {$subV}";
                    }
                } else {
                    $headerLines[] = "{$k}: {$v}";
                }
            }
            $smtpHeadersStr = implode("\r\n", $headerLines);
        } else {
            $smtpHeadersStr = $rawHeadersStr;
        }

        $delivered = false;
        $errorMsg = '';

        try {
            // 1. Try delivery using Roundcube's native SMTP engine
            if (isset($this->rcmail)) {
                if (method_exists($this->rcmail, 'smtp_init')) {
                    $this->rcmail->smtp_init(true);
                }
                if (!empty($this->rcmail->smtp) && is_object($this->rcmail->smtp) && method_exists($this->rcmail->smtp, 'send_mail')) {
                    $smtpOpts = $params['options'] ?? [];
                    $delivered = (bool)$this->rcmail->smtp->send_mail($cleanFrom, $recipientsList, $smtpHeadersStr, $body, $smtpOpts);
                    if (!$delivered) {
                        $smtpErr = method_exists($this->rcmail->smtp, 'get_error') ? $this->rcmail->smtp->get_error() : null;
                        if ($smtpErr) {
                            $errorMsg = is_array($smtpErr) ? ($smtpErr['message'] ?? json_encode($smtpErr)) : (string)$smtpErr;
                        }
                    }
                }
            }

            // 2. Fallback to PHP native mail() if SMTP was not used or failed
            if (!$delivered) {
                // For mail(), omit Subject, To, and Bcc because mail() passes them separately
                $mailHeaderLines = [];
                foreach ($headers as $k => $v) {
                    if (in_array(strtolower($k), ['subject', 'to', 'bcc'], true)) {
                        continue;
                    }
                    if (is_array($v)) {
                        foreach ($v as $subV) {
                            $mailHeaderLines[] = "{$k}: {$subV}";
                        }
                    } else {
                        $mailHeaderLines[] = "{$k}: {$v}";
                    }
                }
                $mailHeaders = implode("\r\n", $mailHeaderLines);

                $delivered = @mail($cleanRecipients, $encodedSubject, $body, $mailHeaders);
                if (!$delivered && empty($errorMsg)) {
                    $errorMsg = 'SMTP and native mail delivery failed';
                }
            }

            if ($delivered) {
                $db->query("UPDATE {$this->table} SET status = 'sent', sent_at = ? WHERE id = ?", date('Y-m-d H:i:s'), $row['id']);

                // Optionally save to Sent mailbox if storage is available
                try {
                    if (!empty($this->rcmail) && !empty($this->rcmail->storage) && is_object($this->rcmail->storage) && method_exists($this->rcmail->storage, 'save_message')) {
                        $sentMbox = (string)$this->rcmail->config->get('sent_mbox');
                        if ($sentMbox !== '' && !$this->rcmail->config->get('no_save_sent_messages')) {
                            $fullMsg = $smtpHeadersStr . "\r\n\r\n" . $body;
                            $this->rcmail->storage->save_message($sentMbox, $fullMsg);
                        }
                    }
                } catch (\Throwable $e) {
                    // Non-fatal
                }

                return true;
            } else {
                $db->query("UPDATE {$this->table} SET status = 'failed', error = ? WHERE id = ?", $errorMsg ?: 'Delivery failed', $row['id']);
                return false;
            }
        } catch (\Throwable $e) {
            $db->query("UPDATE {$this->table} SET status = 'failed', error = ? WHERE id = ?", $e->getMessage(), $row['id']);
            return false;
        }
    }

    /**
     * Hook: Register section in Preferences.
     */
    public function hook_preferences_sections_list(array $args): array
    {
        $args['list']['email_scheduler'] = [
            'id' => 'email_scheduler',
            'section' => 'email_scheduler',
            'name' => $this->gettext('email_scheduler_title'),
        ];
        return $args;
    }

    /**
     * Hook: Render Settings UI for Undo Send & Email Scheduler.
     */
    public function hook_preferences_list(array $args): array
    {
        if ($args['section'] !== 'email_scheduler') {
            return $args;
        }

        $undoDelay = (int)$this->rcmail->config->get('undo_send_delay', $this->rcmail->config->get('email_scheduler_default_delay', 10));

        $select = new html_select(['name' => '_undo_send_delay', 'id' => 'rcmfd_undo_send_delay', 'class' => 'form-control']);
        $select->add($this->gettext('undo_send_disabled'), 0);
        $select->add(sprintf($this->gettext('undo_send_seconds'), 5), 5);
        $select->add(sprintf($this->gettext('undo_send_seconds'), 10), 10);
        $select->add(sprintf($this->gettext('undo_send_seconds'), 20), 20);
        $select->add(sprintf($this->gettext('undo_send_seconds'), 30), 30);

        // Scheduled messages list
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $res = $db->query("SELECT id, subject, recipients, send_at, status FROM {$this->table} WHERE user_id = ? AND status IN ('scheduled', 'delayed') ORDER BY send_at ASC", $userId);
        $schedTitle = rcube::Q($this->gettext('scheduled_messages_list'));
        $tableHtml = '<div id="scheduled-messages-container" class="scheduled-messages-container" style="width: 100%; margin-top: 16px;">';
        $tableHtml .= '<h3 class="scheduled-messages-heading" style="font-size: 15px; font-weight: 600; margin: 0 0 12px 0; color: inherit;">' . $schedTitle . '</h3>';
        $tableHtml .= '<div class="table-responsive" style="width: 100%; overflow-x: auto;">';
        $tableHtml .= '<table class="table table-bordered table-striped" id="scheduled-emails-table" style="width: 100%; margin-bottom: 0;">';
        $tableHtml .= '<thead><tr>';
        $tableHtml .= '<th style="width: 35%;">' . rcube::Q($this->gettext('col_subject')) . '</th>';
        $tableHtml .= '<th style="width: 30%;">' . rcube::Q($this->gettext('col_recipients')) . '</th>';
        $tableHtml .= '<th style="width: 20%;">' . rcube::Q($this->gettext('col_send_at')) . '</th>';
        $tableHtml .= '<th style="width: 15%; text-align: right; white-space: nowrap;">' . rcube::Q($this->gettext('col_actions')) . '</th>';
        $tableHtml .= '</tr></thead><tbody>';

        $hasRows = false;
        while ($row = $db->fetch_assoc($res)) {
            $hasRows = true;
            $tableHtml .= sprintf(
                '<tr id="sched-row-%d"><td>%s</td><td>%s</td><td>%s</td><td style="text-align: right; white-space: nowrap;">' .
                '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="email_scheduler_send_now(%d)">%s</button> ' .
                '<button type="button" class="btn btn-sm btn-danger" onclick="email_scheduler_cancel(%d)">%s</button>' .
                '</td></tr>',
                $row['id'],
                rcube::Q($row['subject'] ?: '(No Subject)'),
                rcube::Q($row['recipients']),
                rcube::Q(date('M j, Y g:i A', strtotime($row['send_at']))),
                $row['id'],
                rcube::Q($this->gettext('action_send_now')),
                $row['id'],
                rcube::Q($this->gettext('action_cancel'))
            );
        }

        if (!$hasRows) {
            $tableHtml .= '<tr><td colspan="4" class="text-center text-muted p-3" style="text-align: center; padding: 16px; color: #5f6368; font-style: italic;">' . rcube::Q($this->gettext('scheduled_none')) . '</td></tr>';
        }
        $tableHtml .= '</tbody></table></div></div>';

        $args['blocks']['email_scheduler'] = [
            'name' => $this->gettext('email_scheduler_title'),
            'options' => [
                'undo_send_delay' => [
                    'title' => html::label('rcmfd_undo_send_delay', rcube::Q($this->gettext('undo_send_delay'))),
                    'content' => $select->show($undoDelay),
                ],
                'scheduled_messages' => [
                    'content' => $tableHtml,
                ],
            ],
        ];

        return $args;
    }

    /**
     * Hook: Persist Undo Send preferences.
     */
    public function hook_preferences_save(array $args): array
    {
        if ($args['section'] === 'email_scheduler') {
            $delay = (int)rcube_utils::get_input_value('_undo_send_delay', rcube_utils::INPUT_POST);
            $args['prefs']['undo_send_delay'] = max(0, min(30, $delay));
        }
        return $args;
    }

    /**
     * Automatically creates the queue table if missing.
     */
    private function ensureTableExists(rcube_db $db): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $driver = $db->db_provider;
        $sql = match ($driver) {
            'sqlite' => "CREATE TABLE IF NOT EXISTS {$this->table} (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL DEFAULT 0, message_id VARCHAR(255) NOT NULL DEFAULT '', subject VARCHAR(500) NOT NULL DEFAULT '', recipients TEXT NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'delayed', headers TEXT, body TEXT, parameters TEXT, send_at DATETIME NOT NULL, created_at DATETIME NOT NULL, sent_at DATETIME, error TEXT);",
            'pgsql', 'postgres' => "CREATE TABLE IF NOT EXISTS {$this->table} (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL DEFAULT 0, message_id VARCHAR(255) NOT NULL DEFAULT '', subject VARCHAR(500) NOT NULL DEFAULT '', recipients TEXT NOT NULL, status VARCHAR(32) NOT NULL DEFAULT 'delayed', headers TEXT, body TEXT, parameters TEXT, send_at TIMESTAMP NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, sent_at TIMESTAMP, error TEXT);",
            default => "CREATE TABLE IF NOT EXISTS `{$this->table}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` INT UNSIGNED NOT NULL DEFAULT 0, `message_id` VARCHAR(255) NOT NULL DEFAULT '', `subject` VARCHAR(500) NOT NULL DEFAULT '', `recipients` TEXT NOT NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'delayed', `headers` MEDIUMTEXT NULL, `body` LONGTEXT NULL, `parameters` MEDIUMTEXT NULL, `send_at` DATETIME NOT NULL, `created_at` DATETIME NOT NULL, `sent_at` DATETIME NULL, `error` TEXT NULL, PRIMARY KEY (`id`), INDEX `user_status` (`user_id`, `status`), INDEX `status_send_at` (`status`, `send_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        };

        @$db->query($sql);
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
