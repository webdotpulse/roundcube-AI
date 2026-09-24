<?php

/**
 * Easy Unsubscribe - Roundcube Webmail Plugin
 *
 * Implements a Gmail-style native Unsubscribe button in Roundcube's message view.
 * Supports:
 * - RFC 8058 One-Click POST (List-Unsubscribe=One-Click)
 * - RFC 2369 Automated Mailto unsubscription via internal Roundcube mail dispatch
 * - RFC 2369 Standard HTTP/HTTPS landing page fallback
 *
 * @version 1.0.0
 * @author Webdotpulse <support@webdotpulse.com>
 * @license GNU GPLv3+
 */
class easy_unsubscribe extends rcube_plugin
{
    /**
     * Target task for this plugin.
     *
     * @var string
     */
    public $task = 'mail';

    /**
     * Roundcube application instance.
     *
     * @var rcmail
     */
    private $rc;

    /**
     * Unsubscribe metadata for the current message being loaded/rendered.
     *
     * @var array|null
     */
    private $current_unsub_data = null;

    /**
     * Session cache key for recorded unsubscriptions.
     */
    const SESSION_KEY = 'easy_unsubscribed_list';

    /**
     * User preference key for persistent unsubscription state.
     */
    const PREF_KEY = 'easy_unsubscribed_messages';

    /**
     * Constructor.
     *
     * @param rcube_plugin_api|null $api
     */
    public function __construct($api = null)
    {
        parent::__construct($api);
        $this->rc = rcmail::get_instance();
    }

    /**
     * Plugin initialization.
     */
    public function init()
    {
        if (!$this->rc) {
            $this->rc = rcmail::get_instance();
        }
        $this->load_config();

        // 1. Hook into IMAP storage initialization to ensure List headers are fetched
        $this->add_hook('storage_init', [$this, 'storage_init']);

        // 2. Register AJAX controller for processing unsubscribe actions
        $this->register_action('plugin.easy_unsubscribe', [$this, 'action_unsubscribe']);

        // 3. Register message viewing hooks
        if ($this->rc->task === 'mail') {
            $action = (string)$this->rc->action;
            if (empty($action) || in_array($action, ['show', 'preview'])) {
                $this->add_hook('message_load', [$this, 'message_load']);
                $this->add_hook('template_object_messageheaders', [$this, 'template_object_messageheaders']);
                $this->add_hook('render_page', [$this, 'render_page']);

                // Include localized strings (true exports them to JavaScript rcmail.gettext)
                $this->add_texts('localization/', true);

                // Include UI scripts and stylesheets
                $this->include_script('easy_unsubscribe.js');
                $skin_path = $this->resolve_skin_path();
                $this->include_stylesheet($skin_path . '/easy_unsubscribe.css');
            }
        }
    }

    /**
     * Resolve skin directory with fallback to elastic.
     *
     * @return string
     */
    private function resolve_skin_path()
    {
        $skin = $this->rc->config->get('skin', 'elastic');
        $skin_dir = $this->home . '/skins/' . $skin;
        if (is_dir($skin_dir)) {
            return 'skins/' . $skin;
        }
        return 'skins/elastic';
    }

    /**
     * Hook: storage_init
     * Ensures IMAP header caching fetches List-Unsubscribe and List-ID headers.
     *
     * @param array $p Hook arguments containing 'fetch_headers'
     * @return array
     */
    public function storage_init($p)
    {
        $required_headers = ['LIST-UNSUBSCRIBE', 'LIST-UNSUBSCRIBE-POST', 'LIST-ID'];
        $existing = !empty($p['fetch_headers']) ? explode(' ', $p['fetch_headers']) : [];
        $merged = array_unique(array_merge($existing, $required_headers));
        $p['fetch_headers'] = implode(' ', $merged);

        return $p;
    }

    /**
     * Hook: message_load
     * Inspects email headers when a message is opened or previewed.
     *
     * @param array $args ['object' => rcube_message]
     * @return array
     */
    public function message_load($args)
    {
        if (empty($args['object']) || !is_object($args['object'])) {
            return $args;
        }

        /** @var rcube_message $message */
        $message = $args['object'];

        // Extract raw header values
        $unsub_header = $this->get_message_header($message, 'list-unsubscribe');
        $unsub_post_header = $this->get_message_header($message, 'list-unsubscribe-post');
        $list_id_header = $this->get_message_header($message, 'list-id');
        $from_header = $this->get_message_header($message, 'from') ?: ($message->headers->from ?? '');

        if (empty($unsub_header)) {
            $this->current_unsub_data = null;
            return $args;
        }

        // Determine sender display name and clean list identification
        $sender_info = $this->extract_sender_info($from_header, $list_id_header);

        // Parse RFC 2369 and RFC 8058 URLs
        $parsed = $this->parse_unsubscribe_headers(
            $unsub_header,
            $unsub_post_header,
            $sender_info,
            $message->uid,
            $message->folder
        );

        if ($parsed) {
            // Check if already unsubscribed in session or user preferences
            $is_unsubscribed = $this->is_already_unsubscribed($message->uid, $message->folder, $parsed);
            $parsed['status'] = $is_unsubscribed ? 'unsubscribed' : 'active';
            $this->current_unsub_data = $parsed;
        } else {
            $this->current_unsub_data = null;
        }

        return $args;
    }

    /**
     * Hook: template_object_messageheaders
     * Passes the parsed unsubscribe data to the client environment.
     *
     * @param array $p Hook arguments containing 'content'
     * @return array
     */
    public function template_object_messageheaders($p)
    {
        if (!empty($this->current_unsub_data)) {
            $this->rc->output->set_env('easy_unsubscribe_data', $this->current_unsub_data);
        }

        return $p;
    }

    /**
     * Hook: render_page
     * Final safety net ensuring easy_unsubscribe_data is present in environment.
     *
     * @param array $args
     * @return array
     */
    public function render_page($args)
    {
        if (!empty($this->current_unsub_data)) {
            $this->rc->output->set_env('easy_unsubscribe_data', $this->current_unsub_data);
        }

        return $args;
    }

    /**
     * Extract a header value from rcube_message object.
     * Safely inspects headers->others, object properties, and get_header().
     *
     * @param rcube_message|object $message
     * @param string $header_name
     * @return string|null
     */
    public function get_message_header($message, $header_name)
    {
        $lower = strtolower($header_name);

        if (is_object($message) && method_exists($message, 'get_header')) {
            $val = $message->get_header($lower);
            if (!empty($val)) {
                return is_array($val) ? implode(', ', $val) : (string)$val;
            }
        }

        if (isset($message->headers->others) && is_array($message->headers->others)) {
            foreach ($message->headers->others as $key => $val) {
                if (strtolower($key) === $lower) {
                    return is_array($val) ? implode(', ', $val) : (string)$val;
                }
            }
        }

        if (isset($message->headers->$lower)) {
            $val = $message->headers->$lower;
            return is_array($val) ? implode(', ', $val) : (string)$val;
        }

        return null;
    }

    /**
     * Extract display name and clean address from sender/list headers.
     *
     * @param string $from_header
     * @param string|null $list_id_header
     * @return array ['display_name' => string, 'email' => string, 'list_name' => string]
     */
    public function extract_sender_info($from_header, $list_id_header = null)
    {
        $display_name = '';
        $email = '';
        $list_name = '';

        if (!empty($list_id_header)) {
            // List-ID can be: "List Description" <list-id.domain.com> or <list-id.domain.com>
            if (preg_match('/^"?([^"<]+)"?\s*<[^>]+>/', trim($list_id_header), $lm)) {
                $list_name = trim($lm[1]);
            }
        }

        if (!empty($from_header)) {
            // RFC 2822 display name: "Sender Name" <sender@domain.com> or Sender Name <sender@domain.com>
            if (preg_match('/^"?([^"<]+)"?\s*<([^>]+)>/', trim($from_header), $m)) {
                $display_name = trim($m[1]);
                $email = trim($m[2]);
            } elseif (preg_match('/^<([^>]+)>/', trim($from_header), $m)) {
                $email = trim($m[1]);
                $display_name = $email;
            } else {
                $email = trim($from_header);
                $display_name = $email;
            }
        }

        $final_name = !empty($list_name) ? $list_name : (!empty($display_name) ? $display_name : $email);

        return [
            'display_name' => $final_name ?: 'Sender',
            'email' => $email,
            'list_name' => $list_name,
        ];
    }

    /**
     * Parse RFC 2369 List-Unsubscribe and RFC 8058 List-Unsubscribe-Post headers.
     *
     * @param string|array $unsub_header Raw List-Unsubscribe header
     * @param string|null $post_header Raw List-Unsubscribe-Post header
     * @param array $sender_info Formatted sender info
     * @param string|int|null $uid Message UID
     * @param string|null $mbox Mailbox folder
     * @return array|null Structured unsubscribe metadata
     */
    public function parse_unsubscribe_headers($unsub_header, $post_header = null, $sender_info = [], $uid = null, $mbox = null)
    {
        if (is_array($unsub_header)) {
            $unsub_header = implode(', ', $unsub_header);
        }

        $header_clean = preg_replace('/\s+/', ' ', (string)$unsub_header);

        // RFC 2369: URLs are enclosed in angle brackets: <URL>, <URL>
        if (!preg_match_all('/<([^>]+)>/', $header_clean, $matches)) {
            return null;
        }

        $mailto_info = null;
        $https_url = null;
        $http_url = null;

        foreach ($matches[1] as $uri) {
            $uri = trim($uri);

            // 1. Mailto URI (RFC 2368 / RFC 2369)
            if (stripos($uri, 'mailto:') === 0 && !$mailto_info) {
                $parsed_mailto = $this->parse_mailto_uri($uri);
                if ($parsed_mailto) {
                    $mailto_info = $parsed_mailto;
                }
            }

            // 2. HTTPS Web URI
            if (stripos($uri, 'https://') === 0 && !$https_url) {
                if (filter_var($uri, FILTER_VALIDATE_URL)) {
                    $https_url = $uri;
                }
            }

            // 3. HTTP Web URI
            if (stripos($uri, 'http://') === 0 && !$http_url) {
                if (filter_var($uri, FILTER_VALIDATE_URL)) {
                    $http_url = $uri;
                }
            }
        }

        // Determine if RFC 8058 One-Click Unsubscribe is signaled:
        // List-Unsubscribe-Post header MUST equal "List-Unsubscribe=One-Click"
        // and at least one HTTPS (or HTTP if enabled) URI MUST be present.
        $is_rfc8058 = false;
        if (!empty($post_header)) {
            $post_val = is_array($post_header) ? implode('', $post_header) : (string)$post_header;
            if (preg_match('/^List-Unsubscribe=One-Click$/i', trim($post_val))) {
                $is_rfc8058 = true;
            }
        }

        $prefer_oneclick = (bool)$this->rc->config->get('easy_unsubscribe_prefer_oneclick', true);
        $allow_http_oneclick = (bool)$this->rc->config->get('easy_unsubscribe_allow_http_oneclick', false);
        $oneclick_url = $https_url ?: ($allow_http_oneclick ? $http_url : null);

        $result_type = null;
        $target_url = null;

        if ($is_rfc8058 && $oneclick_url && $prefer_oneclick) {
            // Primary: RFC 8058 One-Click HTTP POST
            $result_type = 'one_click';
            $target_url = $oneclick_url;
        } elseif ($mailto_info) {
            // Secondary: RFC 2369 Mailto automated dispatch
            $result_type = 'mailto';
        } elseif ($https_url || $http_url) {
            // Tertiary: Standard external web landing page
            $result_type = 'http';
            $target_url = $https_url ?: $http_url;
        } elseif ($is_rfc8058 && $oneclick_url) {
            // Fallback for one_click when prefer_oneclick is false but mailto wasn't present
            $result_type = 'one_click';
            $target_url = $oneclick_url;
        }

        if (!$result_type) {
            return null;
        }

        return [
            'type' => $result_type,
            'url' => $target_url,
            'https_url' => $https_url,
            'http_url' => $http_url,
            'mailto' => $mailto_info,
            'is_rfc8058' => $is_rfc8058,
            'sender_name' => $sender_info['display_name'] ?? 'Mailing List',
            'sender_email' => $sender_info['email'] ?? '',
            'uid' => $uid,
            'mbox' => $mbox,
        ];
    }

    /**
     * Parse a mailto: URI into recipient, subject, and body components.
     *
     * @param string $uri e.g. "mailto:unsub@domain.com?subject=unsubscribe&body=optout"
     * @return array|null ['email' => string, 'subject' => string, 'body' => string, 'raw' => string]
     */
    public function parse_mailto_uri($uri)
    {
        $uri_body = substr($uri, 7); // Strip 'mailto:'
        $parts = explode('?', $uri_body, 2);
        $email = trim(urldecode($parts[0]));

        $subject = 'Unsubscribe';
        $body = 'Unsubscribe';

        if (isset($parts[1])) {
            parse_str($parts[1], $query_params);
            if (!empty($query_params['subject'])) {
                $subject = trim((string)$query_params['subject']);
            }
            if (!empty($query_params['body'])) {
                $body = trim((string)$query_params['body']);
            }
        }

        // Validate destination email address format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return [
            'email' => $email,
            'subject' => $subject,
            'body' => $body,
            'raw' => $uri,
        ];
    }

    /**
     * Controller action for plugin.easy_unsubscribe (POST request).
     */
    public function action_unsubscribe()
    {
        // 1. CSRF Verification
        if (method_exists($this->rc, 'request_security_check')) {
            $this->rc->request_security_check(rcube_utils::INPUT_POST);
        } elseif (method_exists($this->rc, 'check_request_token') && !$this->rc->check_request_token(rcube_utils::INPUT_POST)) {
            $this->send_response(false, $this->gettext('error_csrf'), 'error');
            return;
        }

        // 2. Validate input parameters
        $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
        $requested_type = rcube_utils::get_input_value('_type', rcube_utils::INPUT_POST);

        if (empty($uid) || empty($mbox)) {
            $this->send_response(false, $this->gettext('error_invalid_params'), 'error');
            return;
        }

        // 3. Load message from authenticated IMAP storage to verify authenticity
        // Never rely on client-supplied URLs or mailto targets directly to prevent SSRF and spoofing
        $storage = $this->rc->get_storage();
        $message = new rcube_message($uid, $mbox);

        if (!$message || empty($message->headers)) {
            $this->send_response(false, $this->gettext('error_message_not_found'), 'error');
            return;
        }

        $unsub_header = $this->get_message_header($message, 'list-unsubscribe');
        $post_header = $this->get_message_header($message, 'list-unsubscribe-post');
        $from_header = $this->get_message_header($message, 'from') ?: ($message->headers->from ?? '');
        $list_id_header = $this->get_message_header($message, 'list-id');

        if (empty($unsub_header)) {
            $this->send_response(false, $this->gettext('error_no_header'), 'error');
            return;
        }

        $sender_info = $this->extract_sender_info($from_header, $list_id_header);
        $parsed = $this->parse_unsubscribe_headers($unsub_header, $post_header, $sender_info, $uid, $mbox);

        if (!$parsed) {
            $this->send_response(false, $this->gettext('error_no_header'), 'error');
            return;
        }

        $action_type = !empty($requested_type) ? $requested_type : $parsed['type'];
        $sender_label = htmlspecialchars($parsed['sender_name'], ENT_QUOTES, 'UTF-8');

        // 4. Execute unsubscribe mechanism based on verified headers
        switch ($action_type) {
            case 'one_click':
                if (empty($parsed['url'])) {
                    $this->send_response(false, $this->gettext('error_no_header'), 'error');
                    return;
                }

                $post_result = $this->execute_one_click_post($parsed['url']);
                if ($post_result['success']) {
                    $this->mark_as_unsubscribed($uid, $mbox, $parsed);
                    $msg = sprintf($this->gettext('success_oneclick'), $sender_label);
                    $this->send_response(true, $msg, 'confirmation', [
                        'status' => 'unsubscribed',
                        'type' => 'one_click',
                        'uid' => $uid,
                    ]);
                } else {
                    $error_text = !empty($post_result['message'])
                        ? $post_result['message']
                        : $this->gettext('error_failed');
                    $this->send_response(false, $error_text, 'error');
                }
                break;

            case 'mailto':
                if (empty($parsed['mailto'])) {
                    $this->send_response(false, $this->gettext('error_no_header'), 'error');
                    return;
                }

                $send_result = $this->execute_mailto_send($parsed['mailto'], $parsed['sender_name']);
                if ($send_result['success']) {
                    $this->mark_as_unsubscribed($uid, $mbox, $parsed);
                    $msg = sprintf($this->gettext('success_mailto'), htmlspecialchars($parsed['mailto']['email'], ENT_QUOTES, 'UTF-8'));
                    $this->send_response(true, $msg, 'confirmation', [
                        'status' => 'unsubscribed',
                        'type' => 'mailto',
                        'uid' => $uid,
                    ]);
                } else {
                    $this->send_response(false, $send_result['message'] ?? $this->gettext('error_mailto_failed'), 'error');
                }
                break;

            case 'http':
                $url = $parsed['url'] ?: ($parsed['https_url'] ?: $parsed['http_url']);
                if (empty($url) || !$this->is_safe_url($url)) {
                    $this->send_response(false, $this->gettext('error_ssrf'), 'error');
                    return;
                }

                // Standard HTTP requires user redirection to the external landing page
                $this->send_response(true, $this->gettext('notice_http_opened'), 'notice', [
                    'status' => 'redirect_required',
                    'type' => 'http',
                    'url' => $url,
                    'uid' => $uid,
                ]);
                break;

            default:
                $this->send_response(false, $this->gettext('error_failed'), 'error');
                break;
        }
    }

    /**
     * Send RFC 8058 One-Click HTTP POST request.
     *
     * RFC 8058 Requirements:
     * - POST to HTTPS URL extracted from List-Unsubscribe
     * - Body: "List-Unsubscribe=One-Click"
     * - Content-Type: "application/x-www-form-urlencoded"
     * - MUST NOT include cookies, credentials, etc.
     * - MUST NOT follow redirects (HTTP 3xx)
     *
     * @param string $url
     * @return array ['success' => bool, 'code' => int, 'message' => string]
     */
    public function execute_one_click_post($url)
    {
        // 1. URL validation and SSRF protection
        if (!$this->is_safe_url($url)) {
            return [
                'success' => false,
                'code' => 0,
                'message' => $this->gettext('error_ssrf'),
            ];
        }

        $timeout = (int)$this->rc->config->get('easy_unsubscribe_timeout', 15);
        $user_agent = (string)$this->rc->config->get('easy_unsubscribe_user_agent', 'Roundcube-EasyUnsubscribe/1.0');
        $body = 'List-Unsubscribe=One-Click';

        // 2. Perform POST via cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: ' . $user_agent,
                    'Accept: */*',
                ],
                CURLOPT_FOLLOWLOCATION => false, // RFC 8058 MUST NOT follow redirects!
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            $status_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($errno) {
                rcube::write_log('easy_unsubscribe', "cURL error ($errno): $error for $url");
                return [
                    'success' => false,
                    'code' => $status_code,
                    'message' => $this->gettext('error_network'),
                ];
            }

            // HTTP 2xx (200, 201, 202, 204) indicates success
            if ($status_code >= 200 && $status_code < 300) {
                return ['success' => true, 'code' => $status_code, 'message' => ''];
            }

            // RFC 8058 states redirect without prior 2xx response is a failure
            if ($status_code >= 300 && $status_code < 400) {
                rcube::write_log('easy_unsubscribe', "Server returned 3xx redirect ($status_code) for One-Click POST to $url");
                return [
                    'success' => false,
                    'code' => $status_code,
                    'message' => sprintf($this->gettext('error_http_post_failed'), (string)$status_code),
                ];
            }

            return [
                'success' => false,
                'code' => $status_code,
                'message' => sprintf($this->gettext('error_http_post_failed'), (string)$status_code),
            ];
        }

        // 3. Fallback: PHP Streams if cURL extension is not installed
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                            "User-Agent: {$user_agent}\r\n" .
                            "Connection: close\r\n",
                'content' => $body,
                'follow_location' => 0, // MUST NOT follow redirects
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $fp = @fopen($url, 'r', false, $context);
        if (!$fp) {
            return [
                'success' => false,
                'code' => 0,
                'message' => $this->gettext('error_network'),
            ];
        }

        $meta = stream_get_meta_data($fp);
        fclose($fp);

        $status_code = 0;
        if (!empty($meta['wrapper_data'])) {
            foreach ($meta['wrapper_data'] as $line) {
                if (preg_match('/^HTTP\/[0-9.]+\s+([0-9]{3})/i', $line, $sm)) {
                    $status_code = (int)$sm[1];
                    break;
                }
            }
        }

        if ($status_code >= 200 && $status_code < 300) {
            return ['success' => true, 'code' => $status_code, 'message' => ''];
        }

        return [
            'success' => false,
            'code' => $status_code,
            'message' => sprintf($this->gettext('error_http_post_failed'), (string)$status_code),
        ];
    }

    /**
     * Send automated unsubscribe email via Roundcube's internal mail-sending API.
     *
     * @param array $mailto_info ['email' => string, 'subject' => string, 'body' => string]
     * @param string $sender_name
     * @return array ['success' => bool, 'message' => string]
     */
    public function execute_mailto_send($mailto_info, $sender_name = '')
    {
        $to_email = $mailto_info['email'];
        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => $this->gettext('error_invalid_params')];
        }

        // Get user's current identity
        $identity = $this->rc->user->get_identity();
        if (empty($identity) || empty($identity['email'])) {
            $identities = $this->rc->user->list_identities();
            $identity = $identities[0] ?? [];
        }

        $from_email = $identity['email'] ?? '';
        if (empty($from_email)) {
            return ['success' => false, 'message' => $this->gettext('error_mailto_failed')];
        }

        $from_name = $identity['name'] ?? '';
        $from_header = !empty($from_name)
            ? sprintf('"%s" <%s>', addcslashes($from_name, '"'), $from_email)
            : $from_email;

        $subject = !empty($mailto_info['subject']) ? $mailto_info['subject'] : 'Unsubscribe';
        $body = !empty($mailto_info['body']) ? $mailto_info['body'] : 'Unsubscribe';

        // Sanitize single-line headers against CRLF injection
        $clean_subject = preg_replace('/[\r\n]+/', ' ', trim($subject));
        $encoded_subject = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($clean_subject, 'UTF-8')
            : $clean_subject;

        $date_header = date('r');
        $msg_id = sprintf('<%s.%s@%s>', md5(uniqid((string)mt_rand(), true)), time(), php_uname('n') ?: 'localhost');

        $headers = [
            'From: ' . $from_header,
            'To: ' . $to_email,
            'Subject: ' . $encoded_subject,
            'Date: ' . $date_header,
            'Message-ID: ' . $msg_id,
            'X-Mailer: Roundcube-EasyUnsubscribe/1.0',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $raw_message = implode("\r\n", $headers) . "\r\n\r\n" . $body;

        // Try delivery via Roundcube's internal mailer
        if (method_exists($this->rc, 'deliver_message')) {
            try {
                $error = null;
                $sent = (bool)$this->rc->deliver_message($raw_message, $from_email, $to_email, $error);
                if ($sent) {
                    return ['success' => true, 'message' => ''];
                }
                if ($error) {
                    rcube::write_log('easy_unsubscribe', "deliver_message error: " . print_r($error, true));
                }
            } catch (\Throwable $e) {
                rcube::write_log('easy_unsubscribe', "Exception delivering unsubscribe email: " . $e->getMessage());
            }
        }

        // Fallback: standard mail() function
        $header_str = implode("\r\n", array_slice($headers, 3)) . "\r\nFrom: " . $from_header;
        $sent_mail = @mail($to_email, $encoded_subject, $body, $header_str);

        if ($sent_mail) {
            return ['success' => true, 'message' => ''];
        }

        return ['success' => false, 'message' => $this->gettext('error_mailto_failed')];
    }

    /**
     * SSRF Protection: Validate URL and ensure target IP is not restricted or private.
     *
     * @param string $url
     * @return bool True if safe, false if restricted/invalid
     */
    public function is_safe_url($url)
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower((string)$scheme), ['https', 'http'])) {
            return false;
        }

        $ssrf_protection = (bool)$this->rc->config->get('easy_unsubscribe_ssrf_protection', true);
        if (!$ssrf_protection) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return false;
        }

        // Block localhost and standard loopback hostnames
        if (in_array(strtolower($host), ['localhost', 'ip6-localhost', 'ip6-loopback'])) {
            return false;
        }

        // Resolve DNS and test for private / reserved IP addresses
        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            // Unresolvable host
            return false;
        }

        // Check if IPv4 is within private or loopback ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
            // Explicit cloud metadata IP protection (169.254.169.254)
            if (strpos($ip, '169.254.') === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a message was already unsubscribed in this session or user preferences.
     *
     * @param string|int $uid
     * @param string $mbox
     * @param array $unsub_info
     * @return bool
     */
    public function is_already_unsubscribed($uid, $mbox, $unsub_info = [])
    {
        $key = $uid . '@' . $mbox;

        // Check session
        if (!empty($_SESSION[self::SESSION_KEY][$key])) {
            return true;
        }

        // Check user preferences if enabled
        if ($this->rc->config->get('easy_unsubscribe_persist_state', true)) {
            $saved = (array)$this->rc->config->get(self::PREF_KEY, []);
            if (!empty($saved[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record an unsubscription in session and user preferences.
     *
     * @param string|int $uid
     * @param string $mbox
     * @param array $unsub_info
     */
    public function mark_as_unsubscribed($uid, $mbox, $unsub_info = [])
    {
        $key = $uid . '@' . $mbox;

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        $_SESSION[self::SESSION_KEY][$key] = time();

        if ($this->rc->config->get('easy_unsubscribe_persist_state', true)) {
            $saved = (array)$this->rc->config->get(self::PREF_KEY, []);
            $saved[$key] = time();
            // Cap stored items to prevent unbounded preference growth
            if (count($saved) > 500) {
                $saved = array_slice($saved, -500, null, true);
            }
            $this->rc->user->save_prefs([self::PREF_KEY => $saved]);
        }
    }

    /**
     * Output standardized AJAX/JSON response to Roundcube client.
     *
     * @param bool $success
     * @param string $message
     * @param string $type Message type ('confirmation', 'error', 'notice')
     * @param array $extra Extra payload data
     */
    private function send_response($success, $message, $type = 'notice', $extra = [])
    {
        $response = array_merge([
            'success' => $success,
            'message' => $message,
            'type' => $type,
        ], $extra);

        if (!empty($message)) {
            $this->rc->output->show_message($message, $type);
        }

        $this->rc->output->command('plugin.easy_unsubscribe_result', $response);
        $this->rc->output->send();
    }
}
