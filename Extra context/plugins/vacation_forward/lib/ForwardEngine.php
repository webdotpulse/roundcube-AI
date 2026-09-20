<?php

/**
 * Mail Forwarding & Redirection Engine
 *
 * Implements address parsing, condition filters, inline preamble vs redirect,
 * and delivery dispatch.
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

declare(strict_types=1);

class VacationForwardForwardEngine
{
    private rcmail $rcmail;

    public function __construct(?rcmail $rcmail = null)
    {
        $this->rcmail = $rcmail ?? rcmail::get_instance();
    }

    /**
     * Parse and sanitize destination email addresses from string.
     *
     * @return array List of valid destination emails
     */
    public static function parseDestinations(string $rawInput): array
    {
        $items = preg_split('/[\r\n,;]+/', $rawInput);
        $validEmails = [];

        foreach ($items as $item) {
            $addr = trim($item);
            if ($addr === '') {
                continue;
            }

            // Extract email from "Name <user@domain.com>" if present
            if (preg_match('/<([^>]+)>/', $addr, $m)) {
                $addr = trim($m[1]);
            }

            $clean = filter_var($addr, FILTER_VALIDATE_EMAIL);
            if ($clean !== false) {
                // Prevent carriage return injection
                $clean = preg_replace('/[\r\n]+/', '', (string)$clean);
                if (!in_array($clean, $validEmails, true)) {
                    $validEmails[] = $clean;
                }
            }
        }

        return $validEmails;
    }

    /**
     * Determines whether forwarding is currently active based on preferences and dates.
     */
    public function evaluateStatus(array $prefs): array
    {
        $status = $prefs['forward_status'] ?? 'disabled';

        if ($status === 'disabled') {
            return [false, 'disabled', 'Forwarding is disabled'];
        }

        if ($status === 'enabled') {
            return [true, 'active', 'Forwarding is permanently active'];
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
                return [false, 'disabled', 'Scheduled period dates missing'];
            }

            try {
                $startDate = new DateTime($startStr, $tz);
                $endDate = new DateTime($endStr, $tz);
            } catch (\Throwable) {
                return [false, 'disabled', 'Invalid schedule dates'];
            }

            if ($now < $startDate) {
                return [false, 'scheduled', 'Forwarding starts on ' . $startDate->format('Y-m-d H:i')];
            }

            if ($now > $endDate) {
                return [false, 'expired', 'Forwarding period ended on ' . $endDate->format('Y-m-d H:i')];
            }

            return [true, 'active', 'Forwarding active until ' . $endDate->format('Y-m-d H:i')];
        }

        return [false, 'disabled', 'Unknown forward status'];
    }

    /**
     * Checks if the message matches forwarding criteria.
     */
    public function matchesCondition(string $subject, string $senderEmail, array $prefs): bool
    {
        $condition = $prefs['forward_condition'] ?? 'all';

        if ($condition === 'all') {
            return true;
        }

        // Matching only mode: check subject keywords
        $keywordsRaw = $prefs['forward_keywords'] ?? '';
        if ($keywordsRaw !== '') {
            $keywords = array_filter(array_map('trim', explode(',', $keywordsRaw)));
            foreach ($keywords as $kw) {
                if ($kw !== '' && stripos($subject, $kw) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process forwarding for an incoming message.
     *
     * @return int Number of successfully delivered forward copies
     */
    public function processForward(
        int $userId,
        string $senderEmail,
        string $senderFromHeader,
        string $originalSubject,
        string $originalBody,
        string $originalMessageId,
        array $headers,
        array $prefs,
        array $identity
    ): int {
        [$isActive] = $this->evaluateStatus($prefs);
        if (!$isActive) {
            return 0;
        }

        $destinations = self::parseDestinations($prefs['forward_destinations'] ?? '');
        if (empty($destinations)) {
            return 0;
        }

        if (!$this->matchesCondition($originalSubject, $senderEmail, $prefs)) {
            return 0;
        }

        $mode = $prefs['forward_mode'] ?? 'inline';
        $userEmail = $identity['email'] ?? 'webmail@localhost';
        $userName = $identity['name'] ?? 'User';

        $forwardSubject = str_starts_with(strtoupper(trim($originalSubject)), 'FWD:') || str_starts_with(strtoupper(trim($originalSubject)), 'FW:')
            ? $originalSubject
            : 'Fwd: ' . $originalSubject;

        $sentCount = 0;

        foreach ($destinations as $destEmail) {
            // Never forward back to original sender to prevent loops
            if (strtolower($destEmail) === strtolower($senderEmail) || strtolower($destEmail) === strtolower($userEmail)) {
                continue;
            }

            if ($mode === 'redirect') {
                $fwdHeaders = [
                    'From' => $senderFromHeader,
                    'To' => $destEmail,
                    'Subject' => $originalSubject,
                    'Resent-From' => "\"{$userName}\" <{$userEmail}>",
                    'Resent-To' => $destEmail,
                    'Resent-Date' => date('r'),
                    'X-Forwarded-For' => $userEmail,
                ];
                $fwdBody = $originalBody;
            } else {
                // Inline mode
                $dateStr = date('r');
                $preamble = "---------- Forwarded message ---------\n" .
                            "From: {$senderFromHeader}\n" .
                            "Date: {$dateStr}\n" .
                            "Subject: {$originalSubject}\n" .
                            "To: {$userEmail}\n\n";

                $fwdHeaders = [
                    'From' => "\"{$userName}\" <{$userEmail}>",
                    'To' => $destEmail,
                    'Subject' => $forwardSubject,
                    'X-Forwarded-By' => $userEmail,
                ];
                $fwdBody = $preamble . $originalBody;
            }

            if ($originalMessageId !== '') {
                $fwdHeaders['References'] = $originalMessageId;
            }

            $success = $this->deliverForward($userEmail, $destEmail, $fwdHeaders['Subject'], $fwdBody, $fwdHeaders);

            $status = $success ? 'sent' : 'failed';
            $this->logActivity($userId, $senderEmail, $destEmail, $fwdHeaders['Subject'], 'forward', $status, "Forward mode: {$mode}", $originalMessageId);

            if ($success) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Deliver forward email.
     */
    private function deliverForward(string $from, string $to, string $subject, string $body, array $headers): bool
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

        if (method_exists($this->rcmail, 'deliver_message')) {
            try {
                $error = null;
                $message = "Subject: {$encodedSubject}\r\n{$rawHeaders}\r\n{$body}";
                $res = $this->rcmail->deliver_message($message, $from, $to, $error);
                if ($res) {
                    return true;
                }
            } catch (\Throwable) {
            }
        }

        return @mail($to, $encodedSubject, $body, $rawHeaders);
    }

    /**
     * Log forward activity to database.
     */
    private function logActivity(
        int $userId,
        string $sender,
        string $recipient,
        string $subject,
        string $actionType,
        string $status,
        string $details = '',
        string $messageId = ''
    ): void {
        $db = $this->rcmail->get_dbh();
        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO vacation_forward_logs (user_id, sender, recipient, subject, action_type, template_used, status, message_id, details, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $userId,
            $sender,
            $recipient,
            $subject,
            $actionType,
            'forward',
            $status,
            $messageId,
            $details,
            $now
        );
    }
}
