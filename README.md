# Roundcube AI & Enterprise Plugin Suite

<p align="center">
  <strong>Autonomous Executive AI Assistant & Enterprise Plugin Suite for Roundcube Webmail</strong><br>
  Powered by Google Gemini 3.8 Flash • 17 Production-Grade Plugins • Multi-Skin Support (GMail+, GMail, Elastic, Larry) • Complete Database Migrations (SQLite, MySQL, PostgreSQL) • Automated Playwright E2E Matrix Verified
</p>

<p align="center">
  <a href="#overview">Overview</a> •
  <a href="#gemini-ai-assistant">Gemini AI Assistant</a> •
  <a href="#plugin-suite">Plugin Suite</a> •
  <a href="#installation--setup">Installation</a> •
  <a href="#database-migrations">Database Migrations</a> •
  <a href="#background-workers--cron">Workers & Cron</a> •
  <a href="#skins--responsiveness">Skins</a> •
  <a href="#testing--verification">Testing & E2E Matrix</a> •
  <a href="#security--stability">Security</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/AI%20Engine-Google%20Gemini-4285F4?style=flat-square&logo=google" alt="Google Gemini">
  <img src="https://img.shields.io/badge/Default%20Model-gemini--3.8--flash-34A853?style=flat-square" alt="gemini-3.8-flash">
  <img src="https://img.shields.io/badge/Plugins%20Bundled-17%20Active-7c3aed?style=flat-square" alt="17 Active Plugins">
  <img src="https://img.shields.io/badge/Skins-GMail%2B%20%7C%20GMail%20%7C%20Elastic%20%7C%20Larry-f59e0b?style=flat-square" alt="Supported Skins">
  <img src="https://img.shields.io/badge/PHPUnit%20Tests-24%2F24%20Passing%20(100%25)-success?style=flat-square" alt="PHP Tests Passing">
  <img src="https://img.shields.io/badge/Playwright%20E2E-8%2F8%20Matrix%20Cells%20(0%20Errors)-blue?style=flat-square" alt="Playwright E2E Verified">
  <img src="https://img.shields.io/badge/Roundcube-1.5%2B%20%2F%201.6%2B-blue?style=flat-square" alt="Roundcube">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-22c55e?style=flat-square" alt="License">
</p>

---

## Overview

The `roundcube-AI` repository bundles a complete, enterprise-grade suite of plugins and custom skins anchored by an autonomous, executive AI assistant powered exclusively by **Google Gemini**.

From automated inbox triage and SSE streaming draft replies to CalDAV calendar scheduling, rich signature generation, two-factor authentication, and asynchronous delivery queues, this repository transforms standard Roundcube into a modern, unified communication and productivity workspace.

---

## Gemini AI Assistant (`lifeprisma_ai` / `roundcube_ai`)

Acts as an autonomous Chief of Staff right in your Roundcube inbox without clunky manual prompting or repetitive copy-pasting.

### Key Capabilities
1. **Zero-Click Executive Briefing (Read View):**
   - Automatically summarizes complex threads into a 1–2 sentence executive synopsis upon opening.
   - Generates interactive action item checklists with deadline tracking and calendar meeting extraction.
   - Detects phishing, impersonation, or credential harvesting with a 1-click **Move to Spam** shield.
2. **Context-Aware Pre-Crafted Draft Replies:**
   - Prepares relevant draft replies above the email before you even click reply.
   - Selectable attachments and response templates.
   - 1-click **"Review & Send in Composer"** or instant tone adjustment (**Concise**, **Professional**, **Friendly**, **Formal**, **Direct**).
3. **IMAP Label Integration (`thunderbird_labels`):**
   - Synchronizes triage priority directly to standard Thunderbird IMAP flags (`$Label1` through `$Label5`):
     - `$Label1` (Important / Red) &rarr; `action_required` / `security_warning`
     - `$Label2` (Work / Orange) &rarr; `meeting`
     - `$Label3` (Personal / Green) &rarr; `fyi`
     - `$Label4` (To Do / Blue) &rarr; `follow_up`
4. **Continuous Learning & AI Memory:**
   - Indexes verified answers and client interactions into per-user memory (`gemini_memory_<user_id>.json`).
   - Automatically replicates consistent answers when recurring questions are asked by different contacts.
5. **Interactive Assistant Modal (`Alt+A`):**
   - Custom floating modal with live model selector (`gemini-3.8-flash`, `gemini-3.7-flash`), tone selector, action buttons (Summarize, Draft Reply, Expand, Polish, Grammar, Subject Lines), and real-time SSE streaming.

### Supported Gemini Models

| Model | Context Window | Output Tokens | Thinking Levels | Primary Use Case |
|---|---|---|---|---|
| **`gemini-3.8-flash`** *(Default)* | 1,000,000 | 64,000 | Tunable (Low, Med, High) | **Flagship:** High-precision email triage, executive briefings, and streaming drafting. |
| **`gemini-3.8-flash-cyber`** | 1,000,000 | 64,000 | Tunable | **Security:** Phishing detection, vulnerability analysis, and fraud auditing. |
| **`gemini-3.7-flash`** | 1,000,000 | 64,000 | Standard | High-performance enterprise drafting and translation. |
| **`gemini-3.6-flash`** | 1,000,000 | 64,000 | Standard | High-throughput background triage. |
| **`gemini-3.5-flash`** | 1,000,000 | 64,000 | Standard | Agentic operations and multi-account scanning. |
| **`gemini-3.5-flash-lite`** | 1,000,000 | 64,000 | Fast | Ultra-fast, cost-efficient bulk email triage. |

---

## Bundled Enterprise Plugin Suite

This repository includes 17 interoperable, hardened plugins:

| Plugin | Purpose & Functionality | Database Migrations |
| :--- | :--- | :--- |
| **`roundcube_ai` / `lifeprisma_ai`** | Google Gemini executive assistant, triage, memory, and composer integration. | Preferences / JSON memory cache |
| **`xframework`** | Foundation library providing Ajax utilities, CSRF validation, assets pipeline, and UI helpers. | Shared framework schema |
| **`xskin`** | Real-time theme switcher, custom color palettes, and skin compatibility engine. | User preferences |
| **`customizr`** | Dynamic logo branding, watermark SVG sanitation, and favicon customizer. | Asset storage |
| **`xcalendar`** | Full-featured calendar supporting Month/Week/Day/Agenda views, CalDAV, alarms, RRULE recurrence, and audio previews. | `xcalendars`, `xevents`, `xattachments` |
| **`xsignature`** | Rich HTML signature designer with custom logos, social media icon collections (7 styles), and XSS sanitization. | `xsignatures`, `xsignature_images` |
| **`xmultibox`** | Multi-account and multi-mailbox switcher with synchronized unread counts. | `xmultibox_accounts` |
| **`email_scheduler`** | Scheduled email dispatch with flatpickr date/time picker and atomic database queue processing. | `email_scheduler_queue` |
| **`merge_and_fix`** | Addressbook duplicate contact detector, automated merger, and contact conflict resolver. | CardDAV / Contact mappings |
| **`newsletter`** | Bulk campaign manager, subscriber list management, bounce tracking, and queue dispatcher. | `newsletter_campaigns`, `newsletter_subscribers`, `newsletter_logs` |
| **`persistent_login`** | Secure remember-me authentication token provider for persistent user sessions. | `persistent_logins` |
| **`thunderbird_labels`** | Visual tagging, multi-label assignment, color badges, and filter bars compatible with Thunderbird. | Message flags / preferences |
| **`twofactor_auth`** | Multi-factor authentication provider supporting TOTP (Google Authenticator, Authy). | User 2FA secrets |
| **`vacation_forward`** | Out-of-office auto-responder, date ranges, email forwarder rules, and template variables. | `vacation_forward_logs`, `vacation_forward_templates` |
| **`roundcube_loader`** | Asynchronous asset preloader for ultra-fast initial page rendering. | Session cache |
| **`roundcube_attachments`** | Multi-file actions, bulk downloads, and inline attachment previews (PDF, CSV, Images). | Temp storage |
| **`thread_drafts`** | Conversation thread collapsing/expanding with draft state indicators. | IMAP thread metadata |

---

## Installation & Setup

### 1. Automated Installation (Recommended)

Run the bundled installation script from your Roundcube root directory to symlink/copy all skins and plugins, generate default configs, and register all components in `config/config.inc.php`:

```bash
cd /path/to/roundcube/
php plugins/lifeprisma_ai/bin/install-extra.php --activate
```

### 2. Manual Configuration (`config/config.inc.php`)

Ensure all 17 plugins are registered in `config/config.inc.php` in the correct dependency order:

```php
// Active Skin: 'gmail_plus', 'gmail', 'elastic', or 'larry'
$config['skin'] = 'gmail_plus';

// Active Plugins (xframework and xskin must precede dependent UI plugins)
$config['plugins'] = [
    'xframework',
    'xskin',
    'customizr',
    'thread_drafts',
    'thunderbird_labels',
    'xcalendar',
    'roundcube_loader',
    'xmultibox',
    'xsignature',
    'roundcube_attachments',
    'twofactor_auth',
    'persistent_login',
    'email_scheduler',
    'merge_and_fix',
    'newsletter',
    'vacation_forward',
    'lifeprisma_ai', // or 'roundcube_ai'
];

// Roundcube Plus licensing & vendor branding removal
$config['license_key'] = 'RCPLUSFREE20266u';
$config['remove_vendor_branding'] = true;
```

### 3. AI Plugin Configuration (`plugins/lifeprisma_ai/config.inc.php`)

```php
// 1. Google Gemini API Key (https://aistudio.google.com/apikey)
$config['lifeprisma_ai_gemini_api_key'] = 'AIzaSy...';

// 2. Default Model
$config['lifeprisma_ai_gemini_model'] = 'gemini-3.8-flash';

// 3. Autonomous Executive Assistant Mode ('open', 'receive', or 'disabled')
$config['lifeprisma_ai_auto_draft_mode'] = 'open';

// 4. IMAP Label Integration
$config['lifeprisma_ai_triage_labels_enabled'] = true;

// 5. Continuous Learning & Memory
$config['lifeprisma_ai_memory_enabled'] = true;
$config['lifeprisma_ai_memory_auto_learn'] = true;
```

---

## Database Migrations

The plugin suite includes database schemas for **MySQL**, **PostgreSQL**, and **SQLite**. Execute the schemas for your chosen engine located under each plugin's `SQL/` directory:

```bash
# SQLite (Example)
sqlite3 roundcube.db < plugins/xcalendar/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/xsignature/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/xmultibox/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/email_scheduler/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/newsletter/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/persistent_login/SQL/sqlite.sql
sqlite3 roundcube.db < plugins/vacation_forward/SQL/sqlite.sql

# MySQL / MariaDB (Example)
mysql -u roundcube -p roundcubemail < plugins/xcalendar/SQL/mysql.sql
mysql -u roundcube -p roundcubemail < plugins/email_scheduler/SQL/mysql.sql
# ... (repeat for respective plugins)

# PostgreSQL (Example)
psql -U roundcube -d roundcubemail -f plugins/xcalendar/SQL/postgres.sql
psql -U roundcube -d roundcubemail -f plugins/email_scheduler/SQL/postgres.sql
# ... (repeat for respective plugins)
```

---

## Background Workers & Cron Daemons

Ensure the background daemons and cron tasks are scheduled in your system crontab (`crontab -e`):

```bash
# 1. 24/7 Gemini Offline Triage Worker (Every 3 minutes)
*/3 * * * * php /path/to/roundcube/plugins/lifeprisma_ai/bin/worker.php >> /var/log/lifeprisma_worker.log 2>&1

# 2. Email Scheduler Dispatcher (Every minute)
* * * * * php /path/to/roundcube/plugins/email_scheduler/cron.php >> /var/log/email_scheduler.log 2>&1

# 3. Vacation & Out-of-Office Auto-Reply Check (Every 5 minutes)
*/5 * * * * php /path/to/roundcube/plugins/vacation_forward/cron.php >> /var/log/vacation_forward.log 2>&1

# 4. Newsletter Campaign Batch Dispatcher (Every 15 minutes)
*/15 * * * * php /path/to/roundcube/plugins/newsletter/cron.php >> /var/log/newsletter.log 2>&1
```

### CLI Worker Reference
```bash
php bin/worker.php --help           # Show command reference
php bin/worker.php                  # Run a single pass across active accounts
php bin/worker.php --daemon         # Run continuously as a background service
php bin/worker.php --interval=30    # Custom poll interval (seconds)
php bin/worker.php --dry-run        # Test triage without writing to IMAP
php bin/worker.php --account=user   # Process a specific user account only
```

---

## Skins & Responsive Compatibility

All plugins are styled and verified across four distinct skins:

1. **`gmail_plus`**: Modern commercial-grade skin featuring widescreen 3-pane layouts, floating action buttons, dark mode support, and dedicated sidebar AI docking.
2. **`gmail`**: Clean Gmail-style interface with native dropdown enhancements and minimal toolbar clutter.
3. **`elastic`**: The official responsive Roundcube skin, fully supported on both desktop and mobile viewports.
4. **`larry`**: Classic Roundcube interface with full legacy theme styling and context menus.

---

## Testing & Quality Assurance

### 1. Backend PHP Unit & Integration Tests (100% Pass)

Execute the comprehensive test suite across all 24 component test files:

```bash
for t in tests/*Test.php; do
    echo "Running $t..."
    php "$t" || exit 1
done
```

**Results:**
- `AiModalSelectTest.php` (43/43 assertions passed)
- `AuditAndSecurityFixesTest.php` (19/19 assertions passed)
- `ThunderbirdLabelsTest.php` (54/54 assertions passed)
- `EmailSchedulerTest.php`, `AiTriageTest.php`, `AiSecurityAndLogicTest.php`, `MergeAndFixTest.php`, etc.
- **Total: 24 / 24 test suites passed with zero failures.**

### 2. Combinatorial Playwright Browser E2E Matrix

A headless browser test runner exercises all interactive workflows across a 4 Skin × 2 Viewport matrix:

```bash
cd tests/e2e
npm install
node run_combinatorial_matrix.js
```

#### Matrix Execution Results

```text
===============================================================
COMBINATORIAL E2E VERIFICATION REPORT
===============================================================
Combinations Tested: 8 / 8 PASSED (100%)
Total Interactive Actions: 382
Buttons & Links Clicked:  199
Forms & Fields Submitted:  8
Modals & Dialogs Exercised: 28
Total Uncaught Console/HTTP Errors: 0
---------------------------------------------------------------
MATRIX BREAKDOWN:
  ✓ PASS | Skin: gmail_plus  | Viewport: Desktop (1920x1080)  | Actions: 50 | Errors: 0
  ✓ PASS | Skin: gmail_plus  | Viewport: Mobile (375x812)     | Actions: 50 | Errors: 0
  ✓ PASS | Skin: gmail       | Viewport: Desktop (1920x1080)  | Actions: 48 | Errors: 0
  ✓ PASS | Skin: gmail       | Viewport: Mobile (375x812)     | Actions: 48 | Errors: 0
  ✓ PASS | Skin: elastic     | Viewport: Desktop (1920x1080)  | Actions: 50 | Errors: 0
  ✓ PASS | Skin: elastic     | Viewport: Mobile (375x812)     | Actions: 50 | Errors: 0
  ✓ PASS | Skin: larry       | Viewport: Desktop (1920x1080)  | Actions: 41 | Errors: 0
  ✓ PASS | Skin: larry       | Viewport: Mobile (375x812)     | Actions: 45 | Errors: 0
===============================================================
```

---

## Security & Defensive Hardening

1. **Stored XSS Elimination (`xsignature.php`):**
   - Implemented `sanitizeHtmlSignature()` using `rcube_washtml` and `clean_html()` to strip malicious tags (`<script>`, `<iframe>`, `<style>`, `<form>`), inline event listeners (`onerror`, `onload`, `onclick`), and pseudo-protocols (`javascript:`, `vbscript:`).
2. **CSRF Enforcement Across All State-Modifying Endpoints:**
   - Real anti-CSRF token verification (`request_security_check`) enforced across `xframework`, `xcalendar` (iTip handlers), `vacation_forward`, and `merge_and_fix`.
3. **Atomic Delivery Concurrency (`email_scheduler.php`):**
   - Implemented atomic row locking using `UPDATE ... SET status = 'processing' WHERE id = ? AND status IN ('scheduled', 'delayed')` to prevent double delivery under concurrent cron execution.
4. **RFC 5545 Recurrence & EXDATE Filtering (`xcalendar`):**
   - Corrected recurrence iterator boundaries and enforced `EXDATE` cancellation exclusion mapping to guarantee accurate calendar sync.
5. **No Session Lock Contention:**
   - PHP sessions are closed early via `session_write_close()` before initiating external LLM network requests, ensuring the UI remains completely responsive.
6. **Strict SSRF & Injection Protection:**
   - External requests restricted to `generativelanguage.googleapis.com` (blocking private/RFC1918 IPs and cloud metadata endpoints).
   - Strict CRLF stripping on all email headers generated by automated drafts.

---

## Keyboard Shortcuts

| Shortcut | Context | Action |
|---|---|---|
| `Alt+A` | Compose / Message View | Open / toggle Gemini AI Assistant modal |
| `Enter` | AI Modal | Submit prompt / generate response |
| `Shift+Enter` | AI Modal | Insert newline in prompt textarea |
| `Escape` | AI Modal / Event Dialog | Close open modal or dialog |

---

## License

This project is open-source software licensed under the [MIT License](LICENSE).
