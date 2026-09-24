# Step-by-Step Manual: Activating the Background Cron Worker

This guide provides a comprehensive, step-by-step walkthrough to activate and monitor the **Gemini Executive Assistant Background Worker** for Roundcube.

> [!TIP]
> **Interactive Version Available**:
> Open [**CRON_WORKER_SETUP.html**](CRON_WORKER_SETUP.html) in your web browser to interactively customize your server paths (`/data/sites/web/...`), test presets, and copy auto-adapted crontab and daemon commands with a single click.

The background worker operates autonomously in the background (even when all users are completely logged out of Roundcube). It connects to IMAP mailboxes, runs Google Gemini triage, tags emails with organizational labels (compatible with `roundcube-labels`), replicates verified answers to common client questions from AI memory, and deposits pre-crafted executive responses directly into the IMAP Drafts folder ready for review.

---

## Architecture Overview

```
[ Incoming Client Email in INBOX ]
               │
               ▼
   [ Background Cron Worker ] (bin/worker.php)
               │
               ├─► 1. Triage & Security Check (Gemini)
               │      └─► Sets IMAP Label Flag ($Label1 - $Label5 via roundcube-labels)
               │
               ├─► 2. AI Knowledge & Memory Check
               │      └─► Replicates verified answer if similar question was answered before
               │
               └─► 3. Draft Generation (Gemini 3.8 Flash)
                      └─► Saves formatted draft into IMAP "Drafts" folder (X-Unsent: 1)
```

---

## Step 1: Verify System Prerequisites

Run the following command on your server to verify that your PHP CLI environment satisfies all prerequisites:

```bash
php -v
php -m | grep -E "openssl|curl|json|mbstring|pcntl"
```

### Requirements:
- **PHP CLI**: Version 8.0 or newer.
- **Extensions**:
  - `openssl` (for secure IMAP TLS/SSL connections)
  - `curl` (for Google Gemini API communication)
  - `json` & `mbstring` (for payload parsing and UTF-8 handling)
  - `pcntl` (optional, for graceful shutdown signals in daemon mode)

---

## Step 2: Configure the Worker Settings

Ensure your plugin configuration file `config.inc.php` exists in the plugin directory:

```bash
cd /var/www/html/plugins/lifeprisma_ai
cp config.inc.php.dist config.inc.php
```

Open `config.inc.php` and configure the following sections:

### 1. Google Gemini API Key
```php
$config['lifeprisma_ai_gemini_api_key'] = 'YOUR_GOOGLE_GEMINI_API_KEY';
$config['lifeprisma_ai_gemini_model'] = 'gemini-3.8-flash';
```

### 2. Message Triage Labels (`roundcube-labels` integration)
```php
$config['lifeprisma_ai_triage_labels_enabled'] = true;
$config['lifeprisma_ai_triage_label_map'] = [
    'action_required_high' => '$Label1', // Belangrijk / Important (Red)
    'action_required'      => '$Label4', // Te doen / To Do (Blue)
    'meeting'              => '$Label2', // Werk / Work (Orange)
    'follow_up'            => '$Label4', // Te doen / To Do (Blue)
    'fyi'                  => '$Label5', // Later (Violet)
    'scam'                 => '$Label1', // Red / Warning
];
```

### 3. Account Configuration

Choose **Method A** (Individual Accounts) or **Method B** (Dovecot Master User):

#### Method A: Individual Account Credentials
```php
$config['lifeprisma_ai_worker_imap_host'] = 'ssl://localhost:993';
$config['lifeprisma_ai_worker_accounts'] = [
    [
        'email' => 'koen@thechargegrid.com',
        'password' => 'your_secret_password_here',
        'drafts_folder' => 'Drafts', // Matches Roundcube Drafts folder name
    ],
];
```

#### Method B: Dovecot Master User (Recommended for multi-user servers)
If your server uses Dovecot Master Users, you do not need to store individual user passwords:
```php
$config['lifeprisma_ai_worker_imap_host'] = 'ssl://localhost:993';
$config['lifeprisma_ai_worker_dovecot_master_user'] = 'masteradmin';
$config['lifeprisma_ai_worker_dovecot_master_password'] = 'master_password';
$config['lifeprisma_ai_worker_accounts'] = [
    ['email' => 'koen@thechargegrid.com', 'drafts_folder' => 'Drafts'],
    ['email' => 'support@thechargegrid.com', 'drafts_folder' => 'Drafts'],
];
```

---

## Step 3: Test with Dry-Run Mode

Before creating the cron job, test the worker in safe **dry-run** mode. Dry-run mode connects to IMAP, scans unread messages, calls Gemini, and prints the triage classification, label assignment, and generated drafts directly to your terminal without modifying anything on IMAP.

```bash
php bin/worker.php --dry-run --verbose
```

### Expected Output:
```
===========================================================
Gemini Executive Assistant — 24/7 CLI Background Worker
Model: gemini-3.8-flash | Mode: Single Pass
DRY RUN MODE ENABLED — No changes will be written to IMAP
===========================================================
[INFO] Processing account: koen@thechargegrid.com on ssl://localhost:993
[INFO] Account: koen@thechargegrid.com — Found 1 unread email(s)
[LABEL] UID 142 'Order #47348 confirmed' — Tagged with label flag $Label1
[AI] Generating Gemini (gemini-3.8-flash) draft reply for: 'Order #47348 confirmed'...
================= [DRY-RUN DRAFT] =================
To: client@example.com
Subject: Re: Order #47348 confirmed
...
===================================================
[INFO] Worker pass finished.
```

---

## Step 4: Setup the Linux Cron Job

### 1. Set Permissions
Ensure the web server user (typically `www-data` on Debian/Ubuntu, `nginx` or `apache` on RHEL/CentOS) has execute permissions:

```bash
chown -R www-data:www-data /var/www/html/plugins/lifeprisma_ai
chmod +x /var/www/html/plugins/lifeprisma_ai/bin/worker.php
```

### 2. Create the Log File
```bash
touch /var/log/roundcube-ai-worker.log
chown www-data:www-data /var/log/roundcube-ai-worker.log
chmod 0640 /var/log/roundcube-ai-worker.log
```

### 3. Add Crontab Entry
Open the crontab editor:

```bash
# When logged in as your account user (e.g. Combell SSH / cPanel):
crontab -e

# Or if logged in as root on a multi-user VPS:
# crontab -e -u www-data
```

Add one of the following schedules:

#### Recommended: Every 3 minutes
```cron
*/3 * * * * /usr/bin/php /var/www/html/plugins/lifeprisma_ai/bin/worker.php >> /var/log/roundcube-ai-worker.log 2>&1
```

#### Every 5 minutes:
```cron
*/5 * * * * /usr/bin/php /var/www/html/plugins/lifeprisma_ai/bin/worker.php >> /var/log/roundcube-ai-worker.log 2>&1
```

#### Every minute (high responsiveness):
```cron
* * * * * /usr/bin/php /var/www/html/plugins/lifeprisma_ai/bin/worker.php >> /var/log/roundcube-ai-worker.log 2>&1
```

---

## Step 5: Configure Log Rotation

To ensure log files do not grow indefinitely, create a logrotate configuration:

Create `/etc/logrotate.d/roundcube-ai-worker`:

```
/var/log/roundcube-ai-worker.log {
    weekly
    missingok
    rotate 8
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

Test log rotation syntax:
```bash
logrotate -d /etc/logrotate.d/roundcube-ai-worker
```

---

## Step 6: Alternative: 24/7 Systemd Daemon Service

If you prefer a continuous background daemon rather than cron, you can use the included systemd unit:

### 1. Copy the Service File
```bash
cp /var/www/html/plugins/lifeprisma_ai/bin/lifeprisma-ai-worker.service /etc/systemd/system/
```

### 2. Verify Working Directory & User
Edit `/etc/systemd/system/lifeprisma-ai-worker.service` to verify paths match your Roundcube installation:
```ini
[Unit]
Description=Gemini Executive Assistant 24/7 Background Worker (Autonomous Mode)
After=network.target dovecot.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/html/plugins/lifeprisma_ai
ExecStart=/usr/bin/php /var/www/html/plugins/lifeprisma_ai/bin/worker.php --daemon --interval=60
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

### 3. Enable and Start the Service
```bash
systemctl daemon-reload
systemctl enable lifeprisma-ai-worker.service
systemctl start lifeprisma-ai-worker.service
```

### 4. Check Status & Live Logs
```bash
systemctl status lifeprisma-ai-worker.service
journalctl -u lifeprisma-ai-worker.service -f
```

---

## Step 7: Verification Checklist

| Check | How to Verify | Expected Result |
| :--- | :--- | :--- |
| **Crontab Active** | `crontab -l` | Shows worker command entry |
| **Log Output** | `tail -f /var/log/roundcube-ai-worker.log` | Shows periodic execution passes |
| **Label Sync** | Send an email with "Urgent" or questions | Email row receives red/blue badge in Roundcube |
| **Drafts Created** | Check Roundcube "Drafts" folder | Draft reply appears with "Re: ..." subject |
| **Answer Replication** | Send a known question from a new client | Replicates verified answer from AI memory |

---

## Troubleshooting

### 1. `Cannot connect to IMAP server`
- Ensure the IMAP port is correct: `ssl://localhost:993` for TLS or `tcp://localhost:143` with STARTTLS.
- If testing on localhost with a self-signed certificate, stream SSL verification is relaxed automatically in `LpaiImapClient`.

### 2. `IMAP login failed`
- If using individual accounts, check the password or app-specific password.
- If using Dovecot Master User, confirm Master User syntax: `username*masteruser` with `master_password`.
- Verify Dovecot allows master user logins in `/etc/dovecot/conf.d/10-auth.conf`.

### 3. Draft folder name mismatch
- If drafts are not appearing in Roundcube, verify your draft mailbox name (e.g. `Drafts` vs `INBOX.Drafts` vs `Concepten` in Dutch locales). Set `'drafts_folder' => 'Drafts'` in account configuration.
