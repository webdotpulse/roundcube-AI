#!/usr/bin/env php
<?php

/**
 * Gemini Executive Assistant (FYXER Mode) — 24/7 CLI Background Worker
 *
 * Runs autonomously in the background (via cron or systemd daemon) to monitor
 * incoming emails, run Gemini 3.8 Flash triage, and prepare draft replies in the
 * user's IMAP Drafts folder even when users are completely logged out of Roundcube.
 *
 * Usage:
 *   php bin/worker.php                   # Single run pass (ideal for cron)
 *   php bin/worker.php --daemon          # Continuous 24/7 background daemon
 *   php bin/worker.php --interval=60     # Daemon loop every 60 seconds
 *   php bin/worker.php --dry-run         # Test triage & print replies to stdout
 *   php bin/worker.php --account=user    # Process single account only
 *   php bin/worker.php --help            # View help and instructions
 *
 * @license MIT
 * @author LifePrisma & Contributors
 */

// Strict CLI only
if (php_sapi_name() !== 'cli') {
    die("Error: This script can only be run from the command line.\n");
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');
date_default_timezone_set('UTC');

// ============================================================
// CLI Arguments Parsing
// ============================================================
$options = getopt('hvd', [
    'help',
    'verbose',
    'daemon',
    'once',
    'dry-run',
    'interval:',
    'account:',
    'config:',
]);

if (isset($options['h']) || isset($options['help'])) {
    echo <<<HELP
Gemini Executive Assistant — 24/7 Background Worker (FYXER Mode)
=================================================================

Monitors mailboxes for unread emails, runs Google Gemini 3.8 Flash triage,
and automatically writes pre-crafted replies to the IMAP Drafts folder.

OPTIONS:
  -h, --help           Show this help message and exit
  -v, --verbose        Enable verbose debug output
  -d, --daemon         Run continuously in background (daemon mode)
      --interval=SEC   Seconds to sleep between checks in daemon mode (default: 60)
      --once           Run a single pass and exit (default if not daemon)
      --dry-run        Triage and generate replies to console without modifying IMAP
      --account=EMAIL  Process only the specified email account
      --config=PATH    Specify custom path to config.inc.php

EXAMPLES:
  # Run once via cron (every 3 minutes):
  */3 * * * * php /path/to/roundcube/plugins/lifeprisma_ai/bin/worker.php >> /var/log/lifeprisma_ai_worker.log 2>&1

  # Run as 24/7 background daemon with 30-second interval:
  php bin/worker.php --daemon --interval=30

  # Test triage on an account without writing drafts:
  php bin/worker.php --account=ceo@example.com --dry-run --verbose

HELP;
    exit(0);
}

$is_daemon = isset($options['d']) || isset($options['daemon']);
$is_dry_run = isset($options['dry-run']);
$is_verbose = isset($options['v']) || isset($options['verbose']);
$poll_interval = isset($options['interval']) ? max(5, (int) $options['interval']) : 60;
$target_account = $options['account'] ?? null;
$custom_config = $options['config'] ?? null;

// Graceful signal handling for daemon mode
$keep_running = true;
if (function_exists('pcntl_signal')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM, function() use (&$keep_running) {
        echo "\n[INFO] Caught SIGTERM, shutting down gracefully...\n";
        $keep_running = false;
    });
    pcntl_signal(SIGINT, function() use (&$keep_running) {
        echo "\n[INFO] Caught SIGINT (Ctrl+C), shutting down...\n";
        $keep_running = false;
    });
}

// ============================================================
// Configuration Loader
// ============================================================
function lpai_worker_load_config($custom_config = null) {
    $config = [];

    // Search candidate paths
    $candidates = [];
    if ($custom_config && file_exists($custom_config)) {
        $candidates[] = $custom_config;
    }
    $candidates[] = dirname(__DIR__) . '/config.inc.php';
    $candidates[] = dirname(__DIR__) . '/config.inc.php.dist';
    $candidates[] = dirname(__DIR__, 3) . '/config/config.inc.php';

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require $file;
            break;
        }
    }

    // Default fallbacks
    $config['lifeprisma_ai_gemini_api_key'] = $config['lifeprisma_ai_gemini_api_key'] ?? getenv('GEMINI_API_KEY') ?: '';
    $config['lifeprisma_ai_gemini_model'] = $config['lifeprisma_ai_gemini_model'] ?? 'gemini-3.8-flash';
    $config['lifeprisma_ai_api_url'] = $config['lifeprisma_ai_api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';
    $config['lifeprisma_ai_auto_draft_filter'] = $config['lifeprisma_ai_auto_draft_filter'] ?? true;
    $config['lifeprisma_ai_default_language'] = $config['lifeprisma_ai_default_language'] ?? 'English';
    $config['lifeprisma_ai_default_tone'] = $config['lifeprisma_ai_default_tone'] ?? 'professional';
    $config['lifeprisma_ai_worker_imap_host'] = $config['lifeprisma_ai_worker_imap_host'] ?? 'ssl://localhost:993';
    $config['lifeprisma_ai_worker_accounts'] = $config['lifeprisma_ai_worker_accounts'] ?? [];

    return $config;
}

// ============================================================
// State & Deduplication Manager
// ============================================================
class LpaiWorkerState {
    private $filepath;
    private $state = [];

    public function __construct($filepath) {
        $this->filepath = $filepath;
        $this->load();
    }

    public function load() {
        if (file_exists($this->filepath)) {
            $data = json_decode(file_get_contents($this->filepath), true);
            if (is_array($data)) {
                $this->state = $data;
            }
        }
    }

    public function save() {
        // Purge entries older than 7 days
        $cutoff = time() - (86400 * 7);
        $filtered = [];
        foreach ($this->state as $k => $entry) {
            if (($entry['time'] ?? 0) > $cutoff) {
                $filtered[$k] = $entry;
            }
        }
        // Cap to 1000 items
        if (count($filtered) > 1000) {
            $filtered = array_slice($filtered, -800, null, true);
        }
        $this->state = $filtered;
        file_put_contents($this->filepath, json_encode($this->state, JSON_PRETTY_PRINT));
    }

    public function is_processed($account, $mbox, $uid) {
        $key = "{$account}:{$mbox}:{$uid}";
        return isset($this->state[$key]);
    }

    public function mark_processed($account, $mbox, $uid, $status, $extra = []) {
        $key = "{$account}:{$mbox}:{$uid}";
        $this->state[$key] = array_merge([
            'status' => $status,
            'time' => time(),
        ], $extra);
        $this->save();
    }
}

// ============================================================
// Lightweight Standalone Socket IMAP Client
// ============================================================
class LpaiImapClient {
    private $socket = null;
    private $tag_count = 0;
    private $verbose = false;

    public function __construct($verbose = false) {
        $this->verbose = $verbose;
    }

    public function connect($host_uri, $timeout = 20) {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ]);

        $this->socket = @stream_socket_client($host_uri, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$this->socket) {
            throw new Exception("Cannot connect to IMAP server {$host_uri}: [{$errno}] {$errstr}");
        }

        stream_set_timeout($this->socket, $timeout);
        $greeting = $this->read_line();
        if ($this->verbose) echo "[IMAP-RAW] Greeting: $greeting\n";
        return true;
    }

    public function login($username, $password) {
        $response = $this->send_command("LOGIN " . $this->escape($username) . " " . $this->escape($password));
        return $this->is_ok($response);
    }

    public function select($folder = 'INBOX') {
        $response = $this->send_command("SELECT " . $this->escape($folder));
        return $this->is_ok($response);
    }

    public function search_unseen() {
        $response = $this->send_command("UID SEARCH UNSEEN");
        $uids = [];
        foreach ($response as $line) {
            if (preg_match('/^\*\s+SEARCH\s+(.*)$/i', trim($line), $m)) {
                $parts = preg_split('/\s+/', trim($m[1]));
                foreach ($parts as $p) {
                    if (is_numeric($p) && (int)$p > 0) {
                        $uids[] = (int)$p;
                    }
                }
            }
        }
        return $uids;
    }

    public function fetch_message($uid) {
        $response = $this->send_command("UID FETCH $uid (RFC822)");
        $full_msg = '';
        $capturing = false;
        $literal_bytes_left = 0;

        foreach ($response as $line) {
            if (preg_match('/\* \d+ FETCH \(UID ' . $uid . '.*\{(\d+)\}/i', $line, $m)) {
                $capturing = true;
                $literal_bytes_left = (int)$m[1];
                continue;
            }
            if ($capturing) {
                if ($literal_bytes_left > 0) {
                    $full_msg .= $line . "\n";
                    $literal_bytes_left -= (strlen($line) + 1);
                }
                if ($literal_bytes_left <= 0) {
                    $capturing = false;
                }
            }
        }

        if (empty($full_msg)) {
            // Alternative fetch RFC822.HEADER and BODY[TEXT]
            $h_res = $this->send_command("UID FETCH $uid (RFC822.HEADER)");
            $full_msg = implode("\n", $h_res);
        }

        return $this->parse_raw_email($full_msg);
    }

    public function append_draft($folder, $raw_email) {
        $tag = $this->get_next_tag();
        $len = strlen($raw_email);
        $cmd = "$tag APPEND " . $this->escape($folder) . " (\\Draft) {{$len}}\r\n";
        
        if ($this->verbose) echo "[IMAP-OUT] $cmd";
        fwrite($this->socket, $cmd);

        $line = $this->read_line();
        if ($this->verbose) echo "[IMAP-IN] $line\n";

        if (strpos($line, '+') !== 0) {
            return false;
        }

        // Send payload and trailing CRLF
        fwrite($this->socket, $raw_email . "\r\n");
        $final_response = $this->read_response_until($tag);
        return $this->is_ok($final_response);
    }

    public function close() {
        if ($this->socket) {
            try {
                $this->send_command("LOGOUT");
            } catch (Exception $e) {}
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    private function parse_raw_email($raw) {
        $parts = explode("\r\n\r\n", str_replace(["\r\n", "\r"], ["\n", "\n"], $raw), 2);
        if (count($parts) < 2) {
            $parts = explode("\n\n", $raw, 2);
        }
        $header_str = $parts[0] ?? '';
        $body_str = $parts[1] ?? '';

        $headers = [];
        $header_lines = explode("\n", $header_str);
        $current_key = '';

        foreach ($header_lines as $hline) {
            if (preg_match('/^([A-Za-z0-9-]+):\s*(.*)$/', $hline, $m)) {
                $current_key = strtolower($m[1]);
                $headers[$current_key] = trim($m[2]);
            } elseif ($current_key && (strpos($hline, ' ') === 0 || strpos($hline, "\t") === 0)) {
                $headers[$current_key] .= ' ' . trim($hline);
            }
        }

        // Decode subject
        $subject = $headers['subject'] ?? '(No Subject)';
        if (function_exists('mb_decode_mimeheader')) {
            $subject = mb_decode_mimeheader($subject);
        }

        // Clean plain body text (extract from multipart if needed)
        $clean_body = $body_str;
        if (preg_match('/Content-Type:\s*multipart\/[a-z]+;\s*boundary="?([^"\s;]+)"?/i', $header_str, $bm)) {
            $boundary = '--' . $bm[1];
            $sections = explode($boundary, $body_str);
            foreach ($sections as $sec) {
                if (stripos($sec, 'Content-Type: text/plain') !== false) {
                    $sec_parts = explode("\n\n", trim($sec), 2);
                    $clean_body = $sec_parts[1] ?? $clean_body;
                    break;
                }
            }
        }

        // Strip HTML if body is HTML
        if (stripos($headers['content-type'] ?? '', 'text/html') !== false) {
            $clean_body = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean_body));
        }

        return [
            'headers' => $headers,
            'subject' => $subject,
            'from' => $headers['from'] ?? '',
            'to' => $headers['to'] ?? '',
            'date' => $headers['date'] ?? date('r'),
            'message_id' => $headers['message-id'] ?? '',
            'raw_headers' => $header_str,
            'body' => trim($clean_body),
        ];
    }

    private function escape($str) {
        return '"' . addcslashes($str, '\\"') . '"';
    }

    private function get_next_tag() {
        $this->tag_count++;
        return sprintf("A%04d", $this->tag_count);
    }

    private function send_command($cmd) {
        $tag = $this->get_next_tag();
        $line = "$tag $cmd\r\n";
        if ($this->verbose) echo "[IMAP-OUT] $line";
        fwrite($this->socket, $line);
        return $this->read_response_until($tag);
    }

    private function read_response_until($tag) {
        $lines = [];
        while ($line = $this->read_line()) {
            if ($this->verbose) echo "[IMAP-IN] $line\n";
            $lines[] = $line;
            if (strpos($line, "$tag OK") === 0 || strpos($line, "$tag NO") === 0 || strpos($line, "$tag BAD") === 0) {
                break;
            }
        }
        return $lines;
    }

    private function read_line() {
        if (!$this->socket || feof($this->socket)) return false;
        $line = fgets($this->socket, 8192);
        return ($line !== false) ? rtrim($line, "\r\n") : false;
    }

    private function is_ok($response) {
        if (empty($response)) return false;
        $last = end($response);
        return (strpos($last, ' OK') !== false);
    }
}

// ============================================================
// Google Gemini API Caller
// ============================================================
function lpai_worker_call_gemini($system_prompt, $user_prompt, $config, $verbose = false) {
    $api_key = $config['lifeprisma_ai_gemini_api_key'];
    $model = $config['lifeprisma_ai_gemini_model'] ?? 'gemini-3.8-flash';
    $api_url = $config['lifeprisma_ai_api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';

    if (empty($api_key)) {
        echo "[ERROR] Google Gemini API Key is missing. Set 'lifeprisma_ai_gemini_api_key' in config.inc.php\n";
        return false;
    }

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
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        echo "[ERROR] Gemini cURL failure: $curl_err\n";
        return false;
    }

    if ($http_code !== 200) {
        echo "[ERROR] Gemini returned HTTP $http_code: $response\n";
        return false;
    }

    $data = json_decode($response, true);
    return trim($data['choices'][0]['message']['content'] ?? '');
}

// ============================================================
// Draft Generator & RFC 2822 Formatter
// ============================================================
function lpai_worker_format_draft_message($to, $subject, $reply_body, $orig_msg_id, $orig_date, $orig_from, $orig_body, $my_email) {
    $boundary = '=_lpai_draft_' . md5(uniqid(microtime(), true));
    $date = date('r');
    $re_subject = (stripos($subject, 'Re:') === 0) ? $subject : 'Re: ' . $subject;

    // Quote original message body
    $quoted = '';
    if (!empty($orig_body)) {
        $quoted = "\n\nOn " . ($orig_date ?: 'recently') . ", " . ($orig_from ?: 'the sender') . " wrote:\n";
        $lines = explode("\n", trim($orig_body));
        foreach (array_slice($lines, 0, 80) as $line) {
            $quoted .= '> ' . $line . "\n";
        }
    }

    $full_body = $reply_body . $quoted;

    $headers = [];
    $headers[] = "Date: $date";
    $headers[] = "From: <$my_email>";
    $headers[] = "To: $to";
    $headers[] = "Subject: $re_subject";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=utf-8";
    $headers[] = "Content-Transfer-Encoding: 8bit";
    $headers[] = "X-Unsent: 1";
    $headers[] = "X-Mailer: Gemini Executive Assistant (FYXER Mode 24/7)";

    if (!empty($orig_msg_id)) {
        $headers[] = "In-Reply-To: $orig_msg_id";
        $headers[] = "References: $orig_msg_id";
    }

    return implode("\r\n", $headers) . "\r\n\r\n" . $full_body;
}

// ============================================================
// Core Execution Pass
// ============================================================
function lpai_worker_execute_pass($config, LpaiWorkerState $state, $target_account = null, $is_dry_run = false, $is_verbose = false) {
    $accounts = $config['lifeprisma_ai_worker_accounts'] ?? [];
    if (empty($accounts)) {
        echo "[WARN] No accounts configured in 'lifeprisma_ai_worker_accounts'. Edit config.inc.php to specify accounts.\n";
        return;
    }

    $imap_host = $config['lifeprisma_ai_worker_imap_host'] ?? 'ssl://localhost:993';
    $model = $config['lifeprisma_ai_gemini_model'] ?? 'gemini-3.8-flash';

    foreach ($accounts as $acc) {
        $email = $acc['username'] ?? ($acc['email'] ?? '');
        if (!$email) continue;
        if ($target_account && stripos($email, $target_account) === false) continue;

        $password = $acc['password'] ?? '';
        $host = $acc['imap_host'] ?? $imap_host;
        $drafts_folder = $acc['drafts_folder'] ?? 'Drafts';

        // Master User Support (Dovecot master user: login as user*masteruser with masterpass)
        if (!empty($config['lifeprisma_ai_worker_dovecot_master_user']) && !empty($config['lifeprisma_ai_worker_dovecot_master_password'])) {
            $master_user = $config['lifeprisma_ai_worker_dovecot_master_user'];
            $master_pass = $config['lifeprisma_ai_worker_dovecot_master_password'];
            $login_user = "{$email}*{$master_user}";
            $login_pass = $master_pass;
        } else {
            $login_user = $email;
            $login_pass = $password;
        }

        if (empty($login_pass)) {
            echo "[WARN] No password or master user configured for account: $email. Skipping.\n";
            continue;
        }

        $now = date('Y-m-d H:i:s');
        echo "[$now] [INFO] Processing account: $email on $host\n";

        $client = new LpaiImapClient($is_verbose);
        try {
            $client->connect($host);
            if (!$client->login($login_user, $login_pass)) {
                echo "[$now] [ERROR] IMAP login failed for account: $email\n";
                $client->close();
                continue;
            }

            if (!$client->select('INBOX')) {
                echo "[$now] [ERROR] Failed to select INBOX for account: $email\n";
                $client->close();
                continue;
            }

            $unseen_uids = $client->search_unseen();
            $count = count($unseen_uids);
            echo "[$now] [INFO] Account: $email — Found $count unread email(s)\n";

            if ($count === 0) {
                $client->close();
                continue;
            }

            // Process most recent 3 unread messages
            $to_process = array_slice(array_reverse($unseen_uids), 0, 3);

            foreach ($to_process as $uid) {
                if ($state->is_processed($email, 'INBOX', $uid)) {
                    if ($is_verbose) echo "[$now] [DEBUG] UID $uid already processed. Skipping.\n";
                    continue;
                }

                $msg = $client->fetch_message($uid);
                if (empty($msg['body'])) {
                    if ($is_verbose) echo "[$now] [DEBUG] UID $uid has empty body. Skipping.\n";
                    $state->mark_processed($email, 'INBOX', $uid, 'empty_body');
                    continue;
                }

                $from = $msg['from'];
                $subject = $msg['subject'];
                $body = $msg['body'];

                // 1. Bulk / Automated Filter
                if (preg_match('/\b(List-Unsubscribe|List-Id|Precedence:\s*(bulk|list|junk)|Auto-Submitted:\s*auto)/i', $msg['raw_headers'])) {
                    echo "[$now] [SKIP] UID $uid '$subject' — Filtered out (Bulk / Automated notification)\n";
                    $state->mark_processed($email, 'INBOX', $uid, 'skipped_bulk');
                    continue;
                }

                // 2. Self-sent filter
                if (stripos($from, $email) !== false) {
                    $state->mark_processed($email, 'INBOX', $uid, 'skipped_self');
                    continue;
                }

                // 3. Actionable / Smart filter
                if (!empty($config['lifeprisma_ai_auto_draft_filter'])) {
                    $text = $subject . ' ' . $body;
                    $has_action = (strpos($text, '?') !== false) ||
                        preg_match('/\b(please|could you|can you|let me know|what do you think|confirm|feedback|reply|respond|waiting for|deadline|meeting|schedule|availability|asap)\b/i', $text);
                    if (!$has_action) {
                        echo "[$now] [SKIP] UID $uid '$subject' — Informational only (no action or questions detected)\n";
                        $state->mark_processed($email, 'INBOX', $uid, 'skipped_no_action');
                        continue;
                    }
                }

                // 4. Generate Draft Reply with Gemini
                echo "[$now] [AI] Generating Gemini ($model) draft reply for: '$subject' from $from...\n";

                $language = $config['lifeprisma_ai_default_language'] ?? 'English';
                $tone = $config['lifeprisma_ai_default_tone'] ?? 'professional';

                $system_prompt = "You are an elite, discreet Executive AI Assistant modeled after FYXER. " .
                    "Your role is to draft an exceptional, highly contextual executive email response. " .
                    "Maintain the user's authentic tone ($tone) in $language. " .
                    "Directly address all questions and action items. Do not include placeholders like [Your Name].";

                $user_prompt = "Original Email Subject: $subject\n" .
                    "From: $from\n\n" .
                    "Original Email Body:\n" . substr($body, 0, 3500) . "\n\n" .
                    "Draft an executive response ready to send.";

                $draft_reply = lpai_worker_call_gemini($system_prompt, $user_prompt, $config, $is_verbose);

                if (empty($draft_reply)) {
                    echo "[$now] [ERROR] Gemini failed to generate reply for UID $uid\n";
                    continue;
                }

                if ($is_dry_run) {
                    echo "\n================= [DRY-RUN DRAFT] =================\n";
                    echo "To: $from\nSubject: Re: $subject\n\n";
                    echo $draft_reply . "\n";
                    echo "===================================================\n\n";
                    $state->mark_processed($email, 'INBOX', $uid, 'dry_run_success', ['subject' => $subject]);
                } else {
                    $raw_draft = lpai_worker_format_draft_message(
                        $from,
                        $subject,
                        $draft_reply,
                        $msg['message_id'],
                        $msg['date'],
                        $from,
                        $body,
                        $email
                    );

                    $saved = $client->append_draft($drafts_folder, $raw_draft);
                    if ($saved) {
                        echo "[$now] [SUCCESS] Pre-crafted Gemini draft saved to '$drafts_folder' for: '$subject'\n";
                        $state->mark_processed($email, 'INBOX', $uid, 'draft_created', ['subject' => $subject]);
                    } else {
                        echo "[$now] [ERROR] Failed to save draft into '$drafts_folder' for UID $uid\n";
                    }
                }
            }

            $client->close();
        } catch (Exception $e) {
            echo "[$now] [ERROR] Exception processing account $email: " . $e->getMessage() . "\n";
            $client->close();
        }
    }
}

// ============================================================
// Main Execution Loop
// ============================================================
$state_file = __DIR__ . '/.worker_state.json';
$state = new LpaiWorkerState($state_file);
$config = lpai_worker_load_config($custom_config);

$model = $config['lifeprisma_ai_gemini_model'] ?? 'gemini-3.8-flash';
echo "===========================================================\n";
echo "Gemini Executive Assistant — 24/7 CLI Background Worker\n";
echo "Model: $model | Mode: " . ($is_daemon ? "Daemon (interval: {$poll_interval}s)" : "Single Pass") . "\n";
if ($is_dry_run) echo "DRY RUN MODE ENABLED — No changes will be written to IMAP\n";
echo "===========================================================\n";

if ($is_daemon) {
    while ($keep_running) {
        lpai_worker_execute_pass($config, $state, $target_account, $is_dry_run, $is_verbose);
        for ($i = 0; $i < $poll_interval && $keep_running; $i++) {
            sleep(1);
        }
    }
    echo "[INFO] Daemon stopped gracefully.\n";
} else {
    lpai_worker_execute_pass($config, $state, $target_account, $is_dry_run, $is_verbose);
    echo "[INFO] Worker pass finished.\n";
}
