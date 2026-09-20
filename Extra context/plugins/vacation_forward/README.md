# Advanced Vacation Out-of-Office & Mail Forwarding Plugin for Roundcube Webmail

A comprehensive, enterprise-grade Roundcube plugin providing independent or unified **Vacation Out-of-Office Auto-Replies** and **Mail Forwarding & Redirection**.

## Key Features

- **Vacation / Out-of-Office Scheduler**:
  - Configure start and end dates/times with quick presets (*This Weekend*, *Next Week*, *Next 2 Weeks*).
  - Timezone-aware date calculations.
  - Auto-disable option when the vacation period expires.
  - Dynamic status badges: **Active Now**, **Scheduled**, **Expired**, **Disabled**.
- **Multi-Language Auto-Reply Templates**:
  - Create and manage multiple templates per language (`en`, `nl`, `de`, `fr`, `es`, `it`, etc.).
  - Automatic language detection from email headers (`Content-Language`, `Accept-Language`), sender TLDs, and stop-word text analysis.
  - Domain-based routing (e.g., specific replies for internal `@company.com` vs external clients).
  - Rich placeholder interpolation: `{START_DATE}`, `{END_DATE}`, `{RETURN_DATE}`, `{SENDER_NAME}`, `{SENDER_EMAIL}`, `{ORIGINAL_SUBJECT}`, `{USER_NAME}`, `{USER_EMAIL}`.
  - Built-in simulation tool to preview how incoming messages are parsed and replied to.
- **Anti-Loop & Smart Suppression**:
  - RFC 3834 compliant (`Auto-Submitted: auto-replied`, `Precedence: auto_reply`, `X-Auto-Response-Suppress: All`).
  - Automatically suppresses replies to mailing lists, bots, bounces, and system daemons (`mailer-daemon`, `postmaster`, `noreply`).
  - Blacklist filters for specific domains or email addresses.
  - Per-sender rate-limiting throttle (e.g. at most 1 auto-reply per 24 hours per sender).
- **Mail Forwarding & Redirection**:
  - Multiple destination email addresses.
  - Option to keep a local copy in the Inbox or redirect without storing.
  - Forwarding modes: inline preamble (`---------- Forwarded message ---------`) or clean redirect (`Resent-From`).
  - Subject keyword filters.
- **ManageSieve Compatibility & Export**:
  - One-click export of RFC-compliant Sieve vacation and redirect scripts.
- **Autonomous Delivery & Offline Cron**:
  - Background worker (`cron.php`) for CLI or webhook automated processing.

## Installation

Add `vacation_forward` to the `$config['plugins']` array in your Roundcube `config/config.inc.php`:

```php
$config['plugins'] = [
    // ...
    'vacation_forward',
];
```

Run the companion installer:
```bash
php bin/install-extra.php --activate
```

## Cron Job Configuration

To automatically disable expired vacations and process offline mailboxes, add the following cron entry:

```bash
* * * * * php /path/to/roundcube/plugins/vacation_forward/cron.php >/dev/null 2>&1
```
