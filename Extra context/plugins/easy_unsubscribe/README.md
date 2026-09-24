# Easy Unsubscribe Roundcube Plugin

A production-ready Roundcube Webmail plugin that brings native **Gmail-style "Unsubscribe"** functionality to your webmail interface.

When an opened email contains standard list-unsubscribe headers, the plugin displays a clean, prominent **"Unsubscribe"** button immediately next to the sender information in the message header view. Users can opt out with a single click or minimal friction.

---

## Features

- **RFC 8058 One-Click POST Support:** Automatically detects `List-Unsubscribe-Post: List-Unsubscribe=One-Click` and sends an asynchronous HTTP POST request to the provider's HTTPS endpoint. Does not follow HTTP 3xx redirects (per RFC 8058 specification).
- **RFC 2369 Automated Mailto Dispatch:** Automatically composes and dispatches an unsubscribe email from the user's active identity to the parsed `mailto:` address using Roundcube's internal mail-sending API.
- **External Web Link Fallback:** For mailing lists requiring web-based unsubscription, prompts the user and opens the provider's opt-out page in a safe window (`rel="noopener noreferrer"`).
- **Native Roundcube Look & Feel:** Seamlessly styled for both **Elastic** (modern flat design, dark mode aware) and **Larry** skins.
- **Security & Privacy First:**
  - Full CSRF token validation on all AJAX actions (`request_security_check`).
  - Server-side header re-verification (never trusts client-supplied URLs).
  - Strict SSRF protection preventing requests to private IP ranges, loopback, and cloud metadata endpoints (`169.254.169.254`).
  - No cookies, credentials, or tracking sent during one-click requests.
- **State Persistence:** Remembers unsubscribed mailing lists and updates the button into a disabled **"✓ Unsubscribed"** badge.
- **Full Internationalization (i18n):** Ready for localization with complete `en_US` language definitions.

---

## Directory Structure

```
easy_unsubscribe/
├── composer.json                       # Plugin package metadata and requirements
├── config.inc.php.dist                 # Configuration template
├── easy_unsubscribe.php                # Main PHP plugin class (hooks & controllers)
├── easy_unsubscribe.js                 # Client-side DOM manipulation & modal logic
├── localization/
│   └── en_US.inc                       # English translations
├── README.md                           # Documentation
└── skins/
    ├── elastic/
    │   └── easy_unsubscribe.css       # Elastic skin styling (light + dark mode)
    └── larry/
        └── easy_unsubscribe.css       # Classic Larry skin styling
```

---

## Installation

### Manual Installation

1. Copy or clone the `easy_unsubscribe` directory into your Roundcube `plugins/` directory:
   ```bash
   cp -r easy_unsubscribe /path/to/roundcubemail/plugins/
   ```

2. Enable the plugin in your Roundcube configuration file (`config/config.inc.php`):
   ```php
   $config['plugins'] = [
       // ... other plugins ...
       'easy_unsubscribe',
   ];
   ```

3. (Optional) Customize plugin settings:
   ```bash
   cd /path/to/roundcubemail/plugins/easy_unsubscribe
   cp config.inc.php.dist config.inc.php
   ```

### Composer Installation

If managing Roundcube via Composer:
```bash
composer require webdotpulse/easy_unsubscribe
```

---

## Configuration Options

Edit `plugins/easy_unsubscribe/config.inc.php` to customize:

| Option | Type | Default | Description |
|---|---|---|---|
| `easy_unsubscribe_prefer_oneclick` | `bool` | `true` | Prioritize RFC 8058 One-Click POST over Mailto and HTTP links. |
| `easy_unsubscribe_allow_http_oneclick` | `bool` | `false` | Allow HTTP for One-Click POST (RFC 8058 mandates HTTPS). |
| `easy_unsubscribe_timeout` | `int` | `15` | Request timeout in seconds for RFC 8058 POST requests. |
| `easy_unsubscribe_ssrf_protection` | `bool` | `true` | Block private IP ranges, loopback, and cloud metadata endpoints. |
| `easy_unsubscribe_persist_state` | `bool` | `true` | Save unsubscribed status across sessions in user preferences. |
| `easy_unsubscribe_user_agent` | `string` | `'Roundcube-EasyUnsubscribe/1.0'` | Custom User-Agent header for HTTP requests. |

---

## Supported Protocols & Standards

### RFC 8058: Signaling One-Click Functionality for List-Unsubscribe
When an email contains:
```http
List-Unsubscribe: <https://example.com/unsubscribe/oneclick?token=xyz>, <mailto:unsub@example.com>
List-Unsubscribe-Post: List-Unsubscribe=One-Click
```
The plugin triggers an asynchronous HTTP POST:
- **Target:** `https://example.com/unsubscribe/oneclick?token=xyz`
- **Method:** `POST`
- **Headers:** `Content-Type: application/x-www-form-urlencoded`
- **Body:** `List-Unsubscribe=One-Click`
- **Redirects:** Not followed (per RFC 8058 Section 3.1)

### RFC 2369: The Use of URLs as Meta-Syntax for Core Mail List Commands
When an email contains:
```http
List-Unsubscribe: <mailto:unsub-request@example.com?subject=unsubscribe>
```
The plugin automatically sends an unsubscribe message from the user's active Roundcube identity to `unsub-request@example.com` with subject `unsubscribe`.

---

## License

This plugin is released under the **GNU General Public License v3.0 or later (GPL-3.0-or-later)**.
