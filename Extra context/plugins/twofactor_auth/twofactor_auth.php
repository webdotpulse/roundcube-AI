<?php

/**
 * Two-Factor Authentication Plugin for Roundcube Webmail
 *
 * Enterprise-grade multi-method Two-Factor Authentication:
 * - Mobile App TOTP (Google Authenticator, Microsoft Authenticator, Authy, etc.)
 * - Email One-Time Passcode (OTP)
 * - SMS One-Time Passcode (OTP)
 * - Emergency Single-Use Recovery Codes
 * - Standalone QR Code generation without external API dependencies
 * - Enforcement and global disable configuration controls
 * - Brute-force rate limiting and timing-attack protections
 *
 * @license MIT
 * @author Webdotpulse & Contributors
 */

declare(strict_types=1);

require_once __DIR__ . '/lib/Totp.php';
require_once __DIR__ . '/lib/QrCode.php';

use TwoFactorAuth\Totp;
use TwoFactorAuth\QrCode;

class twofactor_auth extends rcube_plugin
{
    public $task = 'login|mail|settings';
    private rcmail $rcmail;

    public function init(): void
    {
        $this->rcmail = rcmail::get_instance();
        $this->load_config();
        $this->add_texts('localization/', true);

        // Global disable check
        if ($this->rcmail->config->get('twofactor_auth_disabled', false)) {
            return;
        }

        // Gatekeeper: prevent access to unauthorized tasks when 2FA is pending
        $this->add_hook('startup', [$this, 'hook_startup']);

        // Login interceptor: triggers 2FA challenge upon successful password verification
        $this->add_hook('login_after', [$this, 'hook_login_after']);

        // Settings / Preferences integration
        $this->add_hook('preferences_sections_list', [$this, 'hook_preferences_sections_list']);
        $this->add_hook('preferences_list', [$this, 'hook_preferences_list']);
        $this->add_hook('preferences_save', [$this, 'hook_preferences_save']);

        // Register plugin actions
        $this->register_action('plugin.twofactor_auth-check', [$this, 'action_check']);
        $this->register_action('plugin.twofactor_auth-verify', [$this, 'action_verify']);
        $this->register_action('plugin.twofactor_auth-send-otp', [$this, 'action_send_otp']);
        $this->register_action('plugin.twofactor_auth-setup', [$this, 'action_setup']);
        $this->register_action('plugin.twofactor_auth-activate', [$this, 'action_activate']);
        $this->register_action('plugin.twofactor_auth-recovery', [$this, 'action_generate_recovery']);
        $this->register_action('plugin.twofactor_auth-disable', [$this, 'action_disable']);

        // Include CSS/JS when on settings or 2FA challenge
        if ($this->rcmail->task === 'settings' || ($this->rcmail->action && str_starts_with($this->rcmail->action, 'plugin.twofactor_auth-'))) {
            $this->include_stylesheet('twofactor_auth.css');
            $this->include_script('twofactor_auth.js');
        }
    }

    /**
     * Startup Gatekeeper: Enforces 2FA completion before allowing any mailbox/settings access.
     */
    public function hook_startup(array $args): array
    {
        if (!empty($_SESSION['2fa_pending'])) {
            $action = $this->rcmail->action;
            $allowedActions = [
                'plugin.twofactor_auth-check',
                'plugin.twofactor_auth-verify',
                'plugin.twofactor_auth-send-otp',
                'logout',
            ];

            if (!in_array($action, $allowedActions, true)) {
                $this->rcmail->output->redirect(['_action' => 'plugin.twofactor_auth-check']);
            }
        }

        return $args;
    }

    /**
     * Intercepts login after credentials are confirmed by IMAP/host.
     */
    public function hook_login_after(array $args): array
    {
        $prefs = $this->getUser2FaPrefs();
        $isEnforced = (bool)$this->rcmail->config->get('twofactor_auth_enforce', false);
        $isEnabled = !empty($prefs['enabled']);

        if ($isEnabled || $isEnforced) {
            $_SESSION['2fa_pending'] = true;
            $_SESSION['2fa_user_id'] = $args['user_id'] ?? $this->rcmail->user->ID;
            $_SESSION['2fa_user'] = $args['user'] ?? $this->rcmail->user->get_username();
            $_SESSION['2fa_attempts'] = 0;
            $_SESSION['2fa_lockout_until'] = 0;
            $_SESSION['2fa_enforced_setup'] = (!$isEnabled && $isEnforced);

            // Redirect to 2FA challenge screen
            $this->rcmail->output->redirect(['_action' => 'plugin.twofactor_auth-check']);
        }

        return $args;
    }

    /**
     * Action: Render 2FA verification challenge screen.
     */
    public function action_check(): void
    {
        if (empty($_SESSION['2fa_pending'])) {
            $this->rcmail->output->redirect(['_task' => 'mail']);
        }

        $prefs = $this->getUser2FaPrefs();
        $isEnforcedSetup = !empty($_SESSION['2fa_enforced_setup']);

        $this->rcmail->output->set_pagetitle($this->gettext('twofactor_title'));
        $this->include_stylesheet('twofactor_auth.css');
        $this->include_script('twofactor_auth.js');

        // Render challenge HTML
        $html = $this->renderChallengeHtml($prefs, $isEnforcedSetup);
        $this->rcmail->output->send_exit($html);
    }

    /**
     * Action: Verify submitted 2FA code (TOTP, Email OTP, SMS OTP, or Recovery Code).
     */
    public function action_verify(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        if (empty($_SESSION['2fa_pending'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Session expired.']);
            return;
        }

        // Check brute-force lockout
        $now = time();
        $lockoutUntil = (int)($_SESSION['2fa_lockout_until'] ?? 0);
        if ($lockoutUntil > $now) {
            $wait = $lockoutUntil - $now;
            $msg = str_replace('%seconds%', (string)$wait, $this->gettext('twofactor_rate_limited'));
            $this->jsonResponse(['success' => false, 'message' => $msg]);
            return;
        }

        $method = trim((string)rcube_utils::get_input_value('method', rcube_utils::INPUT_POST));
        $code = trim((string)rcube_utils::get_input_value('code', rcube_utils::INPUT_POST));
        $prefs = $this->getUser2FaPrefs();
        $isValid = false;

        switch ($method) {
            case 'totp':
                if (!empty($prefs['totp_secret'])) {
                    $isValid = Totp::verifyCode($prefs['totp_secret'], $code, 1);
                }
                break;

            case 'email':
                $otpHash = $_SESSION['2fa_email_otp_hash'] ?? '';
                $otpTime = (int)($_SESSION['2fa_email_otp_time'] ?? 0);
                $ttl = (int)$this->rcmail->config->get('twofactor_auth_otp_ttl', 600);
                if ($otpHash && ($now - $otpTime) <= $ttl) {
                    $isValid = password_verify($code, $otpHash);
                }
                break;

            case 'sms':
                $otpHash = $_SESSION['2fa_sms_otp_hash'] ?? '';
                $otpTime = (int)($_SESSION['2fa_sms_otp_time'] ?? 0);
                $ttl = (int)$this->rcmail->config->get('twofactor_auth_otp_ttl', 600);
                if ($otpHash && ($now - $otpTime) <= $ttl) {
                    $isValid = password_verify($code, $otpHash);
                }
                break;

            case 'recovery':
                $cleanCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code) ?? '');
                $recoveryHashes = $prefs['recovery_codes'] ?? [];
                foreach ($recoveryHashes as $idx => $hash) {
                    if (password_verify($cleanCode, $hash)) {
                        $isValid = true;
                        // Consume recovery code
                        unset($recoveryHashes[$idx]);
                        $prefs['recovery_codes'] = array_values($recoveryHashes);
                        $this->saveUser2FaPrefs($prefs);
                        break;
                    }
                }
                break;
        }

        if ($isValid) {
            // Authentication complete: lift gatekeeper
            unset($_SESSION['2fa_pending'], $_SESSION['2fa_email_otp_hash'], $_SESSION['2fa_sms_otp_hash'], $_SESSION['2fa_attempts']);
            $_SESSION['2fa_authenticated'] = true;

            $targetUrl = !empty($_SESSION['2fa_enforced_setup'])
                ? $this->rcmail->url(['_task' => 'settings', '_section' => 'twofactor_auth'])
                : $this->rcmail->url(['_task' => 'mail']);

            $this->jsonResponse(['success' => true, 'redirect' => $targetUrl]);
        } else {
            // Invalid code: increment attempts and check lockout threshold
            $_SESSION['2fa_attempts'] = (int)($_SESSION['2fa_attempts'] ?? 0) + 1;
            $maxAttempts = (int)$this->rcmail->config->get('twofactor_auth_max_attempts', 5);

            if ($_SESSION['2fa_attempts'] >= $maxAttempts) {
                $lockoutDuration = (int)$this->rcmail->config->get('twofactor_auth_lockout_duration', 300);
                $_SESSION['2fa_lockout_until'] = $now + $lockoutDuration;
                $_SESSION['2fa_attempts'] = 0;
                $msg = str_replace('%seconds%', (string)$lockoutDuration, $this->gettext('twofactor_rate_limited'));
            } else {
                $msg = $this->gettext('twofactor_invalid_code');
            }

            $this->jsonResponse(['success' => false, 'message' => $msg]);
        }
    }

    /**
     * Action: Send Email or SMS One-Time Passcode with rate-limiting.
     */
    public function action_send_otp(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $now = time();
        $cooldown = (int)$this->rcmail->config->get('twofactor_auth_otp_resend_cooldown', 30);
        $lastSent = (int)($_SESSION['2fa_last_otp_sent'] ?? 0);

        if (($now - $lastSent) < $cooldown) {
            $wait = $cooldown - ($now - $lastSent);
            $msg = str_replace('%seconds%', (string)$wait, $this->gettext('twofactor_resend_wait'));
            $this->jsonResponse(['success' => false, 'message' => $msg]);
            return;
        }

        $type = rcube_utils::get_input_value('type', rcube_utils::INPUT_POST) === 'sms' ? 'sms' : 'email';
        $prefs = $this->getUser2FaPrefs();
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $issuer = $this->rcmail->config->get('twofactor_auth_issuer', 'Roundcube Webmail');
        $ttlMinutes = (int)ceil((int)$this->rcmail->config->get('twofactor_auth_otp_ttl', 600) / 60);

        if ($type === 'email') {
            $destEmail = !empty($prefs['email']) ? $prefs['email'] : $this->rcmail->user->get_username();
            $subjectTpl = $this->rcmail->config->get('twofactor_auth_email_notice_subject', '[%issuer%] Your Security Verification Code');
            $bodyTpl = $this->rcmail->config->get('twofactor_auth_email_notice_body', "Hello %user%,\n\nYour one-time security code is:\n\n    %code%\n\nValid for %ttl_minutes% minutes.");

            $subject = str_replace(['%issuer%', '%user%'], [$issuer, $destEmail], $subjectTpl);
            $body = str_replace(['%code%', '%user%', '%issuer%', '%ttl_minutes%'], [$otp, $destEmail, $issuer, (string)$ttlMinutes], $bodyTpl);

            // Send via mail transport
            @mail($destEmail, $subject, $body, "From: no-reply@{$_SERVER['SERVER_NAME']}\r\nContent-Type: text/plain; charset=UTF-8");

            $_SESSION['2fa_email_otp_hash'] = $otpHash;
            $_SESSION['2fa_email_otp_time'] = $now;
            $_SESSION['2fa_last_otp_sent'] = $now;

            $this->jsonResponse(['success' => true, 'message' => $this->gettext('twofactor_otp_sent')]);
        } else {
            // SMS OTP
            $phone = $prefs['phone'] ?? '';
            if (empty($phone)) {
                $this->jsonResponse(['success' => false, 'message' => 'No mobile phone configured for SMS OTP.']);
                return;
            }

            $msgTpl = $this->rcmail->config->get('twofactor_auth_sms_message', '%issuer% verification code: %code%. Valid for 10 minutes.');
            $smsText = str_replace(['%code%', '%issuer%'], [$otp, $issuer], $msgTpl);

            $this->dispatchSms($phone, $smsText);

            $_SESSION['2fa_sms_otp_hash'] = $otpHash;
            $_SESSION['2fa_sms_otp_time'] = $now;
            $_SESSION['2fa_last_otp_sent'] = $now;

            $this->jsonResponse(['success' => true, 'message' => $this->gettext('twofactor_otp_sent')]);
        }
    }

    /**
     * Action: Setup wizard initialization - generates secret and QR code.
     */
    public function action_setup(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $secret = Totp::generateSecret(20);
        $user = $this->rcmail->user->get_username();
        $issuer = $this->rcmail->config->get('twofactor_auth_issuer', 'Roundcube Webmail');
        $otpUri = Totp::getOtpAuthUri($user, $issuer, $secret);
        $qrSvg = QrCode::getSvg($otpUri, 220);

        $_SESSION['2fa_setup_secret'] = $secret;

        $this->jsonResponse([
            'success' => true,
            'secret' => $secret,
            'formatted_secret' => chunk_split($secret, 4, ' '),
            'qr_svg' => $qrSvg,
            'otp_uri' => $otpUri,
        ]);
    }

    /**
     * Action: Activate 2FA after successful initial code validation.
     */
    public function action_activate(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $secret = $_SESSION['2fa_setup_secret'] ?? '';
        $code = trim((string)rcube_utils::get_input_value('code', rcube_utils::INPUT_POST));
        $method = rcube_utils::get_input_value('method', rcube_utils::INPUT_POST) ?: 'totp';
        $email = trim((string)rcube_utils::get_input_value('email', rcube_utils::INPUT_POST));
        $phone = trim((string)rcube_utils::get_input_value('phone', rcube_utils::INPUT_POST));

        if (empty($secret) || !Totp::verifyCode($secret, $code, 1)) {
            $this->jsonResponse(['success' => false, 'message' => $this->gettext('twofactor_invalid_code')]);
            return;
        }

        // Generate initial emergency recovery codes
        $rawCodes = $this->generateRecoveryCodes();
        $hashedCodes = array_map(fn($c) => password_hash(strtoupper(str_replace('-', '', $c)), PASSWORD_DEFAULT), $rawCodes);

        $prefs = [
            'enabled' => true,
            'method' => $method,
            'totp_secret' => $secret,
            'email' => $email,
            'phone' => $phone,
            'recovery_codes' => $hashedCodes,
            'activated_at' => date('Y-m-d H:i:s'),
        ];

        $this->saveUser2FaPrefs($prefs);
        unset($_SESSION['2fa_setup_secret']);

        $this->jsonResponse([
            'success' => true,
            'message' => $this->gettext('twofactor_setup_success'),
            'recovery_codes' => $rawCodes,
        ]);
    }

    /**
     * Action: Generate fresh emergency recovery codes.
     */
    public function action_generate_recovery(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        $prefs = $this->getUser2FaPrefs();
        if (empty($prefs['enabled'])) {
            $this->jsonResponse(['success' => false, 'message' => '2FA is not enabled.']);
            return;
        }

        $rawCodes = $this->generateRecoveryCodes();
        $hashedCodes = array_map(fn($c) => password_hash(strtoupper(str_replace('-', '', $c)), PASSWORD_DEFAULT), $rawCodes);

        $prefs['recovery_codes'] = $hashedCodes;
        $this->saveUser2FaPrefs($prefs);

        $this->jsonResponse([
            'success' => true,
            'message' => $this->gettext('twofactor_recovery_success'),
            'recovery_codes' => $rawCodes,
        ]);
    }

    /**
     * Action: Disable 2FA.
     */
    public function action_disable(): void
    {
        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);

        if ($this->rcmail->config->get('twofactor_auth_enforce', false)) {
            $this->jsonResponse(['success' => false, 'message' => $this->gettext('twofactor_enforced_notice')]);
            return;
        }

        $this->saveUser2FaPrefs([
            'enabled' => false,
            'method' => 'totp',
            'totp_secret' => '',
            'email' => '',
            'phone' => '',
            'recovery_codes' => [],
        ]);

        $this->jsonResponse(['success' => true, 'message' => $this->gettext('twofactor_disable_success')]);
    }

    /**
     * Hook: Register Two-Factor Authentication in Preferences Sections.
     */
    public function hook_preferences_sections_list(array $args): array
    {
        $args['list']['twofactor_auth'] = [
            'id' => 'twofactor_auth',
            'section' => 'twofactor_auth',
            'name' => $this->gettext('twofactor_title'),
        ];
        return $args;
    }

    /**
     * Hook: Render Settings UI for Two-Factor Authentication.
     */
    public function hook_preferences_list(array $args): array
    {
        if ($args['section'] !== 'twofactor_auth') {
            return $args;
        }

        $prefs = $this->getUser2FaPrefs();
        $isEnforced = (bool)$this->rcmail->config->get('twofactor_auth_enforce', false);
        $isEnabled = !empty($prefs['enabled']);

        $this->include_stylesheet('twofactor_auth.css');
        $this->include_script('twofactor_auth.js');

        $content = '<div class="twofactor-settings-card">';

        // Status Header
        $statusBadge = $isEnabled
            ? '<span class="twofactor-badge twofactor-badge-success">' . rcube::Q($this->gettext('twofactor_enabled')) . '</span>'
            : ($isEnforced
                ? '<span class="twofactor-badge twofactor-badge-warning">' . rcube::Q($this->gettext('twofactor_enforced')) . '</span>'
                : '<span class="twofactor-badge twofactor-badge-secondary">' . rcube::Q($this->gettext('twofactor_disabled')) . '</span>');

        $content .= '<div class="twofactor-header">';
        $content .= '<h3>' . rcube::Q($this->gettext('twofactor_title')) . ' ' . $statusBadge . '</h3>';
        $content .= '<p class="text-muted">' . rcube::Q($this->gettext('twofactor_desc')) . '</p>';
        $content .= '</div>';

        if ($isEnabled) {
            $remaining = count($prefs['recovery_codes'] ?? []);
            $content .= '<div class="twofactor-active-box">';
            $content .= '<p><strong>' . rcube::Q($this->gettext('twofactor_active_method')) . ':</strong> ' . rcube::Q(ucfirst($prefs['method'] ?? 'TOTP')) . '</p>';
            $content .= '<p><strong>' . rcube::Q($this->gettext('twofactor_recovery_remaining')) . '</strong> ' . $remaining . ' / 10</p>';
            $content .= '<div class="btn-group mt-2">';
            $content .= '<button type="button" class="btn btn-secondary" onclick="twofactor_generate_recovery()">' . rcube::Q($this->gettext('twofactor_recovery_generate')) . '</button>';
            if (!$isEnforced) {
                $content .= '<button type="button" class="btn btn-danger" onclick="twofactor_disable()">' . rcube::Q($this->gettext('twofactor_disable_btn')) . '</button>';
            }
            $content .= '</div>';
            $content .= '</div>';
        } else {
            $content .= '<div class="twofactor-setup-trigger">';
            $content .= '<button type="button" class="btn btn-primary btn-lg" onclick="twofactor_start_setup()">' . rcube::Q($this->gettext('twofactor_setup')) . '</button>';
            $content .= '</div>';
        }

        // Setup & Recovery Code Container
        $content .= '<div id="twofactor-modal-container" style="display: none;"></div>';
        $content .= '</div>';

        $args['blocks']['twofactor_auth'] = [
            'name' => $this->gettext('twofactor_title'),
            'options' => [
                'twofactor_content' => [
                    'content' => $content,
                ],
            ],
        ];

        return $args;
    }

    public function hook_preferences_save(array $args): array
    {
        return $args;
    }

    /**
     * Dispatch SMS using configured webhook, shell command, or custom handler.
     */
    protected function dispatchSms(string $phone, string $text): bool
    {
        $gateway = $this->rcmail->config->get('twofactor_auth_sms_gateway');
        if (empty($gateway)) {
            return false;
        }

        if (str_starts_with($gateway, 'http://') || str_starts_with($gateway, 'https://')) {
            $url = str_replace(
                ['%phone%', '%message%'],
                [rawurlencode($phone), rawurlencode($text)],
                $gateway
            );
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            @file_get_contents($url, false, $ctx);
            return true;
        }

        $cmd = str_replace(
            ['%phone%', '%message%'],
            [escapeshellarg($phone), escapeshellarg($text)],
            $gateway
        );
        @exec($cmd);
        return true;
    }

    /**
     * Generates a batch of 10 human-readable one-time recovery codes.
     * Format: ABCD-EFGH
     */
    protected function generateRecoveryCodes(int $count = 10): array
    {
        $codes = [];
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $count; $i++) {
            $p1 = '';
            $p2 = '';
            for ($j = 0; $j < 4; $j++) {
                $p1 .= $chars[random_int(0, $max)];
                $p2 .= $chars[random_int(0, $max)];
            }
            $codes[] = "{$p1}-{$p2}";
        }

        return $codes;
    }

    /**
     * Retrieves stored 2FA configuration from user preferences.
     */
    public function getUser2FaPrefs(): array
    {
        $userPrefs = $this->rcmail->user->get_prefs();
        $stored = $userPrefs['twofactor_auth_data'] ?? [];
        return is_array($stored) ? $stored : [];
    }

    /**
     * Persists 2FA configuration to user preferences.
     */
    public function saveUser2FaPrefs(array $data): bool
    {
        return $this->rcmail->user->save_prefs(['twofactor_auth_data' => $data]);
    }

    /**
     * Renders complete standalone HTML page for 2FA verification challenge.
     */
    protected function renderChallengeHtml(array $prefs, bool $isEnforcedSetup): string
    {
        $token = $this->rcmail->get_request_token();
        $title = htmlspecialchars($this->gettext('twofactor_check_title'), ENT_QUOTES);
        $subtitle = htmlspecialchars($this->gettext('twofactor_check_subtitle'), ENT_QUOTES);
        $verifyBtn = htmlspecialchars($this->gettext('twofactor_verify_btn'), ENT_QUOTES);
        $logoutBtn = htmlspecialchars($this->gettext('twofactor_logout_btn'), ENT_QUOTES);
        $resendBtn = htmlspecialchars($this->gettext('twofactor_resend_otp'), ENT_QUOTES);
        $issuer = htmlspecialchars($this->rcmail->config->get('twofactor_auth_issuer', 'Roundcube Webmail'), ENT_QUOTES);

        $hasEmail = !empty($prefs['email']);
        $hasSms = !empty($prefs['phone']);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - {$issuer}</title>
    <style>
        :root { --primary-color: #1a73e8; --bg-color: #f8f9fa; --card-bg: #ffffff; --text-color: #202124; --border-color: #dadce0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg-color); color: var(--text-color); margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .twofactor-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 36px 32px; width: 100%; max-width: 440px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); text-align: center; }
        .twofactor-icon { width: 56px; height: 56px; background: #e8f0fe; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--primary-color); }
        .twofactor-icon svg { width: 28px; height: 28px; fill: currentColor; }
        h1 { font-size: 22px; font-weight: 600; margin: 0 0 8px; }
        p.subtitle { color: #5f6368; font-size: 14px; margin: 0 0 24px; }
        .method-selector { display: flex; justify-content: center; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .method-btn { background: none; border: 1px solid var(--border-color); border-radius: 20px; padding: 6px 14px; font-size: 13px; font-weight: 500; cursor: pointer; color: #5f6368; transition: all 0.15s; }
        .method-btn.active { background: #e8f0fe; border-color: var(--primary-color); color: var(--primary-color); }
        .code-input-group { margin-bottom: 20px; }
        .code-input { width: 100%; font-size: 24px; letter-spacing: 6px; text-align: center; font-weight: 600; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; box-sizing: border-box; font-family: monospace; transition: border-color 0.2s; }
        .code-input:focus { outline: none; border-color: var(--primary-color); }
        .btn-submit { width: 100%; background: var(--primary-color); color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #1557b0; }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
        .alert-box { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; display: none; text-align: left; }
        .alert-error { background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }
        .alert-info { background: #e8f0fe; color: #1a73e8; border: 1px solid #d2e3fc; }
        .footer-links { margin-top: 24px; display: flex; justify-content: space-between; font-size: 13px; }
        .footer-links a { color: #5f6368; text-decoration: none; }
        .footer-links a:hover { color: var(--primary-color); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="twofactor-card">
        <div class="twofactor-icon">
            <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
        </div>
        <h1>{$title}</h1>
        <p class="subtitle">{$subtitle}</p>

        <div id="twofactor-alert" class="alert-box"></div>

        <div class="method-selector">
            <button type="button" class="method-btn active" data-method="totp" onclick="twofactor_set_method('totp')">App (TOTP)</button>
            <button type="button" class="method-btn" data-method="email" onclick="twofactor_set_method('email')">Email OTP</button>
            <button type="button" class="method-btn" data-method="sms" onclick="twofactor_set_method('sms')">SMS OTP</button>
            <button type="button" class="method-btn" data-method="recovery" onclick="twofactor_set_method('recovery')">Recovery Code</button>
        </div>

        <form id="twofactor-form" onsubmit="twofactor_submit(event)">
            <input type="hidden" name="_token" id="twofactor-token" value="{$token}">
            <input type="hidden" name="method" id="twofactor-method" value="totp">

            <div class="code-input-group">
                <input type="text" id="twofactor-code" name="code" class="code-input" placeholder="000000" autocomplete="one-time-code" autofocus required>
            </div>

            <button type="submit" id="twofactor-submit-btn" class="btn-submit">{$verifyBtn}</button>

            <div id="otp-resend-container" style="display: none; margin-top: 14px;">
                <button type="button" id="otp-resend-btn" class="method-btn" onclick="twofactor_request_otp()">{$resendBtn}</button>
            </div>
        </form>

        <div class="footer-links">
            <a href="?_task=logout">{$logoutBtn}</a>
            <span style="color: #dadce0;">|</span>
            <span style="color: #5f6368;">{$issuer}</span>
        </div>
    </div>

    <script>
        let currentMethod = 'totp';
        function twofactor_set_method(m) {
            currentMethod = m;
            document.getElementById('twofactor-method').value = m;
            document.querySelectorAll('.method-btn').forEach(b => b.classList.toggle('active', b.getAttribute('data-method') === m));
            const input = document.getElementById('twofactor-code');
            const resend = document.getElementById('otp-resend-container');
            if (m === 'recovery') {
                input.placeholder = 'ABCD-EFGH';
                input.maxLength = 10;
                resend.style.display = 'none';
            } else {
                input.placeholder = '000000';
                input.maxLength = 6;
                resend.style.display = (m === 'email' || m === 'sms') ? 'block' : 'none';
                if (m === 'email' || m === 'sms') twofactor_request_otp();
            }
            input.value = '';
            input.focus();
        }

        async function twofactor_submit(e) {
            e.preventDefault();
            const btn = document.getElementById('twofactor-submit-btn');
            const alert = document.getElementById('twofactor-alert');
            const code = document.getElementById('twofactor-code').value.trim();
            const token = document.getElementById('twofactor-token').value;
            btn.disabled = true;
            alert.style.display = 'none';

            try {
                const fd = new FormData();
                fd.append('_token', token);
                fd.append('method', currentMethod);
                fd.append('code', code);
                const res = await fetch('?_action=plugin.twofactor_auth-verify', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || '?_task=mail';
                } else {
                    alert.className = 'alert-box alert-error';
                    alert.textContent = data.message;
                    alert.style.display = 'block';
                    document.getElementById('twofactor-code').select();
                }
            } catch (err) {
                alert.className = 'alert-box alert-error';
                alert.textContent = 'Verification error. Please retry.';
                alert.style.display = 'block';
            } finally {
                btn.disabled = false;
            }
        }

        async function twofactor_request_otp() {
            const token = document.getElementById('twofactor-token').value;
            const alert = document.getElementById('twofactor-alert');
            const fd = new FormData();
            fd.append('_token', token);
            fd.append('type', currentMethod);
            const res = await fetch('?_action=plugin.twofactor_auth-send-otp', { method: 'POST', body: fd });
            const data = await res.json();
            alert.className = data.success ? 'alert-box alert-info' : 'alert-box alert-error';
            alert.textContent = data.message;
            alert.style.display = 'block';
        }
    </script>
</body>
</html>
HTML;
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
