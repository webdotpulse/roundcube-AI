# Roundcube Thread Drafts Plugin

[![Roundcube Webmail Plugin](https://img.shields.io/badge/Roundcube-Plugin-blue.svg)](https://roundcube.net/)
[![License: GPL-3.0-or-later](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](LICENSE.md)

A Roundcube Webmail plugin that **seamlessly includes active unsent drafts into conversation threads**.

---

## The Problem

Standard conversation threading in Roundcube groups messages using RFC headers (`In-Reply-To` and `References`) belonging to sent or received messages in the current folder. However, active unsent drafts typically reside only in the **Drafts** folder until sent. This means when reviewing a thread in your Inbox or Archive, you cannot see that you (or an auto-save) already started drafting a reply, requiring you to manually check the Drafts folder.

## The Solution

**`thread_drafts`** bridges this gap:

1. **Integrated Conversation Threads**: Active drafts from your Drafts folder that belong to threads currently in view are dynamically embedded into the conversation tree right beneath their parent message.
2. **Visual Distinction**: Draft rows feature a clean, modern **`[Draft]`** pill badge, italicized subject, and recipient formatting (`To: ...`).
3. **Thread Root Indicators**: Thread root messages display a **`[Draft]`** badge so you instantly know a conversation contains an active draft—even when the thread is collapsed.
4. **Instant Resume Editing**: Clicking the subject link or double-clicking the draft row immediately opens the Roundcube compose screen with your draft loaded.
5. **Message View Alert**: When viewing an email that has an active draft reply, a helpful notice appears with a 1-click **"Resume Draft"** button.
6. **Dark Mode & Elastic Ready**: Crafted to look stunning in both light and dark modes of Roundcube's Elastic skin as well as legacy skins.

---

## How It Works

```
Inbox Conversation Thread:
├─ [Root] Client: "Feedback on proposal" (UID: 101, Depth: 0) [Draft]
│  ├─ You: "Re: Feedback on proposal" (UID: 102, Depth: 1)
│  ├─ Client: "Re: Feedback on proposal" (UID: 103, Depth: 1)
│  └─ [Draft] You: "Re: Feedback on proposal" (UID: 12-Drafts, Depth: 2) -> Click to edit!
```

- **Header Matching**: Uses `In-Reply-To` and `References` headers according to RFC 5322 and RFC 5256 threading standards.
- **Tree Splicing**: Accurately computes message depth and parent UIDs, ensuring standard tree branch lines and expand/collapse actions work without modifying core Roundcube files.
- **Cross-Folder Integration**: Employs Roundcube's native multi-folder UID format (`<UID>-<MBOX>`), allowing delete, move, preview, and compose operations to work natively.

---

## Installation

### Manual Installation

1. Copy or clone the `roundcube-thread-drafts` directory into your Roundcube `plugins/` directory:
   ```bash
   cd /path/to/roundcube/plugins
   git clone https://github.com/webdotpulse/roundcube-thread-drafts.git thread_drafts
   ```
2. Enable the plugin in your Roundcube configuration (`config/config.inc.php`):
   ```php
   $config['plugins'] = [
       // ... other plugins ...
       'thread_drafts',
   ];
   ```
3. (Optional) Copy and adjust the plugin configuration:
   ```bash
   cp plugins/thread_drafts/config.inc.php.dist plugins/thread_drafts/config.inc.php
   ```

### Via Composer

```bash
composer require webdotpulse/roundcube-thread-drafts
```

---

## Configuration

The default configuration options are provided in `config.inc.php.dist`:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `thread_drafts_enabled` | `bool` | `true` | Enable or disable the plugin globally. |
| `thread_drafts_show_root_badge` | `bool` | `true` | Display a `[Draft]` badge on thread root messages when a child draft exists. |
| `thread_drafts_show_message_banner` | `bool` | `true` | Display a banner with "Resume Draft" button when reading an email with a draft reply. |
| `thread_drafts_max_drafts` | `int` | `50` | Maximum number of recent drafts to scan (performance guard). |
| `thread_drafts_subject_fallback` | `bool` | `false` | Enable normalized subject matching when In-Reply-To/References are missing. |

Users can also toggle their personal preferences under **Settings -> Preferences -> Mailbox View**.

---

## Localization

Supported languages:
- English (`en_US`)
- Dutch (`nl_NL`)
- German (`de_DE`)
- French (`fr_FR`)

---

## License

This plugin is released under the [GNU General Public License v3](LICENSE.md).
