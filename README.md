# Gemini Executive Assistant for Roundcube

<p align="center">
  <strong>The autonomous, executive AI assistant for Roundcube webmail — powered exclusively by Google Gemini.</strong><br>
  Instant executive briefings, automated triage with IMAP labels, action item checklists, continuous learning memory, template attachments, and pre-crafted draft replies.
</p>

<p align="center">
  <a href="#autonomous-assistant">Autonomous Assistant</a> •
  <a href="#latest-gemini-models">Latest Models</a> •
  <a href="#features">Features</a> •
  <a href="#message-triage--labels">Labels Integration</a> •
  <a href="#ai-memory--learning">AI Memory</a> •
  <a href="#installation">Installation</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#247-offline-background-worker-cli--cron--systemd">Cron Worker</a> •
  <a href="#security--stability">Security & Stability</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/AI%20Engine-Google%20Gemini-4285F4?style=flat-square&logo=google" alt="Google Gemini">
  <img src="https://img.shields.io/badge/Default%20Model-gemini--3.8--flash-34A853?style=flat-square" alt="gemini-3.8-flash">
  <img src="https://img.shields.io/badge/Mode-Autonomous%20Assistant-7c3aed?style=flat-square" alt="Autonomous Assistant">
  <img src="https://img.shields.io/badge/Labels-roundcube--labels%20Compatible-f59e0b?style=flat-square" alt="Labels Compatible">
  <img src="https://img.shields.io/badge/Roundcube-1.5%2B-blue?style=flat-square" alt="Roundcube">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-22c55e?style=flat-square" alt="License">
</p>

---

## Autonomous Executive Assistant

Most email AI plugins are clunky: they require manual prompting, repetitive copy-pasting, and confusing provider selections.

Powered exclusively by **Google Gemini**, this assistant acts as an autonomous Chief of Staff right in your Roundcube inbox. 

When you open any email, Gemini has already:
1. **Triaged the Message:** Categorized priority and intent (`Action Required`, `Meeting`, `Follow-up`, `FYI`, or `Security Warning`) and tagged it with standard IMAP labels compatible with [roundcube-labels](https://github.com/webdotpulse/roundcube-labels).
2. **Consulted AI Memory:** Checked past verified answers and learned knowledge to replicate consistent answers when a similar question is asked by another client.
3. **Prepared an Executive Briefing:** 1–2 crisp sentence synopsis plus an interactive action item checklist with deadline tracking.
4. **Drafted Your Reply with Attachments:** A context-aware draft reply is already prepared and waiting above the email, complete with selectable attachments and templates. Click **"Review & Send in Composer"** or adjust the tone in one click (**Concise**, **Professional**, **Friendly**).

---

## Latest Gemini Models (2026 Roster)

This plugin is engineered to harness Google's latest Gemini model family:

| Model | Context Window | Max Output Tokens | Thinking Levels | Ideal Use Case |
|---|---|---|---|---|
| **`gemini-3.8-flash`** *(Default)* | 1,000,000 | 64,000 | Tunable (Low, Med, High) | **Flagship:** The newest and most intelligent Flash model. Instant email triage, executive summaries, and high-precision drafting. |
| **`gemini-3.8-flash-cyber`** | 1,000,000 | 64,000 | Tunable | **Specialized Security:** Vulnerability management, phishing analysis, and fraud detection. |
| **`gemini-3.7-flash`** | 1,000,000 | 64,000 | Standard | Previous iteration, fully supported for robust enterprise workflows. |
| **`gemini-3.6-flash`** | 1,000,000 | 64,000 | Standard | High-efficiency model released earlier in 2026. |
| **`gemini-3.5-flash`** | 1,000,000 | 64,000 | Standard | Optimized for scale, agentic workflows, and fast multimodal tasks. |
| **`gemini-3.5-flash-lite`** | 1,000,000 | 64,000 | Fast | Ultra-fast and lightweight model for high-volume email processing at minimal cost. |

---

## Features

### 1. Seamless Sidebar Integration
- Replaces disruptive floating buttons with an elegant **purple Gemini font icon** (`#9333ea`) directly docked in your Roundcube main navigation sidebar (`#taskmenu`), fully aligned across default Elastic and Roundcube Plus `gmail_plus` skins.

### 2. Zero-Click Executive Hub (Read View)
- **Automatic Triage Badge:** Color-coded status (`Action Required` in amber/red, `Meeting` in blue, `Follow-up` in purple, `FYI` in emerald).
- **Executive Synopsis:** High-level summary of sender intentions without reading paragraph walls.
- **Action Item Checklist:** Check off action items as you review them; meeting times and calendar invites detected automatically.
- **Pre-Crafted Draft Reply:** Gemini analyzes the thread and prepares an appropriate response.
- **Attachments in Replies & Templates:** Choose original attachments or predefined template documents to automatically attach when sending the draft to the composer.
- **1-Click Review & Send in Composer:** Injects the AI draft cleanly into Roundcube's composer, carrying forward attachments, blockquotes, and signatures.
- **Instant Tone Tuning:** Retune the prepared draft instantly with pills (`Concise`, `Professional`, `Friendly`).
- **Phishing & Scam Shield:** Flags suspicious requests, impersonation, or credential harvesting with a 1-click **Move to Spam** action.

### 3. Message Triage & Labels (`roundcube-labels`)
- Deep integration with [roundcube-labels](https://github.com/webdotpulse/roundcube-labels) via Thunderbird standard IMAP flags (`$Label1` through `$Label5`).
- During triage (in the browser or 24/7 background worker), emails are automatically flagged:
  - `$Label1` (Belangrijk / Important / Red) &rarr; `action_required` / `security_warning`
  - `$Label2` (Werk / Work / Orange) &rarr; `meeting`
  - `$Label3` (Persoonlijk / Personal / Green) &rarr; `fyi`
  - `$Label4` (Te doen / To do / Blue) &rarr; `follow_up`
- Displays colorful label badges directly inside message list rows and synchronized in real time.

### 4. Continuous Learning & AI Memory
- **Learns and Remembers:** When clients send similar questions to ones already resolved, Gemini recalls previous answers and replicates them with appropriate context.
- **Manual "Remember this Answer" Button:** Save high-quality answers with one click directly from the Executive Hub.
- **Auto-Learn on Sent:** Automatically saves user-edited answers when sending emails, indexing client questions and final responses into persistent per-user memory (`<rcube_temp>/gemini_memory_<user_id>.json`).

### 5. Quick Actions Bar
Directly above the message body for rapid on-demand commands:
- **Translate:** One-click translation into English, Spanish, French, German, Italian, Portuguese, or Dutch.
- **Summarize:** Streamlined point-by-point takeaway generation.
- **Reply with Gemini:** Open full interactive composer assistant.

### 6. Interactive Gemini Modal Panel (`Alt+A`)
- **Compose:** Generate emails from quick bullet points or instructions.
- **Rewrite:** Refactor existing drafts with selected tone and style.
- **Fix Grammar:** Clean up syntax, typos, and style while preserving your authentic voice.
- **Subject Generator:** Generates high-converting, relevant email subject lines.
- **Real-Time Token & Cost Estimation:** Displays live cost estimates per request based on Google Gemini pricing.

---

## Installation

### Option 1: Git Clone (Recommended)

```bash
cd /path/to/roundcube/plugins/
git clone https://github.com/webdotpulse/roundcube-AI.git lifeprisma_ai
cd lifeprisma_ai
cp config.inc.php.dist config.inc.php
```

Enable the plugin in Roundcube's main configuration (`config/config.inc.php`):

```php
$config['plugins'] = [
    // ... other plugins
    'roundcube-labels', // optional companion plugin
    'lifeprisma_ai',
];
```

### Option 2: Composer

```bash
cd /path/to/roundcube/
composer require webdotpulse/roundcube-ai
```

> [!TIP]
> **Automatic Extra Content Installation:** When running `composer install` or `composer require`, all bundled extra content is automatically deployed into your Roundcube environment:
> - **Skins:** GMail+ (`skins/gmail_plus`)
> - **Roundcube Plus Framework:** `plugins/xskin` and `plugins/xframework`
> - **Companion Plugins:** `plugins/customizr`, `plugins/thread_drafts`, and `plugins/thunderbird_labels`
> - Default configuration files (`config.inc.php`) are safely initialized without overwriting existing settings.
>
> You can also run or re-run the installer at any time:
> ```bash
> php plugins/lifeprisma_ai/bin/install-extra.php --activate
> ```

---

## Configuration

Copy `config.inc.php.dist` to `config.inc.php` and configure your Google Gemini API key:

```php
<?php

// 1. Your Google Gemini API Key (https://aistudio.google.com/apikey)
$config['lifeprisma_ai_gemini_api_key'] = 'AIzaSy...';

// 2. Default Gemini model (gemini-3.8-flash recommended)
$config['lifeprisma_ai_gemini_model'] = 'gemini-3.8-flash';

// 3. Autonomous Executive Assistant Mode
//   'open'     — (Recommended) Auto-triage, briefing & draft reply on opening email
//   'receive'  — Background triage & draft creation when mail lands in INBOX
//   'disabled' — Manual on-demand only
$config['lifeprisma_ai_auto_draft_mode'] = 'open';

// 4. IMAP Label Integration (compatible with roundcube-labels)
$config['lifeprisma_ai_triage_labels_enabled'] = true;
$config['lifeprisma_ai_triage_label_map'] = [
    'action_required'  => '$Label1', // Belangrijk / Important (Red)
    'meeting'          => '$Label2', // Werk / Work (Orange)
    'fyi'              => '$Label3', // Persoonlijk / Personal (Green)
    'follow_up'        => '$Label4', // Te doen / To Do (Blue)
    'security_warning' => '$Label1', // Belangrijk / Important (Red)
];

// 5. Continuous Learning & Memory
$config['lifeprisma_ai_memory_enabled'] = true;
$config['lifeprisma_ai_memory_auto_learn'] = true;
$config['lifeprisma_ai_memory_max_items'] = 150;

// 6. Template & Reply Attachments
$config['lifeprisma_ai_attachments_enabled'] = true;

// 7. Default Language & Tone
$config['lifeprisma_ai_default_language'] = 'English';
$config['lifeprisma_ai_default_tone'] = 'professional';
```

---

## Skin Compatibility

The plugin includes native support and dedicated assets for both default Roundcube and Roundcube Plus commercial skins:

- **Elastic (Default):** Seamlessly integrates with standard Roundcube layout and responsive CSS.
- **GMail+ (`roundcube_plus_skin_gmail_plus`):** 
  - Dedicated styling matching modern web interfaces.
  - **Sidebar Docking:** Placed natively as a purple Gemini font/SVG icon (`#taskmenu-gemini-btn`) inside `#taskmenu` without obscuring email contents or conflicting with right-hand drawer menus.
  - **Widescreen 3-Pane Triage:** Automatically detects message preview switches in widescreen layouts and re-triages/briefs in real time without requiring a full page refresh.
  - Full support for **Dark Mode** and custom color schemes.

---

## 24/7 Offline Background Worker (CLI / Cron / Systemd)

Want Gemini to triage emails, set IMAP labels, and prepare draft replies **around the clock even when you are completely logged out of Roundcube and your computer is turned off**?

> [!TIP]
> For a detailed, step-by-step setup guide with copy-paste commands, see the dedicated [**Cron Worker Activation Guide**](docs/CRON_WORKER_SETUP.md) or open the interactive configurator [**CRON_WORKER_SETUP.html**](docs/CRON_WORKER_SETUP.html) to adapt commands dynamically to your server paths.

### Quick Activation (Linux Cron)

Add the worker to your crontab (`crontab -e`) to run every 3 minutes:

```bash
*/3 * * * * php /path/to/roundcube/plugins/lifeprisma_ai/bin/worker.php >> /var/log/lifeprisma_ai_worker.log 2>&1
```

### CLI Command Reference

```bash
php bin/worker.php --help           # Show command reference
php bin/worker.php                  # Run a single pass across configured accounts
php bin/worker.php --daemon         # Run continuously in background
php bin/worker.php --interval=30    # Custom poll interval (e.g. 30 seconds)
php bin/worker.php --dry-run        # Test triage without saving to IMAP
php bin/worker.php --account=user   # Process a specific email account only
php bin/worker.php --verbose        # Extra debug output
```

---

## Security & Stability

Built specifically for high-reliability enterprise email environments:

1. **No Session Lock Contention:**
   - PHP sessions are immediately released via `session_write_close()` before initiating any Google Gemini cURL API calls. The Roundcube UI remains snappy and never freezes.
2. **Strict SSRF Protection:**
   - All external outbound requests are restricted to Google Gemini API hostnames (`generativelanguage.googleapis.com` / `*.googleapis.com`). RFC 1918 private IPs, AWS/GCP metadata endpoints (`169.254.169.254`), and loopback addresses (`127.0.0.1`) are hard-blocked.
3. **CRLF & Header Injection Immune:**
   - All email headers (`Subject`, `To`, `References`, `In-Reply-To`) created by auto-drafting are strictly sanitized with `rcube_mime::encode_header` and regex stripped of `\r` and `\n` to prevent SMTP header splitting.
4. **CSRF & XSS Hardened:**
   - Every AJAX and streaming endpoint verifies Roundcube's `_token` anti-CSRF token.
   - All dynamic HTML in the executive hub is sanitized using `lpai_escape_html` before DOM insertion.
5. **Type Safe with PHP 8.x:**
   - Safely unwraps Roundcube `rcube_result_set` objects via `->get()` to prevent PHP 8 `TypeError` crashes during inbox scans.

---

## Keyboard Shortcuts

| Shortcut | Action |
|---|---|
| `Alt+A` | Open / toggle Gemini Assistant panel |
| `Enter` | Submit prompt / request |
| `Shift+Enter` | New line in instruction area |
| `Escape` | Close Gemini panel |

---

## License

MIT License — Free to use, modify, and distribute.
