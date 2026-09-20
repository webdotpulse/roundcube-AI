# Comprehensive Application Audit & Optimization Report

**Audit Date:** September 2026  
**Auditor Roles:** Principal Software Architect, Senior Security Engineer, Domain Mathematician  
**Workspace:** `webdotpulse/roundcube-AI`  
**Primary Artifacts Evaluated:** [`lifeprisma_ai.php`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php), [`src/lifeprisma_ai.js`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js), [`bin/worker.php`](file:///home/koen/Git/roundcube-AI/bin/worker.php), [`bin/install-extra.php`](file:///home/koen/Git/roundcube-AI/bin/install-extra.php), [`Extra context/plugins/xsignature/xsignature.php`](file:///home/koen/Git/roundcube-AI/Extra%20context/plugins/xsignature/xsignature.php)

---

## 1. Executive Summary & Health Score

This audit presents an exhaustive evaluation of the Roundcube AI suite covering mathematical algorithms, architectural resilience, OWASP/CWE security posture, and production reliability.

### Codebase Health Scores (Scale 1–10)

| Dimension | Score | Assessment | Primary Hazard |
| :--- | :---: | :--- | :--- |
| **Logic & Mathematical Integrity** | **6.0 / 10** | Token pricing ignores dynamic config and truncates $0.00 to `$0.000000`; search memory fallback injects unrelated client Q&A when relevance is 0; rate limiting bypasses aggregate per-minute quotas on background endpoints. | Token pricing display distortion; prompt token waste & context corruption from mismatched memories; API quota exhaustion via rapid background calls. |
| **Architecture & Deficiencies** | **5.5 / 10** | Caching has been upgraded to multi-tier fallback (Redis -> DB -> File), but global admin configuration hijacks Roundcube's core `users` table; background worker state saving lacks file locking; SSE streaming buffers have unbounded accumulation on gateway errors. | Full-table unindexed scans on `users.preferences`; race condition state corruption in CLI daemon; memory growth on malformed SSE streams. |
| **Security, Validation & Hardening** | **3.5 / 10** | Template attachment upload lacks extension whitelisting, enabling arbitrary PHP script upload; compose preparation accepts arbitrary file paths enabling local file exfiltration (`/etc/passwd`, DB credentials); global AI memory leaks client correspondence across user accounts; background worker draft generation is vulnerable to CRLF email header injection. | Remote Code Execution (RCE) via uploaded `.php` attachment; Arbitrary Local File Read/Exfiltration; Cross-user tenant data leakage; IMAP header tampering. |
| **Operational Reliability** | **6.0 / 10** | Worker IMAP client hardcodes SSL peer verification to false; SSE streaming omits token usage reporting; MIME parsing in standalone client is fragile against nested boundaries. | Man-in-the-Middle credential interception; zero token visibility during streaming; silent worker triage dropouts on complex emails. |

### Primary Risk Areas Requiring Immediate Remediation

1. **Critical Arbitrary File Upload / Remote Code Execution ([`lifeprisma_ai.php:2086-2098`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2086-L2098)):**
   The template attachment upload handler (`op=upload_attachment`) moves uploaded files to `data/attachments/templates/<tpl_id>/` without restricting file extensions or MIME types. An authenticated user can upload executable `.php` scripts into the web-accessible directory, achieving Remote Code Execution (RCE).
2. **Critical Arbitrary File Attachment Exfiltration ([`lifeprisma_ai.php:2580-2594`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2580-L2594)):**
   `handle_message_compose()` attaches arbitrary file paths provided in the `attachments` array of `plugin.lifeprisma_ai_prepare_compose`. Because absolute paths (e.g. `/etc/passwd` or `config/config.inc.php`) are not checked against a confined directory, Roundcube attaches sensitive server files directly into outgoing draft emails.
3. **Critical Cross-User Tenant Information Leakage ([`lifeprisma_ai.php:2343-2372`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2343-L2372), [`2520-2535`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2520-L2535)):**
   AI learned memory is stored in a single shared file (`data/ai_memory.json`). Every outgoing email sent by any user is parsed and added to this global file. When any other user receives an email, those private Q&As are injected into their Gemini prompt, exposing confidential correspondence across users.
4. **Email Header Injection (CRLF) in Worker ([`bin/worker.php:628-639`](file:///home/koen/Git/roundcube-AI/bin/worker.php#L628-L639)):**
   The standalone worker crafts RFC 2822 draft emails by concatenating raw email addresses and subjects without stripping `\r\n`, allowing arbitrary header injection and Bcc/Cc hijacking.
5. **SSRF DNS Rebinding Bypass ([`lifeprisma_ai.php:1761-1782`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1761-L1782)):**
   `validate_api_url()` returns `true` for any hostname that is not a literal IP address without verifying the resolved IP address, permitting DNS rebinding to internal IP ranges (`127.0.0.1`, `169.254.169.254`, `10.0.0.0/8`).

---

## 2. Logic & Mathematical Verification

### Issue 2.1: Irrelevant Memory Injection on Zero-Score Fallback
- **Location:** [`lifeprisma_ai.php:2374-2410`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2374-L2410)
- **Observed Formula/Logic:**
  ```php
  public function find_matching_memory($query, array $memories, $limit = 3)
  {
      if (empty($memories) || empty($query)) return [];
      $words = preg_split('/[\s,\.\?\!\:\;]+/', mb_strtolower($query));
      // ... filter stopwords ...
      $scored = [];
      foreach ($memories as $item) {
          $text = mb_strtolower(($item['question'] ?? '') . ' ' . ($item['subject'] ?? '') . ' ' . ($item['answer'] ?? ''));
          $score = 0;
          foreach ($words as $w) {
              if (mb_strpos($text, $w) !== false) {
                  $score += 2;
              }
          }
          if ($score > 0) {
              $scored[] = ['score' => $score, 'item' => $item];
          }
      }

      if (empty($scored)) {
          return array_slice($memories, -($limit)); // BUG: Returns last 3 memories when match score is 0
      }
      // ...
  }
  ```
- **Mathematical & Algorithmic Analysis:**
  When a query contains no keywords that match any stored memory item ($Score = 0$ for all items), the function returns the last 3 items in the database anyway via `array_slice($memories, -($limit))`. In `handle_triage()` and `call_gemini_triage()`, this injects unrelated Q&As into the prompt:
  $$\text{Relevance}(Q, M_i) = 0 \implies M_i \in \text{PromptContext}$$
  This contaminates the prompt context with completely irrelevant instructions, wastes prompt tokens, and forces Gemini to replicate guidance for unrelated topics.
- **Corrected Formulation:**
  $$\text{MatchingMemories}(Q) = \{ M \in \text{Memories} \mid \text{Score}(Q, M) > 0 \} \downarrow_{\text{Score}} [0 \dots \text{limit}-1]$$
  If $\max(\text{Score}) = 0$, the function MUST return an empty set $\emptyset$.
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -2396,7 +2396,7 @@ class lifeprisma_ai extends rcube_plugin
           }

           if (empty($scored)) {
  -            return array_slice($memories, -($limit));
  +            return [];
           }

           usort($scored, function ($a, $b) {
  ```

---

### Issue 2.2: Hardcoded Rates, Falsy Zero Formatting, and Config Bypass in Cost Estimation
- **Location:** [`src/lifeprisma_ai.js:1471-1486`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L1471-L1486)
- **Observed Formula/Logic:**
  ```javascript
  function lpai_estimate_cost(model, inpTokens, outTokens) {
      var inp = Number(inpTokens) || 0;
      var out = Number(outTokens) || 0;
      if (inp === 0 && out === 0) return null;

      var rates = [0.30, 2.50]; // Gemini 3.8 / 3.7 / 3.6 / 3.5 Flash default ($ per 1M tokens)
      if (model && model.indexOf('lite') >= 0) {
          rates = [0.075, 0.30];
      } else if (model && model.indexOf('pro') >= 0) {
          rates = [1.25, 10.00];
      }

      var cost = (inp * rates[0] + out * rates[1]) / 1000000;
      if (cost < 0.0001) return '$' + cost.toFixed(6);
      return '$' + cost.toFixed(4);
  }
  ```
- **Mathematical & Logic Analysis:**
  1. **Zero Falsy Representation:** If `cost === 0` (e.g., free-tier or zero-priced internal models), `cost < 0.0001` evaluates to `true`, producing `"$0.000000"` instead of `"$0.00"`.
  2. **Config Bypass:** The server passes accurate pricing in `rcmail.env.lpai_gemini.pricing`, which includes explicit rates for `gemini-3.8-flash`, `gemini-3.8-flash-cyber`, `gemini-3.7-flash`, etc. The client completely ignores this environment data and relies on fallback string searches for `'lite'` and `'pro'`.
  3. **Precision Boundary Artifacts:** Arithmetic on floating points can produce precision errors: $(200 \times 0.30 + 100 \times 2.50) / 10^6 = 0.00031000000000000005$. Formatting must be strictly bounded.
- **Corrected Formulation:**
  $$\text{Cost} = \frac{N_{\text{in}} \cdot R_{\text{in}} + N_{\text{out}} \cdot R_{\text{out}}}{10^6}, \quad \text{Formatted} = \begin{cases} \text{null}, & N_{\text{in}} = 0 \land N_{\text{out}} = 0 \\ "\$0.00", & \text{Cost} = 0 \\ "\$"\text{Fixed}(\text{Cost}, 6), & 0 < \text{Cost} < 0.0001 \\ "\$"\text{Fixed}(\text{Cost}, 4), & \text{Cost} \ge 0.0001 \end{cases}$$
- **Remediation Diff:**
  ```diff
  --- a/src/lifeprisma_ai.js
  +++ b/src/lifeprisma_ai.js
  @@ -1473,12 +1473,26 @@ function lpai_estimate_cost(model, inpTokens, outTokens) {
       var out = Number(outTokens) || 0;
       if (inp === 0 && out === 0) return null;

  -    var rates = [0.30, 2.50]; // Gemini 3.8 / 3.7 / 3.6 / 3.5 Flash default ($ per 1M tokens)
  -    if (model && model.indexOf('lite') >= 0) {
  +    var rates = null;
  +    var geminiEnv = (window.rcmail && rcmail.env && rcmail.env.lpai_gemini) || {};
  +    var pricingTable = geminiEnv.pricing || {};
  +    if (model && pricingTable[model]) {
  +        var mRates = pricingTable[model];
  +        rates = [Number(mRates.input) || 0, Number(mRates.output) || 0];
  +    }
  +
  +    if (!rates && model && model.indexOf('lite') >= 0) {
           rates = [0.075, 0.30];
  -    } else if (model && model.indexOf('pro') >= 0) {
  +    } else if (!rates && model && model.indexOf('pro') >= 0) {
           rates = [1.25, 10.00];
  +    } else if (!rates) {
  +        rates = [0.30, 2.50];
       }

       var cost = (inp * rates[0] + out * rates[1]) / 1000000;
  +    if (cost === 0) return '$0.00';
       if (cost < 0.0001) return '$' + cost.toFixed(6);
       return '$' + cost.toFixed(4);
   }
  ```

---

### Issue 2.3: Rate Limiting Sliding-Window Bypass on Background Endpoints
- **Location:** [`lifeprisma_ai.php:1784-1817`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1784-L1817)
- **Observed Formula/Logic:**
  ```php
  private function check_rate_limit($action = '')
  {
      $rcmail = rcmail::get_instance();
      $cooldown = (int) $rcmail->config->get('lifeprisma_ai_rate_limit', 2);
      $max_per_min = (int) $rcmail->config->get('lifeprisma_ai_rate_limit_per_min', 60);

      if ($cooldown <= 0 && $max_per_min <= 0) return true;

      $now = microtime(true);
      $is_bg = in_array($action, ['triage', 'autocomplete', 'detect_tone'], true);
      $session_key = $is_bg ? 'lpai_last_bg_req' : 'lpai_last_req';
      $effective_cooldown = $is_bg ? 0.3 : $cooldown;

      if ($effective_cooldown > 0) {
          $last = isset($_SESSION[$session_key]) ? (float) $_SESSION[$session_key] : 0.0;
          if ($last > 0 && ($now - $last) >= 0 && ($now - $last) < $effective_cooldown) {
              return false;
          }
      }

      if (!$is_bg && $max_per_min > 0) {
          $history = $_SESSION['lpai_req_hist'] ?? [];
          // ... slides window ...
      }
  ```
- **Mathematical & Logic Analysis:**
  For background actions (`triage`, `autocomplete`, `detect_tone`), the condition `if (!$is_bg && $max_per_min > 0)` is evaluated. Because `$is_bg` is `true`, the sliding-window frequency limiter is completely skipped:
  $$\text{MaxRate}_{\text{bg}} = \frac{1}{\text{effective\_cooldown}} = \frac{1}{0.3} \approx 3.33 \text{ req/sec} = 200 \text{ req/min}$$
  If an autocomplete event triggers on each keystroke or a script cycles unread messages, a user can execute up to 200 requests/min, bypassing the configured `lifeprisma_ai_rate_limit_per_min` (default 60) and exhausting Gemini API rate quotas.
- **Corrected Formulation:**
  Background actions must enforce a dedicated sliding window (e.g., $1.5 \times \text{max\_per\_min}$) rather than no window at all.
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -1802,8 +1802,10 @@ class lifeprisma_ai extends rcube_plugin
           }

  -        if (!$is_bg && $max_per_min > 0) {
  -            $history = $_SESSION['lpai_req_hist'] ?? [];
  +        $hist_key = $is_bg ? 'lpai_bg_hist' : 'lpai_req_hist';
  +        $effective_max = $is_bg ? ($max_per_min * 2) : $max_per_min;
  +        if ($effective_max > 0) {
  +            $history = $_SESSION[$hist_key] ?? [];
               if (!is_array($history)) $history = [];
               $history = array_values(array_filter($history, function ($t) use ($now) {
                   return ($now - (float) $t) < 60.0;
  @@ -1811,7 +1813,7 @@ class lifeprisma_ai extends rcube_plugin
  -            if (count($history) >= $max_per_min) return false;
  +            if (count($history) >= $effective_max) return false;
               $history[] = $now;
  -            $_SESSION['lpai_req_hist'] = $history;
  +            $_SESSION[$hist_key] = $history;
           }

           $_SESSION[$session_key] = $now;
  ```

---

## 3. Bugs, Vulnerabilities & Edge-Case Vulnerabilities

### [CRITICAL] Issue 3.1: Arbitrary File Upload (Remote Code Execution) in Template Attachments
- **Location:** [`lifeprisma_ai.php:2086-2098`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2086-L2098)
- **CWE:** CWE-434 (Unrestricted Upload of File with Dangerous Type)
- **Reproduction Scenario:**
  1. An authenticated user sends a POST request to `?_task=mail&_action=plugin.lifeprisma_ai_templates`:
     ```http
     POST /?_task=mail&_action=plugin.lifeprisma_ai_templates HTTP/1.1
     Content-Type: multipart/form-data; boundary=----WebKitFormBoundary
     ...
     ------WebKitFormBoundary
     Content-Disposition: form-data; name="op"
     upload_attachment
     ------WebKitFormBoundary
     Content-Disposition: form-data; name="tpl_id"
     123
     ------WebKitFormBoundary
     Content-Disposition: form-data; name="file"; filename="shell.php"
     Content-Type: application/x-php

     <?php system($_GET['cmd']); ?>
     ------WebKitFormBoundary--
     ```
  2. The server moves the file to `plugins/lifeprisma_ai/data/attachments/templates/123/shell.php`.
  3. The attacker navigates directly to `https://<domain>/plugins/lifeprisma_ai/data/attachments/templates/123/shell.php?cmd=id`, achieving full Remote Code Execution as the web server user (`www-data`/`nginx`).
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -2086,10 +2086,21 @@ class lifeprisma_ai extends rcube_plugin
               $clean_tpl_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $tpl_id);
               $upload_dir = $this->home . '/data/attachments/templates/' . $clean_tpl_id;
               if (!is_dir($upload_dir)) {
  -                @mkdir($upload_dir, 0755, true);
  +                @mkdir($upload_dir, 0750, true);
               }

  -            $safe_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
  +            $orig_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  +            $disallowed_exts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'inc', 'sh', 'cgi', 'pl', 'py', 'exe', 'htaccess'];
  +            if (in_array($orig_ext, $disallowed_exts, true) || empty($orig_ext)) {
  +                echo json_encode(['status' => 'error', 'message' => 'Disallowed or dangerous file extension']);
  +                exit;
  +            }
  +
  +            // Prevent executable file upload and enforce strict whitelist
  +            $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'zip'];
  +            if (!in_array($orig_ext, $allowed_exts, true)) {
  +                echo json_encode(['status' => 'error', 'message' => 'File type not permitted']);
  +                exit;
  +            }
  +            $safe_name = md5(uniqid((string) microtime(), true)) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
               $target_path = $upload_dir . '/' . $safe_name;

               if (!move_uploaded_file($file['tmp_name'], $target_path)) {
  ```

---

### [CRITICAL] Issue 3.2: Arbitrary Local File Read / Exfiltration via Compose Preparation
- **Location:** [`lifeprisma_ai.php:2580-2594`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2580-L2594)
- **CWE:** CWE-22 (Improper Limitation of a Pathname to a Restricted Directory / Path Traversal), CWE-200 (Exposure of Sensitive Information)
- **Reproduction Scenario:**
  1. An attacker sends an AJAX request:
     ```javascript
     $.post('?_task=mail&_action=plugin.lifeprisma_ai_prepare_compose', {
         _token: rcmail.env.request_token,
         reply: 'Check this attachment.',
         subject: 'Sensitive Data',
         attachments: JSON.stringify([{ path: '/etc/passwd', name: 'passwd.txt' }])
     });
     ```
  2. The attacker triggers `rcmail.open_window('?_task=mail&_action=compose')`.
  3. `handle_message_compose()` checks:
     ```php
     $full_path = $att['path'] ?? ''; // '/etc/passwd'
     if (!empty($full_path) && strpos($full_path, '/') !== 0) { ... } // Skipped because it starts with '/'
     if (file_exists($full_path)) {
         $args['attachments'][] = ['path' => $full_path, 'name' => $att['name']];
     }
     ```
  4. Roundcube attaches `/etc/passwd` to the draft. If the path specified is `config/config.inc.php`, the host database credentials, encryption `des_key`, and master configuration are attached to the draft and exfiltrated.
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -2580,11 +2580,18 @@ class lifeprisma_ai extends rcube_plugin
                   foreach ($data['attachments'] as $att) {
                       $full_path = $att['path'] ?? '';
  -                    if (!empty($full_path) && strpos($full_path, '/') !== 0) {
  -                        $full_path = $this->home . '/' . $full_path;
  -                    }
  -                    if (file_exists($full_path)) {
  +                    // Strictly confine attachments to plugin data/attachments directory
  +                    $base_allowed = realpath($this->home . '/data/attachments');
  +                    if (!$base_allowed) continue;
  +                    
  +                    $resolved = realpath(strpos($full_path, '/') === 0 ? $full_path : $this->home . '/' . $full_path);
  +                    if (!$resolved || strpos($resolved, $base_allowed) !== 0) {
  +                        $this->ai_log("[SECURITY] Blocked unauthorized attachment path traversal: " . $full_path);
  +                        continue;
  +                    }
  +                    if (file_exists($resolved)) {
                           $args['attachments'][] = [
  -                            'path' => $full_path,
  +                            'path' => $resolved,
                               'name' => $att['name'] ?? basename($full_path),
                               'mimetype' => $att['mimetype'] ?? 'application/octet-stream',
                           ];
  ```

---

### [CRITICAL] Issue 3.3: Cross-User Tenant Information Disclosure via Global AI Memory
- **Location:** [`lifeprisma_ai.php:2343-2372`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2343-L2372), [`2520-2535`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2520-L2535)
- **CWE:** CWE-359 (Exposure of Private Personal Information), CWE-200 (Exposure of Sensitive Information to an Unauthorized Actor)
- **Reproduction Scenario:**
  1. `User A` (`ceo@company.com`) sends a confidential email negotiating a deal: `"The confidential acquisition offer is $5.2M with 15% escrow"`.
  2. `handle_message_sent()` triggers automatically, strips quotes, and writes the subject and reply into `$this->home . '/data/ai_memory.json'`.
  3. `User B` (`contractor@company.com`) receives an email asking `"What is the acquisition escrow terms?"`.
  4. `handle_triage()` reads `data/ai_memory.json`, matches `acquisition` and `escrow`, and injects `User A`'s confidential text into `User B`'s triage briefing as a `"VERIFIED PREVIOUS CLIENT ANSWER"`.
  5. `User B` now has full visibility into `User A`'s private deal terms without authorization.
- **Remediation Diff:**
  Memory must be partitioned by authenticated user ID:
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -2343,10 +2343,12 @@ class lifeprisma_ai extends rcube_plugin
       public function get_memory_file()
       {
  -        $dir = $this->home . '/data';
  +        $rcmail = rcmail::get_instance();
  +        $user_id = $rcmail->user ? (int) $rcmail->user->ID : 0;
  +        $dir = $this->home . '/data/memory';
           if (!is_dir($dir)) {
  -            @mkdir($dir, 0755, true);
  +            @mkdir($dir, 0750, true);
           }
  -        return $dir . '/ai_memory.json';
  +        return $dir . '/user_' . md5("salt_{$user_id}") . '.json';
       }
  ```

---

### [HIGH] Issue 3.4: Server-Side Request Forgery (SSRF) via Unvalidated Domain Resolution
- **Location:** [`lifeprisma_ai.php:1761-1782`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1761-L1782)
- **CWE:** CWE-918 (Server-Side Request Forgery)
- **Failure Vector:**
  `validate_api_url()` checks if the scheme is `https`, then checks if the host ends in `.googleapis.com`. If neither, it checks `filter_var($host, FILTER_VALIDATE_IP)`. If `$host` is an arbitrary alphanumeric domain (e.g. `https://attacker-domain.com` or `https://internal.company.lan`), `filter_var` returns `false`, falling through to line 1781: `return true;`.
  An attacker or compromised admin can point the endpoint to an external domain configured to resolve to `127.0.0.1` (DNS Rebinding) or an internal microservice, allowing cURL to transmit POST requests with internal authorization headers.
- **Remediation Diff:**
  ```diff
  --- a/lifeprisma_ai.php
  +++ b/lifeprisma_ai.php
  @@ -1774,9 +1774,15 @@ class lifeprisma_ai extends rcube_plugin
               return true;
           }

  -        // Validate IP to prevent SSRF if custom endpoint
  -        if (filter_var($host, FILTER_VALIDATE_IP)) {
  -            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
  +        // Strict DNS resolution & private IP rejection
  +        $resolved_ip = gethostbyname($host);
  +        if (empty($resolved_ip) || $resolved_ip === $host) {
  +            return false;
  +        }
  +
  +        if (!filter_var($resolved_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
  +            $this->ai_log("[SECURITY] Prohibited SSRF target resolving to private IP: " . $resolved_ip);
  +            return false;
           }

           return true;
  ```

---

### [HIGH] Issue 3.5: Email Header Injection (CRLF) in Standalone Worker
- **Location:** [`bin/worker.php:626-643`](file:///home/koen/Git/roundcube-AI/bin/worker.php#L626-L643)
- **CWE:** CWE-93 (Improper Neutralization of CRLF Sequences - 'CRLF Injection')
- **Failure Vector:**
  In `lpai_worker_format_draft_message()`, headers are assembled:
  ```php
  $headers[] = "To: $to";
  $headers[] = "Subject: $re_subject";
  ```
  If an incoming email has a crafted subject or `From` address containing `\r\nBcc: attacker@domain.com`, the worker writes injected headers directly into the draft. When a user clicks "Send", the email is dispatched to unauthorized third parties.
- **Remediation Diff:**
  ```diff
  --- a/bin/worker.php
  +++ b/bin/worker.php
  @@ -611,6 +611,8 @@ function lpai_worker_format_draft_message($to, $subject, $reply_body, $orig_msg
       $date = date('r');
  +    $clean_to = preg_replace('/[\r\n]+/', ' ', trim($to));
  +    $clean_subj = preg_replace('/[\r\n]+/', ' ', trim($re_subject));
  +    $encoded_subj = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($clean_subj, 'UTF-8') : $clean_subj;
  -    $re_subject = (stripos($subject, 'Re:') === 0) ? $subject : 'Re: ' . $subject;

       // Quote original message body
  @@ -628,8 +630,8 @@ function lpai_worker_format_draft_message($to, $subject, $reply_body, $orig_msg
       $headers[] = "Date: $date";
       $headers[] = "From: <$my_email>";
  -    $headers[] = "To: $to";
  -    $headers[] = "Subject: $re_subject";
  +    $headers[] = "To: $clean_to";
  +    $headers[] = "Subject: $encoded_subj";
       $headers[] = "MIME-Version: 1.0";
  ```

---

### [MEDIUM] Issue 3.6: Insecure Default SSL Context in Standalone Worker
- **Location:** [`bin/worker.php:243-249`](file:///home/koen/Git/roundcube-AI/bin/worker.php#L243-L249)
- **CWE:** CWE-295 (Improper Certificate Validation)
- **Failure Vector:**
  `LpaiImapClient::connect()` unconditionally disables SSL verification:
  `'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true`.
  When connecting across local networks or remote hosts, an attacker on the same segment can perform an active SSL MITM and capture plaintext IMAP passwords.
- **Remediation:** Provide an explicit configuration setting `lifeprisma_ai_worker_imap_ssl_verify` (defaulting to `true`).

---

## 4. Architecture, Performance & Resilience Gaps

### Issue 4.1: Database Core Table Pollution & Unindexed Full-Table Scans
- **Location:** [`lifeprisma_ai.php:1675-1732`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1675-L1732)
- **Deficiency:**
  Global admin configuration is stored as a dummy row (`username = '__genia_admin__'`) in Roundcube's core `users` table. In `get_usage_stats()`, it executes:
  ```sql
  SELECT COUNT(*) as active_users FROM users WHERE username != '__genia_admin__' AND preferences LIKE '%genia_%'
  ```
  `preferences` is an unindexed `TEXT` or `LONGTEXT` column containing serialized PHP arrays. In mail servers with $10,000+$ users, running `LIKE '%genia_%'` executes a full-table table scan, reading megabytes of serialized blobs from disk on every admin settings page access.
- **Architectural Solution:**
  1. Store global administrator configuration in Roundcube's native `system` table via `$db->query("SELECT value FROM {$system_table} WHERE name = 'lifeprisma_admin'")` or in `config.inc.php`.
  2. Cache the active user count in the file/Redis cache with a 1-hour TTL instead of executing full-table scans on every HTTP request.

---

### Issue 4.2: Missing Token Usage Accounting in SSE Streaming
- **Location:** [`lifeprisma_ai.php:680-768`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L680-L768)
- **Deficiency:**
  When invoking `handle_stream()`, the stream payload omits `"stream_options": {"include_usage": true}`. OpenAI and Google Gemini API endpoints do not emit token usage objects in streaming chunks unless explicitly requested. As a result, `$stream_tokens` remains `['input' => 0, 'output' => 0]`, and the client UI displays `$0.00` or fails to account for usage.
- **Remediation:** Add `"stream_options" => ["include_usage" => true]` to the request payload and capture usage chunks in `CURLOPT_WRITEFUNCTION`.

---

### Issue 4.3: Unbounded SSE Stream Buffer Growth
- **Location:** [`lifeprisma_ai.php:733-736`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L733-L736)
- **Deficiency:**
  `$stream_buffer .= $data;` accumulates data until a newline `\n` is encountered. If an upstream proxy or gateway errors out and sends a continuous payload without newlines (or a large binary dump), `$stream_buffer` grows until PHP memory limits (`memory_limit`) are exhausted, terminating the script with a fatal error.
- **Remediation:** Enforce a maximum chunk buffer limit (e.g. 64 KB). If `$stream_buffer` exceeds this without newlines, abort the transfer.

---

## 5. Prioritized Action Matrix

| Priority | Category | File / Component | Effort | Expected Impact |
| :---: | :---: | :--- | :---: | :--- |
| **P0** | **Security** | [`lifeprisma_ai.php:2086-2098`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2086-L2098) | **Low** (1h) | **Eliminates Remote Code Execution (RCE)** by enforcing file extension whitelists on template uploads. |
| **P0** | **Security** | [`lifeprisma_ai.php:2580-2594`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2580-L2594) | **Low** (1h) | **Prevents Arbitrary Local File Exfiltration** by confining composer attachment paths with `realpath()`. |
| **P0** | **Security** | [`lifeprisma_ai.php:2343-2372`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2343-L2372) | **Medium** (2h) | **Prevents Cross-Tenant Data Leaks** by isolating AI memory per authenticated user ID. |
| **P1** | **Security** | [`bin/worker.php:628-639`](file:///home/koen/Git/roundcube-AI/bin/worker.php#L628-L639) | **Low** (30m) | **Prevents Email Header Injection (CRLF)** in background worker drafts. |
| **P1** | **Security** | [`lifeprisma_ai.php:1761-1782`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1761-L1782) | **Low** (1h) | **Blocks SSRF & DNS Rebinding** by resolving hostnames and validating against private IP ranges. |
| **P1** | **Logic / Math** | [`lifeprisma_ai.php:2396-2400`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L2396-L2400) | **Low** (15m) | **Prevents Irrelevant Prompt Contamination** by returning empty set on zero memory match scores. |
| **P2** | **Logic / Math** | [`src/lifeprisma_ai.js:1471-1486`](file:///home/koen/Git/roundcube-AI/src/lifeprisma_ai.js#L1471-L1486) | **Low** (1h) | Fixes dynamic pricing lookup and `$0.000000` zero formatting in frontend cost estimation. |
| **P2** | **Performance** | [`lifeprisma_ai.php:1720-1732`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L1720-L1732) | **Medium** (2h) | Eliminates unindexed `users.preferences LIKE '%genia_%'` full-table database scans. |
| **P2** | **Resilience** | [`lifeprisma_ai.php:680-768`](file:///home/koen/Git/roundcube-AI/lifeprisma_ai.php#L680-L768) | **Low** (1h) | Fixes zero token reporting during SSE streaming via `stream_options.include_usage`. |
| **P3** | **Security** | [`bin/worker.php:243-249`](file:///home/koen/Git/roundcube-AI/bin/worker.php#L243-L249) | **Low** (30m) | Enables configurable SSL certificate verification for IMAP daemon. |
