# Email Scheduler & Undo Send Plugin for Roundcube Webmail

Allows Roundcube users to delay, cancel, or schedule the delivery of their email messages.

## Features
- **Undo Send (Delay Delivery)**: Automatically delays message delivery by 5 to 30 seconds with an animated countdown toast and an instant "Undo" button that keeps the composer open.
- **Send Later (Schedule Delivery)**: Schedule outgoing emails to be delivered at a specific future date and time using quick presets (Tomorrow morning, Tomorrow afternoon, Monday morning) or a custom date/time picker.
- **Queue Management**: View, reschedule, cancel, or immediately dispatch pending messages in `Settings > Preferences > Email Scheduler & Undo Send`.
- **Autonomous Delivery Worker**: Delivers due scheduled emails via cron (`cron.php`) or opportunistic background dispatch.

## Installation
Add `email_scheduler` to the `$config['plugins']` array in your Roundcube `config/config.inc.php`:
```php
$config['plugins'] = [
    // ...
    'email_scheduler',
];
```
Copy `plugins/email_scheduler/config.inc.php.dist` to `plugins/email_scheduler/config.inc.php`.

## Cron Worker Setup
Add the following cron entry to automatically deliver scheduled emails:
```bash
* * * * * php /path/to/roundcube/plugins/email_scheduler/cron.php >/dev/null 2>&1
```
