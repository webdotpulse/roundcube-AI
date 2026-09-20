CREATE TABLE IF NOT EXISTS newsletter_campaigns (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL DEFAULT 0,
    subject VARCHAR(500) NOT NULL DEFAULT '',
    from_email VARCHAR(255) NOT NULL DEFAULT '',
    from_name VARCHAR(255) NOT NULL DEFAULT '',
    recipients_total INTEGER NOT NULL DEFAULT 0,
    recipients_sent INTEGER NOT NULL DEFAULT 0,
    recipients_failed INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    body_html TEXT,
    body_text TEXT,
    spam_score INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL,
    started_at TIMESTAMP,
    finished_at TIMESTAMP,
    log_data TEXT
);

CREATE INDEX IF NOT EXISTS newsletter_campaigns_user_status ON newsletter_campaigns (user_id, status);
CREATE INDEX IF NOT EXISTS newsletter_campaigns_created ON newsletter_campaigns (created_at);

CREATE TABLE IF NOT EXISTS newsletter_suppressions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL DEFAULT 0,
    email VARCHAR(255) NOT NULL,
    reason VARCHAR(64) NOT NULL DEFAULT 'user_unsubscribe',
    campaign_id INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL,
    CONSTRAINT newsletter_suppressions_user_email UNIQUE (user_id, email)
);

CREATE INDEX IF NOT EXISTS newsletter_suppressions_email ON newsletter_suppressions (email);
