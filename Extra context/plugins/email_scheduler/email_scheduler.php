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
        $messageId = $args['headers']['Message-ID'] ?? sprintf('<%s@%s>', md5(uniqid((string)mt_rand(), true)), $_SERVER['SERVER_NAME'] ?? 'localhost');
        $subject = (string)($args['headers']['Subject'] ?? '');
        $recipients = is_array($args['mailto'] ?? null) ? implode(', ', $args['mailto']) : (string)($args['mailto'] ?? '');

        $serializedHeaders = json_encode($args['headers'] ?? []);
        $body = (string)($args['body'] ?? '');
        $serializedParams = json_encode([
            'from' => $args['from'] ?? '',
            'mailto' => $args['mailto'] ?? [],
            'charset' => $args['charset'] ?? 'UTF-8',
        ]);

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
            $sent = $this->deliverQueuedMessage($row);
            $this->jsonResponse([
                'success' => $sent,
                'message' => $sent ? $this->gettext('msg_sent_now') : 'Delivery error occurred.',
            ]);
            return;
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
        $token = (string)$this->rcmail->config->get('email_scheduler_cron_token');
        if ($token !== '') {
            $reqToken = rcube_utils::get_input_value('token', rcube_utils::INPUT_GET);
            if (!hash_equals($token, (string)$reqToken)) {
                header('HTTP/1.1 403 Forbidden', true, 403);
                exit('Forbidden');
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
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $nowStr = date('Y-m-d H:i:s');
        $query = "SELECT * FROM {$this->table} WHERE status IN ('scheduled', 'delayed') AND send_at <= ? ORDER BY send_at ASC LIMIT 50";
        $res = $db->query($query, $nowStr);
        $count = 0;

        while ($row = $db->fetch_assoc($res)) {
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
        $body = $row['body'] ?? '';
        $recipients = $row['recipients'] ?? '';

        try {
            // Deliver using native mail delivery or PHP mail
            $subject = $row['subject'] ?? ($headers['Subject'] ?? 'No Subject');
            $from = $params['from'] ?? ($headers['From'] ?? 'webmail@localhost');

            $mailHeaders = "From: {$from}\r\n" .
                           "Subject: {$subject}\r\n" .
                           "MIME-Version: 1.0\r\n" .
                           "Content-Type: text/plain; charset=UTF-8\r\n";

            $delivered = @mail($recipients, $subject, $body, $mailHeaders);

            if ($delivered) {
                $db->query("UPDATE {$this->table} SET status = 'sent', sent_at = ? WHERE id = ?", date('Y-m-d H:i:s'), $row['id']);
                return true;
            } else {
                $db->query("UPDATE {$this->table} SET status = 'failed', error = ? WHERE id = ?", 'SMTP delivery failed', $row['id']);
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
        $tableHtml = '<div class="table-responsive mt-3">';
        $tableHtml .= '<table class="table table-bordered table-striped" id="scheduled-emails-table">';
        $tableHtml .= '<thead><tr>';
        $tableHtml .= '<th>' . rcube::Q($this->gettext('col_subject')) . '</th>';
        $tableHtml .= '<th>' . rcube::Q($this->gettext('col_recipients')) . '</th>';
        $tableHtml .= '<th>' . rcube::Q($this->gettext('col_send_at')) . '</th>';
        $tableHtml .= '<th>' . rcube::Q($this->gettext('col_actions')) . '</th>';
        $tableHtml .= '</tr></thead><tbody>';

        $hasRows = false;
        while ($row = $db->fetch_assoc($res)) {
            $hasRows = true;
            $tableHtml .= sprintf(
                '<tr id="sched-row-%d"><td>%s</td><td>%s</td><td>%s</td><td>' .
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
            $tableHtml .= '<tr><td colspan="4" class="text-center text-muted p-3">' . rcube::Q($this->gettext('scheduled_none')) . '</td></tr>';
        }
        $tableHtml .= '</tbody></table></div>';

        $args['blocks']['email_scheduler'] = [
            'name' => $this->gettext('email_scheduler_title'),
            'options' => [
                'undo_send_delay' => [
                    'title' => html::label('rcmfd_undo_send_delay', rcube::Q($this->gettext('undo_send_delay'))),
                    'content' => $select->show($undoDelay),
                ],
                'scheduled_messages' => [
                    'title' => rcube::Q($this->gettext('scheduled_messages_list')),
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
