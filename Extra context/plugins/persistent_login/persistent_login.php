<?php

/**
 * Persistent Login ("Remember Me" / "Stay Signed In") Plugin for Roundcube
 *
 * Enterprise-grade persistent authentication featuring:
 * - OWASP / Paragonie split-token architecture (selector/series + verifier)
 * - Cryptographic hash storage (SHA-256) of verifiers
 * - Automatic verifier rotation on each auto-login
 * - Cookie theft detection with instant user-wide session invalidation
 * - Authenticated AES-256 encryption for IMAP credentials
 * - Multi-engine database support (MySQL, MariaDB, PostgreSQL, SQLite)
 * - Seamless skin integration (Elastic, Gmail Plus, Larry, Classic)
 * - User device management in Settings -> Preferences -> Trusted Devices
 * - Single-click session revocation and "Sign out all other devices"
 * - Compatibility with Two-Factor Authentication (twofactor_auth)
 *
 * @license MIT
 * @author Webdotpulse & Contributors
 */

declare(strict_types=1);

class persistent_login extends rcube_plugin
{
    public $task = 'login|mail|settings|logout';

    private rcmail $rcmail;
    private string $tableName = 'persistent_logins';
    private ?array $autoLoginData = null;
    private ?string $currentSeries = null;
    private bool $cookieCleared = false;
    private ?array $pendingAuth = null;

    public function __construct($api = null)
    {
        if ($api !== null && is_subclass_of($this, 'rcube_plugin') && method_exists('rcube_plugin', '__construct')) {
            parent::__construct($api);
        }
        if (class_exists('rcmail', false)) {
            $this->rcmail = rcmail::get_instance();
        }
    }

    public function init(): void
    {
        if (!isset($this->rcmail)) {
            $this->rcmail = rcmail::get_instance();
        }
        $this->load_config();
        $this->add_texts('localization/', true);

        // Core authentication & session lifecycle hooks
        $this->add_hook('startup', [$this, 'hook_startup']);
        $this->add_hook('authenticate', [$this, 'hook_authenticate']);
        $this->add_hook('login_after', [$this, 'hook_login_after']);
        $this->add_hook('login_failed', [$this, 'hook_login_failed']);
        $this->add_hook('logout_actions', [$this, 'hook_logout_actions']);
        $this->add_hook('logout_after', [$this, 'hook_logout_after']);

        // UI injection hooks for login page
        $this->add_hook('template_container', [$this, 'hook_template_container']);
        $this->add_hook('render_page', [$this, 'hook_render_page']);

        // Settings / Preferences integration
        $this->add_hook('preferences_sections_list', [$this, 'hook_preferences_sections_list']);
        $this->add_hook('preferences_list', [$this, 'hook_preferences_list']);
        $this->add_hook('preferences_save', [$this, 'hook_preferences_save']);

        // Register plugin actions (AJAX endpoints)
        $this->register_action('plugin.persistent_login-revoke', [$this, 'action_revoke']);
        $this->register_action('plugin.persistent_login-revoke-all', [$this, 'action_revoke_all']);
        $this->register_action('plugin.persistent_login-sessions', [$this, 'action_sessions']);

        // Include CSS & JS assets on relevant screens
        if ($this->rcmail->task === 'login' || $this->rcmail->task === 'settings') {
            $this->include_stylesheet('persistent_login.css');
            $this->include_script('persistent_login.js');

            if ($this->rcmail->task === 'login') {
                $defaultChecked = (bool)$this->rcmail->config->get('persistent_login_default_checked', false);
                $this->rcmail->output->set_env('persistent_login_default', $defaultChecked);
                $this->rcmail->output->add_script(
                    'window.rcmail_persistent_login_default = ' . ($defaultChecked ? 'true' : 'false') . ';',
                    'head_top'
                );
            }
        }
    }

    // =========================================================================
    // Lifecycle Hooks
    // =========================================================================

    /**
     * Startup Gatekeeper: Detects persistent cookie or handles logout.
     */
    public function hook_startup(array $args): array
    {
        $task = $args['task'] ?? $this->rcmail->task;
        $action = $args['action'] ?? $this->rcmail->action;

        // 1. Intercept user-initiated logout
        if ($task === 'logout' || $action === 'logout') {
            $this->handle_logout_revocation();
            return $args;
        }

        // 2. If user is already authenticated in session
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : (!empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
        if ($userId > 0) {
            // Finalize any pending persistent token creation
            if (!empty($_SESSION['persistent_login_pending']) || !empty($_SESSION['persistent_login_auth']) || !empty($this->pendingAuth)) {
                $this->finalize_pending_login($userId);
            }

            $cookieValue = $this->get_cookie();
            if ($cookieValue) {
                $parsed = $this->parse_cookie_value($cookieValue);
                if ($parsed) {
                    $this->currentSeries = $parsed['series'];
                }
            }
            return $args;
        }

        // 3. User is not authenticated — check for persistent login cookie
        $cookieValue = $this->get_cookie();
        if (!$cookieValue) {
            return $args;
        }

        $tokenRecord = $this->validate_token($cookieValue);

        if (!empty($tokenRecord['theft'])) {
            // Token theft detected: user was wiped of all sessions
            $this->clear_cookie();
            if ($this->rcmail->output) {
                $this->rcmail->output->show_message('persistent_login.token_theft_detected', 'error');
            }
            return $args;
        }

        if ($tokenRecord && !empty($tokenRecord['user_name']) && !empty($tokenRecord['password'])) {
            // Valid token found! Queue credentials for auto-login
            $this->autoLoginData = $tokenRecord;
            $this->currentSeries = $tokenRecord['series'];

            // Direct Roundcube into the login action
            $args['action'] = 'login';

            // Interop: Mark 2FA as trusted if enabled in config
            if ($this->rcmail->config->get('persistent_login_trust_2fa', true)) {
                $_SESSION['2fa_trusted_device'] = true;
                unset($_SESSION['2fa_pending']);
            }
        } else {
            // Cookie exists but token was invalid or expired
            $this->clear_cookie();
        }

        return $args;
    }

    /**
     * Authenticate Hook: Injects credentials during auto-login or captures form checkbox & credentials.
     */
    public function hook_authenticate(array $args): array
    {
        if (!empty($args['user']) && !empty($args['pass'])) {
            $this->autoLoginData = null;
        }

        if ($this->autoLoginData) {
            // Supplying validated persistent credentials to Roundcube
            $args['user'] = $this->autoLoginData['user_name'];
            $args['pass'] = $this->autoLoginData['password'];
            $args['host'] = $this->autoLoginData['host'];
            $args['cookiecheck'] = false;
            $args['valid'] = true;
        } else {
            // Manual form submission: check if user requested "Keep me logged in"
            $rememberPost = !empty($_POST['_persistent_login'])
                || !empty($_POST['_remember_me'])
                || !empty($_POST['_ifpl'])
                || !empty($_POST['remember'])
                || !empty($_POST['rememberme'])
                || !empty(rcube_utils::get_input_value('_persistent_login', rcube_utils::INPUT_POST))
                || !empty(rcube_utils::get_input_value('_remember_me', rcube_utils::INPUT_POST))
                || !empty(rcube_utils::get_input_value('_ifpl', rcube_utils::INPUT_POST));

            if ($rememberPost) {
                $user = (string)($args['user'] ?? rcube_utils::get_input_value('_user', rcube_utils::INPUT_POST) ?? '');
                $pass = (string)($args['pass'] ?? rcube_utils::get_input_value('_pass', rcube_utils::INPUT_POST) ?? '');
                $host = (string)($args['host'] ?? rcube_utils::get_input_value('_host', rcube_utils::INPUT_POST) ?? '');

                $this->pendingAuth = [
                    'user' => $user,
                    'pass' => $pass,
                    'host' => $host,
                ];

                $_SESSION['persistent_login_pending'] = true;
                $_SESSION['persistent_login_remember'] = true;
                if ($pass !== '') {
                    $_SESSION['persistent_login_auth'] = [
                        'user' => $user,
                        'pass' => $this->encrypt_password($pass),
                        'host' => $host,
                    ];
                }
            } else {
                unset($_SESSION['persistent_login_pending'], $_SESSION['persistent_login_remember'], $_SESSION['persistent_login_auth']);
                $this->pendingAuth = null;
            }
        }

        return $args;
    }

    /**
     * Login After Hook: Finalizes token creation or performs token rotation.
     */
    public function hook_login_after(array $args): array
    {
        $userId = !empty($args['user_id'])
            ? (int)$args['user_id']
            : ($this->rcmail->user ? (int)$this->rcmail->user->ID : (!empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0));

        if ($userId <= 0) {
            return $args;
        }

        if ($this->autoLoginData) {
            // Case A: User authenticated automatically via persistent login
            $series = $this->autoLoginData['series'];

            if ($this->rcmail->config->get('persistent_login_rotate_token', true)) {
                // Rotate verifier on each successful auto-login
                $this->rotate_token($series, $userId);
            } else {
                // Update last_used timestamp
                $this->touch_token($series, $userId);
            }

            // Interop with twofactor_auth: bypass challenge on trusted persistent device
            if ($this->rcmail->config->get('persistent_login_trust_2fa', true)) {
                unset($_SESSION['2fa_pending']);
            }

            // Periodic garbage collection (1% probability per auto-login)
            if (random_int(1, 100) === 1) {
                $this->gc();
            }

            $this->autoLoginData = null;
        } elseif (!empty($_SESSION['persistent_login_pending']) || !empty($this->pendingAuth) || !empty($args['pass'])) {
            // Case B: User authenticated manually and opted in to "Keep me logged in"
            $username = (string)($args['user'] ?? '');
            $password = (string)($args['pass'] ?? '');
            $host = (string)($args['host'] ?? '');

            if ($username !== '' && $password !== '') {
                unset($_SESSION['persistent_login_pending'], $_SESSION['persistent_login_auth']);
                $this->pendingAuth = null;
                $this->currentSeries = $this->create_token($userId, $username, $password, $host);
            } else {
                $this->finalize_pending_login($userId);
            }
        }

        return $args;
    }

    /**
     * Finalizes pending persistent login credentials and creates token & cookie.
     */
    public function finalize_pending_login(int $userId): ?string
    {
        if ($userId <= 0) {
            return null;
        }

        $username = '';
        $password = '';
        $host = '';

        if (!empty($this->pendingAuth)) {
            $username = (string)($this->pendingAuth['user'] ?? '');
            $password = (string)($this->pendingAuth['pass'] ?? '');
            $host = (string)($this->pendingAuth['host'] ?? '');
        }

        if (($username === '' || $password === '') && !empty($_SESSION['persistent_login_auth'])) {
            $sessAuth = $_SESSION['persistent_login_auth'];
            if ($username === '' && !empty($sessAuth['user'])) {
                $username = (string)$sessAuth['user'];
            }
            if ($password === '' && !empty($sessAuth['pass'])) {
                $password = (string)$this->decrypt_password($sessAuth['pass']);
            }
            if ($host === '' && !empty($sessAuth['host'])) {
                $host = (string)$sessAuth['host'];
            }
        }

        if ($username === '') {
            $username = $this->rcmail->user ? (string)$this->rcmail->user->get_username() : (string)($_SESSION['username'] ?? '');
        }

        if ($password === '') {
            if (!empty($_SESSION['password']) && method_exists($this->rcmail, 'decrypt')) {
                $password = (string)$this->rcmail->decrypt($_SESSION['password']);
            } elseif (!empty($_POST['_pass'])) {
                $password = (string)$_POST['_pass'];
            }
        }

        if ($host === '') {
            $host = (string)($this->rcmail->config->get('default_host', '') ?: ($_SESSION['storage_host'] ?? ''));
        }

        unset($_SESSION['persistent_login_pending'], $_SESSION['persistent_login_auth']);
        $this->pendingAuth = null;

        if ($username !== '' && $password !== '') {
            $series = $this->create_token($userId, $username, $password, $host);
            if ($series) {
                $this->currentSeries = $series;
            }
            return $series;
        }

        return null;
    }

    /**
     * Login Failed Hook: Cleans up invalid tokens if credentials were rejected.
     */
    public function hook_login_failed(array $args): array
    {
        if ($this->autoLoginData) {
            // Password changed on server or credentials invalidated
            $this->revoke_token($this->autoLoginData['series']);
            $this->clear_cookie();
            $this->autoLoginData = null;

            if ($this->rcmail->output) {
                $this->rcmail->output->show_message('persistent_login.login_session_expired', 'warning');
            }
        }

        return $args;
    }

    /**
     * Logout Actions Hook: Invoked when user logs out.
     */
    public function hook_logout_actions(array $args): array
    {
        $this->handle_logout_revocation();
        return $args;
    }

    /**
     * Logout After Hook: Secondary logout handler.
     */
    public function hook_logout_after(array $args): array
    {
        $this->handle_logout_revocation();
        return $args;
    }

    private function handle_logout_revocation(): void
    {
        if ($this->cookieCleared) {
            return;
        }

        $cookieValue = $this->get_cookie();
        if ($cookieValue) {
            $parsed = $this->parse_cookie_value($cookieValue);
            if ($parsed && !empty($parsed['series'])) {
                $this->revoke_token($parsed['series']);
            }
        }

        $this->clear_cookie();
        $this->cookieCleared = true;
        $this->autoLoginData = null;
        $this->currentSeries = null;
        $this->pendingAuth = null;
    }

    // =========================================================================
    // UI Template Hooks
    // =========================================================================

    /**
     * Injects checkbox into Roundcube container tags (loginform / loginfooter).
     */
    public function hook_template_container(array $args): array
    {
        if ($args['name'] === 'loginform' || $args['name'] === 'loginfooter') {
            $checkboxHtml = $this->render_checkbox_html();
            $args['content'] = ($args['content'] ?? '') . $checkboxHtml;
        }

        return $args;
    }

    /**
     * Fallback server-side HTML injection for login template.
     */
    public function hook_render_page(array $args): array
    {
        if (($args['template'] ?? '') === 'login') {
            $html = $args['content'] ?? '';

            // If not already injected, insert before submit button
            if (!str_contains($html, 'rcmfd_persistent_login')) {
                $checkboxHtml = $this->render_checkbox_html();
                $pattern = '/(<(?:button|input)[^>]+type=[\'"]submit[\'"][^>]*>)/i';
                if (preg_match($pattern, $html)) {
                    $html = preg_replace($pattern, $checkboxHtml . "\n$1", $html, 1);
                    $args['content'] = $html;
                }
            }
        }

        return $args;
    }

    private function render_checkbox_html(): string
    {
        $checked = (bool)$this->rcmail->config->get('persistent_login_default_checked', false) ? ' checked="checked"' : '';
        $labelText = htmlspecialchars($this->gettext('remember_me'), ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<div class="persistent-login-group form-group">' .
            '<label class="persistent-login-label" for="rcmfd_persistent_login">' .
            '<input type="checkbox" name="_persistent_login" id="rcmfd_persistent_login" value="1" class="persistent-login-checkbox"%s>' .
            '<span>%s</span>' .
            '</label>' .
            '</div>',
            $checked,
            $labelText
        );
    }

    // =========================================================================
    // Preferences & Trusted Devices Settings Hooks
    // =========================================================================

    /**
     * Adds section under Settings -> Preferences.
     */
    public function hook_preferences_sections_list(array $args): array
    {
        $args['list']['persistent_login'] = [
            'id' => 'persistent_login',
            'section' => 'persistent_login',
            'name' => $this->gettext('trusted_devices'),
        ];

        return $args;
    }

    /**
     * Renders Trusted Devices table and options in Settings -> Preferences.
     */
    public function hook_preferences_list(array $args): array
    {
        if ($args['section'] !== 'persistent_login') {
            return $args;
        }

        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : (!empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
        $username = $this->rcmail->user ? (string)$this->rcmail->user->get_username() : (!empty($_SESSION['username']) ? (string)$_SESSION['username'] : '');
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $sessions = $this->get_user_sessions($userId, $username);

        // Auto-heal / auto-register session if currently logged in with remember me on
        $cookieName = (string)$this->rcmail->config->get('persistent_login_cookie_name', '_rc_persistent_login');
        if (empty($sessions) && $userId > 0 && (!empty($_COOKIE[$cookieName]) || !empty($_SESSION['persistent_login_remember']) || !empty($_SESSION['persistent_login_pending']) || !empty($_SESSION['password']))) {
            $this->finalize_pending_login($userId);
            $sessions = $this->get_user_sessions($userId, $username);
        }

        // Identify current session series
        $currentSeries = $this->currentSeries;
        if (!$currentSeries) {
            $cookieVal = $this->get_cookie();
            if ($cookieVal) {
                $parsed = $this->parse_cookie_value($cookieVal);
                $currentSeries = $parsed['series'] ?? null;
            }
        }
        if (!$currentSeries && count($sessions) === 1) {
            $currentSeries = $sessions[0]['series'];
            $this->currentSeries = $currentSeries;
        }

        $tableHtml = '<div id="persistent-sessions-wrapper" class="persistent-sessions-container">';

        if (count($sessions) > 1) {
            $tableHtml .= '<div class="persistent-sessions-toolbar">';
            $tableHtml .= sprintf(
                '<button type="button" id="btn-revoke-all-other" class="btn-revoke-all">%s</button>',
                htmlspecialchars($this->gettext('revoke_all_other'), ENT_QUOTES, 'UTF-8')
            );
            $tableHtml .= '</div>';
        }

        $tableHtml .= '<table class="persistent-sessions-table">';
        $tableHtml .= '<thead><tr>';
        $tableHtml .= '<th>' . htmlspecialchars($this->gettext('device') ?: 'Device', ENT_QUOTES, 'UTF-8') . '</th>';
        $tableHtml .= '<th>' . htmlspecialchars($this->gettext('ip_address'), ENT_QUOTES, 'UTF-8') . '</th>';
        $tableHtml .= '<th>' . htmlspecialchars($this->gettext('first_login'), ENT_QUOTES, 'UTF-8') . '</th>';
        $tableHtml .= '<th>' . htmlspecialchars($this->gettext('last_active'), ENT_QUOTES, 'UTF-8') . '</th>';
        $tableHtml .= '<th style="text-align: right;">' . htmlspecialchars($this->gettext('revoke'), ENT_QUOTES, 'UTF-8') . '</th>';
        $tableHtml .= '</tr></thead><tbody>';

        if (empty($sessions)) {
            $tableHtml .= '<tr><td colspan="5" class="text-center text-muted p-4">' .
                          htmlspecialchars($this->gettext('no_active_sessions'), ENT_QUOTES, 'UTF-8') .
                          '</td></tr>';
        } else {
            foreach ($sessions as $s) {
                $isCurrent = ($currentSeries !== null && hash_equals($currentSeries, $s['series']));
                $uaInfo = $this->parse_user_agent($s['user_agent'] ?? '');

                $rowClass = $isCurrent ? 'session-row-current' : '';
                $currentBadge = $isCurrent
                    ? ' <span class="persistent-badge badge-current-device">' . htmlspecialchars($this->gettext('current_device'), ENT_QUOTES, 'UTF-8') . '</span>'
                    : '';

                $createdFormatted = date('M j, Y H:i', strtotime($s['created']));
                $lastUsedFormatted = date('M j, Y H:i', strtotime($s['last_used']));

                $tableHtml .= sprintf(
                    '<tr id="session-row-%s" class="%s">' .
                    '<td>' .
                    '<div class="session-device-title">%s %s%s</div>' .
                    '<div class="session-device-meta">%s</div>' .
                    '</td>' .
                    '<td><code>%s</code></td>' .
                    '<td>%s</td>' .
                    '<td>%s</td>' .
                    '<td style="text-align: right;">' .
                    '<button type="button" class="btn-revoke-session" data-series="%s">%s</button>' .
                    '</td>' .
                    '</tr>',
                    htmlspecialchars($s['series'], ENT_QUOTES, 'UTF-8'),
                    $rowClass,
                    htmlspecialchars($uaInfo['icon'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($uaInfo['device_name'], ENT_QUOTES, 'UTF-8'),
                    $currentBadge,
                    htmlspecialchars($uaInfo['browser_os'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($s['ip_address'] ?: '—', ENT_QUOTES, 'UTF-8'),
                    $createdFormatted,
                    $lastUsedFormatted,
                    htmlspecialchars($s['series'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($this->gettext('revoke'), ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $tableHtml .= '</tbody></table></div>';

        $options = [
            'trusted_sessions_list' => [
                'content' => $tableHtml,
            ],
        ];

        // User configurable duration option
        if ($this->rcmail->config->get('persistent_login_user_can_choose_duration', true)) {
            $allowed = $this->rcmail->config->get('persistent_login_allowed_durations', [7, 14, 30, 60, 90]);
            $currentLifetime = (int)$this->rcmail->config->get('persistent_login_lifetime', 30);
            $userLifetime = (int)($this->rcmail->user ? $this->rcmail->user->get_prefs()['persistent_login_lifetime'] ?? $currentLifetime : $currentLifetime);

            $select = new html_select(['name' => '_persistent_login_lifetime', 'id' => 'rcmfd_persistent_lifetime']);
            foreach ($allowed as $days) {
                $select->add(sprintf($this->gettext('days_count'), $days), $days);
            }

            $options['persistent_login_duration'] = [
                'title' => html::label('rcmfd_persistent_lifetime', htmlspecialchars($this->gettext('lifetime_setting'), ENT_QUOTES, 'UTF-8')),
                'content' => $select->show($userLifetime),
            ];
        }

        $args['blocks']['persistent_login'] = [
            'name' => $this->gettext('trusted_devices'),
            'options' => $options,
        ];

        return $args;
    }

    /**
     * Saves user configurable persistent login duration preference.
     */
    public function hook_preferences_save(array $args): array
    {
        if ($args['section'] === 'persistent_login') {
            if ($this->rcmail->config->get('persistent_login_user_can_choose_duration', true)) {
                $days = (int)rcube_utils::get_input_value('_persistent_login_lifetime', rcube_utils::INPUT_POST);
                $allowed = $this->rcmail->config->get('persistent_login_allowed_durations', [7, 14, 30, 60, 90]);
                if (in_array($days, $allowed, true)) {
                    $args['prefs']['persistent_login_lifetime'] = $days;
                }
            }
        }

        return $args;
    }

    // =========================================================================
    // Plugin Action Endpoints (AJAX)
    // =========================================================================

    /**
     * Action: Revokes a single session by series.
     */
    public function action_revoke(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $series = (string)rcube_utils::get_input_value('_series', rcube_utils::INPUT_POST);
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;

        if ($userId <= 0 || $series === '') {
            $this->json_response(['success' => false, 'message' => 'Invalid parameters.']);
        }

        // Verify session belongs to user
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $res = $db->query("SELECT user_id FROM {$this->tableName} WHERE series = ?", $series);
        $row = $db->fetch_assoc($res);

        if (!$row || (int)$row['user_id'] !== $userId) {
            $this->json_response(['success' => false, 'message' => 'Session not found.']);
        }

        $this->revoke_token($series);

        // If revoking current device session, clear cookie
        $cookieVal = $this->get_cookie();
        if ($cookieVal) {
            $parsed = $this->parse_cookie_value($cookieVal);
            if ($parsed && hash_equals($parsed['series'], $series)) {
                $this->clear_cookie();
            }
        }

        $this->json_response([
            'success' => true,
            'message' => $this->gettext('session_revoked'),
        ]);
    }

    /**
     * Action: Revokes all other sessions except the current device.
     */
    public function action_revoke_all(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : 0;
        if ($userId <= 0) {
            $this->json_response(['success' => false, 'message' => 'Authentication required.']);
        }

        $cookieVal = $this->get_cookie();
        $currentSeries = null;
        if ($cookieVal) {
            $parsed = $this->parse_cookie_value($cookieVal);
            $currentSeries = $parsed['series'] ?? null;
        }

        $this->revoke_all_user_tokens($userId, $currentSeries);

        $this->json_response([
            'success' => true,
            'message' => $this->gettext('all_sessions_revoked'),
        ]);
    }

    /**
     * Action: Returns active sessions list in JSON format.
     */
    public function action_sessions(): void
    {
        $userId = $this->rcmail->user ? (int)$this->rcmail->user->ID : (!empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
        $username = $this->rcmail->user ? (string)$this->rcmail->user->get_username() : (!empty($_SESSION['username']) ? (string)$_SESSION['username'] : '');
        if ($userId <= 0 && $username === '') {
            $this->json_response(['success' => false, 'sessions' => []]);
        }

        $sessions = $this->get_user_sessions($userId, $username);
        $this->json_response(['success' => true, 'sessions' => $sessions]);
    }

    // =========================================================================
    // Core Cryptographic & Token Operations
    // =========================================================================

    /**
     * Creates a new split-token persistent login session and sets the client cookie.
     */
    public function create_token(int $userId, string $username, string $password, string $host): ?string
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        // Enforce max concurrent tokens per user
        $maxTokens = (int)$this->rcmail->config->get('persistent_login_max_tokens_per_user', 10);
        $this->enforce_token_limit($userId, $maxTokens);

        // 1. Generate selector (series) and verifier
        $series = bin2hex(random_bytes(16));    // 32 chars hex
        $verifier = bin2hex(random_bytes(32));  // 64 chars hex

        // 2. Hash verifier using SHA-256
        $tokenHash = hash('sha256', $verifier);

        // 3. Encrypt user password using authenticated encryption
        $encryptedPass = $this->encrypt_password($password);

        // 4. Calculate expiration timestamp
        $lifetimeDays = $this->get_user_lifetime($userId);
        $expires = date('Y-m-d H:i:s', time() + ($lifetimeDays * 86400));
        $now = date('Y-m-d H:i:s');

        $ip = $this->get_client_ip();
        $userAgent = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);

        // 5. Store in database
        $sql = "INSERT INTO {$this->tableName}
                (series, token_hash, user_id, user_name, user_pass, host, ip_address, user_agent, created, last_used, expires)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $res = $db->query(
            $sql,
            $series,
            $tokenHash,
            $userId,
            $username,
            $encryptedPass,
            $host,
            $ip,
            $userAgent,
            $now,
            $now,
            $expires
        );

        if (!$res) {
            rcube::write_log('persistent_login', "Failed to store persistent token for user {$username}");
            return null;
        }

        // 6. Set client-side persistent cookie
        $this->set_cookie($series, $verifier, time() + ($lifetimeDays * 86400));
        $this->cookieCleared = false;

        return $series;
    }

    /**
     * Validates a client cookie value against the database.
     * Returns the token record on success, or an array with ['theft' => true] on replay attack.
     */
    public function validate_token(string $cookieValue): ?array
    {
        $parsed = $this->parse_cookie_value($cookieValue);
        if (!$parsed) {
            return null;
        }

        $series = $parsed['series'];
        $verifier = $parsed['verifier'];

        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $res = $db->query("SELECT * FROM {$this->tableName} WHERE series = ?", $series);
        $record = $db->fetch_assoc($res);

        if (!$record) {
            return null;
        }

        // Check if session has expired
        if (strtotime($record['expires']) <= time()) {
            $this->revoke_token($series);
            return null;
        }

        // Constant-time comparison of verifier hash
        $expectedHash = $record['token_hash'];
        $actualHash = hash('sha256', $verifier);

        if (!hash_equals($expectedHash, $actualHash)) {
            // CRITICAL THEFT DETECTION:
            // A valid series was provided with an invalid verifier. This indicates
            // that a previous verifier was replayed or the cookie was intercepted.
            // Action: Invalidate ALL active sessions for this user immediately!
            $userId = (int)$record['user_id'];
            $username = $record['user_name'];
            $this->revoke_all_user_tokens($userId);

            rcube::write_log(
                'persistent_login',
                "SECURITY ALERT: Token verifier mismatch on series '{$series}' for user '{$username}'. Potential stolen cookie attack. Revoked all persistent sessions for user ID {$userId}."
            );

            return ['theft' => true, 'user_id' => $userId];
        }

        // Optional IP verification
        if ($this->rcmail->config->get('persistent_login_check_ip', false)) {
            $currentIp = $this->get_client_ip();
            if ($record['ip_address'] !== '' && !hash_equals($record['ip_address'], $currentIp)) {
                rcube::write_log('persistent_login', "Session rejected: IP mismatch ({$record['ip_address']} vs {$currentIp})");
                return null;
            }
        }

        // Optional User-Agent verification
        if ($this->rcmail->config->get('persistent_login_check_user_agent', true)) {
            $currentUserAgent = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
            if ($record['user_agent'] !== '' && !hash_equals($record['user_agent'], $currentUserAgent)) {
                rcube::write_log('persistent_login', "Session rejected: User-Agent mismatch");
                return null;
            }
        }

        // Decrypt password
        $decryptedPassword = $this->decrypt_password($record['user_pass']);
        if ($decryptedPassword === null) {
            rcube::write_log('persistent_login', "Failed to decrypt password for user '{$record['user_name']}'");
            return null;
        }

        $record['password'] = $decryptedPassword;

        return $record;
    }

    /**
     * Rotates the verifier on an active session (rolling session architecture).
     */
    public function rotate_token(string $series, int $userId): ?string
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        // Generate new high-entropy verifier
        $newVerifier = bin2hex(random_bytes(32));
        $newTokenHash = hash('sha256', $newVerifier);

        $lifetimeDays = $this->get_user_lifetime($userId);
        $newExpires = date('Y-m-d H:i:s', time() + ($lifetimeDays * 86400));
        $now = date('Y-m-d H:i:s');
        $ip = $this->get_client_ip();

        $sql = "UPDATE {$this->tableName}
                SET token_hash = ?, last_used = ?, expires = ?, ip_address = ?
                WHERE series = ?";

        $db->query($sql, $newTokenHash, $now, $newExpires, $ip, $series);

        // Re-issue cookie with same series and new verifier
        $this->set_cookie($series, $newVerifier, time() + ($lifetimeDays * 86400));

        return $newVerifier;
    }

    /**
     * Updates last_used timestamp and extends expiry without rotating verifier.
     */
    public function touch_token(string $series, int $userId): void
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $lifetimeDays = $this->get_user_lifetime($userId);
        $newExpires = date('Y-m-d H:i:s', time() + ($lifetimeDays * 86400));
        $now = date('Y-m-d H:i:s');
        $ip = $this->get_client_ip();

        $sql = "UPDATE {$this->tableName}
                SET last_used = ?, expires = ?, ip_address = ?
                WHERE series = ?";

        $db->query($sql, $now, $newExpires, $ip, $series);
    }

    /**
     * Revokes a single token session from the database.
     */
    public function revoke_token(string $series): bool
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $db->query("DELETE FROM {$this->tableName} WHERE series = ?", $series);
        return true;
    }

    /**
     * Revokes all active persistent tokens for a user, optionally preserving one series.
     */
    public function revoke_all_user_tokens(int $userId, ?string $exceptSeries = null): bool
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        if ($exceptSeries !== null && $exceptSeries !== '') {
            $db->query("DELETE FROM {$this->tableName} WHERE user_id = ? AND series != ?", $userId, $exceptSeries);
        } else {
            $db->query("DELETE FROM {$this->tableName} WHERE user_id = ?", $userId);
        }

        return true;
    }

    /**
     * Enforces the maximum concurrent persistent sessions limit per user.
     */
    private function enforce_token_limit(int $userId, int $maxTokens): void
    {
        if ($maxTokens <= 0) {
            return;
        }

        $db = $this->rcmail->get_dbh();
        $res = $db->query(
            "SELECT series FROM {$this->tableName} WHERE user_id = ? ORDER BY last_used DESC",
            $userId
        );

        $seriesList = [];
        while ($row = $db->fetch_assoc($res)) {
            $seriesList[] = $row['series'];
        }

        // If limit exceeded, prune oldest entries
        if (count($seriesList) >= $maxTokens) {
            $toDelete = array_slice($seriesList, $maxTokens - 1);
            foreach ($toDelete as $oldSeries) {
                $db->query("DELETE FROM {$this->tableName} WHERE series = ?", $oldSeries);
            }
        }
    }

    /**
     * Fetches all active sessions for a user.
     */
    public function get_user_sessions(int $userId, string $username = ''): array
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $now = date('Y-m-d H:i:s');
        $sessions = [];

        if ($userId > 0) {
            $res = $db->query(
                "SELECT series, host, ip_address, user_agent, created, last_used, expires
                 FROM {$this->tableName}
                 WHERE user_id = ? AND expires > ?
                 ORDER BY last_used DESC",
                $userId,
                $now
            );
            while ($row = $db->fetch_assoc($res)) {
                $sessions[] = $row;
            }
        }

        // Fallback search by username if no sessions found by user_id
        if (empty($sessions) && $username !== '') {
            $res = $db->query(
                "SELECT series, host, ip_address, user_agent, created, last_used, expires
                 FROM {$this->tableName}
                 WHERE user_name = ? AND expires > ?
                 ORDER BY last_used DESC",
                $username,
                $now
            );
            while ($row = $db->fetch_assoc($res)) {
                $sessions[] = $row;
            }
        }

        return $sessions;
    }

    /**
     * Purges expired tokens from database.
     */
    public function gc(): void
    {
        $db = $this->rcmail->get_dbh();
        $this->ensureTableExists($db);

        $now = date('Y-m-d H:i:s');
        $db->query("DELETE FROM {$this->tableName} WHERE expires <= ?", $now);
    }

    // =========================================================================
    // Cookie Handling & Security
    // =========================================================================

    /**
     * Sets the persistent login cookie with strict security flags.
     */
    public function set_cookie(string $series, string $verifier, int $expires): void
    {
        $cookieName = (string)$this->rcmail->config->get('persistent_login_cookie_name', '_rc_persistent_login');
        $rawPayload = $series . ':' . $verifier;

        // Generate HMAC signature to protect cookie against tampering
        $signature = hash_hmac('sha256', $rawPayload, $this->get_encryption_key());
        $cookieValue = base64_encode($rawPayload . ':' . $signature);

        // Security attributes
        $secureOverride = $this->rcmail->config->get('persistent_login_cookie_secure', null);
        $isSecure = ($secureOverride !== null) ? (bool)$secureOverride : rcube_utils::https_check();

        $sameSite = (string)$this->rcmail->config->get('persistent_login_cookie_samesite', 'Lax');
        $path = (string)$this->rcmail->config->get('session_path', '/');
        $domain = (string)$this->rcmail->config->get('session_domain', '');

        // Set cookie with PHP standard options array
        $options = [
            'expires' => $expires,
            'path' => $path ?: '/',
            'domain' => $domain,
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => $sameSite,
        ];

        @setcookie($cookieName, $cookieValue, $options);
        $_COOKIE[$cookieName] = $cookieValue;
    }

    /**
     * Clears the persistent login cookie from the client browser.
     */
    public function clear_cookie(): void
    {
        $cookieName = (string)$this->rcmail->config->get('persistent_login_cookie_name', '_rc_persistent_login');
        $path = (string)$this->rcmail->config->get('session_path', '/');
        $domain = (string)$this->rcmail->config->get('session_domain', '');

        $options = [
            'expires' => time() - 3600,
            'path' => $path ?: '/',
            'domain' => $domain,
            'secure' => rcube_utils::https_check(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        @setcookie($cookieName, '', $options);
        unset($_COOKIE[$cookieName]);
    }

    private function get_cookie(): ?string
    {
        $cookieName = (string)$this->rcmail->config->get('persistent_login_cookie_name', '_rc_persistent_login');
        return $_COOKIE[$cookieName] ?? null;
    }

    /**
     * Parses and HMAC-verifies cookie value.
     */
    public function parse_cookie_value(string $cookieValue): ?array
    {
        $cookieValue = trim($cookieValue);
        if ($cookieValue === '') {
            return null;
        }

        // Handle URL encoding and spaces converted from '+'
        $raw = str_replace(' ', '+', rawurldecode($cookieValue));
        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            $decoded = base64_decode(str_replace(' ', '+', $cookieValue), true);
        }
        if ($decoded === false) {
            $decoded = base64_decode($cookieValue, true);
        }
        if ($decoded === false) {
            return null;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 3) {
            // Legacy fallback if signature was omitted
            if (count($parts) === 2 && strlen($parts[0]) === 32 && strlen($parts[1]) === 64) {
                return ['series' => $parts[0], 'verifier' => $parts[1]];
            }
            return null;
        }

        [$series, $verifier, $signature] = $parts;

        // Verify selector & verifier format
        if (strlen($series) !== 32 || strlen($verifier) !== 64) {
            return null;
        }

        // Verify HMAC signature
        $expectedSignature = hash_hmac('sha256', $series . ':' . $verifier, $this->get_encryption_key());
        if (!hash_equals($expectedSignature, $signature)) {
            // Fallback: check with default des_key in case persistent_login_secret_key was modified
            $desKey = (string)$this->rcmail->config->get('des_key', 'rcmail-default-salt-key-32-chars!!');
            $fallbackKey = hash('sha256', ':' . $desKey, true);
            $fallbackSig = hash_hmac('sha256', $series . ':' . $verifier, $fallbackKey);
            if (!hash_equals($fallbackSig, $signature)) {
                rcube::write_log('persistent_login', 'Cookie HMAC signature verification failed');
                return null;
            }
        }

        return ['series' => $series, 'verifier' => $verifier];
    }

    // =========================================================================
    // Credential Encryption & Decryption (Authenticated AES-256-GCM / OpenSSL)
    // =========================================================================

    /**
     * Encrypts user password using OpenSSL AES-256-GCM or AES-256-CBC with HMAC.
     */
    public function encrypt_password(string $password): string
    {
        $key = $this->get_encryption_key();

        if (function_exists('openssl_encrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) {
            $iv = random_bytes(12);
            $tag = '';
            $cipher = openssl_encrypt($password, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($cipher !== false) {
                return 'gcm:' . base64_encode($iv . $tag . $cipher);
            }
        }

        // Fallback to Roundcube's native encryption
        return 'rc:' . $this->rcmail->encrypt($password);
    }

    /**
     * Decrypts user password.
     */
    public function decrypt_password(string $ciphertext): ?string
    {
        if (str_starts_with($ciphertext, 'gcm:')) {
            $raw = base64_decode(substr($ciphertext, 4), true);
            if ($raw === false || strlen($raw) < 28) {
                return null;
            }
            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $encrypted = substr($raw, 28);

            $key = $this->get_encryption_key();
            $decrypted = openssl_decrypt($encrypted, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            return ($decrypted !== false) ? $decrypted : null;
        }

        if (str_starts_with($ciphertext, 'rc:')) {
            return $this->rcmail->decrypt(substr($ciphertext, 3));
        }

        // Direct Roundcube decrypt fallback
        return $this->rcmail->decrypt($ciphertext);
    }

    private function get_encryption_key(): string
    {
        $customKey = (string)$this->rcmail->config->get('persistent_login_secret_key', '');
        $desKey = (string)$this->rcmail->config->get('des_key', 'rcmail-default-salt-key-32-chars!!');

        // Derive 32-byte (256-bit) encryption key
        return hash('sha256', $customKey . ':' . $desKey, true);
    }

    private function get_user_lifetime(int $userId): int
    {
        $defaultLifetime = (int)$this->rcmail->config->get('persistent_login_lifetime', 30);
        if ($this->rcmail->config->get('persistent_login_user_can_choose_duration', true) && $this->rcmail->user) {
            $prefs = $this->rcmail->user->get_prefs();
            if (!empty($prefs['persistent_login_lifetime'])) {
                return max(1, min(365, (int)$prefs['persistent_login_lifetime']));
            }
        }

        return max(1, min(365, $defaultLifetime));
    }

    private function get_client_ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }
        return mb_substr($ip, 0, 45);
    }

    // =========================================================================
    // Database Schema Auto-Creation
    // =========================================================================

    /**
     * Automatically creates persistent_logins table if not already present.
     */
    public function ensureTableExists(rcube_db $db): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $provider = $db->db_provider;

        $sql = match ($provider) {
            'sqlite' => "CREATE TABLE IF NOT EXISTS {$this->tableName} (
                series VARCHAR(64) PRIMARY KEY,
                token_hash VARCHAR(128) NOT NULL,
                user_id INTEGER NOT NULL,
                user_name VARCHAR(128) NOT NULL,
                user_pass TEXT NOT NULL,
                host VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL DEFAULT '',
                user_agent VARCHAR(500) NOT NULL DEFAULT '',
                created DATETIME NOT NULL,
                last_used DATETIME NOT NULL,
                expires DATETIME NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_persistent_user_id ON {$this->tableName} (user_id);
            CREATE INDEX IF NOT EXISTS idx_persistent_expires ON {$this->tableName} (expires);",

            'pgsql', 'postgres' => "CREATE TABLE IF NOT EXISTS {$this->tableName} (
                series VARCHAR(64) NOT NULL,
                token_hash VARCHAR(128) NOT NULL,
                user_id INTEGER NOT NULL,
                user_name VARCHAR(128) NOT NULL,
                user_pass TEXT NOT NULL,
                host VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL DEFAULT '',
                user_agent VARCHAR(500) NOT NULL DEFAULT '',
                created TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                last_used TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                expires TIMESTAMP WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (series)
            );
            CREATE INDEX IF NOT EXISTS idx_persistent_user_id ON {$this->tableName} (user_id);
            CREATE INDEX IF NOT EXISTS idx_persistent_expires ON {$this->tableName} (expires);",

            default => "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `series` VARCHAR(64) NOT NULL,
                `token_hash` VARCHAR(128) NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `user_name` VARCHAR(128) NOT NULL,
                `user_pass` TEXT NOT NULL,
                `host` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
                `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
                `created` DATETIME NOT NULL,
                `last_used` DATETIME NOT NULL,
                `expires` DATETIME NOT NULL,
                PRIMARY KEY (`series`),
                INDEX `idx_persistent_user_id` (`user_id`),
                INDEX `idx_persistent_expires` (`expires`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        };

        // Execute table statement
        @$db->query($sql);
    }

    // =========================================================================
    // User-Agent & Device Name Parser
    // =========================================================================

    /**
     * Parses User-Agent header into human-friendly device, OS, and browser labels.
     */
    public function parse_user_agent(string $ua): array
    {
        $ua = trim($ua);
        if ($ua === '') {
            return [
                'device_name' => $this->gettext('device_unknown'),
                'browser_os' => '—',
                'type' => 'desktop',
                'icon' => '💻',
            ];
        }

        // Detect OS
        $os = 'Unknown OS';
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Windows NT 10.0/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/CrOS/i', $ua)) {
            $os = 'Chrome OS';
        }

        // Detect Browser
        $browser = 'Web Browser';
        if (preg_match('/Edg\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $ua, $m) && !preg_match('/Edg/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $ua, $m) && !preg_match('/Chrome/i', $ua)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Opera|OPR\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Opera';
        }

        // Detect Device Type
        $type = 'desktop';
        $icon = '💻';
        $deviceName = "{$browser} on {$os}";

        if (preg_match('/iPad|Tablet/i', $ua)) {
            $type = 'tablet';
            $icon = '📱';
            $deviceName = "{$this->gettext('device_type_tablet')} ({$os})";
        } elseif (preg_match('/Mobile|iPhone|Android/i', $ua)) {
            $type = 'mobile';
            $icon = '📱';
            $deviceName = "{$this->gettext('device_type_mobile')} ({$os})";
        }

        return [
            'device_name' => $deviceName,
            'browser_os' => "{$browser} • {$os}",
            'type' => $type,
            'icon' => $icon,
        ];
    }

    private function json_response(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
