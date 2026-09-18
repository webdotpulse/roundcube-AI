# Gemini Executive Assistant for Roundcube (FYXER Mode)

<p align="center">
  <strong>The autonomous, executive AI assistant for Roundcube webmail — powered exclusively by Google Gemini.</strong><br>
  Instant executive briefings, automated triage, action item checklists, and pre-crafted draft replies modeled after <a href="https://www.fyxer.com/">FYXER</a>.
</p>

<p align="center">
  <a href="#why-fyxer-mode">Why FYXER Mode?</a> •
  <a href="#latest-gemini-models">Latest Models</a> •
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#security--stability">Security & Stability</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/AI%20Engine-Google%20Gemini-4285F4?style=flat-square&logo=google" alt="Google Gemini">
  <img src="https://img.shields.io/badge/Default%20Model-gemini--3.8--flash-34A853?style=flat-square" alt="gemini-3.8-flash">
  <img src="https://img.shields.io/badge/Mode-Autonomous%20(FYXER)-7c3aed?style=flat-square" alt="FYXER Mode">
  <img src="https://img.shields.io/badge/Roundcube-1.5%2B-blue?style=flat-square" alt="Roundcube">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-22c55e?style=flat-square" alt="License">
</p>

---

## Why FYXER Mode?

Most email AI plugins are clunky: they require manual prompting, repetitive copy-pasting, and confusing provider selections.

Modeled after **[FYXER](https://www.fyxer.com/)** and powered exclusively by **Google Gemini**, this assistant acts as an autonomous Chief of Staff right in your Roundcube inbox. 

When you open any email, Gemini has already:
1. **Triaged the Message:** Categorized priority and intent (`Action Required`, `Meeting`, `Follow-up`, `FYI`, or `Security Warning`).
2. **Prepared an Executive Briefing:** 1–2 crisp sentence synopsis plus an interactive action item checklist with deadline tracking.
3. **Drafted Your Reply:** A context-aware draft reply is already prepared and waiting above the email. Click **"Review & Send in Composer"** or adjust the tone in one click (**Concise**, **Professional**, **Friendly**).

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

### 1. Zero-Click Executive Hub (Read View)
- **Automatic Triage Badge:** Color-coded status (`Action Required` in amber/red, `Meeting` in blue, `Follow-up` in purple, `FYI` in emerald).
- **Executive Synopsis:** High-level summary of sender intentions without reading paragraph walls.
- **Action Item Checklist:** Check off action items as you review them; meeting times and calendar invites detected automatically.
- **Pre-Crafted Draft Reply:** Gemini analyzes the thread and prepares an appropriate response.
- **1-Click Review & Send in Composer:** Injects the AI draft cleanly into Roundcube's composer, preserving blockquotes and signatures.
- **Instant Tone Tuning:** Retune the prepared draft instantly with pills (`Concise`, `Professional`, `Friendly`).
- **Phishing & Scam Shield:** Flags suspicious requests, impersonation, or credential harvesting with a 1-click **Move to Spam** action.

### 2. Quick Actions Bar
Directly above the message body for rapid on-demand commands:
- **Translate:** One-click translation into English, Spanish, French, German, Italian, Portuguese, or Dutch.
- **Summarize:** Streamlined point-by-point takeaway generation.
- **Reply with Gemini:** Open full interactive composer assistant.

### 3. Interactive Gemini Modal Panel (`Alt+A`)
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
git clone https://github.com/eduardostern/roundcube-genia.git lifeprisma_ai
cd lifeprisma_ai
cp config.inc.php.dist config.inc.php
```

Enable the plugin in Roundcube's main configuration (`config/config.inc.php`):

```php
$config['plugins'] = [
    // ... other plugins
    'lifeprisma_ai',
];
```

### Option 2: Composer

```bash
cd /path/to/roundcube/
composer require lifeprisma/roundcube-genia
```

---

## Configuration

Copy `config.inc.php.dist` to `config.inc.php` and configure your Google Gemini API key:

```php
<?php

// 1. Your Google Gemini API Key (https://aistudio.google.com/apikey)
$config['lifeprisma_ai_gemini_api_key'] = 'AIzaSy...';

// 2. Default Gemini model (gemini-3.8-flash recommended)
$config['lifeprisma_ai_gemini_model'] = 'gemini-3.8-flash';

// 3. Autonomous Executive Assistant (FYXER Mode)
//   'open'     — (Recommended) Auto-triage, briefing & draft reply on opening email
//   'receive'  — Background triage & draft creation when mail lands in INBOX
//   'disabled' — Manual on-demand only
$config['lifeprisma_ai_auto_draft_mode'] = 'open';

// 4. Smart filter (skips newsletters, bulk marketing, automated notifications)
$config['lifeprisma_ai_auto_draft_filter'] = true;

// 5. Default Language & Tone
$config['lifeprisma_ai_default_language'] = 'English';
$config['lifeprisma_ai_default_tone'] = 'professional';
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
