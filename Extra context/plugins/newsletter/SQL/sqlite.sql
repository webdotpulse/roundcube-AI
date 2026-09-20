CREATE TABLE IF NOT EXISTS newsletter_campaigns (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL DEFAULT 0,
    subject TEXT NOT NULL DEFAULT '',
    from_email TEXT NOT NULL DEFAULT '',
    from_name TEXT NOT NULL DEFAULT '',
    recipients_total INTEGER NOT NULL DEFAULT 0,
    recipients_sent INTEGER NOT NULL DEFAULT 0,
    recipients_failed INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'draft',
    body_html TEXT,
    body_text TEXT,
    spam_score INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    started_at DATETIME,
    finished_at DATETIME,
    log_data TEXT
);

CREATE INDEX IF NOT EXISTS newsletter_campaigns_user_status ON newsletter_campaigns (user_id, status);
CREATE INDEX IF NOT EXISTS newsletter_campaigns_created ON newsletter_campaigns (created_at);

CREATE TABLE IF NOT EXISTS newsletter_suppressions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL DEFAULT 0,
    email TEXT NOT NULL,
    reason TEXT NOT NULL DEFAULT 'user_unsubscribe',
    campaign_id INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS newsletter_suppressions_user_email ON newsletter_suppressions (user_id, email);
CREATE INDEX IF NOT EXISTS newsletter_suppressions_email ON newsletter_suppressions (email);
