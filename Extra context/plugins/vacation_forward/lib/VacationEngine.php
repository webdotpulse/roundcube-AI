<?php

/**
 * Vacation & Auto-Reply Evaluation Engine
 *
 * Implements vacation period scheduling, timezone calculations,
 * RFC 3834 auto-submitted compliance, anti-loop heuristics,
 * and rate-limiting throttling.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

require_once __DIR__ . '/TemplateManager.php';

class VacationForwardVacationEngine
{
    private rcmail $rcmail;

    public function __construct(?rcmail $rcmail = null)
    {
        $this->rcmail = $rcmail ?? rcmail::get_instance();
    }

    /**
     * Determines whether the vacation responder is active right now.
     *
     * @return array [isActive, statusLabel, reason]
     */
    public function evaluateStatus(array $prefs): array
    {
        $status = $prefs['vacation_status'] ?? 'disabled';

        if ($status === 'disabled') {
            return [false, 'disabled', 'Auto-reply is disabled'];
        }

        if ($status === 'enabled') {
            return [true, 'active', 'Auto-reply is permanently enabled'];
        }

        if ($status === 'scheduled') {
            $tzName = $prefs['timezone'] ?? ($this->rcmail->config->get('timezone') ?: 'UTC');
            try {
                $tz = new DateTimeZone($tzName);
            } catch (\Throwable) {
                $tz = new DateTimeZone('UTC');
            }

            $now = new DateTime('now', $tz);
            $startStr = $prefs['vacation_start'] ?? '';
            $endStr = $prefs['vacation_end'] ?? '';

            if (empty($startStr) || empty($endStr)) {
                return [false, 'disabled', 'Scheduled period dates are missing'];
            }

            try {
                $startDate = new DateTime($startStr, $tz);
                $endDate = new DateTime($endStr, $tz);
            } catch (\Throwable) {
                return [false, 'disabled', 'Invalid date format in schedule'];
            }

            if ($now < $startDate) {
                return [false, 'scheduled', 'Vacation starts on ' . $startDate->format('Y-m-d H:i')];
            }

            if ($now > $endDate) {
                return [false, 'expired', 'Vacation ended on ' . $endDate->format('Y-m-d H:i')];
            }

            return [true, 'active', 'Vacation active until ' . $endDate->format('Y-m-d H:i')];
        }

        return [false, 'disabled', 'Unknown status'];
    }

    /**
     * Evaluates whether an incoming message should be suppressed (loops, daemons, mailing lists).
     *
     * @return array [shouldSuppress, reason]
     */
    public function shouldSuppress(string $senderEmail, array $headers, array $prefs): array
    {
        $cleanSender = strtolower(trim($senderEmail));

        if ($cleanSender === '') {
            return [true, 'Empty sender address'];
        }

        // 1. System daemons & bounces
        $daemonPrefixes = ['mailer-daemon@', 'postmaster@', 'noreply@', 'no-reply@', 'donotreply@', 'bounce@', 'bounces@'];
        foreach ($daemonPrefixes as $dp) {
            if (str_starts_with($cleanSender, $dp) || str_contains($cleanSender, '+' . $dp)) {
                return [true, "Sender matches system daemon prefix ({$dp})"];
            }
        }

        // 2. Auto-Submitted header (RFC 3834)
        $autoSubmitted = strtolower(trim((string)($headers['Auto-Submitted'] ?? $headers['auto-submitted'] ?? '')));
        if ($autoSubmitted !== '' && $autoSubmitted !== 'no') {
            return [true, "Auto-Submitted header present: {$autoSubmitted}"];
        }

        // 3. Precedence header
        $precedence = strtolower(trim((string)($headers['Precedence'] ?? $headers['precedence'] ?? '')));
        if (in_array($precedence, ['bulk', 'list', 'junk', 'auto_reply'], true)) {
            return [true, "Precedence header indicates automated email: {$precedence}"];
        }

        // 4. Mailing list headers
        if (!empty($headers['List-Id']) || !empty($headers['list-id']) ||
            !empty($headers['List-Unsubscribe']) || !empty($headers['list-unsubscribe']) ||
            !empty($headers['List-Post']) || !empty($headers['list-post'])) {
            return [true, 'Message originated from a mailing list'];
        }

        // 5. Microsoft Exchange / suppression headers
        $autoSuppress = strtolower(trim((string)($headers['X-Auto-Response-Suppress'] ?? $headers['x-auto-response-suppress'] ?? '')));
        if ($autoSuppress !== '' && (str_contains($autoSuppress, 'all') || str_contains($autoSuppress, 'oof'))) {
            return [true, "X-Auto-Response-Suppress header present: {$autoSuppress}"];
        }

        // 6. User blacklist filter
        $blacklistRaw = $prefs['vacation_blacklist'] ?? '';
        if ($blacklistRaw !== '') {
            $lines = preg_split('/[\r\n,]+/', $blacklistRaw);
            foreach ($lines as $line) {
                $pattern = strtolower(trim($line));
                if ($pattern === '') {
                    continue;
                }
                if (str_starts_with($pattern, '@') && str_ends_with($cleanSender, $pattern)) {
                    return [true, "Sender matches blacklisted domain: {$pattern}"];
                }
                if ($cleanSender === $pattern || str_contains($cleanSender, $pattern)) {
                    return [true, "Sender matches blacklisted pattern: {$pattern}"];
                }
            }
        }

        return [false, 'OK'];
    }

    /**
     * Checks if reply rate limit has been exceeded for this sender.
     *
     * @return bool True if allowed to reply, false if throttled.
     */
    public function checkRateLimit(int $userId, string $senderEmail, string $intervalCode): bool
    {
        if ($intervalCode === 'unlimited') {
            return true;
        }

        $seconds = match ($intervalCode) {
            '1_hour' => 3600,
            '6_hours' => 21600,
            '12_hours' => 43200,
            '1_day' => 86400,
            '2_days' => 172800,
            '3_days' => 259200,
            '7_days' => 604800,
            '14_days' => 1209600,
            default => 86400,
        };

        $db = $this->rcmail->get_dbh();
        $this->ensureLogsTable($db);

        $cutoff = date('Y-m-d H:i:s', time() - $seconds);
        $cleanSender = strtolower(trim($senderEmail));

        $sql = "SELECT id FROM vacation_forward_logs 
                WHERE user_id = ? AND sender = ? AND action_type = 'auto_reply' AND status = 'sent' AND created_at >= ? 
                LIMIT 1";

        $res = $db->query($sql, $userId, $cleanSender, $cutoff);
        if ($db->fetch_assoc($res)) {
            // Already replied within interval window
            return false;
        }

        return true;
    }

    /**
     * Executes the vacation auto-reply.
     */
    public function processAutoReply(
        int $userId,
        string $senderEmail,
        string $senderFromHeader,
        string $originalSubject,
        string $originalBody,
        string $originalMessageId,
        array $headers,
        array $prefs,
        array $identity
    ): bool {
        // 1. Status evaluation
        [$isActive, , $statusReason] = $this->evaluateStatus($prefs);
        if (!$isActive) {
            return false;
        }

        // 2. Loop suppression check
        [$shouldSuppress, $suppressReason] = $this->shouldSuppress($senderEmail, $headers, $prefs);
        if ($shouldSuppress) {
            $this->logActivity($userId, $senderEmail, $identity['email'] ?? '', $originalSubject, 'skipped', 'suppressed', 'Loop suppression: ' . $suppressReason, $originalMessageId);
            return false;
        }

        // 3. Rate limiting check
        $interval = $prefs['vacation_rate_interval'] ?? '1_day';
        if (!$this->checkRateLimit($userId, $senderEmail, $interval)) {
            $this->logActivity($userId, $senderEmail, $identity['email'] ?? '', $originalSubject, 'auto_reply', 'throttled', 'Throttled by rate limit interval (' . $interval . ')', $originalMessageId);
            return false;
        }

        // 4. Resolve multi-language template
        $templates = $prefs['vacation_templates'] ?? [];
        [$template, $matchReason, $detectedLang] = VacationForwardTemplateManager::resolveTemplate(
            $templates,
            $senderEmail,
            $originalSubject,
            $originalBody,
            $headers
        );

        // 5. Interpolate placeholders
        $userTz = $prefs['timezone'] ?? ($this->rcmail->config->get('timezone') ?: 'UTC');
        $startDateStr = !empty($prefs['vacation_start']) ? date('M j, Y', strtotime($prefs['vacation_start'])) : '';
        $endDateStr = !empty($prefs['vacation_end']) ? date('M j, Y', strtotime($prefs['vacation_end'])) : '';
        $returnDateStr = VacationForwardTemplateManager::calculateReturnDate($prefs['vacation_end'] ?? null, $userTz);
        $senderName = VacationForwardTemplateManager::extractSenderName($senderFromHeader, $senderEmail);

        $context = [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'return_date' => $returnDateStr,
            'sender_name' => $senderName,
            'sender_email' => $senderEmail,
            'original_subject' => $originalSubject,
            'user_name' => $identity['name'] ?? 'User',
            'user_email' => $identity['email'] ?? '',
        ];

        $subjectTpl = $template['subject'] ?? 'Out of Office: {ORIGINAL_SUBJECT}';
        $bodyTpl = $template['body'] ?? "I am out of the office until {END_DATE}.";

        $finalSubject = VacationForwardTemplateManager::interpolate($subjectTpl, $context);
        $finalBody = VacationForwardTemplateManager::interpolate($bodyTpl, $context);

        // Optional custom subject prefix
        $prefix = $prefs['vacation_subject_prefix'] ?? '';
        if ($prefix !== '' && !str_starts_with($finalSubject, $prefix)) {
            $finalSubject = $prefix . ' ' . $finalSubject;
        }

        // 6. Send the RFC 3834 auto-reply
        $userEmail = $identity['email'] ?? 'webmail@localhost';
        $userName = $identity['name'] ?? '';

        $cleanFrom = $userName !== '' ? "\"{$userName}\" <{$userEmail}>" : $userEmail;
        $cleanTo = $senderEmail;

        $msgHeaders = [
            'From' => $cleanFrom,
            'To' => $cleanTo,
            'Subject' => $finalSubject,
            'Auto-Submitted' => 'auto-replied',
            'Precedence' => 'auto_reply',
            'X-Auto-Response-Suppress' => 'All',
        ];

        if ($originalMessageId !== '') {
            $msgHeaders['In-Reply-To'] = $originalMessageId;
            $msgHeaders['References'] = $originalMessageId;
        }

        $sent = $this->deliverMessage($cleanFrom, $cleanTo, $finalSubject, $finalBody, $msgHeaders);

        // 7. Record activity log
        $status = $sent ? 'sent' : 'failed';
        $details = $sent ? "Matched template [{$template['name']}] ({$matchReason})" : "Delivery error";
        $this->logActivity($userId, $senderEmail, $userEmail, $finalSubject, 'auto_reply', $status, $details, $originalMessageId, $template['name'] ?? 'default');

        return $sent;
    }

    /**
     * Sends message using Roundcube's transport or PHP mail.
     */
    public function deliverMessage(string $from, string $to, string $subject, string $body, array $headers): bool
    {
        $cleanSubject = preg_replace('/[\r\n]+/', ' ', trim($subject));
        $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($cleanSubject, 'UTF-8') : $cleanSubject;

        $headerLines = [];
        foreach ($headers as $k => $v) {
            if ($k === 'Subject' || $k === 'To') {
                continue;
            }
            $cleanV = preg_replace('/[\r\n]+/', ' ', trim((string)$v));
            $headerLines[] = "{$k}: {$cleanV}";
        }
        $headerLines[] = 'MIME-Version: 1.0';
        $headerLines[] = 'Content-Type: text/plain; charset=UTF-8';
        $headerLines[] = 'Content-Transfer-Encoding: 8bit';

        $rawHeaders = implode("\r\n", $headerLines) . "\r\n";

        // Try Roundcube delivery if available
        if (method_exists($this->rcmail, 'deliver_message')) {
            try {
                $error = null;
                $message = "Subject: {$encodedSubject}\r\n{$rawHeaders}\r\n{$body}";
                $res = $this->rcmail->deliver_message($message, $from, $to, $error);
                if ($res) {
                    return true;
                }
            } catch (\Throwable) {
                // Fallback to mail()
            }
        }

        return @mail($to, $encodedSubject, $body, $rawHeaders);
    }

    /**
     * Log activity to database.
     */
    public function logActivity(
        int $userId,
        string $sender,
        string $recipient,
        string $subject,
        string $actionType,
        string $status,
        string $details = '',
        string $messageId = '',
        string $templateUsed = 'default'
    ): void {
        $db = $this->rcmail->get_dbh();
        $this->ensureLogsTable($db);

        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO vacation_forward_logs (user_id, sender, recipient, subject, action_type, template_used, status, message_id, details, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $userId,
            $sender,
            $recipient,
            $subject,
            $actionType,
            $templateUsed,
            $status,
            $messageId,
            $details,
            $now
        );
    }

    /**
     * Ensure tracking table exists across MySQL, SQLite, and PostgreSQL.
     */
    public function ensureLogsTable($db): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $dbType = method_exists($db, 'get_type') ? $db->get_type() : 'mysql';
        $sqlFile = match ($dbType) {
            'sqlite' => dirname(__DIR__) . '/SQL/sqlite.sql',
            'postgres', 'pgsql' => dirname(__DIR__) . '/SQL/postgres.sql',
            default => dirname(__DIR__) . '/SQL/mysql.sql',
        };

        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $statements = array_filter(array_map('trim', explode(';', (string)$sql)));
            foreach ($statements as $stmt) {
                if ($stmt !== '') {
                    @$db->query($stmt);
                }
            }
        }
        $checked = true;
    }
}
