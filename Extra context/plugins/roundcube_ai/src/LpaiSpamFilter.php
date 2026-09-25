<?php

/**
 * Gemini Executive Assistant & Roundcube AI — Advanced Spam Filter & Self-Learning Engine
 *
 * Implements a multi-tiered, enterprise-grade anti-spam classification engine:
 * 1. Statistical Naive Bayes Token Classifier with Robinson & Graham smoothing
 * 2. Header & Structural Heuristics (SPF, DKIM, DMARC, Return-Path spoofing, attachment risks)
 * 3. Sender & Domain Dynamic Reputation System
 * 4. User-configurable Whitelists, Blacklists & Custom Trigger Keywords
 * 5. Bi-directional Continuous Learning (Auto-correction on untagging)
 * 6. Optional Deep Phishing/Spam Semantic Inspection via Google Gemini 3.8 Flash
 *
 * @license MIT
 * @author LifePrisma & Roundcube-AI Contributors
 */

class LpaiSpamFilter
{
    /** @var string Data storage directory for learned Bayesian models */
    private static $storage_dir = null;

    /** @var array Cached user models to avoid repeated disk reads */
    private static $models_cache = [];

    /** @var string Current user identifier for instance methods */
    private $user_identifier = 'default';

    /** @var string Last active user identifier for static fallbacks */
    private static $last_user_identifier = 'default';

    /** @var array Whitelist rules for instance methods */
    private $whitelist = [];

    /** @var array Blacklist rules for instance methods */
    private $blacklist = [];

    /** @var array Custom keywords for instance methods */
    private $custom_keywords = [];

    /** @var int Spam threshold percentage for instance methods (0-100) */
    private $threshold = 75;

    /**
     * Constructor allowing instance-oriented usage.
     */
    public function __construct(string $user_identifier = 'default', ?string $storage_dir = null)
    {
        $this->user_identifier = $user_identifier;
        self::$last_user_identifier = $user_identifier;
        if ($storage_dir !== null) {
            self::set_storage_dir($storage_dir);
        }
    }

    public function set_whitelist(array $whitelist): void
    {
        $this->whitelist = $whitelist;
    }

    public function set_blacklist(array $blacklist): void
    {
        $this->blacklist = $blacklist;
    }

    public function set_custom_keywords(array $keywords): void
    {
        $this->custom_keywords = $keywords;
    }

    public function set_threshold(int $threshold): void
    {
        $this->threshold = $threshold;
    }

    /**
     * Magic caller routing instance invocations to static implementations.
     */
    public function __call(string $name, array $args)
    {
        switch ($name) {
            case 'classify':
                $body = $args[0] ?? '';
                $headers = $args[1] ?? [];
                $threshold = $args[2] ?? null;
                $raw_headers = '';
                foreach ($headers as $k => $v) {
                    $raw_headers .= "{$k}: {$v}\r\n";
                }
                $subject = $headers['subject'] ?? '';
                $sender = $headers['from'] ?? '';

                $calc_threshold = $this->threshold;
                if ($threshold !== null) {
                    $calc_threshold = (is_float($threshold) && $threshold <= 1.0)
                        ? (int) round($threshold * 100)
                        : (int) $threshold;
                }

                $config = [
                    'lifeprisma_ai_spam_whitelist' => $this->whitelist,
                    'lifeprisma_ai_spam_blacklist' => $this->blacklist,
                    'lifeprisma_ai_spam_keywords'  => $this->custom_keywords,
                    'lifeprisma_ai_spam_threshold' => $calc_threshold,
                ];

                $res = self::check_message($subject, $body, $raw_headers, $sender, $this->user_identifier, $config);
                $res['score_points'] = $res['score'];
                $res['score'] = round($res['score_points'] / 100, 4);
                return $res;

            case 'learn_spam':
                $body = $args[0] ?? '';
                $headers = $args[1] ?? [];
                $message_id = $args[2] ?? '';
                $raw_headers = '';
                foreach ($headers as $k => $v) {
                    $raw_headers .= "{$k}: {$v}\r\n";
                }
                $subject = $headers['subject'] ?? '';
                $sender = $headers['from'] ?? '';
                $model = self::learn_spam($subject, $body, $raw_headers, $sender, $this->user_identifier, $message_id);
                return [
                    'success'    => true,
                    'spam_count' => (int) ($model['total_spam'] ?? 0),
                    'ham_count'  => (int) ($model['total_ham'] ?? 0),
                    'model'      => $model,
                ];

            case 'learn_ham':
                $body = $args[0] ?? '';
                $headers = $args[1] ?? [];
                $reverse_spam = $args[2] ?? true;
                $message_id = $args[3] ?? '';
                $raw_headers = '';
                foreach ($headers as $k => $v) {
                    $raw_headers .= "{$k}: {$v}\r\n";
                }
                $subject = $headers['subject'] ?? '';
                $sender = $headers['from'] ?? '';
                $model = self::learn_ham($subject, $body, $raw_headers, $sender, $this->user_identifier, $message_id, $reverse_spam);
                return [
                    'success'    => true,
                    'spam_count' => (int) ($model['total_spam'] ?? 0),
                    'ham_count'  => (int) ($model['total_ham'] ?? 0),
                    'model'      => $model,
                ];

            case 'get_stats':
                $user = $args[0] ?? $this->user_identifier;
                return self::get_stats($user);

            case 'reset_database':
                $user = $args[0] ?? $this->user_identifier;
                return self::reset_model($user);

            default:
                throw new \BadMethodCallException("Method {$name} does not exist on LpaiSpamFilter");
        }
    }

    /**
     * Built-in baseline spam tokens with pre-trained frequency biases.
     * Guarantees zero-day heuristic protection out-of-the-box before the user trains the filter.
     */
    private static $baseline_tokens = [
        'viagra'                => ['s' => 50, 'h' => 0],
        'cialis'                => ['s' => 45, 'h' => 0],
        'casino'                => ['s' => 60, 'h' => 0],
        'bitcoin'               => ['s' => 30, 'h' => 2],
        'crypto'                => ['s' => 25, 'h' => 3],
        'wallet'                => ['s' => 20, 'h' => 5],
        'wire_transfer'         => ['s' => 40, 'h' => 1],
        'western_union'         => ['s' => 50, 'h' => 0],
        'inheritance'           => ['s' => 45, 'h' => 0],
        'beneficiary'           => ['s' => 35, 'h' => 1],
        'urgent_action'         => ['s' => 30, 'h' => 2],
        'account_suspended'     => ['s' => 40, 'h' => 1],
        'verify_account'        => ['s' => 35, 'h' => 2],
        'confirm_identity'      => ['s' => 30, 'h' => 1],
        'claim_prize'           => ['s' => 50, 'h' => 0],
        'congratulations_won'   => ['s' => 55, 'h' => 0],
        'lottery'               => ['s' => 60, 'h' => 0],
        'gift_card'             => ['s' => 30, 'h' => 1],
        'million_dollars'       => ['s' => 50, 'h' => 0],
        'fund_transfer'         => ['s' => 35, 'h' => 1],
        'password_expired'      => ['s' => 35, 'h' => 1],
        'log_in_now'            => ['s' => 25, 'h' => 2],
        'free_gift'             => ['s' => 40, 'h' => 0],
        'investment_opportunity'=> ['s' => 35, 'h' => 0],
        'act_now'               => ['s' => 30, 'h' => 2],
        'click_here'            => ['s' => 25, 'h' => 5],
        'risk_free'             => ['s' => 35, 'h' => 1],
        'unclaimed_funds'       => ['s' => 50, 'h' => 0],
        'urgent_confidential'   => ['s' => 40, 'h' => 0],
        'pay_in_crypto'         => ['s' => 45, 'h' => 0],
        'debt_relief'           => ['s' => 40, 'h' => 0],
        'make_money_fast'       => ['s' => 50, 'h' => 0],
        // Baseline HAM seed tokens to prevent false positives on legitimate emails
        'regards'               => ['s' => 0, 'h' => 50],
        'thanks'                => ['s' => 1, 'h' => 45],
        'thank_you'             => ['s' => 1, 'h' => 45],
        'meeting'               => ['s' => 1, 'h' => 40],
        'schedule'              => ['s' => 2, 'h' => 35],
        'attached'              => ['s' => 3, 'h' => 35],
        'project'               => ['s' => 1, 'h' => 40],
        'update'                => ['s' => 2, 'h' => 30],
        'invoice'               => ['s' => 5, 'h' => 35],
        'colleague'             => ['s' => 0, 'h' => 30],
        'team'                  => ['s' => 2, 'h' => 30],
        'review'                => ['s' => 2, 'h' => 30],
        'discussion'            => ['s' => 0, 'h' => 25],
        'contract'              => ['s' => 2, 'h' => 25],
        'report'                => ['s' => 2, 'h' => 25],
        'sincerely'             => ['s' => 1, 'h' => 30],
        'bedankt'               => ['s' => 0, 'h' => 40],
        'groeten'               => ['s' => 0, 'h' => 40],
        'afspraak'              => ['s' => 0, 'h' => 30],
        'vergadering'           => ['s' => 0, 'h' => 30],
        'factuur'               => ['s' => 2, 'h' => 30],
        'bijlage'               => ['s' => 1, 'h' => 30],
    ];

    /**
     * Locates the host Roundcube root directory.
     */
    public static function find_roundcube_root(): ?string
    {
        if (defined('RCUBE_INSTALL_PATH') && is_dir(RCUBE_INSTALL_PATH)) {
            return rtrim(RCUBE_INSTALL_PATH, '/\\');
        }
        if (defined('INSTALL_PATH') && is_dir(INSTALL_PATH)) {
            return rtrim(INSTALL_PATH, '/\\');
        }
        if (class_exists('rcmail', false)) {
            try {
                $rcmail = rcmail::get_instance();
                if ($rcmail && $rcmail->config) {
                    $dir = $rcmail->config->get('roundcube_dir');
                    if ($dir && is_dir($dir)) {
                        return rtrim($dir, '/\\');
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Walk up directory tree from plugin src
        $candidates = [
            dirname(__DIR__, 3), // plugins/lifeprisma_ai/src -> <roundcube_root>
            dirname(__DIR__, 4), // vendor/webdotpulse/roundcube-ai/src -> <roundcube_root>
            dirname(__DIR__, 2),
        ];

        foreach ($candidates as $cand) {
            if (is_dir($cand) && (
                is_dir($cand . '/plugins') ||
                is_dir($cand . '/skins') ||
                file_exists($cand . '/program/include/iniset.php') ||
                file_exists($cand . '/index.php')
            )) {
                return rtrim($cand, '/\\');
            }
        }

        return null;
    }

    /**
     * Resolves and ensures writable storage directory for spam models.
     * Prioritizes persistent paths OUTSIDE the plugin folder so that
     * `composer require` / `composer update` never resets learned data.
     */
    public static function get_storage_dir(): string
    {
        if (self::$storage_dir !== null) {
            return self::$storage_dir;
        }

        $candidates = [];

        // 1. Explicitly configured storage directory in Roundcube config
        if (class_exists('rcmail', false)) {
            try {
                $rcmail = rcmail::get_instance();
                if ($rcmail && $rcmail->config) {
                    $custom_spam_dir = $rcmail->config->get('lifeprisma_ai_spam_storage_dir');
                    if ($custom_spam_dir) {
                        $candidates[] = rtrim($custom_spam_dir, '/\\');
                    }
                    $custom_data_dir = $rcmail->config->get('lifeprisma_ai_data_dir');
                    if ($custom_data_dir) {
                        $candidates[] = rtrim($custom_data_dir, '/\\') . '/spam';
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Canonical Roundcube host data directory (OUTSIDE plugin folder)
        $rc_root = self::find_roundcube_root();
        if ($rc_root) {
            $candidates[] = $rc_root . '/data/lifeprisma_ai/spam';
            $candidates[] = $rc_root . '/data/spam';
            $candidates[] = $rc_root . '/temp/lifeprisma_ai/spam';
        }

        // 3. System persistent directory
        if (is_dir('/var/lib/roundcube')) {
            $candidates[] = '/var/lib/roundcube/lifeprisma_ai/spam';
        }

        // 4. Legacy local plugin directory
        $legacy_local_dir = dirname(__DIR__) . '/data/spam';
        $candidates[] = $legacy_local_dir;

        // 5. System temp fallback
        $candidates[] = sys_get_temp_dir() . '/roundcube_lifeprisma_ai/spam';
        $candidates[] = sys_get_temp_dir() . '/roundcube_spam_filter';

        foreach ($candidates as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                // Secure with .htaccess if under web-accessible data directory
                self::secure_storage_directory($dir);

                // Auto-migrate any legacy files from plugin directory to persistent directory
                if ($dir !== $legacy_local_dir && is_dir($legacy_local_dir)) {
                    self::migrate_legacy_storage_files($legacy_local_dir, $dir);
                }

                self::$storage_dir = $dir;
                return self::$storage_dir;
            }
        }

        self::$storage_dir = sys_get_temp_dir();
        return self::$storage_dir;
    }

    /**
     * Secures a storage directory by adding an .htaccess file.
     */
    private static function secure_storage_directory(string $dir): void
    {
        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "# LifePrisma AI data protection\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\nOptions -Indexes\n");
        }
    }

    /**
     * Migrates legacy bayes_*.json model files into persistent storage.
     */
    private static function migrate_legacy_storage_files(string $legacy_dir, string $target_dir): void
    {
        static $migrated = false;
        if ($migrated) return;
        $migrated = true;

        $files = glob($legacy_dir . '/bayes_*.json') ?: [];
        foreach ($files as $src_file) {
            $dest_file = $target_dir . '/' . basename($src_file);
            if (!file_exists($dest_file) || (filemtime($src_file) > filemtime($dest_file))) {
                @copy($src_file, $dest_file);
            }
        }
    }

    /**
     * Resolves the Roundcube database handler and ensures the persistent table exists.
     */
    private static function get_db()
    {
        if (!class_exists('rcmail', false)) {
            return null;
        }
        try {
            $rcmail = rcmail::get_instance();
            if ($rcmail && method_exists($rcmail, 'get_dbh')) {
                $db = $rcmail->get_dbh();
                if ($db) {
                    self::ensure_database_table($db);
                    return $db;
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    /**
     * Creates the lpai_spam_models table in the Roundcube database if missing.
     */
    private static function ensure_database_table($db): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $table = method_exists($db, 'table_name') ? $db->table_name('lpai_spam_models') : 'lpai_spam_models';
        $driver = strtolower($db->db_provider ?? 'mysql');

        $sql = match ($driver) {
            'sqlite' => "CREATE TABLE IF NOT EXISTS {$table} (
                user_id VARCHAR(128) PRIMARY KEY,
                total_spam INTEGER NOT NULL DEFAULT 0,
                total_ham INTEGER NOT NULL DEFAULT 0,
                tokens_count INTEGER NOT NULL DEFAULT 0,
                model_data TEXT NOT NULL,
                created_at INTEGER NOT NULL DEFAULT 0,
                updated_at INTEGER NOT NULL DEFAULT 0
            );",
            'pgsql', 'postgres' => "CREATE TABLE IF NOT EXISTS {$table} (
                user_id VARCHAR(128) PRIMARY KEY,
                total_spam INTEGER NOT NULL DEFAULT 0,
                total_ham INTEGER NOT NULL DEFAULT 0,
                tokens_count INTEGER NOT NULL DEFAULT 0,
                model_data TEXT NOT NULL,
                created_at INTEGER NOT NULL DEFAULT 0,
                updated_at INTEGER NOT NULL DEFAULT 0
            );",
            default => "CREATE TABLE IF NOT EXISTS `{$table}` (
                `user_id` VARCHAR(128) NOT NULL,
                `total_spam` INT UNSIGNED NOT NULL DEFAULT 0,
                `total_ham` INT UNSIGNED NOT NULL DEFAULT 0,
                `tokens_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `model_data` LONGTEXT NOT NULL,
                `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
                `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`user_id`),
                INDEX `idx_lpai_updated` (`updated_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        };

        try {
            @$db->query($sql);
        } catch (\Throwable $e) {}
    }

    /**
     * Synchronizes a Bayesian model into the persistent database table.
     */
    private static function sync_model_to_db($db, string $user_identifier, array $model): bool
    {
        try {
            $table = method_exists($db, 'table_name') ? $db->table_name('lpai_spam_models') : 'lpai_spam_models';
            $json = json_encode($model, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) return false;

            $tokens_count = count($model['tokens'] ?? []);
            $total_spam = (int)($model['total_spam'] ?? 0);
            $total_ham = (int)($model['total_ham'] ?? 0);
            $now = (int)($model['updated_at'] ?? time());
            $created_at = (int)($model['created_at'] ?? $now);

            $res = $db->query("SELECT user_id FROM {$table} WHERE user_id = ?", $user_identifier);
            if ($res && $db->fetch_assoc($res)) {
                $db->query(
                    "UPDATE {$table} SET total_spam = ?, total_ham = ?, tokens_count = ?, model_data = ?, updated_at = ? WHERE user_id = ?",
                    $total_spam, $total_ham, $tokens_count, $json, $now, $user_identifier
                );
            } else {
                $db->query(
                    "INSERT INTO {$table} (user_id, total_spam, total_ham, tokens_count, model_data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    $user_identifier, $total_spam, $total_ham, $tokens_count, $json, $created_at, $now
                );
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Override storage directory (primarily for testing).
     */
    public static function set_storage_dir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        self::$storage_dir = $dir;
        self::$models_cache = [];
    }

    /**
     * Generates a safe storage path for a user's Bayesian model.
     */
    public static function get_model_path(string $user_identifier): string
    {
        $safe_id = preg_replace('/[^a-zA-Z0-9_\-\.@]/', '_', $user_identifier);
        $hash = md5($user_identifier);
        return self::get_storage_dir() . "/bayes_{$safe_id}_{$hash}.json";
    }

    /**
     * Atomically writes a model JSON file to disk.
     */
    public static function write_model_file(string $file, array $model): bool
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $tmp_file = $file . '.' . uniqid('tmp_', true);

        $json = json_encode($model, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $written = @file_put_contents($tmp_file, $json, LOCK_EX);
        if ($written === false) {
            return false;
        }

        return @rename($tmp_file, $file);
    }

    /**
     * Loads the user's learned model, initializing default structure if new.
     * Restores automatically from Roundcube database if disk file is missing (e.g. composer update).
     */
    public static function load_model(string $user_identifier): array
    {
        if (isset(self::$models_cache[$user_identifier])) {
            return self::$models_cache[$user_identifier];
        }

        $file = self::get_model_path($user_identifier);
        $default_model = [
            'version'           => 1,
            'user'              => $user_identifier,
            'total_spam'        => 0,
            'total_ham'         => 0,
            'created_at'        => time(),
            'updated_at'        => time(),
            'tokens'            => [],
            'sender_reputation' => [],
            'learned_message_ids' => [], // ID => 'spam' | 'ham' for auto-correction
        ];

        $disk_model = null;
        if (file_exists($file)) {
            $fp = @fopen($file, 'rb');
            if ($fp) {
                @flock($fp, LOCK_SH);
                $content = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);

                if (!empty($content)) {
                    $data = json_decode($content, true);
                    if (is_array($data) && isset($data['tokens'])) {
                        $disk_model = array_merge($default_model, $data);
                    }
                }
            }
        }

        $db = self::get_db();
        $db_model = null;
        if ($db) {
            try {
                $table = method_exists($db, 'table_name') ? $db->table_name('lpai_spam_models') : 'lpai_spam_models';
                $res = $db->query("SELECT model_data, updated_at FROM {$table} WHERE user_id = ?", $user_identifier);
                if ($res && ($row = $db->fetch_assoc($res))) {
                    $parsed = json_decode($row['model_data'] ?? '', true);
                    if (is_array($parsed) && isset($parsed['tokens'])) {
                        $db_model = array_merge($default_model, $parsed);
                        if (!empty($row['updated_at'])) {
                            $db_model['updated_at'] = (int)$row['updated_at'];
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        if ($disk_model && $db_model) {
            $disk_updated = (int)($disk_model['updated_at'] ?? 0);
            $db_updated = (int)($db_model['updated_at'] ?? 0);
            if ($db_updated > $disk_updated) {
                $model = $db_model;
                self::write_model_file($file, $model);
            } else {
                $model = $disk_model;
                if ($disk_updated > $db_updated && $db) {
                    self::sync_model_to_db($db, $user_identifier, $model);
                }
            }
        } elseif ($disk_model) {
            $model = $disk_model;
            if ($db) {
                self::sync_model_to_db($db, $user_identifier, $model);
            }
        } elseif ($db_model) {
            // Restored from database after file deletion / composer update!
            $model = $db_model;
            self::write_model_file($file, $model);
        } else {
            $model = $default_model;
        }

        self::$models_cache[$user_identifier] = $model;
        return self::$models_cache[$user_identifier];
    }

    /**
     * Persists the user's learned model atomically to disk and database.
     */
    public static function save_model(string $user_identifier, array $model): bool
    {
        $model['updated_at'] = time();
        self::$models_cache[$user_identifier] = $model;

        // Auto-prune model if dictionary exceeds 12,000 tokens to keep execution fast (< 3ms)
        if (count($model['tokens']) > 12000) {
            $model = self::prune_model_tokens($model, 8000);
            self::$models_cache[$user_identifier] = $model;
        }

        $file = self::get_model_path($user_identifier);
        $written = self::write_model_file($file, $model);

        // Synchronize atomically to Roundcube database
        $db = self::get_db();
        if ($db) {
            self::sync_model_to_db($db, $user_identifier, $model);
        }

        return $written;
    }

    /**
     * Resets the learned Bayesian database for a given user.
     */
    public static function reset_model(?string $user_identifier = null): bool
    {
        $user_identifier = $user_identifier ?: self::$last_user_identifier;
        unset(self::$models_cache[$user_identifier]);
        $file = self::get_model_path($user_identifier);
        if (file_exists($file)) {
            @unlink($file);
        }
        $db = self::get_db();
        if ($db) {
            try {
                $table = method_exists($db, 'table_name') ? $db->table_name('lpai_spam_models') : 'lpai_spam_models';
                $db->query("DELETE FROM {$table} WHERE user_id = ?", $user_identifier);
            } catch (\Throwable $e) {}
        }
        return true;
    }

    /**
     * Prunes infrequent tokens when dictionary grows too large.
     */
    private static function prune_model_tokens(array $model, int $target_count): array
    {
        $tokens = $model['tokens'];
        if (count($tokens) <= $target_count) {
            return $model;
        }

        // Calculate salience score for each token: (s + h)
        $scored = [];
        foreach ($tokens as $tok => $data) {
            $sum = ($data['s'] ?? 0) + ($data['h'] ?? 0);
            $scored[$tok] = $sum;
        }

        arsort($scored, SORT_NUMERIC);
        $kept_keys = array_slice(array_keys($scored), 0, $target_count, true);
        $model['tokens'] = array_intersect_key($tokens, array_flip($kept_keys));

        return $model;
    }

    // =========================================================================
    // Feature Extraction & Normalization
    // =========================================================================

    /**
     * Extracts tokens (words, n-grams, domains, flags) from email content.
     */
    public static function extract_tokens(string $subject, string $body, string $raw_headers = '', string $sender = ''): array
    {
        $tokens = [];

        // 1. Sender domain token
        if (!empty($sender) && preg_match('/@([a-zA-Z0-9\-\.]+)/', $sender, $m)) {
            $sender_domain = strtolower(trim($m[1]));
            $tokens["from_domain:{$sender_domain}"] = 2; // Domain gets 2x weight
        }

        // 2. Normalize and tokenize Subject
        $clean_subject = self::normalize_text($subject);
        $subject_words = self::tokenize_string($clean_subject);
        foreach ($subject_words as $w) {
            $tokens["subj:{$w}"] = ($tokens["subj:{$w}"] ?? 0) + 3; // Subject tokens get 3x weight
            $tokens[$w] = ($tokens[$w] ?? 0) + 2;
        }

        // 2b. Subject bigrams
        $subject_bigrams = self::extract_bigrams($subject_words);
        foreach ($subject_bigrams as $bg) {
            $tokens["subj_bi:{$bg}"] = ($tokens["subj_bi:{$bg}"] ?? 0) + 3;
            $tokens[$bg] = ($tokens[$bg] ?? 0) + 2;
        }

        // 3. Normalize and tokenize Body
        // Strip HTML tags cleanly while capturing hyperlinks
        $links = [];
        if (preg_match_all('/href=[\'"](https?:\/\/[^\'"]+)[\'"]/i', $body, $m_links)) {
            foreach ($m_links[1] as $u) {
                $host = parse_url($u, PHP_URL_HOST);
                if ($host) {
                    $links[] = strtolower($host);
                }
            }
        }
        foreach (array_unique($links) as $domain) {
            $tokens["link_domain:{$domain}"] = 2;
        }

        $clean_body = self::normalize_text($body);
        $body_words = self::tokenize_string($clean_body);
        // Cap body tokens to first 2,000 words to ensure performance
        $body_words = array_slice($body_words, 0, 2000);

        foreach ($body_words as $w) {
            $tokens[$w] = ($tokens[$w] ?? 0) + 1;
        }

        $body_bigrams = self::extract_bigrams($body_words);
        foreach ($body_bigrams as $bg) {
            $tokens[$bg] = ($tokens[$bg] ?? 0) + 1;
        }

        // 4. Header-derived structural tokens
        if (!empty($raw_headers)) {
            if (preg_match('/Received-SPF:\s*(fail|softfail)/i', $raw_headers)) {
                $tokens['header:spf_fail'] = 5;
            } elseif (preg_match('/Received-SPF:\s*pass/i', $raw_headers)) {
                $tokens['header:spf_pass'] = 5;
            }

            if (preg_match('/dkim=(fail|neutral)/i', $raw_headers)) {
                $tokens['header:dkim_fail'] = 4;
            } elseif (preg_match('/dkim=pass/i', $raw_headers)) {
                $tokens['header:dkim_pass'] = 4;
            }

            if (preg_match('/dmarc=(fail|reject)/i', $raw_headers)) {
                $tokens['header:dmarc_fail'] = 5;
            }

            if (preg_match('/X-Spam-Flag:\s*YES/i', $raw_headers) || preg_match('/X-Spam-Status:\s*Yes/i', $raw_headers)) {
                $tokens['header:mta_spam_flag'] = 8;
            }

            if (preg_match('/Precedence:\s*(bulk|junk)/i', $raw_headers) || preg_match('/Auto-Submitted:\s*auto/i', $raw_headers)) {
                $tokens['header:precedence_bulk'] = 2;
            }
        }

        // 5. Stylistic tokens
        if (strlen($subject) > 8) {
            $caps_count = strlen(preg_replace('/[^A-Z]/', '', $subject));
            $total_letters = strlen(preg_replace('/[^a-zA-Z]/', '', $subject));
            if ($total_letters > 6 && ($caps_count / $total_letters) > 0.65) {
                $tokens['style:all_caps_subject'] = 4;
            }
        }

        if (preg_match('/[\!\?\$]{3,}/', $subject)) {
            $tokens['style:excessive_punctuation'] = 3;
        }

        return $tokens;
    }

    /**
     * Normalizes text by decoding HTML, quoted-printable, base64 fragments, and lowercasing.
     */
    public static function normalize_text(string $text): string
    {
        // Decode quoted printable if encoded
        if (strpos($text, '=') !== false) {
            $text = quoted_printable_decode($text);
        }

        // Strip HTML tags while converting breaks to spaces
        $text = preg_replace('/<(br|p|div|tr|h[1-6])[^>]*>/i', ' ', $text);
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove non-printable / zero-width characters commonly used in obfuscation
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\xE2\x80\x8B\xEF\xBB\xBF]/u', '', $text);

        // Standardize currency symbols
        $text = str_replace(['$', '€', '£', '¥'], [' dollar ', ' euro ', ' pound ', ' yen '], $text);

        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * Splits normalized string into alphanumeric word tokens.
     */
    private static function tokenize_string(string $text): array
    {
        // Split by non-alphanumeric (allowing dots and dashes inside tokens like domain names)
        preg_match_all('/[a-z0-9\_\-\.]{2,35}/u', $text, $matches);
        if (empty($matches[0])) {
            return [];
        }

        $common_stopwords = [
            // English function words, pronouns, prepositions & auxiliaries
            'the','and','that','have','for','not','with','you','this','but','his','from','they','say','her',
            'she','will','one','all','would','there','their','what','out','about','who','get','which','go',
            'when','make','can','like','time','just','him','know','take','people','into','year','your','good',
            'some','could','them','see','other','than','then','now','look','only','come','its','over','think',
            'also','back','after','use','two','how','our','work','first','well','way','even','new','want',
            'because','any','these','give','day','most','are','were','was','been','being','has','had','having',
            'does','did','done','doing','should','would','could','shall','may','might','must',
            'between','into','through','during','before','after','above','below','down','off','under',
            'again','further','then','once','here','there','where','why','how','both','each','few','more',
            'such','nor','own','same','too','very',
            // Dutch function words, pronouns, prepositions & auxiliaries
            'het','de','een','van','naar','met','voor','niet','dat','die','aan','ook','maar','om','bij',
            'als','zijn','wat','over','door','uit','wel','nog','waren','worden','werd','omdat','haar','hun',
            'mij','mijn','jou','jouw','wij','ons','onze','jullie','geen','kunnen','zullen','zou','zouden',
            'moet','moeten','hebt','heeft','hebben','hadden','dan',
        ];
        $stopwords_map = array_flip($common_stopwords);

        $words = [];
        foreach ($matches[0] as $token) {
            $token = trim($token, '.-_');
            if (strlen($token) < 2) continue;
            if (isset($stopwords_map[$token])) continue;
            $words[] = $token;
        }

        return $words;
    }

    /**
     * Extracts consecutive word pairs (bigrams).
     */
    private static function extract_bigrams(array $words): array
    {
        $bigrams = [];
        $len = count($words);
        for ($i = 0; $i < $len - 1; $i++) {
            $w1 = $words[$i];
            $w2 = $words[$i + 1];
            // Only keep bigrams where neither word is pure numbers
            if (!is_numeric($w1) || !is_numeric($w2)) {
                $bigrams[] = "{$w1}_{$w2}";
            }
        }
        return $bigrams;
    }

    // =========================================================================
    // Statistical Bayesian Probability Engine (Robinson & Graham Smoothing)
    // =========================================================================

    /**
     * Calculates the statistical probability of a token being spam.
     * Uses Gary Robinson's formula with prior probability s * x / (s + n).
     *
     * @param string $token
     * @param array $model
     * @return float Probability between 0.01 and 0.99
     */
    public static function calculate_token_probability(string $token, array $model): float
    {
        $s = 2.0; // Robinson's weight for initial belief (2.0 provides stability against single-occurrence skew)
        $x = 0.5; // Assumed initial probability (neutral)

        $token_spam = 0;
        $token_ham = 0;

        // Check user learned model
        if (isset($model['tokens'][$token])) {
            $token_spam += (int) ($model['tokens'][$token]['s'] ?? 0);
            $token_ham += (int) ($model['tokens'][$token]['h'] ?? 0);
        }

        // Blend baseline pre-trained seed tokens
        if (isset(self::$baseline_tokens[$token])) {
            $token_spam += (int) (self::$baseline_tokens[$token]['s'] ?? 0);
            $token_ham += (int) (self::$baseline_tokens[$token]['h'] ?? 0);
        }

        $n = $token_spam + $token_ham;
        if ($n === 0) {
            return $x; // Unknown token remains neutral 0.5
        }

        // Relative frequency
        $user_spam = (int) ($model['total_spam'] ?? 0);
        $user_ham = (int) ($model['total_ham'] ?? 0);

        $total_spam = max(1, $user_spam + 50);
        $total_ham = max(1, $user_ham + 50);

        $p_spam = $token_spam / $total_spam;
        $p_ham = $token_ham / $total_ham;

        $p = $p_spam / ($p_spam + $p_ham);

        // Imbalance guard: if user has trained spam but virtually zero ham,
        // dampen tokens with zero ham observations that are not pre-trained baseline spam seeds
        if ($token_ham === 0 && $user_ham === 0 && !isset(self::$baseline_tokens[$token])) {
            $p = min(0.70, $p);
        }

        // Robinson's smoothing formula: f(w) = (s*x + n*p) / (s + n)
        $smoothed = ($s * $x + $n * $p) / ($s + $n);

        // Bound between 0.01 and 0.99
        return max(0.01, min(0.99, $smoothed));
    }

    /**
     * Combines probabilities of the most salient tokens using Robinson's geometric mean.
     *
     * @param array $tokens Array of token => weight
     * @param array $model User model
     * @param int $k Number of top discriminating tokens to evaluate
     * @return array [ 'score' => float, 'top_tokens' => array ]
     */
    public static function evaluate_bayesian_score(array $tokens, array $model, int $k = 15): array
    {
        if (empty($tokens)) {
            return ['score' => 0.5, 'top_tokens' => []];
        }

        $token_probs = [];
        foreach ($tokens as $token => $weight) {
            $prob = self::calculate_token_probability($token, $model);
            $distance = abs($prob - 0.5); // Deviation from neutral
            if ($distance >= 0.05) { // Only consider non-neutral tokens
                $token_probs[$token] = [
                    'token'    => $token,
                    'prob'     => $prob,
                    'distance' => $distance * $weight,
                    'weight'   => $weight,
                ];
            }
        }

        if (empty($token_probs)) {
            return ['score' => 0.5, 'top_tokens' => []];
        }

        // Sort by distance descending (most decisive tokens first)
        uasort($token_probs, function ($a, $b) {
            return ($b['distance'] <=> $a['distance']);
        });

        $top = array_slice($token_probs, 0, $k, true);

        // Robinson's combination: P = product(p_i) / [product(p_i) + product(1 - p_i)]
        // Using log-space to prevent float underflow
        $log_p = 0.0;
        $log_one_minus_p = 0.0;

        foreach ($top as $t) {
            $p = $t['prob'];
            $log_p += log($p);
            $log_one_minus_p += log(1.0 - $p);
        }

        $p_combined = 1.0 / (1.0 + exp($log_one_minus_p - $log_p));

        return [
            'score'      => round($p_combined, 4),
            'top_tokens' => array_values($top),
        ];
    }

    // =========================================================================
    // Sender & Domain Reputation
    // =========================================================================

    /**
     * Evaluates sender reputation score (-100 to +100).
     */
    public static function get_sender_reputation(string $sender, array $model): int
    {
        $email = self::extract_email_address($sender);
        if (empty($email)) {
            return 0;
        }

        $domain = '';
        if (strpos($email, '@') !== false) {
            $domain = substr($email, strpos($email, '@') + 1);
        }

        $rep = 0;
        if (isset($model['sender_reputation'][$email])) {
            $rep += (int) $model['sender_reputation'][$email];
        }

        if (!empty($domain) && isset($model['sender_reputation']['@' . $domain])) {
            $rep += (int) $model['sender_reputation']['@' . $domain];
        }

        return max(-100, min(100, $rep));
    }

    /**
     * Extracts pure email address from 'Name <user@domain.com>'.
     */
    public static function extract_email_address(string $sender): string
    {
        if (preg_match('/<([^>]+)>/', $sender, $m)) {
            return strtolower(trim($m[1]));
        }
        if (preg_match('/[a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}/', $sender, $m)) {
            return strtolower(trim($m[0]));
        }
        return strtolower(trim($sender));
    }

    // =========================================================================
    // Rules, Whitelists & Blacklists Pre-Flight
    // =========================================================================

    /**
     * Checks if sender matches user whitelist or blacklist.
     *
     * @param string $sender
     * @param array $whitelist Array of emails or @domains
     * @param array $blacklist Array of emails or @domains
     * @return string|null 'whitelisted', 'blacklisted', or null
     */
    public static function check_lists(string $sender, array $whitelist, array $blacklist): ?string
    {
        $email = self::extract_email_address($sender);
        if (empty($email)) {
            return null;
        }

        $domain = '@' . substr($email, strpos($email, '@') + 1);

        // 1. Whitelist takes highest precedence
        foreach ($whitelist as $item) {
            $item = strtolower(trim($item));
            if (empty($item)) continue;
            if ($item === $email || $item === $domain || (strpos($item, '@') === false && $item === ltrim($domain, '@'))) {
                return 'whitelisted';
            }
        }

        // 2. Blacklist
        foreach ($blacklist as $item) {
            $item = strtolower(trim($item));
            if (empty($item)) continue;
            if ($item === $email || $item === $domain || (strpos($item, '@') === false && $item === ltrim($domain, '@'))) {
                return 'blacklisted';
            }
        }

        return null;
    }

    /**
     * Checks for user custom trigger keywords in subject or body.
     */
    public static function check_custom_keywords(string $subject, string $body, array $keywords): array
    {
        $matches = [];
        $haystack = mb_strtolower($subject . ' ' . $body, 'UTF-8');
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if (empty($kw)) continue;
            if (stripos($haystack, strtolower($kw)) !== false) {
                $matches[] = $kw;
            }
        }
        return $matches;
    }

    // =========================================================================
    // Header & Structural Heuristics Analyzer
    // =========================================================================

    /**
     * Evaluates deterministic security & spoofing heuristics.
     *
     * @return array [ 'penalty' => int, 'reasons' => array ]
     */
    public static function evaluate_heuristics(string $subject, string $body, string $raw_headers, string $sender): array
    {
        $penalty = 0;
        $reasons = [];

        // 1. Display name spoofing (Display name contains an email address different from actual From)
        if (preg_match('/["\']?([^"\'<]+@[^"\'<]+)["\']?\s*<([^>]+)>/i', $sender, $m)) {
            $displayed_email = strtolower(trim($m[1]));
            $actual_email = strtolower(trim($m[2]));
            if ($displayed_email !== $actual_email) {
                $penalty += 45;
                $reasons[] = "Display name impersonation spoofing detected ({$displayed_email} vs {$actual_email})";
            }
        }

        if (!empty($raw_headers)) {
            // 2. SPF Failure
            if (preg_match('/Received-SPF:\s*(fail|hardfail)/i', $raw_headers)) {
                $penalty += 40;
                $reasons[] = "SPF authentication hard fail";
            } elseif (preg_match('/Received-SPF:\s*softfail/i', $raw_headers)) {
                $penalty += 20;
                $reasons[] = "SPF authentication softfail";
            }

            // 3. DKIM Failure
            if (preg_match('/dkim=fail/i', $raw_headers)) {
                $penalty += 30;
                $reasons[] = "DKIM signature verification failed";
            }

            // 4. DMARC Failure
            if (preg_match('/dmarc=(fail|reject)/i', $raw_headers)) {
                $penalty += 40;
                $reasons[] = "DMARC policy check failed";
            }

            // 5. MTA / SpamAssassin Existing Flag
            if (preg_match('/X-Spam-Flag:\s*YES/i', $raw_headers)) {
                $penalty += 50;
                $reasons[] = "Server mail filter flagged message as spam (X-Spam-Flag: YES)";
            }

            // 6. Suspicious attachments in MIME headers
            if (preg_match('/filename=[\'"]?[^\'"]+\.(exe|scr|bat|cmd|vbs|hta|ps1|iso|img|wsf)[\'"]?/i', $raw_headers)) {
                $penalty += 60;
                $reasons[] = "Dangerous executable / script attachment extension detected";
            }
        }

        // 7. ALL CAPS Subject
        if (strlen($subject) > 8) {
            $caps = strlen(preg_replace('/[^A-Z]/', '', $subject));
            $letters = strlen(preg_replace('/[^a-zA-Z]/', '', $subject));
            if ($letters > 6 && ($caps / $letters) > 0.70) {
                $penalty += 15;
                $reasons[] = "Subject contains excessive ALL CAPS (>70%)";
            }
        }

        // 8. Urgent Phishing / Extortion buzzwords
        $phish_patterns = [
            '/\b(crypto\s+transfer|send\s+bitcoin|btc\s+wallet|transfer\s+funds\s+within\s+24\s*h)\b/i' => 'Urgent crypto extortion pattern',
            '/\b(your\s+account\s+will\s+be\s+deactivated|confirm\s+your\s+credentials|verify\s+your\s+identity\s+now)\b/i' => 'Credential phishing urgency threat',
            '/\b(you\s+have\s+won\s+a\s+free|claim\s+your\s+gift\s+card|lottery\s+winner)\b/i' => 'Prize/Lottery scam pattern',
        ];
        foreach ($phish_patterns as $pattern => $msg) {
            if (preg_match($pattern, $subject . ' ' . $body)) {
                $penalty += 25;
                $reasons[] = $msg;
            }
        }

        return [
            'penalty' => min(100, $penalty),
            'reasons' => $reasons,
        ];
    }

    // =========================================================================
    // Master Classification Decision Engine
    // =========================================================================

    /**
     * Inspects an email message and produces a full anti-spam verdict.
     *
     * @param string $subject
     * @param string $body
     * @param string $raw_headers
     * @param string $sender
     * @param string $user_identifier
     * @param array $config User or plugin configuration
     * @return array Complete classification decision
     */
    public static function check_message(
        string $subject,
        string $body,
        string $raw_headers,
        string $sender,
        string $user_identifier,
        array $config = []
    ): array {
        $whitelist = $config['lifeprisma_ai_spam_whitelist'] ?? [];
        $blacklist = $config['lifeprisma_ai_spam_blacklist'] ?? [];
        $keywords = $config['lifeprisma_ai_spam_keywords'] ?? [];
        
        // Normalize threshold: float <= 1.0 (e.g. 0.85 from config.inc.php) is converted to percentage (85)
        $raw_threshold = $config['lifeprisma_ai_spam_threshold'] ?? 75;
        if (is_numeric($raw_threshold) && (float)$raw_threshold > 0 && (float)$raw_threshold <= 1.0) {
            $threshold = (int) round(((float)$raw_threshold) * 100);
        } else {
            $threshold = (int) ($raw_threshold ?: 75);
        }
        if ($threshold < 50) {
            $threshold = 75; // Sane fallback to prevent false positives
        }

        // 1. Whitelist Check (Instant HAM, bypasses all filters)
        $list_status = self::check_lists($sender, $whitelist, $blacklist);
        if ($list_status === 'whitelisted') {
            return [
                'is_spam'           => false,
                'score'             => 0,
                'threshold'         => $threshold,
                'verdict'           => 'ham',
                'reasons'           => ['Sender is explicitly on Whitelist'],
                'bayes_score'       => 0.0,
                'heuristic_score'   => 0,
                'sender_reputation' => 100,
                'top_tokens'        => [],
            ];
        }

        // 2. Blacklist Check (Instant SPAM 100%)
        if ($list_status === 'blacklisted') {
            return [
                'is_spam'           => true,
                'score'             => 100,
                'threshold'         => $threshold,
                'verdict'           => 'spam',
                'reasons'           => ['Sender is explicitly on Blacklist'],
                'bayes_score'       => 1.0,
                'heuristic_score'   => 100,
                'sender_reputation' => -100,
                'top_tokens'        => [],
            ];
        }

        // 3. Load learned Bayesian model & Sender Reputation
        $model = self::load_model($user_identifier);
        $tokens = self::extract_tokens($subject, $body, $raw_headers, $sender);
        $bayes_result = self::evaluate_bayesian_score($tokens, $model);
        $bayes_prob = $bayes_result['score']; // 0.0 to 1.0
        $sender_rep = self::get_sender_reputation($sender, $model); // -100 to +100

        // 4. Evaluate Heuristics
        $heuristics = self::evaluate_heuristics($subject, $body, $raw_headers, $sender);
        $reasons = $heuristics['reasons'];

        // 5. Custom Trigger Keywords
        $kw_matches = self::check_custom_keywords($subject, $body, $keywords);
        if (!empty($kw_matches)) {
            $heuristics['penalty'] += count($kw_matches) * 20;
            $reasons[] = 'Matched user spam keywords: ' . implode(', ', $kw_matches);
        }

        // 6. Calculate Weighted Combined Spam Score (0 to 100)
        // Neutral bayesian (0.5) must NOT contribute to spam score.
        // Scores > 0.5 contribute linearly (0.5..1.0 -> 0..100 points).
        // Scores < 0.5 provide a ham discount (-50..0 points).
        if ($bayes_prob > 0.5) {
            $bayes_points = ($bayes_prob - 0.5) * 200;
        } else {
            $bayes_points = ($bayes_prob - 0.5) * 100;
        }

        $heuristic_points = $heuristics['penalty'];
        $rep_penalty = (-$sender_rep); // Negative reputation increases spam score

        $combined_score = ($bayes_points * 0.60) +
            ($heuristic_points * 0.30) +
            (max(0, $rep_penalty) * 0.10);

        // If high-confidence Bayesian spam
        if ($bayes_prob >= 0.85) {
            $combined_score = max($combined_score, round(($bayes_prob - 0.5) * 200 * 0.85));
        }
        if ($heuristic_points >= 80) {
            $combined_score = max($combined_score, $heuristic_points);
        }

        // If sender has high positive reputation, provide discount
        if ($sender_rep > 20) {
            $combined_score = max(0, $combined_score - round($sender_rep * 0.25));
        }

        $combined_score = max(0, min(100, (int) round($combined_score)));

        // Add explanation reasons if Bayesian score is high
        if ($bayes_prob >= 0.75) {
            $sig_tokens = array_slice(array_column($bayes_result['top_tokens'], 'token'), 0, 4);
            if (!empty($sig_tokens)) {
                $reasons[] = 'Bayesian learning engine detected spam patterns in tokens: ' . implode(', ', $sig_tokens);
            }
        }

        $is_spam = ($combined_score >= $threshold);

        // Ensure there is always a clear reason when an email is marked as spam
        if ($is_spam && empty($reasons)) {
            $reasons[] = "Combined anti-spam score exceeded threshold ({$combined_score}% >= {$threshold}%)";
        }

        return [
            'is_spam'           => $is_spam,
            'score'             => $combined_score,
            'threshold'         => $threshold,
            'verdict'           => $is_spam ? 'spam' : 'ham',
            'reasons'           => $reasons,
            'bayes_score'       => round($bayes_prob, 4),
            'heuristic_score'   => $heuristics['penalty'],
            'sender_reputation' => $sender_rep,
            'top_tokens'        => $bayes_result['top_tokens'],
        ];
    }

    // =========================================================================
    // Bi-directional Continuous Learning Engine
    // =========================================================================

    /**
     * Trains the model that this message is SPAM.
     * Increments spam token counts and penalizes sender reputation.
     *
     * Supports both:
     * 1. Standard: learn_spam($subject, $body, $raw_headers, $sender, $user_identifier, $message_id)
     * 2. Convenience: learn_spam($body, $headers, $message_id)
     */
    public static function learn_spam(
        $arg1,
        $arg2 = '',
        $arg3 = '',
        $arg4 = '',
        $arg5 = '',
        $arg6 = ''
    ): array {
        if (is_array($arg2)) {
            $body = (string) $arg1;
            $headers = $arg2;
            $message_id = (string) $arg3;
            $subject = $headers['subject'] ?? '';
            $sender = $headers['from'] ?? '';
            $raw_headers = '';
            foreach ($headers as $k => $v) {
                $raw_headers .= "{$k}: {$v}\r\n";
            }
            $user_identifier = self::$last_user_identifier;
        } else {
            $subject = (string) $arg1;
            $body = (string) $arg2;
            $raw_headers = (string) $arg3;
            $sender = (string) $arg4;
            $user_identifier = (string) ($arg5 ?: self::$last_user_identifier);
            $message_id = (string) $arg6;
        }

        $model = self::load_model($user_identifier);
        $tokens = self::extract_tokens($subject, $body, $raw_headers, $sender);

        // Check if this message was previously learned as HAM (auto-correction)
        if (!empty($message_id) && isset($model['learned_message_ids'][$message_id])) {
            if ($model['learned_message_ids'][$message_id] === 'ham') {
                // Reverse HAM training
                $model['total_ham'] = max(0, $model['total_ham'] - 1);
                foreach ($tokens as $tok => $weight) {
                    if (isset($model['tokens'][$tok])) {
                        $model['tokens'][$tok]['h'] = max(0, ($model['tokens'][$tok]['h'] ?? 0) - 1);
                    }
                }
            } elseif ($model['learned_message_ids'][$message_id] === 'spam') {
                // Already trained as spam, skip duplicate count
                $model['success'] = true;
                $model['spam_count'] = (int) ($model['total_spam'] ?? 0);
                $model['ham_count'] = (int) ($model['total_ham'] ?? 0);
                return $model;
            }
        }

        $model['total_spam']++;
        foreach ($tokens as $tok => $weight) {
            if (!isset($model['tokens'][$tok])) {
                $model['tokens'][$tok] = ['s' => 0, 'h' => 0];
            }
            $model['tokens'][$tok]['s'] = ($model['tokens'][$tok]['s'] ?? 0) + 1;
        }

        // Adjust sender reputation negatively
        $email = self::extract_email_address($sender);
        if (!empty($email)) {
            $curr = $model['sender_reputation'][$email] ?? 0;
            $model['sender_reputation'][$email] = max(-100, $curr - 15);
        }

        if (!empty($message_id)) {
            $model['learned_message_ids'][$message_id] = 'spam';
        }

        self::save_model($user_identifier, $model);
        $model['success'] = true;
        $model['spam_count'] = (int) ($model['total_spam'] ?? 0);
        $model['ham_count'] = (int) ($model['total_ham'] ?? 0);
        return $model;
    }

    /**
     * Trains the model that this message is NOT SPAM (HAM).
     * If reversing previous spam training, un-trains spam tokens and trains ham.
     *
     * Supports both:
     * 1. Standard: learn_ham($subject, $body, $raw_headers, $sender, $user_identifier, $message_id, $reverse_spam)
     * 2. Convenience: learn_ham($body, $headers, $reverse_spam, $message_id)
     */
    public static function learn_ham(
        $arg1,
        $arg2 = '',
        $arg3 = '',
        $arg4 = '',
        $arg5 = '',
        $arg6 = '',
        $arg7 = false
    ): array {
        if (is_array($arg2)) {
            $body = (string) $arg1;
            $headers = $arg2;
            $reverse_spam = is_bool($arg3) ? $arg3 : false;
            $message_id = is_string($arg3) ? $arg3 : (string) $arg4;
            $subject = $headers['subject'] ?? '';
            $sender = $headers['from'] ?? '';
            $raw_headers = '';
            foreach ($headers as $k => $v) {
                $raw_headers .= "{$k}: {$v}\r\n";
            }
            $user_identifier = self::$last_user_identifier;
        } else {
            $subject = (string) $arg1;
            $body = (string) $arg2;
            $raw_headers = (string) $arg3;
            $sender = (string) $arg4;
            $user_identifier = (string) ($arg5 ?: self::$last_user_identifier);
            $message_id = (string) $arg6;
            $reverse_spam = is_bool($arg7) ? $arg7 : false;
        }

        $model = self::load_model($user_identifier);
        $tokens = self::extract_tokens($subject, $body, $raw_headers, $sender);

        // Auto-correction: Reverse SPAM training if previously learned as spam
        $was_spam = false;
        if (!empty($message_id) && isset($model['learned_message_ids'][$message_id])) {
            $was_spam = ($model['learned_message_ids'][$message_id] === 'spam');
            if ($model['learned_message_ids'][$message_id] === 'ham') {
                // Already trained as ham, skip duplicate count (idempotent)
                $model['success'] = true;
                $model['spam_count'] = (int) ($model['total_spam'] ?? 0);
                $model['ham_count'] = (int) ($model['total_ham'] ?? 0);
                return $model;
            }
        } elseif ($reverse_spam) {
            $was_spam = true;
        }

        if ($was_spam) {
            $model['total_spam'] = max(0, $model['total_spam'] - 1);
            foreach ($tokens as $tok => $weight) {
                if (isset($model['tokens'][$tok])) {
                    $model['tokens'][$tok]['s'] = max(0, ($model['tokens'][$tok]['s'] ?? 0) - 1);
                }
            }
        }

        $model['total_ham']++;
        foreach ($tokens as $tok => $weight) {
            if (!isset($model['tokens'][$tok])) {
                $model['tokens'][$tok] = ['s' => 0, 'h' => 0];
            }
            $model['tokens'][$tok]['h'] = ($model['tokens'][$tok]['h'] ?? 0) + 1;
        }

        // Adjust sender reputation positively
        $email = self::extract_email_address($sender);
        if (!empty($email)) {
            $curr = $model['sender_reputation'][$email] ?? 0;
            $model['sender_reputation'][$email] = min(100, $curr + 15);
        }

        if (!empty($message_id)) {
            $model['learned_message_ids'][$message_id] = 'ham';
        }

        self::save_model($user_identifier, $model);
        $model['success'] = true;
        $model['spam_count'] = (int) ($model['total_spam'] ?? 0);
        $model['ham_count'] = (int) ($model['total_ham'] ?? 0);
        return $model;
    }

    /**
     * Retrieves statistics about the user's learned model.
     */
    public static function get_stats(?string $user_identifier = null): array
    {
        $user_identifier = $user_identifier ?: self::$last_user_identifier;
        $model = self::load_model($user_identifier);
        $baseline_count = count(self::$baseline_tokens);
        $total_tokens = count($model['tokens']) + $baseline_count;
        $total_spam = (int) ($model['total_spam'] ?? 0);
        $total_ham = (int) ($model['total_ham'] ?? 0);
        $total_messages = $total_spam + $total_ham;

        $ratio = ($total_messages > 0)
            ? round(($total_spam / $total_messages) * 100, 1)
            : 0;

        return [
            'user'           => $user_identifier,
            'total_spam'     => $total_spam,
            'total_ham'      => $total_ham,
            'spam_messages'  => $total_spam,
            'ham_messages'   => $total_ham,
            'total_messages' => $total_messages,
            'total_tokens'   => $total_tokens,
            'learned_tokens' => count($model['tokens']),
            'spam_ratio'     => $ratio,
            'updated_at'     => $model['updated_at'] ?? time(),
            'storage_file'   => self::get_model_path($user_identifier),
        ];
    }
}
