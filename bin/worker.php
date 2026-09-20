#!/usr/bin/env php
<?php

/**
 * Gemini Executive Assistant — 24/7 CLI Background Worker (Autonomous Mode)
 *
 * Runs autonomously in the background (via cron or systemd daemon) to monitor
 * incoming emails, run Gemini 3.8 Flash triage, assign labels (roundcube-labels),
 * and prepare draft replies in the user's IMAP Drafts folder with learned answer replication.
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
    'reset-state',
    'interval:',
    'account:',
    'config:',
]);

if (isset($options['h']) || isset($options['help'])) {
    echo <<<HELP
Gemini Executive Assistant — 24/7 Background Worker (Autonomous Mode)
=================================================================

Monitors mailboxes for unread emails, runs Google Gemini 3.8 Flash triage,
applies organizational labels, and writes pre-crafted replies to the IMAP Drafts folder.

OPTIONS:
  -h, --help           Show this help message and exit
  -v, --verbose        Enable verbose debug output
  -d, --daemon         Run continuously in background (daemon mode)
      --interval=SEC   Seconds to sleep between checks in daemon mode (default: 60)
      --once           Run a single pass and exit (default if not daemon)
      --dry-run        Triage and generate replies to console without modifying IMAP
      --reset-state    Clear processed email history (.worker_state.json) and re-check all unseen emails
      --account=EMAIL  Process only the specified email account
      --config=PATH    Specify custom path to config.inc.php

EXAMPLES:
  # Run once via cron (every 3 minutes):
  */3 * * * * php /path/to/roundcube/plugins/lifeprisma_ai/bin/worker.php >> /var/log/lifeprisma_ai_worker.log 2>&1

  # Run as 24/7 background daemon with 30-second interval:
  php bin/worker.php --daemon --interval=30

  # Test triage on an account without writing drafts:
  php bin/worker.php --account=koen@thechargegrid.com --dry-run --verbose

  # Reset processed state and re-triage unread emails:
  php bin/worker.php --reset-state --dry-run --verbose

HELP;
    exit(0);
}

$is_daemon = isset($options['d']) || isset($options['daemon']);
$is_dry_run = isset($options['dry-run']);
$is_verbose = isset($options['v']) || isset($options['verbose']);
$is_reset_state = isset($options['reset-state']);
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

    // Search candidate paths in strict priority order:
    // 1. Custom CLI path (--config=...)
    // 2. Plugin config (plugins/lifeprisma_ai/config.inc.php)
    // 3. Roundcube root config (config/config.inc.php)
    // 4. Distribution template fallback (config.inc.php.dist)
    $candidates = [];
    if ($custom_config && file_exists($custom_config)) {
        $candidates[] = $custom_config;
    }
    $candidates[] = dirname(__DIR__) . '/config.inc.php';
    $candidates[] = dirname(__DIR__, 3) . '/config/config.inc.php';
    $candidates[] = dirname(__DIR__) . '/config.inc.php.dist';

    $loaded_from = null;
    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require $file;
            $loaded_from = $file;
            if (!empty($config['lifeprisma_ai_worker_accounts'])) {
                break;
            }
        }
    }

    if ($loaded_from) {
        $config['_loaded_from'] = $loaded_from;
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
// AI Learned Memory Loader
// ============================================================
function lpai_worker_load_memory($config, $account_id = null) {
    $candidates = [];
    if (!empty($account_id)) {
        $user_hash = md5("lpai_user_mem_" . $account_id);
        $candidates[] = dirname(__DIR__) . '/data/memory/user_' . $user_hash . '.json';
    }
    $candidates[] = dirname(__DIR__) . '/data/ai_memory.json';
    $candidates[] = dirname(__DIR__) . '/.ai_memory.json';

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            $data = json_decode($content, true);
            if (is_array($data)) return $data;
        }
    }
    return [];
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

    public function reset() {
        $this->state = [];
        if (file_exists($this->filepath)) {
            @unlink($this->filepath);
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
        file_put_contents($this->filepath, json_encode($this->state, JSON_PRETTY_PRINT), LOCK_EX);
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

    public function connect($host_uri, $timeout = 20, $ssl_verify = true) {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => (bool) $ssl_verify,
                'verify_peer_name' => (bool) $ssl_verify,
                'allow_self_signed' => !$ssl_verify,
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
        $tag = $this->get_next_tag();
        $cmd = "$tag UID FETCH $uid (BODY.PEEK[])\r\n";
        if ($this->verbose) echo "[IMAP-OUT] $cmd";
        fwrite($this->socket, $cmd);

        $full_msg = '';

        while (($line = $this->read_line()) !== false) {
            if ($this->verbose) echo "[IMAP-IN] $line\n";

            // Detect IMAP literal specification: * <num> FETCH (UID <uid> ... {<size>}
            if (preg_match('/\{(\d+)\}\s*$/', $line, $m)) {
                $literal_size = (int)$m[1];
                if ($literal_size > 0) {
                    $read_bytes = 0;
                    while ($read_bytes < $literal_size && !feof($this->socket)) {
                        $chunk = fread($this->socket, min(8192, $literal_size - $read_bytes));
                        if ($chunk === false || strlen($chunk) === 0) break;
                        $full_msg .= $chunk;
                        $read_bytes += strlen($chunk);
                    }
                    if ($this->verbose) echo "[IMAP-RAW] Read $read_bytes / $literal_size literal bytes\n";
                }
            }

            if (strpos($line, "$tag OK") === 0 || strpos($line, "$tag NO") === 0 || strpos($line, "$tag BAD") === 0) {
                break;
            }
        }

        if (empty($full_msg)) {
            // Alternative non-destructive fetch of headers
            $h_res = $this->send_command("UID FETCH $uid (BODY.PEEK[HEADER])");
            $full_msg = implode("\n", $h_res);
        }

        return $this->parse_raw_email($full_msg);
    }

    public function list_folders() {
        $response = $this->send_command('LIST "" "*"');
        $folders = [];
        foreach ($response as $line) {
            if (preg_match('/^\*\s+LIST\s+\(([^)]*)\)\s+(?:"[^"]*"|nil|\S+)\s+(.+)$/i', trim($line), $m)) {
                $raw_flags = preg_split('/\s+/', trim($m[1]));
                $name = trim($m[2]);
                if (strlen($name) >= 2 && $name[0] === '"' && substr($name, -1) === '"') {
                    $name = substr($name, 1, -1);
                }
                $folders[] = [
                    'name' => $name,
                    'flags' => $raw_flags,
                ];
            }
        }
        return $folders;
    }

    public function resolve_drafts_folder($preferred = 'Drafts') {
        $folders = $this->list_folders();
        if (empty($folders)) {
            return $preferred;
        }

        // 1. Check for SPECIAL-USE \Drafts attribute
        foreach ($folders as $f) {
            foreach ($f['flags'] as $flag) {
                if (strcasecmp($flag, '\\Drafts') === 0) {
                    return $f['name'];
                }
            }
        }

        // 2. Exact match for preferred
        foreach ($folders as $f) {
            if (strcasecmp($f['name'], $preferred) === 0) {
                return $f['name'];
            }
        }

        // 3. Fallback to common candidates
        $candidates = ['Drafts', 'INBOX.Drafts', 'INBOX/Drafts', 'Concepten', 'INBOX.Concepten', 'Brouillons'];
        foreach ($candidates as $cand) {
            foreach ($folders as $f) {
                if (strcasecmp($f['name'], $cand) === 0) {
                    return $f['name'];
                }
            }
        }

        return $preferred;
    }

    public function append_draft($folder, $raw_email) {
        $tag = $this->get_next_tag();
        $len = strlen($raw_email);
        $cmd = "$tag APPEND " . $this->escape($folder) . " (\\Draft \\Seen) {{$len}}\r\n";
        
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

    public function add_flags($uid, $flags) {
        if (empty($flags)) return false;
        $response = $this->send_command("UID STORE $uid +FLAGS ($flags)");
        return $this->is_ok($response);
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
        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
        $parts = explode("\n\n", $normalized, 2);
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
            $found_plain = false;
            foreach ($sections as $sec) {
                if (stripos($sec, 'Content-Type: text/plain') !== false) {
                    $sec_norm = str_replace(["\r\n", "\r"], ["\n", "\n"], trim($sec));
                    $sec_parts = explode("\n\n", $sec_norm, 2);
                    $part_headers = strtolower($sec_parts[0] ?? '');
                    $part_body = $sec_parts[1] ?? '';
                    if (strpos($part_headers, 'content-transfer-encoding: base64') !== false) {
                        $part_body = base64_decode($part_body);
                    } elseif (strpos($part_headers, 'content-transfer-encoding: quoted-printable') !== false) {
                        $part_body = quoted_printable_decode($part_body);
                    }
                    $clean_body = $part_body;
                    $found_plain = true;
                    break;
                }
            }
            if (!$found_plain) {
                // Fallback to text/html section if text/plain not present
                foreach ($sections as $sec) {
                    if (stripos($sec, 'Content-Type: text/html') !== false) {
                        $sec_norm = str_replace(["\r\n", "\r"], ["\n", "\n"], trim($sec));
                        $sec_parts = explode("\n\n", $sec_norm, 2);
                        $part_headers = strtolower($sec_parts[0] ?? '');
                        $part_body = $sec_parts[1] ?? '';
                        if (strpos($part_headers, 'content-transfer-encoding: base64') !== false) {
                            $part_body = base64_decode($part_body);
                        } elseif (strpos($part_headers, 'content-transfer-encoding: quoted-printable') !== false) {
                            $part_body = quoted_printable_decode($part_body);
                        }
                        $clean_body = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $part_body));
                        break;
                    }
                }
            }
        } else {
            // Single part email - decode if encoded
            $encoding = strtolower($headers['content-transfer-encoding'] ?? '');
            if ($encoding === 'base64') {
                $clean_body = base64_decode($clean_body);
            } elseif ($encoding === 'quoted-printable') {
                $clean_body = quoted_printable_decode($clean_body);
            }
            // Strip HTML if single-part is HTML
            if (stripos($headers['content-type'] ?? '', 'text/html') !== false) {
                $clean_body = strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean_body));
            }
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
        while (($line = $this->read_line()) !== false) {
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

    // Sanitize headers against CRLF injection
    $clean_to = preg_replace('/[\r\n]+/', ' ', trim($to));
    $clean_from = preg_replace('/[\r\n]+/', ' ', trim($my_email));
    $clean_subj = preg_replace('/[\r\n]+/', ' ', trim($re_subject));
    $encoded_subj = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($clean_subj, 'UTF-8') : $clean_subj;

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
    $headers[] = "From: <$clean_from>";
    $headers[] = "To: $clean_to";
    $headers[] = "Subject: $encoded_subj";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=utf-8";
    $headers[] = "Content-Transfer-Encoding: 8bit";
    $headers[] = "X-Unsent: 1";
    $headers[] = "X-Mailer: Gemini Executive Assistant (Autonomous 24/7)";

    if (!empty($orig_msg_id)) {
        $clean_msg_id = preg_replace('/[\r\n]+/', '', trim($orig_msg_id));
        $headers[] = "In-Reply-To: $clean_msg_id";
        $headers[] = "References: $clean_msg_id";
    }

    return implode("\r\n", $headers) . "\r\n\r\n" . $full_body;
}

// ============================================================
// Core Execution Pass
// ============================================================
function lpai_worker_execute_pass($config, LpaiWorkerState $state, $target_account = null, $is_dry_run = false, $is_verbose = false) {
    $accounts = $config['lifeprisma_ai_worker_accounts'] ?? [];
    if (empty($accounts)) {
        echo "[WARN] No accounts configured in 'lifeprisma_ai_worker_accounts'.\n";
        echo "       Please edit " . ($config['_loaded_from'] ?? 'config.inc.php') . " to configure your account credentials:\n";
        echo "       \$config['lifeprisma_ai_worker_accounts'] = [\n";
        echo "           ['email' => 'koen@thechargegrid.com', 'password' => 'YOUR_PASSWORD', 'drafts_folder' => 'Drafts'],\n";
        echo "       ];\n";
        return;
    }

    $imap_host = $config['lifeprisma_ai_worker_imap_host'] ?? 'ssl://localhost:993';
    if ($is_verbose && strpos($imap_host, 'localhost') !== false) {
        echo "[HINT] Worker IMAP host is set to '$imap_host'. On shared/managed hosting (e.g. Combell), verify if your mail host is external (e.g. 'ssl://imap.combell.com:993' or 'ssl://mail.thechargegrid.com:993').\n";
    }
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

        $ssl_verify = $config['lifeprisma_ai_worker_imap_ssl_verify'] ?? true;
        $client = new LpaiImapClient($is_verbose);
        try {
            $client->connect($host, 20, $ssl_verify);
            if (!$client->login($login_user, $login_pass)) {
                echo "[$now] [ERROR] IMAP login failed for account: $email\n";
                $client->close();
                continue;
            }

            $drafts_folder = $client->resolve_drafts_folder($drafts_folder);
            if ($is_verbose) {
                echo "[$now] [IMAP] Target Drafts folder resolved to: '$drafts_folder'\n";
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
                    if ($is_verbose) echo "[$now] [SKIP] UID $uid '$subject' — Self-sent email\n";
                    $state->mark_processed($email, 'INBOX', $uid, 'skipped_self');
                    continue;
                }

                // 3. Triage & Label Assignment (roundcube-labels / Thunderbird compatible)
                $assigned_flag = null;
                $category = 'fyi';

                if (!empty($config['lifeprisma_ai_triage_labels_enabled'])) {
                    $label_map = $config['lifeprisma_ai_triage_label_map'] ?? [
                        'action_required_high' => '$Label1',
                        'action_required'      => '$Label4',
                        'meeting'              => '$Label2',
                        'follow_up'            => '$Label4',
                        'fyi'                  => '$Label5',
                        'scam'                 => '$Label1',
                    ];

                    $text_combined = $subject . ' ' . $body;
                    $text_lower = strtolower($text_combined);
                    $has_question = (strpos($text_combined, '?') !== false);
                    $has_action_words = (bool) preg_match('/\b(please|could you|can you|let me know|what do you think|confirm|feedback|reply|respond|waiting for|deadline|meeting|schedule|availability|asap|gelieve|kunt u|kan je|bevestig|graag|reactie|antwoord|nodig|actie)\b/i', $text_lower);

                    if (preg_match('/\b(urgent|asap|critical|immediate|action required|important|belangrijk|spoed|dringend|prioriteit)\b/i', $text_lower)) {
                        $category = 'action_required_high';
                        $assigned_flag = $label_map['action_required_high'] ?? '$Label1';
                    } elseif (preg_match('/\b(meeting|zoom|google meet|teams|calendar|schedule|afspraak|overleg|vergadering)\b/i', $text_lower)) {
                        $category = 'meeting';
                        $assigned_flag = $label_map['meeting'] ?? '$Label2';
                    } elseif (preg_match('/\b(follow up|checking in|status update|status|opvolging)\b/i', $text_lower)) {
                        $category = 'follow_up';
                        $assigned_flag = $label_map['follow_up'] ?? '$Label4';
                    } elseif ($has_question || $has_action_words) {
                        $category = 'action_required';
                        $assigned_flag = $label_map['action_required'] ?? '$Label4';
                    } else {
                        $category = 'fyi';
                        $assigned_flag = $label_map['fyi'] ?? '$Label5';
                    }

                    if (!$is_dry_run && !empty($assigned_flag)) {
                        $flag_ok = $client->add_flags($uid, $assigned_flag);
                        if ($flag_ok) {
                            echo "[$now] [LABEL] UID $uid '$subject' — Tagged with label flag $assigned_flag ($category)\n";
                        } else {
                            echo "[$now] [WARN] Failed to set flag $assigned_flag on UID $uid\n";
                        }
                    } else {
                        echo "[$now] [LABEL-PLAN] UID $uid '$subject' — Evaluated label: $assigned_flag ($category)" . ($is_dry_run ? " (Dry-run, not stored)" : "") . "\n";
                    }
                }

                // 4. Smart Draft Gate (skips draft reply generation for purely informational / FYI emails)
                if (!empty($config['lifeprisma_ai_auto_draft_filter'])) {
                    $text_combined = $subject . ' ' . $body;
                    $has_action = (strpos($text_combined, '?') !== false) ||
                        preg_match('/\b(please|could you|can you|let me know|what do you think|confirm|feedback|reply|respond|waiting for|deadline|meeting|schedule|availability|asap|gelieve|kunt u|kan je|bevestig|graag|reactie|antwoord|nodig|actie)\b/i', strtolower($text_combined));

                    if (!$has_action && $category === 'fyi') {
                        echo "[$now] [SKIP-DRAFT] UID $uid '$subject' — Informational email (no reply needed, labeled as FYI)\n";
                        if (!$is_dry_run) {
                            $state->mark_processed($email, 'INBOX', $uid, 'labeled_fyi_no_draft', ['subject' => $subject, 'category' => $category, 'label' => $assigned_flag]);
                        }
                        continue;
                    }
                }

                // 5. Generate Draft Reply with Gemini (with AI Memory Replication)
                echo "[$now] [AI] Generating Gemini ($model) draft reply for: '$subject' from $from...\n";

                $language = $config['lifeprisma_ai_default_language'] ?? 'English';
                $tone = $config['lifeprisma_ai_default_tone'] ?? 'professional';

                // Load learned Q&A memory to replicate answers to similar client questions
                $memories = lpai_worker_load_memory($config, $email);
                $memory_prompt = '';
                if (!empty($memories)) {
                    $memory_prompt .= "\n\nORGANIZATIONAL KNOWLEDGE & PAST VERIFIED CLIENT ANSWERS:\n";
                    $count = 0;
                    foreach ($memories as $mem) {
                        if (!empty($mem['question']) && !empty($mem['answer'])) {
                            $count++;
                            $memory_prompt .= "--- [Memory Item #{$count}] ---\n";
                            $memory_prompt .= "Client Question: " . substr($mem['question'], 0, 350) . "\n";
                            $memory_prompt .= "Verified Answer: " . substr($mem['answer'], 0, 900) . "\n";
                            if ($count >= 25) break;
                        }
                    }
                    $memory_prompt .= "\nCRITICAL ANSWER REPLICATION INSTRUCTION: If the incoming email asks a question similar or equivalent to any question in your memory above, REPLICATE the verified answer accurately. Adapt salutations and specific context for the recipient, but preserve the exact factual answer, instructions, policy, and details.";
                }

                $system_prompt = "You are an elite, discreet Executive AI Assistant. " .
                    "Your role is to draft an exceptional, highly contextual executive email response. " .
                    "Maintain the user's authentic tone ($tone) in $language. " .
                    "Directly address all questions and action items. Do not include placeholders like [Your Name]." .
                    $memory_prompt;

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
                        $state->mark_processed($email, 'INBOX', $uid, 'draft_created', ['subject' => $subject, 'category' => $category, 'label' => $assigned_flag]);
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
if (isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $state_file = __DIR__ . '/.worker_state.json';
    $state = new LpaiWorkerState($state_file);

    if ($is_reset_state) {
        $state->reset();
        echo "[INFO] Worker state reset successfully. All unseen emails will be re-processed.\n";
    }

    $config = lpai_worker_load_config($custom_config);
    $loaded_cfg = $config['_loaded_from'] ?? 'default fallbacks';

    $model = $config['lifeprisma_ai_gemini_model'] ?? 'gemini-3.8-flash';
    echo "===========================================================\n";
    echo "Gemini Executive Assistant — 24/7 CLI Background Worker\n";
    echo "Model: $model | Mode: " . ($is_daemon ? "Daemon (interval: {$poll_interval}s)" : "Single Pass") . "\n";
    echo "Config: $loaded_cfg\n";
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
}
