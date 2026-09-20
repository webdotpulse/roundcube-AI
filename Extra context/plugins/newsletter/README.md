# Newsletter Plugin for Roundcube

A high-deliverability Newsletter Campaign Suite for Roundcube Webmail that enables sending mass emails to address book contacts, groups, or custom recipient lists in throttled batches while applying industry-standard anti-spam deliverability engineering.

---

## 🎯 Deliverability & Anti-Spam Architecture

Sending bulk emails through webmail typically results in messages landing in the spam or junk folder. This plugin implements technical safeguards and best practices to ensure high inbox delivery rates:

### 1. ✉️ 1-to-1 Envelope Delivery (Never Bulk BCC)
- **The Problem**: Blasting emails by putting 50+ recipients into `Bcc:` is an immediate red flag for SpamAssassin, Gmail, Outlook, and Yahoo. Furthermore, a single bouncing address in BCC can cause the entire message to fail.
- **The Solution**: The plugin sends an individual SMTP envelope for every recipient. Each email has the recipient's personal name and address directly in the `To:` header: `To: "Jane Doe" <jane@example.com>`.

### 2. 🏷️ RFC 8058 One-Click Unsubscribe Compliance
- **Requirement**: Since February 2024, Google and Yahoo enforce RFC 8058 one-click unsubscribe headers for bulk senders. Missing headers can cause ISP blocking or junk folder penalties.
- **Embedded Headers**:
  ```http
  List-Unsubscribe: <https://your-domain/?_task=newsletter&_action=plugin.newsletter-unsubscribe&email=...&t=...>, <mailto:unsubscribe+token@domain?subject=unsubscribe>
  List-Unsubscribe-Post: List-Unsubscribe=One-Click
  Precedence: bulk
  Auto-Submitted: auto-generated
  ```
- Recipient email clients (Gmail, Apple Mail, Outlook Web) show a native **"Unsubscribe"** button at the top of the email that functions instantly without user frustration.

### 3. 🛡️ Permanent Suppression List
- Any recipient who clicks the unsubscribe button or web link is immediately added to the suppression list.
- Prior to every campaign dispatch, recipients are checked against the suppression database. Unsubscribed addresses are excluded automatically.

### 4. 📄 Multipart/Alternative (Dual MIME: HTML + Plaintext)
- HTML-only emails with no plaintext alternative receive substantial penalties in SpamAssassin (`MIME_HTML_ONLY`, `HTML_MESSAGE`).
- The plugin automatically generates a clean, readable plaintext alternative from your HTML, preserving link URLs and line spacing.

### 5. ⏱️ Throttled Batch Dispatch
- High-velocity SMTP sending triggers rate limit rejections (`421 Too many connections` or `450 Rate limit exceeded`).
- The plugin sends in configurable chunks (default: 25 recipients per batch) with micro-throttling between dispatches (default: 150ms) and pauses between batches (default: 2s).
- Live progress tracking provides real-time Sent/Failed counters, ETA calculations, Pause, Resume, and Cancel controls.

### 6. 🔍 Pre-Flight Anti-Spam Heuristic Analyzer
- Evaluates subject line length, uppercase percentage (ALL CAPS penalties), excessive punctuation (`!!!`, `???`, `$$$`), and over 20+ known spam trigger keywords.
- Inspects body HTML for missing unsubscribe links, dangerous elements (`<script>`, `<iframe>`, `<form>`), and text-to-code ratios.
- Provides a color-coded Spam Score (0 - 100) with actionable recommendations before sending.

---

## 🛠️ Configuration

Copy `config.inc.php.dist` to `config.inc.php` in the plugin directory to customize defaults:

```php
// Number of recipients sent per batch
$config['newsletter_batch_size'] = 25;

// Delay between batches in seconds
$config['newsletter_batch_delay'] = 2;

// Throttle pause between individual emails in milliseconds
$config['newsletter_throttle_ms'] = 150;

// Warning threshold for anti-spam score
$config['newsletter_spam_score_threshold'] = 50;

// Automatically append unsubscribe footer if not present in template
$config['newsletter_auto_append_unsubscribe_footer'] = true;
```

---

## 🚀 CLI Background Worker (Cron)

For headless or automated batch dispatching, schedule the CLI cron worker:

```bash
# Process active newsletter queues every 5 minutes
*/5 * * * * php /path/to/roundcube/plugins/newsletter/cron.php >> /var/log/roundcube-newsletter.log 2>&1
```

---

## 🔑 External DNS Deliverability Checklist

To achieve >99% inbox placement, ensure your server DNS is configured with:
1. **SPF (Sender Policy Framework)**: `v=spf1 mx ip4:YOUR_IP ~all`
2. **DKIM (DomainKeys Identified Mail)**: Sign outgoing messages with a 2048-bit key.
3. **DMARC**: `v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@yourdomain.com`
4. **Reverse DNS (PTR)**: Ensure the reverse DNS of your mail server IP matches its hostname.
